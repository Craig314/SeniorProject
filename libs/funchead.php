<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application Function Header

This is the header file for function.  This contains quite a bit
of common code that a function uses.  The purpose of a function is
to do one thing and to do it well.  This will take commands using
the HTTP methods GET or POST.

The function must define the following functions:

	commandProcessorGet($commandId)
		This defines function commands which are accessed using the
		GET method.
	
	commandProcessorPost($commandId)
		This defines function commands which are accessed using the
		POST method.

The function must also define the following variables before this file is
included:

	$functionFilename
		The filename for the function.
	
	$functionDisplayUpper
		Capitalized function name.  Used in various displays.

	$functionDisplayLower
		Lowercase function name.  Used in various displays.

	$functionPermission
		The associated permission bit in the current user's profile
		that if set, gives the user access to this function.
	
	$functionSystem
		Indicates if the permission bitmap to use is system flags
		or application flags.  It is very important that this is
		set correctly.


Notes:

This restarts the session so the $_SESSION superglobal array can be accessed.

*/


require_once 'utility.php';
require_once 'confload.php';
require_once 'dbaseconf.php';
require_once 'account.php';
require_once 'session.php';
require_once 'security.php';
require_once 'html.php';
require_once 'ajax.php';
require_once 'flag.php';
require_once 'error.php';
require_once 'vfystr.php';


// Restart the session.
$session->restart();

// Regenerate the session ID if needed.
$session->regenerateId();

// Sends an error message to the client.  Does not clear forms.
function handleError($message)
{
	global $ajax;

	$ajax->sendCommand(ajaxClass::CMD_ERRDISP, $message);
	exit(1);
}

// Sends an error message to the client.  Does clear forms.
function handleErrorClear($message)
{
	global $ajax;

	$ajax->sendCommand(ajaxClass::CMD_ERRCLRDISP, $message);
	exit(1);
}

// Sends an error message to the client.  Does not clear forms.
function handleErrorCont($message)
{
	global $ajax;

	$ajax->sendCommand(ajaxClass::CMD_ERRDISP, $message);
	exit(1);
}

// Sends an error message to the client.  Does clear forms.
function handleErrorClearCont($message)
{
	global $ajax;

	$ajax->sendCommand(ajaxClass::CMD_ERRCLRDISP, $message);
	exit(1);
}

// Sends a successful response message.
function sendResponse($message)
{
	global $ajax;

	$ajax->sendCommand(ajaxClass::CMD_OKDISP, $message);
	exit(1);
}

// Sends a successful response message and clears the form.
function sendResponseClear($message)
{
	global $ajax;

	$ajax->sendCommand(ajaxClass::CMD_OKCLRDISP, $message);
	exit(1);
}

// This functions checks to make sure that the request is
// appoperite for the user making the request. If the
// security checks fail, then the user is redirected to
// the approperite page. This funtion only returns if the
// user checks pass.
function checkUserSecurity()
{
	global $CONFIGVAR;
	global $functionPermission;
	global $vendor;
	global $admin;
	global $dbconf;

	// The vendor account always has access.
	if ($vendor != 0) return;

	// The admin account has access to everything that is not vendor
	// only.  So check for the admin account.
	if ($admin != 0) return;

	// Check if all users have access.  If all users do have access,
	// then don't bother with the rest of the checks.
	if ($functionPermission == -1) return;

	// Now check if the user has access according to their profile
	// permission flag bits.
	if ($functionSystem == true)
	{
		if (flag::sessionGetSys($functionPermission) == 0)
			redirectPortal();
	}
	else
	{
		if (flag::sessionGetApp($functionPermission) == 0)
			redirectPortal();
	}
}

// Checks to make sure that the token is present and the same
// as when the page was first sent to the user.
function checkTokenSecurity($method = 0)
{
	global $CONFIGVAR;

	if ($CONFIGVAR['session_use_tokens']['value'] != 0)
	{
		switch ($method)
		{
			case 0:		// POST method
				if (!isset($_POST['token']))
					printErrorImmediate('Security Error: Missing Access Token.');
				$secToken = $_POST['token'];
				break;
			case 1:		// PUT method
				if (!isset($_SERVER['HTTP_X_TOKEN']))
					printErrorImmediate('Security Error: Missing Access Token.');
				$secToken = $_SERVER['HTTP_X_TOKEN'];
				break;
			default:
				$secToken = '';
				break;
		}
		$result = strcasecmp($_SESSION['token'], $secToken);
		if ($result != 0)
			printErrorImmediate('Security Error: Invalid Access Token.');
	}
}

// Helper function for userCheckSecurity.  Redirects the user to
// the banner page using Ajax.
function redirectBanner()
{
	global $CONFIGVAR;

	$ajax->redirect('/' . $CONFIGVAR['html_banner_page']['value']);
	exit;
}

// Helper function for userCheckSecurity.  Redirects the user to
// the login page usin Ajax.
function redirectLogin()
{
	global $CONFIGVAR;

	$ajax->redirect('/' . $CONFIGVAR['html_login_page']['value']);
	exit;
}

