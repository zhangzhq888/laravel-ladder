<?php
// 带版本控制的asset
if (! function_exists('v_asset')) {
    /**
     * Generate an asset path for the application.
     *
     * @param  string  $path
     * @param  bool    $secure
     * @return string
     */
    function v_asset($path, $secure = null)
    {
	    $version ='';
	    if(config('app.env') != 'local'){
		    $version = '/' . trim(file_get_contents(public_path('version'))) . "/";
	    }
	    $versionPath = preg_replace('~/+~', '/', $version . $path);
	    
        return asset($versionPath, $secure);
    }
}


if (! function_exists('sql_safe')) {
	function sql_safe(&...$arguments)
	{
		foreach ($arguments as &$variable){
			if(empty($variable)) return;
			if(!is_array($variable) && !is_object($variable)){
				$variable = \DB::connection()->getPdo()->quote($variable);
			} elseif (is_array($variable)){
				foreach ($variable as $key => &$value){
					sql_safe($value);
				}
			}
		}
	}
}

if (! function_exists('float_equal')) {
	function float_equal($a, $b){
		return (abs($a-$b) < 0.00001);
	}
}