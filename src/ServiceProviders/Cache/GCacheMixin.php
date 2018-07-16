<?php

namespace Laravelladder\Core\ServiceProviders\Cache;

/**
 * Class GCacheMixin
 * 全局缓存服务基类
 * @package Laravelladder\Core\Services
 */
trait GCacheMixin
{
	/**
	 * 获取缓存的prefix
	 * @return mixed
	 */
    abstract public static function getCachePrefix();
	
	/**
	 * 获取缓存过期时间,分钟,如果是null则为永久
	 * @return int
	 */
	abstract public static function getCacheExpiry();
    
	/**
	 * 生成缓存Key
	 * @param $params
	 * @return string
	 */
    protected static function getCacheKey($params){
	    if(is_array($params)) $params = md5(json_encode($params));
	    return sprintf("%s_%s",
		    static::getCachePrefix(),
		    $params);
    }

	/**
	 * 保存object到缓存
	 * @param $params
	 * @param $value
	 */
    protected static function saveToCache($params, $value){
    	if(is_numeric(static::getCacheExpiry())){
		    return \GCache::put(
			    static::getCacheKey($params),
			    $value,
			    static::getCacheExpiry());
	    } else {
		    // 如果没有过期时间就是永久
		    return \GCache::forever(
			    static::getCacheKey($params),
			    $value);
	    }
    }
	
	/**
	 * 忘记cache
	 * @param $params
	 * @return mixed
	 */
    protected static function forgetCache($params){
    	return \GCache::forget(static::getCacheKey($params));
    }
	/**
	 * 从缓存获取object
	 * @param $params
	 * @return mixed
	 */
	protected static function getFromCache($params){
		return \GCache::get(static::getCacheKey($params));
	}

}
