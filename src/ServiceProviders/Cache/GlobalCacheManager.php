<?php

namespace Laravelladder\Core\ServiceProviders\Cache;

use Illuminate\Cache\CacheManager as Manager;
class GlobalCacheManager extends Manager
{
	protected function getPrefix(array $config)
	{
		return 'Laravelladder_global_';
	}
}
