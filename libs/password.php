<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application Password Library

This library provides methods which are used for password handling, such as
generating salts and encrypting passwords.  Since this is a complex subject
and the implementation is very easy to get wrong, macro methods are provided
to automatically handle password encryption.

*/


require_once 'confload.php';
require_once 'utility.php';


interface passwordInterface
{
	static public function getHashAlgorithm();
	static public function generateSalt($saltlen = 0);
	static public function generateChallenge($length);
	static public function hash($passwd, $salt, $digest, $count);
	static public function encryptNew($passwd, &$hexsalt, &$hexpass, &$digest, $count);
	static public function encrypt($passwd, $hexsalt, $digest, $count);
	static public function verify($passwd, $hexsalt, $hexpass, $digest, $count);
	static public function verifyCHAP($passwd, $challenge, $hexpass, $digest);
	static public function checkComplexity($passwd);
}


class password implements passwordInterface
{

	// Returns the best digest algorithm available on the system.
	static public function getHashAlgorithm()
	{
		global $CONFIGVAR;

		$mdp = openssl_get_md_methods();
		$mdr = explode(' ', $CONFIGVAR['openssl_digests']['value']);

		// Scan what SSL supports with what we are looking for.
		// Return string on first match.
		foreach($mdr as $req)
		{
			foreach($mdp as $ssl)
			{
				if (strcasecmp($req, $ssl) == 0)
				return $req;
			}
		}
		echo 'Security Error: Unable to get message digest algorithm.';
		exit(1);
	}

	// Generates a random salt using the OpenSSL CSPRNG.
	// Returns a BINARY string that represents the salt.
	static public function generateSalt($saltlen = 0)
	{
		global $CONFIGVAR;

		$cstrong = NULL;
		if ($saltlen <= 0) $saltlen = $CONFIGVAR['security_salt_len']['value'];
		$salt = openssl_random_pseudo_bytes($saltlen, $cstrong);
		if ($cstrong == false)
		{
			echo 'Security Error: OpenSSL Weak crypto!';
			exit(1);
		}
		return $salt;
	}

	// Generates a one-time-password challenge.
	static public function generateChallenge($length)
	{
		$cstrong = NULL;
		$random = openssl_random_pseudo_bytes($length, $cstrong);
		if ($cstrong == false)
		{
			echo 'Security Error: OpenSSL Weak crypto!';
			exit(1);
		}
		return $random;
	}

	// Performs password hashing.  Uses random timing values
	// to thwart password hash timing attacks.
	// Returns a BINARY string of the hashed password.
	static public function hash($passwd, $salt, $digest, $count)
	{
		global $CONFIGVAR;

		// Repeat concat the password multiple times to build up the length.
		$passlen = strlen($passwd);
		if ($passlen == 0)
		{
			echo 'Security Error: Password is zero length.';
			exit(1);
		}
		$loopcount = (integer)(1024 / $passlen);
		$longpass = '';
		if ($loopcount > 0)
		{
			for ($i = 0; $i < $loopcount; $i++)
				$longpass .= $passwd;
		}
		else $longpass = $passwd;

		// XXX: This uses an undocumented OpenSSL call to
		// access a message digest algorithm.  This may
		// need to be modified in the future.
		$pwdhash = openssl_digest($longpass, $digest, true);
		for ($i = 0; $i < $count; $i++)
		{
			if ($salt) $pwdhash .= $salt;
			$pwdhash = openssl_digest($pwdhash, $digest, true);
		}

		// This is the random timing component which introduces
		// a timing jitter in the algorithm to thwart timing
		// attacks.
		$value = rand($CONFIGVAR['security_hashtime_min']['value'], $CONFIGVAR['security_hashtime_max']['value']);
		for ($i = 0; $i < $value; $i++)
		{
			$longpass = md5($longpass);
		}

		return $pwdhash;
	}

