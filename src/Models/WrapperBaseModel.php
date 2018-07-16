<?php

namespace Laravelladder\Core\Models;

/**
 * Class WrapperBaseModel
 *
 * 写死的数据模型基类
 *
 * @package Laravelladder\Core\Models
 */
abstract class WrapperBaseModel extends BaseModel
{
	// 所有数据均可填入
	protected static $unguarded = true;
}
