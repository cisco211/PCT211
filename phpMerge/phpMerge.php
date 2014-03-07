<?php
/**
 * phpMerge by C!$C0^211
 */

// When running this in cli
if (PHP_SAPI == 'cli') {
	
	// Settings
	if (!defined('PHPMERGE_DEBUG')) define('PHPMERGE_DEBUG',TRUE); // Debug phpMerge
	if (!defined('PHPMERGE_TARGET')) define('PHPMERGE_TARGET',NULL); // Output file target (NULL to disable)
	if (!defined('PHPMERGE_TARGET_APPEND')) define('PHPMERGE_TARGET_APPEND',NULL); // Code to append in target (NULL to disable)
	if (!defined('PHPMERGE_TARGET_PREPEND')) define('PHPMERGE_TARGET_PREPEND',NULL); // Code to prepend in target (NULL to disable)
	if (!defined('PHPMERGE_PROJECT_ROOT')) define('PHPMERGE_PROJECT_ROOT',dirname(__FILE__)); // Project root path
	if (!defined('PHPMERGE_PROJECT_INDEX')) define('PHPMERGE_PROJECT_INDEX','index.php'); // Project index file
	if (!isset($PHPMERGE_PROJECT_PATH_CONSTANTS)) $PHPMERGE_PROJECT_PATH_CONSTANTS = array( // Needed when using constants inside require/include
	);
	
	// Helper variables
	$m = NULL;
	$o = NULL;
	$d = NULL;
	
	// Execute
	try {
		$m = new phpMerge(PHPMERGE_PROJECT_ROOT,PHPMERGE_PROJECT_INDEX,$PHPMERGE_PROJECT_PATH_CONSTANTS);
		$m->debug(PHPMERGE_DEBUG);
		$m->run();
		$o = $m->output();
		$d = $m->debug();
		if (PHPMERGE_TARGET !== NULL) $m->write(PHPMERGE_TARGET,PHPMERGE_TARGET_PREPEND,PHPMERGE_TARGET_APPEND);
	} catch(Exception $e) {
		if (PHPMERGE_DEBUG) exit('ERROR: '.get_class($e).' "'.$e->getMessage().'" in '.$e->getFile().':'.$e->getLine().chr(13).chr(10).$e->getTraceAsString().chr(13).chr(10));
		else exit('ERROR: '.$e->getMessage().chr(13).chr(10));
	}
	
	// Debug output
	if (PHPMERGE_DEBUG) print 'DEBUG:'.chr(13).chr(10).'======'.chr(13).chr(10).$d.chr(13).chr(10);
	
	// Data output
	if (PHPMERGE_TARGET === NULL) print 'OUTPUT:'.chr(13).chr(10).'======='.chr(13).chr(10).$o;
	else print 'OUTPUT:'.chr(13).chr(10).'======='.chr(13).chr(10).'Has been written to "'.PHPMERGE_TARGET.'"!'.chr(13).chr(10);
	print chr(13).chr(10);
	
	// Done
	exit(0);
}

/**
 * phpMerge class
 */
final class phpMerge {
	
	/**
	 * Debug output
	 */
	private $debug = '';
	
	/**
	 * Debug switch
	 */
	private $debugEnable = FALSE;
	
	/**
	 * Project index file
	 */
	private $projectIndex = NULL;
	
	/**
	 * Project path constants
	 */
	private $projectPathConstants = array();
	
	/**
	 * Project root path
	 */
	private $projectRoot = NULL;
	
	/**
	 * List of files that got included once already
	 */
	private $once = array();
	
	/**
	 * Output
	 */
	private $output = '';
	
	/**
	 * Debug output
	 */
	private function _debug($keyword,$message) {
		if ($this->debugEnable) $this->debug .= strtoupper($keyword).' '.$message.chr(13).chr(10);
	}
	
	/**
	 * Parse path in php code
	 */
	private function _parsePath($input) {
		$search = array();
		$replace = array();
		foreach ($this->projectPathConstants as $s => $r) {
			$search[] = $s.'.';			$replace[] = '\''.$r.'\'.';
			$search[] = '.'.$s;			$replace[] = '.\''.$r.'\'';
			$search[] = '\'.'.$s.'.\'';	$replace[] = '\''.$r.'\'';
			$search[] = $s.'.\'';		$replace[] = '\''.$r;
			$search[] = '\'.'.$s;		$replace[] = $r.'\'';
			$search[] = $s;				$replace[] = '\''.$r.'\'';
		}
		$search[] = '\'.\'';
		$replace[] = '';
		return trim(str_replace($search,$replace,$input),'\'"');
	}
	
