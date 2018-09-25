<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

String Verification Library

This library performs validation of user input strings to make
sure that they make sense and that invalid input is flagged as
an error.

*/


require_once 'utility.php';
require_once 'error.php';


interface verifyStringInterface
{
	const STR_NONE			= 0;	// No Verification
	const STR_USERID		= 1;	// User ID
	const STR_PASSWD		= 2;	// Password
	const STR_NAME			= 3;	// Name
	const STR_ADDR			= 4;	// Address
	const STR_PHONE			= 5;	// Phone Number
	const STR_EMAIL			= 6;	// EMail Address
	const STR_ASCII			= 7;	// ASCII String
	const STR_ALPHA			= 8;	// Alpha Only
	const STR_NUMERIC		= 9;	// Numeric Only
	const STR_ALPHANUM		= 10;	// Alphanumeric
	const STR_ALPHANUMPUNCT	= 11;	// Alphanumeric + Punctuation.
	const STR_PINTEGER		= 12;	// Positive Integer
	const STR_INTEGER		= 13;	// Integer (decimal or 0x hex format)
	const STR_FLOAT			= 14;	// Floating Point
	const STR_DATE			= 15;	// Date
	const STR_DATEUNIX		= 16;	// Unix Date Timestamp
	const STR_FILENAME		= 17;	// Filename
	const STR_PATHNAME		= 18;	// Pathname
	const STR_URI			= 19;	// Uniform Resource Identifier
	const STR_URL			= 20;	// Uniform Resource Location
	const STR_TIMEDISP		= 21;	// Time Displacement
	const STR_ALPHASPEC		= 22;	// Alpha Spec'd (a-z_ only)
	const STR_DESC			= 23;	// Descriptions
	const STR_CUSTOM		= 99;	// Custom Verification (Do Not Use);

	// Start custom checks at 100

	public function errstat();
	public function fieldchk(&$fieldData, $index, $data);
	public function strchk($data, $field, $id, $type, $blank = true, $max = 0, $min = 0);
}


class verifyString implements verifyStringInterface
{

	private $etype = handleErrors::ETFORM;
	private $estate = handleErrors::ESFAIL;

	// Returns the verify string error status.
	// Returns true if any errors occurred.
	public function errstat()
	{
		global $herr;

		return $herr->checkState();
	}

	// Same as strchk below, but uses the field data array to perform
	// checks. $index is the index into the array.  $data is the data
	// to check.
	public function fieldchk(&$fieldData, $index, $data)
	{
		if ($fieldData[$index]['type'] == self::STR_CUSTOM)
			$type = $fieldData[$index]['ctype'];
		else
			$type = $fieldData[$index]['type'];
		return $this->strchk($data, $fieldData[$index]['dispname'],
			$fieldData[$index]['name'], $type, $fieldData[$index]['noblank'],
			$fieldData[$index]['max'], $fieldData[$index]['min']);
	}

