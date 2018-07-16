<?php

namespace Laravelladder\Core\Exceptions\Repositories;

/**
 * Class ItemAlreadyDeletedException
 *
 * Endpoint 要删除的数据不存在
 *
 * @package Laravelladder\Core\Exceptions\Repositories
 */
class ItemAlreadyDeletedException extends \Laravelladder\Core\Exceptions\BaseException{
    protected $message = '要删除的数据不存在';
}