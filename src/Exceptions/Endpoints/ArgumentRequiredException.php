<?php

namespace Laravelladder\Core\Exceptions\Endpoints;

/**
 * Class Exception
 *
 * Endpoint 缺少必填参数
 *
 * @package Laravelladder\Core\Exceptions\Endpoints
 */
class ArgumentRequiredException extends \Laravelladder\Core\Exceptions\BaseException{
    protected $message = '缺少必填参数';
}