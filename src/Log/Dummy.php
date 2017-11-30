<?php namespace InsertName\Log;

/**
 * Class LogDummy
 *
 * Diese Klasse wird nur als Dummy genutzt. Sie antwortet auf alle methoden mit true
 * Sie dient nur als Fallback für die Log Factoryäo
 */
class Dummy {
	public function __call($name, $arguments) {
		return true;
	}
}