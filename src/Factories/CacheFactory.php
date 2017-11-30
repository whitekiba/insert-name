<?php namespace Fluxnet\Factories;

use App\Config;
use Fluxnet\Auth\{Dummy, Ldap, Mysql};

/**
 * Class CacheFactory
 *
 * @package Fluxnet\Factories
 */
class CacheFactory {
    /**
     * @param $class
     * @return bool
     */
    public static function get($class) {
        if (class_exists("Fluxnet\\Cache\\".$class)) {
            return new $class;
        }

        return false;
    }
}
