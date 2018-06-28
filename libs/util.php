<?php

/*

PHP Web Application Utility Routine Library

These routines are for general use throughout the application.  They
perform generic tasks designed to make things easier.

*/

require_once "confbase.php";
require_once "error.php";


// Checks to see if the current user is on a special account.
// Returns true if the account is special.  False if not.
function specialCheckAccount()
{
	global $CONFIGVAR;

	$userId = $_SESSION['user_id'];
	if ($userId == $CONFIGVAR['user_id_vendor']['value']) return true;
	if ($userId == $CONFIGVAR['user_id_admin']['value']) return true;
	return false;
}


// Checks to see if the profile id of the current user
// corresponds to a special account.  Returns true if
// the profile id is special, false if not.
function specialCheckProfile()
{
	global $CONFIGVAR;

	$profId = $_SESSION['profile_id'];
	if ($profId == $CONFIGVAR['profile_id_vendor']['value']) return true;
	if ($profId == $CONFIGVAR['profile_id_admin']['value']) return true;
	return false;
}

// Checks to see if the current user is the vendor account.
// Returns true if the account is the vendor account.
function specialAccountVendor()
{
	global $CONFIGVAR;

	$userId = $_SESSION['user_id'];
	$profId = $_SESSION['profile_id'];
	if ($userId == $CONFIGVAR['user_id_vendor']['value']
		&& $profId == $CONFIGVAR['profile_id_vendor']['value'])
		return true;
	return false;
}

// Checks to see if the current user is the admin account.
// Returns true if the account is the admin account, false
// if not.
function specialAccountAdmin()
{
	global $CONFIGVAR;

	$userId = $_SESSION['user_id'];
	$profId = $_SESSION['profile_id'];
	if ($userId == $CONFIGVAR['user_id_admin']['value']
		&& $profId == $CONFIGVAR['profile_id_admin']['value'])
		return true;
	return false;
}

// An enhanced version of isset.
// Returns true if a variable is set, not null, and not empty.
function realisset($data)
{
	$result = false;
	$result = (isset($data)) ? true : $result;
	$result = (empty($data)) ? false : $result;
	return $result;
}

// Converts a PHP string into a C-style null terminated string.
function str_to_nts($str)
{
	return $str . 0x00;
}

// Converts a C-style null terminated string into a PHP string.
function nts_to_str($str)
{
	$i = strpos($str, "\0");
	if ($i === false) return $str;
	$result =  substr($str, 0, $i);
	return $result;
}

// Prints an error message and terminates.
// Does not return.
function printErrorImmediate($message)
{
	echo $message;
	exit(1);
}

?>