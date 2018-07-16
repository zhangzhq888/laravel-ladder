<?php

namespace Laravelladder\Core\Exceptions\Validations;

use Laravelladder\Core\Validations\Validator;

class ValidationException extends \Laravelladder\Core\Exceptions\BaseException{
	protected $code = 422;
	protected $message = '表单验证错误';
	protected $data = [];
	protected $report = false;
	
	public function __construct(Validator $validator){
		$this->data = $validator->messages()->toArray();
		// 合成数据
		$msg = [];
		foreach ($this->data as $field => $messages){
			$msg[] = "[" . $field . " " . implode(',', $messages) . "]";
		}
		parent::__construct(implode(";", $msg), $this->code, null);
	}
}