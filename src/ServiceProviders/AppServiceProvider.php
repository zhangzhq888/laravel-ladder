<?php

namespace Laravelladder\Core\ServiceProviders;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
	    \Log::info("请求开始");
	    \App::terminating(function(){\Log::info("请求结束");});
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (strtoupper($this->app->environment()) != 'PRODUCTION')
        {
            if ( ! empty( $providers = config( 'app.dev_providers' ) ) )
            {
                foreach ( $providers as $provider )
                {
                    $this->app->register( $provider );
                }
            }

            if ( ! empty( $aliases = config( 'app.dev_aliases' ) ) )
            {
                foreach ( $aliases as $alias => $facade )
                {
                    $this->app->alias( $alias, $facade );
                }
            }
        }

    }
}
