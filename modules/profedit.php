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
$moduleFilename = 'profedit.php';

// The name of the module.  It shows in the title bar of the web
// browser and other places.
$moduleTitle = 'Profile Editor';

// $moduleId must be a unique positive integer. Module IDs < 1000 are
// reserved for system use.  Therefore application module IDs will
// start at 1000.
$moduleId = 21;

// The capitalized short display name of the module.  This shows up
// on buttons, and some error messages.
$moduleDisplayUpper = 'Profile';

// The lowercase short display name of the module.  This shows up in
// various messages.
$moduleDisplayLower = 'profile';

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
const BASEDIR = '../libs/';
require_once BASEDIR . 'flag.php';
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

	// Dump the profile database and process it.
	$rxm = $dbconf->queryProfileAll();
	if ($rxm == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
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
			'Portal',
		),
		'tdata' => array(),
		'tooltip' => array(),
	);
	foreach($rxm as $kx => $vx)
	{
		$tdata = array(
			$vx['profileid'],
			$vx['name'],
			$vx['profileid'],
			convPortalType($vx['portal']),
		);
		array_push($list['tdata'], $tdata);
		array_push($list['tooltip'], $vx['description']);
	}

	// Generate rest of page.
	$data = array(
		array(
			'type' => html::TYPE_HEADING,
			'message1' => $moduleTitle,
			'warning' => '',
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
	global $dbconf;
	global $herr;
	global $moduleDisplayLower;

	$key = getPostValue('select_item', 'hidden');
	if ($key == NULL)
		handleError('You must select a ' . $moduleDisplayLower . ' from the list view.');
	// The below line requires customization for database loading.	
	$rxa = $dbconf->queryProfile($key);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Unable to retrieve required ' . $moduleDisplayLower . ' data.');
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
	global $CONFIGVAR;
	global $vendor;

	// Get field data.
	$fieldData = generateFieldCheck(FIELDCHK_ARRAY);

	// Get data
	$key = getPostValue('hidden');
	$id = getPostValue('profid');
	$name = getPostValue('profname');
	$desc = getPostValue('profdesc');
	$port = getPostValue('profport');

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

	// Check field data.
	$vfystr->fieldchk($fieldData, 0, $id);
	$vfystr->fieldchk($fieldData, 1, $name);
	$vfystr->fieldchk($fieldData, 3, $desc);
	$vfystr->fieldchk($fieldData, 2, $port);

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
	
	// Check if we have a system profile and the permissions for it.
	if (!$vendor)
	{
		if ($key == $CONFIGVAR['profile_id_none']['value'] ||
		$key == $CONFIGVAR['profile_id_vendor']['value'] ||
		$key == $CONFIGVAR['profile_id_admin']['value'])
		{
			handleError('You do not have permission to update a system profile.');
		}
	}
	
	// Set default flag values based on profile type.
	if ($key == $CONFIGVAR['profile_id_vendor']['value'] ||
		$key == $CONFIGVAR['profile_id_admin']['value'])
	{
		// Admin and Vendor profiles have all 1's set.
		$bmfs = hex2bin('FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF');
		$bmfa = hex2bin('FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF');
	}
	else
	{
		// All other profiles require checking flags.
		// Start with all flags off.
		$bmfs = hex2bin('00000000000000000000000000000000');
		$bmfa = hex2bin('00000000000000000000000000000000');

		// Scan the $_POST variable for checked flags.
		for ($i = 0; $i < FLAG_COUNT_SYSTEM; $i++)
		{
			$sysflag = getPostValue(html::CHECKFLAG_NAME . 'sys_' . $i);
			$appflag = getPostValue(html::CHECKFLAG_NAME . 'app_' . $i);
			if (empty($sysflag)) flag::setFlag($i, $bmfs, false);
				else flag::setFlag($i, $bmfs, true);
			if (empty($appflag)) flag::setFlag($i, $bmfa, false);
				else flag::setFlag($i, $bmfa, true);
		}
	}

	// Retrieve the module list so we know what modules are available.
	$rxm = $dbconf->queryModuleAll();
	if ($rxm == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage);
		else
			handleError('Database Error: Unable to retrieve module list.');
	}

	// We are good, update the record
	$result = $dbconf->updateProfile($key, $name, $desc, $port, $bmfs, $bmfa);
	if ($result == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database: Record update failed. Key = ' . $key);
	}

	// Now we look for any module flags that are set to true.
	foreach ($rxm as $kx => $vx)
	{
		if ($vx['vendor'] != 0) continue;
		if ($vx['allusers'] != 0) continue;
		if ($vx['active'] == 0) continue;
		$maflag = getPostValue(html::CHECKFLAG_NAME . 'mdac_' . $vx['moduleid']);
		if ($maflag != NULL)
		{
			// If the entry is not in the database, then insert it.
			// Otherwise leave it.
			$result = $dbconf->queryModaccess($id, $vx['moduleid']);
			if ($result == false)
			{
				if ($herr->checkState())
					handleError($herr->errorGetMessage . '<br>PROF='
						. $id . '; MOD=' . $vx['moduleid']);
				$result = $dbconf->insertModaccess($id, $vx['moduleid']);
				if ($result == false)
				{
					if ($herr->checkState())
						handleError($herr->errorGetMessage . '<br>PROF='
							. $id . '; MOD=' . $vx['moduleid']);
					else
						handleError('Database Error: Unable to insert module ' .
							'access record.<br>' . 'PROF=' . $id . '; MOD=' .
							$vx['moduleid']);
				}
			}
		}
		else
		{
			// If the entry is in the database, then we have to remove it.
			// Otherwise do nothing.
			$result = $dbconf->queryModaccess($id, $vx['moduleid']);
			if ($result == false)
			{
				if ($herr->checkState())
					handleError($herr->errorGetMessage . '<br>PROF='
						. $id . '; MOD=' . $vx['moduleid']);
			}
			else
			{
				$result = $dbconf->deleteModaccess($id, $vx['moduleid']);
				if ($result == false)
				{
					if ($herr->checkState())
						handleError($herr->errorGetMessage . '<br>PROF='
							. $id . '; MOD=' . $vx['moduleid']);
					else
						handleError('Database Error: Unable to delete module ' .
							'access record<br>PROF=' . $id . '; MOD=' .
							$vx['moduleid']);
				}
			}
		}
	}

	// Notify the user.
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
	global $CONFIGVAR;

	// Get field data.
	$fieldData = generateFieldCheck(FIELDCHK_ARRAY);

	// Get data
	$id = getPostValue('profid');
	$name = getPostValue('profname');
	$desc = getPostValue('profdesc');
	$port = getPostValue('profport');

	// Check field data.
	$vfystr->fieldchk($fieldData, 0, $id);
	$vfystr->fieldchk($fieldData, 1, $name);
	$vfystr->fieldchk($fieldData, 3, $desc);
	$vfystr->fieldchk($fieldData, 2, $port);

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
	
	// Set default flag values based on profile type.
	if ($id == $CONFIGVAR['profile_id_vendor']['value'] ||
		$id == $CONFIGVAR['profile_id_admin']['value'])
	{
		// Admin and Vendor profiles have all 1's set.
		$bmfs = hex2bin('FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF');
		$bmfa = hex2bin('FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF');
	}
	else
	{
		// All other profiles require checking flags.
		// Start with all flags off.
		$bmfs = hex2bin('00000000000000000000000000000000');
		$bmfa = hex2bin('00000000000000000000000000000000');

		// Scan the $_POST variable for checked flags.
		for ($i = 0; $i < FLAG_COUNT_SYSTEM; $i++)
		{
			$sysflag = getPostValue(html::CHECKFLAG_NAME . 'sys_' . $i);
			$appflag = getPostValue(html::CHECKFLAG_NAME . 'app_' . $i);
			if (empty($sysflag)) flag::setFlag($i, $bmfs, false);
				else flag::setFlag($i, $bmfs, true);
			if (empty($appflag)) flag::setFlag($i, $bmfa, false);
				else flag::setFlag($i, $bmfa, true);
		}
	}

	// Retrieve the module list so we know what modules are available.
	$rxm = $dbconf->queryModuleAll();
	if ($rxm == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage);
		else
			handleError('Database Error: Unable to retrieve module list.');
	}

	// We are good, insert the record
	$result = $dbconf->insertProfile($id, $name, $desc, $port, $bmfs, $bmfa);
	if ($result == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database: Record insert failed. Key = ' . $id);
	}

	// Now we look for any module flags that are set to true.
	foreach ($rxm as $kx => $vx)
	{
		if ($vx['vendor'] != 0) continue;
		if ($vx['allusers'] != 0) continue;
		if ($vx['active'] == 0) continue;
		$maflag = getPostValue(html::CHECKFLAG_NAME . 'mdac_' . $vx['moduleid']);
		if ($maflag != NULL)
		{
			$result = $dbconf->insertModaccess($id, $vx['moduleid']);
			if ($result == false)
			{
				if ($herr->checkState())
					handleError($herr->errorGetMessage);
				else
					handleError('Database Error: Unable to insert module ' .
						'access record.<br>' . 'PROF=' . $id . '; MOD=' .
						$vx['moduleid']);
			}
		}
	}

	// Notify the user.
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
	global $CONFIGVAR;
	global $vendor;

	// Gather data...
	$key = getPostValue('hidden');
	$id = getPostValue('profid');

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
	
	// Check if we have a system profile and the permissions for it.
	if (!$vendor)
	{
		if ($key == $CONFIGVAR['profile_id_none']['value'] ||
		$key == $CONFIGVAR['profile_id_vendor']['value'] ||
		$key == $CONFIGVAR['profile_id_admin']['value'])
		{
			handleError('You do not have permission to delete a system profile.');
		}
	}
	
	// Now remove the module from the database.
	$result = $dbconf->deleteProfile($key);
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
	global $moduleDisplayUpper;
	global $CONFIGVAR;
	global $vendor;
	global $dbconf;
	global $herr;
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
			$hideValue = $rxa['profileid'];
			$disable = true;
			$default = true;
			$key = true;
			break;
		case MODE_UPDATE:		// Update
			$msg1 = 'Updating ' . $moduleDisplayUpper . ' Data For';
			$msg2 = $rxa['name'];
			$warn = '';
			$btnset = html::BTNTYP_UPDATE;
			$action = 'submitUpdate()';
			$hideValue = $rxa['profileid'];
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
			$warn = '';
			$btnset = html::BTNTYP_DELETE;
			$action = 'submitDelete()';
			$hideValue = $rxa['profileid'];
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
			'profileid' => '',
			'name' => '',
			'description' => '',
			'portal' => 2,
		);
	}

	// Custom field rendering code
	$profid = generateField(html::TYPE_TEXT, 'profid', 'Profile ID', 3,
		$rxa['profileid'], 'The numeric ID of the profile.', $default, $key);
	$profname = generateField(html::TYPE_TEXT, 'profname', 'Name', 6, $rxa['name'],
		'Display name of the profile.', $default, $disable);
	$profdesc = generateField(html::TYPE_AREA, 'profdesc', 'Description', 6,
		$rxa['description'], 'The description of the profile.', $default, $disable);
	$profdesc['rows'] = 5;
	$profport = array(
		'type' => html::TYPE_PULLDN,
		'label' => 'Portal Type',
		'default' => $rxa['portal'],
		'name' => 'profport',
		'fsize' => 4,
		'tooltip' => 'Type of portal that the user sees.',
		'optlist' => array(
			'Grid Portal' => 0,
			'Link Portal' => 1,
			'Land Portal' => 2,
		),
	);

	// System defined profiles cannot have their accesses changed.
	if ($rxa['profileid'] != $CONFIGVAR['profile_id_vendor']['value'] &&
		$rxa['profileid'] != $CONFIGVAR['profile_id_admin']['value'] &&
		$rxa['profileid'] != $CONFIGVAR['profile_id_none']['value'])
	{
		// This generates the system flag checkbox list
		$rxcs = $dbconf->queryFlagdescCoreAll();
		if ($rxcs == false)
		{
			$clsysfo = NULL;
			$clsysfc = NULL;
			$clsys = NULL;
		}
		else
		{
			$clsysfo = array(
				'type' => html::TYPE_FSETOPEN,
				'name' => 'System Flags',
			);
			$clsysfc = array(
				'type' => html::TYPE_FSETCLOSE,
			);
			$clsys= array(
				'type' => html::TYPE_CHECKLIST,
				'list' => array(),
				'lsize' => 4,
				'fsize' => 1,
				'default' => $default,
				'disable' => $disable,
			);
			foreach ($rxcs as $kx => $vx)
			{
				$flag = array(
					'flag' => 'sys_' . $vx['flag'],
					'label' => $vx['name'],
					'tooltip' => $vx['description'],
					'default' => flag::getFlag($vx['flag'], $rxa['bitmap_core']),
				);
				array_push($clsys['list'], $flag);
			}
		}
		
		// This generates the application flag checkbox list
		$rxca = $dbconf->queryFlagdescAppAll();
		if ($rxca == false)
		{
			$clappfo = NULL;
			$clappfc = NULL;
			$clapp = NULL;
		}
		else
		{
			$clappfo = array(
				'type' => html::TYPE_FSETOPEN,
				'name' => 'Application Flags',
			);
			$clappfc = array(
				'type' => html::TYPE_FSETCLOSE,
			);
			$clapp= array(
				'type' => html::TYPE_CHECKLIST,
				'list' => array(),
				'lsize' => 4,
				'fsize' => 1,
				'default' => $default,
				'disable' => $disable,
			);
			foreach ($rxca as $kx => $vx)
			{
				$flag = array(
					'flag' => 'app_' . $vx['flag'],
					'label' => $vx['name'],
					'tooltip' => $vx['description'],
					'default' => flag::getFlag($vx['flag'], $rxa['bitmap_app']),
				);
				array_push($clapp['list'], $flag);
			}
		}

		// This generates the module access checkbox list.
		$rxb = $dbconf->queryModuleAll();
		if ($rxb == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage);
			else
				handleError('Database Error: Unable to retrieve module list.');
		}
		if ($mode != MODE_INSERT)
		{
			$rxc = $dbconf->queryModaccessProfile($rxa['profileid']);
			if ($rxc == false)
			{
				if ($herr->checkState())
					handleError($herr->errorGetMessage);
				else $rxc = NULL;
			}
		}
		else $rxc = NULL;
		$modaccfo = array(
			'type' => html::TYPE_FSETOPEN,
			'name' => 'Module Access',
		);
		$modaccfc = array(
			'type' => html::TYPE_FSETCLOSE,
		);
		$modacc = array(
			'type' => html::TYPE_CHECKLIST,
			'list' => array(),
			'lsize' => 4,
			'fsize' => 1,
			'default' => $default,
			'disable' => $disable,
		);
		foreach ($rxb as $kx => $vx)
		{
			if ($vx['vendor'] != 0) continue;
			if ($vx['allusers'] != 0) continue;
			if ($vx['active'] == 0) continue;
			$accDefault = false;
			if (is_array($rxc))
			{
				foreach ($rxc as $ka => $va)
				{
					if ($vx['moduleid'] == $va['moduleid'])
					{
						$accDefault = true;
						break;
					}
				}
			}
			$flag = array(
				'flag' => 'mdac_' . $vx['moduleid'],
				'label' => $vx['name'],
				'tooltip' => $vx['description'],
				'default' => $accDefault,
			);
			array_push($modacc['list'], $flag);
		}
	}
	else
	{
		// The vendor and admin accounts don't get to see the check lists
		// for the system flags and modules because
		// 1. The vendor has access to everything.
		// 2. The admin has access to everything that is not vendor only.
		// 3. The system and application flags are all set to 1 for both
		//    the vendor and admin profiles.
		$clsysfo = NULL;
		$clsysfc = NULL;
		$clsys = NULL;
		$clappfo = NULL;
		$clappfc = NULL;
		$clapp = NULL;
		$modaccfo = NULL;
		$modaccfc = NULL;
		$modacc = NULL;
	}

	// System profile warnings.
	if (!$vendor)
	{
		if ($rxa['profileid'] == $CONFIGVAR['profile_id_none']['value'] ||
			$rxa['profileid'] == $CONFIGVAR['profile_id_vendor']['value'] ||
			$rxa['profileid'] == $CONFIGVAR['profile_id_admin']['value'])
		{
			switch($mode)
			{
				case MODE_UPDATE:
					$warn = 'You cannot update a system profile.';
					break;
				case MODE_DELETE:
					$warn = 'You cannot delete a system profile.';
					break;
				default:
					break;
			}
		}
	}

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
		$profid,
		$profname,
		$profdesc,
		$profport,
		$clappfo,
		$clapp,
		$clappfc,
		$clsysfo,
		$clsys,
		$clsysfc,
		$modaccfo,
		$modacc,
		$modaccfc,

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

