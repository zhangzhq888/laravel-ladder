<?php

namespace Laravelladder\Core\Middleware;

use Closure;
use \Illuminate\Http\Request;
use Laravelladder\Core\ServiceProviders\Log\Logger;
use Laravelladder\Core\Services\App;
use Laravelladder\Core\Services\RpcBaseService;


class RpcFilter
{
	public function handle(Request $request, Closure $next)
	{
		$params = $this->getHeaderParams($request->headers->all());
		// 获取传参并设为trace
		if(isset($params['trace'])) Logger::setParentRequestId($params['trace']);
		if(isset($params['appid'])) App::setRequestAppId($params['appid']);
		return $next($request);
	}
	
	protected function getHeaderParams(array $headers){
		$packageEnv = [];
		foreach ($headers as $key => $value) {
			if (strpos($key, strtolower(RpcBaseService::HEADER_PREFIX)) === 0) {
				// 将大写ENV变量去掉PREFIX并换成小写
				$envKey = strtolower(substr($key, strlen(RpcBaseService::HEADER_PREFIX)));
				$packageEnv[$envKey] = is_array($value) ? array_shift($value) : $value;
			}
		}
		return $packageEnv;
	}
}
