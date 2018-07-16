<?php

namespace Laravelladder\Core\Repositories;

use Carbon\Carbon;
use Laravelladder\Core\Collections\BaseCollection;
use Laravelladder\Core\Exceptions\Repositories\FilterHasArrayValueButIsEmptyException;
use Laravelladder\Core\Exceptions\Repositories\ItemAlreadyDeletedException;
use Laravelladder\Core\Models\BaseModel;
use Laravelladder\Core\Models\EloquentBaseModel;
use Laravelladder\Core\Validations\Rule;
use Laravelladder\Core\Validations\Validator;

/**
 * Class EloquentBaseRepository
 *
 * 面向数据库的数据仓库
 * @package Laravelladder\Core\Repositories
 */
abstract class EloquentBaseRepository extends BaseRepository
{
	/*
	|--------------------------------------------------------------------------
	| 数据库，数据库方法类获取相关
	|--------------------------------------------------------------------------
	*/
	/**
	 * 获取数据库Query方法
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public static function getQuery(){
		return static::getModel()
			->query();
	}
	
	/**
	 * 获取Repo对应的数据模型
	 *
	 * @return EloquentBaseModel;
	 */
	public static function getModel(){
		return null;
	}
	
	/**
	 * 获取仓库对应的数据库表名
	 * @return string
	 */
	public static function getTableName(){
		return (static::getModel())::TABLE_NAME;
	}
	/*
	|--------------------------------------------------------------------------
	| 便捷方法相关
	|--------------------------------------------------------------------------
	*/
	/**
	 * 通过ID获取Model
	 * @param $id
	 * @return BaseModel | null
	 */
	public function getById($id){
		if(empty($id)) return null;
		$sql = "
select * from " . static::getTableName() . " 
where `" . (static::getModel())::FIELD_ID . "`=:id
LIMIT 1";
		$item = static::sqlSelectOne($sql, [
			'id' => $id
		]);
		return static::castToModel($item);
	}
	
	/**
	 * 通过ID数组获取多个Model组成的Collection
	 * @param array $ids
	 * @return BaseCollection
	 */
	public function getByIds(array $ids){
		if(count($ids) == 0) return static::getCollection();
		$sql = "
select * from " . static::getTableName() . " 
where `" . (static::getModel())::FIELD_ID . "` IN (:ids) AND :not_deleted";
		$item = static::sqlSelect($sql, [
			'ids' => $ids
		]);
		return static::castListToCollection($item);
	}
	
	/**
	 * 通过键值对获取一个
	 * @param $field
	 * @param $value
	 * @return BaseModel
	 */
	public function getOneByFieldValue($field, $value){
		$sql = "
select * from " . static::getTableName() . " 
where `$field`=:value AND :not_deleted
LIMIT 1";
		$item = static::sqlSelectOne($sql, [
			'value' => $value
		]);
		return static::castToModel($item);
	}
	
	/**
	 * 通过键值对获取一列
	 * @param $field
	 * @param $value
	 * @return BaseCollection
	 */
	public function getListByFieldValue($field, $value){
		$sql = "
select * from " . static::getTableName() . " 
where `$field`=:value  AND :not_deleted";
		$items = static::sqlSelect($sql, [
			'value' => $value
		]);
		return static::castListToCollection($items);
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
select :select_columns from " . static::getTableName() . "
where " . implode(" AND ", $keyValueQuery) . " AND :not_deleted
:order_by
:pagination";
		
		list($items, $total, $limit, $offset) = static::sqlSelectWithPagination($sql, $fieldValueMap, $columns, $limit, $offset, $orderBy);
		
		return static::castListToPaginatedCollection($items, $total, $limit, $offset);
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
		$filterQuery = static::sqlPrepareFilters(static::getTableName(),$filters);
		
		$sql = "SELECT :select_columns FROM " . static::getTableName() .
			" WHERE " . $filterQuery . ( $includeDeleted ? "" : " AND :not_deleted" ) .
			" :order_by " .
			" :pagination";
		
		list($items, $total, $limit, $offset) = static::sqlSelectWithPagination($sql, [], ['*'], $limit, $offset,$orderBy);
		return static::castListToPaginatedCollection($items, $total, $limit, $offset);
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
select * from " . static::getTableName() . " 
where `$field` IN (:value) AND :not_deleted";
		$items = static::sqlSelect($sql, [
			'value' => $value,
		]);
		return static::castListToCollection($items);
	}
	
