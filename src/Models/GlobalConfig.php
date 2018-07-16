<?php

namespace Laravelladder\Core\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Laravelladder\Core\Validations\Rule;

/**
 * 全局配置模型
 * Class GlobalConfig
 * @package Laravelladder\Core\Models
 */
class GlobalConfig extends EloquentBaseModel
{
    const TABLE_NAME = 'global_configs';
    protected $table = self::TABLE_NAME;
	use SoftDeletes;
	
	const DB_FIELD_ID = 'id';
	
	const DB_FIELD_KEY = 'key';
	const KEY_PHONE_WHITE_LIST = 'phone_white_list'; // 测试账号白名单
	const KEY_BANGONG_TAGS_RECOMMEND = 'bangong_tags_recommend'; // 办公一级分类推荐
	const KEY_MAKKET_TAGS_RECOMMEND  = 'market_tags_recommend'; // 超人商城一级分类推荐
    const KEY_IAP_NAVBAR_RECOMMEND   = 'iap_navbar_recommend'; // 内购系统一级导航推荐
    const KEY_IAP_NAVBAR_GOODS_RECOMMEND   = 'iap_navbar_goods_recommend'; // 内购系统楼层推荐商品
    const KEY_IAP_NAVBAR_GOODS  = 'iap_navbar_goods'; // 内购系统导航关联商品
	const KEY_TOP_BROKER_LIST = 'top_broker_list'; // 总部销售电话名单


    const DB_FIELD_VALUE = 'value';
	const DB_FIELD_CREATED_AT = 'created_at';
	const DB_FIELD_UPDATED_AT = 'updated_at';
	const DB_FIELD_DELETED_AT = 'deleted_at';
	
	protected $fillable = [
		self::DB_FIELD_KEY,
		self::DB_FIELD_VALUE
	];
	
	public static $ruleStore = [
		self::FIELD_ID => [
			Rule::ID_VALUE_REQUIRED => true,
		],
		self::DB_FIELD_VALUE => [
			Rule::ID_VALUE_REQUIRED => true,
		],
	];

	public static $ruleUpdate = [
		self::DB_FIELD_VALUE => [
			Rule::ID_VALUE_REQUIRED => true
		]
	];
}
