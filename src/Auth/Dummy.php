<?php namespace Fluxnet\Auth;

use Fluxnet\Interfaces\Auth as IAuth;
use Fluxnet\Base\Auth;

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