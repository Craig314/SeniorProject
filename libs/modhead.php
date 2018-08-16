<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application Module Header

This is the header file for modules.  This contains quite a bit
of common code that a module uses.

The module must define the following functions:

	loadInitialContent()
		Loads the initial page.  The page can be either the default template
		page, or a custom page.

	loadAdditionalContent()
		If the default page is used, this loads the page contents via HTML
		injection into a div tag in the template HTML.

	commandProcessor($command_id)
		This defines module specific commands.

The module must also define the following variables before this file is
included:

	$moduleFilename
		The filename for this module.

	$moduleTitle
		The display title for this module.

	$moduleId
		The ID number for this module.
		Must match what is in the database.

Notes:

This restarts the session so the $_SESSION superglobal array can be accessed.

AJAX cannot be used in this file since AJAX requires the initial page to load
first which has not happened yet when this file executes.  That is why we are
using html static methods for redirect and internal error message displays.

*/


require_once 'utility.php';
require_once 'confload.php';
require_once 'dbaseconf.php';
require_once 'account.php';
require_once 'session.php';
require_once 'security.php';
require_once 'html.php';
require_once 'ajax.php';
require_once 'error.php';
require_once 'vfystr.php';


// This is to be filled later.
$moduleData = NULL;

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
	global $moduleId;
	global $moduleData;
	global $vendor;
	global $admin;
	global $dbconf;

	// The vendor account always has access.
	if ($vendor != 0) return;

	// If only the vendor account has access and we get to this
	// point, then the user is not on the vendor account.
	if ($moduleData['vendor'] != 0) redirectPortal();

	// The admin account has access to everything that is not vendor
	// only.  So check for the admin account.
	if ($admin != 0) return;

	// Check if all users have access.  If all users do have access,
	// then don't bother with the rest of the checks.
	if ($moduleData['allusers'] == 1) return;

	// If this is one of the portals, then all users have access.
	if ($moduleId == $CONFIGVAR['html_grid_mod_id']['value']) return;
	if ($moduleId == $CONFIGVAR['html_link_mod_id']['value']) return;

	// Now check if the user has access according to their profile.
	if ($dbconf->queryModaccess($moduleId, $_SESSION['profileId']) == false)
		redirectPortal();
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
// their portal page as defined in their profile.
function redirectPortal()
{
	global $CONFIGVAR;
	global $dbconf;
	global $herr;

	if ($_SESSION['portalType'] == 1)
		html::redirect('/modules/' . $CONFIGVAR['html_linkportal_page']['value']);
	else
		html::redirect('/modules/' . $CONFIGVAR['html_gridportal_page']['value']);
	exit;
}

// Helper function for userCheckSecurity.  Redirects the user to
// the banner page.
function redirectBanner()
{
	global $CONFIGVAR;

	html::redirect('/' . $CONFIGVAR['html_banner_page']['value']);
	exit;
}

// Helper function for userCheckSecurity.  Redirects the user to
// the login page.
function redirectLogin()
{
	global $CONFIGVAR;

	html::redirect('/' . $CONFIGVAR['html_login_page']['value']);
	exit;
}

// Redirects the user to their portal page as defined in their
// profile using Ajax.
function redirectPortalAjax()
{
	global $CONFIGVAR;
	global $dbconf;
	global $herr;
	global $ajax;

	if ($_SESSION['portalType'] == 1)
		$ajax->redirect('/modules/' . $CONFIGVAR['html_linkportal_page']['value']);
	else
		$ajax->redirect('/modules/' . $CONFIGVAR['html_gridportal_page']['value']);
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
// when a module loads.
function httpMethod_GET()
{
	// We need to make sure that the user has access.
	checkUserSecurity();

	// Now we call the module specific initial content
	// generator (usually the template page) and then
	// exit the script.
	loadInitialContent();
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

	// Command Dispatcher
	// Should be integers and all the commands that the server
	// will recognize from the client will go here.
	switch ($commandId)
	{
		case -1:      // Load Additional Content
			loadAdditionalContent();
			exit(0);
			break;
		case -2:      // Heartbeat
			$ajax->sendCode(ajaxClass::CODE_OK, 'Heartbeat OK');
			exit(0);
			break;
		case -3:      // LOGOUT
			$_SESSION = array();
			$cookie = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000, $cookie['path'],
			$cookie['domain'], $cookie['secure'], $cookie['httponly']);
			session_destroy();
			$ajax->redirect('/' . $CONFIGVAR['html_login_page']['value']);
			exit(0);
			break;
		case -4:      // HOME
			redirectPortalAjax();
			exit(0);
			break;
		default:
			// If we get here, then proceed to module specific code.
			commandProcessor($commandId);
			exit(0);
			break;
	}
}

// The HTTP PUT method is used primarilly for bulk data transfer
// that POST may not be able to handle, such as uploading large
// files (File Size > 8MB).  Note, the module must enable this
// method by setting httpMethod_PUT_ENABLE to true.
function httpMethod_PUT()
{
	global $ajax;
	global $httpMethod_PUT_ENABLE;

	// Make sure that this method is enabled in the module.  If it is,
	// then we know that the module is expecting it.
	if (!isset($httpMethod_PUT_ENABLE))
	{
		// Unknown request method
		$ajax->sendCode(ajaxClass::CODE_NOMETH);
		exit(1);
	}
	else
	{
		if ($httpMethod_PUT_ENABLE !== true)
		{
			// Unknown request method
			$ajax->sendCode(ajaxClass::CODE_NOMETH);
			exit(1);
		}
	}

	// We need to make sure that the user has access.
	checkUserSecurity();

	// In additon, we also need to check the tokens as well.
	checkTokenSecurity(1);

	// Now we *NEED* the command to determine what we are going
	// to do.  If it's missing, then this function will not
	// return.
	$commandId = extractCommandId();

	// The PUT method uses custom commands only.
	// XXX: This should probably go to a separate command
	// processor specific for PUT.
	commandProcessor($commandId);
}

// Check to make sure that mandatory variables have been set.
if (!isset($moduleId) || empty($moduleId))
	printErrorImmediate('Internal Error: Module ID is not set.');
if (!isset($moduleTitle) || empty($moduleTitle))
	printErrorImmediate('Internal Error: Module Title is not set.');
if (!isset($moduleFilename) || empty($moduleFilename))
	printErrorImmediate('Internal Error: Module Filename is not set.');

// Check to make sure that the user is logged in.
if ($account->checkCredentials() == false) redirectLogin();

// Check to make sure that the user has seen the banner page.
if ($_SESSION['banner'] != true) redirectBanner();

// Set flags indicating that the logged in user is on a
// special account.
$admin = $account->checkAccountAdmin();
$vendor = $account->checkAccountVendor();
$special = $account->checkAccountSpecial();

// Loads the module data for this module, unless it is one of the
// portals, then there is no data in the database.
if ($moduleId != $CONFIGVAR['html_grid_mod_id']['value'] &&
	$moduleId != $CONFIGVAR['html_link_mod_id']['value'])
{
	$moduleData = $dbconf->queryModule($moduleId);
	if ($moduleData === false)
	{
		if ($herr->checkState())
			printErrorImmediate($herr->errorGetMessage());
		else
			printErrorImmediate('Module database query failed.');
	}

	// We need to make sure that the user has access.
	// This function does not return if this check fails.
	checkUserSecurity();
}

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