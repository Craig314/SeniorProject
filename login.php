<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application Login Page

This is the main login page for the application.
This is one of the few pages which have embedded HTML.

This page handles three different types of logins:
	1. Native: Uses the internal database to log the user in.
	2. OAuth: Connects to an outside provider to perform the login.
	3. OpenID: Connects to an outside provider to perform the login.
	
Options 2 and 3 require customization to the specific providers
that are being used.  In some cases, more than one provider is
being used based on the user.

*/


// Mode option.
// 0: Native login mode only
// 1: Native, OAuth, and OpenID login modes
$modeOption = 1;


const BASEDIR = 'libs/';
require_once BASEDIR . 'confbase.php';
require_once BASEDIR . 'confload.php';
require_once BASEDIR . 'dbaseuser.php';
require_once BASEDIR . 'dbaseconf.php';
require_once BASEDIR . 'error.php';
require_once BASEDIR . 'session.php';
require_once BASEDIR . 'password.php';
require_once BASEDIR . 'security.php';
require_once BASEDIR . 'vfystr.php';
require_once BASEDIR . 'html.php';
require_once BASEDIR . 'ajax.php';
require_once BASEDIR . 'account.php';
require_once BASEDIR . 'openid.php';
require_once BASEDIR . 'oauth.php';

// For developmental use only.
if (APP_DEBUG_STATUS === true)
{
	processSharedMemoryReload();
}

// Verify that we are on the correct port using the
// correct protocol.
html::checkRequestPort();

// Global Variables
$baseUrl = html::getBaseURL();
$userMax = $CONFIGVAR['security_username_maxlen']['value'];
$passMax = $CONFIGVAR['security_passwd_maxlen']['value'];

// Start the session and load some default values.
$session->create();

// Now we process requests.
if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
	// This is called on a GET operation.
	htmlPageTemplate();
	exit(0);
}
else if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	// This is called on a POST operation.
	if (isset($_POST['COMMAND']))
	{
		$command_id = (int)$_POST['COMMAND'];
		switch($command_id)
		{
			case -1:		// Load additional content
				determineLoginType();
				break;
			case 1:			// Submit Username
				processUsername();
				break;
			case 2:			// Submit Password
				processPassword();
				break;
			default:
				$ajax->sendCommand(ajaxClass::CMD_ERRCLRDISP,
					'Internal Error. Try Again. Contact your<br>administrator' .
					' if the problem persists.');
				exit(1);
				break;
		}
		exit(0);
	}
	else
	{
		// If we get to the POST operation without a COMMAND,
		// then something screwy is going on.  Have the user
		// retry it.
		$ajax->sendCommand(ajaxClass::CMD_ERRCLRDISP,
			'Internal Error. Try Again. Contact your<br>administrator' .
			' if the problem persists.');
		exit(1);
	}
}
else
{
	html::sendCode(ajaxClass::CODE_NOMETH);
	exit(1);
}

// Error exit function.
function handleError($message)
{
	global $ajax;
	$ajax->sendCommand(ajaxClass::CMD_ERRDISP, $message);
	exit(1);
}

// Determines the login type
function determineLoginType()
{
	global $CONFIGVAR;
	global $modeOption;
	global $ajax;

	switch($modeOption)
	{
		case 0:
			html1Stage_login();
			break;
		case 1:
			if ($CONFIGVAR['oauth_enable']['value'] == 0 &&
				$CONFIGVAR['openid_enable']['value'] == 0)
			{
				$html = html1Stage_login();
			}
			else
			{
				$html = html2Stage_username();
			}
			break;
		default:
			$html = html1Stage_login();
			break;
	}
	$ajax->writeMainPanelImmediate($html, NULL);
	exit(0);
}

// Returns information about the specific username.
function getUserNameData($username)
{
	global $dbuser;
	global $herr;

	$rxa = $dbuser->queryUsers($username);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
	}
	return $rxa;
}

// Returns information about the specific userid.
function getUserData($userid)
{
	global $dbuser;
	global $herr;

	$rxa = $dbuser->queryUsersUserId($userid);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Unable to query user ID.');
	}
	return $rxa;
}

