<?php
if (!defined('MB_EXEC')) exit('Invalid access!'.EOL);

// No time limit
set_time_limit(0);

// Generic constants
define('EOL',chr(13).chr(10));

// Project constants
define('MB_DEBUG',TRUE);
define('MB_NAME','MyBackup');
define('MB_START',microtime(TRUE));
define('MB_VERSION','0.1');
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
require_once(MB_ROOT.DS.'core'.DS.'Log.class.php');
MB_Log()->debug('START');
MB_Log()->debug(' '.__FILE__.':'.__LINE__);

// Include Exception class
require_once(MB_ROOT.DS.'core'.DS.'Exception.class.php');

// Include Option class
require_once(MB_ROOT.DS.'core'.DS.'Option.class.php');

// Include Config class
require_once(MB_ROOT.DS.'core'.DS.'Config.class.php');

// Include Controller class
require_once(MB_ROOT.DS.'core'.DS.'Controller.class.php');

// Run
try {
	$c = new MB_Controller();
	$c->route();
	$c->run();
} catch (Exception $e) {
	MB_Exception::showError($e);
}