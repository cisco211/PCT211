<?php
if (!defined('MB_EXEC')) exit('Invalid access!'.EOL);
MB_Log()->debug(' '.__FILE__);

#register_shutdown_function(function() {});
#register_shutdown_function(array('MB_Controller','shutdown'));

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
	 * Index action
	 */
	public function actionIndex() {
		print '__INDEX__'.EOL;
	}
	
	/**
	 * Help action
	 */
	public function actionHelp() {
		print '__HELP__'.EOL;
	}
	
	/**
	 * Router
	 */
	public function route() {
		MB_Log()->debug('  '.__METHOD__.'()');
		
		print 'Console arguments: ';
		var_export(MB_Option()->getAll());
		print EOL;
		
		// Show help
		if (MB_Option()->help === FALSE) {
			$this->action = 'Help';
			return;
		}
		
		// Show default
		$this->action = 'Index';
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