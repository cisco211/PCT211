<?php
if (!defined('MB_EXEC')) exit('Invalid access!'.EOL);
MB_Log()->debug(' '.__FILE__.':'.__LINE__);

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
	
	/**
	 * Configuration data
	 */
	private $__config = array(
		'backup'=>array(
			'root'=>NULL,
		),
		'exec'=>array(
			'append'=>array(),
			'prepend'=>array(),
		),
		'file'=>array(
			'clone'=>array(
				'command'=>NULL,
				'entries'=>array(),
			),
		),
		'mysql'=>array(
			'dump'=>array(
				'command'=>NULL,
				'entries'=>array(),
			),
		),
	);
	
	/**
	 * Default configuration
	 * @return array
	 */
	private function _default() {
		return explode(chr(10),<<<ENDCFG
# myBackup configuration file
# ===========================

# BACKUP commands
# ---------------

# Backup root directory
BACKUP_ROOT	{\$MB_ROOT}/backup

# Backup pattern
BACKUP_PATTERN	{\$yyyy}{\$mm}{\$dd}

# EXEC commands
# -------------
# List of commands that has to be executed

# Execute after processing
EXEC_APPEND	echo myBackup has ended!

# Execute before processing
EXEC_PREPEND	echo myBackup has started!

# FILE commands
# -------------

# Clone files
#FILE_CLONE	SOURCE?
FILE_CLONE

# Clone command
FILE_CLONE_CMD	cp --parents -rfLv {\$source} {\$target}

# MYSQL commands
# --------------

# Dump database
#MYSQL_DUMP	USER?	PASSWORD?	DATABASE?
MYSQL_DUMP

# Dump command
MYSQL_DUMP_CMD	mysqldump -u {\$user} -p{\$password} {\$database} > {\$target}
ENDCFG
		);
	}
	
	private function _extract($line,$position) {
		$output = explode(chr(9),substr($line,$position));
		foreach ($output as $k => $v) {
			if (empty($v)) unset($output[$k]);
		}
		return $output;
	}
	
	/**
	 * Parser debugging
	 * @param string $command
	 * @param array $values
	 */
	private function _parseDebug($command,$values=array()) {
		#MB_Log()->debug('  '.__METHOD__.'()');
		if (!MB_DEBUG) return;
		$message = '   '.$command;
		if (is_string($values)) $values = explode(chr(9),$values);
		foreach ($values as $value) {
			if (!empty($value)) $message .= ','.var_export($value,TRUE);
		}
		MB_Log()->debug($message);
	}
	
	/**
	 * Parser
	 * @param array $lines
	 * @return boolean
	 */
	private function _parse(array $lines) {
		MB_Log()->debug('  '.__METHOD__.'()');
		
		// Iterate over lines
		foreach ($lines as $line) {
			$l = trim($line);
				
			// Empty line
			if (empty($l)) {
				#$this->_parseDebug('EMPTY');
			}
				
			// Comment
			else if (substr($l,0,1) === '#') {
				$c = trim(substr($l,1));
				#$this->_parseDebug('COMMENT',$c);
			}
				
			// Backup
			else if (substr($l,0,6) === 'BACKUP') {
				
				// Pattern
				if (substr($l,7,7) === 'PATTERN') {
					$v = $this->_extract($l,15);
					if (count($v) == 1) $this->__config['backup']['pattern'] = $v[0];
					$this->_parseDebug('BACKUP_PATTERN',$v);
				}
				
				// Root
				else if (substr($l,7,4) === 'ROOT') {
					$v = $this->_extract($l,12);
					if (count($v) == 1) $this->__config['backup']['root'] = $v[0];
					$this->_parseDebug('BACKUP_ROOT',$v);
				}
		
				// Any other
				else {
					$this->_parseDebug('UNKNOWN',$l);
				}
		
			}
				
			// Exec
			else if (substr($l,0,4) === 'EXEC') {
		
				// Append
				if (substr($l,5,6) === 'APPEND') {
					$v = $this->_extract($l,12);
					if (count($v) == 1) $this->__config['exec']['append'][] = $v[0];
					$this->_parseDebug('EXEC_APPEND',$v);
				}
		
				// Prepend
				else if (substr($l,5,7) === 'PREPEND') {
					$v = $this->_extract($l,13);
					if (count($v) == 1) $this->__config['exec']['prepend'][] = $v[0];
					$this->_parseDebug('EXEC_PREPEND',$v);
				}
		
				// Any other
				else {
					$this->_parseDebug('UNKNOWN',$l);
				}
		
			}
				
			// File
			else if (substr($l,0,4) === 'FILE') {
		
				// Clone command
				if (substr($l,5,9) === 'CLONE_CMD') {
					$v = $this->_extract($l,15);
					if (count($v) == 1) $this->__config['file']['clone']['command'] = $v[0];
					$this->_parseDebug('FILE_CLONE_CMD',$v);
				}
		
				// Clone
				else if (substr($l,5,5) === 'CLONE') {
					$v = $this->_extract($l,11);
					if (count($v) == 1) $this->__config['file']['clone']['entries'][] = $v[0];
					$this->_parseDebug('FILE_CLONE',$v);
				}
		
				// Any other
				else {
					$this->_parseDebug('UNKNOWN',$l);
				}
			}
				
			// MySQL
			else if (substr($l,0,5) === 'MYSQL') {
		
				// Clone command
				if (substr($l,6,8) === 'DUMP_CMD') {
					$v = $this->_extract($l,15);
					if (count($v) == 1) $this->__config['mysql']['dump']['command'] = $v[0];
					$this->_parseDebug('MYSQL_DUMP_CMD',$v);
				}
		
				// Clone
				else if (substr($l,6,4) === 'DUMP') {
					$v = $this->_extract($l,11);
					if (count($v) == 3) $this->__config['mysql']['dump']['entries'][] = array('user'=>$v[0],'password'=>$v[1],'database'=>$v[2]);
					if (isset($v[1]) AND !empty($v[1])) $v[1] = '*****';
					$this->_parseDebug('MYSQL_DUMP',$v);
				}
		
				// Any other
				else {
					$this->_parseDebug('UNKNOWN',$l);
				}
			}
			
			// Any other
			else {
				$this->_parseDebug('UNKNOWN',$l);
			}
		}
		#var_export($this->__config);print EOL;
		return TRUE;
	}
	
	/**
	 * Constructor
	 */
	private function __construct() {
		MB_Log()->debug('  '.__METHOD__.'()');
		$this->_parse($this->_default());
	}
	
	/**
	 * Get config value(s)
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		MB_Log()->debug('  '.__METHOD__.'()');
		$keys = explode('.',$key);
		switch (count($keys)) {
			case 3: return isset($this->__config[$keys[0]][$keys[1]][$keys[2]]) ? $this->__config[$keys[0]][$keys[1]][$keys[2]] : NULL; break;
			case 2: return isset($this->__config[$keys[0]][$keys[1]]) ? $this->__config[$keys[0]][$keys[1]] : NULL; break;
			case 1: return isset($this->__config[$keys[0]]) ? $this->__config[$keys[0]] : NULL; break;
			case 0: case FALSE: default: return NULL; break;
		}
	}
	
	/**
	 * Return class instance
	 */
	public static function getInstance() {
		MB_Log()->debug('  '.__METHOD__.'()');
		if(self::$instance === NULL) self::$instance = new MB_Config();
		return self::$instance;
	}
	
	/**
	 * Read configuration file
	 * @param string $file
	 * @throws MB_Exception
	 * @return boolean
	 */
	public function read($file) {
		MB_Log()->debug('  '.__METHOD__.'()');
		$lines = array();
		if (!@file_exists($file)) throw new MB_Exception('Configuration file "'.$file.'" not found!');
		$lines = @file($file);
		if ($lines === FALSE) throw new MB_Exception('Configuration file "'.$file.'" not readable!');
		return $this->_parse($lines);
	}
}