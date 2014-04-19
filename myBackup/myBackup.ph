#!/usr/bin/env php
<?php
/* ---------- INDEX: /index.php' ---------- */
/**
 * CFB211 - C!$C0^211's Flexible Backup 211
 */

// Generic constants
define('DS',DIRECTORY_SEPARATOR);

// Project constants
define('MB_EXEC',TRUE);
define('MB_ROOT',dirname(__FILE__));

// Load bootstrap
/* ---------- REQUIRE_ONCE: MB_ROOT.DS.'core'.DS.'Bootstrap.php' ---------- */
if (!defined('MB_EXEC')) exit('Invalid access!'.EOL);

// No time limit
set_time_limit(0);

// PHP Options
ini_set('output_buffering',0);
ini_set('implicit_flush',1);
ini_set('zlib.output_compression',0);

// Output buffering
ob_start(NULL,2);
ob_implicit_flush(TRUE);
ob_end_flush();

// Generic constants
define('EOL',chr(13).chr(10));

// Project constants
define('MB_DEBUG',TRUE);
define('MB_NAME','MyBackup');
define('MB_START',microtime(TRUE));
define('MB_VERSION','0.5');
define('MB_XDEBUG',FALSE);

// TODO
define('MB_LOGBUFFERING',FALSE);
define('MB_LOGFILEPATTERN','Ymd');
define('MB_LOGLINEPATTERN','%datetime [%type]: %message');

/**
 * Exit function alias to support XDEBUG profiling
 */
function MB_Quit($data = NULL) {
	if (defined('MB_XDEBUG') AND MB_XDEBUG === TRUE) {
		if ($data !== NULL) print $data;
		return;
	} else {
		if ($data === NULL) exit();
		else exit($data);
	}
}

// Include Log class
/* ---------- REQUIRE_ONCE: MB_ROOT.DS.'core'.DS.'Log.class.php' ---------- */
if (!defined('MB_EXEC')) exit('Invalid access!'.EOL);

/**
 * Shortcut to Config class
 */
function MB_Log() {
	return MB_Log::getInstance();
}

/**
 * Log class DEPRECATED!!!
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
		if (MB_DEBUG AND !file_exists(MB_ROOT.DS.'log') AND !@mkdir(MB_ROOT.DS.'log')) MB_Quit('ERROR: Failed to create log directory!'.EOL);
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
MB_Log()->debug('START');
MB_Log()->debug(' '.__FILE__.':'.__LINE__);

// Include Exception class
/* ---------- REQUIRE_ONCE: MB_ROOT.DS.'core'.DS.'Exception.class.php' ---------- */
if (!defined('MB_EXEC')) exit('Invalid access!'.EOL);
MB_Log()->debug(' '.__FILE__.':'.__LINE__);

/**
 * Exception class
 */
final class MB_Exception extends ErrorException {

	/**
	 * Show error
	 */
	public static function showError($e) {
		MB_Log()->debug('  '.__METHOD__.'()');
		MB_Log()->debug(get_class($e).' "'.$e->getMessage().'" in '.$e->getFile().':'.$e->getLine().EOL.$e->getTraceAsString().EOL);
		if (MB_DEBUG) MB_Quit(get_class($e).' "'.$e->getMessage().'" in '.$e->getFile().':'.$e->getLine().EOL.$e->getTraceAsString().EOL);
		else MB_Quit('ERROR: '.$e->getMessage().EOL);
	}

	/**
	 * Error handler
	 */
	public static function errorHandler($severity,$message,$filename,$lineno) {
		MB_Log()->debug('  '.__METHOD__.'()');
		try {
			throw new MB_Exception($message,0,$severity,$filename,$lineno);
		} catch (MB_Exception $e) {
			self::showError($e);
		}
	}

	/**
	 * Shutdown handler
	 */
	public static function shutdownHandler() {
		MB_Log()->debug('  '.__METHOD__.'()');
		MB_Log()->debug('END '.round(microtime(TRUE)-MB_START,11).'s'.EOL);
	}
}

// Set error handler
set_error_handler(array('MB_Exception','errorHandler'));

// Set shutdown function
register_shutdown_function(array('MB_Exception','shutdownHandler'));

// Include Format class
/* ---------- REQUIRE_ONCE: MB_ROOT.DS.'core'.DS.'Format.class.php' ---------- */
if (!defined('MB_EXEC')) exit('Invalid access!'.EOL);
MB_Log()->debug(' '.__FILE__.':'.__LINE__);