	// Runs the checks.
	// $data is the string to check.
	// $field is the name of the field (used in error messages).
	// $type is one of the type constants.
	// $min/$max are min and max string lengths.
	public function strchk($data, $field, $id, $type, $blank = true, $max = 0, $min = 0)
	{
		global $herr;

		// Check min/max validation
		if ($min > $max)
		{
			$min = 0;
			$max = 0;
		}

		// Common Checks
		if ($blank)
		{
			if ($this->checkBlank($data, $field, $id))
			{
				if ($type != self::STR_NUMERIC && $type != self::STR_PINTEGER
					&& $type != self::STR_FLOAT && $type != self::STR_INTEGER)
				{
					if (!$this->checkLengthMinimum($data, $field, $id, $min)) return false;
					if (!$this->checkLengthMaximum($data, $field, $id, $max)) return false;
				}
				if ($type != self::STR_PASSWD)
					if (!$this->checkCharCSSA($data, $field, $id)) return false;
			}
			else return false;
		}
		else
		{
			if ($data != "")
			{
				if ($type != self::STR_NUMERIC && $type != self::STR_PINTEGER
					&& $type != self::STR_FLOAT)
				{
					if (!$this->checkLengthMinimum($data, $field, $id, $min)) return false;
					if (!$this->checkLengthMaximum($data, $field, $id, $max)) return false;
				}
				if ($type != self::STR_PASSWD)
					if (!$this->checkCharCSSA($data, $field, $id)) return false;
			}
			else return true;
		}

		// Specific Checks
		switch ($type)
		{
			case self::STR_NONE:
				$result = true;
				break;
			case self::STR_USERID:
				$result = $this->checkCharLogin($data, $field, $id);
				break;
			case self::STR_PASSWD:
				$result = $this->checkCharASCII($data, $field, $id);
				break;
			case self::STR_NAME:
				$result = $this->checkCharAlpha($data, $field, $id);
				break;
			case self::STR_ADDR:
				$result = $this->checkCharASCIIFormat($data, $field, $id);
				break;
			case self::STR_PHONE:
				$result = $this->checkCharPhone($data, $field, $id);
				break;
			case self::STR_EMAIL:
				$result = $this->validateEmail($data, $field, $id);
				break;
			case self::STR_ASCII:
				$result = $this->checkCharASCII($data, $field, $id);
				break;
			case self::STR_ALPHA:
				$result = $this->checkCharAlpha($data, $field, $id);
				break;
			case self::STR_NUMERIC:
				$result = $this->checkCharNumeric($data, $field, $id, $max, $min);
				break;
			case self::STR_ALPHANUM:
				$result = $this->checkCharAlphaNum($data, $field, $id);
				break;
			case self::STR_ALPHANUMPUNCT:
				$result = $this->checkCharAlphaNumPunct($data, $field, $id);
				break;
			case self::STR_PINTEGER:
				$result = $this->checkCharPosInteger($data, $field, $id, $max, $min);
				break;
			case self::STR_INTEGER:
				$result = $this->checkCharInteger($data, $field, $id, $max, $min);
				break;
			case self::STR_FLOAT:
				$result = $this->checkCharFloat($data, $field, $id, $max, $min);
				break;
			case self::STR_DATE:
				$result = $this->validateDate($data, $field, $id);
				break;
			case self::STR_DATEUNIX:
				$result = $this->validateDateUnix($data, $field, $id);
				break;
			case self::STR_FILENAME:
				$result = $this->checkCharFilename($data, $field, $id);
				break;
			case self::STR_PATHNAME:
				$result = $this->checkCharPathname($data, $field, $id);
				break;
			case self::STR_URI:
				$result = $this->checkCharURI($data, $field, $id);
				break;
			case self::STR_URL:
				$result = $this->checkCharURL($data, $field, $id);
				break;
			case self::STR_TIMEDISP:
				$result = $this->validateTimeDisp($data, $field, $id);
				break;
			case self::STR_ALPHASPEC:
				$result = $this->checkCharAlphaSpec($data, $field, $id);
				break;
			case self::STR_DESC:
				$result = $this->checkCharASCIIFormat($data, $field, $id);
				break;
			default:
				$herr->errorPutMessage($this->etype,
					'Internal Error: Invalid datatype code.', $this->estate,
					$field, $id);
				$result = false;
				break;
		}
		return $result;
	}

	// Checks string maximum length.
	private function checkLengthMaximum($data, $field, $id, $len)
	{
		global $herr;

		if ($len == 0) return true;
		if (strlen($data) > $len)
		{
			$herr->errorPutMessage($this->etype,
				'Maximum length of ' . $len . ' exceeded.', $this->estate,
				$field, $id);
			return false;
		}
		return true;
	}

	// Checks string minimum length.
	private function checkLengthMinimum($data, $field, $id, $len)
	{
		global $herr;

		if ($len == 0) return true;
		if (strlen($data) < $len)
		{
			$herr->errorPutMessage($this->etype,
				'Minimum length of ' . $len . ' not met.', $this->estate,
				$field, $id);
			return false;
		}
		return true;
	}

	// Checks for blank string.
	private function checkBlank($data, $field, $id)
	{
		global $herr;

		if (strlen($data) == 0)
		{
			$herr->errorPutMessage($this->etype,
				'Blank field not allowed.', $this->estate, $field, $id);
			return false;
		}
		return true;
	}

