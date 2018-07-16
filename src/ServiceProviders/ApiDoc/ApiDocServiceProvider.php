<?php
namespace Laravelladder\Core\ServiceProviders\ApiDoc;

use Jlapp\Swaggervel\SwaggervelServiceProvider;

class ApiDocServiceProvider extends SwaggervelServiceProvider {
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        require_once __DIR__ .'/routes.php';
    }

}
