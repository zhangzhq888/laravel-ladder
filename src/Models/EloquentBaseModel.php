<?php

namespace Laravelladder\Core\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Laravelladder\Core\Validations\Rule;

/**
 * Class EloquentBaseModel
 *
 * 关联数据库模型基类
 *
 * @package Laravelladder\Core\Models
 */
abstract class EloquentBaseModel extends BaseModel
{
    const TABLE_NAME = '';
    protected $table = self::TABLE_NAME;
	use SoftDeletes;
	
	// 此处用于存储创建新object时字段必须满足的规则, 规则从Rule中来
	public static $ruleStore = [
//		self::FIELD_ID => [
//			Rule::ID_VALUE_CUSTOM_NOT_EMPTY => true,
//			Rule::ID_VALIE_NUMERIC => true,
//			Rule::ID_VALUE_DIGITS => 10
//		],
	];
	// 此处用于存储更新object时字段必须满足的规则, 规则同上
	public static $ruleUpdate = [];
}
