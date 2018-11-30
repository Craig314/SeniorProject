<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application Database Utilities


*/

const BASEDIR2 = '../libs/';
require_once BASEDIR2 . 'error.php';
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

// Calculates a letter grade based on the scale.
function caluclateGrade($grade, $scale)
{
	global $CONFIGVAR;
	global $dbapp;
	global $herr;

	$rxa = $dbapp->queryGradescale($scale);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Missing grade scale record.');
	}

	switch($CONFIGVAR['gradescale_mode']['value'])
	{
		case 0:
			if ($rxa['grade_ap'] > $rxa['grade_a'])
			{
				if ($grade >= $rxa['grade_ap']) return 'A+';
			}
			if ($grade >= $rxa['grade_a']) return 'A';
			if ($grade >= $rxa['grade_am']) return 'A-';
			if ($grade >= $rxa['grade_bp']) return 'B+';
			if ($grade >= $rxa['grade_b']) return 'B';
			if ($grade >= $rxa['grade_bm']) return 'B-';
			if ($grade >= $rxa['grade_cp']) return 'C+';
			if ($grade >= $rxa['grade_c']) return 'C';
			if ($grade >= $rxa['grade_cm']) return 'C-';
			if ($grade >= $rxa['grade_dp']) return 'D+';
			if ($grade >= $rxa['grade_d']) return 'D';
			if ($grade >= $rxa['grade_dm']) return 'D-';
			return 'F';
			break;
		case 1:
			if ($grade >= $rxa['grade_a']) return 'A';
			if ($grade >= $rxa['grade_b']) return 'B';
			if ($grade >= $rxa['grade_c']) return 'C';
			if ($grade >= $rxa['grade_d']) return 'D';
			return 'F';
			break;
		default:
			handleError('Database Error: Invalid grade scale mode.');
	}
}


?>