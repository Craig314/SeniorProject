<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application Portal Grid View

Because the HTML for this module is highly specialized, it is one of the
few files that actually has HTML in the rendering code.

The css filename must match the module name, so if the module filename is
abc123.php, then the associated stylesheet must be named abc123.css.  The
same condition applies to JavaScript files as well.

The following variables are optional and only need to be defined if
the features/abilities they represent are being used:

	$htmlInjectFile
		Specifies a HTML file to use as the template instead of the
		default page.

*/



// These variables must be set for every module. The variable moduleId
// must be a unique positive integer. Module IDs < 1000 are reserved for
// system use.  Therefore application module IDs will start at 1000.
$moduleFilename = 'gridportal.php';
$moduleTitle = 'Portal';
$moduleId = 0;

// This setting indicates that a file will be used instead of the
// default template.  Set to the name of the file to be used.
//$inject_html_file = '../dat/somefile.html';
$htmlInjectFile = false;

// Order matter here.  The modhead library needs to be loaded first.
// If additional libraries are needed, then load them afterwards.
const DIR = '../libs/';
require_once DIR . 'modhead.php';

// Called when the client sends a GET request to the server.
// This call comes from modhead.php.
function loadInitialContent()
{
	global $htmlInjectFile;

	if ($htmlInjectFile === false)
	{
		global $moduleFilename;
		global $moduleTitle;
		global $baseUrl;

		// The moduleTitle, baseUrl, and moduleFilename are mandatory
		// parameters and cannot be ommitted.  The moduleTitle sets
		// the title of the module as it appears on the brower's title
		// bar.  The baseUrl parameter is used when constructing URLs
		// to various resources on the server.  And finally, the
		// moduleFilename is required because AJAX needs a file on the
		// server to communicate with.

		// Left and right are for the navigation bar left side, right side.
		// It uses an associtive array to pass the contents to the HTML
		// template.  The key is the display name.  The value is the function
		// to be called.  Note that this uses the jQuery function call format.
		$left = array(
		'Home' => 'returnHome',
		);
		//$right = array(
		//);

		// The function bar sits below the navigation bar.  It has the same
		// properties as the navigation bar, with the addition that you can
		// use nested associtive arrays to group buttons together.
		// $funcBar = array();

		// jsfiles is an associtive array which contains additional
		// JavaScript filenames that should be included in the head
		// section of the HTML page.
		$jsfiles = array(
			"js/gridportal.js"
		);
	
		// cssfiles is an associtive array which contains additional
		// cascading style sheets that should be included in the head
		// section of the HTML page.
		$cssfiles = array(
			"/css/gridportal.css"
		);
	
		//loadTemplatePage($moduleTitle, $htmlUrl, $moduleFilename,
		//  $left, $right, $funcBar, $jsFiles, $cssFiles, $htmlFlags);
		html_inital_content_load($moduleTitle, $baseUrl, $moduleFilename,
	    "", "", "", $jsfiles, $cssfiles, '');
	}
	else
	{
		// Load file from disk and transmit it to client.
		if (file_exists($htmlInjectFile))
		{
			$result = readfile($htmlInjectFile);
		}
		else printErrorImmediately('Internal System Error: ' . $htmlInjectFile .
			' was not found.<br>Contact your system administrator.  XX34226');
	}
}

// Called when server is issued command -1. This call comes from
// modhead.php.
function loadAdditionalContent()
{
	/*
	Consider using the HTML insert methods instead of manually
	coding HTML here.  This way, everything looks the same and
	the messy HTML code isn't present to mess with the format
	of the script.  New insert methods for various HTML controls
	and constructs are being added on a regular basis.
	*/
	global $baseUrl;
	global $dbconf;

	// Set some variables.
	$admin = $account->checkAccountAdmin();
	$vendor = $account->checkAccountVendor();

	// Load the modaccess table.
	$rxa = $dbconf->queryModaccess($_SESSION['profId']);

	// Load the module table.
	$rxm = $dbconf->queryModuleAll();

?>
Module Template<br>
Additional HTML Content Here
<?php
	exit;
}

