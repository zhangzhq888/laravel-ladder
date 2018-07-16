<?php

namespace Laravelladder\Core\Validations;

trait ValidationMixin
{

    /**
     * 验证表单
     * @param array $data
     * @param array $rule
     * @param array $message
     * @param array $customRules
     * @param bool $sanitize 是否要去掉Rule中没有的字段
     * @return Validator
     */
    public function validate(array &$data,
                             array $rule,
                             array
                             $message = array(),
                             array $customRules = array(),
                             $sanitize = false){
	    if($sanitize){
		    foreach ($data as $field => $value){
			    if(!isset($rule[$field])) {
				    \Log::debug(__METHOD__ . "字段 $field 在rules中不存在，删掉");
				    unset($data[$field]);
			    }
		    }
	    }
        return \Validator::make($data, Rule::makeValidationRules($rule), $message, $customRules);
    }
}
