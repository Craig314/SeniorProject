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
$session->start();
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

// Now we process requests.
if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
	// This is called on a GET operation.
	loginShowHeader();
	switch($modeOption)
	{
		case 0:
			loginShowNative(false, $modeOption);
			break;
		case 1:
			if ($CONFIGVAR['oauth_enable']['value'] == 0 &&
				$CONFIGVAR['openid_enable']['value'] == 0)
			{
				loginShowNative(false, 0);
			}
			else
			{
				loginShowOption(false);
				loginShowNative(true, $modeOption);
				if ($CONFIGVAR['oauth_enable']['value'] == 0) loginShowOAuth(true);
				if ($CONFIGVAR['openid_enable']['value'] == 0) loginShowOpenID(true);
			}
			break;
		default:
			loginShowNative(false, $modeOption);
			break;
	}
	loginShowFooter();
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
			case 1:
				native_login();
				break;
			case 2:
				oauth_login();
				break;
			case 3:
				openid_login();
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

function error_exit($message)
{
	global $ajax;
	$ajax->sendCommand(ajaxClass::CMD_ERRCLRCHTML, $message);
	exit(1);
}

// Performs the native login method.
function native_login()
{
	global $CONFIGVAR;
	global $userMax;
	global $passMax;
	global $ajax;
	global $herr;
	global $vfystr;
	global $dbuser;
	global $dbconf;
	global $account;
	global $session;

	// There's a lot of security issues that needs to be looked
	// for in this.  Robust coding practices.

	// We would like to do base64 decoding, but if someone tries
	// to screw with the parameter, then we just error it out.
	if (isset($_POST['base64']))
	{
		if (is_numeric($_POST['base64']))
		{
			$base64 = (int)$_POST['base64'];
			if ($base64 != 0 && $base64 != 1)
				error_exit('Invalid characters detected.');
		}
		else error_exit('Invalid characters detected.');
	}
	else error_exit('Missing encode parameter.');

	// Get and decode the client provided username.
	if (isset($_POST['native_username']))
	{
		$post = $_POST['native_username'];
		$urldec = rawurldecode($post);
		if ($base64 === 1) $b64dec = base64_decode($urldec);
			else $b64dec = $urldec;
		$username = $b64dec;
	}
	else error_exit('Missing username parameter.');

	// Get and decode the client provided password.
	if (isset($_POST['native_password']))
	{
		$post = $_POST['native_password'];
		$urldec = rawurldecode($post);
		if ($base64 === 1) $b64dec = base64_decode($urldec);
			else $b64dec = $urldec;
		$rpasswd = $b64dec;
	}
	else error_exit('Missing password parameter.');

	// Check user provided input.
	$vfystr->strchk($username, 'Username', '', verifyString::STR_USERID, true, $userMax, 1);
	$vfystr->strchk($rpasswd, 'Password', '', verifyString::STR_PASSWD, true, $passMax, 1);
	if ($herr->checkState())
	{
		$errstr = $herr->errorGetMessage();
		error_exit($errstr);
	}

	// We have all three parameters, now we run the validate.

	// Get userid and profid for the username from the database.
	$rxa_users = $dbuser->queryUsers($username);
	if ($rxa_users == false) error_exit('Invalid Username/Password');
	$userid = (int)$rxa_users['userid'];
	$profid = (int)$rxa_users['profileid'];

	// Get the login data
	$rxa_login = $dbuser->queryLogin($userid);
	if ($rxa_login == false)
		error_exit('Stored Data Conflict<br>Contact Your Administrator<br>XX32334');
	$hexpass	= (string)$rxa_login['passwd'];
	$hexsalt	= (string)$rxa_login['salt'];
	$digest		= (string)$rxa_login['digest'];
	$count		= (int)$rxa_login['count'];
	$logfail	= (int)$rxa_login['failcount'];
	$locktime	= (int)$rxa_login['locktime'];

	// Verify the password.  If too many attempts have been made
	// within a specified time period, then lockout the account
	// for the same configured amount of time.
	$passchk = password::verify($rpasswd, $hexsalt, $hexpass, $digest, $count);
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
		error_exit('Invalid Username/Password');
	}

	// Mark the current time
	$loginTime = time();

	// If we get here, then the password was correct.  Now we need
	// to check if the account has been disabled...or locked out,
	// which means we need to query the database again for the login
	// data.
	$rxa_login = $dbuser->queryLogin($userid);
	if ($rxa_login == false)
		error_exit('Stored Data Conflict<br>Contact Your Administrator<br>XX32745');
	$active = (int)$rxa_login['active'];
	$lockout = (int)$rxa_login['locked'];
	$locktime = (int)$rxa_login['locktime'];
	if ($active == 0)
		error_exit('Your account has been disabled.<br>Contact your administrator');
	if ($lockout != 0)
	{
		if ($loginTime < $locktime)
			error_exit('Your account has been locked out.<br>Try again later.');
		// Account is no longer locked out.  Update the database.
		$dbuser->updateLoginLockout($userid, 0, 0);
	}

	// The user has successfully logged in. Now we load in their contact info.
	$rxa_contact = $dbuser->queryContact($userid);
	if ($rxa_contact == false)
		error_exit('Stored Data Conflict<br>Contact Your Administrator<br>XX29174');

	// And the profile
	$rxa_profile = $dbconf->queryProfile($profid);
	if ($rxa_profile == false)
		error_exit('Stored Data Conflict<br>Contact Your Administrator<br>XX87319');
	
	// Then we set some variables.
	$lastLoginTime = (int)$rxa_login['lastlog'];
	$realName = (string)$rxa_contact['name'];
	$passChangeTime = (int)$rxa_login['timeout'];
	$portalType = (int)$rxa_profile['portal'];

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
	$_SESSION['banner'] = false;
	$_SESSION['login'] = true;
	$_SESSION['loginLast'] = $lastLoginTime;
	$_SESSION['loginTime'] = $loginTime;
	$_SESSION['nameUser'] = $username;
	$_SESSION['nameReal'] = $realName;
	$_SESSION['userId'] = $userid;
	$_SESSION['profileId'] = $profid;
	$_SESSION['passChange'] = $changePass;
	$_SESSION['portalType'] = $portalType;
	$_SESSION['regenTimeLast'] = time() + $CONFIGVAR['session_regen_time']['value'];
	$_SESSION['flagSystem'] = $rxa_profile['bitmap_core'];
	$_SESSION['flagApp'] = $rxa_profile['bitmap_app'];

	// The user is now logged in.  Initiate forced redirect
	// To configured banner page.
	$ajax->redirect('/' . $CONFIGVAR['html_banner_page']['value']);
	exit(0);
}

