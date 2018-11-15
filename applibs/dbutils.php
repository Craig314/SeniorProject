<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application Database Utilities


*/

const BASEDIR = '../libs/';
require_once BASEDIR . 'database.php';
require_once BASEDIR . 'dbaseconf.php';
require_once BASEDIR . 'dbaseuser.php';
require_once 'dbaseapp.php';


// Queries all users based on a profile and returns an array of key=value
// pairs in array format.  If no records are found, then returns NULL.
function getUsersByProfile($profid)
{
	global $dbuser;
	global $herr;
	global $dbuser;

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


?>