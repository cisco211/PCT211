<?php
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
		$fileTarballCommand = MB_Config()->get('file.tarball.command');
		if (count($fileTarballs) > 0) {
			MB_System()->output('Processing file tarball(s)...');
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
		
		// MONGO_DUMP_TARBALL
		$mongoDumpTarball = MB_Config()->get('mongo.dump.tarball');
		if (count($mongoDumps) > 0 AND $mongoDumpTarball) {
			MB_System()->output('Tarball mongo dump(s)...');
			$archiveCommand = MB_Config()->get('archive.command');
			$c = MB_Format()->cmdArchive($archiveCommand,$backupRoot.DS.$targetName.DS.'MONGO_DUMP',$backupRoot.DS.$targetName.DS.'MONGO_DUMP.tar');
			MB_System()->execute($c);
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
		
		// MYSQL_DUMP_TARBALL
		$mysqlDumpTarball = MB_Config()->get('mysql.dump.tarball');
		if (count($mysqlDumps) > 0 AND $mysqlDumpTarball) {
			MB_System()->output('Tarball mysql dump(s)...');
			$archiveCommand = MB_Config()->get('archive.command');
			$c = MB_Format()->cmdArchive($archiveCommand,$backupRoot.DS.$targetName.DS.'MYSQL_DUMP',$backupRoot.DS.$targetName.DS.'MYSQL_DUMP.tar');
			MB_System()->execute($c);
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
	 * Version action
	 */
	public function actionVersion() {
		MB_Log()->debug('  '.__METHOD__.'()');
		if (str_replace(MB_ROOT.DS,'',__FILE__) == 'myBackup.ph')$c = 'myBackup.ph';
		else $c = 'php index.php';
		MB_System()->output('PCT211/myBackup v'.MB_VERSION.' "'.$c.'"');
		MB_System()->output('Made by C!$C0^211 (http://cisco211.de)');
		MB_System()->output('USE AT YOUR OWN RISK SOFTWARE!');
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
		
		// Do help
		if (MB_Option()->version === FALSE) {
			$this->action = 'Version';
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