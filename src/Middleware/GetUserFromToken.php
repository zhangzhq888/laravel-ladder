<?php

namespace Laravelladder\Core\Middleware;

use Laravelladder\Core\Exceptions\BaseException;
use Tymon\JWTAuth\Middleware\GetUserFromToken as BaseMiddleware;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class GetUserFromToken extends BaseMiddleware
{
    public function handle($request, \Closure $next)
    {
        if (! $token = $this->auth->setRequest($request)->getToken()) {
	        throw static::makeException('Token未提供');
        }
	    try {
		    $user = $this->auth->authenticate($token);
	    } catch (TokenExpiredException $e) {
        	throw static::makeException('Token过期');
	    } catch (JWTException $e) {
		    throw static::makeException('Token不合法');
	    }
	
	    if (! $user) {
		    throw static::makeException('无法通过Token获取用户', 401, true);
	    }
	    
	    $this->events->fire('tymon.jwt.valid', $user);
	
	    return $next($request);
    }
    
    protected static function makeException($message, $code = 401, $report = false){
        return (new BaseException($message, $code))->setReport($report);
    }
}
