<?php

namespace Laravelladder\Core\Exceptions\Repositories;

/**
 * Class FilterHasArrayValueButIsEmptyException
 *
 * Endpoint 数据仓库过滤值中有数组但数组为空
 *
 * @package Laravelladder\Core\Exceptions\Repositories
 */
class FilterHasArrayValueButIsEmptyException extends \Laravelladder\Core\Exceptions\BaseException{
    protected $message = '数据仓库过滤值中有数组但数组为空';
}