<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application Module User Data Editor

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
$moduleFilename = 'userdata.php';

// The name of the module.  It shows in the title bar of the web
// browser and other places.
$moduleTitle = 'User Data Editor';

// $moduleId must be a unique positive integer. Module IDs < 1000 are
// reserved for system use.  Therefore application module IDs will
// start at 1000.
$moduleId = 31;

// The capitalized short display name of the module.  This shows up
// on buttons, and some error messages.
$moduleDisplayUpper = 'User';

// The lowercase short display name of the module.  This shows up in
// various messages.
$moduleDisplayLower = 'user';

// Set to true if this is a system module.
$moduleSystem = true;

// Flags in the permissions bitmap of what permissions the user
// has in this module.  Currently not implemented.
$modulePermissions = array();

// This setting indicates that a file will be used instead of the
// default template.  Set to the name of the file to be used.
//$inject_html_file = '../dat/somefile.html';
$htmlInjectFile = false;

// Order matters here.  The modhead library needs to be loaded last.
// If additional libraries are needed, then load them before.
require_once '../libs/includes.php';

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
		$left = array(
			'Home' => 'returnHome',
		);
		//$right = array(
		//);

		// The function bar sits below the navigation bar.  It has the same
		// properties as the navigation bar, with the addition that you can
		// use nested associtive arrays to group buttons together.
		// $funcBar = array();
		$funcBar = array(
			'Update' => 'updateDataItem',
			'View' => 'viewDataItem',
		);

		// jsfiles is an associtive array which contains additional
		// JavaScript filenames that should be included in the head
		// section of the HTML page.
		$jsFiles = array(
			'/js/baseline/common.js',
		);

		// cssfiles is an associtive array which contains additional
		// cascading style sheets that should be included in the head
		// section of the HTML page.
		// $cssFiles = array();

		// The final option, htmlFlags, is an array that holds the names
		// of supported options.  Currently, those options are checkbox,
		// datepick, tooltip, and type2.
		// $htmlFlags= array(
		// 	'checkbox',
		// 	'datepick',
		// 	'tooltip',
		//	'type2',
		// );
		$htmlFlags = array(
			'tooltip',
		);

		//html::loadTemplatePage($moduleTitle, $htmlUrl, $moduleFilename,
		//  $left, $right, $funcBar, $jsFiles, $cssFiles, $htmlFlags,
		//	$funcbar2, $funcbar3);
		html::loadTemplatePage($moduleTitle, $baseUrl, $moduleFilename,
			$left, '', $funcBar, $jsFiles, '', $htmlFlags);
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
	global $baseUrl;

	viewRecordView();
	exit(0);
}

// Called when the initial command processor doesn't have the
// command number. This call comes from modhead.php.
function commandProcessor($commandId)
{
	global $ajax;

	switch ((int)$commandId)
	{
		case 1:		// View
			viewRecordView();
			break;
		case 2:		// Update
			updateRecordView();
			break;
		case 5:	// Submit Update
			updateRecordAction();
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

// Helper function for the view functions below that loads information
// from the database and check for errors.
// XXX: Requires customization.
function databaseLoad()
{
	global $herr;
	global $moduleDisplayLower;
	global $dbuser;

	// Get current userid.
	$key = $_SESSION['userId'];

	// Query database.
	$rxa = $dbuser->queryContact($key);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Unable to retrieve required ' .
				$moduleDisplayLower . ' data.');
	}
	$rxb = $dbuser->queryUsersUserId($key);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Unable to retrieve required ' .
				$moduleDisplayLower . ' data.');
	}

	// Merge results.
	$rxa['userid'] = $key;
	$rxa['username'] = $rxb['username'];
	$rxa['orgid'] = $rxb['orgid'];

	return $rxa;
}

// The View Record view.
function viewRecordView()
{
	$rxa = databaseLoad();
	formPage(MODE_VIEW, $rxa);
}

// The Edit Record view.
function updateRecordView()
{
	$rxa = databaseLoad();
	formPage(MODE_UPDATE, $rxa);
}

