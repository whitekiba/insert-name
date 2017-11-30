<?php namespace InsertName\Factories;

use App\Config;
use InsertName\Auth\{Dummy, Ldap, Mysql};

class AuthFactory {
	public static function get() {
		$config = Config::getInstance();
		switch ($config->get("auth_type")) {
			case "ldap":
				return Ldap::getInstance();
			case "mysql":
				return Mysql::getInstance();
			default:
				return Dummy::getInstance();
		}
	}
}
