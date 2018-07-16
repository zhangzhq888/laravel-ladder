<?php

namespace Laravelladder\Core\Middleware;

use Closure;
use \Illuminate\Http\Request;
use Laravelladder\Core\Exceptions\BaseException;
use Laravelladder\Core\ServiceProviders\Log\Logger;
use Laravelladder\Core\Services\App;
use Laravelladder\Core\Services\RpcBaseService;
use Laravelladder\Core\Utils\Request\Helper;


class BlockIP
{
	public function getBlockIps(){
		return [
			
		];
	}
	public function handle(Request $request, Closure $next)
	{
		if(in_array(Helper::getRequestIp(), $this->getBlockIps())) throw (new BaseException('当前IP被封禁, 如有疑问请联系客服', 403))->setReport(false);
		return $next($request);
	}
}