// Returns login information about the specific userid.
function getUserLogin($userid)
{
	global $dbuser;
	global $herr;

	$rxa = $dbuser->queryLogin($userid);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Unable to query user login data.');
	}
	return $rxa;
}

// Returns contact information about the specific userid.
function getUserContact($userid)
{
	global $dbuser;
	global $herr;

	$rxa = $dbuser->queryContact($userid);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Unable to query user contact data.');
	}
	return $rxa;
}

// Returns information about the specific profile ID.
function getProfileData($profid)
{
	global $dbconf;
	global $herr;

	$rxa = $dbconf->queryProfile($profid);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Unable to query profile ID.');
	}
	return $rxa;
}

// Decodes the base64 flag.
function decodeB64Flag()
{
	if (isset($_POST['base64']))
	{
		if (is_numeric($_POST['base64']))
		{
			$base64 = (int)$_POST['base64'];
			if ($base64 != 0 && $base64 != 1)
				handleError('Invalid characters detected.');
		}
		else handleError('Invalid characters detected.');
	}
	else handleError('Missing encode parameter.');
	if ($base64 == 0) return false;
	return true;
}

// Decodes the CHAP flag.
function decodeCHAPFlag()
{
	if (isset($_POST['use_chap']))
	{
		if (is_numeric($_POST['use_chap']))
		{
			$usechap = (int)$_POST['use_chap'];
			if ($usechap != 0 && $usechap != 1)
				handleError('Invalid characters detected.');
		}
		else handleError('Invalid characters detected.');
	}
	else handleError('Missing CHAP parameter.');
	if ($usechap == 0) return false;
	return true;
}

// Decodes and checks the username that was submitted.
function decodeUsername()
{
	global $userMax;
	global $vfystr;
	global $herr;

	// Get Base64 encoding flag.
	$base64 = decodeB64Flag();

	// Get and decode the username that was provided by the client.
	if (isset($_POST['username']))
	{
		$post = $_POST['username'];
		$urldec = rawurldecode($post);
		if ($base64 === true) $b64dec = base64_decode($urldec);
			else $b64dec = $urldec;
		$username = $b64dec;
	}
	else handleError('Missing username parameter.');

	// Check to make sure that the username contains valid characters.
	$vfystr->strchk($username, 'Username', '', verifyString::STR_USERID, true, $userMax, 1);
	if ($herr->checkState())
	{
		handleError($herr->errorGetMessage());
	}

	// Return the username
	return $username;
}

// Decodes and checks the password that was submitted.
function decodePassword()
{
	global $passMax;
	global $vfystr;
	global $herr;

	// Get Base64 encoding flag.
	$base64 = decodeB64Flag();

	// Get CHAP flag.
	$chap = decodeCHAPFlag();

	// Get and decode the password that was provided by the client.
	if (isset($_POST['password']))
	{
		$post = $_POST['password'];
		$urldec = rawurldecode($post);
		if ($base64 === true) $b64dec = base64_decode($urldec);
			else $b64dec = $urldec;
		$password = $b64dec;
	}
	else handleError('Missing password parameter.');

	// Check to make sure that the password is ok to process.
	// Different checks are required if the CHAP flag is set.
	if ($chap == true)
	{
		$vfystr->strchk($password, 'Password', '', verifyString::STR_HEX,
			true, $passMax, 1);
	}
	else
	{
		$vfystr->strchk($password, 'Password', '', verifyString::STR_PASSWD,
			true, $passMax, 1);
	}
	if ($herr->checkState())
	{
		handleError($herr->errorGetMessage());
	}

	// Return the password.
	return $password;
}

// Process username submission
function processUsername()
{
	$username = decodeUsername();
	$rxa = getUserNameData($username);
	if ($rxa != false)
	{
		// If the username is in the database.
		$_SESSION['userName'] = $username;
		$_SESSION['userId'] = $rxa['userid'];
		$_SESSION['method'] = $rxa['method'];
		switch ($rxa['method'])
		{
			case LOGIN_METHOD_NATIVE:
				stage1_native($rxa);
				break;
			case LOGIN_METHOD_OAUTH:
				stage1_oauth($rxa);
				break;
			case LOGIN_METHOD_OPENID:
				stage1_openid($rxa);
				break;
			default:
				handleError('Invalid login mode for user name.<br>Contact your administrator.');
				exit(1);
		}
	}
	else
	{
		// If the username was not found.
		$_SESSION['userName'] = '';
		$_SESSION['userId'] = $CONFIGVAR['account_id_none']['value'];
		if ($CONFIGVAR['oauth_enable']['value'] == 0 &&
			$CONFIGVAR['openid_enable']['value'] == 0)
		{
			handleError('Invalid username/password');
		}
		else
		{
			$ajax->writeMainPanelImmediate(html2Stage_password($username));
		}
	}
}

