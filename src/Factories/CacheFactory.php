<?php namespace InsertName\Factories;

use App\Config;
use InsertName\Auth\{Dummy, Ldap, Mysql};

/**
 * Class CacheFactory
 *
 * @package InsertName\Factories
 */
class CacheFactory {
    /**
     * @param $class
     * @return bool
     */
    public static function get($class) {
        if (class_exists("InsertName\\Cache\\".$class)) {
            return new $class;
        }

        return false;
    }
}
