<?php

/*

Header file for modules.  This contains quite a bit of common code
that a module uses.

The module must define the following functions:

	load_initial_content()
		Loads the initial page.  The page can be either the default template
		page, or a custom page.

	load_additional_content()
		If the default page is used, this loads the page contents via HTML
		injection into a div tag in the template HTML.

	command_processor($command_id)
		This defines module specific commands.

Notes:

Session must already be running for this to function correctly.


*/


require_once "util.php";
require_once "confload.php";
require_once "account.php";
require_once "security.php";
require_once "database.php";
require_once "html.php";



// This functions checks to make sure that the request is
// appoperite for the user making the request.
// If the security checks fail, then the user is redirected
// to the approperite page.
// This funtion only returns if the user checks pass.
function checkUserSecurity()
{
	// Check User Credentials
	if ($sec->check_credentials() == false)
	{
		html_redirect($html_login_page);
		exit;
	}
	if (!check_special_account())
	{
		if ($dbase->query_module_hasaccess($module_id, $_SESSION['pos_id']) == false)
		{
			html_redirect("modules/" . $html_portal_page);
			exit;
		}
	}
	if ($_SESSION['banner'] != true)
	{
		html_redirect($html_banner_page);
		exit;
	}
}


// The HTTP GET method is the initial request to the server
// when a module loads.
if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
	// We need to make sure that the user has access.
	checkUserSecurity();

	// If the method is GET, then we call the module specific
	// initial content generator.
	load_initial_content();
	exit;
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
		$command_id = (int)$_POST['COMMAND'];
		switch ($command_id)
		{
			case -1:      // Load Additional
				load_additional_content();
				exit;
				break;
			case -2:      // Heartbeat
				html_ajax_send_code(200, "Heartbeat OK");
				exit;
				break;
			case -3:      // LOGOUT
				$_SESSION = array();
				$cookie = session_get_cookie_params();
				setcookie(session_name(), "", time() - 42000, $cookie["path"],
				$cookie["domain"], $cookie["secure"], $cookie["httponly"]);
				session_destroy();
				html_ajax_redirect($html_login_page);
				exit;
				break;
			case -4:      // HOME
				html_ajax_redirect("modules/" . $html_portal_page);
				exit;
				break;
			default:
				// If we get here, then proceed to module specific code.
				command_processor($command_id);
				exit;
				break;
		}
	}
	else
	{
		html_ajax_send_code(400);
		exit;
	}
}
else
{
	html_ajax_send_code(405);
	exit;
}


?>
