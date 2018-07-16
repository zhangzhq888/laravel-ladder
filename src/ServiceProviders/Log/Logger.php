<?php

namespace Laravelladder\Core\ServiceProviders\Log;

use Laravelladder\Core\Services\App;
use Laravelladder\Core\Utils\Request\Helper as RequestHelper;
use Laravelladder\Core\Utils\Time\Helper as TimeHelper;
class Logger extends \Monolog\Logger
{
	protected static $startTime = null;
	protected static $parentRequestId = null;

    public static function getExecutionTime(){
	    if(is_null(static::$startTime)) static::$startTime = microtime(true);
	    $diff = microtime(true) - static::$startTime;
	    $sec = intval($diff);
	    $micro = $diff - $sec;
	    return round($micro * 1000, 4);
    }
    
    public static function getParentRequestId(){
	    return static::$parentRequestId ? static::$parentRequestId : '';
    }
    
    public static function setParentRequestId($id){
	    static::$parentRequestId = $id;
    }
    
    public static function getTrace(){
	    return empty(static::getParentRequestId()) ?
		    RequestHelper::getRequestId() : static::getParentRequestId() . "-" . RequestHelper::getRequestId();
    }
    
    
    public static function getRequestSource(){
    	return ( App::$requestAppId && isset(App::$nameMap[App::$requestAppId]) ) ?
		    App::$nameMap[App::$requestAppId] : RequestHelper::getRequestIp();
    }
    
    public function addRecord($level, $message, array $context = array()){
    	$appName = App::getRequestAppName();
	    $requestRoute = RequestHelper::getRequestRoute();
        $prepend = sprintf("[app:%s src:%s time:%s trace:%s url:%s href:%s]",
	        $appName,
	        static::getRequestSource(),
	        round(TimeHelper::getCurrentTimeSinceAppStart() * 1000),
	        static::getTrace(),
	        $level >= static::INFO ? $requestRoute : "N",
	        (
	        	$level >= static::INFO &&
		        RequestHelper::getXLaravelladderHref() != RequestHelper::NOT_SET_EMPTY_VALUE
	        ) ? RequestHelper::getXLaravelladderHref() : "N"
        );
        
        return parent::addRecord($level, $prepend . " " . $message, $context);
    }
}
