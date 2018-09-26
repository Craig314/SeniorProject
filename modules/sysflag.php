<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application Module Template

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
$moduleFilename = 'sysflag.php';

// The name of the module.  It shows in the title bar of the web
// browser and other places.
$moduleTitle = 'System Flags';

// $moduleId must be a unique positive integer. Module IDs < 1000 are
// reserved for system use.  Therefore application module IDs will
// start at 1000.
$moduleId = 5;

// The capitalized short display name of the module.  This shows up
// on buttons, and some error messages.
$moduleDisplayUpper = 'System Flags';

// The lowercase short display name of the module.  This shows up in
// various messages.
$moduleDisplayLower = 'system flags';

// Set to true if this is a system module.
$moduleSystem = true;

// Flags in the permissions bitmap of what permissions the user
// has in this module.  Currently not implemented.
$modulePermissions = array();



// These are the data editing modes.
const MODE_VIEW	= 0;
const MODE_UPDATE	= 1;
const MODE_INSERT	= 2;
const MODE_DELETE	= 3;

// This setting indicates that a file will be used instead of the
// default template.  Set to the name of the file to be used.
//$inject_html_file = '../dat/somefile.html';
$htmlInjectFile = false;

// Order matters here.  The modhead library needs to be loaded last.
// If additional libraries are needed, then load them before.
const BASEDIR = '../libs/';
require_once BASEDIR . 'modhead.php';

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
		$funcBar = array(
			array(
				'Insert' => 'insertDataItem',
				'Update' => 'updateDataItem',
				'Delete' => 'deleteDataItem',
			),
			'View' => 'viewDataItem',
			'List' => 'listDataItems',
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
		// datepick, and tooltip.
		// $htmlFlags= array(
		// 	'checkbox',
		// 	'datepick',
		// 	'tooltip',
		// );
		$htmlFlags = array(
			'tooltip',
		);

		//html::loadTemplatePage($moduleTitle, $htmlUrl, $moduleFilename,
		//  $left, $right, $funcBar, $jsFiles, $cssFiles, $htmlFlags);
		html::loadTemplatePage($moduleTitle, $baseUrl, $moduleFilename,
			$left, '', $funcBar, $jsFiles, '', $htmlFlags);
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
	global $baseUrl;
	global $dbconf;
	global $herr;

	// First we build the unassigned array.
	$flag = array();
	for ($i = 0; $i < FLAG_COUNT_SYSTEM; $i++)
	{
		$temp = array(
			'flag' => $i,
			'name' => 'Unassigned',
			'desc' => 'This flag is currently unassigned.',
		);
		$flag[$i] = $temp;
	}

	// Get data from database and merge it into the above array.
	$rxa = $dbconf->queryFlagdescCoreAll();
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessages());
	}
	else
	{
		foreach ($rxa as $kx => $vx)
		{
			$flag[$vx['flag']]['name'] = $vx['name'];
			$flag[$vx['flag']]['desc'] = $vx['description'];
		}
	}

	// Generate Selection Table.
	$list = array(
		'type' => html::TYPE_RADTABLE,
		'name' => 'select_item',
		'clickset' => true,
		'condense' => true,
		'hover' => true,
		'titles' => array(
			// Add column titles here
			'Name',
			'ID',
		),
		'tdata' => array(),
		'tooltip' => array(),
	);
	foreach ($flag as $kx => $vx)
	{
		$tdata = array(
			// These are the values that show up under the columns above.
			// The *FIRST* value is the value that is sent when a row
			// is selected.  AKA Key Field.
			$vx['flag'],
			$vx['name'],
			$vx['flag'],
		);
		array_push($list['tdata'], $tdata);
		array_push($list['tooltip'], $vx['desc']);
	}

	// Generate rest of page.
	$data = array(
		array(
			'type' => html::TYPE_HEADING,
			'message1' => 'System Flags',
		),
		array('type' => html::TYPE_TOPB1),
		array('type' => html::TYPE_WD75OPEN),
		array(
			'type' => html::TYPE_FORMOPEN,
			'name' => 'select_table',
		),

		// Enter custom data here.
		$list,


		array('type' => html::TYPE_FORMCLOSE),
		array('type' => html::TYPE_WDCLOSE),
		array('type' => html::TYPE_BOTB1)
	);

	// Render
	echo html::pageAutoGenerate($data);

	exit(0);
}

