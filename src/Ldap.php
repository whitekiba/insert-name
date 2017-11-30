<?php namespace InsertName;

use InsertName\Exception\FatalException;

/**
 * Class LDAP
 *
 * Don't use. It's ass
 *
 * @package InsertName
 */
class LDAP {
	private $authconn;
	private $dn_map = array();

	/**
	 * LDAP constructor.
	 */
	function __construct() {
		$this->_config = Config::getInstance();
		$this->ldapconn = ldap_connect($this->_config->get("ldap_host"));
		ldap_set_option($this->ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
		if (!@ldap_bind($this->ldapconn, $this->_config->get("ldap_dn"), $this->_config->get("ldap_pass"))) {
			throw new FatalException("LDAP could not be reached...");
		}
	}

	/**
	 * @param $username
	 * @param $group
	 * @return bool
	 */
	function isInGroup($username, $group) {
		$read = ldap_read($this->ldapconn, "cn=".$group.",".$this->_config->get("ldap_base_group"), "objectClass=*");
		$entries = ldap_get_entries($this->ldapconn, $read);
		if (array_key_exists("memberuid", $entries[0])) {
			if (in_array($username, $entries[0]["memberuid"])) {
				return true;
			} else {
				return false;
			}  
		} else {
			return false;
		}
	}

	/**
	 * Get the password hash of user
	 * we need it to calculate the API Key
	 * @param $username
	 * @return
	 */
	function getPasswordHash($username) {
		$read = ldap_read($this->ldapconn, $this->getDNbyUsername($username), "objectClass=*");
		$entries = ldap_get_entries($this->ldapconn, $read);
		return $entries[0]["userpassword"][0];
	}

	function getUserUUID($username) {
		$read = ldap_read($this->ldapconn, $this->getDNbyUsername($username), "objectClass=*", array("entryUUID"));
		$entries = ldap_get_entries($this->ldapconn, $read);
		return $entries[0]["entryuuid"][0];
	}

	/**
	 * @param $username
	 * @param $password
	 * @return bool
	 */
	public function auth($username, $password) {
		$username = trim($username);
		$password = trim($password);
		$this->_config = \Config::getInstance();
		$ldapdn = "uid=".$username.",".$this->_config->get("ldap_base_user");
		$this->authconn = ldap_connect($this->_config->get("ldap_host"));
		ldap_set_option($this->authconn, LDAP_OPT_PROTOCOL_VERSION, 3);
		$ldapbind = @ldap_bind($this->authconn, $ldapdn, $password);
		if ($ldapbind) {
			$this->_config->set("cur_user_dn", $ldapdn);
			return true;
		} else {
			if ($this->tryOtherAuth($username, $password)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Alternative authentifizierung
	 * @param $username
	 * @param $password
	 * @return bool
	 */
	private function tryOtherAuth($username, $password) {
		$CN = $this->getCNbyUID($username);
		if ($CN) {
			$ldapdn = "cn=".$CN.",".$this->_config->get("ldap_base_user");
			$ldapbind = @ldap_bind($this->authconn, $ldapdn, $password);
			if ($ldapbind) {
				$this->_config->set("cur_user_dn", $ldapdn);
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Get the Common Name of a user by his uid
	 * needed because of the old entries wich are using the CN as their DN Value
	 * @param $uid
	 * @return bool
	 */
	private function getCNbyUID($uid) {
		$search = ldap_search($this->ldapconn, $this->_config->get("ldap_base_user"), "uid=".$uid);
		$entries = ldap_get_entries($this->ldapconn, $search);
		if (isset($entries[0]['cn'][0])) {
			return $entries[0]['cn'][0];
		} else {
			return false;
		}
	}

	/**
	 * DN bestimmen anhand des Usernamen
	 * Und den Kram cachen.
	 *
	 * @param $username
	 * @return bool|mixed
	 */
	private function getDNbyUsername($username) {
		if (isset($this->dn_map[$username])) {
			return $this->dn_map[$username];
		}

		$search = ldap_search($this->ldapconn, $this->_config->get("ldap_base_user"), "uid=".$username);
		$entries = ldap_get_entries($this->ldapconn, $search);
		if (isset($entries[0]['dn'])) {
			$this->dn_map[$username] = $entries[0]['dn'];
			return $entries[0]['dn'];
		} else {
			return false;
		}
	}

	public function getConn() {
		return $this->ldapconn;
	}
}
