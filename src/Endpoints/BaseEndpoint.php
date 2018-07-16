<?php

namespace Laravelladder\Core\Endpoints;

use Laravelladder\Core\Repositories\CoreRepositoryMixin;
use Laravelladder\Core\Collections\BaseCollection;
use Laravelladder\Core\Exceptions\BaseException;
use Laravelladder\Core\Exceptions\Endpoints\ArgumentRequiredException;
use Laravelladder\Core\Models\BaseModel;
use Laravelladder\Core\Models\BaseResponse;
use Laravelladder\Core\Services\CacheMixin;
use Laravelladder\Core\Validations\Rule;
use Laravelladder\Core\Validations\ValidationMixin;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Laravelladder\Core\Models\EmptyModel;
use Laravelladder\Core\Utils\String\Helper;
/**
 * Class BaseEndpoint
 *
 * 系统业务域Endpoint基类
 *
 * @package Laravelladder\Core\Endpoints
 */
abstract class BaseEndpoint
{
	use DispatchesJobs;
	use CacheMixin;
	use CoreRepositoryMixin;
	use ValidationMixin;

	const ARGUMENT_P_LIMIT = 'p_limit'; // 分页
	const ARGUMENT_P_OFFSET = 'p_offset'; // 跳过数目
	
	const ARGUMENT_FILTERS = 'filters'; // 过滤条件,如
	/**
	 * $this->argument(static::ARGUMENT_FILTERS) =
	 * array(
	 *      array('display_name', 'like', '%123%'),
	 *      array('age', '=', '44'),
	 *      array('parent_id', 'IN', [1,2,3])
	 * );
	 */
	
    /**
     * 获取一个实例
     * @return static
     */
    public static function getInstance(){
        return new static();
    }

    /**
     * 获取Endpoint运行需要的参数
     * @return array
     */
    protected function getArguments()
    {
        return array(
            //array('testParam1', InputArgument::REQUIRED, '必填字段名', '默认值'),
            //array('testParam2', InputArgument::OPTIONAL, '选填字段名', '默认值'),
	        //array('testParam2', [Rule::ID_VALUE_CUSTOM_CHINESE_ONLY => true, Rule::ID_VALUE_DIGIT_BETWEEN => '1,10'], '选填字段名', '默认值'),
        );
    }

    /**
     * 子类需要实现的方法，包含了业务最核心的逻辑和调用流程，
     * 只能调用Repo，Service，Event 和Queue ，其他人通
     * 过查看Endpoint方法就能直接了解业务的核心逻辑
     *
     * @return mixed
     */
    abstract public function dryRun();
	
	/**
	 * 功能同 dryRun 但运行完成后会打印日志
	 * @return mixed
	 */
    public function execute(){
    	$result = $this->dryRun();
	    \Log::debug("Endpoint " . get_called_class() . " 运行完毕");
	    return $result;
    }
    /**
     * 获取一个参数的值
     * @param string $key 参数字段
     * @return mixed
     */
    protected function argument($key){
        if(isset($this->arguments[$key])) return $this->arguments[$key];
    }
	
	/**
	 * 假设我们笃定字段是json或者array的话， 获取array
	 * @param $key string 参数字段
	 * @return mixed
	 */
	protected function argumentArray($key){
		$argument = $this->argument($key);
		if(is_array($argument)) return $argument;
		$result = @json_decode($argument,true);
		if (is_array($result)) {
			return $result;
		}else{
			return [$argument];
		}
	}
	
    protected $arguments = [];
	
	/**
	 * 设置运行参数
	 * @param array $arguments
	 * @return $this
	 * @throws ArgumentRequiredException
	 * @throws \Laravelladder\Core\Exceptions\Validations\ValidationException
	 */
    public function setArguments(array $arguments){
	    $validArguments = $this->getArguments();
	    $toValidateValues = [];
	    $toValidateRules = [];
	    \Log::debug(get_called_class() . " 待校验传参为 " . json_encode($arguments));
	    /**
	     * 往$this->arguemtns中赋予参数
	     */
	    foreach ($validArguments as $key => $value){ // 遍历Endpoint定义需要传的参数
		    $argKey = $value[0];
		    $argRule = $value[1];
		    $argName = isset($value[2]) ? $value[2] : "";
		    $argDefault = isset($value[3]) ? $value[3] : null;
		    // $arguments 为用户实际传入的参数
	    	foreach ($arguments as $k => $v){ // 如果Endpoint有定义需要传参，才设置argument
			    if($argKey == $k) {
                    Helper::ensureUtf8($v);
				    $this->arguments[$k] = $v;
				    break;
			    }
		    }
		    
		    $fieldDisplayName = "[$argName:$argKey]";
		    if(isset($this->arguments[$argKey])){
	    		// DO NOTHING
			    // 如果有值了，就部用取默认值
		    } elseif (in_array($argRule, [InputArgument::REQUIRED, Rule::ID_VALUE_REQUIRED])){
			    throw new ArgumentRequiredException($fieldDisplayName);
		    } else {
			    $this->arguments[$argKey] = $argDefault;
		    }
		    
		    // 生成过滤字段
		    $toValidateRules[$fieldDisplayName] = is_array($argRule) ? $argRule : [];
		    $toValidateValues[$fieldDisplayName] = $this->arguments[$argKey];
	    }
	    
	    // 数据校验
	    if(count($toValidateValues) > 0) {
		    $validator = $this->validate($toValidateValues, $toValidateRules);
		    if($validator->customFails()) throw $validator->makeException();
	    }
	    
	    \Log::debug("Endpoint " . get_called_class() . " 传参", $this->arguments);
	    return $this;
    }

