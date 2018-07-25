<?php

namespace Laravelladder\Core\Services;

/**
 * Class App
 * APP ID 列表
 * @package Laravelladder\Core\Services
 */
class App
{
    // 网关层 80
    const ID_VALUE_HI_BLOG = 1;

    // 业务层 90

    // 核心层 50

    // 辅助业务 200


    // 名称映射
    public static $nameMap = [
        self::ID_VALUE_HI_BLOG          => 'hi_blog',
    ];

    // 内网端口
    public static $internalPortMap = [
        self::ID_VALUE_HI_BLOG          => 801,
    ];

    public static $requestAppId = null;

    /**
     * @param $id
     */
    public static function setRequestAppId($id){
        static::$requestAppId = $id;
    }

    public static function getRequestAppId(){
        return static::$requestAppId ? static::$requestAppId : \Config::get('app.id');
    }

    /**
     * @return mixed|string
     */
    public static function getRequestAppName(){
        $appId = static::getRequestAppId();
        return isset(static::$nameMap[$appId]) ? static::$nameMap[$appId] : 'unknown';
    }
}