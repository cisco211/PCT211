#!/usr/bin/env php
<?php
/**
 * Configuration for testproject
 */
define('PHPMERGE_DEBUG',TRUE); // Debug phpMerge
define('PHPMERGE_TARGET','myBackup.ph'); // Output file target (NULL to disable)
define('PHPMERGE_TARGET_APPEND',NULL); // Code to append in target (NULL to disable)
define('PHPMERGE_TARGET_PREPEND','#!/usr/bin/env php'.chr(10)); // Code to prepend in target (NULL to disable)
define('PHPMERGE_PROJECT_ROOT',dirname(__FILE__)); // Project root path
define('PHPMERGE_PROJECT_INDEX','index.php'); // Project index file
$PHPMERGE_PROJECT_PATH_CONSTANTS = array( // Needed when using constants inside require/include
	'DS'=>DIRECTORY_SEPARATOR, // Project uses aliased directory separator
	'MB_ROOT'=>PHPMERGE_PROJECT_ROOT, // Project uses a constant for root path
);

// Include phpMerge
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'phpMerge.php');