    /**
     * 设置参数并调用dryRun方法
     * @param $arguments
     * @return mixed
     */
    public function dryRunWithArguments($arguments){
        return $this
            ->setArguments($arguments)
            ->execute();
    }
	
	/**
	 * 设置参数并调用execute方法
	 * @param array $arguments
	 * @return mixed
	 */
	public function executeWithArguments(array $arguments){
    	return $this->dryRunWithArguments($arguments);
	}
		/**
     * 设置参数并调用fire方法
     * @param $arguments
     * @return mixed
     */
    public function fireWithArguments($arguments){
        return $this
            ->setArguments($arguments)
            ->fire();
    }
	
	/**
	 * 调用dryRun并将结果打包成Api友好的值
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
	 */
    public function fire(){
	    $cachedResultContent = $this->getResultFromCache('fire');
	    if($cachedResultContent) return $cachedResultContent;
        $result = $this->execute();
        $response = static::makeResultResponse($result);
	    $contentToCache = $response->getContent();
	    $this->storeResultToCache('fire', $contentToCache);
	    return $response;
    }
	
	/**
	 * 处理Result将结果打包成Api友好的值
	 * @param $result
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
	 */
    public static function makeResultResponse($result){
	    $response = BaseResponse::getInstance();
	    if($result instanceof BaseModel) $response->setData($result->toApiArray());
	    if($result instanceof BaseCollection) $response->setData($result->toPaginatedApiArray());
	    if(!is_object($result)) $response->setData($result);
	    return $response->toApiResponse();
    }
	

    public static function makeEmptyModel($id = 0, $result = 'ok'){
	    return new EmptyModel([
	    	EmptyModel::FIELD_ID => $id,
		    EmptyModel::FIELD_RESULT => $result
	    ]);
    }
    
	/*
	|--------------------------------------------------------------------------
	| 抛异常相关
	|--------------------------------------------------------------------------
	*/
	
	/**
	 * 生成普通的异常
	 * @param $message
	 * @param int $code
	 * @param bool $report
	 * @return BaseException
	 */
    public static function makeGeneralException($message, $code = 400, $report = true){
	    return BaseException::getInstance($message, $code, null, $report);
    }
	
	/*
	|--------------------------------------------------------------------------
	| 分页相关
	|--------------------------------------------------------------------------
	*/


    const ARGUMENT_SORT = 'sort';
    const ARGUMENT_PER_PAGE = 'perPage';
    const ARGUMENT_PAGE = 'page';

    protected function pagionationArguments(){
        return array(
            array(static::ARGUMENT_PER_PAGE, InputArgument::OPTIONAL, 'per page', 10),
            array(static::ARGUMENT_PAGE, InputArgument::OPTIONAL, 'which page', 1),
            array(static::ARGUMENT_SORT, InputArgument::OPTIONAL, 'sort', ModelBase::FIELD_ID . "|asc"),
        );
    }

    protected function getPaginationArguments(){
        $array = explode('|', $this->argument(static::ARGUMENT_SORT));
        return array(
            $this->argument(static::ARGUMENT_PER_PAGE),
            $this->argument(static::ARGUMENT_PAGE),
            isset($array[0]) && !empty($array[0]) ? $array[0] : ModelBase::FIELD_ID,
            isset($array[1]) && in_array($array[1], array('desc', 'asc'))? $array[1] : "asc"
        );
    }
	
	/*
	|--------------------------------------------------------------------------
	| 缓存相关
	|--------------------------------------------------------------------------
	*/
	
	protected $enableCache = false;
	protected $cacheExpiry = 30;
	
	/**
	 * 将数据存入缓存
	 * @param string $methodName 缓存的方法名, 推荐只缓存dryRun和fire的返回值
	 * @param $result
	 */
	protected function storeResultToCache($methodName = 'fire', $result){
		if(!$this->enableCache || strtoupper($this->getEnvName()) != 'PRODUCTION') return;
		static::saveToCache($methodName,md5(json_encode($this->arguments)), $result);
	}
	
	/**
	 * 将数据从缓存中取出
	 * @param string $methodName 缓存的方法名, 推荐只缓存dryRun和fire的返回值
	 * @return mixed
	 */
	protected function getResultFromCache($methodName = 'fire'){
		if(!$this->enableCache || strtoupper($this->getEnvName()) != 'PRODUCTION') return;
		$cachedResult = static::getFromCache($methodName, md5(json_encode($this->arguments)));
		if(!is_null($cachedResult)){
			\Log::debug(__METHOD__ . "缓存不为空, 直接读取缓存");
			return $cachedResult;
		}
	}
}