/**
 * Shortcut to Format class
 */
function MB_Format() {
	return MB_Format::getInstance();
}

/**
 * Format class
 */
final class MB_Format {

	/**
	 * Instance variable
	 */
	private static $instance = NULL;

	/**
	 * Return class instance
	 */
	public static function getInstance() {
		MB_Log()->debug('  '.__METHOD__.'()');
		if(self::$instance === NULL) self::$instance = new MB_Format();
		return self::$instance;
	}

	/**
	 * Format any string
	 * @param string $string
	 * @param array $data
	 * @return string
	 */
	public function any($string,$data=array()) {
		$s = array();
		$r = array();
		foreach ($data as $k => $v) {
			$s[] = '{$'.$k.'}';
			$r[] = $v;
		}
		return str_replace($s,$r,$string);
	}

	/**
	 * Format generic string
	 * @param string $string
	 * @return string
	 */
	public function generic($string) {
		return $this->any($string,array(
			'DS'=>DS,
			'EOL'=>EOL,
			'MB_DEBUG'=>MB_DEBUG,
			'MB_EXEC'=>MB_EXEC,
			'MB_LOGBUFFERING'=>MB_LOGBUFFERING,
			'MB_LOGFILEPATTERN'=>MB_LOGFILEPATTERN,
			'MB_LOGLINEPATTERN'=>MB_LOGLINEPATTERN,
			'MB_NAME'=>MB_NAME,
			'MB_ROOT'=>MB_ROOT,
			'MB_START'=>MB_START,
			'MB_VERSION'=>MB_VERSION,
			'MB_XDEBUG'=>MB_XDEBUG,
			'dd'=>date('d'),
			'mm'=>date('m'),
			'yyyy'=>date('Y'),
		));
	}

	/**
	 * Format ARCHIVE_CMD string
	 * @param string $string
	 * @param string $source
	 * @param string $target
	 * @return string
	 */
	public function cmdArchive($string,$source,$target) {
		return $this->any($string,array('source'=>$source,'target'=>$target));
	}

	/**
	 * Format ARCHIVE_COMPRESS_CMD string
	 * @param string $string
	 * @param string $source
	 * @param string $target
	 * @return string
	 */
	public function cmdArchiveCompress($string,$source,$target) {
		return $this->any($string,array('source'=>$source,'target'=>$target));
	}

	/**
	 * Format CLEAN_CMD string
	 * @param string $string
	 * @param string $target
	 * @return string
	 */
	public function cmdClean($string,$target) {
		return $this->any($string,array('target'=>$target));
	}

	/**
	 * Format FILE_CLONE_CMD string
	 * @param string $string
	 * @param string $source
	 * @param string $target
	 * @return string
	 */
	public function cmdFileClone($string,$source,$target) {
		return $this->any($string,array('source'=>$source,'target'=>$target));
	}

	/**
	 * Format FILE_TARBALL_CMD string
	 * @param string $string
	 * @param string $source
	 * @param string $target
	 * @return string
	 */
	public function cmdFileTarball($string,$source,$target) {
		return $this->any($string,array('source'=>$source,'target'=>$target));
	}

	/**
	 * Format MONGO_DUMP_CMD string
	 * @param string $string
	 * @param string $host
	 * @param string $port
	 * @param string $user
	 * @param string $password
	 * @param string $database
	 * @param string $collection
	 * @param string $target
	 * @return string
	 */
	public function cmdMongoDump($string,$host,$port,$user,$password,$database,$collection,$target) {
		return $this->any($string,array('host'=>$host,'host'=>$port,'user'=>$user,'password'=>$password,'database'=>$database,'collection'=>$collection,'target'=>$target));
	}

	/**
	 * Format MYSQL_DUMP_CMD string
	 * @param string $string
	 * @param string $user
	 * @param string $password
	 * @param string $database
	 * @param string $target
	 * @return string
	 */
	public function cmdMysqlDump($string,$user,$password,$database,$target) {
		return $this->any($string,array('user'=>$user,'password'=>$password,'database'=>$database,'target'=>$target));
	}

}

// Include System class
/* ---------- REQUIRE_ONCE: MB_ROOT.DS.'core'.DS.'System.class.php' ---------- */
if (!defined('MB_EXEC')) exit('Invalid access!'.EOL);
MB_Log()->debug(' '.__FILE__.':'.__LINE__);