// Process password submission.
// Only valid for native logins.
function processPassword()
{
	global $CONFIGVAR;

	$password = decodePassword();
	$chap = decodeCHAPFlag();
	if ($chap == true)
	{
		if ($CONFIGVAR['security_chap_enable']['value'] == 0)
		{
			handleError('Invalid password mode.');
		}
		else
		{
			native_login($_SESSION['userName'], $password, true);
		}
	}
	else
	{
		native_login($_SESSION['userName'], $password, false);
	}
}

// This applies to the native login method.
function stage1_native($rxa)
{
	global $CONFIGVAR;
	global $ajax;

	// Check if we need to display the password prompt.
	if ($CONFIGVAR['oauth_enable']['value'] == 0 &&
		$CONFIGVAR['openid_enable']['value'] == 0)
	{
		// Native logins only, so we send the command to instruct the client
		// to send us the password.
		$chap = generateCHAP($rxa['userid']);
		if ($chap == false)
		{
			$ajax->sendCommand(901);
		}
		else
		{
			$ajax->loadQueueCommand(900, $chap);
			$ajax->loadQueueCommand(901);
			$ajax->sendQueue();
		}
	}
	else
	{
		// We have multiple types of logins enabled, so since this is
		// the native method, we send the password prompt with the
		// CHAP challenge data package, if enabled.
		$html = html2Stage_password($rxa['username']);
		$chap = generateCHAP($rxa['userid']);
		if ($chap == false)
		{
			$ajax->writeMainPanelImmediate($html);
		}
		else
		{
			$ajax->loadQueueCommand(900, $chap);
			$ajax->loadQueueCommand(ajaxClass::CMD_WMAINPANEL, $html);
			$ajax->sendQueue();
			exit(0);
		}
	}
}

// This applies to the OAuth login method.
function stage1_oauth($rxa)
{
	global $CONFIGVAR;

	if ($CONFIGVAR['oauth_enable']['value'] == 0)
	{
		handleError('Login method not available.<br>Contact your administrator.');
	}
	$oauth->initiate($rxa['userid'], $rxa['username']);
}

// This applies to the OpenID login method.
function stage1_openid($rxa)
{
	global $CONFIGVAR;

	if ($CONFIGVAR['openid_enable']['value'] == 0)
	{
		handleError('Login method not available.<br>Contact your administrator.');
	}
	$openid->initiate($rxa['userid']);
}

// Generates a CHAP challenge data package to be sent to the client.
function generateCHAP($userid)
{
	global $CONFIGVAR;

	// If CHAP is not enabled, then forget it.
	if ($CONFIGVAR['security_chap_enable']['value'] == 0) return false;

	// Query user login data.
	$rxa = getUserLogin($userid);

	// Generate the challenge.
	$binChallenge = password::generateChallenge(
		$CONFIGVAR['security_chap_length']['value']);
	$hexChallenge = bin2hex($binChallenge);
	$_SESSION['challenge'] = $hexChallenge;

	// Construct JSON data array.
	$data = array(
		'salt' => $rxa['salt'],
		'digest' => $rxa['digest'],
		'count' => $rxa['count'],
		'challenge' => $hexChallenge,
	);
	$json = json_encode($data);

	// Return JSON format data.
	return $json;
}

