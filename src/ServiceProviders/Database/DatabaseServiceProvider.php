<?php

namespace Laravelladder\Core\ServiceProviders\Database;


use Laravelladder\Core\ServiceProviders\Database\Connectors\ConnectionFactory;

class DatabaseServiceProvider extends \Illuminate\Database\DatabaseServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    	parent::register();
	    $this->app->singleton('db.factory', function ($app) {
		    return new ConnectionFactory($app);
	    });
    }
}
