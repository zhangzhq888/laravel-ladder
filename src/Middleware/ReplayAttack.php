<?php

namespace Laravelladder\Core\Middleware;

use Closure;
use \Illuminate\Http\Request;
use Laravelladder\Core\ServiceProviders\Log\Logger;
use Laravelladder\Core\Services\App;
use Laravelladder\Core\Services\RpcBaseService;
use Laravelladder\Core\Exceptions\BaseException;

class ReplayAttack
{
    private $replay_attack_key = "replay_attack_nonce:";
    private $replay_encypt_key = "Laravelladder2017";
    const MAX_ALLOWED_TIME = 3600;
	public function handle(Request $request, Closure $next)
	{
        $params = $request->server("HTTP_X_Laravelladder_KEY");
        $params = json_decode($params, true);
        if(json_last_error() != JSON_ERROR_NONE) throw new BaseException("header 传参错误");

		if(!isset($params['nonce'])) throw new BaseException("nonce 不能为空");
		if(!isset($params['timestamp'])) throw new BaseException("timestamp 不能为空");
        $this->replay_attack_key = $this->replay_attack_key . $params['nonce'];
        /**
         * 1秒内出现相同nonce视为有效请求
         * 防止同一页面请求的接口太多 导致nonce一样
         */
        if(static::MAX_ALLOWED_TIME - (int)\Redis::ttl($this->replay_attack_key) <= 1){
            return $next($request);
        }
		//验证nonce正确性
        if($params['nonce'] != md5($params['timestamp'] . $this->replay_encypt_key)) throw new BaseException("请求验证失败");
        //验证链接是否过期
        if(time() - substr($params['timestamp'],0,10) > static::MAX_ALLOWED_TIME) throw new BaseException("请求链接过期，请重新发起请求");

        //验证是否重复请求
        if(\Redis::exists ($this->replay_attack_key)) throw new BaseException("拒绝重复请求");
        //写入请求记录集合中
        \Redis::setex($this->replay_attack_key, static::MAX_ALLOWED_TIME, time());
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
