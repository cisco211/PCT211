<?php
define('ROOT',dirname(__FILE__));
define('DS',DIRECTORY_SEPARATOR);
define('EOL',chr(13).chr(10));
print 'Content of index.php'.EOL;
include(ROOT.DS.'here'.DS.'foo.php');
require(ROOT.DS.'here'.DS.'bar.php');
include_once(ROOT.DS.'here'.DS.'baz.php');
require_once(ROOT.DS.'here'.DS.'raz.php');