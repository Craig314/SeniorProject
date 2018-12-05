<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application Module Grade Weight Group Editor

The css filename must match the module name, so if the module filename is
abc123.php, then the associated stylesheet must be named abc123.css.  The
same condition applies to JavaScript files as well.

The following variables are optional and only need to be defined if
the features/abilities they represent are being used:

	$htmlInjectFile
		Specifies a HTML file to use as the template instead of the
		default page.

Note on filenames:  Filename conflicts between files in the modules
directory and the application directory are resolved by the files in
the module directory taking precidence.

*/



// These variables must be set for every module.

// The executable file for the module.  Filename and extension only,
// no path component.
$moduleFilename = 'gradeweight.php';		// XXX Set This

// The name of the module.  It shows in the title bar of the web
// browser and other places.
$moduleTitle = 'Grade Weight';		// XXX Set This

// $moduleId must be a unique positive integer. Module IDs < 1000 are
// reserved for system use.  Therefore application module IDs will
// start at 1000.
$moduleId = 1502;		// XXX Set This

// The capitalized short display name of the module.  This shows up
// on buttons, and some error messages.
$moduleDisplayUpper = 'Grade Weights';		// XXX Set This

// The lowercase short display name of the module.  This shows up in
// various messages.
$moduleDisplayLower = 'grade weights';		// XXX Set This

// Set to true if this is a system module.
$moduleSystem = false;

// Flags in the permissions bitmap of what permissions the user
// has in this module.  Currently not implemented.
$modulePermissions = array();



// These are the data editing modes.
const MODE_VIEW		= 0;
const MODE_UPDATE	= 1;
const MODE_INSERT	= 2;
const MODE_DELETE	= 3;

// Field check generation data formats.
const FIELDCHK_JSON		= 0;
const FIELDCHK_ARRAY	= 1;

// This setting indicates that a file will be used instead of the
// default template.  Set to the name of the file to be used.
//$inject_html_file = '../dat/somefile.html';
$htmlInjectFile = false;

// Order matters here.  The modhead library needs to be loaded last.
// If additional libraries are needed, then load them before.
// Freeform execute stops at modhead.php
const BASEDIR = '../libs/';
const BASEAPP = '../applibs/';
require_once BASEDIR . 'dbaseuser.php';
require_once BASEAPP . 'panels.php';
require_once BASEAPP . 'loadmodule.php';
require_once BASEAPP . 'dbaseapp.php';
require_once BASEAPP . 'dbutils.php';
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
			'/js/module/portal.js',
		);

		// cssfiles is an associtive array which contains additional
		// cascading style sheets that should be included in the head
		// section of the HTML page.
		$cssFiles = array(
			'/css/tooltip-linebreak.css',
			'/css/tooltip-left.css',
			'/css/tooltip-mono.css',
			'/css/portal.css',
		);

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
			'type2',
		);

		//html::loadTemplatePage($moduleTitle, $htmlUrl, $moduleFilename,
		//  $left, $right, $funcBar, $jsFiles, $cssFiles, $htmlFlags,
		//	$funcbar2, $funcbar3);
		html::loadTemplatePage($moduleTitle, $baseUrl, $moduleFilename,
			$left, '', $funcBar, $jsFiles, $cssFiles, $htmlFlags);
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
	global $panels;
	global $ajax;
	global $vendor;
	global $admin;

	if ($vendor || $admin)
	{
		$list = loadAdditionalAdmin();
	}
	else
	{
		$list = loadAdditionalUser();
	}

	// Generate rest of page.
	$data = array(
		array(
			'type' => html::TYPE_HEADING,
			'message1' => 'Grade Weight Group Editor',

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

	// Get panel content
	$navContent = $panels->getLinks();
	$statusContent = $panels->getStatus();
	$mainContent = html::pageAutoGenerate($data);

	// Queue content in ajax transmit buffer.
	$ajax->loadQueueCommand(ajaxClass::CMD_WMAINPANEL, $mainContent);
	$ajax->loadQueueCommand(ajaxClass::CMD_WNAVPANEL, $navContent);
	$ajax->loadQueueCommand(ajaxClass::CMD_WSTATPANEL, $statusContent);

	// Render
	$ajax->sendQueue();

	exit(0);
}

// This generates a listing that a normal user sees.
function loadAdditionalUser()
{
	global $herr;
	global $dbapp;
	global $CONFIGVAR;

	// Get data from database.
	$rxa = $dbapp->queryWeightgroup($CONFIGVAR['default_weightgroup']['value']);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Default grade weight group missing.');
	}
	else
	{
		$rxc = array($rxa);
	}
	$rxb = $dbapp->queryWeightgroupInstructAll($_SESSION['userId']);
	if ($rxb == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
	}
	else
	{
		$rxc = array_merge($rxc, $rxb);
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
			'Group Name',
		),
		'tdata' => array(),
		'tooltip' => array(),
	);
	foreach ($rxc as $kx => $vx)
	{
		$tdata = array(
			// These are the values that show up under the columns above.
			// The *FIRST* value is the value that is sent when a row
			// is selected.  AKA Key Field.
			$vx['group'],
			$vx['name'],
		);
		array_push($list['tdata'], $tdata);
		array_push($list['tooltip'], $vx['description']);
	}

	// Return prepared list
	return $list;
}

