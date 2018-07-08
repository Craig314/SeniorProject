#!/usr/local/bin/php
<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group: Sacramento, Ca

*/


// Command Line Program
const COMMAND_LINE_PROGRAM = true;

require_once '../libs/utility.php';
require_once '../libs/confload.php';


// Check to make sure we are running from the command line.
if (php_sapi_name() != 'cli')
{
	printErrorImmediate('This program can only be run from the command line.');
}

// Main program starts here.
echo "Configuration reload utility.\n\n";
echo "Reloading Configuration...\n";
processSharedMemoryReload();
echo "Done.\n";
var_dump($CONFIGVAR);
exit(0);


?>