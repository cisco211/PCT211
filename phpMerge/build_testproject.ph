#!/usr/bin/env php
<?php
/**
 * Configuration for testproject
 */
define('PHPMERGE_DEBUG',FALSE); // Debug phpMerge
define('PHPMERGE_TARGET',NULL); // Output file target (NULL to disable)
define('PHPMERGE_TARGET_APPEND',NULL); // Code to append in target (NULL to disable)
define('PHPMERGE_TARGET_PREPEND','#!/usr/bin/env php'.chr(10)); // Code to prepend in target (NULL to disable)
define('PHPMERGE_PROJECT_ROOT',dirname(__FILE__).DIRECTORY_SEPARATOR.'testproject'); // Project root path
define('PHPMERGE_PROJECT_INDEX','index.php'); // Project index file
$PHPMERGE_PROJECT_PATH_CONSTANTS = array( // Needed when using constants inside require/include
	'ROOT'=>PHPMERGE_PROJECT_ROOT, // Project uses a constant for root path
	'DS'=>DIRECTORY_SEPARATOR, // Project uses aliased directory separator
);

// Include phpMerge
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'phpMerge.php');