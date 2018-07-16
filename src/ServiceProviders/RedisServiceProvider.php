<?php

namespace Laravelladder\Core\ServiceProviders;

use Illuminate\Redis\RedisServiceProvider as ServiceProvider;
use Illuminate\Redis\Database;

class RedisServiceProvider extends ServiceProvider
{
    protected $defer = true;
	
	public static $redisConfig = [
		'local' => [
			'cluster' => false,
			'default' => [
				'host' => 'r-2zeb119b822c0234.redis.rds.aliyuncs.com',
				'password' => 'Super1985',
				'port' => 6379,
				'database' => 1,
			],
		],
		'staging' => [
			'cluster' => false,
			'default' => [
				'host' => 'r-2zeb119b822c0234.redis.rds.aliyuncs.com',
				'password' => 'Super1985',
				'port' => 6379,
				'database' => 0,
			],
		],
		'production' => [
			'cluster' => false,
			'default' => [
				'host' => 'r-2ze4bf02c141f1c4.redis.rds.aliyuncs.com',
				'password' => 'Super1985',
				'port' => 6379,
				'database' => 0,
			],
		]
	];
    public function register()
    {
	    $env = $this->app->environment();
		$config = isset(static::$redisConfig[$env]) ?
			static::$redisConfig[$env] :
			static::$redisConfig['local'];
	    
	    $this->app->singleton('redis', function ($app) use ($config){
            return new Database($config);
        });
    }
    
    public function getRedis(){
	    $env = $this->app->environment();
	    $config = isset(static::$redisConfig[$env]) ?
		    static::$redisConfig[$env] :
		    static::$redisConfig['local'];
    }
}