// This generates a listing that an admin or vendor sees.
function loadAdditionalAdmin()
{
	global $herr;
	global $dbapp;
	global $dbuser;
	global $CONFIGVAR;

	// Get data from database.
	$rxa = $dbapp->queryWeightgroupAll();
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Default grade weight group missing.');
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
			'ID',
			'Instructor',
			'Name',
		),
		'tdata' => array(),
		'tooltip' => array(),
	);
	foreach ($rxa as $kx => $vx)
	{
		$rxb = $dbuser->queryContact($vx['instructor']);
		if ($rxb == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage());
			else
				handleError('Database Error: Missing user contact record.<br>' .
					'USERID = ' . $vx['instructor']);
		}
		$tdata = array(
			// These are the values that show up under the columns above.
			// The *FIRST* value is the value that is sent when a row
			// is selected.  AKA Key Field.
			$vx['group'],
			$vx['group'],
			$rxb['name'],
			$vx['name'],
		);
		array_push($list['tdata'], $tdata);
		array_push($list['tooltip'], $vx['description']);
	}

	// Return prepared list
	return $list;
}


// Called when the initial command processor doesn't have the
// command number. This call comes from modhead.php.
function commandProcessor($commandId)
{
	global $ajax;
	global $moduleLoad;

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
		case 5:	// Submit Update
			updateRecordAction();
			break;
		case 6:	// Submit Insert
			insertRecordAction();
			break;
		case 7:	// Submit Delete
			deleteRecordAction();
			break;
		case 90:		// Load Module
			$moduleLoad->loadModule();
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
	global $dbapp;

	$key = getPostValue('select_item', 'hidden');
	if ($key == NULL)
		handleError('You must select a ' . $moduleDisplayLower . ' from the list view.');
	// The below line requires customization for database loading.	
	$rxa = $dbapp->queryWeightgroup($key);		// XXX Set This
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Unable to retrieve required '
				. $moduleDisplayLower . ' data.');
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
// XXX: Requires customization.
function updateRecordAction()
{
	global $ajax;
	global $herr;
	global $vfystr;
	global $CONFIGVAR;
	global $moduleDisplayUpper;
	global $moduleDisplayLower;
	global $dbapp;
	global $vendor;
	global $admin;

	// Get field data.
	$fieldData = generateFieldCheck(FIELDCHK_ARRAY);

	// Get data
	$key = getPostValue('hidden');
	$name = getPostValue('wname');
	$desc = getPostValue('wdesc');
	$weight = getPostValue('weight');

	// Check key data.
	if ($key == NULL)
		handleError('Missing ' . $moduleDisplayLower . ' selection data.');
	if (!is_numeric($key))
		handleError('Malformed key sequence.');

	// Special: The vendor or an admin can update a weight group
	// on behalf of an instructor.
	if ($vendor || $admin)
	{
		$inst = getPostValue('instructor');
		$vfystr->fieldchk($fieldData, 3, $inst);
	}

	// Check field data.
	$vfystr->fieldchk($fieldData, 0, $name);
	$vfystr->fieldchk($fieldData, 1, $desc);
	$vfystr->fieldchk($fieldData, 2, $weight);

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
	
	// We are good, update the record
	$rxa = $dbapp->queryWeightgroup($key);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Missing weight group record.');
	}

	// Security Checks
	// The vendor and admin can update any record, so the checks are bypassed.
	if (!$vendor && !$admin)
	{
		// All other users.
		if ($key == $CONFIGVAR['default_weightgroup']['value'])
		{
			handleError('Security Violation: You are not allowed to edit the ' .
				'default weight group.');
		}
		if ($_SESSION['userId'] != $rxa['instructor'])
		{
			handleError('Security Violation: You do not own the weight group that' .
				'<br>you are attempting to update.<br>Permission Denied.');
		}
	}

	if (!$vendor && !$admin)
	{
		$inst = $rxa['instructor'];
	}
	$result = $dbapp->updateWeightgroup($key, $inst, $weight, $desc, $name);
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
// XXX: Requires customization.
function insertRecordAction()
{
	global $ajax;
	global $herr;
	global $vfystr;
	global $CONFIGVAR;
	global $moduleDisplayUpper;
	global $moduleDisplayLower;
	global $dbapp;
	global $vendor;
	global $admin;

	// Get field data.
	$fieldData = generateFieldCheck(FIELDCHK_ARRAY);

	// Get data
	$id = 0;	// Field is auto-increment so we set to 0 for insert.
	$name = getPostValue('wname');
	$desc = getPostValue('wdesc');
	$weight = getPostValue('weight');

	// Special: The vendor or an admin can insert a weight group
	// on behalf of an instructor.
	if ($vendor || $admin)
	{
		$inst = getPostValue('instructor');
		$vfystr->fieldchk($fieldData, 3, $inst);
	}
	else
	{
		$inst = $_SESSION['userId'];
	}

	// Check field data.
	$vfystr->fieldchk($fieldData, 0, $name);
	$vfystr->fieldchk($fieldData, 1, $desc);
	$vfystr->fieldchk($fieldData, 2, $weight);

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
	
	// We are good, insert the record
	$result = $dbapp->insertWeightgroup($inst, $weight, $desc, $name);
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
// XXX: Requires customization.
function deleteRecordAction()
{
	global $ajax;
	global $herr;
	global $vfystr;
	global $CONFIGVAR;
	global $moduleDisplayUpper;
	global $moduleDisplayLower;
	global $dbapp;
	global $vendor;
	global $admin;

	// Gather data...
	$key = getPostValue('hidden');

	// ...and check it.
	if ($key == NULL)
		handleError('Missing ' . $moduleDisplayLower . ' module selection data.');
	if (!is_numeric($key))
		handleError('Malformed key sequence.');
	
	// Now remove the record from the database.
	$rxa = $dbapp->queryWeightgroup($key);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Missing weight group record.');
	}

	// Security Checks
	// The vendor can delete any record.  The admin can delete any record
	// except the default. So we need to check that.
	if ($vendor || $admin)
	{
		if ($admin)
		{
			if ($key == $CONFIGVAR['default_weightgroup']['value'])
			{
				handleError('Security Violation: You are not allowed to delete the ' .
					'default weight group.<br>Permission Denied.');
			}
		}
	}
	else
	{
		// All other users.
		if ($key == $CONFIGVAR['default_weightgroup']['value'])
		{
			handleError('Security Violation: You are not allowed to delete the ' .
				'default weight group.<br>Permission Denied.');
		}
		if ($_SESSION['userId'] != $rxa['instructor'])
		{
			handleError('Security Violation: You do not own the weight group that' .
				'<br>you are attempting to delete.<br>Permission Denied.');
		}
	}
	$inst = $rxa['instructor'];

	$result = $dbapp->deleteWeightgroup($key, $inst);
	if ($result == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Unable to delete ' . $moduleDisplayLower .
				' data. Key = ' . $key);
	}
	sendResponse($moduleDisplayUpper . ' delete completed: key = ' . $key);
	exit(0);
}

