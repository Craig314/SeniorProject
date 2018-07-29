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

const BASEDIR = '../libs/';
require_once BASEDIR . 'utility.php';
require_once BASEDIR . 'confload.php';
require_once BASEDIR . 'dbaseuser.php';
require_once BASEDIR . 'password.php';
require_once BASEDIR . 'security.php';

// Configuration Settings
const ACCOUNT_NAME = 'admin';	// Must be either "vendor" or "admin" (case sensitive)
const PROFILE_NAME = 'Admin';	// Must be either "Vendor" or "Admin" (case sensitive)



// ****************************************************************************
// ****************** DO NOT MODIFY ANYTHING BELOW THIS LINE ******************
// ****************************************************************************



// Check to make sure we are running from the command line.
if (php_sapi_name() != 'cli')
{
	printErrorImmediate('This program can only be run from the command line.');
}


// ** DO NOT MESS WITH THESE **
const PROFILE_PARAMETER = 'profile_id_' . ACCOUNT_NAME;
const ACCOUNT_PARAMETER = 'account_id_' . ACCOUNT_NAME;


// Creates or updates the login table record.
function dbaseTableLogin($userid, $passwd)
{
	global $CONFIGVAR;
	global $password;
	global $dbuser;
	$hexsalt = NULL;
	$hexpass = NULL;
	$digest = NULL;
	$count = $CONFIGVAR['security_hash_rounds']['value'];
	password::encryptNew($passwd, $hexsalt, $hexpass, $digest, $count);

	$result = $dbuser->queryLogin($userid);

	if ($result != false && is_array($result))
	{
		$result = $dbuser->updateLogin($userid, true, false, 0, 0, -1, -1,
			$digest, $count, $hexsalt, $hexpass);
		if ($result == false) printErrorImmediate('Login table update record failed.');
	}
	else
	{
		$result = $dbuser->insertLogin($userid, true, false, 0, 0, -1, -1,
			$digest, $count, $hexsalt, $hexpass);
		if ($result == false) printErrorImmediate('Login table insert record failed.');
	}
}

// Creates or updates the users table data.
function dbaseTableUsers($userid, $profid, $username)
{
	global $dbuser;
	$result = $dbuser->queryUsers($username);
	if ($result != false && is_array($result))
	{
		$result = $dbuser->updateUsers($username, $userid, $profid, LOGIN_METHOD_NATIVE);
		if ($result == false) printErrorImmediate('Users table update record failed.');
	}
	else
	{
		$result = $dbuser->insertUsers($username, $userid, $profid, LOGIN_METHOD_NATIVE);
		if ($result == false) printErrorImmediate('Users table insert record failed.');
	}
}

// Creates or update the contact table data.
function dbaseTableContact($userid)
{
	global $dbuser;

	$name = 'Application ' . PROFILE_NAME;
	$addr = 'SEA-CORE International LTD.';
	$email = 'seacoregroup@gmail.com';

	$name = safeEncodeString($name);
	$addr = safeEncodeString($addr);
	$email = safeEncodeString($email);

	$result = $dbuser->queryContact($userid, 0);
	if ($result != false && is_array($result))
	{
		$result = $dbuser->updateContact($userid, 0, $name, $addr, $addr,
			$email, '', '', '');
		if ($result == false) printErrorImmediate('Contact table update record failed.');
	}
	else
	{
		$result = $dbuser->insertContact($userid, 0, $name, $addr, $addr,
			$email, '', '', '');
		if ($result == false) printErrorImmediate('Contact table insert record failed.');
	}
}

// Creates or updates the profile table data.
function dbaseTableProfile($profid)
{
	global $dbconf;
	$name = PROFILE_NAME;
	$desc = safeEncodeString('Profile for use only by the application ' . ACCOUNT_NAME . '.');
	$portal = 0;
	$bmc = hex2bin('FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF');
	$bma = hex2bin('FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF');

	$result = $dbconf->queryProfile($profid);
	if ($result != false && is_array($result))
	{
		$result = $dbconf->updateProfile($profid, $name, $desc, $portal, $bmc, $bma);
		if ($result == false) printErrorImmediate('Profile table update record failed.');
	}
	else
	{
		$result = $dbconf->insertProfile($profid, $name, $desc, $portal, $bmc, $bma);
		if ($result == false) printErrorImmediate('Profile table insert record failed.');
	}
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
$profid = $CONFIGVAR[PROFILE_PARAMETER]['value'];
$userid = $CONFIGVAR[ACCOUNT_PARAMETER]['value'];
echo "\nUser creation program for " . PROFILE_NAME . " user.\n\n";
$passwd = getPassword();
echo "\nProcessing user data\n";
dbaseTableProfile($profid);
dbaseTableLogin($userid, $passwd);
dbaseTableContact($userid);
dbaseTableUsers($userid, $profid, ACCOUNT_NAME);
echo "Done.\n";


?>