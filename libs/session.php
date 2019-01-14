<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application Session Handling Library

This library contains session functions that make the application more secure.

*/


require_once 'confload.php';
require_once 'utility.php';


interface sessionInterface
{
	public function start();
	public function regenerateId();
	public function validate();
	public function restart();
	public function getToken();
}


class session implements sessionInterface
{
	// Creates a new session and fills in default values.
	public function create()
	{
		global $CONFIGVAR;

		// Start the session
		$result = session_start();
		if (!$result) printErrorImmediate('Security Error: Session start failed');

		// Load default values
		$_SESSION['banner'] = false;
		$_SESSION['login'] = false;
		$_SESSION['loginLast'] = -1;
		$_SESSION['loginTime'] = -1;
		$_SESSION['nameUser'] = '';
		$_SESSION['nameReal'] = '';
		$_SESSION['userId'] = $CONFIGVAR['account_id_none']['value'];
		$_SESSION['profileId'] = $CONFIGVAR['profile_id_none']['value'];
		$_SESSION['passChange'] = false;
		$_SESSION['portalType'] = -1;
		$_SESSION['flagSys'] = hex2bin('00000000000000000000000000000000');
		$_SESSION['flagApp'] = hex2bin('00000000000000000000000000000000');

		// These are needed to verify that the user's session has
		// not been hijacked.
		$_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes
			($CONFIGVAR['session_nonce_len']['value']));
		$_SESSION['IPAddress'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['userAgent'] = md5($_SERVER['HTTP_USER_AGENT']);

	}

	// Configures a new session and stores various values in
	// the $_SESSION variable to authenticate the user.
	// This should only be called on successful login.
	public function start()
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
		if ($CONFIGVAR['session_regen_enable']['value'] != 0)
		{
			$result = setcookie(ini_get('session.name'), session_id(), time() + $timeout, "/",
				$hostname, $secured, true);
			if (!$result) printErrorImmediate('Security Error: Set Cookie Failed');
		}
		
		// These are to ensure the user is logged into the system.

		// These are needed to verify that the user's session has
		// not been hijacked.
		$_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes
			($CONFIGVAR['session_nonce_len']['value']));
		$_SESSION['IPAddress'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['userAgent'] = md5($_SERVER['HTTP_USER_AGENT']);
	}

	// Restarts the session and sets certain parameters on the user's
	// session cookie to make it more secure.  This needs to be done
	// on every request.
	public function restart()
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
		if ($CONFIGVAR['session_regen_enable']['value'] != 0)
		{
			$result = setcookie(session_name(), session_id(), time() + $timeout, "/",
				$hostname, $secured, true);
			if (!$result) printErrorImmediate('Security Error: Set Cookie Failed');
		}
	}

	// Regenerates the session ID to thwart attacks caused by
	// session fixation.
	// This should be called on every client request.
	public function regenerateId()
	{
		global $CONFIGVAR;

		// If session regeneration is not enabled, the just return.
		if ($CONFIGVAR['session_regen_enable']['value'] == 0) return;

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

			// Prevent new session from expiring.
			unset($_SESSION['OBSOLETE']);
			unset($_SESSION['EXPIRES']);
		}
	}

	// Validates the user's session data to make sure that it
	// really is the user.  Any errors that occurr will
	// immediately terminate the script.
	public function validate()
	{
		$hijack = '<br>Possible session hijacking attempt.';

		// Check for expired session
		if ($_SESSION['OBSOLETE'] && $_SESSION['EXPIRES'] < time())
			printErrorImmediate('Security Error: Attemted to use expired session.');
		
		// Check if client IP address changed
		if ($_SESSION['IPAddress'] != $_SERVER['REMOTE_ADDR'])
			printErrorImmediate('Security Error: IP Address mismatch.' . $hijack);
		
		// Check if client user agent has changed
		if ($_SESSION['userAgent'] != md5($_SERVER['HTTP_USER_AGENT']))
			printErrorImmediate('Security Error: User Agent Mismatch.' . $hijack);
	}

	// If session tokens are being used, then return the session
	// token, or false if session tokens are not being used.
	public function getToken()
	{
		global $CONFIGVAR;

		if ($CONFIGVAR['session_use_tokens']['value'] == 0) return false;
		if (!isset($_SESSION['token']))
			$_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes
				($CONFIGVAR['session_nonce_len']['value']));
		if (empty($_SESSION['token']))
			$_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes
				($CONFIGVAR['session_nonce_len']['value']));
		return $_SESSION['token'];
	}

}


// Automatically instantiate the class
$session = new session();


?>