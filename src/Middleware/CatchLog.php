<?php

namespace Laravelladder\Core\Middleware;

use Closure;
use Laravelladder\Core\Services\App;
use Redis;
use Laravelladder\Core\Utils\Request\Helper;

class CatchLog
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        return $response;
    }

    public function terminate($request, $response)
    {
        $endDate       = microtime(true);                                       //回调时间
        try {
            $Redis = static ::RedisInit();
            $Redis->rpush('service_log_', json_encode([
                'serverName'    => $request->server('SERVER_ADDR'),             //主机名
                'serverVersion' => '1.0',                                       //项目版本号
                'ips'           => Helper::getRequestIp(),      //客户IP
                'reqTime'       => static::msectimeFormat($request->server('REQUEST_TIME_FLOAT')), //请求时间(yyyy-mm-dd hh:mm:ss.SSS)
                'reqHeader'     => $request->server('HTTP_USER_AGENT'),         //请求头
                'reqUrl'        => stripos($request->getRequestUri(),'?') ? substr($request->getRequestUri(), 0, stripos($request->getRequestUri(),'?')) : $request->getRequestUri(), //请求地址
                'reqParms'      => $request->getContent(),                      //请求参数
                'reqCookie'     => isset($_COOKIE['Laravelladder_cookie_id']) ? $_COOKIE['Laravelladder_cookie_id'] : '' ,               //COOKIE_ID
                'resCode'       => $response->getStatusCode(),                  //响应状态,
                'resTime'       => static::msectimeFormat($endDate),            //响应时间(yyyy-mm-dd hh:mm:ss.SSS)
                'resResult'     => $response->getContent(),                     //响应结果,
                'msMinus'       => round($endDate - $request->server('REQUEST_TIME_FLOAT'),2), //时间差（毫秒）
                'projectName'   => App::getRequestAppName(),                    //项目名称
                'beginTime'     => $request->server('REQUEST_TIME_FLOAT'),
            ]));
        } catch(\Exception $e) {
            \Log::error("推送日志失败{$e->getMessage()} {$e->getTraceAsString()}");
        }
    }
    //返回当前的毫秒时间格式
    public static function msectimeFormat($time)
    {
       $timeArr = explode('.' , $time);
        return  date('Y-m-d H:i:s', isset($timeArr[0]) ? $timeArr[0] : time()) .'.'. substr(isset($timeArr[1]) ? $timeArr[1] : '0000', 0, 3);
    }

    public static function RedisInit() {

        $db   = 0;
        $host = 'r-2zed940a9be09ff4.redis.rds.aliyuncs.com';
        $pwd  = 'Super1985';
        if (env('APP_ENV') == 'local') {
            $db   = 1;
            $host = 'r-2zeb119b822c0234.redis.rds.aliyuncs.com';
            $pwd  = 'Super1985';
        }
        if (env('APP_ENV') == 'staging') {
            $db   = 0;
            $host = 'r-2zeb119b822c0234.redis.rds.aliyuncs.com';
            $pwd  = 'Super1985';
        }
        $redis = new \Illuminate\Redis\Database(
            [
                'cluster' => false,
                'default' =>
                    [
                        'host' => $host,
                        'password' => $pwd,
                        'port' => 6379,
                        'database' => $db,
                    ],
            ]
        );
        return $redis;
    }
}
