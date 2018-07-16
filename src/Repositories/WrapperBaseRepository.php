<?php

namespace Laravelladder\Core\Repositories;

use Laravelladder\Core\Collections\BaseCollection;
use Laravelladder\Core\Models\BaseResponse;

/**
 * Class WrapperBaseRepository
 * 数据仓库基类, 数据直接存在代码中
 * @package Laravelladder\Core\Repositories
 */
abstract class WrapperBaseRepository extends BaseRepository
{
	/**
	 * 获取所有写死的数据
	 * @return array
	 */
    public static function getWrapperData(){
	    return [];
    }
	
	/**
	 * 通过主键获取Model
	 * @param $id
	 * @return \Illuminate\Database\Eloquent\Model
	 */
    public function getById($id){
	    return $this
		    ->getAll()
		    ->find($id);
    }
	
	/**
	 * 获取所有数据
	 * @return BaseCollection
	 */
    public function getAll(){
	    $items = static::getWrapperData();
	    $collection = static::getCollection();
	    foreach($items as $item){
		    $collection = $collection->push(static::getModel()->fill($item));
	    }
	    return $collection;
    }
	
	/**
	 * 根据监制对获取一列
	 * @param $field
	 * @param $value
	 * @return static
	 */
	public function getListByFieldValue($field, $value){
		return $this
			->getAll()
			->where($field, $value, false);
	}
}
