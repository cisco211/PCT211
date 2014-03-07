<?php
if (!defined('MB_EXEC')) exit('Invalid access!'.EOL);
MB_Log()->debug(' '.__FILE__);

/**
 * Shortcut to Config class
 */
function MB_Config() {
	return MB_Config::getInstance();
}

/**
 * Configuration class
 */
final class MB_Config {
	
	/**
	 * Instance variable
	 */
	private static $instance = NULL;
	
	private function __construct() {
		MB_Log()->debug('  '.__METHOD__.'()');
		$config = MB_Option()->get('file',NULL);
		if (!$config) return;
		if (!@file_exists($config)) throw new MB_Exception('Config file does not exist!');
	}
	
	/**
	 * Return class instance
	 */
	public static function getInstance() {
		MB_Log()->debug('  '.__METHOD__.'()');
		if(self::$instance === NULL) self::$instance = new MB_Config();
		return self::$instance;
	}
	
}