<?php

// This library contains functions to convert times and dates
// between different formats.

// The base time format is Unix Time Stamp which starts at
// the epoch time of Jan 1, 1970 12:00:01 AM.


require_once "confbase.php";


interface timedate_interface
{
	static public function getTimeUnix();
	static public function unix2db($unixtime);
	static public function db2unix($dbasetime);
	static public function unix2tod($unixtime);
	static public function unixDiffTime($time1, $time2);
	static public function unixDiffDate($time1, $time2);
}

class timedate implements timedate_interface
{

	// Generates a DateTimeZone object
	static private function setTimeZone()
	{
		global $CONFIGVAR;

		return new DateTimeZone($CONFIGVAR['timezone_default']['value']);
	}

	// Returns the current system time as a unix timestamp.
	static public function getTimeUnix()
	{
		$dt = DateTime::createFromFormat('U', time());
		$dt->setTimezone(timedate::setTimeZone());
		return $dt->format('U');
	}

	// Converts a Unix time to database format of YYYY-MM-DD HH:mm:SS
	static public function unix2db($unixtime)
	{
		$dt = DateTime::createFromFormat('U', $unixtime);
		$dt->setTimezone(timedate::setTimeZone());
		return $dt->format('Y-m-d H:i:s');
	}

	// Converts a database time stamp format of YYYY-MM-DD HH-mm-SS
	// to Unix format.
	static public function db2unix($dbasetime)
	{
		$tz = timedate::setTimeZone();
		$dt = DateTime::createFromFormat('Y-m-d H:i:s', $dbasetime, $tz);
		return $dt->format('U');
	}

	// Converts a Unix time to time of day in HH:mm format.
	static public function unix2tod($unixtime)
	{
		$dt = DateTime::createFromFormat('U', $unixtime);
		$dt->setTimezone(timedate::setTimeZone());
		return $dt->format('H:i');
	}

	// Helper method that calculates time differences.
	static private function helperDifftime($time1, $time2)
	{
		$tmin = min($time1, $time2);
		$tmax = max($time1, $time2);
		$tz = timedate::setTimeZone();
		$dtmin = DateTime::createFromFormat('U', $tmin);
		$dtmax = DateTime::createFromFormat('U', $tmax);
		$dtmin->setTimezone($tz);
		$dtmax->setTimezone($tz);
		return $dtmax->diff($dtmin);
	}

	// Takes the difference between 2 unix timestamps and outputs
	// that difference in HH:mm format.
	static public function unixDiffTime($time1, $time2)
	{
		$dti = timedate::helperDiffTime($time1, $time2);
		return($dti->format('%H:%I'));
	}

	// Takes the difference between 2 unix timestamps and outputs
	// that difference in YYYY-MM-DD HH:mm:SS format.
	static public function unixDiffDate($time1, $time2)
	{
		$dti = timedate::helperDiffTime($time1, $time2);
		return($dti->format('%Y-%M-%D %H:%I:%S'));
	}
}
?>