<?php namespace InsertName\Interfaces;

interface Auth {
	//Standardkram.
	public function isOnline();
	public function login($username, $password);
	public function logout($redirect = false);

	public function getPasswordHash($username);

}
