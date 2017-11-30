<?php namespace InsertName\Auth;

use InsertName\Interfaces\Auth as IAuth;
use InsertName\Base\Auth;
use InsertName\LDAP;

/**
 * Class LdapAuth
 */
class LdapAuth extends Auth implements IAuth {
	private $_ldap;

	/**
	 * LdapAuth constructor.
	 */
	function __construct() {
		parent::__construct();
		$this->_ldap = new LDAP();
	}

	/**
	 * @param $username
	 * @param $password
	 * @return bool
	 */
	public function login($username, $password) {
		if ($this->_ldap->auth($username, $password)) {
			$this->registerSession($username);
			$this->username = $username;
			$this->online = true;
			return true;
		}
		return false;
	}

	/**
	 * @param $username
	 * @return mixed
	 */
	public function getPasswordHash($username) {
		return $this->_ldap->getPasswordHash($username);
	}

	/**
	 * @return bool|mixed
	 */
	protected function getUserID() {
		return $this->_ldap->getUserUUID($this->username);
	}
}