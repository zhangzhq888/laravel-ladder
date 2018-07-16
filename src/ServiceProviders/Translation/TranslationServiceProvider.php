<?php

namespace Laravelladder\Core\ServiceProviders\Translation;

use Illuminate\Translation\FileLoader;
use Illuminate\Translation\TranslationServiceProvider as ServiceProvider;

class TranslationServiceProvider extends ServiceProvider
{
	
    /**
     * Register the translation line loader.
     *
     * @return void
     */
    protected function registerLoader()
    {
        $this->app->singleton('translation.loader', function ($app) {
            return new FileLoader($app['files'], __DIR__ . "/lang");
        });
    }
}
