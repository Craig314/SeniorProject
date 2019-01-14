<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

This is the banner page which is displayed after the user
logs in.

This is one of the few pages which have embedded HTML.

This page will display the configured banner title and
banner message to the user.  They must click through to
continue to their configured portal page. If it's time
for the user to change their password, this page will
force them to do so.

*/


const BASEDIR = 'libs/';
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
require_once BASEDIR . 'timedate.php';

processSharedMemoryReload();

// Restart the user session.
$session->restart();

// Regenerate the session ID if needed.
$session->regenerateId();

// Make sure that we are logged in.
if (isset($_SESSION['login']))
{
	if ($_SESSION['login'] != true)
	{
		html::redirect('/' . $CONFIGVAR['html_login_page']['value']);
		exit(0);
	}
}
else
{
	html::redirect('/' . $CONFIGVAR['html_login_page']['value']);
	exit(0);
}

// Banner Messages
$bannerTitle = $CONFIGVAR['login_banner_title']['value'];
$bannerSubtitle = $CONFIGVAR['login_banner_subtitle']['value'];
$bannerMessage = $CONFIGVAR['login_banner_message']['value'];

// Last login time
$lastLoginTime = $_SESSION['loginLast'];
$lastLogin = timedate::unix2canonical($lastLoginTime);

// Other things
$baseUrl = html::getBaseURL();
$passMax = $CONFIGVAR['security_passwd_maxlen']['value'];
$passMin = $CONFIGVAR['security_passwd_minlen']['value'];

// Now we process requests.
if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
	// Called on a GET operation.
	if ($CONFIGVAR['session_use_tokens']['value'] != 0) $token = $_SESSION['token'];
		else $token = false;
	
	echo htmlTemplate($bannerTitle, $bannerSubtitle, $bannerMessage, $token);
	exit(0);
}
else if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	// Called on a POST operation.

	// Makes sure that the token set via the GET method matches on
	// the user's POST method.
	if ($CONFIGVAR['session_use_tokens']['value'] != 0)
	{
		if (!isset($_POST['token']))
			printErrorImmediate('Security Error: Missing Access Token.');
		$result = strcasecmp($_SESSION['token'], $_POST['token']);
		if ($result != 0)
			printErrorImmediate('Security Error: Invalid Access Token.');
	}

	// Process the command.
	if (isset($_POST['COMMAND']))
	{
		$command_id = (int)$_POST['COMMAND'];
		switch($command_id)
		{
			case -1:
				loadAdditionalContent();
				break;
			case 1:
				changePassword();
				break;
			case 2:
				redirectPortal();
				break;
			default:
				handleError('Invalid Command');
				break;
		}
		exit(0);
	}
	else
	{
		$ajax->sendCode(ajaxClass::CODE_BADREQ);
		exit(1);
	}
	exit(0);
}
else
{
	// Called when the http method is neither GET or POST.
	html::sendCode(ajaxClass::CODE_NOMETH);
	exit(1);
}

// Displays either the continue button or forces the user to change
// their password.
function loadAdditionalContent()
{
	global $ajax;

	if (isset($_SESSION['passChange']))
	{
		if ($_SESSION['passChange'] == true)	
		{
			$html = htmlChangePassword();
		}
		else
		{
			$html = htmlContinue();
		}
	}
	else
	{
		if ($_SESSION['method'] != 0)
		{
			$html = htmlContinue();
		}
		else
		{
			handleError("Internal Error: Password change flag missing.");
		}
	}
	$ajax->writeMainPanelImmediate($html);
}

// Redirects the user to their portal page.
function redirectPortal()
{
	global $CONFIGVAR;
	global $ajax;

	if (isset($_SESSION['passChange']))
	{
		if ($_SESSION['passChange'] == true)
			handleError('Cannot Continue: Your password must be changed.');
	}
	else
	{
		if ($_SESSION['method'] == 0)
		{
			handleError("Internal Error: Password change flag missing.");
		}
	}
	$_SESSION['banner'] = true;
	switch((int)$_SESSION['portalType'])
	{
		case 0:
			$ajax->redirect('/modules' . '/' . $CONFIGVAR['html_gridportal_page']['value']);
			break;
		case 1:
			$ajax->redirect('/modules' . '/' . $CONFIGVAR['html_linkportal_page']['value']);
			break;
		case 2:
			$ajax->redirect('/application' . '/' . $CONFIGVAR['html_appportal_page']['value']);
			break;
		default:
			handleError('Invalid redirect.  Contact your administrator.');
			break;
	}
}

// Exits out with an error message.
function handleError($message)
{
	global $ajax;

	$ajax->sendCommand(ajaxClass::CMD_ERRCLRDISP, $message);
	exit(1);
}


