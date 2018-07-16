<?php

namespace Laravelladder\Core\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Laravelladder\Core\Endpoints\BaseEndpoint;
use Laravelladder\Core\Exceptions\BaseException;

/**
 * Class BaseModel
 *
 * 数据模型基类
 *
 * @package Laravelladder\Core\Models
 */
abstract class BaseModel extends Model
{
    const TABLE_NAME = '';
    protected $table = self::TABLE_NAME;

    const FIELD_ID = 'id';


    // 字段所属类型
    const ATTRIBUTE_CAST_TYPE_INTEGER = 'integer';
    const ATTRIBUTE_CAST_TYPE_REAL = 'real';
    const ATTRIBUTE_CAST_TYPE_FLOAT = 'float';
    const ATTRIBUTE_CAST_TYPE_DOUBLE = 'double';
    const ATTRIBUTE_CAST_TYPE_STRING = 'string';
    const ATTRIBUTE_CAST_TYPE_BOOLEAN = 'boolean';
    const ATTRIBUTE_CAST_TYPE_OBJECT = 'object';
    const ATTRIBUTE_CAST_TYPE_COLLECTION = 'collection';
    const ATTRIBUTE_CAST_TYPE_DATE = 'date';
    const ATTRIBUTE_CAST_TYPE_DATETIME = 'datetime';
    const ATTRIBUTE_CAST_TYPE_ARRAY = 'array';

    /**
     * 将model中所有可赋值的字段以key value的方式返回
     *
     * @return array
     */
    public function toApiArray(){
        return $this->toArrayWithFields($this->getFillable());
    }

    /**
     * 传入希望获取的字段并获取model中字段对应的值
     *
     * @param array $fields 所有要从model中提取的字段名toArray
     * @return array
     */
	public function toArrayWithFields(array $fields = array()){
		$array = [];
		foreach ($fields as $field){
			$array[$field] = isset($this->{$field}) ?
				static::handleSpecialFieldForApiResponse($this->{$field}) :
				null;
		}
		return $array;
	}
	
	/**
	 * 添加字段并存值
	 * @param $key
	 * @param null $value 如果已经有值的填上
	 * @return $this
	 * @throws BaseException
	 */
	public function addFillable($key,
	                            $value = null
	){
		if(empty($key)) throw new BaseException("键不能为空");
		if(!in_array($key, $this->fillable)){
			$this->fillable[] = $key;
		}
		if(!is_null($value)) $this->setAttribute($key, $value);
		return $this;
	}
	
	public function getFieldAsString($fieldName){
		$value = $this->{$fieldName};
		if(is_string($value)) return $value;
		if($value instanceof Carbon) return $value->toDateTimeString();
	}
	/**
	 * 将$value打成API友好的返回
	 * @param $value
	 * @return string
	 */
	public static function handleSpecialFieldForApiResponse($value){
		if($value instanceof Carbon) return $value->toDateTimeString();
		return $value;
	}

    /**
     * 获取一个model实例
     *
     * @param array $data 用于初始化model的值
     * @return static
     */
    public static function getInstance(array $data = array()){
        return new static($data);
    }

    /**
     * 确定字段是否为给定类型
     *
     * @param $key 字段名
     * @param $cast 给定类型名
     * @return bool
     */
    public function matchCastType($key, $cast){
        return isset($this->casts[$key]) && $this->casts[$key] == $cast;
    }

    /**
     * (依情况重载) 将给定值映射为目标类型
     *
     * @param string $key 目标映射类型
     * @param mixed $value 值
     * @return mixed
     */
    protected function castAttribute($key, $value)
    {
        if (is_null($value)) {
            return $value;
        }
        switch ($this->getCastType($key)) {
            default:
                return parent::castAttribute($key, $value);
        }
    }
	
	/**
	 * 将Model替换成另一个Model
	 * @param $modelName
	 * @return BaseModel
	 */
	public function copyToModel($modelName){
		$model = new $modelName();
		/* @var BaseModel $model */
		return $model->fill($this->toArray());
	}
	
	/*
	|--------------------------------------------------------------------------
	| Api返回相关
	|--------------------------------------------------------------------------
	*/
	public function toEndpointResponse(callable $callback){
		$result = $callback($this);
		return BaseEndpoint::makeResultResponse($result);
	}
}
