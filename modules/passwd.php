<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application Password Change Module

This module allows the user to change their password.

This is one of the few pages which have embedded HTML.


*/


// These variables must be set for every module.

// The executable file for the module.  Filename and extension only,
// no path component.
$moduleFilename = 'passwd.php';

// The name of the module.  It shows in the title bar of the web
// browser and other places.
$moduleTitle = 'Change Password';

// $moduleId must be a unique positive integer. Module IDs < 1000 are
// reserved for system use.  Therefore application module IDs will
// start at 1000.
$moduleId = 4;

// The capitalized short display name of the module.  This shows up
// on buttons, and some error messages.
$moduleDisplayUpper = 'Password';

// The lowercase short display name of the module.  This shows up in
// various messages.
$moduleDisplayLower = 'password';

// Set to true if this is a system module.
$moduleSystem = true;

// Flags in the permissions bitmap of what permissions the user
// has in this module.  Currently not implemented.
$modulePermissions = array();



const BASEDIR = '../libs/';
require_once BASEDIR . 'dbaseuser.php';
require_once BASEDIR . 'password.php';
require_once BASEDIR . 'timedate.php';
require_once BASEDIR . 'modhead.php';

// Other things
$baseUrl = html::getBaseURL();

function loadInitialContent()
{
	global $CONFIGVAR;

	// Called on a GET operation.
	if ($CONFIGVAR['session_use_tokens']['value'] != 0) $token = $_SESSION['token'];
		else $token = false;
	showHeader('Change Password', 'Changing Password For', $_SESSION['nameUser'], $token);
	showPassword();
	showFooter();
	exit(0);
}

function commandProcessor($commandId)
{
	switch($commandId)
	{
		case 1:
			change_password();
			break;
		default:
			// If we get here, then the command is undefined.
			$ajax->sendCode(ajaxClass::CODE_NOTIMP,
				'The command ' . $_POST['COMMAND'] .
				' has not been implemented');
			exit(1);
			break;
	}
	exit(0);
}

// Changes the user password.
function change_password()
{
	global $CONFIGVAR;
	global $dbuser;
	global $vfystr;
	global $ajax;
	global $herr;

	// Set a few things up.
	$passMax = $CONFIGVAR['security_passwd_maxlen']['value'];
	$passMin = $CONFIGVAR['security_passwd_minlen']['value'];

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
	$vfystr->strchk($oldpass, 'Old Password', 'oldpass', verifyString::STR_PASSWD, true, $passMax, 1);
	if ($herr->checkState())
	{
		$errstr = $herr->errorGetMessage();
		handleError($errstr);
	}

	// Verify that the old password matches what is on record.
	$result = password::verify($oldpass, $hexsalt, $hexpass, $digest, $count);
	if ($result == false)
		handleError('Invalid existing password was entered.');
	
	// Check the new passwords for errors.
	$vfystr->strchk($oldpass, 'New Password 1', 'newpass1', verifyString::STR_PASSWD, true, $passMax, $passMin);
	$vfystr->strchk($oldpass, 'New Password 2', 'newpass2', verifyString::STR_PASSWD, true, $passMax, $passMin);
	if ($herr->checkState())
	{
		$errstr = $herr->errorGetMessage();
		handleError($errstr);
	}

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
	$ajax->sendResponseClear('Your password has been changed.');

	exit(0);
}

// Sends the banner page header to the client.
function showHeader($title, $message, $user, $token)
{
	global $baseUrl;
	global $moduleTitle;
	global $moduleFilename;
	global $vendor;
	global $admin;

	if ($vendor == true || $admin == true)
		$warning = 'WARNING<br>You are changing the password for a system account.<br>This is not recommended.<br>Proceed with caution.';
	else
		$warning = '';
?>
<!DOCTYPE html>
<html lang="en-US">
	<head>
		<title><?php echo $moduleTitle; ?></title>
		<script type="text/javascript" src="<?php echo $baseUrl; ?>/js/ajax.js"></script>
		<script type="text/javascript" src="<?php echo $baseUrl; ?>/js/passwd.js"></script>
		<link rel="stylesheet" type="text/css" href="<?php echo $baseUrl; ?>/APIs/Bootstrap/bootstrap-3.3.7-dist/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="<?php echo $baseUrl; ?>/css/common.css">
	</head>
	<body href-link="<?php echo $moduleFilename; ?>" onload="initialRun()">
<?php
	if ($token != false)
	{
		html::insertToken($token);
	}
?>
		<br><br>
		<div class="text-center">
			<h1 class="color-black"><?php echo $title; ?></h1>
			<h3 class="color-black"><?php echo $message; ?> <span class="color-blue"><?php echo $user; ?></span></h3>
			<h4 class="color-red"><?php echo $warning; ?></h4>
		</div>
		<div class="image-border-top">
			<img src="<?php echo $baseUrl; ?>/images/border2a.gif" />
		</div>
		<br>
		<div class="color-black" id="main"></div>
		<div class="color-blue" id="responseTarget"></div>
		<div class="color-red" id="errorTarget"></div>
		<br>
<?php
}

// Sends the banner page footer to the client.
function showFooter()
{
	global $baseUrl;
?>
		<div class="image-border-bottom">
			<img src="<?php echo $baseUrl; ?>/images/border2b.gif" />
		</div>
		<script type="text/javascript" src="<?php echo $baseUrl; ?>/APIs/JQuery/BaseJQuery/jquery-3.1.0.min.js"></script>
		<script type="text/javascript" src="<?php echo $baseUrl; ?>/APIs/Bootstrap/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
	</body>
</html>
<?php
}

// Sends the change password form to the client.
function showPassword()
{
	global $CONFIGVAR;

	$passMax = $CONFIGVAR['security_passwd_maxlen']['value'];
?>
		<div id="block_password">
			<div class="width75">
				<form class="passwd form-horizontal">
					<div class="row">
						<div class="form-group">
							<label class="control-label col-xs-3" for="oldpass">Old Password</label>
							<div class="col-xs-7">
								<input type="password" class="form-control" id="oldpass" maxlength=<?php echo $passMax; ?>>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="form-group">
							<label class="control-label col-xs-3" for="newpass1">Enter New Password</label>
							<div class="col-xs-7">
								<input type="password" class="form-control" id="newpass1" maxlength=<?php echo $passMax; ?>>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="form-group">
							<label class="control-label col-xs-3" for="newpass2">Enter Password Again</label>
							<div class="col-xs-7">
								<input type="password" class="form-control" id="newpass2" maxlength=<?php echo $passMax; ?>>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="button">
							<div class="form-group">
								<span class="col-xs-1"></span>
								<input type="button" class="btn btn-danger col-xs-2" value="Submit" onclick="submitPasswordChange()">
								<span class="col-xs-2"></span>
								<input type="button" class="btn btn-primary col-xs-2" value="Reset" onclick="clearForm()">
								<span class="col-xs-2"></span>
								<input type="button" class="btn btn-success col-xs-2" value="Return" onclick="ajaxServerCommand.returnHome()">
								<span class="col-xs-1"></span>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
<?php
}


?>