// Performs full password validation.  Uses either the standard
// method or CHAP.
function native_login($username, $password, $useCHAP)
{
	global $CONFIGVAR;
	global $ajax;
	global $herr;
	global $dbuser;
	global $dbconf;
	global $account;
	global $session;

	// Get userid and profid for the username from the database.
	$rxa_users = $dbuser->queryUsers($username);
	if ($rxa_users == false) handleError('Invalid Username/Password');
	$userid = (int)$rxa_users['userid'];
	$profid = (int)$rxa_users['profileid'];
	$active = (int)$rxa_users['active'];

	// Get the login data
	$rxa_login = $dbuser->queryLogin($userid);
	if ($rxa_login == false)
		handleError('Stored Data Conflict<br>Contact Your Administrator<br>XX32334');
	$hexpass	= (string)$rxa_login['passwd'];
	$hexsalt	= (string)$rxa_login['salt'];
	$digest		= (string)$rxa_login['digest'];
	$count		= (int)$rxa_login['count'];
	$logfail	= (int)$rxa_login['failcount'];
	$locktime	= (int)$rxa_login['locktime'];

	// Verify the password.  If too many attempts have been made
	// within a specified time period, then lockout the account
	// for the same configured amount of time.
	if ($useCHAP == true)
	{
		$passchk = password::verifyCHAP($password, $_SESSION['challenge'],
			$hexpass, $digest);
	}
	else
	{
		$passchk = password::verify($password, $hexsalt, $hexpass, $digest,
			$count);
	}
	if ($passchk == false)
	{
		if ($locktime != 0)
		{
			if (time() > $locktime)
			{
				$logfail = 1;
				$locktime = time() + (int)$CONFIGVAR['security_lockout_time'];
				$dbuser->updateLoginFail($userid, $logfail);
				$dbuser->updateLoginLockout($userid, 0, $locktime);
			}
			else
			{
				$logfail++;
				$dbuser->updateLoginFail($userid, $logfail);
			}
		}
		else
		{
			$logfail = 1;
			$locktime = time() + (int)$CONFIGVAR['security_lockout_time'];
			$dbuser->updateLoginFail($userid, $logfail);
			$dbuser->updateLoginLockout($userid, 0, $locktime);
		}
		if ($logfail > (int)$CONFIGVAR['security_login_failure_lockout'])
		{
			$locktime = time() + (int)$CONFIGVAR['security_lockout_time'];
			$dbuser->updateLoginLockout($userid, 1, $locktime);
		}
		handleError('Invalid Username/Password');
	}

	// Mark the current time
	$loginTime = time();

	// If we get here, then the password was correct.  Now we need
	// to check if the account has been disabled...or locked out,
	// which means we need to query the database again for the login
	// data.
	$rxa_login = $dbuser->queryLogin($userid);
	if ($rxa_login == false)
		handleError('Stored Data Conflict<br>Contact Your Administrator<br>XX32745');
	$lockout = (int)$rxa_login['locked'];
	$locktime = (int)$rxa_login['locktime'];
	if ($active == 0)
		handleError('Your account has been disabled.<br>Contact your administrator');
	if ($lockout != 0)
	{
		if ($loginTime < $locktime)
			handleError('Your account has been locked out.<br>Try again later.');
		// Account is no longer locked out.  Update the database.
		$dbuser->updateLoginLockout($userid, 0, 0);
	}

	// The user has successfully logged in. Now we load in their contact info.
	$rxa_contact = $dbuser->queryContact($userid);
	if ($rxa_contact == false)
		handleError('Stored Data Conflict<br>Contact Your Administrator<br>XX29174');

	// And the profile
	$rxa_profile = $dbconf->queryProfile($profid);
	if ($rxa_profile == false)
		handleError('Stored Data Conflict<br>Contact Your Administrator<br>XX87319');
	
	// Then we set some variables.
	$passChangeTime = (int)$rxa_login['timeout'];

	// Check if the user needs to change their password, or if they are
	// exempt.
	if ($account->checkAccountSpecial(false, $userid, $profid) == false)
	{
		if ($loginTime > $passChangeTime) $changePass = true;
			else $changePass = false;
	}
	else $changePass = false;

	// Final updates to the database.
	$dbuser->updateLoginFail($userid, 0);
	$dbuser->updateLoginLastlog($userid, $loginTime);

	// Load updated information into session variables
	user_login($rxa_users, $rxa_profile, $rxa_contact, $rxa_login);
	$_SESSION['passChange'] = $changePass;

	// The user is now logged in.  Initiate forced redirect
	// To configured banner page.
	$ajax->redirect('/' . $CONFIGVAR['html_banner_page']['value']);
	exit(0);
}

