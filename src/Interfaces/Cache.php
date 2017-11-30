<?php namespace Fluxnet\Interfaces;

interface Cache {
    public function set($key, $value, $expire = false) : bool;
    public function get($key);
    public function flush(): bool;
    public function del($key);
    public function exists($key);
}