// Changes the user password.
function changePassword()
{
	global $passMin;
	global $passMax;
	global $dbuser;
	global $vfystr;
	global $ajax;
	global $herr;
	global $CONFIGVAR;

	// We would like to do base64 decoding, but if someone tries
	// to screw with the parameter, then we just error it out.
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

	// Get and decode the client provided old password.
	if (isset($_POST['oldpassword']))
	{
		$post = $_POST['oldpassword'];
		$urldec = rawurldecode($post);
		if ($base64 === 1) $b64dec = base64_decode($urldec);
			else $b64dec = $urldec;
		$oldpass = $b64dec;
	}
	else handleError('Missing old password parameter.');

	// Get and decode the client provided new password 1.
	if (isset($_POST['newpassword1']))
	{
		$post = $_POST['newpassword1'];
		$urldec = rawurldecode($post);
		if ($base64 === 1) $b64dec = base64_decode($urldec);
			else $b64dec = $urldec;
		$newpass1 = $b64dec;
	}
	else handleError('Missing new password 1 parameter.');

	// Get and decode the client provided new password 2.
	if (isset($_POST['newpassword2']))
	{
		$post = $_POST['newpassword2'];
		$urldec = rawurldecode($post);
		if ($base64 === 1) $b64dec = base64_decode($urldec);
			else $b64dec = $urldec;
		$newpass2 = $b64dec;
	}
	else handleError('Missing new password 2 parameter.');

	// Now we have everything that we need to change the user's
	// password.

	// Load the user's login data.
	$userid = $_SESSION['userId'];
	$username = $_SESSION['nameUser'];
	$rxa_login = $dbuser->queryLogin($userid);
	if ($rxa_login == false)
		handleError('Stored Data Conflict<br>Contact Your Administrator<br>XX32745');
	$hexpass	= (string)$rxa_login['passwd'];
	$hexsalt	= (string)$rxa_login['salt'];
	$digest		= (string)$rxa_login['digest'];
	$count		= (int)$rxa_login['count'];

	// Check the old password for errors.
	$vfystr->strchk($oldpass, 'Old Password', '', verifyString::STR_PASSWD, true, $passMax, 1);
	if ($herr->checkState())
		handleError($herr->errorGetMessage());

	// Verify that the old password matches what is on record.
	$result = password::verify($oldpass, $hexsalt, $hexpass, $digest, $count);
	if (!$result)
		handleError('Invalid existing password was entered.');
	
	// Check the new passwords for errors.
	$vfystr->strchk($newpass1, 'New Password 1', '', verifyString::STR_PASSWD, true, $passMax, $passMin);
	$vfystr->strchk($newpass2, 'New Password 2', '', verifyString::STR_PASSWD, true, $passMax, $passMin);
	if ($herr->checkState())
		handleError($herr->errorGetMessage());

	// Makes sure that the two new passwords match.
	if (strcmp($newpass1, $newpass2) != 0)
		handleError('The new passwords do not match.');
	
	// Make sure that the old and new passwords do not match.
	if (strcmp($oldpass, $newpass1) == 0)
		handleError('The old and new passwords cannot match');
	
	// Make sure that the new password does not match the username.
	if (strcmp($username, $newpass1) == 0)
		handleError('The new password cannot match your username.');

	// Make sure that the new password does not contain the username.
	if (stripos($newpass1, $username) !== false)
		handleError('The new password cannot contain your username.');

	// Make sure that the new password complies with
	// complexity requirements.
	if (!password::checkComplexity($newpass1))
	{
		$msg = 'The new password does not meet complexity requirements.';
		switch($CONFIGVAR['security_passwd_complex_level']['value'])
		{
			case 0:		// Shouldn't happen
				handleError('Internal Error: Contact your administrator.  XX28362');
				break;
			case 1:
				$mxe = 'Password must contain upper and lower case letters.';
				break;
			case 2:
				$mxe = 'Password must contain upper and lower case letters and numbers.';
				break;
			case 3:
				$mxe = 'Password must contain upper and lower case letters, numbers, and symbols.';
				break;
			default:	// Shouldn't happen
				handleError('Internal Error: Contact your administrator.  XX27572');
				break;
		}
		handleError($msg . '<br>' . $mxe);
	}

	// If we get to this point, then all checks have passed.

	// Encode password
	// Here we overwrite what was in the variables previously.
	password::encryptNew($newpass1, $hexsalt, $hexpass, $digest,
		$CONFIGVAR['security_hash_rounds']['value']);
	
	// Write updated login data to database.
	$nextchange = time() + $CONFIGVAR['security_passexp_timeout']['value'];
	$result = $dbuser->updateLoginPassword($userid, $nextchange, $digest,
		$count, $hexsalt, $hexpass);
	if (!$result)
		handleError($herr->errorGetMessage());
	
	// Clear the password change flag.
	$_SESSION['passChange'] = false;

	// Advance the page.
	$timeout = (integer)($CONFIGVAR['security_passexp_timeout']['value'] / 86400);
	$message = 'Your password has been changed.<br>';
	$message .= 'It must be changed again in ' . $timeout . ' days.';
	echo htmlContinue($message);
	exit(0);
}