// Generate generic form page.
// XXX Requires customization
function formPage($mode, $rxa)
{
	global $CONFIGVAR;
	global $moduleDisplayUpper;
	global $moduleDisplayLower;
	global $ajax;
	global $vendor;
	global $admin;

	// Set user warning for editing default gradescale.
	if ($rxa['group'] == $CONFIGVAR['default_weightgroup']['value'])
	{
		if ($vendor || $admin)
		{
			switch($mode)
			{
				case MODE_UPDATE;
					$warning = 'You are updating the default weight group.<br>' .
						'All courses and assignments using it will be affected.';
					break;
				case MODE_DELETE;
					if ($vendor)
					{
						$warning = 'You are deleting the default weight group.<br>' .
							'All courses and assignments using it will be affected.';
					}
					else
					{
						$warning = 'You are not allowed to delete the default weight group.';
					}
					break;
				default:
					$warning = '';
					break;
			}
		}
		else
		{
			switch($mode)
			{
				case MODE_UPDATE;
					$warning = 'You are not allowed to change the default weight group.';
					break;
				case MODE_DELETE;
					$warning = 'You are not allowed to delete the default weight group.';
					break;
				default:
					$warning = '';
					break;
			}
		}
	}
	else
	{
		switch($mode)
		{
			case MODE_UPDATE;
				$warning = 'You are updating a weight group.<br>' .
					'All courses and assignments using it will be affected.';
				break;
			case MODE_DELETE;
				$warning = 'You are not allowed to delete a weight group<br>' .
					'if courses and/or assignments are still using it.';
				break;
			default:
				$warning = '';
				break;
		}
	}

	// Determine the editing mode.
	switch($mode)
	{
		case MODE_VIEW:			// View
			$msg1 = 'Viewing ' . $moduleDisplayUpper . ' Data For';
			$msg2 = $rxa['name'];
			$warn = $warning;
			$btnset = html::BTNTYP_VIEW;
			$action = '';
			$hideValue = $rxa['group'];
			$disable = true;
			$default = true;
			$key = true;
			break;
		case MODE_UPDATE:		// Update
			$msg1 = 'Updating ' . $moduleDisplayUpper . ' Data For';
			$msg2 = $rxa['name'];
			$warn = $warning;
			$btnset = html::BTNTYP_UPDATE;
			$action = 'submitUpdate()';
			$hideValue = $rxa['group'];
			$disable = false;
			$default = true;
			$key = true;
			break;
		case MODE_INSERT:		// Insert
			$msg1 = 'Inserting ' . $moduleDisplayUpper . ' Data';
			$msg2 = $rxa['name'];
			$warn = $warning;
			$btnset = html::BTNTYP_INSERT;
			$action = 'submitInsert()';
			$disable = false;
			$default = true;
			$key = false;
			break;
		case MODE_DELETE:		// Delete
			$msg1 = 'Deleting ' . $moduleDisplayUpper . ' Data For';
			$msg2 = $rxa['name'];
			$warn = $warning;
			$btnset = html::BTNTYP_DELETE;
			$action = 'submitDelete()';
			$hideValue = $rxa['group'];
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
		$rxa = array(
			'group' => 'Automatic Assignment',
			'instructor' => ($vendor || $admin) ? '' : $_SESSION['userId'],
			'name' => '',
			'description' => '',
			'weight' => 0,
		);
	}

	// Only for the vendor or an admin.
	if ($vendor || $admin)
	{
		$group = generateField(html::TYPE_TEXT, 'group', 'Group ID', 4,
			$rxa['group'], 'The internal grade weight group identification number.',
			$default, true);
		if ($mode == MODE_INSERT || $mode == MODE_UPDATE)
		{
			$rxb = getUsersByProfile($CONFIGVAR['app_profile_instruct']['value']);
			$instruct = array(
				'type' => html::TYPE_PULLDN,
				'name' => 'instructor',
				'label' => 'Instructor',
				'fsize' => 6,
				'disable' => $disable,
				'optlist' => $rxb,
				'tooltip' => 'The instructor which owns this weight group.',
			);
			if ($default) $instruct['default'] = $rxa['instructor'];
		}
		else
		{
			$iname = getUserName($rxa['instructor']);
			$instruct = generateField(html::TYPE_TEXT, 'instructor', 'Instructor', 6,
				$iname['name'], 'The instructor which owns this weight group.',
				$default, $disable);
		}
	}
	else
	{
		$group = '';
		$instruct = '';
	}

	// XXX Custom field rendering code
	$name = generateField(html::TYPE_TEXT, 'wname', 'Group Name', 4,
		$rxa['name'], 'The name of this grade weight group.',
		$default, $disable);
	$desc = generateField(html::TYPE_AREA, 'wdesc', 'Description', 6,
		$rxa['description'], 'The description of this grade weight group.',
		$default, $disable);
	$desc['rows'] = 6;
	$name = generateField(html::TYPE_TEXT, 'weight', 'Group Weight', 2,
		$rxa['weight'], 'The name of this grade weight group.',
		$default, $disable);


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

		// XXX Enter custom field data here.
		$group,
		$instruct,
		$name,
		$desc,
		$weight,

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
function generateFieldCheck($returnType = 0)
{
	global $CONFIGVAR;
	global $vfystr;

	$data = array(
		0 => array(
			'dispname' => 'Weight Name',
			'name' => 'wname',
			'type' => $vfystr::STR_NAME,
			'noblank' => true,
			'max' => 32,
			'min' => 1,
		),
		1 => array(
			'dispname' => 'Description',
			'name' => 'wdesc',
			'type' => $vfystr::STR_ASCII,
			'noblank' => false,
			'max' => 512,
			'min' => 0,
		),
		2 => array(
			'dispname' => 'Weight Value',
			'name' => 'weight',
			'type' => $vfystr::STR_PINTEGER,
			'noblank' => true,
			'max' => 100,
			'min' => 0,
		),
		3 => array(
			'dispname' => 'Instructor',
			'name' => 'instructor',
			'type' => $vfystr::STR_PINTEGER,
			'noblank' => true,
			'max' => 2147483647,
			'min' => 0,
			'optional' => true,
		),

	);
	switch ($returnType)
	{
		case FIELDCHK_JSON:
			$fieldcheck = json_encode($data);
			break;
		case FIELDCHK_ARRAY:
			$fieldcheck = $data;
			break;
		default:
			handleError('Internal Programming Error: CODE XY039223<br>' .
				'Contact your administrator.');
			break;
	}
	return $fieldcheck;
}


?>