// OAuth Login
// This is called from the oauth.php library in the callback function.
// Logs the user into the system.
function oauth_login($userid, $rxu, $rxprov)
{
	global $CONFIGVAR;

	// Get the pertenant user data from the database.
	$rxc = getUserContact($userid);
	$rxp = getProfileData($rxu['profileid']);

	// Set the user's session.
	user_login($rxu, $rxp, $rxc, NULL);
	$_SESSION['expire'] = $time + $rxprov['expire'];
	$_SESSION['provider'] = $rxprov['provider'];

	// The user is now logged in.  Initiate forced redirect
	// To configured banner page.
	$ajax->redirect('/' . $CONFIGVAR['html_banner_page']['value']);
	exit(0);
}

// OpenID Login
// This is called from the openid.php library in the callback function.
// Logs the user into the system.
function openid_login($userid, $rxu, $rxprov)
{
	global $CONFIGVAR;

	// Get the pertenant user data from the database.
	$rxc = getUserContact($userid);
	$rxp = getProfileData($rxu['profileid']);

	// Set the user's session.
	user_login($rxu, $rxp, $rxc, NULL);
	$_SESSION['expire'] = $time + $rxprov['expire'];
	$_SESSION['provider'] = $rxprov['provider'];

	// The user is now logged in.  Initiate forced redirect
	// To configured banner page.
	$ajax->redirect('/' . $CONFIGVAR['html_banner_page']['value']);
	exit(0);
}

// Logs the user into the system and sets various session variables.
function user_login($user, $profile, $contact, $login)
{
	global $CONFIGVAR;

	$_SESSION['banner'] = false;
	$_SESSION['login'] = true;
	$_SESSION['loginTime'] = time();
	$_SESSION['regenTimeLast'] = time() + $CONFIGVAR['session_regen_time']['value'];
	if (!empty($login))
	{
		$_SESSION['loginLast'] = $login['lastlog'];
	}
	$_SESSION['nameReal'] = $contact['name'];
	$_SESSION['nameUser'] = $user['username'];
	$_SESSION['userId'] = $user['userid'];
	$_SESSION['profileId'] = $user['profileid'];
	$_SESSION['method'] = $user['method'];
	$_SESSION['portalType'] = $profile['portal'];
	$_SESSION['flagSys'] = $profile['bitmap_core'];
	$_SESSION['flagApp'] = $profile['bitmap_app'];
}

// Generates and displays the username prompt.
function html2Stage_username()
{
	$html = htmlFormOpen('submit_form');
	$html .= htmlUsername();
	$html .= htmlButtons();
	$html .= htmlFormClose();
	return $html;
}

// Generates and displays the password prompt.
function html2Stage_password($username)
{
	$html = htmlFormOpen('submit_form');
	$html .= htmlPrintUser($username);
	$html .= htmlPassword();
	$html .= htmlButtons();
	$html .= htmlFormClose();
	return $html;
}

// Generates and displays both the username and password prompts.
function html1Stage_login()
{
	$html = htmlFormOpen('submit_form');
	$html .= htmlUsername();
	$html .= htmlPassword();
	$html .= htmlButtons();
	$html .= htmlFormClose();
	return $html;
}

// Opens an HTML form.
function htmlFormOpen($name)
{
	$html = "				<form class=\"login form-horizontal\" id=\"$name\" method=post>
";
	return $html;
}

// Closes an HTML form.
function htmlFormClose()
{
	$html = "				</form>
";
	return $html;
}

// Shows an HTML Username field.
function htmlUsername()
{
	global $userMax;
	$html = "					<div class=\"row\">
						<div class=\"form-group\">
							<label class=\"control-label col-xs-3 text-right\" for=\"username\">Username</label>
							<div class=\"input-group col-xs-8\">
								<span class=\"input-group-addon\"><i class=\"glyphicon glyphicon-user\"></i></span>
								<input type=\"text\" class=\"form-control\" id=\"username\" maxlength=\"$userMax\">
							</div>
						</div>
					</div>
";
	return $html;
}

