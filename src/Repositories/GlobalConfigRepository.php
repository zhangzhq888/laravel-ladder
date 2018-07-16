<?php

namespace Laravelladder\Core\Repositories;

use Carbon\Carbon;
use Laravelladder\Core\Collections\BaseCollection;
use Laravelladder\Core\Exceptions\Repositories\FilterHasArrayValueButIsEmptyException;
use Laravelladder\Core\Exceptions\Repositories\ItemAlreadyDeletedException;
use Laravelladder\Core\Models\BaseModel;
use Laravelladder\Core\Models\EloquentBaseModel;
use Laravelladder\Core\Models\GlobalConfig;
use Laravelladder\Core\ServiceProviders\Cache\GlobalCacheKey;
use Laravelladder\Core\ServiceProviders\Cache\GCacheMixin;
use Laravelladder\Core\Validations\Rule;
use Laravelladder\Core\Validations\Validator;

/**
 * Class GlobalConfigRepository
 *
 * 全局配置数据仓库
 * @package Laravelladder\Core\Repositories
 */
class GlobalConfigRepository extends EloquentBaseRepository
{
	use GCacheMixin;
	/**
	 * 获取Repo对应的数据模型
	 * @return GlobalConfig;
	 */
	public static function getModel()
	{
		return GlobalConfig::getInstance();
	}
	
	/**
	 * 获取缓存的prefix
	 * @return mixed
	 */
	public static function getCachePrefix(){
		return GlobalCacheKey::KEY_GLOBAL_CONFIG_PREFIX;
	}
	
	/**
	 * 获取缓存过期时间,分钟
	 * @return int
	 */
	public static function getCacheExpiry(){
		return null;
	}
	
	/**
	 * 获取key
	 * @param $key
	 * @return mixed|string
	 */
	public function getValueByKey($key){
		$cacheValue = static::getFromCache($key);
		if($cacheValue) return $cacheValue;
		\Log::debug(__METHOD__ . "未缓存中未找到 $key ，查库");
		$item = static::getOneByFieldValue(GlobalConfig::DB_FIELD_KEY, $key);
		$value = ( !($item instanceof GlobalConfig)) ? '' : $item->{GlobalConfig::DB_FIELD_VALUE};
		\Log::debug(__METHOD__ . "将key对应的值 $key 存入数据库 ");
		static::saveToCache($key, $value);
		return $value;
	}
	
	/**
	 * 更新值
	 * @param $key
	 * @param $value
	 * @return int
	 */
	public function updateValueByKey($key, $value)
	{
		$item = static::getOneByFieldValue(GlobalConfig::DB_FIELD_KEY, $key);
		if(!($item instanceof GlobalConfig)){
			$model = static::insertAndGetModel([
				GlobalConfig::DB_FIELD_KEY => $key,
				GlobalConfig::DB_FIELD_VALUE => $value
			]);
			$result = $model->getKey();
		} else {
			$result = static::getModel()
				->newQuery()
				->where(GlobalConfig::DB_FIELD_KEY, $key)
				->update([
					GlobalConfig::DB_FIELD_VALUE => $value
				]);
		}
		
		static::saveToCache($key, $value);
		return $result;
	}
}
