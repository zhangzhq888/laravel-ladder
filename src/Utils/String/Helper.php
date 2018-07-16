<?php

namespace Laravelladder\Core\Utils\String;
/**
 * Class Helper
 *
 * 字符串相关的通用方法
 * @package Laravelladder\Core\Utils\String
 */
class Helper
{
    /**
     * 生成随机的由数字，大小写字母组成的字符串
     * @param int $length
     * @return string
     */
    public static function generateRandomString($length = 10) {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }

    /**
     * 将下划线(或其他符号分隔)字符串换成驼峰形式
     * @param $input 输入字符串
     * @param string $separator 分隔符
     * @return mixed
     */
    public static function camelize($input, $separator = '_'){
        return str_replace($separator, '', lcfirst(ucwords($input)));
    }

    /**
     * 将阿拉伯数字转换成中文数
     * @param $num
     * @param bool $mode
     * @return mixed
     */
    public static function numberToChinese($num,$mode=true){
        $char = array("零","壹","贰","叁","肆","伍","陆","柒","捌","玖");
        $dw = array("","拾","佰","仟","","萬","億","兆");
        $retval = "";

        if($mode)
            preg_match_all("/^0*(\d*)\.?(\d*)/",$num, $ar);
        else
            preg_match_all("/(\d*)\.?(\d*)/",$num, $ar);
        if($ar[2][0]!= ""){
            $decimal = $ar[2][0];
            $jiao = substr($decimal,0,1) ? $char[substr($decimal,0,1)] . "角" : "";
            $fen = substr($decimal,1,1) ? $char[substr($decimal,1,1)] . "分" : "";
            $retval = $jiao . $fen;
        } else{
            $retval = "整";
        }

        if($ar[1][0] != "") {
            $str = strrev($ar[1][0]);
            for($i=1;$i<strlen($str);$i++) {
                $out[$i] = $char[$str[$i]];
                if($mode) {
                    $out[$i] .= $str[$i] != "0"? $dw[$i%4] : "";
                    if($str[$i]+$str[$i-1] == 0)
                        $out[$i] = "";
                    if($i%4 == 0)
                        $out[$i] .= $dw[4+floor($i/4)];
                }
            }

            //如果最后两位都是$dw里面的，则去掉最后一个
            $retval = join("",array_reverse($out)) . "圆" . $retval;
        }
        return str_replace("億萬","億",$retval);
    }
    
    public static function stringContains($string, $niddle){
        return strpos($string, $niddle) !== false;
    }

    /**
     * @param $data
     */
    public static function ensureUtf8(&$data){
        if (!is_string($data)) return;
        $encode_array = array('ASCII', 'UTF-8', 'GBK', 'GB2312', 'BIG5');
        $encoded = mb_detect_encoding($data, $encode_array);
        if ($encoded && $encoded != 'UTF-8') {
            $data = mb_convert_encoding($data, 'UTF-8', $encoded);
        }
    }
	
	/**
	 * 保证传进来的是
	 * @param $data 需要保证是数字的变量
	 * @return int
	 */
	public static function ensureNumber(&$data){
		if(is_object($data)) {
			return 0;
		}elseif(is_array($data)){
			foreach ($data as $key => $d){
				if(is_array($d)) {
					static::ensureNumber($data[$key]);
					continue;
				}
				$str = preg_replace('/\D/', '', $d);
				$data[$key] = $str;
			}
		} else {
			$data = preg_replace('/\D/', '', $data);
		}
		
	}
	/**
	 * 验证是不是合法的用户名
	 * @param $loginName
	 * @return bool
	 */
    public static function validateLoginName($loginName){
	    if(
	    	empty($loginName) ||
		    !preg_match('/^[a-z0-9_]{6,16}$/', $loginName) ||
		    !preg_match('/^.*(?=.{6,16})(?=.*\d)(?=.*[a-z]).*$/', $loginName)
	    ) return false;
	    return true;
    }
	
	/**
	 * 验证是不是合法的手机号
	 * @param $phone
	 * @return bool
	 */
    public static function validatePhone($phone){
	    if(
	    	empty($phone) ||
		    !preg_match('/^1\d{10}$/', $phone)
	    ) return false;
	    return true;
    }

    /**
     * 计数器
     * @param $prefix
     * @return bool|int
     */
    public static function getCounter($prefix,$ttl = 86400)
    {
        $config_prefix = \Config::get("cache.prefix");
        $key = $config_prefix . "_$prefix";
        $counter = \Redis::setnx($key, 1);
        \Redis::expire($key,$ttl);
        if (!$counter)  $counter = \Redis::incr($key);
        return $counter;
    }
    
}
