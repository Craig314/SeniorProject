<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application OAuth Provider Edit

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
$moduleFilename = 'oauthedit.php';

// The name of the module.  It shows in the title bar of the web
// browser and other places.
$moduleTitle = 'OAuth Provider Editor';

// $moduleId must be a unique positive integer. Module IDs < 1000 are
// reserved for system use.  Therefore application module IDs will
// start at 1000.
$moduleId = 12;

// The capitalized short display name of the module.  This shows up
// on buttons, and some error messages.
$moduleDisplayUpper = 'OAuth Provider';

// The lowercase short display name of the module.  This shows up in
// various messages.
$moduleDisplayLower = 'provider';

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
			'/js/common.js',
			'/js/oauthedit.js',
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

	// Get data from database.
	$rxa = $dbconf->queryOAuthAll();
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessages());
		else
			handleError('There are no ' . $moduleDisplayLower .
				's in the database to edit.');
	}

	// Generate Selection Table.
	$list = array(
		'type' => html::TYPE_RADTABLE,
		'name' => 'select_item',
		'titles' => array(
			// Add column titles here
			'Name',
			'ID',
			'Module',
			'Expire',
			'Client ID',
		),
		'tdata' => array(),
		'tooltip' => array(),
	);
	foreach ($rxa as $kx => $vx)
	{
		$tdata = array(
			// These are the values that show up under the columns above.
			// The *FIRST* value is the value that is sent when a row
			// is selected.  AKA Key Field.
			$vx['provider'],
			$vx['name'],
			$vx['provider'],
			$vx['module'],
			$vx['expire'],
			$vx['clientid'],
		);
		array_push($list['tdata'], $tdata);
		array_push($list['tooltip'], $vx['name']);
	}

	// Generate rest of page.
	$data = array(
		array(
			'type' => html::TYPE_HEADING,
			'message1' => 'OAuth Provider Edit',
			'message2' => '',	// Delete if not needed.
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
	html::pageAutoGenerate($data);

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
function databaseLoad()
{
	global $herr;
	global $moduleDisplayLower;
	global $dbconf;

	$key = getPostValue('select_item', 'hidden');
	if ($key == NULL)
		handleError('You must select a ' . $moduleDisplayLower .
			' from the list view.');
	// The below line requires customization for database loading.	
	$rxa = $dbconf->queryOAuth($key);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessages());
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
function updateRecordAction()
{
	global $ajax;
	global $herr;
	global $vfystr;
	global $CONFIGVAR;
	global $moduleDisplayUpper;
	global $moduleDisplayLower;
	global $dbconf;

	// Set the field list.
	$fieldlist = array(
		'provider',
		'name',
		'module',
		'expire',
		'clientid',
		'clientsecret',
		'scope',
		'authtype',
		'authurl',
		'redirect',
		'resource1',
		'resource2',
		'resource3',
		'resource4',
	);
	
	// Get data
	$key = getPostValue('hidden');
	$id = getPostValue('provider');

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

	// Get data.
	$name = getPostValue('name');
	$module = getPostValue('module');
	$expire = getPostValue('expire');
	$clientid = getPostValue('clientid');
	$clientsecret = getPostValue('clientsecret');
	$scope = getPostValue('scope');
	$authtype = getPostValue('authtype');
	$authurl = getPostValue('authurl');
	$redirect = getPostValue('redirect');
	$resource1 = getPostValue('resource1');
	$resource2 = getPostValue('resource2');
	$resource3 = getPostValue('resource3');
	$resource4 = getPostValue('resource4');

	// Check mandatory fields.
	$vfystr->strchk($name, 'Name', 'name', verifyString::STR_NAME, true, 50, 3);
	$vfystr->strchk($module, 'Module', 'module', verifyString::STR_FILENAME,
		true, 32, 3);
	$vfystr->strchk($expire, 'Expire', 'expire', verifyString::STR_PINTEGER,
		true, 1209600, 900);
	$vfystr->strchk($clientid, 'Client ID', 'clientid',
		verifyString::STR_ALPHANUM, true, 32, 3);
	$vfystr->strchk($scope, 'Scope', 'scope', verifyString::STR_ALPHANUMPUNCT, true,
		256, 3);
	$vfystr->strchk($authtype, 'Authentication Type', 'authtype',
		verifyString::STR_ALPHANUM, true, 16, 1);
	$vfystr->strchk($authurl, 'Authentication URL', 'authurl',
		verifyString::STR_URI, true, 512, 3);
	$vfystr->strchk($redirect, 'Redirect URL', 'redirect',
		verifyString::STR_URI, true, 512, 3);
	$vfystr->strchk($resource1, 'Resource URL 1', 'resource1',
		verifyString::STR_URI, true, 512, 3);

	// Check optional fields.
	$vfystr->strchk($clientsecret, 'Client Secret', 'clientsecret',
		verifyString::STR_ALPHANUM, false, 64, 3);
	$vfystr->strchk($resource2, 'Resource URL 2', 'resource2',
		verifyString::STR_URI, false, 512, 3);
	$vfystr->strchk($resource3, 'Resource URL 3', 'resource3',
		verifyString::STR_URI, false, 512, 3);
	$vfystr->strchk($resource4, 'Resource URL 4', 'resource4',
		verifyString::STR_URI, false, 512, 3);

	// Handle any errors from above.
	if ($vfystr->errstat() == true)
	{
		if ($herr->checkState() == true)
		{
			$rxe = $herr->errorGetData();
			$ajax->sendStatus($rxe, $fieldlist);
			exit(1);
		}
	}

	// Safely encode all strings to prevent XSS attacks.
	$name = safeEncodeString($name);
	
	// We are good, update the record
	$result = $dbconf->updateOAuth($key, $name, $module, $expire, $clientid,
		$clientsecret, $scope, $authtype, $authurl, $redirect, $resource1,
		$resource2, $resource3, $resource4);
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
	global $CONFIGVAR;
	global $moduleDisplayUpper;
	global $moduleDisplayLower;

	// Set the field list.
	$fieldlist = array(
		'provider',
		'name',
		'module',
		'expire',
		'clientid',
		'clientsecret',
		'scope',
		'authtype',
		'authurl',
		'redirect',
		'resource1',
		'resource2',
		'resource3',
		'resource4',
	);
	
	// Get data
	$id = getPostValue('provider');
	$name = getPostValue('name');
	$module = getPostValue('module');
	$expire = getPostValue('expire');
	$clientid = getPostValue('clientid');
	$clientsecret = getPostValue('clientsecret');
	$scope = getPostValue('scope');
	$authtype = getPostValue('authtype');
	$authurl = getPostValue('authurl');
	$redirect = getPostValue('redirect');
	$resource1 = getPostValue('resource1');
	$resource2 = getPostValue('resource2');
	$resource3 = getPostValue('resource3');
	$resource4 = getPostValue('resource4');

	// Check mandatory fields.
	$vfystr->strchk($id, 'Provider', 'provider', verifyString::STR_PINTEGER,
		true, 2147483647, 0);
	$vfystr->strchk($name, 'Name', 'name', verifyString::STR_NAME, true, 50, 3);
	$vfystr->strchk($module, 'Module', 'module', verifyString::STR_FILENAME,
		true, 32, 3);
	$vfystr->strchk($expire, 'Expire', 'expire', verifyString::STR_PINTEGER,
		true, 1209600, 900);
	$vfystr->strchk($clientid, 'Client ID', 'clientid',
		verifyString::STR_ALPHANUM, true, 32, 3);
	$vfystr->strchk($scope, 'Scope', 'scope', verifyString::STR_ALPHANUMPUNCT, true,
		256, 3);
	$vfystr->strchk($authtype, 'Authentication Type', 'authtype',
		verifyString::STR_ALPHANUM, true, 16, 1);
	$vfystr->strchk($authurl, 'Authentication URL', 'authurl',
		verifyString::STR_URI, true, 512, 3);
	$vfystr->strchk($redirect, 'Redirect URL', 'redirect',
		verifyString::STR_URI, true, 512, 3);
	$vfystr->strchk($resource1, 'Resource URL 1', 'resource1',
		verifyString::STR_URI, true, 512, 3);

	// Check optional fields.
	$vfystr->strchk($clientsecret, 'Client Secret', 'clientsecret',
		verifyString::STR_ALPHANUM, false, 64, 3);
	$vfystr->strchk($resource2, 'Resource URL 2', 'resource2',
		verifyString::STR_URI, false, 512, 3);
	$vfystr->strchk($resource3, 'Resource URL 3', 'resource3',
		verifyString::STR_URI, false, 512, 3);
	$vfystr->strchk($resource4, 'Resource URL 4', 'resource4',
		verifyString::STR_URI, false, 512, 3);

	// Handle any errors from above.
	if ($vfystr->errstat() == true)
	{
		if ($herr->checkState() == true)
		{
			$rxe = $herr->errorGetData();
			$ajax->sendStatus($rxe, $fieldlist);
			exit(1);
		}
	}

	// Safely encode all strings to prevent XSS attacks.
	$name = safeEncodeString($name);
	
	// We are good, insert the record
	$result = $dbconf->insertOAuth($id, $name, $module, $expire, $clientid,
		$clientsecret, $scope, $authtype, $authurl, $redirect, $resource1,
		$resource2, $resource3, $resource4);
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
	global $dbuser;

	// Gather data...
	$key = getPostValue('hidden');
	$id = getPostValue('provider');

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
	
	// Check if any users are using this provider.
	$result = $dbuser->queryOAuthProvAll($key);
	if ($result == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessages());
	}
	else
	{
		handleError('Unable to delete provider while users are still using it.');
	}
	
	// Now remove the record from the database.
	$result = $dbconf->deleteOAuth($key);
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

	// Determine the editing mode.
	switch($mode)
	{
		case MODE_VIEW:			// View
			$msg1 = 'Viewing ' . $moduleDisplayUpper . ' Data For';
			$msg2 = $rxa['name'];
			$warn = '';
			$btnset = html::BTNTYP_VIEW;
			$action = '';
			$hideValue = $rxa['provider'];
			$disable = true;
			$default = true;
			$key = true;
			break;
		case MODE_UPDATE:		// Update
			$msg1 = 'Updating ' . $moduleDisplayUpper . ' Data For';
			$msg2 = $rxa['name'];
			$warn = 'Updating a provider will affect all users who use that provider.';
			$btnset = html::BTNTYP_UPDATE;
			$action = 'submitUpdate()';
			$hideValue = $rxa['provider'];
			$disable = false;
			$default = true;
			$key = true;
			break;
		case MODE_INSERT:		// Insert
			$msg1 = 'Inserting ' . $moduleDisplayUpper . ' Data';
			$msg2 = $rxa['name'];
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
			$warn = 'A provider can only be deleted when there are no users using it.';
			$btnset = html::BTNTYP_DELETE;
			$action = 'submitDelete()';
			$hideValue = $rxa['provider'];
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
			'provider' => 0,
			'name' => '',
			'module' => '',
			'expire' => 3600,
			'clientid' => '',
			'clientsecret' => '',
			'scope' => '',
			'authtype' => '',
			'authurl' => '',
			'redirecturl' => '',
			'resourceurl1' => '',
			'resourceurl2' => '',
			'resourceurl3' => '',
			'resourceurl4' => '',
		);
	}

	// Custom field rendering code
	$provider = generateField(html::TYPE_TEXT, 'provider', 'Provider', 3,
		$rxa['provider'], 'The provider\'s identification number', $default,
		$key);
	$name = generateField(html::TYPE_TEXT, 'name', 'Provider Name', 5,
		$rxa['name'], 'The name of the provider.', $default, $disable);
	$module = generateField(html::TYPE_TEXT, 'module', 'Module', 5,
		$rxa['module'], 'The module that the provider communicates with.',
		$default, $disable);
	$expire = generateField(html::TYPE_TEXT, 'expire', 'Expire', 3,
		$rxa['expire'], 'Default time that a user\'s login expires.',
		true, $disable);
	$clientid = generateField(html::TYPE_TEXT, 'clientid', 'Client ID', 4,
		$rxa['clientid'], 'The client ID as given by the provider.',
		$default, $disable);
	$clientsecret = generateField(html::TYPE_TEXT, 'clientsecret',
		'Client Secret', 4, $rxa['clientsecret'],
		'The client password that the application uses to authenticate' .
		chr(13) . ' to the provider. It must be kept confidential.',
		$default, $disable);
	$scope = generateField(html::TYPE_AREA, 'scope', 'Authority Scope', 8,
		$rxa['scope'], 'The default scope of authority as granted by the user.',
		$default, $disable);
	$scope['rows'] = 5;
	$authtype = generateField(html::TYPE_TEXT, 'authtype', 'Authentication Type',
		5, $rxa['authtype'], 'The type of authentication requested.',
		$default, $disable);
	$authurl = generateField(html::TYPE_AREA, 'authurl', 'Authentication URL', 8,
		$rxa['authurl'], 'The URL to redirect the user to for authentication' .
		' by the provider.', $default, $disable);
	$authurl['rows'] = 5;
	$redirect = generateField(html::TYPE_AREA, 'redirect', 'Redirect URL', 8,
		$rxa['redirecturl'], 'The URL that the user is redirected to when ' .
		'authentication is completed.',
		$default, $disable);
	$redirect['rows'] = 5;
	$resource1 = generateField(html::TYPE_AREA, 'resource1', 'Resource URL 1', 8,
		$rxa['resourceurl1'], 'The provider\'s URL which provides the access' .
		chr(13) . 'to the resources of the user\'s account', $default, $disable);
	$resource1['rows'] = 5;
	$resource2 = generateField(html::TYPE_AREA, 'resource2', 'Resource URL 2', 8,
		$rxa['resourceurl1'], 'The provider\'s URL which provides the access' .
		chr(13) . 'to the resources of the user\'s account', $default, $disable);
	$resource2['rows'] = 5;
	$resource3 = generateField(html::TYPE_AREA, 'resource3', 'Resource URL 3', 8,
		$rxa['resourceurl1'], 'The provider\'s URL which provides the access' .
		chr(13) . 'to the resources of the user\'s account', $default, $disable);
	$resource3['rows'] = 5;
	$resource4 = generateField(html::TYPE_AREA, 'resource4', 'Resource URL 4', 8,
		$rxa['resourceurl1'], 'The provider\'s URL which provides the access' .
		chr(13) . 'to the resources of the user\'s account', $default, $disable);
	$resource4['rows'] = 5;

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
		$provider,
		array(
			'type' => html::TYPE_FSETCLOSE,
		),
		array(
			'type' => html::TYPE_FSETOPEN,
			'name' => 'Provider Settings',
		),
		$name,
		$module,
		$expire,
		array(
			'type' => html::TYPE_FSETCLOSE,
		),
		array(
			'type' => html::TYPE_FSETOPEN,
			'name' => 'Client Authentication',
		),
		$clientid,
		$clientsecret,
		array(
			'type' => html::TYPE_FSETCLOSE,
		),
		array(
			'type' => html::TYPE_FSETOPEN,
			'name' => 'User Authentication',
		),
		$authtype,
		$authurl,
		$scope,
		$redirect,
		array(
			'type' => html::TYPE_FSETCLOSE,
		),
		array(
			'type' => html::TYPE_FSETOPEN,
			'name' => 'User Account Resources',
		),
		$resource1,
		$resource2,
		$resource3,
		$resource4,
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
	html::pageAutoGenerate($data);
}

// Generates a generic field array from the different fields.
// If more or different fields are needed, then one can just
// add them manually.
function generateField($type, $name, $label, $size = 0, $value = '',
	$tooltip = '', $default = false, $disabled = false)
{
	$data = array(
		'type' => $type,
		'name' => $name,
		'label' => $label,
	);
	if ($size != 0) $data['fsize'] = $size;
	if ($disabled == true) $data['disable'] = true;
	if ($default != false)
	{
		$data['value'] = $value;
		$data['default'] = $value;
	}
	if (!empty($tooltip)) $data['tooltip'] = $tooltip;
	$data['lsize'] = 3;
	return $data;
}

// Returns the first argument match of a $_POST value.  If no
// values are found, then returns null.
function getPostValue(...$list)
{
	foreach($list as $param)
	{
		if (isset($_POST[$param])) return $_POST[$param];
	}
	return NULL;
}


?>