/**
 * Shortcut to System class
 */
function MB_System() {
	return MB_System::getInstance();
}

/**
 * System class
 */
final class MB_System {

	/**
	 * Handler to stdout
	 */
	private $stdout = NULL;

	/**
	 * Instance variable
	 */
	private static $instance = NULL;

	/**
	 * Execute command
	 */
	private function _execute($command,$buffer) {
		MB_Log()->debug('  '.__METHOD__.'()');
		$d = array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','a'));
		$p = array();
		$q = MB_Option()->quiet;
		$h = proc_open('('.$command.')2>&1',$d,$p);
		$x = array('code'=>NULL,'output'=>NULL);
		if ($h === FALSE OR !is_resource($h)) return $x;
		$s = proc_get_status($h);
		while ($s['running']) {
			if (!feof($p[1])) {
				$b = fgets($p[1],128);
				if (!$buffer AND $q !== FALSE) fwrite($this->stdout,$b);
				else $x['output'] .= $b;
			}
			#usleep(10000);
			$s = proc_get_status($h);
		}
		fclose($p[0]);
		fclose($p[1]);
		fclose($p[2]);
		proc_close($h);
		$x['code'] = $s['exitcode'];
		return $x;
	}

	private function __construct() {
		$this->stdout = fopen('php://stdout','w');
	}

	public function __destruct() {
		@fclose($this->stdout);
	}

	/**
	 * Check file existance of any file in given list
	 */
	public function anyExists() {
		MB_Log()->debug('  '.__METHOD__.'()');
		$status = FALSE;
		$argc = func_num_args();
		$argv = func_get_args();
		if ($argc == 2 AND is_array($argv[1])) {
			foreach ($argv[1] as $arg) {
				if (file_exists($argv[0].DS.$arg)) return TRUE;
			}
		} else {
			for ($i = 1; $i < $argc; $i++) {
				if (file_exists($argv[0].DS.$argv[$i])) return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Check command
	 */
	public function check($command) {
		MB_Log()->debug('  '.__METHOD__.'()');
		$r = $this->_execute('which '.$command,TRUE);
		if ($r['code'] == 0) return TRUE;
		else return FALSE;
	}

	/**
	 * Clean path
	 */
	public function clean($path,$command,$timeout = 0) {
		MB_Log()->debug('  '.__METHOD__.'()');
		if (!file_exists($path) OR !is_dir($path)) return FALSE;
		if ($timeout == 0) return TRUE;
		$items = new DirectoryIterator($path);
		foreach ($items as $item) {
			if (!$item->isDot() AND (filemtime($path.DS.$item->getFilename())+$timeout) < time()) {
				$this->_execute(MB_Format()->cmdClean($command,$path.DS.$item->getFilename()),FALSE);
			}
		}
		return TRUE;
	}

	/**
	 * Execute command
	 */
	public function execute($command,$secureCommand=NULL) {
		MB_Log()->debug('  '.__METHOD__.'()');
		$this->output('Executing "'.($secureCommand === NULL?$command:$secureCommand).'"');
		$r = $this->_execute($command,FALSE);
		return $r;
	}

	/**
	 * Return class instance
	 */
	public static function getInstance() {
		MB_Log()->debug('  '.__METHOD__.'()');
		if(self::$instance === NULL) self::$instance = new MB_System();
		return self::$instance;
	}

	/**
	 * Output data
	 */
	public function output($data = '') {
		MB_Log()->debug('  '.__METHOD__.'()');
		MB_Log()->debug($data);
		if (MB_Option()->quiet !== FALSE) fwrite($this->stdout,$data.EOL);
	}

	/**
	 * Return safe target name
	 */
	public function target($root,$name) {
		MB_Log()->debug('  '.__METHOD__.'()');
		if (!file_exists($root) OR !is_dir($root)) return $name;
		$list = array(
			$name,
			$name.'.cpio',
			$name.'.cpio.bz2',
			$name.'.cpio.gz',
			$name.'.tar',
			$name.'.tar.bz2',
			$name.'.tar.gz',
		);
		if ($this->anyExists($root,$list)) {
			$i = 2;
			foreach ($list as $k => $v) $list[$k] = str_replace($name,$name.'_'.$i,$v);
			while ($this->anyExists($root,$list)) {
				$i++;
				foreach ($list as $k => $v) $list[$k] = str_replace($name.'_'.($i-1),$name.'_'.$i,$v);
			}
			return $name.'_'.$i;
		} else return $name;
	}
}

// Include Option class
/* ---------- REQUIRE_ONCE: MB_ROOT.DS.'core'.DS.'Option.class.php' ---------- */
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

// Include Config class
/* ---------- REQUIRE_ONCE: MB_ROOT.DS.'core'.DS.'Config.class.php' ---------- */
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

# MYSQL commands
# --------------

# Dump database
#MYSQL_DUMP	USER?	PASSWORD?	DATABASE?
MYSQL_DUMP

# Dump command
#MYSQL_DUMP_CMD	mysqldump --add-locks --events --verbose --all-databases -u {\$user} -p{\$password} > {\$target}
MYSQL_DUMP_CMD	mysqldump --add-locks --events --verbose -u {\$user} -p{\$password} {\$database} > {\$target}

#~END
ENDCFG
		);
	}

