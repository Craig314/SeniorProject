<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application Configuration Editor

This edits configuration data for the application.

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
$moduleFilename = 'configedit.php';

// The name of the module.  It shows in the title bar of the web
// browser and other places.
$moduleTitle = 'Configuration Editor';

// $moduleId must be a unique positive integer. Module IDs < 1000 are
// reserved for system use.  Therefore application module IDs will
// start at 1000.
$moduleId = 11;

// The capitalized short display name of the module.  This shows up
// on buttons, and some error messages.
$moduleDisplayUpper = 'Configuration';

// The lowercase short display name of the module.  This shows up in
// various messages.
$moduleDisplayLower = 'configuration';

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
			'/js/module/configedit.js',
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
	global $vendor;

	// Get data from database.
	$rxa = $dbconf->queryConfigAll();
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('There are no ' . $moduleDisplayLower . 's in the database to edit.');
	}

	// Generate Selection Table.
	$list = array(
		'type' => html::TYPE_RADTABLE,
		'name' => 'select_item',
		'clickset' => true,
		'condense' => true,
		'hover' => true,
		'titles' => array(
			'Name',
			'ID',
			'Type',
			'Value',
		),
		'tdata' => array(),
		'tooltip' => array(),
	);
	foreach ($rxa as $kx => $vx)
	{
		if (!$vendor)
		{
			// The administrator does not have access to all settings.
			if ($vx['admin'] == 0) continue;
		}
		$tdata = array(
			$vx['setting'],
			$vx['dispname'],
			$vx['setting'],
			convertType($vx['type']),
			convertLongString($vx['type'], $vx['value']),
		);
		array_push($list['tdata'], $tdata);
		array_push($list['tooltip'], $vx['description']);
	}

	// Generate rest of page.
	$data = array(
		array(
			'type' => html::TYPE_HEADING,
			'message1' => 'Configuration Editor',
			'warning' => 'Changing values here can render the<br>application unusable.',
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
		case 5:	// Submit Update
			updateRecordAction();
			break;
		case 6:	// Submit Insert
			insertRecordAction();
			break;
		case 7:	// Submit Delete
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
	global $herr;
	global $moduleDisplayLower;
	global $dbconf;

	$key = getPostValue('select_item', 'hidden');
	if ($key == NULL)
		handleError('You must select a ' . $moduleDisplayLower . ' from the list view.');
	$rxa = $dbconf->queryConfig($key);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
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
	global $CONFIGVAR;
	global $moduleDisplayUpper;
	global $moduleDisplayLower;
	global $dbconf;

	// Get field data.
	$fieldData = generateFieldCheck(FIELDCHK_ARRAY);

	// Get data
	$key = getPostValue('hidden');
	$id = getPostValue('setting');

	// Check key data.
	if ($key == NULL)
		handleError('Missing ' . $moduleDisplayLower . ' selection data.');
	if ($id == NULL)
		handleError('Missing ' . $moduleDisplayLower . ' key data.');
	if (!is_numeric($key))
		handleError('Malformed key sequence.');
	if (!is_numeric($id))
		handleError('Malformed key sequence.');
	if ($key != $id)
		handleError('Database key mismatch.');

	// Get data value and check it.
	$datatype = getPostValue('datatype');
	switch ($datatype)
	{
		case DBTYPE_STRING:
			$value = getPostValue('datavalue3');
			$vfystr->fieldchk($fieldData, 8, $value);
			break;
		case DBTYPE_INTEGER:
			$value = getPostValue('datavalue3');
			$vfystr->fieldchk($fieldData, 8, $value);
			break;
		case DBTYPE_BOOLEAN:
			$value = getPostValue('datavalue2');
			if (!empty($value)) $value = 1; else $value = 0;
			break;
		case DBTYPE_LONGSTR:
			$value = getPostValue('datavalue1');
			$vfystr->fieldchk($fieldData, 6, $value);
			break;
		case DBTYPE_TIMEDISP:
			$value = getPostValue('datavalue3');
			$vfystr->fieldchk($fieldData, 8, $value);
			break;
		default:
			handleError('Invalid datatype detected.');
			break;
	}

	// Get the other values.
	$intname = getPostValue('intname');
	$dispname = getPostValue('dispname');
	$description = getPostValue('description');
	$accadmin = getPostValue('accadmin');

	// Check the other values.
	$vfystr->fieldchk($fieldData, 2, $intname);
	$vfystr->fieldchk($fieldData, 3, $dispname);
	$vfystr->fieldchk($fieldData, 4, $description);
	if (!empty($accadmin)) $accadmin = 1; else $accadmin = 0;

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
	
	// We are good, update the record
	$result = $dbconf->updateConfigAll($key, $datatype, $intname, $dispname,
		$value, $description, $accadmin);
	if ($result == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database: Record update failed. Key = ' . $key);
	}
	sendResponse($moduleDisplayUpper . ' update completed: key = ' . $key);

	// Reload the shared memory for configuration.
	processSharedMemoryReload();

	exit(0);
}

// Inserts the record into the database.
function insertRecordAction()
{
	global $ajax;
	global $herr;
	global $vfystr;
	global $CONFIGVAR;
	global $moduleDisplayUpper;
	global $moduleDisplayLower;
	global $dbconf;

	// Get field data.
	$fieldData = generateFieldCheck(FIELDCHK_ARRAY);

	// Get data
	$id = getPostValue('setting');
	$vfystr->strchk($id, 'Setting Number', 'setting', verifyString::STR_INTEGER, true);

	// Get data value and check it.
	$datatype = getPostValue('datatype');
	switch ($datatype)
	{
		case DBTYPE_STRING:
			$value = getPostValue('datavalue3');
			$vfystr->fieldchk($fieldData, 8, $value);
			break;
		case DBTYPE_INTEGER:
			$value = getPostValue('datavalue3');
			$vfystr->fieldchk($fieldData, 8, $value);
			break;
		case DBTYPE_BOOLEAN:
			$value = getPostValue('datavalue2');
			if (!empty($value)) $value = 1; else $value = 0;
			break;
		case DBTYPE_LONGSTR:
			$value = getPostValue('datavalue1');
			$vfystr->fieldchk($fieldData, 6, $value);
			break;
		case DBTYPE_TIMEDISP:
			$value = getPostValue('datavalue3');
			$vfystr->fieldchk($fieldData, 8, $value);
			break;
		default:
			handleError('Invalid datatype detected.');
			break;
	}

	// Get the other values.
	$intname = getPostValue('intname');
	$dispname = getPostValue('dispname');
	$description = getPostValue('description');
	$accadmin = getPostValue('accadmin');

	// Check the other values.
	$vfystr->fieldchk($fieldData, 2, $intname);
	$vfystr->fieldchk($fieldData, 3, $dispname);
	$vfystr->fieldchk($fieldData, 4, $description);
	if (!empty($accadmin)) $accadmin = 1; else $accadmin = 0;

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

	// We are good, insert the record
	$result = $dbconf->insertConfig($id, $datatype, $intname, $dispname,
		$value, $description, $accadmin);
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
	global $ajax;
	global $herr;
	global $vfystr;
	global $CONFIGVAR;
	global $moduleDisplayUpper;
	global $moduleDisplayLower;
	global $dbconf;

	// Gather data...
	$key = getPostValue('hidden');
	$id = getPostValue('setting');

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
	
	// Failsafe Check: If the id is < 1000 then it's a system
	// setting and we exit with error.
	if ($id < 1000)
		handleError('System settings (Setting Number < 1000) cannot be ' .
			'deleted.<br>It can only be deleted manually from the database.');
	
	// Now remove the record from the database.
	$result = $dbconf->deleteConfig($key);
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
			$msg2 = $rxa['dispname'];
			$warn = '';
			$btnset = html::BTNTYP_VIEW;
			$action = '';
			$hideValue = $rxa['setting'];
			$disable = true;
			$default = true;
			$key = true;
			break;
		case MODE_UPDATE:		// Update
			$msg1 = 'Updating ' . $moduleDisplayUpper . ' Data For';
			$msg2 = $rxa['dispname'];
			$warn = 'Changing a setting can make the application unusable.';
			$btnset = html::BTNTYP_UPDATE;
			$action = 'submitUpdate()';
			$hideValue = $rxa['setting'];
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
			$msg2 = $rxa['dispname'];
			$warn = 'Deleting a setting may cause the application to crash.';
			$btnset = html::BTNTYP_DELETE;
			$action = 'submitDelete()';
			$hideValue = $rxa['setting'];
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
			'setting' => '',
			'name' => '',
			'dispname' => '',
			'description' => '',
			'type' => '',
			'value' => '',
			'admin' => '',
		);
	}

	// Custom field rendering code
	$setting = generateField(html::TYPE_TEXT, 'setting', 'Setting Number', 2,
		$rxa['setting'], 'The setting ID index.', $default, $key);
	$intname = generateField(html::TYPE_TEXT, 'intname', 'Internal Name', 6,
		$rxa['name'], 'The internal name of the setting.', $default, $disable);
	$dispname = generateField(html::TYPE_TEXT, 'dispname', 'Display Name', 6,
		$rxa['dispname'], 'The display name of the setting.', $default, $disable);
	$description = generateField(html::TYPE_AREA, 'description', 'Description', 6,
		$rxa['description'], 'The long description of the setting.', $default, $disable);
	$description['rows'] = 5;
	if ($mode == MODE_UPDATE || $mode == MODE_INSERT)
	{
		$datatype = array(
			'type' => html::TYPE_PULLDN,
			'label' => 'Value Type',
			'default' => $rxa['type'],
			'name' => 'datatype',
			'fsize' => 4,
			'optlist' => array(
				'String' => DBTYPE_STRING,
				'Integer' => DBTYPE_INTEGER,
				'Boolean' => DBTYPE_BOOLEAN,
				'Long String' => DBTYPE_LONGSTR,
				'Time Displacement' => DBTYPE_TIMEDISP,
			),
			'tooltip' => 'Sets the data type of the value.',
			'disable' => $disable,
			'event' => 'onchange',
			'action' => 'setHidden()',
		);
	}
	else
	{
		$datatype = generateField(html::TYPE_TEXT, 'datatype', 'Value Type', 4,
			convertType($rxa['type']), 'The value data type.', $default, $disable);
	}
	switch ($rxa['type'])
	{
		case DBTYPE_LONGSTR:
			$hideLStr = false;
			$hideBool = true;
			$hideOther = true;
			break;
		case DBTYPE_BOOLEAN:
			$hideLStr = true;
			$hideBool = false;
			$hideOther = true;
			break;
		default:
			$hideLStr = true;
			$hideBool = true;
			$hideOther = false;
			break;
	}
	$value1 = generateField(html::TYPE_AREA, 'datavalue1', 'Value', 6,
		$rxa['value'], 'The setting\'s value.', $default, $disable);
	$value1['rows'] = 5;
	$value2 = generateField(html::TYPE_CHECK, 'datavalue2', 'Value', 1,
		$rxa['value'], 'The setting\'s value.', $default, $disable);
	$value3 = generateField(html::TYPE_TEXT, 'datavalue3', 'Value', 6,
		$rxa['value'], 'The setting\'s value.', $default, $disable);
	$accadmin = generateField(html::TYPE_CHECK, 'accadmin', 'Admin Access', 1,
		$rxa['admin'], 'When checked, the Administrator has access to this setting.',
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

		// Enter custom field data here.
		array(
			'type' => html::TYPE_FSETOPEN,
			'name' => 'Key',
		),
		$setting,
		array(
			'type' => html::TYPE_FSETCLOSE,
		),
		array(
			'type' => html::TYPE_FSETOPEN,
			'name' => 'Description',
		),
		$dispname,
		$intname,
		$description,
		array(
			'type' => html::TYPE_FSETCLOSE,
		),
		array(
			'type' => html::TYPE_FSETOPEN,
			'name' => 'Data',
		),
		$datatype,
		array(
			'type' => html::TYPE_HIDEOPEN,
			'name' => 'dataLongString',
			'hidden' => $hideLStr,
		),
		$value1,
		array(
			'type' => html::TYPE_HIDECLOSE,
		),
		array(
			'type' => html::TYPE_HIDEOPEN,
			'name' => 'dataBoolean',
			'hidden' => $hideBool,
		),
		$value2,
		array(
			'type' => html::TYPE_HIDECLOSE,
		),
		array(
			'type' => html::TYPE_HIDEOPEN,
			'name' => 'dataOther',
			'hidden' => $hideOther,
		),
		$value3,
		array(
			'type' => html::TYPE_HIDECLOSE,
		),
		array(
			'type' => html::TYPE_FSETCLOSE,
		),
		array(
			'type' => html::TYPE_FSETOPEN,
			'name' => 'Access Security',
		),
		$accadmin,
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
			'name' => 'setting',
			'dispname' => 'Setting Number',
			'type' => $vfystr::STR_PINTEGER,
			'noblank' => true,
			'max' => 0,
			'min' => 1,
		),
		1 => array(
			'name' => 'datatype',
			'dispname' => 'Data Type',
			'type' => $vfystr::STR_INTEGER,
			'noblank' => true,
			'max' => 25,
			'min' => 0,
		),
		2 => array(
			'name' => 'intname',
			'dispname' => 'Internal Name',
			'type' => $vfystr::STR_ALPHASPEC,
			'noblank' => true,
			'max' => 32,
			'min' => 1,
		),
		3 => array(
			'name' => 'dispname',
			'dispname' => 'Display Name',
			'type' => $vfystr::STR_ALPHANUM,
			'noblank' => true,
			'max' => 64,
			'min' => 1,
		),
		4 => array(
			'name' => 'description',
			'dispname' => 'Description',
			'type' => $vfystr::STR_ASCII,
			'noblank' => true,
			'max' => 256,
			'min' => 1,
		),
		5 => array(
			'name' => 'accadmin',
			'dispname' => 'Admin Access',
			'type' => $vfystr::STR_NONE,
			'noblank' => false,
			'max' => 0,
			'min' => 1,
		),
		6 => array(
			'name' => 'datavalue1',
			'dispname' => 'Value',
			'type' => $vfystr::STR_CUSTOM,
			'ctype' => $vfystr::STR_NONE,
			'noblank' => false,
			'max' => 0,
			'min' => 1,
		),
		7 => array(
			'name' => 'datavalue2',
			'dispname' => 'Value',
			'type' => $vfystr::STR_NONE,
			'noblank' => false,
			'max' => 0,
			'min' => 1,
		),
		8 => array(
			'name' => 'datavalue3',
			'dispname' => 'Value',
			'type' => $vfystr::STR_CUSTOM,
			'ctype' => $vfystr::STR_NONE,
			'noblank' => false,
			'max' => 0,
			'min' => 1,
		),
	);
	return $data;
}

?>