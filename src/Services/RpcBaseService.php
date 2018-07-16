<?php

namespace Laravelladder\Core\Services;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Foundation\Testing\Constraints\PageConstraint;
use Laravelladder\Core\Collections\BaseCollection;
use Laravelladder\Core\Exceptions\BaseException;
use Laravelladder\Core\Exceptions\Services\ResponseContentInvalidException;
use Laravelladder\Core\Exceptions\Services\ResponseStatusInvalidException;
use Laravelladder\Core\Exceptions\Services\RpcRequestException;
use Laravelladder\Core\Models\BaseModel;
use Laravelladder\Core\Models\BaseResponse;
use Laravelladder\Core\ServiceProviders\Log\Logger;
use Laravelladder\Core\Utils\Time;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Laravelladder\Core\Utils\Request;

/**
 * Class RpcBaseService
 * RPC服务基类
 * @package Laravelladder\Core\Services
 */
abstract class RpcBaseService extends BaseService
{
	const HEADER_PREFIX = 'X-Laravelladder-';
    /**
     * Api请求客户端
     * @var \GuzzleHttp\Client
     */
    protected static $_apiClient;

    /**
     * 获取Api请求客户端
     * @return \GuzzleHttp\Client
     */
    protected static function getApiClient()
    {
        if (!static::$_apiClient) {
            $timestamp = Time\Helper::getNowMYSQL();
            $appId = static::getAppId();
            $encryptString = $appId . "|" . $timestamp;
            static::$_apiClient = new \GuzzleHttp\Client([
                'base_uri' => static::getResourceUrl() . ":" . static::getResourcePort(),
                'timeout' => static::getTimeout(),
                'headers' => array(
                    'User-Agent' => 'Laravelladder-core/1.0',
                    'Accept'     => 'application/json',
	                static::HEADER_PREFIX . 'TRACE' => Logger::getTrace(),
                    static::HEADER_PREFIX . 'IP' => Request\Helper::getRequestIp(),
	                static::HEADER_PREFIX . 'APPID' => $appId,
	                static::HEADER_PREFIX . 'TIMESTAMP' => $timestamp,
	                static::HEADER_PREFIX . 'SIGNITURE' => $encryptString,
                ),
            ]);
        }
        return static::$_apiClient;
    }

    public static function getInstance(){
	    return new static();
    }
    /**
     * 客户端对应系统ID
     * @return mixed
     */
    protected static function getAppId(){
        return \Config::get('app.id');
    }
	
	/**
	 * 获取缓存的prefix
	 * @return mixed
	 */
    protected static function getCachePrefix(){
	    return str_replace('/', '_', static::getClassName());
    }
	
	/**
	 * 获取缓存过期时间
	 * @return int
	 */
    protected static function getCacheExpiry(){
	    return 5;
    }
    
	/**
	 * 生成缓存Key
	 * @param $url
	 * @param $params
	 * @return string
	 */
    protected static function getCacheKey($url, $params){
	    if(is_array($params)) $params = md5(json_encode($params));
	    return sprintf("%s_%s_%s",
		    static::getCachePrefix(),
		    $url,
		    $params);
    }

	/**
	 * 保存object到缓存
	 * @param $url
	 * @param $params
	 * @param $value
	 */
    protected static function saveToCache($url, $params, $value){
	    return \Cache::put(
	    	static::getCacheKey($url, $params),
		    $value,
		    static::getCacheExpiry());
    }
	
	/**
	 * 从缓存获取object
	 * @param $url
	 * @param $params
	 * @return mixed
	 */
	protected static function getFromCache($url, $params){
		return \Cache::get(static::getCacheKey($url, $params));
	}

    /**
     * 获得Resource路径
     * @param string $path
     * @return string
     */
	protected static function getResourceUrl($path = ''){
		$config = static::getConfig();
		return "http://{$config['uri']}:{$config['port']}/$path";
	}

    /**
     * 服务器端口
     * @return int
     */
    protected static function getResourcePort(){
        return 80;
    }

    /**
     * 请求Timeout时间
     * @return int
     */
    protected static function getTimeout(){
        return 60;
    }
	
	/**
	 * 生成请求参数
	 * @param $method
	 * @param $params
	 * @return array
	 */
	protected static function makeRequestOptions($method, $params){
		if($method == 'GET'){
			foreach ($params as $key => $param){
				$params[$key] = is_array($param) ? json_encode($param) : $param;
			}
			return array(
				'query'  => $params
			);
		} else {
			if(empty($params)) $params = ['json' => [ 'apiEmptyFiller' => '空值填充' ]];
			return array(
				'json'  => $params
			);
		}
	}
	
