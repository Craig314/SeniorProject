<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application OAuth Library


*/


require_once 'confload.php';
require_once 'dbaseconf.php';
require_once 'dbaseuser.php';
require_once 'error.php';
require_once 'ajax.php';
require_once 'html.php';

// Initiate Flag
$oAuthInitiate = true;


interface oauthInterface
{
	public function callback($oAuthData);
	public function initiate($userid, $username = '', $password = '');
}

class oauthClass implements oauthInterface
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

	// Writes the state token to the given user.
	private function writeState($userid, $state)
	{
		global $dbuser;
		global $herr;
		$result = $dbuser->updateOAuthState($userid, $state);
		if ($result == false)
		{
			if ($herr->checkState())
				$this->handleError($herr->errorGetMessage);
			else
				$this->handleError('Database Error: Unable to save user state data.');
		}
	}

	// Generates a random challenge string by generating random bytes and
	// then encoding them in base64.
	private function generateChallenge()
	{
		$binstr = openssl_pseudo_random_bytes(32);
		$str = base64_encode($binstr);
		return($str);
	}

	// Writes the challenge token to the given user record.
	private function writeChallenge($userid, $challenge)
	{
		global $dbuser;
		global $herr;

		$result = $dbuser->updateOAuthChallenge($userid, $challenge);
		if ($result == false)
		{
			if ($herr->checkState())
				$this->handleError($herr->errorGetMessage);
			else
				$this->handleError('Database Error: Unable to save user challenge data.');
		}
	}

	// Hashes the challenge according to database settings.
	private function calculateChallenge($rxa, $challenge)
	{
		$result = array();
		if ($rxa['usepkce'] == 1)
		{
			switch ($rxa['pkcemethod'])
			{
				case 0:
					$result['method'] = 'plain';
					$result['challenge'] = $challenge;
					break;
				case 1:
					$result['method'] = 'S256';
					$binary = openssl_digest($challenge, 'SHA-256', true);
					$strb64 = base64_encode($binary);
					$result['challenge'] = $strb64;
					break;
				default:
					$result['method'] = 'S256';
					$binary = openssl_digest($challenge, 'SHA-256', true);
					$strb64 = base64_encode($binary);
					$result['challenge'] = $strb64;
					break;
			}
		}
		else
		{
			$result = NULL;
		}
		return $result;
	}

	// Converts the returned code into an access token by accessing
	// the provider over the network.
	private function sendRequestToken($url, $request)
	{
		$options = array(
			'http' => array(
				'header' => "Content-type: application/x-www-form-urlencoded\r\n",
				'method' => 'POST',
				'content' => $request,
			),
		);
		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		if ($result === false)
			$this->handleError('Network Error: Unable to contact OAuth provider.');
		$response = JSON_decode($result, true);
		if ($response == NULL)
			$this->handleError('OAuth provider data decoding error.  CODE=' . json_last_error());
		$data = oAuth_convertResponseToken($response);
		return $data;
	}

	// Authorization code flow
	private function oaAuthcode($rxp, $rxa, $userid, $challenge)
	{
		$state = $this->generateState();
		$request = oAuth_buildRequestAuthcode($rxc, $state, $challenge);
		$this->writeState($userid, $state);
		html::redirectUrl($request);
		exit(0);
	}

	// Implicit flow
	private function oaImplicit($rxp, $rxa, $userid)
	{
		$state = $this->generateState();
		$request = oAuth_buildRequestImplicit($rxc, $state);
		$this->writeState($userid, $state);
	}

	// Password flow
	private function oaPassword($rxp, $rxa, $userid)
	{
		$state = $this->generateState();
		$request = oAuth_buildRequestPassword($rxc, $username, $password, $state);
		$this->writeState($userid, $state);
	}

	// Client flow
	private function oaClient($rxa)
	{
		$request = Auth_buildRequestClient($rxc);
	}

	// Returns information about the given provider from the database.
	private function getProviderData($provider)
	{
		global $dbconf;
		global $herr;

		$rxa = $dbconf->queryOAuth($rxa['provider']);
		if ($rxa == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage);
			else
				handleError('Database Error: Unable to access OAuth provider data.');
		}
		return $rxa;
	}

	// Returns information about the given user from the database.
	private function getUserData($userid)
	{
		global $dbuser;
		global $herr;

		$rxa = $dbuser->queryOAuth($userid);
		if ($rxa == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage);
			else
				handleError('Database Error: Unable to access OAuth user data.');
		}
		return $rxa;
	}

	// Performs the initial setup for OAuth server communication.
	public function initiate($userid, $username = '', $password = '')
	{
		global $CONFIGVAR;
		global $herr;

		$rxu = $this->getUserData($userid);
		$rxp = $this->getProviderData($userid);

		// Load the configured module.
		$module = '../authorize/oauth.' . $rxp['module'] . '.php';
		if (!file_exist($module))
			$this->handleError('OAuth module file not found.' .
				'<br>Contact your administrator.');
		// XXX Not sure if this will work.
		require_once $module;

		if ($rxp['usepkce'] != 0)
		{
			$challenge = $this->generateChallenge();
			$this->writeChallenge($userid, $challenge);
			$rxu['challenge'] = $challenge;
			$hashed = $this->calculateChallenge($rxp, $challenge);
		}
		else
			$hashed = NULL;

		$authType = $rxp['authtype'];
		switch ($authType)
		{
			case OAUTH_AUTHCODE:
				$this->oaAuthcode($rxp, $rxu, $userid, $hashed);
				break;
			case OAUTH_IMPLICIT:
				$this->oaImplicit($rxp, $rxu, $userid);
				break;
			case OAUTH_PASSWORD:
				$this->oaPassword($rxp, $rxu, $userid);
				break;
			case OAUTH_CLIENTAPP:
				$this->oaClient($rxp, $rxu);
				break;
			default:
				$this->handleError('Invalid AuthType returned from database.' .
					'<br>Contact your administrator.');
				break;
		}
	}

	// When the user is redirected back to the application, after provider
	// specific code is executed, this method is called.
	public function callback($oAuthData)
	{
		// Perform authentication.
		$userid = $_SESSION['userId'];
		$rxu = $this->getUserData($userid);
		if (strcmp($oAuthData['state'], $rxu['state']) != 0)
			handleError('Authentication Failue: Invalid state from OAuth provider.');
		$challenge = (!empty($rxu['challenge'])) ? $rxu['challenge'] : NULL;
		$rxp = $this->getProviderData($rxu['provider']);
		$request = Auth_buildRequestAccessToken($rxp, $oAuthData['code'], $challenge);
		$exchange = (!empty($rxp['exchangeurl'])) ? $rxp['exchangeurl'] : $rxp['authurl'];
		$response = $this->sendRequestToken($exchange, $request);
		if (!empty($response['error']))
		{
			$errdesc = (!empty($response['errdesc'])) ? $response['errdesc'] : 'Unknown Error';
			$erruri = (!empty($response['errdesc'])) ? $response['erruri'] : 'Unknown URI';
			handleError('Authentication Failue: ' . $errdesc . ' at ' . $erruri . '.');
		}

		// If we get to this point, then the authentication was successful.
		// So, we log the user into the application.
	}
}

$oauth = new oauthClass();

?>