<?php

namespace Laravelladder\Core\Repositories;

use Laravelladder\Core\Collections\BaseCollection;
use Laravelladder\Core\Exceptions\BaseException;
use Laravelladder\Core\Models\UniModel;

/**
 * 全局可查的数据仓库
 * Class UniRepository
 * @package Laravelladder\Core\Repositories
 */
class UniRepository {
	/**
	 * @param $query
	 * @param array $bindings
	 * @return BaseCollection
	 */
	public function sqlSelectList($query, $bindings = []){
		$query = EloquentBaseRepository::sqlPrepareQuery($query, $bindings);
		$list = \DB::select($query);
		return self::castListToCollection($list);
	}
	
	
	/**
	 * @param $query
	 * @param array $bindings
	 * @param array $columns
	 * @param int $limit
	 * @param int $offset
	 * @param array $orderBy
	 * @return BaseCollection
	 */
	public function sqlSelectListWithPagination($query,
	                                            $bindings = [],
	                                            $columns = [],
	                                            $limit = 15,
	                                            $offset = 0,
	                                            array $orderBy = []
	){
		$selectColumnMark = 'select_columns';
		$paginationMark = 'pagination';
		$orderByMark = 'order_by';
		// 生成count语句
		$countQuery = $query;
		$countQuery = str_replace(":$selectColumnMark", ' count(*) ', $countQuery);
		$countQuery = str_replace(":$paginationMark", '', $countQuery);
		$countQuery = str_replace(":$orderByMark", '', $countQuery);
		$countQuery = EloquentBaseRepository::sqlPrepareQuery($countQuery, $bindings);
		$result = \DB::selectOne($countQuery);
		$total = ((array) $result)['count(*)'];
		if($total>0){
			// 生成查询语句
			$selectQuery = $query;
			$selectQuery = str_replace(":$selectColumnMark", " " . implode(',', $columns) . " ", $selectQuery);
			$limit = ( $limit < 0 ) ? $total - $offset : $limit; // 如果limit是-1，则设为最大
			$selectQuery = str_replace(":$paginationMark", " LIMIT $limit OFFSET $offset ", $selectQuery);
			// 生成排序语句, 支持多维排序
			if(!empty($orderBy)){
				$orderByArray = [];
				foreach ($orderBy as $o => $d){
					$orderByArray[] = "$o $d";
				}
				$orderByQuery = " ORDER BY " . implode(', ', $orderByArray);
				$selectQuery = str_replace(":$orderByMark", $orderByQuery, $selectQuery);
			} else {
				$selectQuery = str_replace(":$orderByMark", "", $selectQuery);
			}
			$selectQuery = EloquentBaseRepository::sqlPrepareQuery($selectQuery, $bindings);
			$items = \DB::select($selectQuery);
		}else{
			$items=[];
		}
		
		return self::castListToPaginatedCollection($items, $total, $limit, $offset);
	}
	
	/**
	 * @param $query
	 * @param array $bindings
	 * @return UniModel
	 */
	public function sqlSelectOne($query, $bindings = []){
		$query = EloquentBaseRepository::sqlPrepareQuery($query, $bindings);
		$item =  \DB::selectOne($query);
		return static::castToModel((array)$item);
	}
	
	/**
	 * 通过ID获取Model
	 * @param $id
	 * @return BaseModel | null
	 */
	public function getById($id){
		if(empty($id)) return null;
		$sql = "
select * from " . $this->getTableName() . "
where `" . UniModel::FIELD_ID . "`=:id
LIMIT 1";
		return $this->sqlSelectOne($sql, [
			'id' => $id
		]);
	}
	
	/**
	 * 通过ID数组获取多个Model组成的Collection
	 * @param array $ids
	 * @return BaseCollection
	 */
	public function getByIds(array $ids){
		if(count($ids) == 0) return new BaseCollection();
		$sql = "
select * from " . $this->getTableName() . "
where `" . UniModel::FIELD_ID . "` IN (:ids) AND :not_deleted";
		return $this->sqlSelectList($sql, [
			'ids' => $ids
		]);
	}
	
	/**
	 * 通过键值对获取一个
	 * @param $field
	 * @param $value
	 * @return UniModel
	 */
	public function getOneByFieldValue($field, $value){
		$sql = "
select * from " . $this->getTableName() . "
where `$field`=:value AND :not_deleted
LIMIT 1";
		return $this->sqlSelectOne($sql, [
			'value' => $value
		]);
	}
	
