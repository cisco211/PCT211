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
	 * Instance variable
	 */
	private static $instance = NULL;
	
	/**
	 * Execute command
	 */
	private function _execute($command) {
		MB_Log()->debug('  '.__METHOD__.'()');
		$c = NULL;
		$o = array();
		$r = exec($command,$o,$c);
		return array('code'=>$c,'result'=>$r,'output'=>$o);
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
		$r = $this->_execute('which '.$command);
		if ($r['code'] == 0 AND strlen($r['result']) > 0 AND substr($r['result'],0,1) == '/') return TRUE;
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
			if (!$item->isDot() AND time() > filemtime($path.DS.$item->getFilename())+$timeout) {
				$this->execute(MB_Format()->cmdClean($command,$path.DS.$item->getFilename()));
			}
		}
		return TRUE;
	}
	
	/**
	 * Execute command
	 */
	public function execute($command,$secureCommand=NULL) {
		MB_Log()->debug('  '.__METHOD__.'()');
		$r = $this->_execute($command);
		$this->output('Executing "'.($secureCommand === NULL?$command:$secureCommand).'"');
		$this->output(implode(EOL,$r['output']));
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
		if (MB_Option()->quiet !== FALSE) print $data.EOL;
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