	/**
	 * 通过ID更新
	 * @param $id
	 * @param array $values
	 * @return bool|int
	 */
	public function updateById($id, array $values){
		return static::getModel()
			->newQuery()
			->find($id)
			->update($values);
	}
	
	/**
	 * 通过字段更新
	 * @param array $fieldMap
	 * $fieldMap = [
	 *      'order_num' => 13412,
	 *      'status' => 12
	 * ]
	 * @param array $values
	 * @return int
	 */
	public function updateByFields(array $fieldMap, array $values){
		$query = static::getModel()
			->newQuery();
		foreach ($fieldMap as $field => $value){
			$query = $query->where($field, $value);
		}
		return $query->update($values);
	}
	
	/**
	 * 添加并获取model，数据库必须要有ID字段
	 * @param array $value
	 * @return BaseModel
	 */
	public function insertAndGetModel(array $value){
		$model = static::getModel()->fill($value);
		$model->save();
		return static::castToModel($model->toArray());
	}
	
	/**
	 * 批量添加并获取Collection
	 * @param array $values
	 * $values = [
	 *  [
	 *     key1: value1,
	 *     key2: value2,
	 *     key3: value3,
	 *  ],
	 *  [
	 *     key1: value1,
	 *     key2: value2,
	 *     key3: value3,
	 *  ],
	 * ]
	 * @return mixed
	 */
	public function batchInsetAndGetCollection(array $values){
		return \DB::transaction(function() use ($values){
			$col = new BaseCollection();
			foreach ($values as $value){
				$col = $col->push($this->insertAndGetModel($value));
			}
			return $col;
		});
	}
	/**
	 * 通过ID删除
	 * @param $id
	 * @return bool|mixed|null
	 * @throws ItemAlreadyDeletedException
	 */
	public function deleteById($id){
		$item = static::getModel()
			->newQuery()
			->find($id);
		if(is_null($item)) throw new ItemAlreadyDeletedException();
		return $item->delete();
	}
	/*
	|--------------------------------------------------------------------------
	| 验证相关
	|--------------------------------------------------------------------------
	*/
	
	/**
	 * 验证添加的数据是否符合规范, 可以在各自的Repo中重载, 不抛错就算成功
	 * 同时可选是否对输入进行清洗，去掉Rule中没有的字段
	 * @param $input
	 * @param bool $sanitize 是否要去掉Rule中没有的字段
	 * @return Validator
	 * @throws \Laravelladder\Core\Exceptions\Validations\ValidationException
	 */
	public function validateStore(&$input, $sanitize = false){
		$model = static::getModel();
		$validator = $this->validate($input, $model::$ruleStore, [], [], $sanitize);
		if($validator->customFails()) throw $validator->makeException();
		return $validator;
	}
	
	/**
	 * 批量验证添加的数据
	 * @param $inputs
	 * @param bool $sanitize
	 */
	public function validateBatchStore(&$inputs, $sanitize = false){
		foreach ($inputs as &$input){
			static::validateStore($input, $sanitize);
		}
	}
	
	/**
	 * 验证更新的数据是否符合规范, 可以在各自的Repo中重载, 不抛错就算成功
	 * 同时可选是否对输入进行清洗，去掉Rule中没有的字段
	 * @param $id
	 * @param $input
	 * @param bool $sanitize 是否要去掉Rule中没有的字段
	 * @return Validator
	 * @throws \Laravelladder\Core\Exceptions\Validations\ValidationException
	 */
	public function validateUpdate($id, &$input, $sanitize = false){
		$model = static::getModel();
		$validator = $this->validate($input, $model::$ruleUpdate, [], [], $sanitize);
		if($validator->customFails()) throw $validator->makeException();
		return $validator;
	}
	/**
	 * 验证是否可以删除数据, 可以在各自的Repo中重载, 不抛错就算成功
	 * @param $id
	 * @return bool
	 * @throws \Laravelladder\Core\Exceptions\Validations\ValidationException
	 */
	public function validateDestroy($id){
		return true;
	}
	