// Redirects the user to their portal page as defined in their
// profile using Ajax.
function redirectPortal()
{
	global $CONFIGVAR;
	global $dbconf;
	global $herr;
	global $ajax;

	if ($_SESSION['portalType'] == 1)
	switch ($_SESSION['portalType'])
	{
		case 0:
			$ajax->redirect('/modules/' . $CONFIGVAR['html_gridportal_page']['value']);
			break;
		case 1:
			$ajax->redirect('/modules/' . $CONFIGVAR['html_linkportal_page']['value']);
			break;
		case 2:
			$ajax::redirect('/application/' . $CONFIGVAR['html_appportal_page']['value']);
			break;
	}
	exit;
}

// This will try to find a command parameter by checking
// several different places.  If a command is not found,
// then an error is displayed.
function extractCommandId()
{
	// First we try the POST superglobal.
	if (isset($_POST['COMMAND']))
	{
		if (!is_numeric($_POST['COMMAND']))
		{
			$ajax->sendCode(ajaxClass::CODE_BADREQ);
			exit(1);
		}
		$command = (integer)$_POST['COMMAND'];
		return $command;
	}

	// Next is the REQUEST superglobal.
	if (isset($_REQUEST['COMMAND']))
	{
		if (!is_numeric($_REQUEST['COMMAND']))
		{
			$ajax->sendCode(ajaxClass::CODE_BADREQ);
			exit(1);
		}
		$command = (integer)$_REQUEST['COMMAND'];
		return $command;
	}

	// Finally we check to see if it was sent as part of the
	// header.
	if (isset($_SERVER['HTTP_X_COMMAND']))
	{
		if (!is_numeric($_SERVER['HTTP_X_COMMAND']))
		{
			$ajax->sendCode(ajaxClass::CODE_BADREQ);
			exit(1);
		}
		$command = (integer)$_SERVER['HTTP_X_COMMAND'];
		return $command;
	}

	// If we get to this point, then we don't have a valid command
	// parameter which means that we cannot continue.  Send back
	// an error.
	$ajax->sendCode(ajaxClass::CODE_BADREQ);
	exit(1);
}

// The HTTP GET method is the initial request to the server
// when a function loads.
function httpMethod_GET()
{
	// We need to make sure that the user has access.
	checkUserSecurity();

	// In additon, we also need to check the tokens as well.
	checkTokenSecurity(0);

	// Now we *NEED* the command to determine what we are going
	// to do.  If it's missing, then this function will not
	// return.
	$commandId = extractCommandId();

	// And now we call the configured command dispatcher to process
	// the command.
	commandProcessorGet(commandId);
	exit(0);
}

// The HTTP POST request is used on subsequent queries to the
// server.  This is where command processing takes place.
function httpMethod_POST()
{
	global $ajax;
	global $CONFIGVAR;

	// We need to make sure that the user has access.
	checkUserSecurity();

	// In additon, we also need to check the tokens as well.
	checkTokenSecurity(0);

	// Now we *NEED* the command to determine what we are going
	// to do.  If it's missing, then this function will not
	// return.
	$commandId = extractCommandId();

	commandProcessorPost($commandId);
	exit(0);
}

// The HTTP PUT request is used for unlimited bulk data
// transfer to the server.  This is where command processing
// takes place.
function httpMethod_PUT()
{
	global $ajax;
	global $CONFIGVAR;

	// We need to make sure that the user has access.
	checkUserSecurity();

	// In additon, we also need to check the tokens as well.
	checkTokenSecurity(1);

	// Now we *NEED* the command to determine what we are going
	// to do.  If it's missing, then this function will not
	// return.
	$commandId = extractCommandId();

	commandProcessorPut($commandId);
	exit(0);
}

// Check to make sure that mandatory variables have been set.
if (!isset($functionFilename) || empty($functionFilename))
	printErrorImmediate('Internal Error: Function Filename is not set.');

// Check to make sure that the user is logged in.
if ($account->checkCredentials() == false) redirectLogin();

// Check to make sure that the user has seen the banner page.
if ($_SESSION['banner'] != true) redirectBanner();

// Set flags indicating that the logged in user is on a
// special account.
$admin = $account->checkAccountAdmin();
$vendor = $account->checkAccountVendor();
$special = $account->checkAccountSpecial();

// Check to make sure that the user has access to this function.
checkUserSecurity();

// Sets the base URL
$baseUrl = html::getBaseURL();


switch ($_SERVER['REQUEST_METHOD'])
{
	case 'GET':
		// GET request method
		httpMethod_GET();
		break;
	case 'POST':
		// POST request method
		httpMethod_POST();
		break;
	case 'PUT':
		// PUT request method
		httpMethod_PUT();
		break;
	default:
		// Unknown request method
		$ajax->sendCode(ajaxClass::CODE_NOMETH);
		exit(1);
		break;
}


?>