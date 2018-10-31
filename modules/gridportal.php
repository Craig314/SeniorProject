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


// These variables must be set for every module.

// The executable file for the module.  Filename and extension only,
// no path component.
$moduleFilename = 'gridportal.php';

// The name of the module.  It shows in the title bar of the web
// browser and other places.
$moduleTitle = 'Portal';

// $moduleId must be a unique positive integer. Module IDs < 1000 are
// reserved for system use.  Therefore application module IDs will
// start at 1000.
$moduleId = 1;

// The capitalized short display name of the module.  This shows up
// on buttons, and some error messages.
$moduleDisplayUpper = 'Portal';

// The lowercase short display name of the module.  This shows up in
// various messages.
$moduleDisplayLower = 'portal';

// Set to true if this is a system module.
$moduleSystem = true;

// Flags in the permissions bitmap of what permissions the user
// has in this module.  Currently not implemented.
$modulePermissions = array();





// This setting indicates that a file will be used instead of the
// default template.  Set to the name of the file to be used.
//$inject_html_file = '../dat/somefile.html';
$htmlInjectFile = false;

// Order matter here.  The modhead library needs to be loaded first.
// If additional libraries are needed, then load them afterwards.
const BASEDIR = '../libs/';
require_once BASEDIR . 'modhead.php';

// Called when the client sends a GET request to the server.
// This call comes from modhead.php.
function loadInitialContent()
{
	global $htmlInjectFile;
	global $inject_html_file;

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
		//$right = array(
		//);

		// The function bar sits below the navigation bar.  It has the same
		// properties as the navigation bar, with the addition that you can
		// use nested associtive arrays to group buttons together.
		// $funcBar = array();

		// jsFiles is an associtive array which contains additional
		// JavaScript filenames that should be included in the head
		// section of the HTML page.
		$jsFiles = array(
			'/js/module/portal.js'
		);
	
		// cssFiles is an associtive array which contains additional
		// cascading style sheets that should be included in the head
		// section of the HTML page.
		$cssFiles = array(
			'/css/portal.css'
		);

		// The final option, htmlFlags, is an array that holds the names
		// of supported options.  Currently, those options are checkbox,
		// datepick, and tooltip.
		// $htmlFiles = array(
		// 	'checkbox',
		// 	'datepick',
		// 	'tooltip',
		// );
		$htmlFiles = array(
		 	'tooltip',
		);
	
		//html::loadTemplatePage($moduleTitle, $htmlUrl, $moduleFilename,
		//  $left, $right, $funcBar, $jsFiles, $cssFiles, $htmlFlags);
		html::loadTemplatePage($moduleTitle, $baseUrl, $moduleFilename,
	    	'', '', '', $jsFiles, $cssFiles, $htmlFiles); 
	}
	else
	{
		// Load file from disk and transmit it to client.
		if (file_exists($inject_html_file))
		{
			$result = readfile($inject_html_file);
		}
		else printErrorImmediate('Internal System Error: ' . $inject_html_file .
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
	global $account;
	global $dbconf;
	global $herr;
	global $admin;
	global $vendor;
	global $special;
	global $moduleId;

	// Get the current user's profile Id.
	$profId = $_SESSION['profileId'];

	// Load the modaccess table.
	$rxa = $dbconf->queryModaccessProfile($profId);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
	}

	// Load the module table.
	$rxm = $dbconf->queryModuleAll();
	if ($rxm == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('There are currently no modules to access.');
	}

	// Now we run the module list and check permissions and
	// active status on each one. 
	$access = false;
	foreach($rxm as $kxa => $vxa)
	{
		// Check if the module is active.
		if ($vxa['active'] == 0) continue;

		// Get what we need from the database array.
		$modId = $vxa['moduleid'];
		$modName = $vxa['name'];
		$modDesc = $vxa['description'];
		$modIcon = $vxa['iconname'] . '.png';

		// Check to see if the current module ID is the same as the
		// executing module ID.  If it is, then skip.
		if ($moduleId == $modId) continue;

		// Don't change the order of these checks.
		// Order Matters.

		// The vendor account always has access.
		if ($vendor != 0)
		{
			writeModuleIcon($baseUrl, $modId, $modIcon, $modName, $modDesc);
			$access = true;
			continue;
		}

		// Some modules are only for the vendor.
		if ($vxa['vendor'] != 0) continue;

		// The admin account has access to everything that is not vendor
		// only.  So we need to check if the user account is the admin
		// account.
		if ($admin != 0)
		{
			writeModuleIcon($baseUrl, $modId, $modIcon, $modName, $modDesc);
			$access = true;
			continue;
		}

		// Modules with allusers set can be accessed by anyone.
		if ($vxa['allusers'] != 0)
		{
			writeModuleIcon($baseUrl, $modId, $modIcon, $modName, $modDesc);
			$access = true;
			continue;
		}

		// Now we have to bash the module id against the modaccess list.
		// If the module id is not on the modaccess list, then that module
		// is not displayed.
		if (!empty($rxa))
		{
			foreach($rxa as $kxb => $vxb)
			{
				if ($modId == $vxb['moduleid'] && $profId == $vxb['profileid'])
				{
					writeModuleIcon($baseUrl, $modId, $modIcon, $modName, $modDesc);
					$access = true;
					break;
				}
			}
		}
	}

	// If $access is still false, then we print an error.
	if (!$access) handleError('You do not have access to any modules.');

	exit(0);
}

