<?php

namespace Laravelladder\Core\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

/**
 * Class BaseHandler
 *
 * 异常处理通用类
 *
 * @package Laravelladder\Core\Exceptions
 */
class BaseHandler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
	    if ($this->shouldReport($e)) {
		    $this->log->error($e);
	    } else {
		    $this->log->warning($e);
	    }
    }
	
	/**
	 * @param Exception $e
	 * @return bool
	 */
	protected function shouldntReport(Exception $e)
	{
		if($e instanceof BaseException) return !$e->getReport();
		return parent::shouldntReport($e);
	}
	
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
	    // rpc, api 异常返回的Exception均为标准api输出
	    if(
	    	strpos($request->getUri(), '/api/') ||
		    strpos($request->getUri(), '/rpc/')
	    ){
		    // 如果是api请求则返回api错误
		    if($e instanceof BaseException){
			    return $e->toApiResponse();
		    } elseif(method_exists($e,'toApiResponse')){
			    return $e->toApiResponse();
		    }
	    }
	    // 普通http请求跳转到http错误页面
	    return parent::render($request, $e);
    }
}