	/**
	 * 完成Api请求
	 * @param $method
	 * @param $url
	 * @param $params
	 * @param bool $isSync
	 * @param bool $readCache
	 * @param bool $writeCache
	 * @return array
	 * @throws RpcRequestException
	 */
    protected static function request($method,
                                      $url,
                                      $params,
                                      $isSync = true,
                                      $readCache = false,
                                      $writeCache = false
    ){
	    // 读取缓存, 开发环境无缓存
	    if(
	    	$method == 'GET'
		    && $readCache
		    && static::getEnvName() == 'production'
	    ) {
		    $cacheResponse = static::getFromCache($url, $params);
		    if(!is_null($cacheResponse)){
			    \Log::debug(get_called_class() . " 缓存不为空, 直接读取缓存");
			    return $cacheResponse;
		    }
	    }
	    
        $requestParams = static::makeRequestOptions($method, $params);
        \Log::debug(sprintf(get_class(static::getInstance()) . " Api 请求: 方法=%s 路径=%s 参数=%s 是否为同步=%s",
            $method,
            $url,
            json_encode($requestParams),
            $isSync));
        $client = static::getApiClient();
        try{
            $response = $client->request($method, $url, $requestParams);
            \Log::debug(get_called_class() . "获取返回数据, 状态值" . $response->getStatusCode());
        } catch(\Exception $e){
            throw static::makeRpcException($e, $method, $url,$requestParams);
        }

        $data = static::parseResponseData($response);
	    // 存入缓存
	    if(
		    $method == 'GET'
		    && $writeCache
		    && static::getEnvName() == 'production'
	    ) static::saveToCache($url, $params, $data);
	    return $data;
    }
	
	
	/**
	 * 非阻塞调用, 只能用于内部调用
	 * @param $url
	 * @param $params
	 * @return bool
	 * @throws RpcRequestException
	 */
	protected static function postRequestNoBlocking($url,
	                                                array $params
	){
		foreach ($params as $key => $param) $params[$key] = json_encode($param);
		\Log::debug(sprintf(get_class(static::getInstance()) . " Api 非阻塞请求: 方法=POST 路径=%s 参数=%s",
			$url,
			json_encode($params)));
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT_MS, 500);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		try{
			$output = curl_exec($ch);
			if(!$output) \Log::info("请求返回报错，继续");
		} catch (\Exception $e){
			\Log::debug("报错 {$e->getMessage()}");
		}
		curl_close($ch);
		return true;
	}

	/**
	 * 生成异常
	 * @param \Exception $e
	 * @param $method
	 * @param $url
	 * @param array $requestParams
	 * @return RpcRequestException
	 */
    protected static function makeRpcException(\Exception $e,
                                               $method,
                                               $url,
                                               array $requestParams){
	    if($e instanceof GuzzleException){
		    \Log::warning(sprintf(get_called_class() . " Api Guzzle请求出错 方法=%s 路径=%s 参数=%s 错误消息=%s",
			    $method,
			    $url,
			    json_encode($requestParams),
			    $e->getMessage()));
	    } else {
		    \Log::error(sprintf(get_called_class() . " Api请求未知错误 方法=%s 路径=%s 参数=%s 错误消息=%s",
			    $method,
			    $url,
			    json_encode($requestParams),
			    $e->getMessage()));
	    }
	    
	    if($e->getCode() == 500){ // 服务器错误，直接抛500
		    return new RpcRequestException($e->getMessage(), 500);
	    } else {
		    if($e instanceof RequestException && !is_null($e->getResponse())){
			    static::parseResponseData($e->getResponse());
		    }
		    
		    $exception = new BaseException($e->getMessage(), $e->getCode());
		    return $exception;
	    }
    }
	
	/**
	 * @param ResponseInterface $response
	 * @return array
	 * @throws ResponseContentInvalidException
	 * @throws ResponseStatusInvalidException
	 */
    protected static function parseResponseData(ResponseInterface $response){
        $code = $response->getStatusCode();
        $content = json_decode($response->getBody()->getContents(), true);
	    if(\Config::get('app.env') == 'local') \Log::debug(get_called_class() . "校验返回值", array(
            '状态' => $code,
            '内容' => $content
        ));
		if(!is_array($content))throw new ResponseContentInvalidException();
        $data = BaseResponse::constructFromArray($content);
        if( $code != 200) {
		    $exception = new ResponseStatusInvalidException($data->getMessage(), $data->{BaseResponse::FIELD_CODE});
		    $exception->setData($data->getData());
		    $exception->setReport($data->getReport());
	        throw $exception;
	    }
        return $data->getData();
    }

    /**
     * 将从api返回的标准分页array cast成Collection
     * @param array $list
     * @param $collectionName
     * @param $modelName
     * @return BaseCollection
     */
    protected static function castPaginatedListToCollection(array $list,
                                                $collectionName = null,
                                                $modelName = null){
        $collection = $collectionName ? new $collectionName() : static::getCollection();
	    $modelName = $modelName ? new $modelName() : static::getModel();
        /* @var BaseCollection $collection */
        foreach ($list[BaseCollection::PAGINATOR_FIELD_DATA] as $item){
            $collection->push(new $modelName($item));
        }
        $collection->setPagination(
            $list[BaseCollection::PAGINATOR_FIELD_TOTAL],
            $list[BaseCollection::PAGINATOR_FIELD_PER_PAGE],
            $list[BaseCollection::PAGINATOR_FIELD_FROM],
            $list[BaseCollection::PAGINATOR_FIELD_IS_PAGINATED]
        );
        return $collection;
    }
}
