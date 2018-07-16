<?php

namespace Laravelladder\Core\Exceptions;
use Laravelladder\Core\Models\BaseResponse;
use Laravelladder\Core\Utils\Request\Helper;

/**
 * Class Exception
 *
 * 异常基类
 *
 * @package Laravelladder\Core\Exceptions
 */
class BaseException extends \Exception{
    protected $code = 400;
    protected $message = '';
    protected $data = [];
    protected $report = true; // 是否要打错误日志

    public function __construct($message = '', $code = 400, Exception $previous = null)
    {
        $message = $this->message . ( $message ? ' ' . $message : '');
        parent::__construct($message, $code, $previous);
    }
	
	/**
	 * 生成实例
	 * @param string $message
	 * @param int $code
	 * @param Exception|null $previous
	 * @param bool $report
	 * @return static
	 */
    public static function getInstance($message = '', $code = 400, Exception $previous = null, $report = true){
    	$exception = new static($message, $code, $previous);
    	return $exception->setReport($report);
    }
    
    /**
     * 获取data字段值
     * @return array
     */
    public function getData(){
        return $this->data;
    }
	
	/**
	 * 设置data字段值
	 * @param $data
	 * @return $this
	 */
    public function setData($data){
	    $this->data = $data;
	    return $this;
    }
	
	/**
	 * 设置code字段值
	 * @param $code
	 * @return $this
	 */
	public function setCode($code){
		$this->code = $code;
		return $this;
	}
	
	/**
	 * @param $report
	 * @return $this
	 */
    public function setReport($report){
    	$this->report = $report;
    	return $this;
    }
	/**
	 * 错误是否应报告
	 * @return bool
	 */
    public function getReport(){
    	return $this->report;
    }
    
    /**
     * 将异常输出成API异常返回值
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function toApiResponse(){
	    if($this->code == 500){ // 如果是500, 则采用统一话术
		    $this->message = "系统错误, 请联系客服";
	    }
        return BaseResponse::getInstance($this->code, $this->message, $this->data, $this->report)
            ->toApiResponse();
    }
}