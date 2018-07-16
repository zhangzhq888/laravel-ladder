<?php

namespace Laravelladder\Core\Exceptions\Services;

/**
 * Class Exception
 *
 * Endpoint Rpc请求错误
 *
 * @package Laravelladder\Core\Exceptions\Services
 */
class RpcRequestException extends \Laravelladder\Core\Exceptions\BaseException{
    protected $message = 'RPC请求错误';
}