// Shows an HTML Password field.
function htmlPassword()
{
	global $passMax;
	$html = "					<div class=\"row\">
						<div class=\"form-group\">
							<label class=\"control-label col-xs-3 text-right\" for=\"password\">Password</label>
							<div class=\"input-group col-xs-8\">
								<span class=\"input-group-addon\"><i class=\"glyphicon glyphicon-lock\"></i></span>
								<input type=\"password\" class=\"form-control\" id=\"password\" maxlength=$passMax>
							</div>
						</div>
					</div>
";
	return $html;
}

// Shows the Username
function htmlPrintUser($username)
{
	$html = "					<div class=\"row\">
						<p>Enter password for $username</p>
					</div>
";
}

// Shows buttons
function htmlButtons()
{
	$html = "					<div class=\"row\">
						<div class=\"button\">
							<div class=\"form-group\">
								<span class=\"col-xs-2\"></span>
								<input type=\"button\" class=\"btn btn-default col-xs-3\" value=\"Submit\" onclick=\"loginAPI.submitForm()\">
								<span class=\"col-xs-2\"></span>
								<input type=\"button\" class=\"btn btn-default col-xs-3\" value=\"Reset\" onclick=\"loginAPI.resetForm()\">
								<span class=\"col-xs-2\"></span>
							</div>
						</div>
					</div>
";
	return $html;
}

// Send the login page template to the client.
function htmlPageTemplate()
{
	global $baseUrl;
?>
<!DOCTYPE html>
<html lang="en-US">
	<head>
		<title>Login Page</title>
		<!-- Favirotie Icons (Shows up next to the URL in the browser address bar)
			 Generated from https://realfavicongenerator.net/ -->
		<link rel="apple-touch-icon" sizes="180x180" href="<?php echo $baseUrl; ?>/images/favicons/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="<?php echo $baseUrl; ?>/images/favicons/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="<?php echo $baseUrl; ?>/images/favicons/favicon-16x16.png">
		<link rel="manifest" href="<?php echo $baseUrl; ?>/images/favicons/site.webmanifest">
		<link rel="mask-icon" href="<?php echo $baseUrl; ?>/images/favicons/safari-pinned-tab.svg" color="#5bbad5">
		<link rel="shortcut icon" href="<?php echo $baseUrl; ?>/images/favicons/favicon.ico">
		<meta name="msapplication-TileColor" content="#da532c">
		<meta name="msapplication-config" content="<?php echo $baseUrl; ?>/images/favicons/browserconfig.xml">
		<meta name="theme-color" content="#ffffff">
		<!-- The rest of the headers -->
		<script type="text/javascript" src="<?php echo $baseUrl; ?>/js/baseline/ajax.js"></script>
		<script type="text/javascript" src="<?php echo $baseUrl; ?>/js/baseline/chap.js"></script>
		<link rel="stylesheet" type="text/css" href="<?php echo $baseUrl; ?>/APIs/Bootstrap/bootstrap-3.3.7-dist/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="<?php echo $baseUrl; ?>/css/common.css">
		<link rel="stylesheet" type="text/css" href="<?php echo $baseUrl; ?>/css/login.css">
	</head>
	<body href-link="login.php" onload="initialRun()">
		<div class="vspace10"></div>
		<div class="image-banner-center">
			<img src="<?php echo $baseUrl; ?>/images/branding/trademark_large.png" />
		</div>
		<div class="vspace5"></div>
		<div class="image-border-top">
			<img src="<?php echo $baseUrl; ?>/images/border/border2a.gif" />
		</div>
		<div class="vspace5"></div>
		<div class="width75">
			<div class="color-blue text-center" id="responseTarget"></div>
			<div class="color-red text-center" id="errorTarget"></div>
			<div id="main"></div>
		</div>
		<div class="vspace5"></div>
		<div class="image-border-bottom">
			<img src="<?php echo $baseUrl; ?>/images/border/border2b.gif" />
		</div>
		<script type="text/javascript" src="<?php echo $baseUrl; ?>/APIs/JQuery/BaseJQuery/jquery-3.1.0.min.js"></script>
		<script type="text/javascript" src="<?php echo $baseUrl; ?>/APIs/Bootstrap/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="<?php echo $baseUrl; ?>/js/module/login.js"></script>
	</body>
</html>
<?php
}


?>