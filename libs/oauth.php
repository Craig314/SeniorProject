<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application OAuth Library


*/


require_once 'confload.php';
require_once 'dbaseconf.php';
require_once 'dbaseuser.php';
require_once 'error.h';


function oauth_()
{
	global $dbconf;
	global $dbuser;
	global $CONFIGVAR;
	global $herr;

	$userid = $_SESSION[''];
	$rxu = $dbuser->OAuth

}

function oauthGenerateState()
{
	$binstate = openssl_random_pseudo_bytes(8);
	$state = base64_encode($binstate);
	return $state;
}

?>