<?php

namespace Laravelladder\Core\ServiceProviders\Cache;

use Illuminate\Cache\CacheServiceProvider as ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
	public function register()
	{
		// 全局缓存
		$this->app->singleton('gcache', function ($app) {
			return new GlobalCacheManager($app);
		});
		parent::register();
	}
	
	public function provides()
	{
		return array_merge(parent::provides(), ['gcache']);
	}
}
