<?php

namespace Laravelladder\Core\ServiceProviders\Cache;

use Illuminate\Support\Facades\Facade;

class GCache extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'gcache';
    }
}