	// Macro function that encrypts a new password.  Returns the
	// salt, encrypted password, and the digest used.  Note that
	// the salt and password are returned via ascii hex strings
	// through the parameter list.
	static public function encryptNew($passwd, &$hexsalt, &$hexpass, &$digest, $count)
	{
		global $CONFIGVAR;
		$digest = self::getHashAlgorithm();
		$binsalt = self::generateSalt($CONFIGVAR['security_salt_len']['value']);
		$binpass = self::hash($passwd, $binsalt, $digest, $count);
		$hexsalt = bin2hex($binsalt);
		$hexpass = bin2hex($binpass);
	}

	// Macro function that encrypts a password based on provided
	// parameters of salt and digest.  Salt is an ascii hex string
	// and digest is one of several OpenSSL recognized message
	// digest algorithms.  Returns the ascii hex string of the
	// given password.
	static public function encrypt($passwd, $hexsalt, $digest, $count)
	{
		$binsalt = hex2bin($hexsalt);
		$binpass = self::hash($passwd, $binsalt, $digest, $count);
		$hexpass = bin2hex($binpass);
		return $hexpass;
	}

	// Macro function to verify a password.  Returns true if the password
	// checks, false if it doesn't.
	static public function verify($passwd, $hexsalt, $hexpass, $digest, $count)
	{
		$hexpass2 = self::encrypt($passwd, $hexsalt, $digest, $count);
		if (strcasecmp($hexpass, $hexpass2) != 0) return false;
		return true;
	}

	// Performs the CHAP verify.  Returns true if passed, false if
	// it doesn't pass.
	static public function verifyCHAP($passwd, $challenge, $hexpass, $digest)
	{
		$binpassusr = hex2bin($passwd);
		$binpassref = hex2bin($hexpass);
		$binchall = hex2bin($challenge);
		$pwdhash = openssl_digest($binpassref . $binchall, $digest, true);
		if (strcasecmp($pwdhash, $binpassusr) != 0) return false;
		return true;
	}

	// Checks the password to make sure that it meets complexity
	// requirements.  Returns true if requirements have been met,
	// false otherwise.
	static public function checkComplexity($passwd)
	{
		global $CONFIGVAR;

		$length = strlen($passwd);
		switch($CONFIGVAR['security_passwd_complex_level']['value'])
		{
			case 0:		// None, do not check
				return true;
				break;
			case 1:		// Upper, lower case letters
				$upper = false;
				$lower = false;
				for ($i = 0; $i < $length; $i++)
				{
					$ascii = ord($passwd[$i]);
					if ($ascii >= 65 && $ascii <=  90) $upper = true;
					if ($ascii >= 97 && $ascii <= 122) $lower = true;
				}
				if ($upper && $lower) return true;
				break;
			case 2:		// Upper, lower case letters, numbers
				$upper = false;
				$lower = false;
				$number = false;
				for ($i = 0; $i < $length; $i++)
				{
					$ascii = ord($passwd[$i]);
					if ($ascii >= 48 && $ascii <=  57) $number = true;
					if ($ascii >= 65 && $ascii <=  90) $upper = true;
					if ($ascii >= 97 && $ascii <= 122) $lower = true;
				}
				if ($upper && $lower && $number) return true;
				break;
			case 3:		// Upper, lower case letters, numbers, symbols
				$upper = false;
				$lower = false;
				$number = false;
				$symbol = false;
				for ($i = 0; $i < $length; $i++)
				{
					$ascii = ord($passwd[$i]);
					if ($ascii >=  32 && $ascii <=  47) $symbol = true;
					if ($ascii >=  48 && $ascii <=  57) $number = true;
					if ($ascii >=  58 && $ascii <=  64) $symbol= true;
					if ($ascii >=  65 && $ascii <=  90) $upper = true;
					if ($ascii >=  91 && $ascii <=  96) $symbol = true;
					if ($ascii >=  97 && $ascii <= 122) $lower = true;
					if ($ascii >= 123 && $ascii <= 126) $symbol = true;
				}
				if ($upper && $lower && $number && symbol) return true;
				break;
			default:
				return false;
				break;
		}
		return false;
	}

}


?>