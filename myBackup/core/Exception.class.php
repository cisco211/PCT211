<?php
if (!defined('MB_EXEC')) exit('Invalid access!'.EOL);
MB_Log()->debug(' '.__FILE__);

/**
 * Exception class
 */
final class MB_Exception extends ErrorException {
	
	/**
	 * Show error
	 */
	public static function showError($e) {
		MB_Log()->debug('  '.__METHOD__.'()');
		MB_Log()->error(get_class($e).' "'.$e->getMessage().'" in '.$e->getFile().':'.$e->getLine().EOL.$e->getTraceAsString().EOL);
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