// Updates the record in the database.
// XXX: Requires customization.
function updateRecordAction()
{
	global $ajax;
	global $herr;
	global $vfystr;
	global $CONFIGVAR;
	global $moduleDisplayUpper;
	global $moduleDisplayLower;
	global $dbuser;

	// Get field data.
	$fieldData = generateFieldCheck(FIELDCHK_ARRAY);

	// Get key data
	$key = $_SESSION['userId'];

	// Get user information from database.
	$rxa = $dbuser->queryContact($key);
	if ($rxa == false)
	{
		if ($herr->checkState() == true)
			handleError($herr->errorGetMessage());
		else
			handleError('Stored Data Conflict: Missing user contact record.');
	}

	$name = $rxa['name'];
	$haddr = getPostValue('haddr');
	$maddr = getPostValue('maddr');
	$email = getPostValue('email');
	$hphone = getPostValue('hphone');
	$cphone = getPostValue('cphone');
	$wphone = getPostValue('wphone');

	// Check field data.
	$vfystr->fieldchk($fieldData, 0, $haddr);
	$vfystr->fieldchk($fieldData, 1, $maddr);
	$vfystr->fieldchk($fieldData, 2, $email);
	$vfystr->fieldchk($fieldData, 3, $hphone);
	$vfystr->fieldchk($fieldData, 4, $cphone);
	$vfystr->fieldchk($fieldData, 5, $wphone);

	// Handle any errors from above.
	if ($vfystr->errstat() == true)
	{
		if ($herr->checkState() == true)
		{
			$rxe = $herr->errorGetData();
			$ajax->sendStatus($rxe);
			exit(1);
		}
	}

	// Safely encode all strings to prevent XSS attacks.
	$haddr = safeEncodeString($haddr);
	$maddr = safeEncodeString($maddr);
	$email = safeEncodeString($email);
	$hphone = safeEncodeString($hphone);
	$cphone = safeEncodeString($cphone);
	$wphone = safeEncodeString($wphone);
	
	// We are good, update the record
	$result = $dbuser->updateContact($key, $name, $haddr, $maddr, $email,
		$hphone, $wphone, $cphone);
	if ($result == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database: Record update failed. Key = ' . $key);
	}
	sendResponse($moduleDisplayUpper . ' update completed: key = ' . $key);
	exit(0);
}

