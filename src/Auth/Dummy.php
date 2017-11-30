<?php namespace InsertName\Auth;

use InsertName\Interfaces\Auth as IAuth;
use InsertName\Base\Auth;

/**
 * Class DummyAuth
 *
 * Dummy Authentifizierung
 * Username test, passwort test. Mehr nicht
 */
class Dummy extends Auth implements IAuth {
	/**
	 * @param $username
	 * @param $password
	 * @return bool
	 */
	public function login($username, $password) {
		if ($username == "test" && $password == "test") {
			if ($this->registerSession($username)) {
				$this->username = $username;
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
		return "test";
	}
	protected function getUserID() {
		return 1;
	}
}