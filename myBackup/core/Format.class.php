<?php
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