// Called when the initial command processor doesn't have the
// command number. This call comes from modhead.php.
function commandProcessor($commandId)
{
	switch ((int)$commandId)
	{
		case 1:		// View
			viewRecordView();
			break;
		case 2:		// Update
			updateRecordView();
			break;
		case 3:		// Insert
			insertRecordView();
			break;
		case 4:		// Delete
			deleteRecordView();
			break;
		case 12:	// Submit Update
			updateRecordAction();
			break;
		case 13:	// Submit Insert
			insertRecordAction();
			break;
		case 14:	// Submit Delete
			deleteRecordAction();
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
	global $dbconf;

	$key = getPostValue('select_item', 'hidden');
	if ($key == NULL)
		handleError('You must select a ' . $moduleDisplayLower . ' from the list view.');
	$rxa = $dbconf->queryFlagdescCore($key);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessages());
		else
			$rxa = array(
				'flag' => $key,
				'name' => 'Unassigned',
				'description' => 'This flag is currently unassigned.',
			);
	}
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

// The Add Record view.
function insertRecordView()
{
	$rxa = databaseLoad();
	formPage(MODE_INSERT, $rxa);
}

// The Delete Record view.
function deleteRecordView()
{
	$rxa = databaseLoad();
	formPage(MODE_DELETE, $rxa);
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
	global $dbconf;

	// Get data
	$key = getPostValue('hidden');
	$id = getPostValue('flag');
	$name = getPostValue('name');
	$desc = getPostValue('desc');

	// Check key data.
	if ($key == NULL)
		handleError('Missing ' . $moduleDisplayLower . ' selection data.');
	if ($id == NULL)
		handleError('Missing ' . $moduleDisplayLower . ' selection data.');
	if (!is_numeric($key))
		handleError('Malformed key sequence.');
	if (!is_numeric($id))
		handleError('Malformed key sequence.');
	if ($key != $id)
		handleError('Database key mismatch.');

	// Check fields.
	$vfystr->strchk($id, 'Flag', 'flag', verifyString::STR_PINTEGER, true, 127, 0);
	$vfystr->strchk($name, 'Name', 'name', verifyString::STR_ALPHANUM, true, 32, 1);
	$vfystr->strchk($desc, 'Description', 'desc', verifyString::STR_DESC,
		false , 256, 0);

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
	$name = safeEncodeString($name);
	$desc = safeEncodeString($desc);
	
	// We are good, insert or update the record
	$res = $dbconf->queryFlagdescCore($key);
	if ($res == false)
	{
		$result = $dbconf->insertFlagdescCore($key, $name, $desc);
	}
	else
	{
		$result = $dbconf->updateFlagdescCore($key, $name, $desc);
	}
	if ($result == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database: Record operation failed. Key = ' . $key);
	}
	sendResponse($moduleDisplayUpper . ': Database operation completed: key = ' . $key);
	exit(0);
}

// Inserts the record into the database.
function insertRecordAction()
{
	// In this case, update and insert are the same thing.
	updateRecordAction();
}

