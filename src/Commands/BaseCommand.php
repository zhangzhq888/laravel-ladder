<?php

namespace Laravelladder\Core\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Laravelladder\Core\ServiceProviders\Log\LogServiceProvider;
use Laravelladder\Core\Utils\Time\Helper as TimeHelper;
use Laravelladder\Core\Utils\Request\Helper as RequestHelper;
use Laravelladder\Core\Services\App;

abstract class BaseCommand extends Command
{
	protected static $logPath = '/opt/clogs/';
	
	public function line($string, $style = null, $verbosity = null)
	{
		$env = strtolower(\Config::get('app.env'));
		$upperStyle = strtoupper(empty($style) ? 'debug' : $style);
		$now = Carbon::now()->toDateTimeString();
		$executeTime = round(TimeHelper::getCurrentTimeSinceAppStart() * 1000);
		$trackId = RequestHelper::getRequestId();
		$ip = RequestHelper::getRequestIp();
		$url = $this->signature;
		$appId = \Config::get('app.id');
		$appName = App::$nameMap[$appId];
		$string = "[{$now}] {$env}.{$upperStyle}: [app:$appName src:$ip time:$executeTime trace:$trackId url:$url href:N] " . $string;
		parent::line($string, $style, $verbosity);
		static::lineToFile($string, $style);
	}
	
	public static function getLogPath(){
		return LogServiceProvider::getLogPath(static::$logPath);
	}
	
	public static function getErrorLogPath(){
		return LogServiceProvider::getErrorLogPath(static::$logPath);
	}
	
	//
	public static function lineToFile($string, $style = null){
		// 打印正常日志
		static::printToFile(static::getLogPath(), $string);
		// 打印错误日志
		if($style == 'error') static::printToFile(static::getErrorLogPath(), $string);
	}
	
	public static function printToFile($path, $string){
		try{
//			file_put_contents($path, $string, FILE_APPEND | LOCK_EX);
			file_put_contents($path, $string . "\n", FILE_APPEND);
		} catch(\Exception $e){
			\Log::error(__METHOD__ . " 数据打印出错 {$e->getMessage()}");
		}
	}
}
