<?php
namespace Laravelladder\Core\Utils\Request;

use \Redis;
use Laravelladder\Core\Utils\Time;
/**
 * Class Helper
 *
 * 请求数据相关的通用方法
 * @package Laravelladder\Core\Utils\Request
 */
class Helper {
    /**
     * 对Http请求生成唯一八位ID
     *
     * @return string
     */
    private static $requestId;
    
    public static function getRequestId(){
    	if(empty(static::$requestId)){
		    static::$requestId = sprintf("%08x", abs(crc32(rand(0,999999) . static::getRequestIp() . static::getRequestTime() . static::getRequestPort())));
	    }
        return static::$requestId;
    }

    /**
     * 获取Http请求方的IP
     *
     * @return string
     */
    public static function getRequestIp(){
	    $ipaddress = '';
	    if (isset($_SERVER['HTTP_CLIENT_IP']))
		    $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
		    $xForwardFor = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		    $ipaddress = trim($xForwardFor[0]);
	    }
	    else if(isset($_SERVER['HTTP_X_FORWARDED']))
		    $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	    else if(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
		    $ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
	    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
		    $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	    else if(isset($_SERVER['HTTP_FORWARDED']))
		    $ipaddress = $_SERVER['HTTP_FORWARDED'];
	    else if(isset($_SERVER['REMOTE_ADDR']))
		    $ipaddress = $_SERVER['REMOTE_ADDR'];
	    else
		    $ipaddress = 'UNKNOWN';
	    return $ipaddress;
    }
	
	/**
	 * 获取请求开始时间
	 * @return string
	 */
    public static function getRequestTime(){
	    if(!isset($_SERVER['REQUEST_TIME'])) return Time\Helper::getAppStartTime();
        return $_SERVER['REQUEST_TIME'];
    }
	
	/**
	 * 获取请求接口
	 * @return string
	 */
    public static function getRequestPort(){
    	if(!isset($_SERVER['REMOTE_PORT'])) return '80';
    	return $_SERVER['REMOTE_PORT'];
    }
	
	/**
	 * 获取请求路径
	 * @return string
	 */
    public static function getRequestUri(){
	    if(!isset($_SERVER['REQUEST_URI'])) return 'UNKNOWN_URI';
	    return $_SERVER['REQUEST_URI'];
    }
	
	/**
	 * 获取请求方法
	 * @return string
	 */
	public static function getRequestMethod(){
		if(!isset($_SERVER['REQUEST_METHOD'])) return 'UNKNOWN_METHOD';
		return $_SERVER['REQUEST_METHOD'];
	}
	
	/**
	 * 获取请求方式，host和路径
	 * @return string
	 */
	public static function getRequestRoute(){
		$method = static::getRequestMethod();
		$host = static::getRequestHost();
		$uri = static::getRequestUri();
		return "$method {$host}{$uri}";
	}
	/**
	 * 获取请求Host
	 * @return string
	 */
	public static function getRequestHost(){
		return isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'UNKNOWN_HOST';
	}
    /**
     * 通过IP限制某业务的访问次数
     * @param string $ip IP地址
     * @param string $type 业务类型（约定为业务的缩写，如'kb','zx','xzc'）
     * @param string $limitNum 限制条数,默认3次
     * @param string $expire 有效期格式（"2017-05-05 12：12：12"),默认当晚24点
     * @param string $warning 超过限制提示语
     * @return bool
     */
    public static function ipFilter(
        $ip,
        $type,
        $limitNum="3",
        $expire = "tonight",
        $warning="您今天的预约次数已达上限，请明日再试"
    ){
        if($expire == "tonight")
            $expire = strtotime(date('Y-m-d 23:59:59'));
        else
            $expire = strtotime($expire);
        $num = Redis::hGet($ip,$type);
        if($num >= $limitNum){
            throw \Laravelladder\Core\Exceptions\BaseException::getInstance($warning, 400, null,false);
        }else{
            Redis::hSet($ip,$type,$num+1);
            Redis::expireAt($ip,$expire);
        }
        return true;
    }
	
	
	protected static $xLaravelladderUserKey = null;
	protected static $xLaravelladderHref = null;
	protected static $xLaravelladderTimestamp = null;
	protected static $xLaravelladderNonce = null;
	const NOT_SET_EMPTY_VALUE = "未设置";
	
	public static function setXLaravelladderUserKey($xLaravelladderUserKey){
		static::$xLaravelladderUserKey = $xLaravelladderUserKey;
	}
	
	public static function setXLaravelladderHref($xLaravelladderHref){
		static::$xLaravelladderHref = $xLaravelladderHref;
	}
	
	public static function setXLaravelladderTimestamp($xLaravelladderTimestamp){
		static::$xLaravelladderTimestamp = $xLaravelladderTimestamp;
	}
	
	public static function setXLaravelladderNonce($xLaravelladderNonce){
		static::$xLaravelladderNonce = $xLaravelladderNonce;
	}
	
	public static function getXLaravelladderUserKey(){
		return !empty(static::$xLaravelladderUserKey) ? static::$xLaravelladderUserKey : static::NOT_SET_EMPTY_VALUE;
	}
	
	public static function getXLaravelladderHref(){
		return !empty(static::$xLaravelladderHref) ? static::$xLaravelladderHref : static::NOT_SET_EMPTY_VALUE;
	}
	
	public static function getXLaravelladderTimestamp(){
		return !empty(static::$xLaravelladderTimestamp) ? static::$xLaravelladderTimestamp : static::NOT_SET_EMPTY_VALUE;
	}
	
	public static function getXLaravelladderNonce(){
		return !empty(static::$xLaravelladderNonce) ? static::$xLaravelladderNonce : static::NOT_SET_EMPTY_VALUE;
	}
}