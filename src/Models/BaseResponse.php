<?php
namespace Laravelladder\Core\Models;

use Illuminate\Http\Response;
use Laravelladder\Core\Exceptions\Models\ResponseDataFormatInvalidException;
use Laravelladder\Core\Utils\Request;
/**
 * Class BaseResponse
 *
 * 返回基类
 *
 * @package Laravelladder\Core\Models
 */
class BaseResponse extends Response {
    const FIELD_CODE = 'code';
    const FIELD_MESSAGE = 'message';
    const FIELD_DATA = 'data';
    const FIELD_TRACK = 'track';
    const FIELD_REPORT = 'report';

    public function __construct($code = 200, $message = 'ok', $data = [], $report = true)
    {
        $this->{static::FIELD_CODE} = $code;
        $this->{static::FIELD_MESSAGE} = $message;
        $this->{static::FIELD_DATA} = $data;
        $this->{static::FIELD_REPORT} = $report;
        $this->{static::FIELD_TRACK} = \Config::get('app.name') . Request\Helper::getRequestId();
    }

    /**
     * 从传入的array中生成实例
     * @param array $content
     * @return BaseResponse
     * @throws ResponseDataFormatInvalidException
     */
    public static function constructFromArray(array $content){
        if(
            !isset($content[static::FIELD_CODE]) ||
            !isset($content[static::FIELD_MESSAGE]) ||
            !isset($content[static::FIELD_DATA]) ||
            !isset($content[static::FIELD_TRACK])){
            throw new ResponseDataFormatInvalidException();
        }
        if(\Config::get('app.env') == 'local') \Log::debug(__METHOD__ . " 返回值为", $content);
        if(!is_array($content[static::FIELD_DATA])){
            \Log::debug("返回值data字段不是数组, 打成空对象");
            $content[static::FIELD_DATA] = [];
        }
	    if(!isset($content[static::FIELD_REPORT])){
		    $content[static::FIELD_REPORT] = true;
	    }
        return static::getInstance(
        	$content[static::FIELD_CODE],
	        $content[static::FIELD_MESSAGE],
	        $content[static::FIELD_DATA],
	        $content[static::FIELD_REPORT]
        );
    }

    /**
     * 获取返回值消息字段
     * @return array
     */
    public function getMessage(){
        return $this->{static::FIELD_MESSAGE};
    }

    /**
     * 静态获得实例
     * @param int $code
     * @param string $message
     * @param array $data
     * @param bool $report
     * @return static
     */
    public static function getInstance($code = 200, $message = 'ok', $data = [], $report = true){
        return new static($code, $message, $data, $report);
    }

    /**
     * 获取返回值数据字段
     * @return array
     */
    public function getData(){
        return $this->{static::FIELD_DATA};
    }

    /**
     * 设置返回值
     * @param $data
     * @return mixed
     */
    public function setData($data){
        return $this->{static::FIELD_DATA} = $data;
    }
	
	/**
	 * 获取错误是否需要写入错误日志
	 * @return bool
	 */
    public function getReport(){
    	return $this->{static::FIELD_REPORT};
    }
    /**
     * 将实例映射成Response返回实例
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function toApiResponse(){
        return response(array(
            static::FIELD_CODE => $this->{static::FIELD_CODE},
            static::FIELD_MESSAGE => $this->{static::FIELD_MESSAGE},
            static::FIELD_TRACK => $this->{static::FIELD_TRACK},
            static::FIELD_DATA => $this->{static::FIELD_DATA},
	        static::FIELD_REPORT => $this->{static::FIELD_REPORT},
        ), $this->getHttpCode());
    }
	
	/**
	 * 返回合法的HTTP状态码
	 * @return int
	 */
	public function getHttpCode(){
    	$code = $this->{static::FIELD_CODE};
		if(is_numeric($code) && $code < 600) return $code;
		return 400; // 状态码不合法的话返回400
	}
}