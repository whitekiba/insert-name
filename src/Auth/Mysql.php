<?php namespace InsertName\Auth;

use InsertName\Interfaces\Auth as IAuth;
use InsertName\Base\Auth;

class MysqlAuth extends Auth implements IAuth {

	/**
	 * @param $username
	 * @param $password
	 * @return bool
	 */
	public function login($username, $password) {
		$res = $this->_db->query('SELECT ID, password FROM users WHERE username LIKE "'.$this->_db->escape($username).'" AND active = 1 LIMIT 1');
		$row = mysqli_fetch_row($res);

		if (password_verify($password, $row[1])) {
		    $this->username = $username;

			if ($this->registerSession($username)) {
				$this->online = true;
				return true;
			}
		}
		return false;
	}

	/**
	 * @param $username
	 * @return bool|mixed
	 */
	public function getPasswordHash($username) {
		$user = new User($username);
		return $user->getPasswordHash();
	}

    /**
     * @return bool|mixed
     * @codeCoverageIgnore
     */
	protected function getUserID() {
		$user = new User($this->username);
		return $user->getID();
	}
}
