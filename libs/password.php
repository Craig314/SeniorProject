<?php

require_once "confload.php";
require_once "util.php";

/*

PHP Web Application Password Library

This library provides methods which are used for password handling, such as
generating salts and encrypting passwords.  Since this is a complex subject
and the implementation is very easy to get wrong, macro methods are provided
to automatically handle password encryption.

*/

interface passwordInterface
{
	public function getHashAlgorithm();
	public function generateSalt($saltlen = 0);
	public function passwordHash($passwd, $salt, $digest);
	public function passwordEncryptNew($password, &$hexsalt, &$hexpass, &$digest);
	public function passwordEncrypt($password, $hexsalt, $digest);
}

class password implements passwordInterface
{

	// Returns the best digest algorithm available on the system.
	public function getHashAlgorithm()
	{
		global $CONFIGVAR;

		$mda = openssl_get_md_methods();
		$mdb = explode(" ", $CONFIGVAR['openssl_digests']['value']);

		// Scan what SSL supports with what we are looking for.
		// Return string on first match.
		foreach($mda as $reqdigalg)
		{
			foreach($mdb as $ssldigalg)
			{
				if (strcasecmp(strtolower($reqdigalg), strtolower($ssldigalg)) == 0)
				return $reqdigalg;
			}
		}
		echo "Security Error: Unable to get message digest algorithm.";
		exit(1);
	}

	// Generates a random salt using the OpenSSL CSPRNG.
	// Returns a BINARY string that represents the salt.
	public function generateSalt($saltlen = 0)
	{
		global $CONFIGVAR;

		$cstrong = NULL;
		if ($saltlen <= 0) $saltlen = $CONFIGVAR['security_salt_len']['value'];
		$salt = openssl_random_pseudo_bytes($saltlen, $cstrong);
		if ($cstrong == false)
		{
			echo "Security Error: OpenSSL Weak crypto!";
			exit(1);
		}
		return $salt;
	}

	// Performs password hashing.  Uses random timing values
	// to thwart password hash timing attacks.
	// Returns a BINARY string of the hashed password.
	public function passwordHash($passwd, $salt, $digest, $rounds)
	{
		global $CONFIGCVAR;

		// Repeat concat the password multiple times to build up the length.
		$length = strlen($passwd);
		if ($length == 0)
		{
			echo "Security Error: Password is zero length.";
			exit(1);
		}
		$loopcount = (integer)(1024 / $passlen);
		$longpass = "";
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
		for ($i = 0; $i < $rounds; $i++)
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
	public function passwordEncryptNew($password, &$hexsalt, &$hexpass, &$digest, $rounds)
	{
		global $CONFIGVAR;
		$digest = $this->getHashAlgorithm();
		$binsalt = $this->generateSalt($CONFIGVAR['security_salt_len']['value']);
		$binpass = $this->passwordHash($password, $binsalt, $digest, $rounds);
		$hexsalt = bin2hex($binsalt);
		$hexpass = bin2hex($binpass);
	}

	// Macro function that encrypts a password based on provided
	// parameters of salt and digest.  Salt is an ascii hex string
	// and digest is one of several OpenSSL recognized message
	// digest algorithms.  Returns the ascii hex string of the
	// given password.
	public function passwordEncrypt($password, $hexsalt, $digest, $rounds)
	{
		$binsalt = hex2bin($hexsalt);
		$binpass = $this->passwordHash($password, $binsalt, $digest, $rounds);
		$hexpass = bin2hex($binpass);
		return $hexpass;
	}

}

// Automatically instantiate the class.
$password = new password();

?>
