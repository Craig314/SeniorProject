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
	const STR_USERID		= 0;	// User ID
	const STR_PASSWD		= 1;	// Password
	const STR_NAME			= 2;	// Name
	const STR_ADDR			= 3;	// Address
	const STR_PHONE			= 4;	// Phone Number
	const STR_EMAIL			= 5;	// EMail Address
	const STR_ASCII			= 6;	// ASCII String
	const STR_ALPHA			= 7;	// Alpha Only
	const STR_NUMERIC		= 8;	// Numeric Only
	const STR_ALPHANUM		= 9;	// Alphanumeric
	const STR_PINTEGER		= 10;	// Positive Integer
	const STR_FLOAT			= 11;	// Floating Point
	const STR_DATE			= 12;	// Date
	const STR_DATEUNIX		= 13;	// Unix Date Timestamp
	const STR_FILENAME		= 14;	// Filename

	// Start custom checks at 100

	public function errstat();
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
			if ($this->chkblank($data, $field, $id))
			{
				if ($type != self::STR_NUMERIC && $type != self::STR_PINTEGER
					&& $type != self::STR_FLOAT)
				{
					if (!$this->chklenmin($data, $field, $id, $min)) return;
					if (!$this->chklenmax($data, $field, $id, $max)) return;
				}
				if ($type != self::STR_PASSWD)
					if (!$this->chkchrcssa($data, $field, $id)) return;
			}
			else return;
		}
		else
		{
			if ($data != "")
			{
				if ($type != self::STR_NUMERIC && $type != self::STR_PINTEGER
					&& $type != self::STR_FLOAT)
				{
					if (!$this->chklenmin($data, $field, $id, $min)) return;
					if (!$this->chklenmax($data, $field, $id, $max)) return;
				}
				if ($type != self::STR_PASSWD)
					if (!$this->chkchrcssa($data, $field, $id)) return;
			}
			else return;
		}

		// Specific Checks
		switch ($type)
		{
			case self::STR_USERID:
				$this->chkchrlogin($data, $field, $id);
				break;
			case self::STR_PASSWD:
				$this->chkchrascii($data, $field, $id);
				break;
			case self::STR_NAME:
				$this->chkchrascii($data, $field, $id);
				break;
			case self::STR_ADDR:
				$this->chkchrasciiformat($data, $field, $id);
				break;
			case self::STR_PHONE:
				$this->chkchrphone($data, $field, $id);
				break;
			case self::STR_EMAIL:
				$this->validate_email($data, $field, $id);
				break;
			case self::STR_ASCII:
				$this->chkchrascii($data, $field, $id);
				break;
			case self::STR_ALPHA:
				$this->chkchralpha($data, $field, $id);
				break;
			case self::STR_NUMERIC:
				$this->chkchrnumber($data, $field, $id, $max, $min);
				break;
			case self::STR_ALPHANUM:
				$this->chkchralphanum($data, $field, $id);
				break;
			case self::STR_PINTEGER:
				$this->chkchrpint($data, $field, $id, $max, $min);
				break;
			case self::STR_FLOAT:
				$this->chkchrfloat($data, $field, $id, $max, $min);
				break;
			case self::STR_DATE:
				$this->validate_date($data, $field, $id);
				break;
			case self::STR_DATEUNIX:
				$this->validate_date_unix($data, $field, $id);
				break;
			case self::STR_FILENAME:
				$this->validate_filename($data, $field, $id);
				break;
			default:
				$herr->errorPutMessage($this->etype,
					'Internal Error: Invalid datatype code.', $this->estate,
					$field, $id);
				break;
		}
	}

	// Checks string maximum length.
	private function chklenmax($data, $field, $id, $len)
	{
		global $herr;

		if ($len == 0) return;
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
	private function chklenmin($data, $field, $id, $len)
	{
		global $herr;

		if ($len == 0) return;
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
	private function chkblank($data, $field, $id)
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
	private function chkchrlogin($data, $field, $id)
	{
		global $herr;

		$result = preg_match('/[^A-Za-z0-9_-]/', $data);
		if ($result != 0)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid characters detected.<br>Only letters, numbers,' .
				' -_ are allowed.', $this->estate,
				$field, $id);
		}
	}

	// Checks for % for cross site scripting attacks.
	private function chkchrcssa($data, $field, $id)
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
	private function chkchrascii($data, $field, $id)
	{
		global $herr;

		$len = strlen($data);
		if ($len > 0)
		{
			for ($i = 0; $i < $len; $i++)
			{
				if (ord($data[$i]) < 32 || ord($data[$i]) > 127)
				{
					$herr->errorPutMessage($this->etype,
						'Invalid characters detected.<br>Only ASCII characters allowed.',
						$this->estate, $field, $id);
				}
			}
		}
	}

	// Checks to make sure string characters are in ASCII range,
	// including new line, carrage return, and tab.
	private function chkchrasciiformat($data, $field, $id)
	{
		global $herr;

		$len = strlen($data);
		if ($len > 0)
		{
			for ($i = 0; $i < $len; $i++)
			{
				if (ord($data[$i]) < 32 || ord($data[$i]) > 127)
				{
					if (ord($data[$i]) == 9) continue;
					if (ord($data[$i]) == 10) continue;
					if (ord($data[$i]) == 13) continue;
					$herr->errorPutMessage($this->etype,
						'Invalid characters detected.<br>Only ASCII characters with' .
						'<br>CR, LF, and TAB are allowed.', $this->estate, $field, $id);
					return;
				}
			}
		}
	}

	// Check phone numbers for invalid characters.
	private function chkchrphone($data, $field, $id)
	{
		global $herr;

		$result = preg_match('/[^0-9\(\) -]/', $data);
		if ($result != 0)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid characters detected.<br>Only numbers, ()-SP are allowed',
				$this->estate, $field, $id);
		}
	}

	// Letters only
	private function chkchralpha($data, $field, $id)
	{
		global $herr;

		$result = preg_match('/[^A-Za-z\ ]/', $data);
		if ($result != 0)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid characters detected.<br>Only letters and space are' .
				' allowed', $this->estate, $field, $id);
		}
	}

	// Numbers only (+/-)
	private function chkchrnumber($data, $field, $id, $max, $min)
	{
		global $herr;

		$result = preg_match('/[^0-9-]/', $data);
		if ($result != 0)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid characters detected. Only numbers, - are allowed', $this->estate,
				$field, $id);
			return;
		}
		if (filter_var($data, FILTER_VALIDATE_INT) === false)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid floating point format detected.',
				$this->estate, $field, $id);
			return;
		}
		if ($max >= $min)
		{
			if ($data > $max || $data < $min)
			{
				$herr->errorPutMessage($this->etype,
					'Numeric value out of range.<br>Must be between '
					. $min . ' and ' . $max . ' inclusive.', $this->estate,
					$field, $id);
				return;
			}
		}
	}

	// Letters and numbers (+/-)
	private function chkchralphanum($data, $field, $id)
	{
		global $herr;

		$result = preg_match('/[^A-Za-z0-9\ -]/', $data);
		if ($result != 0)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid characters detected.<br>Only letters, numbers,' .
				' - SP are allowed.', $this->estate, $field, $id);
		}
	}

	// Positive integers
	private function chkchrpint($data, $field, $id, $max, $min)
	{
		global $herr;

		$result = preg_match('/[^0-9]/', $data);
		if ($result != 0)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid characters detected.<br>Only numbers are allowed.',
				$this->estate, $field, $id);
			return;
		}
		if (filter_var($data, FILTER_VALIDATE_INT) === false)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid integer format detected.',
				$this->estate, $field, $id);
			return;
		}
		if ($max >= $min)
		{
			if ($data > $max || $data < $min)
			{
				$herr->errorPutMessage($this->etype,
					'Numeric value out of range.<br>Must be between '
					. $min . ' and ' . $max . ' inclusive.', $this->estate,
					$field, $id);
				return;
			}
		}
	}

	// Floating point
	private function chkchrfloat($data, $field, $id, $max, $min)
	{
		global $herr;

		$result = preg_match('/[^0-9\.\+[Ee]-]/', $data);
		if ($result != 0)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid characters detected. Only numbers, .Ee+- are allowed.',
				$this->estate, $field, $id);
			return;
		}
		if (filter_var($data, FILTER_VALIDATE_FLOAT) === false)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid floating point format detected.',
				$this->estate, $field, $id);
			return;
		}
		if ($max >= $min)
		{
			if ($data > $max || $data < $min)
			{
				$herr->errorPutMessage($this->etype,
					'Numeric value out of range.<br>Must be between '
					. $min . ' and ' . $max . ' inclusive.', $this->estate,
					$field, $id);
				return;
			}
		}
	}

	// Validates an email address.
	private function validate_email($data, $field, $id)
	{
		global $herr;

		$result = filter_var($data, FILTER_SANITIZE_EMAIL);
		if (strcasecmp($result, $data) != 0)
		{
			$herr->errorPutMessage($this->etype,
				'EMail address contains invalid characters.', $this->estate,
				$field, $id);
			return;
		}
		$result = filter_var($data, FILTER_VALIDATE_EMAIL);
		if (!$result)
		{
			$herr->errorPutMessage($this->etype,
				'EMail address not formatted correctly.', $this->estate,
				$field, $id);
		}
	}

	// Validates the correct date was entered.
	// Year range 1600-9999 should be sufficient for most
	// purposes.
	private function validate_date($data, $field, $id)
	{
		helper_validate_date($data, $field, $id, 1600, 9999);
	}

	// Verifies that the correct date was entered.
	// This confines the date range to the range supported
	// by unix.  The range 1970-9999 is 253,369,036,800
	// seconds which easily fits into a 64-bit integer.
	private function validate_date_unix($data, $field, $id)
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
				helper_validate_date($data, $field, $id, 1970, 2037);
				break;
			case 8:
				helper_validate_date($data, $field, $id, 1970, 9999);
				break;
			default:
				printErrorImmediate('Critical System Error: Platform not supported.'
					. ' Only platform which have 32/64 bit word sizes are supported.');
		}
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
				return;
			}
		}
		else
		{
			$herr->errorPutMessage($this->etype,
				'Invalid date format detected. Must be MM/DD/YYYY',
				$this->estate, $field, $id);
			return;
		}
		// Check to make sure that the year makes sense.
		if ($date[2] < $minyear || $date[2] > $maxyear)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid date format detected. Must be MM/DD/YYYY',
				$this->estate, $field, $id);
			return;
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
			return;
		}
		// Check if the day is in the proper range.
		if ($date[1] < 1 || $date[1] > $md[$date[0]])
		{
			$herr->errorPutMessage($this->etype,
				'Invalid date format detected. Must be MM/DD/YYYY',
				$this->estate, $field, $id);
			return;
		}
	}

	// Validates filenames
	private function validate_filename($data, $field, $id)
	{
		global $herr;

		$result = preg_match('/[^A-Za-z0-9\ \._-]/', $data);
		if ($result != 0)
		{
			$herr->errorPutMessage($this->etype,
				'Invalid characters detected.<br>Only letters, numbers, ._-' .
				' are allowed.', $this->estate, $field, $id);
		}
	}
}


// Auto instantiate the class.
$vfystr = new verifyString();


?>