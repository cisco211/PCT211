<?php
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