// Called when the initial command processor doesn't have the
// command number. This call comes from modhead.php.
function commandProcessor($commandId)
{
	switch ((int)$commandId)
	{
		case 5:
			loadModule();
			break;
		default:
			// If we get here, then the command is undefined.
			$ajax->sendCode(ajaxClass::CODE_NOTIMP,
				'The command ' . $_POST['COMMAND'] .
				' has not been implemented');
			exit(1);
			break;
	}
}

// This is the function that writes the HTML to the client.
function writeModuleIcon($url, $id, $iname, $dname, $desc)
{
	$tooltip = 'data-toggle="tooltip" data-html="true" title="' . $desc . '"';
?>
	<div class="icon" onclick="loadModule(<?php echo $id; ?>)" <?php echo $tooltip; ?>>
		<div class="iconimg">
			<img src="<?php echo $url. "/images/icon128/" . $iname; ?>">
		</div>
		<div class="icontxt iconfont"><?php echo $dname; ?></div>
	</div>
<?php
}

// Loads the selected module.
function loadModule()
{
	global $baseUrl;
	global $dbconf;
	global $herr;
	global $vendor;
	global $admin;

	// Check input.
	if (!isset($_POST['MODULE']))
	{
		$ajax->SendCode(ajaxClass::CODE_BADREQ, 'Missing module identifier');
		exit(1);
	}
	else
	{
		if (!is_numeric($_POST['MODULE']))
		{
			$ajax->SendCode(ajaxClass::CODE_BADREQ, 'Malformed module identifier');
			exit(1);
		}
		else
		{
			$modId = $_POST['MODULE'];
		}
	}

	// Load module data.
	$rxa = $dbconf->queryModule($modId);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Unable to get module information.');
	}

	// Perform security checks.
	if ($vendor != 0) redirect($rxa['filename']);
	if ($rxa['vendor'] != 0) handleError('You do not have access to the requested module.');
	if ($admin != 0) redirect($rxa['filename']);
	if ($rxa['allusers'] != 0) redirect($rxa['filename']);
	$rxm = $dbconf->queryModaccess($_SESSION['profileId'], $modId);
	if ($rxm == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('You do not have access to the requested module.');
	}

	// Redirect.
	redirect($rxa['filename']);
}

// Redirect
function redirect($filename)
{
	global $ajax;
	global $CONFIGVAR;

	$docRoot = $CONFIGVAR['server_document_root']['value'];
	$result = file_exists($docRoot . '/modules/' . $filename);
	if ($result) $ajax->redirect('/modules/' . $filename);
	else
	{
		$result = file_exists($docRoot . '/application/' . $filename);
		if ($result) $ajax->redirect('/application/' . $filename);
		else
			handleError('Configured module/application file is missing' .
				'<br>Contact your administrator.');
	}
	exit(0);
}


?>