<?php
namespace Laravelladder\Core\Utils\String;

/**
 * Class Helper
 * 哈希相关的通用方法
 * @package Laravelladder\Core\Utils\String
 */
class Hash
{
	public static function makePwd($password){
		return hash_hmac("sha256",$password, "&Up3rX-");
	}
	
	public static function matchPwd($encryptedPwd, $pwd){
		return hash_equals(static::makePwd($pwd), $encryptedPwd);
	}
}
