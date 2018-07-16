<?php

namespace Laravelladder\Core\Utils\Lock;

use Laravelladder\Core\Exceptions\BaseException;

/**
 * Class Helper
 *
 * 字符串相关的通用方法
 * @package Laravelladder\Core\Utils\String
 */
class Helper
{
	/**
	 * 获得锁
	 * @param $key 项目唯一key
	 * @param float $maxWaitTimeInSeconds 最长等待锁的时间, 每 10/1,000,000 秒尝试获取一次
	 * @param int $expiryInSeconds 如果释放放的话，锁的最长有效时间
	 * @return bool 是否成功获取锁
	 * @throws BaseException
	 */
    public static function getLock($key, $maxWaitTimeInSeconds = 0.1, $expiryInSeconds = 3600) {
	    $config_prefix = \Config::get("cache.prefix");
	    $hashKey = $config_prefix . "_$key";
	    $success = \Redis::setnx($hashKey, 1);
	    if ($success)  {
	    	\Redis::expire($hashKey, $expiryInSeconds);
	    	return true;
	    }
	    if($maxWaitTimeInSeconds < 0) {
		    \Log::error("获取锁失败, key 为 $hashKey");
		    return false;
	    }
	    $waitTimeInMillSeconds = 100;
	    $waitTimeInSeconds = $waitTimeInMillSeconds / 1000000;
	    usleep($waitTimeInMillSeconds);
	    return Helper::getLock($key,$maxWaitTimeInSeconds - $waitTimeInSeconds, $expiryInSeconds);
    }
	
	/**
	 * 释放锁
	 * @param $key
	 * @return int
	 */
    public static function releaseLock($key){
	    $config_prefix = \Config::get("cache.prefix");
	    $hashKey = $config_prefix . "_$key";
	    return \Redis::del($hashKey);
    }
}