	/*
	|--------------------------------------------------------------------------
	| SQL 语句运行相关
	|--------------------------------------------------------------------------
	*/
	
	/**
	 * 选择一个
	 * @param $query
	 * @param array $bindings
	 * @return array | null
	 */
	public static function sqlSelectOne($query, $bindings = []){
		$query = static::sqlPrepareQuery($query, $bindings);
		return \DB::selectOne($query);
	}
	
	/**
	 * 选择一列
	 * @param $query
	 * @param array $bindings
	 * @return array
	 */
	public static function sqlSelect($query, $bindings = []){
		$query = static::sqlPrepareQuery($query, $bindings);
		return \DB::select($query);
	}
	
	/**
	 * 选择一列, 包含分页
	 * @param $query
	 * @param array $bindings
	 * @param array $columns
	 * @param int $limit
	 * @param int $offset
	 * @param array $orderBy = ['id'=> 'desc', 'created_at' => 'asc']
	 * @return array
	 */
	public static function sqlSelectWithPagination($query,
	                                               $bindings = [],
	                                               $columns = [],
	                                               $limit = 15,
	                                               $offset = 0,
	                                               array $orderBy = []){
		
		$selectColumnMark = 'select_columns';
		$paginationMark = 'pagination';
		$orderByMark = 'order_by';
		
		// 生成count语句
		$countQuery = $query;
		$countQuery = str_replace(":$selectColumnMark", ' count(*) ', $countQuery);
		$countQuery = str_replace(":$paginationMark", '', $countQuery);
		$countQuery = str_replace(":$orderByMark", '', $countQuery);
		$countQuery = static::sqlPrepareQuery($countQuery, $bindings);
		$result = \DB::selectOne($countQuery);
		$total = ((array) $result)['count(*)'];
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
		$selectQuery = static::sqlPrepareQuery($selectQuery, $bindings);
		$items = \DB::select($selectQuery);
		
		return [$items, $total, $limit, $offset];
	}
	
	/**
	 * 运行statement
	 * @param $query
	 * @param array $bindings
	 * @return int
	 */
	public static function sqlStatement($query, $bindings = []){
		$query = static::sqlPrepareQuery($query, $bindings);
		return \DB::connection()->getPdo()->exec($query);
	}
	
	/**
	 * 运行insert,获取id, 如果是0则添加失败，是非零则添加成功
	 * @param $query
	 * @param array $bindings
	 * @return int
	 */
	public static function sqlInsert($query, $bindings = []){
		$query = static::sqlPrepareQuery($query, $bindings);
		$builder = static::getQuery()->getQuery();
		$builder->getConnection()->insert($query);
		$id = $builder->getConnection()->getPdo()->lastInsertId(null);
		return is_numeric($id) ? (int) $id : $id;
	}
	
	/**
	 * 运行update,获取影响行数
	 * @param $query
	 * @param array $bindings
	 * @return int
	 */
	public static function sqlUpdate($query, $bindings = []){
		$query = static::sqlPrepareQuery($query, $bindings);
		$builder = static::getQuery()->getQuery();
		$num = $builder->getConnection()->update($query);
		return is_numeric($num) ? (int) $num : $num;
	}
	
	/**
	 * 运行删除,获取影响行数
	 * @param $query
	 * @param array $bindings
	 * @return int
	 */
	public static function sqlDelete($query, $bindings = []){
		$query = static::sqlPrepareQuery($query, $bindings);
		$builder = static::getQuery()->getQuery();
		$num = $builder->getConnection()->delete($query);
		return is_numeric($num) ? (int) $num : $num;
	}
	
