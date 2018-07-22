<?php

/*
SEA-CORE International Ltd.
SEA-CORE Development Group


PHP Web Application Misculanious Security Routines

This library contains non-object oriented security routines that don't really
fit anywhere else.  Other security related routines have been placed in the
following libraries:

account.php		Account handling/checking
password.php	Password generation/encryption
session.php		Session handling/checking

*/

// Encodes HTML entities for save storage in the database which
// prevents persistant cross-site scripting (XSS) attacks.
function safeEncodeString($str)
{
	$result = htmlentities($str, ENT_QUOTES | ENT_HTML5);
	return $result;
}




?>