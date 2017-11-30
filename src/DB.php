<?php namespace Fluxnet;

use App\Config;
use Fluxnet\Exception\FatalException;
use mysqli;

/**
 * Class DB
 */
class DB {
	static private $instance;
	private $_config, $db_name;
	public $conn;

	/**
	 * DB constructor.
	 * @param bool $host
	 * @param bool $user
	 * @param bool $pass
	 * @param bool $db
	 */
	function __construct($host = false, $user = false, $pass = false, $db = false) {
	    $this->conn = new mysqli();
	    $this->conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 2);
        /**
         * TODO: Der folgende Block ist unschön.
         * Der Code ist zu komplex und kann sicherlich reduziert werden.
         * Bei gelegenheit mal anschauen wie die API stabil gehalten werden kann
         */
		if ($host && $user && $pass && $db) {
			@$this->conn->connect($host, $user, $pass, $db);
			$this->db_name = $db;
		} else {
            $this->_config = Config::getInstance();
			@$this->conn->connect($this->_config->get("db_host"),
				$this->_config->get("db_user"),
				$this->_config->get("db_pass"),
				$this->_config->get("db_name"));

			$this->db_name = $this->_config->get("db_name");
		}

		if ($this->conn->connect_errno) {
			throw new FatalException("Database not reachable.");
		}
	}

	/**
	 * MySQLi query wrapper
	 * @param $sql
	 * @return bool|mysqli_result
	 */
	public function query($sql) {
		return $this->conn->query($sql);
	}

	/**
	 * @param $str
	 * @return string
	 */
	public function escape($str) {
		return mysqli_real_escape_string($this->conn, $str);
	}

	/**
	 * Return the ID of the last insert
	 * @return mixed
	 */
	public function last_id() {
		return $this->conn->insert_id;
	}

    /**
     * Check if database exists.
     * Primarily for migrations and deployments
     *
     * @return bool
     */
	public function exists() {
	    $sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '".$this->db_name."'";
	    $result = $this->query($sql);
	    if (mysqli_num_rows($result) > 0) {
	        return true;
        }
        return false;
    }

    /**
     * Methode um ganze Dateien zu importieren.
     * Da wir pro query aufruf nur eine query ausführen dürfen müssen wir das aufteilen
     * Nötig geworden für Migrationen und automatischen Import
     *
     * @param $filename
     * @return bool
     */
    public function importFile($filename) {
	    $templine = '';
        $lines = file($filename);

        foreach ($lines as $line) {
            if (substr($line, 0, 2) == '--' || $line == '')
                continue;

            $templine .= $line;
            if (substr(trim($line), -1, 1) == ';')  {
                if (!$this->query($templine))
                    return false;

                $templine = '';
            }
        }

        return true;
    }

	/**
	 * Datenbank Singleton
	 * @return DB
	 */
	static public function getInstance() {
		if (self::$instance === null) {
			self::$instance = new self;
		}
		return self::$instance;
	}
}
