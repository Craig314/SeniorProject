#!/usr/local/bin/php
<?php


/*

SEA-CORE International Ltd.
SEA-CORE Development Group: Sacramento, Ca

This program creates/updates the user as defined in the configuration
settings.  The user is created in the database along with the profile
if necessary.

This program can only be run from the command line.  It will
terminate with an error condition if an attempt is made to access it
from a web server.

*/




// Command Line Program
const COMMAND_LINE_PROGRAM = true;

require_once '../libs/utility.php';
require_once '../libs/confload.php';
require_once '../libs/password.php';



// ****************************************************************************
// ****************** DO NOT MODIFY ANYTHING BELOW THIS LINE ******************
// ****************************************************************************



// Check to make sure we are running from the command line.
if (php_sapi_name() != 'cli')
{
	printErrorImmediate('This program can only be run from the command line.');
}


// Prompt the user for their password on the command line.
function getPassword()
{
	global $CONFIGVAR;
	$minpass = $CONFIGVAR['security_passwd_minlen']['value'];
	$maxpass = $CONFIGVAR['security_passwd_maxlen']['value'];

	echo 'Please enter a password between ' .
		$CONFIGVAR['security_passwd_minlen']['value'] . ' and ' .
		$CONFIGVAR['security_passwd_maxlen']['value'] . " characters.\n";
	do
	{
		$flag = 0;
		$pass1 = readline('Enter new password: ');
		$pass2 = readline('Enter new password again: ');
		if (strcmp($pass1, $pass2) != 0)
		{
			echo "Passwords do not match. Try again.\n";
			continue;
		}
		else $flag++;
		$length = strlen($pass1);
		if ($length < $minpass)
		{
			echo 'Password is too short.  Minimum length is '
				. $minpass . " characters.\n";
			continue;
		}
		else $flag++;
		if ($length > $maxpass)
		{
			echo 'Password is too long.  Maximum length is '
				. $maxpass . " characters.\n";
			continue;
		}
		else $flag++;
	} while ($flag < 3);
	return $pass1;
}


// MAIN PROGRAM STARTS HERE

$hexsalt = NULL;
$hexpass = NULL;
$digest = NULL;

echo "\nPassword Hashing Utility\n\n";
$passwd = getPassword();
$count = $CONFIGVAR['security_hash_rounds']['value'];
$password->encryptNew($passwd, $hexsalt, $hexpass, $digest, $count);

echo "\n";
echo "Password: " . $passwd . "\n";
echo "Digest:   " . $digest . "\n";
echo "Count:    " . $count . "\n";
echo "Hex Salt: " . $hexsalt . "\n";
echo "Hex Pass: " . $hexpass . "\n";
echo "\n";

?>