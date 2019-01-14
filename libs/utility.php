<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

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

// Prints an error message and terminates execution.
// Does not return.
function printErrorImmediate($message)
{
	echo $message . "\n";
	exit(1);
}

// Prints an error message and continues execution.
function printErrorContinue($message)
{
	echo $message . "\n";
}

// Returns a value indicating the type of OS that the script is
// running on.
// 0 - Other (Usually Unix)
// 1 - Mac OSX
// 2 - Windows
// Will add more values if needed (ie. Netware)
function identOS()
{
	$os = PHP_OS;

	if (stripos($os, 'darwin') !== false) return 1;
	if (stripos($os, 'win') !== false) return 2;
	return 0;
}

// Generates a generic field array from the different fields.
// If more or different fields are needed, then one can just
// add them manually.
function generateField($type, $name, $label, $size = 0, $value = '',
	$tooltip = '', $default = false, $disabled = false)
{
	$data = array(
		'type' => $type,
		'name' => $name,
		'label' => $label,
	);
	if ($size != 0) $data['fsize'] = $size;
	if ($disabled == true) $data['disable'] = true;
	if ($default != false)
	{
		$data['value'] = $value;
		$data['default'] = $value;
	}
	if (!empty($tooltip)) $data['tooltip'] = $tooltip;
	return $data;
}

// Returns the first argument match of a $_POST value.  If no
// values are found, then returns null.
function getPostValue(...$list)
{
	foreach($list as $param)
	{
		if (isset($_POST[$param])) return $_POST[$param];
	}
	return NULL;
}

// Returns the first argument match of a $_GET value.  If no
// values are found, then returns null.
function getGetValue(...$list)
{
	foreach($list as $param)
	{
		if (isset($_GET[$param])) return $_GET[$param];
	}
	return NULL;
}

// Converts a boolean value into Yes/No.
function convBooleanValue($value)
{
	if ($value == 0) return 'No';
	return 'Yes';
}

// Converts type numbers into names.
function convertType($type)
{
	switch ($type)
	{
		case DBTYPE_STRING:
			return 'String';
			break;
		case DBTYPE_INTEGER:
			return 'Integer';
			break;
		case DBTYPE_BOOLEAN:
			return 'Boolean';
			break;
		case DBTYPE_LONGSTR:
			return 'Long String';
			break;
		case DBTYPE_TIMEDISP:
			return 'Time Displacement';
			break;
		default:
			return 'Unknown';
			break;
	}
}

// Converts long string values to shorter strings for display.
function convertLongString($type, $value)
{
	if ($type == DBTYPE_LONGSTR)
	{
		$len = strlen($value);
		$data = '';
		if ($len > DBSTR_LENGTH)
		{
			for ($i = 0; $i < DBSTR_LENGTH; $i++)
			{
				$data .= $value[$i];
			}
			$data .= '...';
		}
		else $data = $value;
		return $data;
	}
	return $value;
}

// Generates either an array or json field data.
function generateFieldCheck($returnType = 0, $dataType = NULL)
{
	global $CONFIGVAR;
	global $vfystr;

	if (function_exists('fcData'))
	{
		if ($dataType !== NULL)
			$data = fcData($dataType);
		else
			$data = fcData();
	}
	else
	{
		$data = NULL;
	}
	switch ($returnType)
	{
		case FIELDCHK_JSON:
			if (!empty($data))
				$fieldcheck = json_encode($data);
			else
				$fieldcheck = '';
			break;
		case FIELDCHK_ARRAY:
			$fieldcheck = $data;
			break;
		default:
			handleError('Internal Programming Error: CODE XY039223<br>' .
				'Contact your administrator.');
			break;
	}
	return $fieldcheck;
}


?>