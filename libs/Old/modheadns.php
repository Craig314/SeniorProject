<?php

// Note: Non-security version of modhead.php.
// For testing only 

require_once "config.php";

if ($config_debug)
  {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
  }


require_once "html.php";
require_once "database.php";

// This is a test version of the module header.
// It does not check user credentials.
// For development and testing only.


if ($_SERVER['REQUEST_METHOD'] == 'GET')
    {
      // If the method is GET, then we call the module specific
      // initial content generator.
      load_initial_content();
      exit;
    }
  else if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
      if (isset($_POST['COMMAND']))
	  {
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
