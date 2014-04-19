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
		'archive'=>array(
			'command'=>NULL,
			'compress'=>array(
				'command'=>NULL,
				'enable'=>NULL,
			),
			'enable'=>NULL,
		),
		'backup'=>array(
			'pattern'=>NULL,
			'root'=>NULL,
		),
		'clean'=>array(
			'command'=>NULL,
			'older'=>NULL,
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
			'tarball'=>array(
				'command'=>NULL,
				'entries'=>array(),
			),
		),
		'mongo'=>array(
			'dump'=>array(
				'command'=>NULL,
				'entries'=>array(),
				'tarball'=>NULL,
			),
		),
		'mysql'=>array(
			'dump'=>array(
				'command'=>NULL,
				'entries'=>array(),
				'tarball'=>NULL,
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

# ARCHIVE commands
# ----------------

# Archive command
#ARCHIVE_CMD	cd {\$source} && find . | cpio -ovF {\$target} && rm -rf {\$source}
ARCHIVE_CMD	tar --remove-files -cvhlpf {\$target} -C {\$source} .

# Compress archive command
#ARCHIVE_COMPRESS_CMD	bzip2 {\$source}
ARCHIVE_COMPRESS_CMD	gzip {\$source}

# Compress archive
#ARCHIVE_COMPRESS_ENABLE	gz
#ARCHIVE_COMPRESS_ENABLE	bz2
ARCHIVE_COMPRESS_ENABLE	no

# Tape archive (backup as one file)
#ARCHIVE_ENABLE	cpio
#ARCHIVE_ENABLE	tar
ARCHIVE_ENABLE	no

# BACKUP commands
# ---------------

# Backup pattern
BACKUP_PATTERN	{\$yyyy}{\$mm}{\$dd}

# Backup root directory
BACKUP_ROOT	{\$MB_ROOT}/backup

# CLEAN commands
# --------------

# Clean command
CLEAN_CMD	rm -rfv {\$target}

# Clean older
#CLEAN_OLDER 1209600
#CLEAN_OLDER 86400
CLEAN_OLDER	0

# EXEC commands
# -------------
# List of commands that has to be executed

# Execute after processing
#EXEC_APPEND	COMMAND?
EXEC_APPEND

# Execute before processing
#EXEC_PREPEND	COMMAND?
EXEC_PREPEND

# FILE commands
# -------------

# Clone files
#FILE_CLONE	SOURCE?
FILE_CLONE

# Clone command
FILE_CLONE_CMD	cp --parents -rfLv {\$source} {\$target}

# Tarball files
#FILE_TARBALL	SOURCE?
FILE_TARBALL

# Tarball command
FILE_TARBALL_CMD	tar -rvhlpf {\$target} {\$source}

# MONGO commands
# --------------

# Dump cluster/database/collection
#MONGO_DUMP DATABASE?
#MONGO_DUMP DATABASE?	COLLECTION?
#MONGO_DUMP USER?	PASSWORD?	DATABASE?
#MONGO_DUMP USER?	PASSWORD?	DATABASE?	COLLECTION?
#MONGO_DUMP HOST?	PORT?	USER?	PASSWORD?	DATABASE?
#MONGO_DUMP HOST?	PORT?	USER?	PASSWORD?	DATABASE?	COLLECTION?
MONGO_DUMP

# Dump command
#MONGO_DUMP_CMD	mongodump --verbose --out {\$target}
#MONGO_DUMP_CMD	mongodump --verbose --db {\$database} --collection {\$collection} --out {\$target}
#MONGO_DUMP_CMD	mongodump --verbose --user {\$user} --password {\$password} --db {\$database} --out {\$target}
#MONGO_DUMP_CMD	mongodump --verbose --user {\$user} --password {\$password} --db {\$database} --collection {\$collection} --out {\$target}
#MONGO_DUMP_CMD	mongodump --verbose --host {\$host} --port {\$port} --user {\$user} --password {\$password} --db {\$database} --out {\$target}
#MONGO_DUMP_CMD	mongodump --verbose --host {\$host} --port {\$port} --user {\$user} --password {\$password} --db {\$database} --collection {\$collection} --out {\$target}
MONGO_DUMP_CMD	mongodump --verbose --db {\$database} --out {\$target}

# Tarball dump
#MONGO_DUMP_TARBALL yes
MONGO_DUMP_TARBALL no

# MYSQL commands
# --------------

# Dump database
#MYSQL_DUMP	USER?	PASSWORD?	DATABASE?
MYSQL_DUMP

# Dump command
#MYSQL_DUMP_CMD	mysqldump --add-locks --events --verbose --all-databases -u {\$user} -p{\$password} > {\$target}
MYSQL_DUMP_CMD	mysqldump --add-locks --events --verbose -u {\$user} -p{\$password} {\$database} > {\$target}

# Tarball dump
#MYSQL_DUMP_TARBALL yes
MYSQL_DUMP_TARBALL no

#~END
ENDCFG
		);
	}
	
	private function _extract($line,$position) {
		#MB_Log()->debug('  '.__METHOD__.'()');
		$output = explode(chr(9),substr($line,$position));
		foreach ($output as $k => $v) {
			if ($v === '') unset($output[$k]);
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
		foreach ($values as $v) $message .= ','.var_export($v,TRUE);
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
			
			// Archive
			else if (substr($l,0,7) === 'ARCHIVE') {
				
				// Command
				if (substr($l,8,3) === 'CMD') {
					$v = $this->_extract($l,12);
					if (count($v) == 1) $this->__config['archive']['command'] = $v[0];
					$this->_parseDebug('ARCHIVE_CMD',$v);
				}
				
				// Compress
				else if (substr($l,8,8) === 'COMPRESS') {
					
					// Command
					if (substr($l,17,3) === 'CMD') {
						$v = $this->_extract($l,21);
						if (count($v) == 1) $this->__config['archive']['compress']['command'] = $v[0];
						$this->_parseDebug('ARCHIVE_COMPRESS_CMD',$v);
					}
					
					// Enable
					else if (substr($l,17,6) === 'ENABLE') {
						$v = $this->_extract($l,24);
						if (count($v) == 1) $this->__config['archive']['compress']['enable'] = (strtolower($v[0]) == 'no' ? FALSE : strtolower($v[0]));
						$this->_parseDebug('ARCHIVE_COMPRESS_ENABLE',$v);
					}
					
					// Any other
					else {
						$this->_parseDebug('UNKNOWN',$l);
					}
				}
				
				// Enable
				else if (substr($l,8,6) === 'ENABLE') {
					$v = $this->_extract($l,15);
					if (count($v) == 1) $this->__config['archive']['enable'] = (strtolower($v[0]) == 'no' ? FALSE : strtolower($v[0]));
					$this->_parseDebug('ARCHIVE_ENABLE',$v);
				}
				
				// Any other
				else {
					$this->_parseDebug('UNKNOWN',$l);
				}
				
			}
			
			// Clean
			else if (substr($l,0,5) === 'CLEAN') {
				
				// Command
				if (substr($l,6,3) === 'CMD') {
					$v = $this->_extract($l,10);
					if (count($v) == 1) $this->__config['clean']['command'] = $v[0];
					$this->_parseDebug('CLEAN_CMD',$v);
				}
				
				// Older
				else if (substr($l,6,5) === 'OLDER') {
					$v = $this->_extract($l,12);
					if (count($v) == 1) $this->__config['clean']['older'] = intval($v[0]);
					$this->_parseDebug('CLEAN_OLDER',$v);
				}
				
				// Any other
				else {
					$this->_parseDebug('UNKNOWN',$l);
				}
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
				
				// Tarball command
				else if (substr($l,5,11) === 'TARBALL_CMD') {
					$v = $this->_extract($l,17);
					if (count($v) == 1) $this->__config['file']['tarball']['command'] = $v[0];
					$this->_parseDebug('FILE_TARBALL_CMD',$v);
				}
				
				// Tarball
				else if (substr($l,5,7) === 'TARBALL') {
					$v = $this->_extract($l,13);
					if (count($v) == 1) $this->__config['file']['tarball']['entries'][] = $v[0];
					$this->_parseDebug('FILE_TARBALL',$v);
				}
				
				// Any other
				else {
					$this->_parseDebug('UNKNOWN',$l);
				}
			}
			
			// Mongo
			else if (substr($l,0,5) === 'MONGO') {
			
				// Dump command
				if (substr($l,6,8) === 'DUMP_CMD') {
					$v = $this->_extract($l,15);
					if (count($v) == 1) $this->__config['mongo']['dump']['command'] = $v[0];
					$this->_parseDebug('MONGO_DUMP_CMD',$v);
				}
			
				// Tarball dump command
				else if (substr($l,6,12) === 'DUMP_TARBALL') {
					$v = $this->_extract($l,19);
					if (count($v) == 1) $this->__config['mongo']['dump']['tarball'] = (strtolower($v[0]) == 'yes' ? TRUE : FALSE);
					$this->_parseDebug('MONGO_DUMP_TARBALL',$v);
				}
				
				// Clone
				else if (substr($l,6,4) === 'DUMP') {
					$v = $this->_extract($l,11);
					if (count($v) == 1) $this->__config['mongo']['dump']['entries'][] = array('host'=>NULL,'port'=>NULL,'user'=>NULL,'password'=>NULL,'database'=>$v[0],'collection'=>NULL);
					if (count($v) == 2) $this->__config['mongo']['dump']['entries'][] = array('host'=>NULL,'port'=>NULL,'user'=>NULL,'password'=>NULL,'database'=>$v[0],'collection'=>$v[1]);
					if (count($v) == 3) $this->__config['mongo']['dump']['entries'][] = array('host'=>NULL,'port'=>NULL,'user'=>$v[0],'password'=>$v[1],'database'=>$v[2],'collection'=>NULL);
					if (count($v) == 4) $this->__config['mongo']['dump']['entries'][] = array('host'=>NULL,'port'=>NULL,'user'=>$v[0],'password'=>$v[1],'database'=>$v[2],'collection'=>$v[3]);
					if (count($v) == 5) $this->__config['mongo']['dump']['entries'][] = array('host'=>$v[0],'port'=>$v[1],'user'=>$v[2],'password'=>$v[3],'database'=>$v[4],'collection'=>NULL);
					if (count($v) == 6) $this->__config['mongo']['dump']['entries'][] = array('host'=>$v[0],'port'=>$v[1],'user'=>$v[2],'password'=>$v[3],'database'=>$v[4],'collection'=>$v[5]);
					if (count($v) == 3 AND isset($v[1]) AND !empty($v[1])) $v[1] = '*****';
					if (count($v) == 4 AND isset($v[1]) AND !empty($v[1])) $v[1] = '*****';
					if (count($v) == 5 AND isset($v[3]) AND !empty($v[3])) $v[3] = '*****';
					if (count($v) == 6 AND isset($v[3]) AND !empty($v[3])) $v[3] = '*****';
					$this->_parseDebug('MONGO_DUMP',$v);
				}
				
				// Any other
				else {
					$this->_parseDebug('UNKNOWN',$l);
				}
			}
				
			
			// MySQL
			else if (substr($l,0,5) === 'MYSQL') {
		
				// Dump command
				if (substr($l,6,8) === 'DUMP_CMD') {
					$v = $this->_extract($l,15);
					if (count($v) == 1) $this->__config['mysql']['dump']['command'] = $v[0];
					$this->_parseDebug('MYSQL_DUMP_CMD',$v);
				}
		
				// Dump tarball
				else if (substr($l,6,12) === 'DUMP_TARBALL') {
					$v = $this->_extract($l,19);
					if (count($v) == 1) $this->__config['mysql']['dump']['tarball'] = (strtolower($v[0]) == 'yes' ? TRUE : FALSE);
					$this->_parseDebug('MYSQL_DUMP_TARBALL',$v);
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
		#var_dump($this->__config);exit();
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
	 * Get default config file
	 * @return string
	 */
	public function getDefault() {
		return $this->_default();
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
		if (!@file_exists($file)) {
			$file2 = MB_ROOT.DS.$file;
			if (@file_exists($file2)) $file = $file2;
			else throw new MB_Exception('Configuration file "'.$file.'" not found!');
		}
		$lines = @file($file);
		if ($lines === FALSE) throw new MB_Exception('Configuration file "'.$file.'" not readable!');
		return $this->_parse($lines);
	}
}