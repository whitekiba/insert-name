<?php namespace InsertName\Base;

use InsertName\Factories\LogFactory;
use InsertName\DB;

/**
 * Class ConfigBase
 *
 * TODO: Es braucht noch eine delete methode
 */
abstract class ConfigBase extends BaseModel implements \ArrayAccess {
	static private $_instances = array();

	protected $db_backed = false;
	protected $db_table = false;
	protected $config = array();
	protected $db_value = array();
	/** @noinspection PhpMissingParentConstructorInspection */

	/**
	 * ConfigBase constructor.
	 */
	function __construct() {
	    $this->config["log_path"] = __DIR__."/../../log/app.log";

        if ($this->db_backed) {
			$this->_db = new DB($this->config["db_host"], $this->config["db_user"], $this->config["db_pass"], $this->config["db_name"]);

			if (!$this->db_table)
				$this->db_table = $this->tableName();
		}
	}

    /**
     * Shorthand for Config::getInstance->get($field)
     *
     *Just use Config::g($field);
     *
     * @param $field
     * @return bool|mixed
     */
	public static function g($field) { return self::getInstance()->get($field); }

    /**
     * Check if config value exists. nothing more
     *
     * @param $key
     * @return bool
     */
	public function exists($key) {
        if ($this->db_backed && !isset($this->config[$key])) {
            $this->load();
        }

	    if (isset($this->config[$key]))
	        return true;
	    return false;
    }

	/**
	 * @param $key
	 * @return bool|mixed
	 */
	public function get($key) {
	    if ($this->db_backed && !isset($this->config[$key])) {
	        $this->load();
        }

		if (isset($this->config[$key])) {
			return $this->config[$key];
		} else {
            LogFactory::get(strtolower(get_called_class()))->info("Unused Key $key requested.");
			return false;
		}
	}

	/**
	 * @param $key
	 * @param $value
	 * @return bool
	 */
	public function set($key, $value) {
		$this->config[$key] = $value;

		if ($this->db_backed && isset($this->db_value[$key])) {
			if ($this->_dbKeyExists($key)) { //Update
				$sql = "UPDATE ".$this->db_table." SET config_value = \"".$this->_db->escape($value)."\" WHERE config_key LIKE \"".$this->_db->escape($key)."\"";
			} else { //erstellen
				$sql = "INSERT INTO ".$this->db_table." (`ID`, `config_key`, `config_value`) VALUES (NULL, '".$this->_db->escape($key)."', '".$this->_db->escape($value)."')";
			}
			$q = $this->_db->query($sql);
			$this->config[$key] = $value;
			$this->db_value[$key] = true;
			return $q;
		}
		return true;
	}

    /**
     * ArrayAccess setter
     * Wir erlauben keine null werte
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function offsetSet($key, $value) {
	    if (!is_null($key))
	        $this->set($key, $value);
    }

    public function offsetExists($key) {
        return isset($this->config[$key]);
    }

    public function offsetUnset($key) {
        unset($this->config[$key]);
    }

    public function offsetGet($key) {
        return $this->get($key);
    }

	/**
	 * @return Config
	 */
	public static function getInstance() {
		$class = get_called_class();
		if (!isset(self::$_instances[$class])) {
			self::$_instances[$class] = new $class();
		}
		return self::$_instances[$class];
	}

	/**
	 * Prüfen ob ein wert schon in der Datenbank ist
	 *
	 * Diese Methode ist ein kleiner helfer für set war aber so umfangreich dass ich es in eine seperate methode gepackt hab
	 * @param $key
	 * @return bool
	 */
	private function _dbKeyExists($key) {
		if ($this->db_backed) {
			$sql = "SELECT ID FROM ".$this->db_table." WHERE \"config_key\" LIKE \"".$this->_db->escape($key)."\";";
			$res = $this->_db->query($sql);
			if (mysqli_num_rows($res) > 0) {
				return true;
			}
		}
		return false;
	}

    /**
     * Config aus DB laden
     * @return bool|void
     */
	protected function load() {
        $res = $this->_db->query("SELECT * FROM ".$this->db_table.";");

        while ($row = mysqli_fetch_assoc($res)) {
            if (!isset($this->config[$row["key"]])) {
                $this->db_value[$row["config_key"]] = true;
                $this->config[$row["config_key"]] = $row["config_value"];
            }
        }
    }
}