// Deletes the record from the database.
// XXX: Requires customization.
function deleteRecordAction()
{
	global $ajax;
	global $herr;
	global $vfystr;
	global $CONFIGVAR;
	global $moduleDisplayUpper;
	global $moduleDisplayLower;
	global $dbconf;

	// Gather data...
	$key = getPostValue('hidden');
	$id = getPostValue('flag');

	// ...and check it.
	if ($key == NULL)
		handleError('Missing ' . $moduleDisplayLower . ' module selection data.');
	if ($id == NULL)
		handleError('Missing ' . $moduleDisplayLower . ' selection data.');
	if (!is_numeric($key))
		handleError('Malformed key sequence.');
	if (!is_numeric($id))
		handleError('Malformed key sequence.');
	if ($key != $id)
		handleError('Database key mismatch.');
	
	// Now remove the record from the database.
	$result = $dbconf->deleteFlagdescCore($key);
	if ($result == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessages());
		else
			handleError('Database Error: Unable to delete ' . $moduleDisplayLower .
				' data. Key = ' . $key);
	}
	sendResponse($moduleDisplayUpper . ' delete completed: key = ' . $key);
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
			$msg2 = 'Flag ' . $rxa['flag'];
			$warn = '';
			$btnset = html::BTNTYP_VIEW;
			$action = '';
			$hideValue = $rxa['flag'];
			$disable = true;
			$default = true;
			$key = true;
			break;
		case MODE_UPDATE:		// Update
			$msg1 = 'Updating ' . $moduleDisplayUpper . ' Data For';
			$msg2 = 'Flag ' . $rxa['flag'];
			$warn = '';
			$btnset = html::BTNTYP_UPDATE;
			$action = 'submitUpdate()';
			$hideValue = $rxa['flag'];
			$disable = false;
			$default = true;
			$key = true;
			break;
		case MODE_INSERT:		// Insert
			$msg1 = 'Inserting ' . $moduleDisplayUpper . ' Data';
			$msg2 = 'Flag ' . $rxa['flag'];
			$warn = '';
			$btnset = html::BTNTYP_INSERT;
			$action = 'submitInsert()';
			$hideValue = $rxa['flag'];
			$disable = false;
			$default = true;
			$key = true;
			break;
		case MODE_DELETE:		// Delete
			$msg1 = 'Deleting ' . $moduleDisplayUpper . ' Data For';
			$msg2 = 'Flag ' . $rxa['flag'];
			$warn = 'If the flag is deleted, then the profile editor will<br>no longer display it.';
			$btnset = html::BTNTYP_DELETE;
			$action = 'submitDelete()';
			$hideValue = $rxa['flag'];
			$disable = true;
			$default = true;
			$key = true;
			break;
		default:
			handleError('Internal Error: Contact your administrator.  XX82747');
			break;
	}

	// Hidden field to pass key data
	if (isset($hideValue))
	{
		$hidden = array(
			'type' => html::TYPE_HIDE,
			'fname'=> 'hiddenForm',
			'name' => 'hidden',
			'data' => $hideValue,
		);
	}
	else $hidden = array();

	// Load $rxa with dummy values for insert mode.
	if ($mode == MODE_INSERT)
	{
		// Variable $rxa is null when the exit mode is insert.
		// Datafill this array with dummy values to prevent PHP
		// from issuing errors.
		// $rxa = array(
		// 	'' => '',
		// );
	}

	// Custom field rendering code
	$flag = generateField(html::TYPE_TEXT, 'flag', 'Flag', 3,
		$rxa['flag'], 'The number of the flag.', $default, $key);
	$name = generateField(html::TYPE_TEXT, 'name', 'Name', 6,
		$rxa['name'], 'The name of the flag.', $default, $disable);
	$desc = generateField(html::TYPE_AREA, 'desc', 'Description', 8,
		$rxa['description'], 'The flag\'s description.', $default, $disable);
	$desc['rows'] = 5;

	// Build out the form array.
	$data = array(
		$hidden,
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
		$flag,
		$name,
		$desc,

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
function generateFieldCheck()
{
	global $CONFIGVAR;
	global $vfystr;

	$data = array(
		0 => array(
			'name' => 'flag',
			'type' => $vfystr::STR_PINTEGER,
			'noblank' => true,
			'max' => 127,
			'min' => 0,
		),
		1 => array(
			'name' => 'name',
			'type' => $vfystr::STR_ALPHANUM,
			'noblank' => true,
			'max' => 32,
			'min' => 3,
		),
		2 => array(
			'name' => 'desc',
			'type' => $vfystr::STR_DESC,
			'noblank' => false,
			'max' => 256,
			'min' => 0,
		),
	);
	$fieldcheck = json_encode($data);
	return $fieldcheck;
}


?>