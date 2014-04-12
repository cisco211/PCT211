<?php
if (!defined('MB_EXEC')) exit('Invalid access!'.EOL);
MB_Log()->debug(' '.__FILE__.':'.__LINE__);

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
	 * Index action (deprecated)
	 */
	public function actionIndex() {
		MB_Log()->debug('  '.__METHOD__.'()');
		
		// Output helperle ^^
		$m = create_function('$a','MB_Log()->info($a);print $a.EOL;');
		
		// Config file info
		if (MB_Option()->file !== NULL) {
			$m('Processing...');
			$m('Configuration file: "'.MB_Option()->file.'"');
		}
		else {
			return $this->actionHelp();
		}
		
		// Build root path
		$backupRoot = MB_Format()->generic(MB_Config()->get('backup.root'));
		$m('Backup root path: "'.$backupRoot.'"');
		
		// Target name
		$targetName = MB_Format()->generic(MB_Config()->get('backup.pattern'));
		if (file_exists($backupRoot.DS.$targetName)) {
			$i = 2;
			while (file_exists($backupRoot.DS.$targetName.'_'.$i)) { $i++; }
			$targetName .= '_'.$i;
			$m('Target already exists, using: "'.$targetName.'"');
		} else {
			$m('Target name: "'.$targetName.'"');
		}
		
		// Create target
		if (!mkdir($backupRoot.DS.$targetName,0770,TRUE)) throw new MB_Exception('Failed to create target directory "'.$backupRoot.DS.$targetName.'"!');
		
		// Prepend execution
		$m('Executing prepend script(s)...');
		$prepends = MB_Config()->get('exec.prepend');
		foreach ($prepends as $prepend) {
			$c = MB_Format()->generic($prepend);
			$m('Executing "'.$c.'"');
			system($c);
		}
		
		// FILE_CLONE
		$m('Processing file clone(s)...');
		$fileCloneCommand = MB_Config()->get('file.clone.command');
		if (!mkdir($backupRoot.DS.$targetName.DS.'FILE_CLONE',0770,TRUE)) throw new MB_Exception('Failed to create target directory "'.$backupRoot.DS.$targetName.DS.'FILE_CLONE"!');
		$fileClones = MB_Config()->get('file.clone.entries');
		foreach ($fileClones as $fileClone) {
			$c = MB_Format()->cmdFileClone($fileCloneCommand,$fileClone,$backupRoot.DS.$targetName.DS.'FILE_CLONE');
			$m('Executing "'.$c.'"');
			system($c);
		}
		
		// MYSQL_DUMP
		$m('Processing mysql dump(s)...');
		$mysqlDumpCommand = MB_Config()->get('mysql.dump.command');
		if (!mkdir($backupRoot.DS.$targetName.DS.'MYSQL_DUMP',0770,TRUE)) throw new MB_Exception('Failed to create target directory "'.$backupRoot.DS.$targetName.DS.'MYSQL_DUMP"!');
		$mysqlDumps = MB_Config()->get('mysql.dump.entries');
		foreach ($mysqlDumps as $mysqlDump) {
			$c = MB_Format()->cmdMysqlDump($mysqlDumpCommand,$mysqlDump['user'],$mysqlDump['password'],$mysqlDump['database'],$backupRoot.DS.$targetName.DS.'MYSQL_DUMP'.DS.$mysqlDump['database'].'.sql');
			$c2 = str_replace($mysqlDump['password'],'*****',$c);
			$m('Executing "'.$c2.'"');
			system($c);
		}
		
		// Append execution
		$m('Executing append script(s)...');
		$prepends = MB_Config()->get('exec.append');
		foreach ($prepends as $append) {
			$c = MB_Format()->generic($append);
			$m('Executing "'.$c.'"');
			system($c);
		}
		
		// ARCHIVE
		
		// COMPRESS
		
		// CLEAN
	}
	
	/**
	 * Help action
	 */
	public function actionHelp() {
		MB_Log()->debug('  '.__METHOD__.'()');
		print '__HELP__'.EOL;
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
		}
		
		// Do help
		if (MB_Option()->help === FALSE) {
			$this->action = 'Help';
			return;
		}
		
		// Do default
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