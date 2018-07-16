<?php

namespace Laravelladder\Core\Services;

/**
 * Class CacheMixin
 * RPC服务基类
 * @package Laravelladder\Core\Services
 */
trait CacheMixin
{
	/**
	 * 获取环境名称
	 * @return string
	 */
	public static function getEnvName(){
		return \Config::get('app.env');
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
	    return static::getAppId() . '_' .str_replace('/', '_', get_class(new static()));
    }
	
	/**
	 * 获取缓存过期时间
	 * @return int
	 */
    protected static function getCacheExpiry(){
	    return 10;
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

}