// Called when the initial command processor doesn't have the
// command number. This call comes from modhead.php.
function commandProcessor($commandId)
{
	switch ((int)$commandId)
	{
		default:
			// If we get here, then the command is undefined.
			$ajax->sendCode(ajaxClass::CODE_NOTIMP,
				'The command ' . $_POST['COMMAND'] .
				' has not been implemented');
			exit(1);
			break;
	}
}


// Called when server is issued command -1.
// This call comes from modhead.php.
function load_additional_content()
  {
    global $html_base_url;
    global $dbase;

    if (check_special_account())
        {
	  // Special accounts get to see all modules.
	  $rxb = $dbase->query_module_all();
	  if (!is_array($rxb))
	    {
	      print_errors();
	      html_ajax_send_command("Database: An unknown database error has occurred.");
	      exit;
	    }
	}
      else
        {
	  // Get module list data.
	  $rxa = $dbase->query_module_access($_SESSION['pos_id']);
	  $rxb = $dbase->query_module_common();
	  if ($rxa === false && $rxb === false)
	    {
	      if ($handerr->chkstate())
		  {
		    $errmsg = $handerr->geterrmsg();
		    html_ajax_send_command(803, $errmsg);
		  }
		else
		  {
		    html_ajax_send_command(803, "You do not have access to any modules.");
		  }
	      exit;
	    }
	  if (!is_array($rxb)) $rxb = array();
	  if (is_array($rxa))
	    {
	      foreach ($rxa as $fx => $vx)
		{
		  $row = $dbase->query_module($vx['module_id']);
		  if ($row == false) continue;
		  array_push($rxb, $row);
		}
	    }
	}

    // Walk the combined array and output HTML for each entry.
    foreach($rxb as $row)
      {
        if ($row == NULL) continue;
	$mod_id = $row['module_id'];
	$mod_dname = $row['display_name'];
	$mod_fname = $row['file_name'];
	$mod_iname = $row['icon_file'];

		// html generation
}
    exit;
	}




function checkPermissions(

// This is the function that writes the HTML to the client.
function writeModuleIcon($url, $id, $iname, $dname)
{
?>
	<div class="icon" onclick="loadModule(<?php echo $id; ?>)">
		<div class="iconimg">
			<img src="<?php echo $url. '/images/icon128/' . $iname; ?>">
		</div>
		<div class="icontxt iconfont"><?php echo $dname; ?></div>
	</div>
<?php
}

// Called by command_processor.
// Loads the specified module
function load_module()
  {
    global $dbase;
    global $vfystr;
    global $handerr;

    if (isset($_POST['MODULE']))
        $module_id = $_POST['MODULE'];
      else
        load_additional_content();
    $vfystr->strchk($module_id, 'MODULE', verify_string::STR_NUMERIC);
    if ($handerr->chkstate())
      {
	$errmsg = $handerr->geterrmsg();
	html_ajax_send_command(803, $errmsg);
	exit;
      }
    if ($dbase->query_module_hasaccess($module_id, $_SESSION['pos_id']) === false)
      {
	if ($handerr->chkstate())
	  {
	    $errmsg = $handerr->geterrmsg();
	    html_ajax_send_command(803, $errmsg);
	    exit;
	  }
	load_additional_content();
	exit;
      }
    $rx = $dbase->query_module($module_id);
    if ($rx === false) load_additional_content();
    html_ajax_redirect("modules/" . $rx['file_name']);
    exit;
  }

// Called when the initial command processor
// doesn't have the command number.
// This call comes from modhead.php.
function command_processor($command_id)
  {
    switch ((int)$command_id)
      {
	case 137:      // Load Module Command
	  load_module();
	  exit;
	  break;
        default:      // Undefined Command
	  html_ajax_send_code(501, "Undefined Command: " . $command_id);
	  exit;
	  break;
      }
  }


?>
