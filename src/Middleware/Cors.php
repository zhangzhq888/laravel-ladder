<?php

namespace Laravelladder\Core\Middleware;

use Closure;

class Cors {
	public static function getAllowOrigin(){
		return '*';
	}
	
	public static function getAllowHeaders(){
		return 'Content-Type, X-Auth-Token, X-Laravelladder-Key, Origin, Authorization';
	}
	
	public static function getAllowMethods(){
		return 'GET, POST, PUT, DELETE, OPTIONS';
	}
	
	public function handle($request, Closure $next)
	{
		$response = $next($request);
		if( // 只有这三个类有header
			$response instanceof \Illuminate\Http\Response ||
			$response instanceof \Illuminate\Http\RedirectResponse ||
			$response instanceof \Illuminate\Http\JsonResponse
		){
			$response
				->header('Access-Control-Allow-Origin', static::getAllowOrigin())
				->header('Access-Control-Allow-Methods', static::getAllowMethods());
		}
		// 记录可能的X-Laravelladder-KEY
		/**
		 * X-Laravelladder-Key:{"userKey":"1510492783974.0432","href":"https://www.Laravelladder.com/yinwu","timestamp":"1510710221237","nonce":"8c6ee7bb84a6175d2d8399f57d7258ed"}
		 */
		if($request instanceof \Illuminate\Http\Request){
			try{
				$LaravelladderKey = $request->headers->get('X-Laravelladder-Key', '{}');
				$LaravelladderKeyArray = json_decode($LaravelladderKey, true);
				if (isset($LaravelladderKeyArray["userKey"])) \Laravelladder\Core\Utils\Request\Helper::setXLaravelladderUserKey($LaravelladderKeyArray["userKey"]);
				if (isset($LaravelladderKeyArray["href"])) \Laravelladder\Core\Utils\Request\Helper::setXLaravelladderHref($LaravelladderKeyArray["href"]);
				if (isset($LaravelladderKeyArray["timestamp"])) \Laravelladder\Core\Utils\Request\Helper::setXLaravelladderTimestamp($LaravelladderKeyArray["timestamp"]);
				if (isset($LaravelladderKeyArray["nonce"])) \Laravelladder\Core\Utils\Request\Helper::setXLaravelladderNonce($LaravelladderKeyArray["nonce"]);
			} catch (\Exception $e){
				\Log::warning("获取X-Laravelladder-Key出错，原因: {$e->getMessage()}");
			}
		}
		
		// 打印传参
		\Log::debug("API请求传参为 " . json_encode($request->all()));
		return $response;
	}
}