// Generate generic form page.
function formPage($mode, $rxa)
{
	global $CONFIGVAR;
	global $moduleDisplayUpper;
	global $moduleDisplayLower;
	global $ajax;

	// Determine the editing mode.
	switch($mode)
	{
		case MODE_VIEW:			// View
			$msg1 = 'Viewing ' . $moduleDisplayUpper . ' Data For';
			$msg2 = $rxa['username'];
			$warn = '';
			$btnset = html::BTNTYP_NONE;
			$action = '';
			$disable = true;
			$default = true;
			$key = true;
			break;
		case MODE_UPDATE:		// Update
			$msg1 = 'Updating ' . $moduleDisplayUpper . ' Data For';
			$msg2 = $rxa['username'];
			$warn = '';
			$btnset = html::BTNTYP_UPDATE;
			$action = 'submitUpdate()';
			$disable = false;
			$default = true;
			$key = true;
			break;
		default:
			handleError('Internal Error: Contact your administrator.  XX82747');
			break;
	}

	// Custom field rendering code
	$userid = generateField(html::TYPE_TEXT, 'userid', 'User ID', 3, $rxa['userid'],
		'The numeric user id which uniquely identifies the user.', true, true);
	$uname = generateField(html::TYPE_TEXT, 'username', 'User Name', 6,
		$rxa['username'], 'The name the user logs in with.', true, true);
	$orgid = generateField(html::TYPE_TEXT, 'orgid', 'Organization ID', 6,
		$rxa['orgid'], 'The organizational ID the user has been assigned.' .
		'<br>This is different from the user ID.', true, true);
	$name = generateField(html::TYPE_TEXT, 'name', 'Name', 6, $rxa['name'],
		'The user\'s real name.', true, true);
	$haddr = generateField(html::TYPE_AREA, 'haddr', 'Home Address', 6,
		$rxa['haddr'], 'The user\'s home address.', $default, $disable);
	$haddr['rows'] = 5;
	$maddr = generateField(html::TYPE_AREA, 'maddr', 'Mailing Address', 6,
		$rxa['maddr'], 'The user\'s mailing address.', $default, $disable);
	$maddr['rows'] = 5;
	$email = generateField(html::TYPE_TEXT, 'email', 'E-Mail Address', 6,
		$rxa['email'], 'The user\'s e-mail address.', $default, $disable);
	$hphone = generateField(html::TYPE_TEXT, 'hphone', 'Home Phone Number', 4,
		$rxa['hphone'], 'The user\'s home phone number', $default, $disable);
	$cphone = generateField(html::TYPE_TEXT, 'cphone', 'Mobile Phone Number', 4,
		$rxa['cphone'], 'The user\'s mobile phone number', $default, $disable);
	$wphone = generateField(html::TYPE_TEXT, 'wphone', 'Work Phone Number', 4,
		$rxa['wphone'], 'The user\'s work phone number', $default, $disable);


	// Build out the form array.
	$data = array(
		array(
			'type' => html::TYPE_HEADING,
			'message1' => $msg1,
			'message2' => $msg2,
			'warning' => $warn,
		),
		array('type' => html::TYPE_TOPB2),
		array('type' => html::TYPE_WD75OPEN),
		array(
			'type' => html::TYPE_FORMOPEN,
			'name' => 'dataForm',
		),

		// Enter custom field data here.
		array(
			'type' => html::TYPE_FSETOPEN,
			'name' => 'Identification',
		),
		$userid,
		$uname,
		$orgid,
		array(
			'type' => html::TYPE_FSETCLOSE,
		),
		array(
			'type' => html::TYPE_FSETOPEN,
			'name' => 'Contact',
		),
		$name,
		$haddr,
		$maddr,
		$email,
		$hphone,
		$cphone,
		$wphone,
		array(
			'type' => html::TYPE_FSETCLOSE,
		),

		array(
			'type' => html::TYPE_ACTBTN,
			'dispname' => $moduleDisplayUpper,
			'btnset' => $btnset,
			'action' => $action,
		),
		array('type' => html::TYPE_FORMCLOSE),
		array('type' => html::TYPE_WDCLOSE),
		array('type' => html::TYPE_BOTB2),
		array('type' => html::TYPE_VTAB10),
	);

	// Render
	$ajax->writeMainPanelImmediate(html::pageAutoGenerate($data),
		generateFieldCheck());
}

// Generate the field definitions for client side error checking.
function fcData()
{
	global $CONFIGVAR;
	global $vfystr;

	$data = array(
		0 => array(
			'dispname' => 'Home Address',
			'name' => 'haddr',
			'type' => $vfystr::STR_ADDR,
			'noblank' => false,
			'max' => 100,
			'min' => 0,
		),
		1 => array(
			'dispname' => 'Mailing Address',
			'name' => 'maddr',
			'type' => $vfystr::STR_ADDR,
			'noblank' => false,
			'max' => 100,
			'min' => 0,
		),
		2 => array(
			'dispname' => 'EMail Address',
			'name' => 'email',
			'type' => $vfystr::STR_EMAIL,
			'noblank' => false,
			'max' => 50,
			'min' => 0,
		),
		3 => array(
			'dispname' => 'Home Phone',
			'name' => 'hphone',
			'type' => $vfystr::STR_PHONE,
			'noblank' => false,
			'max' => 30,
			'min' => 0,
		),
		4 => array(
			'dispname' => 'Cell Phone',
			'name' => 'cphone',
			'type' => $vfystr::STR_PHONE,
			'noblank' => false,
			'max' => 30,
			'min' => 0,
		),
		5 => array(
			'dispname' => 'Work Phone',
			'name' => 'wphone',
			'type' => $vfystr::STR_PHONE,
			'noblank' => false,
			'max' => 30,
			'min' => 0,
		),
	);
	return $data;
}


?>