	private function _extract($line,$position) {
		$output = explode(chr(9),substr($line,$position));
		foreach ($output as $k => $v) {
			if (empty($v) AND $v !== '0') unset($output[$k]);
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
			if (!empty($value) OR $value === '0') $message .= ','.var_export($value,TRUE);
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

				// Clone command
				if (substr($l,6,8) === 'DUMP_CMD') {
					$v = $this->_extract($l,15);
					if (count($v) == 1) $this->__config['mongo']['dump']['command'] = $v[0];
					$this->_parseDebug('MONGO_DUMP_CMD',$v);
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

// Include Controller class
/* ---------- REQUIRE_ONCE: MB_ROOT.DS.'core'.DS.'Controller.class.php' ---------- */
if (!defined('MB_EXEC')) exit('Invalid access!'.EOL);
MB_Log()->debug(' '.__FILE__.':'.__LINE__);

/**
 * Controller class
 */
final class MB_Controller {

	private $action = 'Index';

	/**
	 * Constructor
	 */
	public function __construct() {
		MB_Log()->debug('  '.__METHOD__.'()');

		// Check SAPI
		if (PHP_SAPI != 'cli') throw new MB_Exception('Invalid PHP_SAPI!');
	}

	/**
	 * Check action
	 */
	public function actionCheck() {
		MB_Log()->debug('  '.__METHOD__.'()');
		MB_System()->output('Checking commands...');
		$commands = array(
			'bzip2',
			'cd',
			'cp',
			'cpio',
			'echo',
			'find',
			'gzip',
			'mongodump',
			'mysqldump',
			'rm',
			'rsync',
			'tar'
		);
		foreach ($commands as $command) {
			if (MB_System()->check($command)) MB_System()->output($command.': OK');
			else MB_System()->output($command.': FAIL');
		}
		MB_System()->output('Done!');
	}

	/**
	 * Default action
	 */
	public function actionDefault() {
		MB_Log()->debug('  '.__METHOD__.'()');
		MB_System()->output(implode(chr(10),MB_Config()->getDefault()));
	}

	/**
	 * Help action
	 */
	public function actionHelp() {
		MB_Log()->debug('  '.__METHOD__.'()');
		if (str_replace(MB_ROOT.DS,'',__FILE__) == 'myBackup.ph')$c = 'myBackup.ph';
		else $c = 'php index.php';
		$f = chr(27).'[1;97m';
		$r = chr(27).'[0m';
		$u = chr(27).'[4;37m';
		MB_System()->output($f.'PCT211/myBackup v'.MB_VERSION.' by C!$C0^211'.$r);
		MB_System()->output($f.'=============================='.str_repeat('=',strlen(MB_VERSION)).$r);
		MB_System()->output();
		MB_System()->output($f.'NAME'.$r);
		MB_System()->output($f.'     '.$c.$r.' -- The PCT211/myBackup configuration file based backup script');
		MB_System()->output();
		MB_System()->output($f.'FEATURES'.$r);
		MB_System()->output('     - Configuration file based backup script.');
		MB_System()->output('     - Execute commands before and after backup actions (f.e. to stop and start services).');
		MB_System()->output('     - Customizable system-command based action system (allows to customize backup behavior).');
		MB_System()->output('     - Local file support.');
		MB_System()->output('     - MySQL dump support for databases.');
		MB_System()->output('     - MongoDB dump support for clusters, databases and collections.');
		MB_System()->output('     - Archive the backup-data (supporting cpio and tar).');
		MB_System()->output('     - Compress the archived backup-data (supporting gzip and bzip2).');
		MB_System()->output('     - Clean older backups based on timeout seconds (helps to automatically keep backup storage small).');
		MB_System()->output('     - Extremly extensible, fully-OOP backup script (ask for needed features).');
		MB_System()->output();
		MB_System()->output($f.'SYNOPSIS'.$r);
		MB_System()->output($f.'     '.$c.' '.$r.'['.$u.'options'.$r.']'.$f.' - c --check '.$r.'| '.$f.'d --default '.$r.'| '.$f.'h --help '.$r.'| '.$f.'f --file '.$r.'configuration');
		MB_System()->output();
		MB_System()->output($f.'DESCRIPTION'.$r);
		MB_System()->output('     '.$f.$c.$r.' performs backup actions using a configuration file.');
		MB_System()->output('     Remember, as always: USE AT YOUR OWN RISK!');
		MB_System()->output();
		MB_System()->output('     The last argument to '.$c.' should be an action; either one of the letters '.$f.'cdfh'.$r.', or one of the long action names.');
		MB_System()->output('     An action letter must be prefixed with "-", and may be combined with other single-letter options.');
		MB_System()->output('     A long function name must be prefixed with '.$f.'--'.$r.'.');
		MB_System()->output('     Some options take a parameter; with the single-letter form these must be given as separate arguments.');
		MB_System()->output('     With the long form, they may be given by appending ='.$u.'value'.$r.' to the option.');
		MB_System()->output();
		MB_System()->output($f.'ACTIONS'.$r);
		MB_System()->output('     Main operation mode:');
		MB_System()->output();
		MB_System()->output($f.'     -c'.$r.', '.$f.'--check'.$r);
		MB_System()->output('          check environment');
		MB_System()->output();
		MB_System()->output($f.'     -d'.$r.', '.$f.'--default'.$r);
		MB_System()->output('          return default configuration');
		MB_System()->output();
		MB_System()->output($f.'     -h'.$r.', '.$f.'--help'.$r);
		MB_System()->output('          show (this) help');
		MB_System()->output();
		MB_System()->output($f.'     -f configuration'.$r.', '.$f.'--file='.$r.$u.'configuration'.$r);
		MB_System()->output('          run configuration file');
		MB_System()->output();
		MB_System()->output($f.'OPTIONS'.$r);
		MB_System()->output('     Operation modifiers:');
		MB_System()->output();
		MB_System()->output($f.'     -q'.$r.', '.$f.'--quiet'.$r);
		MB_System()->output('          suppresses all output');
		MB_System()->output();
		MB_System()->output($f.'CONFIGURATION'.$r);
		MB_System()->output('     The configuration files are self-explanatory!');
		MB_System()->output('     To show the default configuration with descriptions, use the '.$f.'-d'.$r.' / '.$f.'--default'.$r.' switch.');
		MB_System()->output('     To create a new configuration file, simply redirect the output of '.$f.'-d'.$r.' / '.$f.'--default'.$r.' switch to a file.');
		MB_System()->output();
		MB_System()->output($f.'EXAMPLES'.$r);
		MB_System()->output('     Check the environment.');
		MB_System()->output('          '.$c.' -c');
		MB_System()->output('     Create new configuration file using internal default.');
		MB_System()->output('          '.$c.' -d > myBackup.conf');
		MB_System()->output('     Run myBackup.conf silently.');
		MB_System()->output('          '.$c.' -qf myBackup.conf');
		MB_System()->output('     Run myBackup.conf as cronjob.');
		MB_System()->output('          0 0 * * * '.MB_ROOT.DS.$c.' -f myBackup.conf > '.MB_ROOT.'/myBackup.log');
		MB_System()->output();
		MB_System()->output($f.'AUTHOR'.$r);
		MB_System()->output('     PCT211/myBackup v'.MB_VERSION.' "'.$f.$c.$r.'"');
		MB_System()->output('     Made by C!$C0^211 ('.$u.'http://cisco211.de'.$r.')');
		MB_System()->output('     USE AT YOUR OWN RISK SOFTWARE!');
		MB_System()->output();
		MB_System()->output($f.'~END'.$r);
	}

	/**
	 * Index action
	 */
	public function actionIndex() {
		MB_Log()->debug('  '.__METHOD__.'()');

		// Config file
		if (MB_Option()->file === NULL) return $this->actionHelp();
		MB_System()->output('PCT211/myBackup v'.MB_VERSION.' by C!$C0^211');
		MB_System()->output('=============================='.str_repeat('=',strlen(MB_VERSION)));
		MB_System()->output('@'.date('Y-m-d H:i:s').' Processing...');
		MB_System()->output('Configuration file: "'.MB_Option()->file.'"');

		// Build root path
		$backupRoot = MB_Format()->generic(MB_Config()->get('backup.root'));
		MB_System()->output('Backup root path: "'.$backupRoot.'"');

		// Target name
		$targetName = MB_System()->target($backupRoot,MB_Format()->generic(MB_Config()->get('backup.pattern')));
		if (strpos($targetName,'_') !== FALSE) MB_System()->output('Target already exists, using: "'.$targetName.'"');
		else MB_System()->output('Target name: "'.$targetName.'"');

		// Create target
		if (!mkdir($backupRoot.DS.$targetName,0770,TRUE)) throw new MB_Exception('Failed to create target directory "'.$backupRoot.DS.$targetName.'"!');

		// Prepend execution
		$prepends = MB_Config()->get('exec.prepend');
		if (count($prepends) > 0) {
			MB_System()->output('Executing prepend script(s)...');
			foreach ($prepends as $prepend) {
				$c = MB_Format()->generic($prepend);
				MB_System()->execute($c);
			}
		}

		// FILE_CLONE
		$fileClones = MB_Config()->get('file.clone.entries');
		if (count($fileClones) > 0) {
			MB_System()->output('Processing file clone(s)...');
			$fileCloneCommand = MB_Config()->get('file.clone.command');
			if (!mkdir($backupRoot.DS.$targetName.DS.'FILE_CLONE',0770,TRUE)) throw new MB_Exception('Failed to create target directory "'.$backupRoot.DS.$targetName.DS.'FILE_CLONE"!');
			foreach ($fileClones as $fileClone) {
				$c = MB_Format()->cmdFileClone($fileCloneCommand,$fileClone,$backupRoot.DS.$targetName.DS.'FILE_CLONE');
				MB_System()->execute($c);
			}
		}

		// FILE_TARBALL
		$fileTarballs = MB_Config()->get('file.tarball.entries');
		if (count($fileTarballs) > 0) {
			MB_System()->output('Processing file tarball(s)...');
			$fileTarballCommand = MB_Config()->get('file.tarball.command');
			foreach ($fileTarballs as $fileTarball) {
				$c = MB_Format()->cmdFileTarball($fileTarballCommand,$fileTarball,$backupRoot.DS.$targetName.DS.'FILE_TARBALL.tar');
				MB_System()->execute($c);
			}
		}

		// MONGO_DUMP
		$mongoDumps = MB_Config()->get('mongo.dump.entries');
		if (count($mongoDumps) > 0) {
			MB_System()->output('Processing mongo dump(s)...');
			$mongoDumpCommand = MB_Config()->get('mongo.dump.command');
			if (!mkdir($backupRoot.DS.$targetName.DS.'MONGO_DUMP',0770,TRUE)) throw new MB_Exception('Failed to create target directory "'.$backupRoot.DS.$targetName.DS.'MONGO_DUMP"!');
			foreach ($mongoDumps as $mongoDump) {
				$c = MB_Format()->cmdMongoDump($mongoDumpCommand,$mongoDump['host'],$mongoDump['port'],$mongoDump['user'],$mongoDump['password'],$mongoDump['database'],$mongoDump['collection'],$backupRoot.DS.$targetName.DS.'MONGO_DUMP');
				$c2 = str_replace($mongoDump['password'],'*****',$c);
				MB_System()->execute($c,$c2);
			}
		}

		// MYSQL_DUMP
		$mysqlDumps = MB_Config()->get('mysql.dump.entries');
		if (count($mysqlDumps) > 0) {
			MB_System()->output('Processing mysql dump(s)...');
			$mysqlDumpCommand = MB_Config()->get('mysql.dump.command');
			if (!mkdir($backupRoot.DS.$targetName.DS.'MYSQL_DUMP',0770,TRUE)) throw new MB_Exception('Failed to create target directory "'.$backupRoot.DS.$targetName.DS.'MYSQL_DUMP"!');
			foreach ($mysqlDumps as $mysqlDump) {
				$c = MB_Format()->cmdMysqlDump($mysqlDumpCommand,$mysqlDump['user'],$mysqlDump['password'],$mysqlDump['database'],$backupRoot.DS.$targetName.DS.'MYSQL_DUMP'.DS.$mysqlDump['database'].'.sql');
				$c2 = str_replace($mysqlDump['password'],'*****',$c);
				MB_System()->execute($c,$c2);
			}
		}

		// ARCHIVE
		$archiveExtension = MB_Config()->get('archive.enable');
		if ($archiveExtension !== FALSE) {
			MB_System()->output('Executing archiver...');
			$archiveCommand = MB_Config()->get('archive.command');
			$c = MB_Format()->cmdArchive($archiveCommand,$backupRoot.DS.$targetName,$backupRoot.DS.$targetName.'.'.$archiveExtension);
			MB_System()->execute($c);
		}

		// ARCHIVE_COMPRESS
		$compressExtension = MB_Config()->get('archive.compress.enable');
		if ($archiveExtension !== FALSE AND $compressExtension !== FALSE) {
			MB_System()->output('Executing archive compression...');
			$compressCommand = MB_Config()->get('archive.compress.command');
			$c = MB_Format()->cmdArchiveCompress($compressCommand,$backupRoot.DS.$targetName.'.'.$archiveExtension,$backupRoot.DS.$targetName.'.'.$archiveExtension.'.'.$compressExtension);
			MB_System()->execute($c);
		}

		// CLEAN
		$cleanOlder = MB_Config()->get('clean.older');
		if ($cleanOlder > 0) {
			$cleanCommand = MB_Config()->get('clean.command');
			MB_System()->output('Executing cleaner...');
			MB_System()->clean($backupRoot,$cleanCommand,$cleanOlder);
		}

		// Append execution
		$appends = MB_Config()->get('exec.append');
		if (count($appends) > 0) {
			MB_System()->output('Executing append script(s)...');
			foreach ($appends as $append) {
				$c = MB_Format()->generic($append);
				MB_System()->execute($c);
			}
		}

		// Done
		MB_System()->output('Runtime: '.round(microtime(TRUE)-MB_START,3).' seconds!');
		MB_System()->output('@'.date('Y-m-d H:i:s').' Done!');
	}

	/**
	 * Unknown action
	 */
	public function actionUnknown() {
		MB_Log()->debug('  '.__METHOD__.'()');
		global $argv;
		MB_System()->output('Invalid action!');
		MB_System()->output('»'.$argv[0].' -h« to show help.');
	}

	/**
	 * Router
	 */
	public function route() {
		MB_Log()->debug('  '.__METHOD__.'()');

		#print 'Console arguments: ';var_export(MB_Option()->getAll());print EOL;

		// Load configuration
		MB_Config();
		if (MB_Option()->file !== NULL) {
			MB_Config()->read(MB_Option()->file);
			$this->action = 'Index';
			return;
		}

		// Do check
		if (MB_Option()->check === FALSE) {
			$this->action = 'Check';
			return;
		}

		// Do default
		if (MB_Option()->default === FALSE) {
			$this->action = 'Default';
			return;
		}

		// Do help
		if (MB_Option()->help === FALSE) {
			$this->action = 'Help';
			return;
		}
		// Do default
		$this->action = 'Unknown';
	}

	/**
	 * Runner
	 */
	public function run() {
		MB_Log()->debug('  '.__METHOD__.'()');
		if ($this->runAction()) return;
		if($this->runNotFound()) return;
	}

	/**
	 * Action runner
	 */
	public function runAction() {
		MB_Log()->debug('  '.__METHOD__.'()');
		if (empty($this->action)) $this->action = 'Index';
		if (method_exists($this,'action'.$this->action)) {
			$method = 'action'.$this->action;
			$this->$method();
			return TRUE;
		} else return FALSE;
	}

	/**
	 * Not found runner
	 */
	public function runNotFound() {
		MB_Log()->debug('  '.__METHOD__.'()');
		throw new MB_Exception('Action \''.$this->action.'\' not found!');
		return TRUE;
	}

}

// Run
try {
	$c = new MB_Controller();
	$c->route();
	$c->run();
} catch (Exception $e) {
	MB_Exception::showError($e);
}
/* --------- END  ---------- */
