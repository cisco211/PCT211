<?php
if (!defined('MB_EXEC')) exit('Invalid access!'.EOL);
MB_Log()->debug(' '.__FILE__.':'.__LINE__);

/**
 * Shortcut to Option class
 */
function MB_Option() {
	return MB_Option::getInstance();
}

/**
 * Configuration class
 */
final class MB_Option {
	
	/**
	 * Current options
	 */
	
	private $__data = array();
	
	/**
	 * Instance variable
	 */
	private static $instance = NULL;
	
	/**
	 * Clone
	 */
	private function __clone() {
		MB_Log()->debug('  '.__METHOD__.'()');
	}
	
	/**
	 * Constructor
	 */
	private function __construct() {
		MB_Log()->debug('  '.__METHOD__.'()');
		
		// Options scheme
		$oS = array(); $oL = array();
		$oS[] = 'c'; $oL[] = 'check'; // Check environment (No value)
		$oS[] = 'd'; $oL[] = 'default'; // Return default config (No value)
		$oS[] = 'f:'; $oL[] = 'file:'; // File (Required value)
		$oS[] = 'h'; $oL[] = 'help'; // Help (No value)
		$oS[] = 'q'; $oL[] = 'quiet'; // Quiet (No value)
		
		// Get options
		$o = getopt(implode('',$oS),$oL);
		
		// Assign options
		$this->_set($o,'c','check');
		$this->_set($o,'d','default');
		$this->_set($o,'f','file');
		$this->_set($o,'h','help');
		$this->_set($o,'q','quiet');
	}
	
	/**
	 * Setter
	 */
	private function _set(&$options,$short,$long) {
		MB_Log()->debug('  '.__METHOD__.'()');
		if (isset($options[$short]))$this->__data[$long] = $options[$short];
		else if (isset($options[$long])) $this->__data[$long] = $options[$long];
		else $this->__data[$long] = NULL;
	}
		
	/**
	 * Getter
	 */
	public function __get($key) {
		MB_Log()->debug('  '.__METHOD__.'()');
		if ($this->exists($key)) return $this->__data[$key];
		else return NULL;
	}
	
	/**
	 * Returns parameter existance
	 */
	public function exists($key) {
		MB_Log()->debug('  '.__METHOD__.'()');
		if (isset($this->__data[$key])) return TRUE;
		else return FALSE;
	}
	
	/**
	 * Get post value
	 */
	public function get($key,$default=NULL) {
		MB_Log()->debug('  '.__METHOD__.'()');
		if ($this->exists($key)) return $this->__data[$key];
		else return $default;
	}
	
	/**
	 * Get all options
	 * 
	 */
	public function getAll() {
		MB_Log()->debug('  '.__METHOD__.'()');
		return $this->__data;
	}
	
	/**
	 * Return class instance
	 */
	public static function getInstance() {
		MB_Log()->debug('  '.__METHOD__.'()');
		if(self::$instance === NULL) self::$instance = new MB_Option();
		return self::$instance;
	}
}