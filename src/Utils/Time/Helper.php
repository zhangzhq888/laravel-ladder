<?php
namespace Laravelladder\Core\Utils\Time;

use Carbon\Carbon;

/**
 * Class Helper
 * 时间相关的通用方法
 * @package Laravelladder\Core\Utils\Time
 */
class Helper
{
    /**
     * 获取当前时间
     * @param $format 格式
     * @return string
     */
    public static function getNow($format){
        return date($format);
    }

    /**
     * 获取当前时间,返回Y-m-d H:i:s 格式
     * @return string
     */
    public static function getNowMYSQL(){
        return static::getNow('Y-m-d H:i:s');
    }
	
	/**
	 * 获取当前日期,返回YYYYMMDD 格式
	 * @return string
	 */
	public static function getNowYYYYMMDD(){
		return static::getNow('Ymd');
	}
	
	/**
	 * 获取20位微秒时间
	 * @return string
	 */
	public static function getMicroTimeInYYYYMMDDHHMMSSmmmmmm(){
		list($micro, $second) = explode(' ',microtime());
		
		$time = Carbon::createFromTimestampUTC($second);
		$dateTime = $time->format('YmdHis');
		return ($dateTime . (int)round($micro * 1000000,0));
	}
	
	/**
	 * 获取当前时间与Laravel启动时间的间隔，以秒记
	 * @return mixed
	 */
	public static function getCurrentTimeSinceAppStart(){
		return microtime(true) - static::getAppStartTime();
	}
	
	/**
	 * 获取Laravel启动时间，以秒记
	 * @return mixed
	 */
	public static function getAppStartTime(){
		if(!defined('LARAVEL_START')) return microtime(true);
		return LARAVEL_START;
	}
}
