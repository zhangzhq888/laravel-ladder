<?php

namespace Laravelladder\Core\ServiceProviders;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Laravelladder\Core\Middleware\Cors;
use Laravelladder\Core\Utils\Request\Helper as RequestHelper;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'Controllers';

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function map(Router $router)
    {
	    // 为了检测系统是否正常添加心跳检测
	    $router->get('/api/hatestheartbeat', function(){
		    return time();
	    });
        $this->mapWebRoutes($router);
	    $this->mapApiRoutes($router);
        $this->mapRpcRoutes($router);
    }
	
    protected function mapWebRoutes(Router $router)
    {
        if(!file_exists(app_path('Http/Routes/web.php'))) return;
        $router->group([
            'namespace' => $this->namespace,
	        'middleware' => 'web',
        ], function ($router) {
            require app_path('Http/Routes/web.php');
        });
    }
	
	protected function mapApiRoutes(Router $router)
	{
		if(!file_exists(app_path('Http/Routes/api.php'))) return;
		// 对于api，支持optiosn
		if (
			isset($_SERVER['REQUEST_METHOD']) &&
			$_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			header('Access-Control-Allow-Origin: ' . Cors::getAllowOrigin());
			header('Access-Control-Allow-Methods: ' . Cors::getAllowMethods());
			header('Access-Control-Allow-Headers: ' . Cors::getAllowHeaders());
			echo 'ok';die();
		}
		$router->group([
			'namespace' => $this->namespace,
			'middleware' => ['web','cors','api'],
			'prefix' => '/api'
		], function ($router) {
			require app_path('Http/Routes/api.php');
		});
	}
	
    protected function mapRpcRoutes(Router $router)
    {
	    if(!file_exists(app_path('Http/Routes/rpc.php'))) return;
	    $router->group([
		    'namespace' => $this->namespace,
		    'middleware' => 'rpc',
		    'prefix' => '/rpc'
	    ], function ($router) {
		    require app_path('Http/Routes/rpc.php');
	    });
    }
}
