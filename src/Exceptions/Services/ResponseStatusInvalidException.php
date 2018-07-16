<?php

namespace Laravelladder\Core\Exceptions\Services;

/**
 * Class ResponseStatusInvalidException
 *
 * Endpoint 请求返回数据格式非法
 *
 * @package Laravelladder\Core\Exceptions\Services
 */
class ResponseStatusInvalidException extends \Laravelladder\Core\Exceptions\BaseException{
    protected $message = '';
}