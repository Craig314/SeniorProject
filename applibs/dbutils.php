<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application Database Utilities


*/

const BASEDIR2 = '../libs/';
require_once BASEDIR2 . 'database.php';
require_once BASEDIR2 . 'dbaseconf.php';
require_once BASEDIR2 . 'dbaseuser.php';
require_once 'dbaseapp.php';


// Queries all users based on a profile and returns an array of key=value
// pairs in array format.  If no records are found, then returns NULL.
function getUsersByProfile($profid)
{
	global $dbuser;
	global $herr;

	$rxa = $dbuser->queryUsersProfId($profid);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			return NULL;
	}

	// Build the response array.
	$result = array();
	foreach ($rxa as $kx => $vx)
	{
		$rxb = $dbuser->queryContact($vx['userid']);
		if ($rxb == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage());
			else
				handleError('Database Error: User contact information missing.'
					. '<br>USERID=' . $vx['userid']
					. '<br>Contact your administrator.');
		}
		$result[$rxb['name']] = $vx['userid'];
	}

	return $result;
}

// Returns the name of a signle user based on user id.
function getUserName($userid)
{
	global $dbuser;
	global $herr;

	$rxa = $dbuser->queryContact($userid);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: User contact information missing.'
				. '<br>USERID=' . $userid
				. '<br>Contact your administrator.');
	}

	return $rxa;
}


?>