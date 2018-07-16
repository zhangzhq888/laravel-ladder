<?php

namespace Laravelladder\Core\ServiceProviders\Log;

use Illuminate\Support\ServiceProvider;
use Illuminate\Log\Writer;
use Illuminate\Contracts\Foundation\Application;
use Laravelladder\Core\Services\App;

class LogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    public static $folderPath = "/opt/logs/";
	
	/**
	 * 获取日志文件路径
	 * @param $folderPath
	 * @return string
	 */
    public static function getLogPath($folderPath){
    	return $folderPath . App::getRequestAppName() . ".log";
    }
	
	public static function getErrorLogPath($folderPath){
		return $folderPath . App::getRequestAppName() . ".error";
	}
	
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app;
        /* @var Application $app */
        $logger = new Writer(new Logger($app->environment()), $app['events']);

        // Daily files are better for production stuff
	    $path = static::getLogPath(static::$folderPath);
	    $logger->useFiles($path);
	    $errorPath = static::getErrorLogPath(static::$folderPath);
	    $logger->useFiles($errorPath, 'error');
	    
        $app->instance('log', $logger);

        // Next we will bind the a Closure to resolve the PSR logger implementation
        // as this will grant us the ability to be interoperable with many other
        // libraries which are able to utilize the PSR standardized interface.
        $app->bind('Psr\Log\LoggerInterface', function (Application $app) {
            return $app['log']->getMonolog();
        });

        $app->bind('Illuminate\Contracts\Logging\Log', function (Application $app) {
            return $app['log'];
        });
    }
}
