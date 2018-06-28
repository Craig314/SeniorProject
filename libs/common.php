<?php

// Import config
require_once "../libs/config.php";

// Debug Mode
if ($config_debug)
  {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
  }


// Start the session.
session_start();


// Include required libs
// **ORDER IS IMPORTANT**
require_once "../libs/error.php";
require_once "../libs/database.php";
require_once "../libs/vfystr.php";
require_once "../libs/security.php";
require_once "../libs/html.php";
require_once "../libs/util.php";
require_once "../libs/modhead.php";


?>
