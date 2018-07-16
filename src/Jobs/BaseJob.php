<?php

namespace Laravelladder\Core\Jobs;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

abstract class BaseJob implements ShouldQueue
{
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;
	
	protected $maxAttempt = 5;
	
	public function failed()
	{
		if($this->attempts() > 5){
			\Log::error(get_called_class() ." 执行失败");
			$this->delete();
		}
	}
}