	// Checks for invalid characters in user id string.
	private function checkCharLogin($data, $field, $id)
	{
		global $herr;

		$regex = '/^[A-Za-z0-9\_\-]+$/';
		$result = preg_match($regex, $data);
		if ($result != 1)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid characters detected.<br>Only letters, numbers,' .
				' -_ are allowed.', $this->estate,
				$field, $id);
			return false;
		}
		return true;
	}

	// Checks for % for cross site scripting attacks.
	private function checkCharCSSA($data, $field, $id)
	{
		global $herr;

		if (strpos($data, "%") !== false)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid characters detected.<br>Percent sign not allowed.',
				$this->estate, $field, $id);
			return false;
		}
		return true;
	}

	// Checks to make sure string characters are in ASCII range.
	private function checkCharASCII($data, $field, $id)
	{
		global $herr;

		$regex = '/^[\x20-\x7E]*$/';
		$result = preg_match($regex, $data);
		if ($result != 1)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid characters detected.<br>Only ASCII characters allowed.',
				$this->estate, $field, $id);
			return false;
		}
		return true;
	}

	// Checks to make sure string characters are in ASCII range,
	// including new line, carrage return, and tab.
	private function checkCharASCIIFormat($data, $field, $id)
	{
		global $herr;

		$regex = '/^[\t\n\r\x20-\x7E]*$/';
		$result = preg_match($regex, $data);
		if ($result != 1)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid characters detected.<br>Only ASCII characters with' .
				'<br>SPACE, CR, LF, and TAB are allowed.',
				$this->estate, $field, $id);
			return false;
		}
		return true;
	}

	// Check phone numbers for invalid characters.
	private function checkCharPhone($data, $field, $id)
	{
		global $herr;

		$regex = '/^[0-9\(\)\-\ ]+$/';
		$result = preg_match($regex, $data);
		if ($result != 1)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid characters detected.<br>Only numbers, ()- ' .
				'and SPACE are allowed', $this->estate, $field, $id);
			return false;
		}
		return true;
	}

	// Letters only
	private function checkCharAlpha($data, $field, $id)
	{
		global $herr;

		$regex = '/^[A-Za-z\ ]+$/';
		$result = preg_match($regex, $data);
		if ($result != 1)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid characters detected.<br>Only letters and space are' .
				' allowed', $this->estate, $field, $id);
			return false;
		}
		return true;
	}

	// Numbers only (+/-)
	private function checkCharNumeric($data, $field, $id, $max, $min)
	{
		global $herr;

		$regex = '/^[+|-]?[0-9]+$/';
		$result = preg_match($regex, $data);
		if ($result != 1)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid characters detected. Only numbers, - are allowed', $this->estate,
				$field, $id);
			return false;
		}
		if (filter_var($data, FILTER_VALIDATE_INT) === false)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid floating point format detected.',
				$this->estate, $field, $id);
			return false;
		}
		if ($max >= $min)
		{
			if ($data > $max || $data < $min)
			{
				$herr->errorPutMessage($this->etype,
					'Numeric value out of range.<br>Must be between '
					. $min . ' and ' . $max . ' inclusive.', $this->estate,
					$field, $id);
				return false;
			}
		}
		return true;
	}

	// Letters and numbers (+/-)
	private function checkCharAlphaNum($data, $field, $id)
	{
		global $herr;

		$regex = '/^[A-Za-z0-9\ _-]+$/';
		$result = preg_match($regex, $data);
		if ($result != 1)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid characters detected.<br>Only letters, numbers,' .
				' _- SP are allowed.', $this->estate, $field, $id);
			return false;
		}
		return true;
	}

	// Letters, numbers, and punctuation.
	private function checkCharAlphaNumPunct($data, $field, $id)
	{
		global $herr;

		$regex = '/^[A-Za-z0-9\ \_\-\,\.\!\;\:\'\"\`\?]+$/';
		$result = preg_match($regex, $data);
		if ($result != 1)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid characters detected.<br>Only letters, numbers,' .
				' _- SP are allowed.', $this->estate, $field, $id);
			return false;
		}
		return true;
	}
	// Positive integers
	private function checkCharPosInteger($data, $field, $id, $max, $min)
	{
		global $herr;

		$regex = '/^([1-9][0-9]*)|([0-9])$/';
		$result = preg_match($regex, $data);
		if ($result != 1)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid characters detected.<br>Only numbers are allowed.',
				$this->estate, $field, $id);
			return false;
		}
		if (filter_var($data, FILTER_VALIDATE_INT) === false)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid integer format detected.',
				$this->estate, $field, $id);
			return false;
		}
		if ($max >= $min)
		{
			if ($data > $max || $data < $min)
			{
				$herr->errorPutMessage($this->etype,
					'Numeric value out of range.<br>Must be between '
					. $min . ' and ' . $max . ' inclusive.', $this->estate,
					$field, $id);
				return false;
			}
		}
		return true;
	}

	// Integer
	private function checkCharInteger($data, $field, $id, $max, $min)
	{
		global $herr;

		$regex = '/^((0x[0-9A-Fa-f]+)|([+|-]?(([1-9][0-9]+)|([0-9]))))$/';
		$result = preg_match($regex, $data);
		if ($result != 1)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid characters or format detected.<br>Only numbers are allowed.',
				$this->estate, $field, $id);
			return false;
		}
		if ($max >= $min)
		{
			if ($data > $max || $data < $min)
			{
				$herr->errorPutMessage($this->etype,
					'Numeric value out of range.<br>Must be between '
					. $min . ' and ' . $max . ' inclusive.', $this->estate,
					$field, $id);
				return false;
			}
		}
		return true;
	}

	// Floating point
	private function checkCharFloat($data, $field, $id, $max, $min)
	{
		global $herr;

		$regex = '/^[+|-]?';
		$regex .= '(([1-9][0-9]*(\.[0-9]+)?)|';
		$regex .= '(0(\.[0-9]+)?))';
		$regex .= '([E|e][+|-][0-9]+)?$/';
		$result = preg_match($regex, $data);
		if ($result != 1)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid characters or format detected. Only numbers, ., [Ee], +, - are allowed.',
				$this->estate, $field, $id);
			return false;
		}
		if (filter_var($data, FILTER_VALIDATE_FLOAT) === false)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid floating point format detected.',
				$this->estate, $field, $id);
			return false;
		}
		if ($max >= $min)
		{
			if ($data > $max || $data < $min)
			{
				$herr->errorPutMessage($this->etype,
					'Numeric value out of range.<br>Must be between '
					. $min . ' and ' . $max . ' inclusive.', $this->estate,
					$field, $id);
				return false;
			}
		}
		return true;
	}

	// Validates an email address.
	private function validateEmail($data, $field, $id)
	{
		global $herr;

		$result = filter_var($data, FILTER_SANITIZE_EMAIL);
		if (strcasecmp($result, $data) != 0)
		{
			$herr->errorPutMessage($this->etype,
				'EMail address contains invalid characters.', $this->estate,
				$field, $id);
			return false;
		}
		$result = filter_var($data, FILTER_VALIDATE_EMAIL);
		if (!$result)
		{
			$herr->errorPutMessage($this->etype,
				'EMail address not formatted correctly.', $this->estate,
				$field, $id);
			return false;
		}
		return true;
	}

	// Validates the correct date was entered.
	// Year range 1600-9999 should be sufficient for most
	// purposes.
	private function validateDate($data, $field, $id)
	{
		helper_validate_date($data, $field, $id, 1600, 9999);
	}

	// Verifies that the correct date was entered.
	// This confines the date range to the range supported
	// by unix.  The range 1970-9999 is 253,369,036,800
	// seconds which easily fits into a 64-bit integer.
	private function validateDateUnix($data, $field, $id)
	{
		// Checks to see if the system time has rolled over.  If it has,
		// then stop, *NOW*
		if (time() < 0)
			printErrorImmediate('Critical System Error: Platform update required.'
				. ' System time exceeds 32-bit integer value.');

		// Sets the end year based on 32/64 bit platform.  Platforms which
		// support other word sizes are pretty much extinct...except maybe
		// embedded systems.
		switch(PHP_INT_SIZE)
		{
			case 4:
				$result = helper_validate_date($data, $field, $id, 1970, 2037);
				break;
			case 8:
				$result = helper_validate_date($data, $field, $id, 1970, 9999);
				break;
			default:
				printErrorImmediate('Critical System Error: Platform not supported.'
					. ' Only platform which have 32/64 bit word sizes are supported.');
				return false;
				break;
		}
		return $result;
	}

	// Date validation helper function.  This is where the actual
	// validation takes place.  Checks to make sure that day,
	// month, and year are within proper ranges, and take leap
	// years into account.
	private function helper_validate_date($data, $field, $id, $minyear, $maxyear)
	{
		global $herr;

		// Number of days of each month.
		$md = array(0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
		// Separate data
		$date = array();
		$toksep = '/\=-.|:+';
		$date[0] = strtok($data, $toksep);
		$date[1] = strtok($toksep);
		$date[2] = strtok($toksep);
		// Validate
		if (is_array($date))
		{
			if (count($date) != 3)
			{
				$herr->errorPutMessage($this->etype,
					'Invalid date format detected. Must be MM/DD/YYYY',
					$this->estate, $field, $id);
				return false;
			}
		}
		else
		{
			$herr->errorPutMessage($this->etype,
				'Invalid date format detected. Must be MM/DD/YYYY',
				$this->estate, $field, $id);
			return false;
		}

		// Check to make sure that the year makes sense.
		if ($date[2] < $minyear || $date[2] > $maxyear)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid date format detected. Must be MM/DD/YYYY',
				$this->estate, $field, $id);
			return false;
		}
		// Figure out if we are on a leap year or not.
		// Note, if the year ends in 00, then it is *NOT* a
		// leap year.
		if (($date[2] % 100) != 0)
		{
			if (($date[2] % 4) == 0) $md[2]++;
		}

		// Check if the month is in the proper range.
		if ($date[0] < 1 || $date[0] > 12)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid date format detected. Must be MM/DD/YYYY',
				$this->estate, $field, $id);
			return false;
		}

		// Check if the day is in the proper range.
		if ($date[1] < 1 || $date[1] > $md[$date[0]])
		{
			$herr->errorPutMessage($this->etype,
				'Invalid date format detected. Must be MM/DD/YYYY',
				$this->estate, $field, $id);
			return false;
		}
		return true;
	}

	// Validates filenames
	private function checkCharFilename($data, $field, $id)
	{
		global $herr;

		$regex = '/^[A-Za-z0-9\.\_\-]+$/';
		$result = preg_match($regex, $data);
		if ($result != 1)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid characters detected.<br>Only letters, numbers, ._-' .
				' are allowed.', $this->estate, $field, $id);
			return false;
		}
		return true;
	}

	// Validates pathnames
	private function checkCharPathname($data, $field, $id)
	{
		global $herr;

		$regex = '/^[A-Za-z0-9\/\.\_\-]+$/';
		$result = preg_match($regex, $data);
		if ($result != 1)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid characters detected.<br>Only letters, numbers, ._-/' .
				' are allowed.', $this->estate, $field, $id);
			return false;
		}
		$result = strpos($data, '..');
		if ($result !== false)
		{
			$herr->errorPutMessage($this->etype,
				'The .. directory construct is not allowed in path names.',
				$this->estate, $field, $id);
			return false;
		}
		return true;
	}

	// Validates URIs
	private function checkCharURI($data, $field, $id)
	{
		global $herr;

		$regex = '/^';
		$regex .= '(http|https)\:\/\/'; // SCHEME 
		$regex .= '([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?'; // User and Pass 
		$regex .= '([a-z0-9-.]*)\.([a-z]{2,3})'; // Host or IP 
		$regex .= '(\:[0-9]{2,5})?'; // Port 
		$regex .= '(\/([a-z0-9+\$_-]\.?)+)*\/?'; // Path 
		$regex .= '(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?'; // GET Query 
		$regex .= '(#[a-z_.-][a-z0-9+\$_.-]*)?'; // Anchor 
		$regex .= '$/';

		$result = preg_match($regex, $data);
		if ($result != 1)
		{
			$herr->errorPutMessage($this->etype,
			'An invalid URI has been detected.',
			$this->estate, $field, $id);
			return false;
		}
		return true;
	}

	// Validates URLs
	private function checkCharURL($data, $field, $id)
	{
		global $herr;

		$regex = '/^';
		$regex = '(http|https)\:\/\/'; // SCHEME 
		$regex .= '([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?'; // User and Pass 
		$regex .= '([a-z0-9-.]*)\.([a-z]{2,3})'; // Host or IP 
		$regex .= '(\:[0-9]{2,5})?'; // Port 
		$regex .= '(\/([a-z0-9+\$_-]\.?)+)*\/?'; // Path 
		$regex .= '$/';

		$result = preg_match($regex, $data);
		if ($result != 1)
		{
			$herr->errorPutMessage($this->etype,
				'An invalid URL has been detected.',
				$this->estate, $field, $id);
			return false;
		}
		return true;
	}

	// Validates the time displacement
	private function validateTimeDisp($data, $field, $id)
	{
		global $herr;

		$regex = '/^[+|-](([0-1]?[0-9])|(2[0-3])):[0-5][0-9]$/';
		$result = preg_match($regex, $data);
		if ($result != 1)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid time displacement format or values out of range.' .
				'<br>Must be +/-00:00 to +/-23:59',
				$this->estate, $field, $id);
			return false;
		}
		return true;
	}

	private function checkCharAlphaSpec($data, $field, $id)
	{
		global $herr;

		$regex = '/^[a-z\_]+$/';
		$result = preg_match($regex, $data);
		if ($result != 1)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid characters detected.  Only a-z and _ are allowed.',
				$this->estate, $field, $id);
			return false;
		}
		return true;
	}
}


// Auto instantiate the class.
$vfystr = new verifyString();


?>