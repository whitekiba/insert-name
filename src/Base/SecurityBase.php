<?php namespace Fluxnet\Base;

abstract class SecurityBase {
    protected $_log;

    function __construct() {
        $this->_log = LogFactory::get();
    }

    /**
     * Check if username is valid
     *
     * we check with a simple regex and against our blacklist.
     * this method does NOT check if a username is already used
     *
     * Do NOT use it to validate a user against our database
     *
     * @param $username
     * @return bool
     */
    public static function validUsername($username) {
        if (!preg_match("/^[a-zA-Z0-9_]+$/", $username)) {
            return false;
        }
        return true;
    }

    /**
     * @param $email
     * @return bool
     */
    public static function validEmailaddress($email) {
        if (mysqli_num_rows(DB::getInstance()->query("SELECT email FROM users WHERE email LIKE '".DB::getInstance()->escape($email)."';")) > 0) {
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return true;
    }

    /**
     * Generate random String
     * @param $length
     * @param bool $characters
     * @return string
     */
    public static function randomString($length, $characters = false) {
        if (!$characters)
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}