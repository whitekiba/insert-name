<?php namespace InsertName;

/**
 * Created by PhpStorm.
 * User: whitekiba
 * Date: 31.10.16
 * Time: 18:21
 */
class User extends Base\BaseModel {
	protected $username;

	/**
	 * User constructor.
	 * @param bool $username
	 */
	function __construct($username = false) {
		parent::__construct();

		if ($username) {
			$this->username = $username;
			$this->values["username"] = $username;
		}
	}

	/**
	 * @return array|bool
	 */
	public function load() {
		if (isset($this->username)) {
			$sql = 'SELECT * FROM users WHERE username LIKE "' . $this->_db->escape($this->username) . '" LIMIT 1;';
			$result = $this->_db->query($sql);
			$fetch = mysqli_fetch_array($result);
			if ($fetch) {
				foreach ($fetch as $key => $value) {
					if (!isset($this->values[$key])) {
						$this->values[$key] = $value;
					}
				}
				$this->_log->info("User ".$this->values["ID"]." wurde geladen.");
				return $this->values;
			}
		}
		return false;
	}

	/**
	 * @param $userid
	 * @return array|bool|null
	 */
	public function loadByUserID($userid) {
		$sql = 'SELECT * FROM users WHERE ID = '.intval($userid).';';
		$result = $this->_db->query($sql);
		if (mysqli_num_rows($result) > 0) {
			$this->values = mysqli_fetch_array($result);
			return $this->values;
		}
		return false;
	}

	/**
	 * create new user
	 * @return bool
	 */
	public function create() {
	    if (!$this->getUsername() || !$this->getEmail())
	        return false;

		if (!is_numeric($this->getID())) {
			$res = $this->_db->query("SELECT ID FROM users WHERE username LIKE \"".$this->getUsername()."\";");
			if (mysqli_num_rows($res) < 1) {
				$sql = "INSERT INTO `users` (`ID`, `username`, `email`, `password`)
						VALUES (NULL, '" . $this->_db->escape($this->getUsername()) . "', '" . $this->_db->escape($this->getEmail()) . "', '" . $this->getPasswordHash() . "');";

				$res = $this->_db->query($sql);
				$this->values["ID"] = $this->_db->last_id();

				if ($res) {
					$this->_log->info("User ".$this->values["ID"]." wurde erstellt.");
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * @return bool
	 */
	public function activate() {
		if (!isset($this->values["ID"])) {
			$this->load();
		}
		if (isset($this->values["ID"])) {
            $sql = "UPDATE users SET active = 1 WHERE ID = " . $this->values["ID"];
            if ($this->_db->query($sql)) {
                $this->_log->info("User " . $this->values["ID"] . " wurde aktiviert.");
                return true;
            }
        }
		return false;
	}

	#getter und setter
	/**
	 * @return bool|mixed
	 */
	public function getUsername() { return $this->get("username"); }

	/**
	 * set Username
	 * @param $username
	 * @return bool
	 */
	public function setUsername($username) {
		return $this->set("username", $username);
	}

	/**
	 * @return bool|mixed
	 */
	public function getID() { return $this->get("ID"); }

	/**
	 * @return bool|mixed
	 */
	public function getEmail() { return $this->get("email"); }

	/**
	 * set Email
	 *
	 * @param $email
	 * @return bool
	 */
	public function setEmail($email) {
		return $this->set("email", $email);
	}

	/**
	 * sets and encrypts the user password
	 * @param $password
	 * @return bool
	 */
	public function setPassword($password) { return $this->set("password", password_hash($password, PASSWORD_DEFAULT)); }

	/**
	 * @return bool|mixed
	 */
	public function getPasswordHash() { return $this->get("password"); }

	/**
	 * @return int|string
	 */
	public static function getUserCount() {
		$db = DB::getInstance();
		$res = $db->query("SELECT COUNT(ID) FROM users WHERE active = 1");
		return mysqli_fetch_array($res)[0];
	}
}
