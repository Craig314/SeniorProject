<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application Module Data Editor

This edits the module definition data in the database.  This module is
set for vendor only access.

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
$moduleFilename = 'modedit.php';

// The name of the module.  It shows in the title bar of the web
// browser and other places.
$moduleTitle = 'Module Data Editor';

// $moduleId must be a unique positive integer. Module IDs < 1000 are
// reserved for system use.  Therefore application module IDs will
// start at 1000.
$moduleId = 10;

// The capitalized short display name of the module.  This shows up
// on buttons, and some error messages.
$moduleDisplayUpper = 'Module';

// The lowercase short display name of the module.  This shows up in
// various messages.
$moduleDisplayLower = 'module';

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
				'/js/module/modedit.js',
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
	global $dbconf;
	global $herr;
	global $moduleTitle;
	global $moduleDisplayLower;

	// Dump the module database and process it.
	$rxm = $dbconf->queryModuleAll();
	if ($rxm == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessages());
		else
			handleError('There are no ' . $moduleDisplayLower . 's in the database to edit.');
	}

	// Generate selection table.
	$list = array(
		'type' => html::TYPE_RADTABLE,
		'name' => 'select_item',
		'clickset' => true,
		'condense' => true,
		'hover' => true,
		'titles' => array(
			'Name',
			'ID',
			'File',
			'Icon',
			'Active',
			'Vendor',
			'All Users',
		),
		'tdata' => array(),
		'tooltip' => array(),
	);
	foreach($rxm as $kx => $vx)
	{
		$tdata = array(
			$vx['moduleid'],	// Needed for the radio button.
			$vx['name'],
			$vx['moduleid'],
			$vx['filename'],
			$vx['iconname'],
			$vx['active'],
			$vx['vendor'],
			$vx['allusers'],
		);
		array_push($list['tdata'], $tdata);
		array_push($list['tooltip'], $vx['description']);

	}

	// Generate rest of page.
	$data = array(
		array(
			'type' => html::TYPE_HEADING,
			'message1' => $moduleTitle,
			'warning' => 'Editing a module can have drastic consequences on application functionality.<br>Proceed With Caution.',
		),
		array('type' => html::TYPE_TOPB1),
		array('type' => html::TYPE_WD75OPEN),
		array(
			'type' => html::TYPE_FORMOPEN,
			'name' => 'select_table',	
		),
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
	global $ajax;

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
function databaseLoad()
{
	global $dbconf;
	global $herr;
	global $moduleDisplayLower;

	$key = getPostValue('select_item', 'hidden');
	if ($key == NULL)
		handleError('You must select a ' . $moduleDisplayLower .
			' from the list view.');
	$rxa = $dbconf->queryModule($key);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessages());
		else
			handleError('Database Error: Unable to retrieve required ' .
				$moduleDisplayLower . ' data.');
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
	formPage(MODE_INSERT, NULL);
}

// The Delete Record view.
function deleteRecordView()
{
	$rxa = databaseLoad();
	formPage(MODE_DELETE, $rxa);
}