	/**
	 * 通过键值对获取一列
	 * @param $field
	 * @param $value
	 * @return BaseCollection
	 */
	public function getListByFieldValue($field, $value){
		$sql = "
select * from " . $this->getTableName() . "
where `$field`=:value  AND :not_deleted";
		return $this->sqlSelectList($sql, [
			'value' => $value
		]);
	}
	
	/**
	 * 通过多个键值对获取一列
	 * @param array $fieldValueMap
	 * @param array $columns
	 * @param int $limit
	 * @param int $offset
	 * @param array $orderBy = ['id'=> 'desc', 'created_at' => 'asc']
	 * @return BaseCollection
	 */
	public function getListByFields(array $fieldValueMap,
	                                array $columns = ['*'],
	                                $limit = 15,
	                                $offset = 0,
	                                array $orderBy = []){
		if(empty($fieldValueMap)) return new BaseCollection();
		$keyValueQuery = [];
		foreach ($fieldValueMap as $field => $value){
			// 如果IN中为空值则返回空
			if(is_array($value) && count($value) == 0) return new BaseCollection();
			$keyValueQuery[] = is_array($value) ? "$field IN (:$field)" : "$field = :$field";
		}
		$sql = "
select :select_columns from " . $this->getTableName() . "
where " . implode(" AND ", $keyValueQuery) . " AND :not_deleted
:order_by
:pagination";
		
		return $this->sqlSelectListWithPagination($sql, $fieldValueMap, $columns, $limit, $offset, $orderBy);
	}
	
	
	/**
	 * 通过过滤条件获取列表，支持排序，分页
	 * @param array $filters = [
	 *      ['created_at', '>', '2017-04-04 00:00:00', 'OR'], //过滤条件之间是AND方法，
	 *      [
	 *          ['status', '=', 1], // 数组中的是OR方法
	 *          ['type', '=', 5]
	 *          'AND'
	 *      ],
	 *      ['updated_at', '>', '2017-04-04 00:00:00', 'OR']
	 * ]
	 *
	 * @param int $limit
	 * @param int $offset
	 * @param array $orderBy = ['created_at' => 'desc', 'id' => 'asc'] // 支持多个排序条件
	 * @param bool $includeDeleted 是否包含已删除的数据
	 * @return BaseCollection
	 */
	public function getListByFilters(array $filters,
	                                 $limit = 15,
	                                 $offset = 0,
	                                 array $orderBy = [],
	                                 $includeDeleted = false
	){
		$filterQuery = EloquentBaseRepository::sqlPrepareFilters($this->getTableName(),$filters);
		
		$sql = "SELECT :select_columns FROM " . $this->getTableName() .
			" WHERE " . $filterQuery . ( $includeDeleted ? "" : " AND :not_deleted" ) .
			" :order_by " .
			" :pagination";
		
		return $this->sqlSelectListWithPagination($sql, [], ['*'], $limit, $offset,$orderBy);
	}
	
	/**
	 * 通过键值对(单键多个值)获取一列
	 * @param $field
	 * @param array $value
	 * @return BaseCollection
	 */
	public function getListByFieldValues($field, array $value){
		if(count($value) == 0) return new BaseCollection();
		$sql = "
select * from " . $this->getTableName() . "
where `$field` IN (:value) AND :not_deleted";
		return $this->sqlSelectList($sql, [
			'value' => $value,
		]);
	}
	
	protected $table;
	
	/**
	 * 设置表名
	 * @param $tableName
	 * @return $this
	 */
	public function setTable($tableName){
		$this->table = $tableName;
		return $this;
	}
	
	
	public function getTableName(){
		if(empty($this->table)) throw new BaseException("请设置表明");
		return $this->table;
	}

	/**
	 * @param array $item
	 * @return UniModel
	 */
	public static function castToModel(array $item){
		$m = new UniModel();
		if($item){
			$m->fillable(array_keys($item));
			$m->fill($item);
		}
		return $m;
	}
	
	/**
	 * @param array $list
	 * @return BaseCollection
	 */
	private static function castListToCollection(array $list){
		$collection = new BaseCollection();
		foreach ($list as $item){
			if(!is_array($item) && !is_object($item)) continue;
			$m = static::castToModel((array)$item);
			$collection->push($m);
		}
		return $collection;
	}
	
	/**
	 * @param array $list
	 * @param $total
	 * @param $limit
	 * @param $offest
	 * @return BaseCollection
	 */
	private static function castListToPaginatedCollection(array $list, $total, $limit, $offest){
		$collection = self::castListToCollection($list);
		$collection->setPagination($total, $limit, $offest, 1);
		return $collection;
	}
}
