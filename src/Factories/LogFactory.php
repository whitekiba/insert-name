<?php namespace InsertName\Factories;

use App\Config;
use InsertName\Log\Dummy;
use Log;

class LogFactory {
	/**
	 * @param bool $ident
	 * @return object Log
	 */
	public static function get($ident = false) {
		if (!$ident)
			$ident = "dyndns";

		if (class_exists("Log")) {
			$config = Config::getInstance();
			if ($config->get("log_path") == "") {
				return Log::singleton("console", '', $ident);
			} else {
			    return Log::singleton("file", $config->get("log_path"), $ident);
            }
		}

		//Return Dummy as last resort
		return new Dummy();
	}
}