// Sends the change password form to the client.
function htmlChangePassword()
{
	global $passMax;
	$html = "
		<div class=\"width75\">
			<form class=\"passwd form-horizontal\">
				<div class=\"form-group\">
					<label class=\"control-label col-xs-4\" for=\"oldpass\">Old Password</label>
					<div class=\"col-xs-8\">
						<input type=\"password\" class=\"form-control\" id=\"oldpass\" maxlength=\"$passMax\">
					</div>
				</div>
				<div class=\"form-group\">
					<label class=\"control-label col-xs-4\" for=\"newpass1\">Enter New Password</label>
					<div class=\"col-xs-8\">
						<input type=\"password\" class=\"form-control\" id=\"newpass1\" maxlength=\"$passMax\">
					</div>
				</div>
				<div class=\"form-group\">
					<label class=\"control-label col-xs-4\" for=\"newpass2\">Enter Password Again</label>
					<div class=\"col-xs-8\">
						<input type=\"password\" class=\"form-control\" id=\"newpass2\" maxlength=\"$passMax\">
					</div>
				</div>
				<div class=\"button\">
					<div class=\"form-group\">
						<span class=\"col-xs-3\"></span>
						<input type=\"button\" class=\"btn btn-default col-xs-2\" value=\"Submit\" onclick=\"submitPasswordChange()\">
						<span class=\"col-xs-2\"></span>
						<input type=\"button\" class=\"btn btn-default col-xs-2\" value=\"Reset\" onclick=\"clearForm()\">
						<span class=\"col-xs-3\"></span>
					</div>
				</div>
			</form>
		</div>";
	return $html;
}

// Send the continue button with an optional message
// to the client.
function htmlContinue($message = '')
{
	$html = "
		<div class=\"width75\">";
	if (isset($_SESSION['loginLast']))
	{
		$date = timedate::unix2canonical($_SESSION['loginLast']);
		$html .= "
			<div class=\"text-center\">
				<h4 class=\"color-blue\">
					Last Login:
					<span class=\"color-red\">
						$date
					</span>
				</h4>
			</div>";
	}
	if (!empty($message))
	{
		$html .= "
			<div class=\"text-center\">
				<h4 class=\"color-blue\">
					$message
				</h4>
			</div>";
	}
	$html .= "
			<form class=\"form-horizontal\">
				<div class=\"button\">
					<div class=\"form-group\">
						<span class=\"col-xs-4\"></span>
						<input type=\"button\" class=\"btn btn-default col-xs-4\" value=\"Continue\" onclick=\"submitContinue()\">
						<span class=\"col-xs-4\"></span>
					</div>
				</div>
			</form>
		</div>
		<div class=\"vspace10\"></div>
";
	return $html;
}

// Sends the banner page template to the client.
function htmlTemplate($title, $subtitle, $message, $token)
{
	global $baseUrl;
?>
<!DOCTYPE html>
<html lang="en-US">
	<head>
		<title>Login Banner</title>
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
		<script type="text/javascript" src="<?php echo $baseUrl; ?>/js/module/banner.js"></script>
		<link rel="stylesheet" type="text/css" href="<?php echo $baseUrl; ?>/APIs/Bootstrap/bootstrap-3.3.7-dist/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="<?php echo $baseUrl; ?>/css/common.css">
	</head>
	<body href-link="banner.php" onload="initialRun()">
<?php
	if ($token != false)
	{
		echo html::insertToken($token);
	}
?>
		<br><br>
		<div class="text-center">
			<h1 class="color-red"><?php echo $title; ?></h1>
			<h4 class="color-red"><?php echo $subtitle; ?></h4>
			<h4 class="color-inherit"><?php echo $message; ?></h4>
		</div>
		<div class="image-border-top">
			<img src="<?php echo $baseUrl; ?>/images/border/border2a.gif" />
		</div>
		<br>
		<div class="width75">
			<div class="color-blue" id="responseTarget"></div>
			<div class="color-red" id="errorTarget"></div>
			<div class="color-black" id="main"></div>
		</div>
		<br>
		<div class="image-border-bottom">
			<img src="<?php echo $baseUrl; ?>/images/border/border2b.gif" />
		</div>
		<script type="text/javascript" src="<?php echo $baseUrl; ?>/APIs/JQuery/BaseJQuery/jquery-3.1.0.min.js"></script>
		<script type="text/javascript" src="<?php echo $baseUrl; ?>/APIs/Bootstrap/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
	</body>
</html>
<?php
}


?>