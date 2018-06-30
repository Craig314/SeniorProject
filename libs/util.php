<?php

/*

PHP Web Application Utility Routine Library

These routines are for general use throughout the application.  They
perform generic tasks designed to make things easier.  Note that these
routines are not encapsulated by any object oriented overhead.

*/

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