// Updates the record in the database.
function updateRecordAction()
{
	global $ajax;
	global $herr;
	global $vfystr;
	global $moduleDisplayUpper;
	global $moduleDisplayLower;
	global $dbconf;

	// Get data
	$key = getPostValue('hidden');
	$id = getPostValue('modid');
	$name = getPostValue('modname');
	$desc = getPostValue('moddesc');
	$file = getPostValue('modfile');
	$icon = getPostValue('modicon');
	$act = getPostValue('modact');
	$all = getPostValue('modalluser');
	$vend = getPostValue('modvend');
	$sys = getPostValue('modsys');

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

	// Check mandatory fields.
	$vfystr->strchk($id, 'Module ID', 'modid', verifyString::STR_PINTEGER, true, 2147483647, 1);
	$vfystr->strchk($name, 'Name', 'modname', verifyString::STR_ALPHA, true, 32, 3);
	$vfystr->strchk($desc, 'Description', 'moddesc', verifyString::STR_ASCII, true, 256, 1);
	$vfystr->strchk($file, 'Filename', 'modfile', verifyString::STR_FILENAME, true, 50, 1);
	$vfystr->strchk($icon, 'Icon', 'modicon', verifyString::STR_FILENAME, true, 50, 1);
	
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
	
	// Convert boolean values
	if (!empty($act)) $act = 1; else $act = 0;
	if (!empty($all)) $all = 1; else $all = 0;
	if (!empty($vend)) $vend = 1; else $vend = 0;
	if (!empty($sys)) $sys = 1; else $sys = 0;

	// We are good, update the record.
	$result = $dbconf->updateModule($key, $name, $desc, $file, $icon, (int)$act,
		(int)$all, (int)$sys, (int)$vend);
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

// Inserts the record into the database.
function insertRecordAction()
{
	global $ajax;
	global $herr;
	global $vfystr;
	global $moduleDisplayUpper;
	global $moduleDisplayLower;
	global $dbconf;

	// Get data
	$id = getPostValue('modid');
	$name = getPostValue('modname');
	$desc = getPostValue('moddesc');
	$file = getPostValue('modfile');
	$icon = getPostValue('modicon');
	$act = getPostValue('modact');
	$all = getPostValue('modalluser');
	$vend = getPostValue('modvend');
	$sys = getPostValue('modsys');

	// Check mandatory fields.
	$vfystr->strchk($id, 'Module ID', 'modid', verifyString::STR_PINTEGER, true, 2147483647, 1);
	$vfystr->strchk($name, 'Name', 'modname', verifyString::STR_ALPHA, true, 32, 3);
	$vfystr->strchk($desc, 'Description', 'moddesc', verifyString::STR_ASCII, true, 256, 1);
	$vfystr->strchk($file, 'Filename', 'modfile', verifyString::STR_FILENAME, true, 50, 1);
	$vfystr->strchk($icon, 'Icon', 'modicon', verifyString::STR_FILENAME, true, 50, 1);
	
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

	// Convert boolean values
	if (!empty($act)) $act = 1; else $act = 0;
	if (!empty($all)) $all = 1; else $all = 0;
	if (!empty($vend)) $vend = 1; else $vend = 0;
	if (!empty($sys)) $sys = 1; else $sys = 0;

	// We are good, update the record.
	$result = $dbconf->insertModule($id, $name, $desc, $file, $icon, (int)$act,
		(int)$all, (int)$sys, (int)$vend);
	if ($result == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database: Record insert failed. Key = ' . $id);
	}
	sendResponseClear($moduleDisplayUpper . ' insert completed: key = '
		. $id);
	exit(0);
}

// Deletes the record from the database.
function deleteRecordAction()
{
	global $herr;
	global $moduleDisplayUpper;
	global $moduleDisplayLower;
	global $dbconf;

	// Gather data...
	$key = getPostValue('hidden');
	$id = getPostValue('modid');
	$modsys = getPostValue('modsys');

	// ...and check it.
	if ($key == NULL)
		handleError('Missing ' . $moduleDisplayLower . ' module selection data.');
	if ($id == NULL)
		handleError('Missing ' . $moduleDisplayLower . ' selection data.');
	if ($modsys != NULL)
		handleError('System modules cannot be deleted.');
	if (!is_numeric($key))
		handleError('Malformed key sequence.');
	if (!is_numeric($id))
		handleError('Malformed key sequence.');
	if ($key != $id)
		handleError('Database key mismatch.');
	
	// Now remove the module from the database.
	$result = $dbconf->deleteModule($key);
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
	global $baseUrl;
	global $ajax;

	// Determine the editing mode.
	switch($mode)
	{
		case MODE_VIEW:			// View
			$msg1 = 'Viewing ' . $moduleDisplayUpper . ' Data For';
			$msg2 = $rxa['name'];
			$warn = '';
			$btnset = html::BTNTYP_VIEW;
			$action = '';
			$hideValue = $rxa['moduleid'];
			$disable = true;
			$default = true;
			$key = true;
			break;
		case MODE_UPDATE:		// Update
			$msg1 = 'Updating ' . $moduleDisplayUpper . ' Data For';
			$msg2 = $rxa['name'];
			$warn = 'Editing module data can have drastic consequences<br>' .
				'on application operation.';
			$btnset = html::BTNTYP_UPDATE;
			$action = 'submitUpdate()';
			$hideValue = $rxa['moduleid'];
			$disable = false;
			$default = true;
			$key = true;
			break;
		case MODE_INSERT:		// Insert
			$msg1 = 'Inserting ' . $moduleDisplayUpper . ' Data';
			$msg2 = '';
			$warn = '';
			$btnset = html::BTNTYP_INSERT;
			$action = 'submitInsert()';
			$disable = false;
			$default = false;
			$key = false;
			break;
		case MODE_DELETE:		// Delete
			$msg1 = 'Deleting ' . $moduleDisplayUpper . ' Data For';
			$msg2 = $rxa['name'];
			if ($rxa['system'] != 0)
				$warn = 'You cannot delete a system module.';
			else
				$warn = 'Deleting module data can have drastic consequences<br>' .
					'on application operation.';
			$btnset = html::BTNTYP_DELETE;
			$action = 'submitDelete()';
			$hideValue = $rxa['moduleid'];
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

	// Another hidden field to pass the base URL.
	$hideurl = array(
		'type' => html::TYPE_HIDE,
		'fname'=> 'base_urlForm',
		'name' => 'base_url',
		'data' => $baseUrl,
	);

	// Load $rxa with dummy values for insert mode.
	if ($mode == MODE_INSERT)
	{
		$rxa = array(
			'moduleid' => '',
			'name' => '',
			'description' => '',
			'filename' => '',
			'iconname' => '',
			'active' => '',
			'allusers' => '',
			'vendor' => '',
			'system' => '',
		);
	}

	// Scans the icon directory for icon file names and creates a
	// pulldown list of them on insert or update modes.  Otherwise
	// the field is a text box.
	$firstimage = '';
	if ($mode == MODE_INSERT || $mode == MODE_UPDATE)
	{
		// Setup
		$iconlist = array();

		// Get the directory listing.
		$icon = scandir('../images/icon128');
		if ($icon === false)
			handleError('File System Error: Unable to get icon filenames.<br>Contact your administrator.');
		
		// Remove the filename extension as they are all .png files.
		foreach($icon as $kx => $vx)
		{
			if ($vx == '.') continue;
			if ($vx == '..') continue;
			$index = strrpos($vx, '.');
			$temp = substr($vx, 0, $index);
			$iconlist[$temp] = $temp;
			if (empty($firstimage)) $firstimage = $temp;
		}
		unset($icon);
		$modicon = array(
			'type' => html::TYPE_PULLDN,
			'label' => 'Icon',
			'default' => $rxa['iconname'],
			'name' => 'modicon',
			'fsize' => 4,
			'optlist' => $iconlist,
			'tooltip' => 'The icon name that the module uses&#013' .
				'when displayed on the portal page.',
			'disable' => $disable,
			'event' => 'onchange',
			'action' => 'changeImage()',
		);
	}
	else
	{
		$modicon = generateField(html::TYPE_TEXT, 'modicon', 'Icon', 4,
			$rxa['iconname'], 'The icon name that the module uses&#013' .
			'when displayed on the portal page.', $default, $disable);
	}

	// Scans the module directory for module file names and creates
	// a pulldown list of them on insert or update modes.  Otherwise
	// the field is a text box.
	if ($mode == MODE_INSERT || $mode == MODE_UPDATE)
	{
		// Setup
		$filelist = array();

		// Get the directory listing.
		$modFiles = scandir('.');
		if ($modFiles === false)
			handleError('File System Error: Unable to get module filenames.<br>Contact your administrator.');
		$appFiles = scandir('../application');
		if ($appFiles === false)
			handleError('File System Error: Unable to get application filenames.<br>Contact your administrator.');
		$files = array_merge($modFiles, $appFiles);
		
		// Remove the filename extension as they are all .png files.
		foreach($files as $kx => $vx)
		{
			// Exclue files/directories that we don't want.
			if ($vx == '.') continue;
			if ($vx == '..') continue;
			if ($vx == 'template.php') continue;
			$filelist[$vx] = $vx;
		}
		unset($files);
		unset($modFiles);
		unset($appFiles);
		$modfile = array(
			'type' => html::TYPE_PULLDN,
			'label' => 'Filename',
			'default' => $rxa['filename'],
			'name' => 'modfile',
			'fsize' => 4,
			'optlist' => $filelist,
			'tooltip' => 'The module\'s executable filename.',
			'disable' => $disable,
		);
	}
	else
	{
		$modfile = generateField(html::TYPE_TEXT, 'modfile', 'Filename', 4,
			$rxa['filename'], 'The module\'s executable filename.', $default,
			$disable);
	}

	// Icon images
	switch ($mode)
	{
			case MODE_VIEW:
			case MODE_UPDATE:
			case MODE_DELETE:
				$imagefile = $baseUrl . '/images/icon128/' . $rxa['iconname']
					. '.png';
				$imagealt = $rxa['iconname'];
				break;
			case MODE_INSERT:
				$imagefile = $baseUrl . '/images/icon128/' . $firstimage
					. '.png';
				$imagealt = $firstimage;
				break;
	}
	$iconimage = array(
		'type' => html::TYPE_IMAGE,
		'name' => 'icon_image',
		'src' => $imagefile,
		'alt' => $imagealt,
		'width' => 128,
		'height' => 128,
		'lsize' => 4,
	);

	// Custom field rendering code
	$modid   = generateField(html::TYPE_TEXT, 'modid', 'Module ID', 3,
		$rxa['moduleid'], 'The numeric ID of the module.', $default, $key);
	$modname = generateField(html::TYPE_TEXT, 'modname', 'Name', 6, $rxa['name'],
		'Display name of the module.', $default, $disable);
	$moddesc = generateField(html::TYPE_AREA, 'moddesc', 'Description', 6,
		$rxa['description'], 'The module\'s description.', $default, $disable);
	$moddesc['rows'] = 5;
	$modact  = generateField(html::TYPE_CHECK, 'modact', 'Active', 1,
		$rxa['active'], 'Indicates if the module is active or not.',
		$default, $disable);
	$modact['sidemode'] = true;
	$modact['side'] = 0;
	$modall  = generateField(html::TYPE_CHECK, 'modalluser', 'All Users', 1,
		$rxa['allusers'], 'Indicates if all users have access to this module.',
		$default, $disable);
	$modall['sidemode'] = true;
	$modall['side'] = 1;
	$modvend = generateField(html::TYPE_CHECK, 'modvend', 'Vendor Only', 1,
		$rxa['vendor'], 'Indicates that only the vendor has access to this' .
		' module.', $default, $disable);
	$modvend['sidemode'] = true;
	$modvend['side'] = 0;
	$modsys  = generateField(html::TYPE_CHECK, 'modsys', 'System', 1,
		$rxa['system'], 'Indicates that this is a system module.', $default,
		$disable);
	$modsys['sidemode'] = true;
	$modsys['side'] = 1;

	// Build out the form array.
	$data = array(
		$hidden,
		$hideurl,
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
		$modid,
		$modname,
		$moddesc,
		$modfile,
		$modicon,
		$iconimage,
		$modact,
		$modall,
		$modvend,
		$modsys,

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
			'name' => 'modid',
			'type' => $vfystr::STR_PINTEGER,
			'noblank' => true,
			'max' => 2147483647,
			'min' => 1,
		),
		1 => array(
			'name' => 'modname',
			'type' => $vfystr::STR_ALPHA,
			'noblank' => true,
			'max' => 32,
			'min' => 3,
		),
		2 => array(
			'name' => 'moddesc',
			'type' => $vfystr::STR_ASCII,
			'noblank' => true,
			'max' => 256,
			'min' => 1,
		),
		3 => array(
			'name' => 'modfile',
			'type' => $vfystr::STR_FILENAME,
			'noblank' => true,
			'max' => 50,
			'min' => 1,
		),
		4 => array(
			'name' => 'modicon',
			'type' => $vfystr::STR_FILENAME,
			'noblank' => true,
			'max' => 50,
			'min' => 1,
		),
		5 => array(
			'name' => 'modact',
			'type' => $vfystr::STR_NONE,
			'noblank' => false,
			'max' => 0,
			'min' => 1,
		),
		6 => array(
			'name' => 'modalluser',
			'type' => $vfystr::STR_NONE,
			'noblank' => false,
			'max' => 0,
			'min' => 1,
		),
		7 => array(
			'name' => 'modvend',
			'type' => $vfystr::STR_NONE,
			'noblank' => false,
			'max' => 0,
			'min' => 1,
		),
		8 => array(
			'name' => 'modsys',
			'type' => $vfystr::STR_NONE,
			'noblank' => false,
			'max' => 0,
			'min' => 1,
		),
	);
	$fieldcheck = json_encode($data);
	return $fieldcheck;
}


?>