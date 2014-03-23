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

# EXEC commands
# -------------
# List of commands that has to be executed

# Execute after processing
EXEC_APPEND	echo myBackup has started!

# Execute before processing
EXEC_PREPEND	echo myBackup has ended!

# FILE commands
# -------------

# Clone files
FILE_CLONE

# Clone command
FILE_CLONE_CMD	cp -rf {\$source} {\$target}

# MYSQL commands
# --------------

# Dump database
MYSQL_DUMP

# Dump command
MYSQL_DUMP_CMD	mysqldump -u {\$user} -p{\$password} {\$database} > {\$target}
ENDCFG
		);
	}
	
	
	/**
	 * Extract config value
	 * @param string $line
	 * @param int $position
	 */
	private function _extract($line,$position) {
		#MB_Log()->debug('  '.__METHOD__.'()');
		$length = strlen($line);
		if ($position > $length) return NULL;
		$stop = @strrpos($line,chr(9),$position);
		if ($stop !== FALSE) $stop -= $position;
		else {
			$stop = strrpos($line,'#',$position);
			if ($stop !== FALSE) $stop -= $position;
			else $stop = $length-$position;
		}
		return trim(substr($line,$position,$stop));
	}
	
	/**
	 * Parser debugging
	 * @param string $command
	 * @param string $value1
	 * @param string $value2
	 * @param string $value3
	 */
	private function _parseDebug($command,$value1=NULL,$value2=NULL,$value3=NULL) {
		#MB_Log()->debug('  '.__METHOD__.'()');
		if (!MB_DEBUG) return;
		$message = '   '.$command;
		if ($value1 !== NULL) $message .= ','.var_export($value1,TRUE);
		if ($value2 !== NULL) $message .= ','.var_export($value2,TRUE);
		if ($value3 !== NULL) $message .= ','.var_export($value3,TRUE);
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
				
				// Root
				if (substr($l,7,4) === 'ROOT') {
					$v1 = $this->_extract($l,12);
					if (!empty($v1)) $this->__config['backup']['root'] = $v1;
					$this->_parseDebug('BACKUP_ROOT',$v1);
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
					$v1 = $this->_extract($l,12);
					if (!empty($v1)) $this->__config['exec']['append'][] = $v1;
					$this->_parseDebug('EXEC_APPEND',$v1);
				}
				
				// Prepend
				else if (substr($l,5,7) === 'PREPEND') {
					$v1 = $this->_extract($l,13);
					if (!empty($v1)) $this->__config['exec']['prepend'][] = $v1;
					$this->_parseDebug('EXEC_PREPEND',$v1);
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
					$v1 = $this->_extract($l,15);
					if (!empty($v1)) $this->__config['file']['clone']['command'] = $v1;
					$this->_parseDebug('FILE_CLONE_CMD',$v1);
				}
				
				// Clone
				else if (substr($l,5,5) === 'CLONE') {
					$v1 = $this->_extract($l,11);
					if (!empty($v1)) $this->__config['file']['clone']['entries'][] = $v1;
					$this->_parseDebug('FILE_CLONE',$v1);
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
					$v1 = $this->_extract($l,15);
					if (!empty($v1)) $this->__config['mysql']['dump']['command'] = $v1;
					$this->_parseDebug('MYSQL_DUMP_CMD',$v1);
				}
				
				// Clone
				else if (substr($l,6,4) === 'DUMP') {
					$v1 = $this->_extract($l,11);
					if (!empty($v1)) $this->__config['mysql']['dump']['entries'][] = $v1;
					$this->_parseDebug('MYSQL_DUMP',$v1);
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