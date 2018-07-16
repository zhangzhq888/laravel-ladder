<?php

namespace Laravelladder\Core\Services;

/**
 * Class App
 * APP ID 列表
 * @package Laravelladder\Core\Services
 */
class App
{
	// 网关层
	const ID_VALUE_GOD = 1;
	const ID_VALUE_WEB = 2;
	const ID_VALUE_BROKER = 3;
	const ID_VALUE_USER = 4;
	const ID_VALUE_SUPPLIER = 5;
	const ID_VALUE_SAAS_SUPPLIER = 6;
	
	// 业务层
	const ID_VALUE_ZHUANGXIU = 101;
	const ID_VALUE_KAIBAN = 102;
	const ID_VALUE_FULI = 103;
	const ID_VALUE_SAAS_EC = 104;
	const ID_VALUE_JIFEN_MALL_LFYH = 150;
	const ID_VALUE_MALL_HXXF = 151;
	
	// 核心层
	const ID_VALUE_MEMBER = 1001;
	const ID_VALUE_OPT = 1002;
	const ID_VALUE_EC_GOODS = 1003;
	const ID_VALUE_NOTI = 1004;
	const ID_VALUE_GATEWAY = 1005;

	// 辅助业务
	const ID_VALUE_IMAGE = 2001;
	
	// 名称映射
	public static $nameMap = [
		self::ID_VALUE_GOD          => 'Laravelladder-god',
		self::ID_VALUE_WEB          => 'Laravelladder-web',
		self::ID_VALUE_BROKER       => 'Laravelladder-broker',
		self::ID_VALUE_USER         => 'Laravelladder-user',
		self::ID_VALUE_SUPPLIER     => 'Laravelladder-supplier',
		self::ID_VALUE_ZHUANGXIU    => 'Laravelladder-zhuangxiu',
		self::ID_VALUE_KAIBAN       => 'Laravelladder-kaiban',
		self::ID_VALUE_FULI         => 'Laravelladder-fuli',
		self::ID_VALUE_JIFEN_MALL_LFYH => 'Laravelladder-mall-flyh',
		self::ID_VALUE_MEMBER       => 'Laravelladder-member',
		self::ID_VALUE_OPT          => 'Laravelladder-opt',
		self::ID_VALUE_EC_GOODS     => 'Laravelladder-ec-goods',
		self::ID_VALUE_NOTI         => 'Laravelladder-noti',
		self::ID_VALUE_MALL_HXXF    => 'Laravelladder-mall-hxxf',
		self::ID_VALUE_IMAGE        => 'Laravelladder-image',
		self::ID_VALUE_GATEWAY      => 'Laravelladder-gateway',
		self::ID_VALUE_SAAS_EC      => 'Laravelladder-saas-ec',
		self::ID_VALUE_SAAS_SUPPLIER=> 'Laravelladder-saas-supplier',
	];
	
	// 内网端口
	public static $internalPortMap = [
		self::ID_VALUE_GOD          => 802,
		self::ID_VALUE_WEB          => 804,
		self::ID_VALUE_BROKER       => 443,
		self::ID_VALUE_USER         => 803,
		self::ID_VALUE_SUPPLIER     => 801,
		self::ID_VALUE_SAAS_SUPPLIER=> 443,
		self::ID_VALUE_GATEWAY      => 443,
		self::ID_VALUE_JIFEN_MALL_LFYH => 443,
		self::ID_VALUE_MALL_HXXF    => 443,
		
		self::ID_VALUE_ZHUANGXIU    => 8101,
		self::ID_VALUE_KAIBAN       => 8102,
		self::ID_VALUE_FULI         => 8103,
		self::ID_VALUE_SAAS_EC      => 8104,
		
		self::ID_VALUE_EC_GOODS     => 8503,
		self::ID_VALUE_NOTI         => 8504,
		self::ID_VALUE_IMAGE        => 8505,
		self::ID_VALUE_MEMBER       => 8002,
		self::ID_VALUE_OPT          => 8001,
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