	/**
	 * 准备query, 对binding的数组做implode处理
	 * @param $query
	 * @param array $bindings
	 * @return string
	 */
	public static function sqlPrepareQuery($query, $bindings = []){
		$keys = array_map('strlen', array_keys($bindings));
		array_multisort($keys, SORT_DESC, $bindings);
		foreach ($bindings as $key => $value){
			static::sqlValueProcess($value);
			$query = str_replace(":$key", $value, $query);
		}
		$query = str_replace(':not_deleted', " deleted_at IS NULL ", $query);
		\Log::debug("生成SQL脚本\n");
		\Log::debug("------------------------------------");
		$q = str_replace("\n", " ",$query);
		\Log::debug($q);
		\Log::debug("------------------------------------");
		return $query;
	}
	
	/**
	 * 准备过滤字段
	 * @param $tableName
	 * @param array $keyOperatorValues
	 * @return int|string
	 * @throws FilterHasArrayValueButIsEmptyException
	 */
	public static function sqlPrepareFilters($tableName, array $keyOperatorValues){
		if(empty($keyOperatorValues)) return 1;
		// 过滤项数组
		$allFilters = [];
		while(count($keyOperatorValues) > 0){
			// 依次获取过滤项
			$keyOperatorValue = array_shift($keyOperatorValues);
			if(static::checkValid2DFilterArrayWithOperator($keyOperatorValue)){
				// 二维数组处理
				$logicOpt = array_pop($keyOperatorValue);
				$queryToAdd = static::sqlPrepareFilters($tableName, $keyOperatorValue);
			} else {
				// 一维数组处理
				if(!isset($keyOperatorValue[3]) || !in_array(strtoupper($keyOperatorValue[3]), ['AND', 'OR'])) $keyOperatorValue[3] = "AND";
				list($key, $operator, $value, $logicOpt) = $keyOperatorValue;
				if (is_null($keyOperatorValue) || $keyOperatorValue === '') continue;
				// if (is_array($value) && count($value) == 0) throw new FilterHasArrayValueButIsEmptyException();
				$needBrace = is_array($value);
				static::sqlValueProcess($value);
				$value = $needBrace ? " ( $value ) " : $value;
				$queryToAdd = ( $needBrace && $value == " (  ) " ) ?
					( (strpos(strtoupper($operator), 'NOT') !== false ) ? 1 : 0) :
					( (empty($tableName) || ( strpos($key, '.') !== false )) ? "$key $operator $value" : "$tableName.$key $operator $value" );
			}
			$logicOpt = count($keyOperatorValues) > 0 ? $logicOpt : '';
			$allFilters[] = " ( $queryToAdd ) $logicOpt";
		}
		$filterQuery = implode("", $allFilters);
		return ($filterQuery == "") ? 1 : $filterQuery;
	}
	
	/**
	 * 确认传参是否是以下格式
	 * [
	 *      [field, =, value, 'and'],
	 *      [field, =, value, 'amd'],
	 *      'AND'
	 * ]
	 *
	 * @param $filters
	 * @return bool
	 */
	public static function checkValid2DFilterArrayWithOperator(&$filters){
		if(static::isValid2DFilterArray($filters)){
			$filters[] = 'AND';
			return true;
		} else {
			$opt = array_pop($filters);
			if(static::isValid2DFilterArray($filters) && in_array(strtoupper($opt), ['AND', 'OR'])){
				array_push($filters, $opt);
				return true;
			} else {
				array_push($filters, $opt);
				return false;
			}
		}
	}
	
	
	/**
	 * 判断是否是合理的2D 数组 filter
	 * @param $filters
	 * @return bool
	 */
	public static function isValid2DFilterArray($filters){
		if(!is_array($filters)) return false;
		foreach ($filters as $filter){
			if(!is_array($filter) || !in_array(count($filter),  [3,4])) return false;
		}
		return true;
	}
	
	public static function sqlValueProcess(&$value){
		if(is_array($value)){
			$value = array_unique($value);
			sql_safe($value);
			foreach ($value as $k => $v){
				if($v == '') $value[$k] = "\"\"";
				if(is_null($v)) unset($value[$k]);
			}
			$value = implode(',', $value);
		} elseif($value === ""){
			$value = "''";
		} elseif (is_null($value)){
			$value = " IS NULL ";
		} else {
			sql_safe($value);
		}
	}
}
