<?php namespace InsertName\Base;
/**
 * Created by PhpStorm.
 * User: whitekiba
 * Date: 07.09.16
 * Time: 10:08
 */

use InsertName\DB;

abstract class Auth {
	static private $_instances = array();
	protected $online, $_db;
	public $username;

	/**
	 * Auth constructor.
	 */
	function __construct() {
		if (isset($_SESSION["InsertName_username"])) {
			$this->username = $_SESSION["InsertName_username"];
		}
	}

	/**
	 * Dummy
	 *
	 * @param $username
	 * @param $password
	 * @return bool
	 */
	public function login($username, $password) { return true; }

	/**
	 *
	 */
	public function isOnline() {
		if ($this->online) {
			return true;
		}
		if ($this->checkSession()) {
			$this->online = true;
			return true;
		}
		return false;
	}

	/**
	 * @param bool $redirect
	 */
	public function logout($redirect = false) {
		$this->online = false;
		$this->deleteSession();
		if ($redirect)
			header("Location: $redirect"); // @codeCoverageIgnore
	}

	/**
	 * Delete session
	 * @internal param $session_id
	 */
	protected function deleteSession() {
        if (session_id() != "") {
            //NOTE: Wir unterdrücken den Fehler um PHPUnit glücklich zu machen
            @session_destroy();
        }
	}

	/**
	 * @param $username
	 * @return bool|mysqli_result
	 */
	protected function registerSession($username) {
	    if (session_id() == "") {
	        //NOTE: Wir unterdrücken den Fehler damit Unittests durchlaufen
	        @session_start();
        }

		$_SESSION['InsertName_username'] = $username;
	    $_SESSION['InsertName_authenticated'] = true;
		return true;
	}

	/**
	 * @return bool
	 */
	protected function checkSession() {
		return (isset($_SESSION['InsertName_authenticated'])) ? false : true;
	}

	/**
	 * @return Auth
	 */
	public static function getInstance() {
		$class = get_called_class();
		if (!isset(self::$_instances[$class])) {
			self::$_instances[$class] = new $class();
		}
		return self::$_instances[$class];
	}

	abstract protected function getUserID();
}