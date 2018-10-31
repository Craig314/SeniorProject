<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

Flags Library

NOTES

The session must already be running for this to function
correctly.

*/


require_once 'confbase.php';
require_once 'session.php';


interface flagInterface 
{
	static public function sessionGetApp($flag);
	static public function sessionGetSys($flag);
	static public function getFlag($flag, &$data);
	static public function setFlag($flag, &$data, $value = false);
}


class flag implements flagInterface
{
	static private $mask = array(
		0 => 0x01,
		1 => 0x02,
		2 => 0x04,
		3 => 0x08,
		4 => 0x10,
		5 => 0x20,
		6 => 0x40,
		7 => 0x80
	);

	// Returns the status of the given session application flag.
	// True if set, false if not, NULL if there was an error.
	static public function sessionGetApp($flag)
	{
		if ($flag < 0 || ($flag >= FLAG_COUNT_APPLICATION))
			return NULL;
		if (!isset($_SESSION['flagApp'])) return NULL;
		$byte = (integer)($flag / 8) + 1;
		$bit = $flag % 8;
		$bitArray = unpack('C*', $_SESSION['flagApp']);
		$result = $mask[$bit] & $bitArray[$byte];
		if ($result) return true;
		return false;
	}

	// Returns the status of the given session system flag.
	// True if set, false if not, NULL if there was an error.
	static public function sessionGetSys($flag)
	{
		if ($flag < 0 || $flag >= FLAG_COUNT_SYSTEM)
			return NULL;
		if (!isset($_SESSION['flagSys'])) return NULL;
		$byte = (integer)($flag / 8) + 1;
		$bit = $flag % 8;
		$bitArray = unpack('C*', $_SESSION['flagSys']);
		$result = $mask[$bit] & $bitArray[$byte];
		if ($result) return true;
		return false;
	}

	// Returns the status of the given flag from the string.
	// True if set, false if not, NULL if there was an error.
	static public function getFlag($flag, &$data)
	{
		if ($flag < 0 || $flag >= (strlen($data) * 8)) return NULL;
		$byte = (integer)($flag / 8) + 1;
		$bit = $flag % 8;
		$bitArray = unpack('C*', $data);
		$result = self::$mask[$bit] & $bitArray[$byte];
		if ($result) return true;
		return false;
	}

	// Sets the status of the given flag in the bit string.
	static public function setFlag($flag, &$data, $value = false)
	{
		if ($flag < 0 || $flag >= (strlen($data) * 8)) return NULL;
		$byte = (integer)($flag / 8) + 1;
		$bit = $flag % 8;
		$bitArray = unpack('C*', $data);
		if ($value == true)
			$bitArray[$byte] |= self::$mask[$bit];
		else 
			$bitArray[$byte] &= ~self::$mask[$bit];
		$data = pack('C*', ...$bitArray);
		return true;
	}

}


?>