<?php

namespace Laravelladder\Core\Exceptions\Models;

/**
 * Class Exception
 *
 * Endpoint 请求返回数据格式非法
 *
 * @package Laravelladder\Core\Exceptions\Models
 */
class ResponseDataFormatInvalidException extends \Laravelladder\Core\Exceptions\BaseException{
    protected $message = '请求返回数据格式非法';
}