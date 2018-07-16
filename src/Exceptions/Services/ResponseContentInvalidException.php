<?php

namespace Laravelladder\Core\Exceptions\Services;

/**
 * Class ResponseStatusInvalidException
 *
 * Endpoint 请求返回数据内容非法
 *
 * @package Laravelladder\Core\Exceptions\Services
 */
class ResponseContentInvalidException extends \Laravelladder\Core\Exceptions\BaseException{
    protected $message = '请求返回值错误';
}