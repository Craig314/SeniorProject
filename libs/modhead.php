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


// This is to be filled later.
$moduleData = NULL;

// Restart the session.
$session->restart();


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

	// Check user credentials
	if ($account->checkCredentials() == false) redirectLogin();

	// Check for banner page
	if ($_SESSION['banner'] != true) redirectBanner();

	// Check if all users have access.  If all users do have access,
	// then don't bother with the rest of the checks.
	if ($moduleData['allusers'] == 1) return;

	// Check vendor only access.  If the admin cannot access the
	// module, then only then vendor can.
	if ($moduleId['admin'] == 0)
	{
		if (!$account->checkAccountVendor()) redirectPortal();
	}

	// Check if this is the admin account.
	if ($account->checkAccountAdmin()) return;

	// Now check if the user has access according to their profile.
	if ($dbconf->queryModaccess($module_id, $_SESSION['profileId']) == false)
		redirectPortal();
}

// Helper function for userCheckSecurity.  Redirects the user to
// their portal page as defined in their profile.
function redirectPortal()
{
	$rxq = $dbconf->queryProfile($_SESSION['profileId']);
	if ($rxq == false) printErrorImmediate('Database Error: Unable to get profile information');
	if ($rxq[''] == 1)
		html::redirect('/modules/' . $CONFIGVAR['html_linkportal_page']['value']);
	else
		html::redirect('/modules/' . $CONFIGVAR['html_gridportal_page']['value']);
	exit;
}

// Helper function for userCheckSecurity.  Redirects the user to
// the banner page.
function redirectBanner()
{
	html::redirect('/' . $CONFIGVAR['html_banner_page']['value']);
	exit;
}

// Helper function for userCheckSecurity.  Redirects the user to
// the login page.
function redirectLogin()
{
	html::redirect('/' . $CONFIGVAR['html_login_page']['value']);
	exit;
}

// Check to make sure that mandatory variables have been set.
if (!isset($moduleId) || empty($moduleId))
	printErrorImmediate('Internal Error: Module ID is not set.');
if (!isset($moduleTitle) || empty($moduleTitle))
	printErrorImmediate('Internal Error: Module Title is not set.');
if (!isset($moduleFilename) || empty($moduleFilename))
	printErrorImmediate('Internal Error: Module Filename is not set.');

// Loads the module data for this module.
$moduleData = $dbconf->queryModule($moduleId);
if ($moduleData === false) printErrorImmediate('Module database query failed.');

// We need to make sure that the user has access.
// This function does not return if this check fails.
checkUserSecurity();

// Sets the base URL
$baseUrl = html::getBaseURL();

// The HTTP GET method is the initial request to the server
// when a module loads.
if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
	// If the method is GET, then we call the module specific
	// initial content generator.
	loadInitialContent();
	exit(0);
}

// The HTTP POST request is used on subsequent queries to the
// server.  This is where command processing takes place.
else if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	// If the method is POST, then we need to check a few things
	// to make sure that the request is legit.
	if (isset($_POST['COMMAND']))
	{
		// We need to make sure that the user has access.
		checkUserSecurity();

		// Command Dispatcher
		// Should be integers and all the commands that the server
		// will recognize from the client will go here.
		$commandId = (int)$_POST['COMMAND'];
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
				$ajax->redirect($CONFIGVAR['html_login_page']['value']);
				exit(0);
				break;
			case -4:      // HOME
				redirectPortal();
				exit(0);
				break;
			default:
				// If we get here, then proceed to module specific code.
				commandProcessor($commandId);
				exit(0);
				break;
		}
	}
	else
	{
		// Malformed request
		$ajax->sendCode(ajaxClass::CODE_BADREQ);
		exit;
	}
}
else
{
	// Unknown request method
	$ajax->sendCode(ajaxClass::CODE_NOMETH);
	exit;
}


?>