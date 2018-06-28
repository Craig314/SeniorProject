<?php

require_once "confbase.php";
require_once "util.php";

/*

Web Application Security Library

The purpose of this library is to supply routines that enhance
the security of any web application.

*/

interface security_interface
{
	public function getHashAlgorithm();
	public function generateSalt($saltlen = 0);
	public function passwordHash($passwd, $salt, $digest);
	public function passwordEncryptNew($password, &$hexsalt, &$hexpass, &$digest);
	public function passwordEncrypt($password, $hexsalt, $digest);
	public function checkCredentials();
	public function sessionStart($userName, $userId, $profileId, $loginStatus);
	public function sessionRegenerateId();
	public function sessionValidate();
	public function sessionRestart();
}

class security implements security_interface
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
	public function passwordHash($passwd, $salt, $digest)
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
		for ($i = 0; $i < $CONFIGVAR['security_hash_rounds']['value']; $i++)
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
	// through the parameter list.  Returns true if successful or
	// false if there was a problem.
	public function passwordEncryptNew($password, &$hexsalt, &$hexpass, &$digest)
	{
		global $CONFIGVAR;
		$digest = $this->getHashAlgorithm();
		$binsalt = $this->generateSalt($CONFIGVAR['security_salt_len']['value']);
		$binpass = $this->passwordHash($password, $binsalt, $digest);
		$hexsalt = bin2hex($binsalt);
		$hexpass = bin2hex($binpass);
		return true;
	}


	// Macro function that encrypts a password based on provided
	// parameters of salt and digest.  Salt is an ascii hex string
	// and digest is one of several OpenSSL recognized message
	// digest algorithms.  Returns the ascii hex string of the
	// given password.
	public function passwordEncrypt($password, $hexsalt, $digest)
	{
		$binsalt = hex2bin($hexsalt);
		$binpass = $this->passwordHash($password, $binsalt, $digest);
		$hexpass = bin2hex($binpass);
		return $hexpass;
	}


	// Checks the credentials from the supergloabal variable
	// $_SESSION.  Returns true if everything is ok, false if
	// there is a problem.
	public function checkCredentials()
	{
		if (isset($_SESSION))
		{
			// Check Login Status
			if (isset($_SESSION['loginStatus']))
			{
				if ($_SESSION['loginStatus'] != true) return false;
			}
			else return false;

			// Check for userName
			if (isset($_SESSION['userName']))
			{
				if ($_SESSION['userName'] === false) return false;
			}
			else return false;

			// Check for userId
			if (isset($_SESSION['userId']))
			{
				if ($_SESSION['userId'] === false) return false;
			}
			else return false;

			// Check for profileId
			if (isset($_SESSION['profileId']))
			{
				if ($_SESSION['profileId'] === false) return false;
			}
			else return false;
		}
		else return false;
		return true;
	}

	// Configures a new session and stores various values in
	// the $_SESSION variable to authenticate the user.
	// This should only be called on successful login.
	public function sessionStart($userName, $userId, $profileId, $loginStatus)
	{
		global $CONFIGVAR;

		// Setup
		$timeout = $CONFIGVAR['session_cookie_expire_time']['value'];
		$hostname = $CONFIGVAR['server_hostname']['value'];
		$secured = $CONFIGVAR['server_secure']['value'];

		// Start Session
		$result = session_start();
		if (!$result) printErrorImmediate('Security Error: Session start failed');

		// Rewrites the sessions cookie with updated parameters.
		$result = setcookie(ini_get('session.name'), session_id(), time() + $timeout, "/",
			$hostname, $secured, true);
		if (!$result) printErrorImmediate('Security Error: Set Cookie Failed');
		
		// These are to ensure the user is logged into the system.
		$_SESSION['loginStatus'] = $loginStatus;
		$_SESSION['userName'] = $userName;
		$_SESSION['userId'] = $userId;
		$_SESSION['profileId'] = $profileId;

		// These are needed to verify that the user's session has
		// not been hijacked.
		$_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes
			($CONFIGVAR['session_nonce_len']['value']));
		$_SESSION['IPAddress'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['userAgent'] = md5($_SERVER['HTTP_USER_AGENT']);
	}

	// Regenerates the session ID to thwart attacks caused by
	// session fixation.
	// This should be called on every client request.
	public function sessionRegenerateId()
	{
		global $CONFIGVAR;

		$currentTime = time();
		if ($currentTime > $_SESSION['regenTimeLast'])
		{
			// Current session to expire at configured time
			// (usually 60 seconds).
			$_SESSION['OBSOLETE'] = true;
			$_SESSION['EXPIRES'] = $currentTime +
				$CONFIGVAR['session_expire_time']['value'];

			// Regenerate session ID and keep old one.
			$result = session_regenerate_id();
			if (!$result) printErrorImmediate('Security Error: Regenerate session ID failed.');

			// Set next session regenerate time.
			$_SESSION['regenTimeLast'] = $currentTime +
				$CONFIGVAR['session_regen_time']['value'];

			// Get new session ID and close both so scripts can use them.
			$sessionNewId = session_id();
			if (!$sessionNewId) printErrorImmediate('Security Error: Unable to obtain new session ID.');
			$result = session_write_close();
			if (!$result) printErrorImmediate('Security Error: Unable to close session.');

			// Set new session ID and open it back up.
			$result = session_id($sessionNewId);
			if (!$result) printErrorImmediate('Security Error: Unable to set new session ID.');
			$result = session_start();
			if (!$result) printErrorImmediate('Security Error: Unable to open new session.');

			// Prevent session from expiring.
			unset($_SESSION['OBSOLETE']);
			unset($_SESSION['EXPIRES']);
		}
	}

	// Validates the user's session data to make sure that it
	// really is the user.  Any errors that occurr will
	// immediately terminate the script.
	public function sessionValidate()
	{
		$hijack = ' Possible session hijacking attempt.';

		// Check for expired session
		if ($_SESSION['OBSOLETE'] && $_SESSION['EXPIRES'] < time())
			printErrorImmediate('Security Error: Attemted to use expired session.');
		
		// Check if client IP address changed
		if ($_SESSION['IPAddress'] != $_SERVER['REMOTE_ADDR'])
			printErrorImmediate('Security Error: IP Address mismatch.' . $hijack);
		
		// Check if client user agent has changed
		if ($_SESSION['userAgent'] != bin2hex(md5($_SERVER['HTTP_USER_AGENT'])))
			printErrorImmediate('Security Error: User Agent Mismatch.' . $hijack);
	}

	// Sets certain parameters on the user's session cookie
	// make is more secure.  This needs to be done on every
	// request.
	public function sessionRestart()
	{
		global $CONFIGVAR;

		// Setup
		$timeout = $CONFIGVAR['session_cookie_expire_time']['value'];
		$hostname = $CONFIGVAR['server_hostname']['value'];
		$secured = $CONFIGVAR['server_secure']['value'];

		// Start Session
		$result = session_start();
		if (!$result) printErrorImmediate('Security Error: Session start failed');

		// Rewrites the session cookie with updated parameters.
		$result = setcookie(session_name(), session_id(), time() + $timeout, "/",
			$hostname, $secured, true);
		if (!$result) printErrorImmediate('Security Error: Set Cookie Failed');
		
	}

}


// Auto instantiate the class.
$sec = new security();


?>