// OAuth Login
function oauth_login()
{
}

// OpenID Login
function openid_login()
{
}

// Send the login page header to the client.
function loginShowHeader()
{
	global $baseUrl;
?>
<!DOCTYPE html>
<html lang="en-US">
	<head>
		<title>Login Page</title>
		<script type="text/javascript" src="<?php echo $baseUrl; ?>/js/ajax.js"></script>
		<script type="text/javascript" src="<?php echo $baseUrl; ?>/js/login.js"></script>
		<link rel="stylesheet" type="text/css" href="<?php echo $baseUrl; ?>/APIs/Bootstrap/bootstrap-3.3.7-dist/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="<?php echo $baseUrl; ?>/css/common.css">
		<link rel="stylesheet" type="text/css" href="<?php echo $baseUrl; ?>/css/login.css">
	</head>
	<body>
		<div class="vspace10"></div>
		<div class="image-banner-center">
			<img src="<?php echo $baseUrl; ?>/images/seacore_logo_trademark_large.png" />
		</div>
		<div class="vspace5"></div>
		<div class="image-border-top">
			<img src="<?php echo $baseUrl; ?>/images/border2a.gif" />
		</div>
		<div class="vspace5"></div>
		<div class="width75">
			<div class="color-blue text-center" id="responseTarget"></div>
			<div class="color-red text-center" id="errorTarget"></div>
			<div id="main"></div>
		</div>
<?php
}

// Sends the login method choice to the client.
function loginShowOption($hidden)
{
	global $baseUrl;

	if ($hidden === true) $hide = 'hidden';
		else $hide = '';
?>
		<div id="method_chooser" <?php echo $hide; ?>>
			<div class="width75">
				<div class="color-black"><b>Select a login method.</b></div>
				<form class="login-choice" id="form_choice" method=post>
					<div class="row">
						<div class="button">
							<div class="form-group">
								<span class="col-xs-4"></span>
								<input type="button" class="btn btn-default col-xs-4" value="Native Login" onclick="unhideFormNative()">
								<span class="col-xs-4"></span>
							</div>
						</div>
					</div>
<?php
	if ($CONFIGVAR['oauth_enable']['value'] != 0)
	{
?>
					<div class="row">
						<div class="button">
							<div class="form-group">
								<span class="col-xs-4"></span>
								<input type="button" class="btn btn-default col-xs-4" value="OAuth Login" onclick="unhideFormOAuth()">
								<span class="col-xs-4"></span>
							</div>
						</div>
					</div>
<?php
	}
	if ($CONFIGVAR['openid_enable']['value'] != 0)
	{
?>
					<div class="row">
						<div class="button">
							<div class="form-group">
								<span class="col-xs-4"></span>
								<input type="button" class="btn btn-default col-xs-4" value="OpenID Login" onclick="unhideFormOpenID()">
								<span class="col-xs-4"></span>
							</div>
						</div>
					</div>
<?php
	}
?>
				</form>
			</div>
		</div>
<?php
}