function convPortalType($portal)
{
	switch ($portal)
	{
		case 0:
			$type = 'Grid';
			break;
		case 1:
			$type = 'Link';
			break;
		case 2:
			$type = 'Land';
			break;
		default:
			$type = $portal;
			break;
	}
	return $type;
}

// Generate the field definitions for client side error checking.
function fcData()
{
	global $CONFIGVAR;
	global $vfystr;

	$data = array(
		0 => array(
			'dispname' => 'Profile ID',
			'name' => 'profid',
			'type' => $vfystr::STR_PINTEGER,
			'noblank' => true,
			'max' => 2147483647,
			'min' => 0,
		),
		1 => array(
			'dispname' => 'Profile Name',
			'name' => 'profname',
			'type' => $vfystr::STR_ALPHA,
			'noblank' => true,
			'max' => 32,
			'min' => 1,
		),
		2 => array(
			'dispname' => 'Portal Type',
			'name' => 'profport',
			'type' => $vfystr::STR_PINTEGER,
			'noblank' => true,
			'max' => 2,
			'min' => 0,
		),
		3 => array(
			'dispname' => 'Description',
			'name' => 'profdesc',
			'type' => $vfystr::STR_ASCII,
			'noblank' => true,
			'max' => 256,
			'min' => 1,
		),
	);
	return $data;
}



?>