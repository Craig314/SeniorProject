<?php

/*

PHP Web Application Session Handling Library

This library contains session functions that make the application more secure.

*/

require_once 'confload.php';
require_once 'utility.php';


interface sessionInterface
{
	public function start($userName, $userId, $profileId, $loginStatus);
	public function regenerateId();
	public function validate();
	public function restart();
}

class session implements sessionInterface
{
	// Configures a new session and stores various values in
	// the $_SESSION variable to authenticate the user.
	// This should only be called on successful login.
	public function start($userName, $userId, $profileId, $loginStatus)
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
		$result = setcookie(session_name(), session_id(), time() + $timeout, "/",
			$hostname, $secured, true);
		if (!$result) printErrorImmediate('Security Error: Set Cookie Failed');
	}

	// Regenerates the session ID to thwart attacks caused by
	// session fixation.
	// This should be called on every client request.
	public function regenerateId()
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
		$hijack = ' Possible session hijacking attempt.';

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

}

// Automatically instantiate the class
$session = new session();

?>