// Sends the native login page to the client.
function loginShowNative($hidden, $mode)
{
	global $userMax;
	global $passMax;

	if ($hidden === true) $hide = 'hidden';
		else $hide = '';
?>
		<div id="native_form" <?php echo $hide; ?>>
			<div class="width75">
				<form class="login form-horizontal" id="form_native" method=post>
					<div class="row">
						<div class="form-group">
							<label class="control-label col-xs-3 text-right" for="native_username">Username</label>
							<div class="input-group col-xs-8">
								<span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
								<input type="text" class="form-control" id="native_username" maxlength=<?php echo $userMax; ?>>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="form-group">
							<label class="control-label col-xs-3 text-right" for="password">Password</label>
							<div class="input-group col-xs-8">
								<span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
								<input type="password" class="form-control" id="native_password" maxlength=<?php echo $passMax; ?>>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="button">
							<div class="form-group">
								<span class="col-xs-2"></span>
								<input type="button" class="btn btn-default col-xs-3" value="Submit" onclick="submitFormNative()">
								<span class="col-xs-2"></span>
								<input type="button" class="btn btn-default col-xs-3" value="Reset" onclick="resetFormNative()">
								<span class="col-xs-2"></span>
							</div>
						</div>
					</div>
<?php
	if ($mode > 0)
	{
?>					
					<div class="button">
						<div class="form-group">
							<span class="col-xs-5"></span>
							<input type="button" class="btn btn-default col-xs-2" value="Return" onclick="returnChooser()">
							<span class="col-xs-5"></span>
						</div>
					</div>
<?php
	}
?>					
				</form>
			</div>
		</div>
<?php
}

// Sends the OAuth login page to the client.
function loginShowOAuth($hidden)
{
	global $baseUrl;

	if ($hidden === true) $hide = 'hidden';
		else $hide = '';
?>
		<div id="oauth_form" <?php echo $hide; ?>>
			<div class="width75">
				<form class="login form-horizontal" id="form_oauth" method=post>
					<div class="button">
						<div class="form-group">
							<span class="col-xs-5"></span>
							<input type="button" class="btn btn-default col-xs-2" value="Return" onclick="returnChooser()">
							<span class="col-xs-5"></span>
						</div>
					</div>
				</form>
			</div>
		</div>
		</div>
<?php
}

// Sends the OpenID login page to the client.
function loginShowOpenID($hidden)
{
	global $baseUrl;

	if ($hidden === true) $hide = 'hidden';
		else $hide = '';
?>
		<div id="openid_form" <?php echo $hide; ?>>
			<div class="width75">
				<form class="login form-horizontal" id="openid_form" method=post>
					<div class="form-group">
						<label class="control-label col-xs-4" for="openid_url">OpenID URL</label>
						<div class="col-xs-8">
							<input type="text" class="form-control" id="openid_url" />
						</div>
					</div>
					<div class="button">
						<div class="form-group">
							<span class="col-xs-2"></span>
							<input type="button" class="btn btn-default col-xs-3" value="Submit" onclick="submitFormOpenID()">
							<span class="col-xs-2"></span>
							<input type="button" class="btn btn-default col-xs-3" value="Reset" onclick="resetFormOpenID()">
							<span class="col-xs-2"></span>
						</div>
					</div>
					<div class="button">
						<div class="form-group">
							<span class="col-xs-5"></span>
							<input type="button" class="btn btn-default col-xs-2" value="Return" onclick="returnChooser()">
							<span class="col-xs-5"></span>
						</div>
					</div>
				</form>
			</div>
		</div>
		</div>
<?php
}

// Sends the login page footer to the client.
function loginShowFooter()
{
	global $baseUrl;
?>
		<div class="vspace5"></div>
		<div class="image-border-bottom">
			<img src="<?php echo $baseUrl; ?>/images/border2b.gif" />
		</div>
		<script type="text/javascript" src="<?php echo $baseUrl; ?>/APIs/JQuery/BaseJQuery/jquery-3.1.0.min.js"></script>
		<script type="text/javascript" src="<?php echo $baseUrl; ?>/APIs/Bootstrap/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
	</body>
</html>
<?php
}

?>