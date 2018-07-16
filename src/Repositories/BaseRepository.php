<?php

namespace Laravelladder\Core\Repositories;

use Illuminate\Foundation\Auth\User;
use Laravelladder\Core\Collections\BaseCollection;
use Laravelladder\Core\Models\BaseModel;
use Laravelladder\Core\Validations\ValidationMixin;

/**
 * Class BaseRepository
 * 数据仓库基类
 * @package Laravelladder\Core\Repositories
 */
abstract class BaseRepository
{
	use ValidationMixin;
    /**
     * 获取Repo的一个实例
     *
     * @return static
     */
    public static function getInstance(){
        return new static();
    }

    /**
     * 获取Repo对应的数据模型
     *
     * @return BaseModel;
     */
    public static function getModel(){
	    return null;
    }

    /**
     * 获取Repo默认使用的Collection
     * @return BaseCollection
     */
    public static function getCollection(){
        return new BaseCollection();
    }
	
	/**
	 * 将array映射成仓库对应的数据模型实例
	 * @param array $array
	 * @param $modelName 如果cast成其他model，在这里写出
	 * @return BaseModel
	 */
    public static function castToModel($array, $modelName = null){
	    if(empty($array)) return null;
        if(is_object($array)) $array = (array) $array;
	    if(!is_array($array)) return null;
	    $model = $modelName ? new $modelName() : static::getModel();
        return $model->fill($array);
    }

    /**
     * 将列表映射成仓库对应的由数据模型组成的Collection实例
     * @param array $list
     * @return BaseCollection
     */
    public static function castListToCollection(array $list){
        $collection = static::getCollection();
        foreach ($list as $item){
            if(!is_array($item) && !is_object($item)) continue;
            $collection->push(static::castToModel($item));
        }
        return $collection;
    }
	
	/**
	 *  将列表映射成仓库对应的由数据模型组成的Collection实例
	 * @param array $list
	 * @param $total
	 * @param $limit
	 * @param $offest
	 * @return BaseCollection
	 */
	public static function castListToPaginatedCollection(array $list, $total, $limit, $offest){
		$collection = static::castListToCollection($list);
		$collection->setPagination($total, $limit, $offest, 1);
		return $collection;
	}
}