	/**
	 * Merge processor
	 */
	private function _process($file,$index) {
		
		// Show progress on cli
		if (PHP_SAPI) print 'Processing '.str_replace($this->projectRoot.DIRECTORY_SEPARATOR,'',$file).chr(13).chr(10);
		
		// Inside php code or not
		$inside = FALSE;
		
		// Lines of given file
		if (!file_exists($file)) throw new Exception('File "'.$file.'" not found!');
		$lines = file($file);
		
		// Line counter
		$i = 1;
		
		// Iterate over lines
		foreach ($lines as $line) {
			
			// Position of php open tag
			$tag_open = strrpos($line,'<?php',0);
			
			// Position of php close tag
			$tag_close = strrpos($line,'?>',0);
			
			// Detect PHP tag(s)
			if ($tag_open !== FALSE OR $tag_close !== FALSE) {
				
				// Only open
				if ($tag_open !== FALSE AND $tag_close === FALSE) $inside = TRUE;
				
				// Only close
				else if ($tag_open === FALSE AND $tag_close !== FALSE) $inside = FALSE;
				
				// Open after close
				else if ($tag_open > $tag_close) $inside = TRUE;
				
				// Close after open
				else $inside = FALSE;
				
				// Debug output
				$this->_debug('phptag',str_replace($this->projectRoot.DIRECTORY_SEPARATOR,'',$file).':'.$i.'> Open: '.(is_bool($tag_open)?'-':$tag_open).' Close: '.(is_bool($tag_close)?'-':$tag_close).' Inside: '.($inside?'Yes':'No'));
			}
			
			// First line of output
			if ($index AND $i == 1) {
				$inside = TRUE;
				$this->output = '';
				
				// Append 
				$this->output .= '<?php'.chr(13).chr(10).'/* ---------- INDEX: '.str_replace($this->projectRoot,'',$file).'\''.' ---------- */'.chr(13).chr(10);
			
			// Include
			} else if (preg_match('/(include|require)\((.*)\);/',$line,$m)===1 OR preg_match('/(include|require) (.*);/',$line,$m)===1) {
				
				// Helper variables
				$code = $m[0];
				$keyword = $m[1];
				$rawpath = $m[2];
				$path = $this->_parsePath($rawpath);
				
				// Debug output
				$this->_debug($keyword,str_replace($this->projectRoot,'',$file).':'.$i.'> '.$rawpath.' -> '.$path);
				
				// Append include info header
				$this->output .= '/* ---------- '.strtoupper($keyword).': '.$rawpath.' ---------- */';
				
				// Append php close tag when inside php code
				if ($inside) $this->output .= '?>';
				
				// Process given path
				$this->_process($path,FALSE);
			
			// Include once
			} else if (preg_match('/(require_once|include_once)\((.*)\);/',$line,$m)===1 OR preg_match('/(require_once|include_once) (.*);/',$line,$m)===1) {
				
				// Helper variables
				$code = $m[0];
				$keyword = $m[1];
				$rawpath = $m[2];
				$path = $this->_parsePath($rawpath);
				
				// File were already included
				if (in_array($path,$this->once)) {
					
					// Debug output
					$this->_debug($keyword,str_replace($this->projectRoot,'',$file).':'.$i.'> '.$rawpath.' -> Skipped, already included!');
					
					// Append include info header
					$this->output .= '/* ---------- '.strtoupper($keyword).': '.$rawpath.' ---------- */'.chr(13).chr(10);
					$this->output .= '//'.rtrim($line).' -> Already included'.chr(13).chr(10);
					
				// Include file only once
				} else {
					
					// Add path to list
					$this->once[] = $path;
					
					// Debug output
					$this->_debug($keyword,str_replace($this->projectRoot,'',$file).':'.$i.'> '.$rawpath.' -> '.$path);
					
					// Append include info header
					$this->output .= '/* ---------- '.strtoupper($keyword).': '.$rawpath.' ---------- */';
					
					// Append php close tag when inside php code
					if ($inside) $this->output .= '?>';
					
					// Process given path
					$this->_process($path,FALSE);
				}
			
			// Normal line
			} else $this->output .= rtrim($line).chr(13).chr(10);
			
			// Next line
			$i++;
		}
		
		// Finish output
		if ($index) {
			
			// Remove pointless stuff
			$this->output = str_replace(array('?><?php'),array(''),$this->output);
			
			// Add end info
			$this->output .= '/* --------- END  ---------- */'.chr(13).chr(10);
		}
		
		// Done
		return TRUE;
	}
	
	/**
	 * Constructor
	 */
	public function __construct($projectRoot,$projectIndex,$projectPathConstants=array()) {
		if (!file_exists($projectRoot) OR !is_dir($projectRoot)) throw new Exception('Project root not found!');
		if (!is_dir($projectRoot)) throw new Exception('Project root must be a directory!');
		if (!file_exists($projectRoot.DIRECTORY_SEPARATOR.$projectIndex)) throw new Exception('Project index not found!');
		if (!is_file($projectRoot.DIRECTORY_SEPARATOR.$projectIndex)) throw new Exception('Project index must be a file!');
		$this->projectRoot = $projectRoot;
		$this->projectIndex = $projectIndex;
		$this->projectPathConstants = $projectPathConstants;
		if (PHP_SAPI) print chr(13).chr(10).'phpMerge started!'.chr(13).chr(10).'Project root: '.$this->projectRoot.chr(13).chr(10).'Project index: '.$this->projectIndex.chr(13).chr(10);
	}
	
	/**
	 * Debug output
	 */
	public function debug($switch=NULL) {
		if ($switch !== NULL) {
			$this->debugEnable = (boolean)$switch;
			return $this->debugEnable;
		} else return $this->debug;
	}
	
	/**
	 * Get output
	 */
	public function output() {
		return $this->output;
	}
	
	/**
	 * Run merger
	 */
	public function run() {
		$result = $this->_process($this->projectRoot.DIRECTORY_SEPARATOR.$this->projectIndex,TRUE);
		if (PHP_SAPI) print 'phpMerge finished!'.chr(13).chr(10).chr(13).chr(10);
	}
	
	/**
	 * Write to file
	 */
	public function write($file,$prepend = NULL,$append = NULL) {
		if (empty($this->output)) throw new Exception('There is no output yet!');
		return file_put_contents($file,($prepend===NULL?'':$prepend).$this->output.($append===NULL?'':$append));
	}
}