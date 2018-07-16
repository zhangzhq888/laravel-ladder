<?php

namespace Laravelladder\Core\Services;

use Laravelladder\Core\Repositories\BaseRepository;

/**
 * Class BaseService
 * 服务基类
 * @package Laravelladder\Core\Services
 */
abstract class BaseService extends BaseRepository
{
	/**
	 * 获得$_SERVER前值, 便于service从.env文件中获取数据， 需要在各个Service的SDK中重载
	 * @return string
	 */
	public static function envPrefix(){
		return '';
	}
	
	/**
	 * 获取.env文件中前值等于envPrefix的变量，并转换成lowercase返回
	 * 如envPrefix为"OPT_SDK"，env文件中有名为 OPT_SDK_URI=localhost OPT_SDK_PORT=8001 的配置
	 * 则返回值为:
	 * [
	 *    "url" => "localhost"
	 *    "port" => "8001"
	 * ]
	 * @return array
	 */
	public static function getConfigFromEnv(){
		$allEnv = $_SERVER;
		$packageEnv = [];
		foreach ($allEnv as $key => $value) {
			if (strpos($key, static::envPrefix()) === 0) {
				// 将大写ENV变量去掉PREFIX并换成小写
				$envKey = strtolower(substr($key, strlen(static::envPrefix()) + 1));
				$packageEnv[$envKey] = $value;
			}
		}
		return $packageEnv;
	}
	/**
	 * 获得service所在的目录
	 * @return string
	 */
	public static function getDirPath(){
		$reflector = new \ReflectionClass(static::getClassName());
		return dirname($reflector->getFileName());
	}
	
	/**
	 * 获取类名
	 * @return string
	 */
	public static function getClassName(){
		return get_class(new static());
	}
	
	/**
	 * 获得配置文件目录的相对位置
	 * @return string
	 */
	public static function getConfigRelativePath(){
		return "/../config/";
	}
	
	/**
	 * 根据当前环境不同获得默认配置信息，并与当前.env 文件中的配置合并
	 * @return mixed
	 */
	public static function getConfig(){
		$path = static::getDirPath() . static::getConfigRelativePath();
		switch (static::getEnvName()){
			case 'production':
				$config = require $path . 'production.php';
				break;
			case 'staging':
				$config = require $path . 'staging.php';
				break;
			default:
				$config = require $path . 'local.php';
		}
		$config = array_merge($config, static::getConfigFromEnv());
		return $config;
	}
	
	/**
	 * 获取环境名称
	 * @return string
	 */
	public static function getEnvName(){
		return \Config::get('app.env');
	}
}
