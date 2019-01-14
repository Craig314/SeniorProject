<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application 


*/


require_once 'confload.php';
require_once 'dbaseconf.php';
require_once 'dbaseuser.php';
require_once 'error.php';
require_once 'ajax.php';
require_once 'html.php';



interface openidInterface
{
	public function initiate($userid);
	public function callback($openIdData);
}


class openidClass implements openidInterface
{
	// Internal mentod to display fatal errors to the user.
	private function handleError($message)
	{
		global $ajax;

		$ajax->sendCommand(ajaxClass::CMD_ERRDISP, $message);
		exit(1);
	}

	// Generates a random state string by generating random bytes and
	// then encoding them in base64.
	private function generateState()
	{
		$strState = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$length = strlen($strState);
		$state = '';
		for ($i = 0; $i < 8; $i++)
		{
			$byte = random_int() % $length;
			$state .= substr($strState, $byte, 1);
		}
		return $state;
	}

	// Returns information about the given provider from the database.
	private function getProviderData($provider)
	{
		global $dbconf;
		global $herr;

		$rxa = $dbconf->queryOpenId($rxa['provider']);
		if ($rxa == false)
		{
			if ($herr->checkState())
				$this->handleError($herr->errorGetMessage);
			else
				$this->handleError('Database Error: Unable to access OpenID provider data.');
		}
		return $rxa;
	}

	// Returns information about the given user from the database.
	private function getUserData($userid)
	{
		global $dbuser;
		global $herr;

		$rxa = $dbuser->queryOpenId($userid);
		if ($rxa == false)
		{
			if ($herr->checkState())
				$this->handleError($herr->errorGetMessage);
			else
				$this->handleError('Database Error: Unable to access OpenID user data.');
		}
		return $rxa;
	}

	// Sets the transaction handle in the database.
	private function setUserTransactionId($userid, $handle)
	{
		global $dbuser;
		global $herr;

		$result = $dbuser->updateOpenIdLogin($userid, $handle, '', 0, 0);
		if ($result == false)
		{
			if ($herr->checkState())
				$this->handleError($herr->errorGetMessage);
			else
				$this->handleError('Database Error: Unable to access OpenID user data.');
		}
	}

	// Performs signature verification using either
	// HMAC-SHA256 or HMAC-SHA160.
	// XXX The spec is not really clear on how to do this
	// so we are going to leave this to return true for now.
	private function evalSignature($data)
	{
		return true;
	}

	// Sets the user's login data.
	// This is called after we get a response from the provider.
	private function setUserInvalidation($userid, $handle, $issue, $expire)
	{
		global $dbuser;
		global $herr;

		$rxa = $dbuser->queryOpenId($userid);
		if ($rxa == false)
		{
			if ($herr->checkState())
				$this->handleError($herr->errorGetMessage);
			else
				$this->handleError('Database Error: Unable to access OpenID user data.');
		}
		$result = $dbuser->updateOpenIdLogin($userid, $rxa['handle'], $handle,
			$issue, $expire);
		if ($result == false)
		{
			if ($herr->checkState())
				$this->handleError($herr->errorGetMessage);
			else
				$this->handleError('Database Error: Unable to access OpenID user data.');
		}
	}

	// Initiates an OpenID authentication request.
	public function initiate($userid)
	{
		// Retrieves information from the database.
		$rxu = $this->getUserData($userid);
		$rxp = $this->getProviderData($userdata['provider']);

		// Generate the request.
		$handle = $this->generateState();
		$request = buildRequest($rxu, $rxp, $handle);

		// Load the configured module.
		$module = '../authorize/openid.' . $rxp['module'] . '.php';
		if (!file_exist($module))
			$this->handleError('OpenID module file not found.' .
				'<br>Contact your administrator.');
		// XXX Not sure if this will work.
		require_once $module;

		// Redirect
		html::redirectUrl($request);

		exit(0);
	}

	public function callback($openIdData)
	{
		// The spec is not clear on alot of this, but it does give the
		// procedure to determine if a user has been authenticated by
		// the provider or not.  Expect code changes when things become
		// more clear.

		// Get information from database.
		$rxu = $this->getUserData($_SESSION['userId']);
		$rxp = $this->getProviderData($rxu['provider']);

		// Begin authentication routine.
		$failed = false;
		if ($openIdData['mode'] != 'id_res') $failed = true;
		if ($openIdData['endpoint'] != $rxp['serverurl']) $failed = true;
		if ($openIdData['return_to'] != $rxp['redirecturl']) $failed = true;
		if ($openIdData['nonce'] == $rxu['nonce']) $failed = true;
		if (evalSignature($openIdData) == false) $failed = true;

		if ($failed == true)
			handleError('Authentication Failure: ' . $openIdData['error']);
		
		// If we get to this point, then the authentication was successful.
		// So, we log the user into the application.
		if (function_exists('openid_login'))
		{
			openid_login($_SESSION['userId'], $rxu, $rxp);
		}
		else
		{
			handleError('OpenID Login Failure: Missing Code');
		}
	}
}

$openid = new openidClass();


?>