<?php
namespace Laravelladder\Core\Jobs;

use Laravelladder\Core\Endpoints\BaseEndpoint;

/**
 * 异步运行Endpoint方法, 需要对应项目打开队列
 * Class AsyncEndpointJob
 * @package Laravelladder\Core\Jobs
 */
class AsyncEndpointJob extends BaseJob
{
    private $endpointName = '';
    private $endpointArguments;

    /**
     * @param $endpointName 查找数据的Endpoint名
     * @param array $endpointArguments Endpoint传参
     */
    public function __construct( $endpointName,
                                 array $endpointArguments
    ){
        $this->endpointName = $endpointName;
        $this->endpointArguments = $endpointArguments;
    }

    public function handle()
    {
    	try{
    		$endpoint = new $this->endpointName();
    		/* @var BaseEndpoint $endpoint */
		    $endpoint
			    ->setArguments($this->endpointArguments)
			    ->dryRun();
	    } catch (\Exception $e){
    		\Log::error("运行Endpoint出错，原因 {$e->getMessage()}");
	    }
    }
}
