<?php
if (!defined('MB_EXEC')) exit('Invalid access!'.EOL);

/**
 * Shortcut to Config class
 */
function MB_Log() {
	return MB_Log::getInstance();
}

/**
 * Log class
 */
final class MB_Log {
	
	/**
	 * Instance variable
	 */
	private static $instance = NULL;
	
	/**
	 * Buffer data
	 */
	private $buffer = array();
	
	/**
	 * Constructor
	 */
	private function __construct() {
		if (!file_exists(MB_ROOT.DS.'log') AND !@mkdir(MB_ROOT.DS.'log')) MB_Quit('ERROR: Failed to create log directory!'.EOL);
	}
	
	/**
	 * Clone
	 */
	private function __clone() {}
	
	/**
	 * Destructor
	 */
	public function __destruct() {
		$this->write();
	}

	/**
	 * Add log entry
	 */
	public function add($type,$message) {
		if (MB_LOGBUFFERING) {
			$this->buffer[$type][] = $this->format($message,$type);
		} else {
			$handler = fopen(MB_ROOT.DS.'log'.DS.date(MB_LOGFILEPATTERN).'.log','a');
			$line = $this->format($message,$type);
			fwrite($handler,$line.EOL,strlen($line.EOL));
			@fclose($handler);
		}
	}
	
	/**
	 * Log debug message
	 */
	public function debug($message) {
		if (!MB_DEBUG) return;
		else $this->add('debug',$message);
	}
	
	/**
	 * Log info message
	 */
	public function info($message) {
		$this->add('info',$message);
	}
	/**
	 * Log error message
	 */
	public function error($message) {
		$this->add('error',$message);
	}
	/**
	 * Format log message
	 */
	public function format($message,$type) {
		$string = MB_LOGLINEPATTERN;
		$string = str_replace('%datetime',date('Y-m-d H:i:s'),$string);
		$string = str_replace('%message',$message,$string);
		$string = str_replace('%type',strtoupper($type),$string);
		return $string;
	}
	
	/**
	 * Get buffer data
	 */
	public function getBuffer() {
		return $this->buffer;
	}
	
	/**
	 * Return class instance
	 */
	public static function getInstance() {
		if(self::$instance === NULL) self::$instance = new MB_Log();
		return self::$instance;
	}
	
	/**
	 * Log security message
	 */
	public function security($message) {
		$this->add('security',$message);
	}
	
	/**
	 * Log warning message
	 */
	public function warning($message) {
		$this->add('warning',$message);
	}
	
	/**
	 * Write buffer to log file
	 */
	public function write() {
		foreach($this->buffer as $type => $lines) {
			$handler = fopen(MB_ROOT.DS.'log'.DS.date(MB_LOGFILEPATTERN).'.log','a');
			foreach($lines as $line) fwrite($handler,$line.EOL,strlen($line.EOL));
			@fclose($handler);
		}
		$this->buffer = array();
	}
}