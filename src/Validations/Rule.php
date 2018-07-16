<?php

namespace Laravelladder\Core\Validations;

class Rule {

    const ID_VALUE_REQUIRED = 'required';
    const ID_VALUE_DIGIT_BETWEEN = 'digits_between';
    const ID_VALUE_DIGITS = 'digits';
    const ID_VALUE_UNIQUE = 'unique';
    const ID_VALUE_ALPHA = 'alpha';
    const ID_VALUE_ALPHA_DASH = 'alpha_dash';
    const ID_VALUE_INTEGER = 'integer';
    const ID_VALUE_MIN = 'min';
    const ID_VALUE_MAX = 'max';
    const ID_VALUE_DATE_FORMAT = 'date_format';
    const ID_VALUE_AFTER = 'after';
    const ID_VALUE_REGEX = 'regex';
    const ID_VALUE_EMAIL = 'email';
    const ID_VALUE_NUMERIC = 'numeric';
    const ID_VALUE_IN = 'in';
    const ID_VALUE_ARRAY = 'array';
	const ID_VALUE_STRING = 'string';
    const ID_VALUE_CUSTOM_NOT_EMPTY = 'not_empty';
    const ID_VALUE_CUSTOM_DASH_DATE = 'dash_date';

    const ID_VALUE_CUSTOM_CONTAIN_UPPER_LOWER = 'contain_upper_lower';
    const ID_VALUE_CUSTOM_CONTAIN_NUMBER = 'contain_number';
    const ID_VALUE_CUSTOM_CONTAIN_SPECIAL_CHARACTER = 'contain_special_character';
    const ID_VALUE_CUSTOM_CONTAIN_TEXT = 'contain_text';
	const ID_VALUE_CUSTOM_CONTAIN_CHINESE = 'contain_chinese';
	const ID_VALUE_CUSTOM_CHINESE_ONLY = 'chinese_only';
	const ID_VALUE_CUSTOM_NUMERIC_DASH = 'numeric_dash';
	
    public static function makeValidationRules($rules){
        $r = array();
        if(!is_array($rules)) return $r;
        foreach($rules as $key => $rule){
            if(!is_array($rule)) continue;
            $validationRules = static::makeValidationRule($rule);
            if(count($validationRules) > 0){
                $r[$key] = $validationRules;
            }
        }
        return $r;
    }

    public static function makeValidationRule($rule){
        if(!is_array($rule)) return array();
        $validationRules = [];
        foreach($rule as $key => $value){
            $string = is_bool($value) ?  $key : ($key . ":" . $value);
            $validationRules[] = $string;
        }
        return implode('|', $validationRules);
    }


    public static function registerCustomValidator(){
        \Validator::extend(static::ID_VALUE_CUSTOM_DASH_DATE, function($attribute, $value, $parameters, Validator $validator) {
            if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$value)) {
                return true;
            }else{
                return false;
            }
        }, '日期字段需要符合格式 YYYY-MM-DD');

        \Validator::extendImplicit(static::ID_VALUE_CUSTOM_NOT_EMPTY, function($attribute, $value, $parameters, Validator $validator) {
        	// 只验证为空的情况，不处理字符串0和整型0的情况
            if(!array_key_exists($attribute, $validator->getData())) return true;
            if(is_string($value) && $value == "0") return true;
            if(is_int($value) && $value == 0) return true;
            return !empty($value);
        }, '字段不能为空');

        // 密码相关
        \Validator::extend(static::ID_VALUE_CUSTOM_CONTAIN_UPPER_LOWER, function($attribute, $value, $parameters, Validator $validator) {
            preg_match("/^(?=.*[a-z])(?=.*[A-Z]).+$/", $value, $output_array);
            return count($output_array) > 0;
        }, '字段需要包含大小写字母');
        \Validator::extend(static::ID_VALUE_CUSTOM_CONTAIN_NUMBER, function($attribute, $value, $parameters, Validator $validator) {
            preg_match("/^(?=.*\d).+$/", $value, $output_array);
            return count($output_array) > 0;
        }, '字段需要有数字');
        \Validator::extend(static::ID_VALUE_CUSTOM_CONTAIN_SPECIAL_CHARACTER, function($attribute, $value, $parameters, Validator $validator) {
            preg_match("/^(?=.*[-+_!@#$%^&*.,?]).+$/", $value, $output_array); // ^(?=.*[_\W]).+$
            return count($output_array) > 0;
        },'字段需要有特殊支付');
        \Validator::extend(static::ID_VALUE_CUSTOM_CONTAIN_TEXT, function($attribute, $value, $parameters, Validator $validator) {
            preg_match("/^(?=.*[A-Za-z]).+$/", $value, $output_array);
            return count($output_array) > 0;
        }, '字段需要有字符');
	    \Validator::extend(static::ID_VALUE_CUSTOM_CONTAIN_CHINESE, function($attribute, $value, $parameters, Validator $validator) {
		    preg_match("/[\x7f-\xff]+/", $value, $output_array);
		    return count($output_array) > 0;
	    }, '字段需要包含中文');
	    \Validator::extend(static::ID_VALUE_CUSTOM_CHINESE_ONLY, function($attribute, $value, $parameters, Validator $validator) {
		    preg_match("/^[\x7f-\xff]+$/", $value, $output_array);
		    return count($output_array) > 0;
	    }, '字段只能包含中文');
	    \Validator::extend(static::ID_VALUE_CUSTOM_NUMERIC_DASH, function($attribute, $value, $parameters, Validator $validator) {
		    preg_match("/^[0-9 \-]+$/", $value, $output_array);
		    return count($output_array) > 0;
	    }, "字段只能包含数字和短横线'-'");
    }
}