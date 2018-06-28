<?php

/*

Accounts Library

Currently, only checks for special accounts.

*/

require_once "confload.php";


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


?>