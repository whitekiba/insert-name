<?php namespace InsertName\Cache;

use InsertName\Factories\LogFactory;
use InsertName\Interfaces\Cache;

class Redis implements Cache {
    private $redis_online = false, $redis, $_config;
    /**
     * Cache constructor.
     */
    function __construct() {
        $this->_config = Config::getInstance();
        $this->redis = new \Redis();
        try {
            //Ja @ ist unschön aber scheinbar der einzige Weg um den Fehler zu vermeiden
            if (@$this->redis->pconnect($this->_config->get("redis_host"), $this->_config->get("redis_port"))) {
                $this->redis->ping();
                $this->redis_online = true;
            }
        } catch (\Exception $e) {
            $this->redis_online = false;
            LogFactory::get()->error("$e");
        }
    }

    /**
     * @param $key
     * @param $value
     * @param bool $expire
     * @return bool
     */
    public function set($key, $value, $expire = false) : bool {
        if ($this->_checkRedisAlive()) {
            $this->redis->set($key, $value);

            if ($expire)
                return (bool)$this->redis->expireAt($key, strtotime('+1 day', time()));

            return true;
        }
    }

    /**
     * @param $key
     * @return bool|string
     */
    public function get($key) {
        if ($this->_checkRedisAlive()) { //beim abfragen pingen wir redis
            return $this->redis->get($key);
        }
        return false;
    }

    /**
     * @return bool
     */
    public function flush() : bool {
        return (bool)$this->redis->flushdb();
    }

    /**
     * Delete a value from redis
     *
     * @param $key
     */
    public function del($key) {
        $this->redis->del($key);
    }

    /**
     * Check if key exists
     *
     * @param $key
     * @return bool
     */
    public function exists($key) {
        return $this->redis->exists($key);
    }

    /**
     * @return bool
     */
    public function online() {
        return $this->redis_online;
    }

    /**
     * @return bool
     */
    private function _checkRedisAlive() {
        if ($this->redis_online)
            return $this->redis_online;

        if ($this->redis->ping() == "+PONG") {
            $this->redis_online = true;
            return true;
        }

        return false;
    }

}