<?php

namespace Laravelladder\Core\Models;

/**
 * Class EmptyModel
 *
 * 数据模型基类
 *
 * @package Laravelladder\Core\Models
 */
/**
 * @SWG\Definition(@SWG\Xml(name="EmptyModel"))
 */
class EmptyModel extends BaseModel
{
	
	/**
	 * @SWG\Property(
	 *     property="id",
	 *     type="int",
	 *     default=1,
	 * )
	 */
    const FIELD_ID = 'id';
	/**
	 * @SWG\Property(
	 *     property="result",
	 *     type="string",
	 *     default="ok",
	 * )
	 */
	const FIELD_RESULT = 'result';
	
	protected $fillable = [
		self::FIELD_ID,
		self::FIELD_RESULT
	];
}
