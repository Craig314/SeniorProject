<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application User Editor Module

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
$moduleFilename = 'useredit.php';

// The name of the module.  It shows in the title bar of the web
// browser and other places.
$moduleTitle = 'User Editor';

// $moduleId must be a unique positive integer. Module IDs < 1000 are
// reserved for system use.  Therefore application module IDs will
// start at 1000.
$moduleId = 10;

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
require_once BASEDIR . 'dbaseuser.php';
require_once BASEDIR . 'timedate.php';
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
			'/js/useredit.js',
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
	global $dbcore;
	global $dbuser;
	global $vendor;
	global $admin;
	global $CONFIGVAR;

	// Query the database for user information.
	$rxu = $dbuser->queryUsersAll();
	if ($rxu == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessages());
		else
			handleError('Database Error: Unable to read users table.');
	}
	$rxc = $dbuser->queryContactAll();
	if ($rxc == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessages());
		else
			handleError('Database Error: Unable to read contact table.');
	}

	// Rearrange the result arrays so they can be processed.
	$users = array();
	foreach ($rxu as $kx => $vx)
	{
		$users[$vx['userid']] = $vx;
	}
	$contacts = array();
	foreach ($rxc as $kx => $vx)
	{
		$contacts[$vx['userid']] = $vx;
	}
	unset($rxu, $rxc);

	// Generate selection table.
	$list = array(
		'type' => html::TYPE_RADTABLE,
		'name' => 'select_item',
		'titles' => array(
			'User Name',
			'User ID',
			'Profile ID',
			'Name',
			'Login Method',
		),
		'tdata' => array(),
		'tooltip' => '',
	);
	$users = array_reverse($users);
	foreach ($users as $kx => $vx)
	{
		if (!$vendor && !$admin)
		{
			if ($vx['userid'] == $CONFIGVAR['account_id_vendor']['value']) continue;
			if ($vx['userid'] == $CONFIGVAR['account_id_admin']['value']) continue;
		}
		if ($admin)
		{
			if ($vx['userid'] == $CONFIGVAR['account_id_vendor']['value']) continue;
		}
		$tdata = array(
			$vx['username'],
			$vx['username'],
			$vx['userid'],
			$vx['profileid'],
			$contacts[$vx['userid']]['name'],
			$vx['method'],
		);
		array_push($list['tdata'], $tdata);
	}
	unset($users, $contacts);

	// Generate rest of page.
	$data = array(
		array(
			'type' => html::TYPE_HEADING,
			'message1' => 'User Editor',
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
// XXX: Requires customization.
function databaseLoad()
{
	global $herr;
	global $moduleDisplayLower;
	global $dbuser;

	$key = getPostValue('select_item', 'hidden');
	if ($key == NULL)
		handleError('You must select a ' . $moduleDisplayLower .
			' from the list view.');

	$rxa = $dbuser->queryUsers($key);
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
// XXX: Requires customization.
function updateRecordAction()
{
	global $ajax;
	global $herr;
	global $vfystr;
	global $moduleDisplayUpper;
	global $moduleDisplayLower;

	// Set the field list.
	$fieldlist = array(
		'username',
		'userid',
		'profid',
		'method',
		'newpass1',
		'newpass2',
		'provider',
		'name',
		'haddr',
		'maddr',
		'hphone',
		'cphone',
		'wphone',
	);
	
	// Get identity data
	$key = getPostValue('hidden');
	$id = getPostValue('username');
	$userid = getPostValue('userid');
	$profid = getPostValue('profid');
	$method = getPostValue('method');

	// Check key data.
	if ($key == NULL)
		handleError('Missing ' . $moduleDisplayLower . ' selection data.');
	if ($id == NULL)
		handleError('Missing ' . $moduleDisplayLower . ' selection data.');
	if (is_numeric($key))
		handleError('Malformed key sequence.');
	if (is_numeric($id))
		handleError('Malformed key sequence.');
	if ($key != $id)
		handleError('Database key mismatch.');

	// Get login data.  Some of these fields are dependent on the
	// login method.
	if (!is_numeric($method))
	{
		$herr->errorPutMessage(handleErrors::ETFORM, 'Malformed login method.', handleErrors::ESFAIL, 'Method', 'method', $method);
	}
	else
	{
		switch ($method)
		{
			case LOGIN_METHOD_NATIVE:
				$newpass1 = getPostValue('newpass1');
				$newpass2 = getPostValue('newpass2');
				$active = getPostValue('active');
				if (!empty($newpass1) || !empty($newpass2))
				{
					// This does some basic checking of passwords if either
					// password field is filled in.
					$flag = false;
					if (empty($newpass1))
					{
						$herr->errorPutMessage(handleErrors::ETFORM,
							'Password field cannot be blank.',
							handleErrors::ESFAIL, '', 'newpass1');
						$flag = true;
					}
					if (empty($newpass2))
					{
						$herr->errorPutMessage(handleErrors::ETFORM,
							'Password field cannot be blank.',
							handleErrors::ESFAIL, '', 'newpass2');
						$flag = true;
					}
					if ($flag == false && strcmp($newpass1, $newpass2) != 0)
					{
						$herr->errorPutMessage(handleErrors::ETFORM,
							'Passwords do not match.',
							handleErrors::ESFAIL, '', 'newpass1');
						$herr->errorPutMessage(handleErrors::ETFORM,
							'Passwords do not match.',
							handleErrors::ESFAIL, '', 'newpass2');
						$flag = true;
					}
					if ($flag == false)
					{
						$vfystr->strchk($newpass1, 'New Password', 'newpass1',
							verifyString::STR_PASSWD, true,
							$CONFIGVAR['security_passwd_maxlen']['value'],
							$CONFIGVAR['security_passwd_minlen']['value']);
						$vfystr->strchk($newpass2, 'New Password Again', 'newpass2',
							verifyString::STR_PASSWD, true,
							$CONFIGVAR['security_passwd_maxlen']['value'],
							$CONFIGVAR['security_passwd_minlen']['value']);
					}
				}
				if ($active != NULL) $active = true; else $active = false;
				break;
			case LOGIN_METHOD_OAUTH:
				$provider = getPostValue('provider');
				if ($provider == NULL)
				{
					$herr->errorPutMessage(handleErrors::ETFORM, 'Blank provider specified.', handleErrors::ESFAIL, 'Provider', 'provider');
				}
				else
				{
				}
				break;
			case LOGIN_METHOD_OPENID:
				break;
			default:
				$herr->errorPutMessage(handleErrors::ETFORM, 'Invalid login method.', handleErrors::ESFAIL, 'Method', 'method', $method);
				break;
		}
	}

	// Get contact data.
	$name = getPostValue('name');
	$haddr = getPostValue('haddr');
	$maddr = getPostValue('maddr');
	$hphone = getPostValue('hphone');
	$cphone = getPostValue('cphone');
	$wphone = getPostValue('wphone');

	// Check mandatory fields.
	$vfystr->strchk();

	// Check optional fields.
	$vfystr->strchk();

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
	// $a = safeEncodeString($a);
	// $a = safeEncodeString($a);
	// $a = safeEncodeString($a);
	// $a = safeEncodeString($a);

	// We are good, update the record
	$result = $DATABASE_UPDATE_OPERATION($key);	// XXX: Set This
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
	global $moduleDisplayUpper;
	global $moduleDisplayLower;

	// Set the field list.
	$fieldlist = array(
		'',
	);
	
	// Get data
	$id = getPostValue('');

	// Check mandatory fields.
	$vfystr->strchk();

	// Check optional fields.
	$vfystr->strchk();

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
	$a = safeEncodeString($a);
	$a = safeEncodeString($a);
	$a = safeEncodeString($a);
	$a = safeEncodeString($a);
	
	// We are good, update the record
	$result = $DATABASE_INSERT_OPERATION($id);	// XXX: Set This
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
	global $herr;
	global $moduleDisplayUpper;
	global $moduleDisplayLower;
	global $database;
	global $dbuser;
	global $CONFIGVAR;

	// Gather data...
	$key = getPostValue('hidden');
	$id = getPostValue('');		// XXX: Set This

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
	
	// Cannot delete vendor or admin user.
	if ($key == $CONFIGVAR['account_id_none']['value'] ||
		$key == $CONFIGVAR['account_id_vendor']['value'] ||
		$key == $CONFIGVAR['account_id_admin']['value'])
		handleError('You are not allowed to delete systems accounts.');

	// Now remove the module from the database.
	$result = $DATABASE_DELETE_OPERATION($key);	// XXX: Set This
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
// This function is very long and complex due to the number of database
// queries involved, the three login types, etc....  Lot's of different
// data is being massaged and presented to the user which necessates the
// complexity.
// XXX This may get broken up into several functions later on....
function formPage($mode, $rxa)
{
	global $CONFIGVAR;
	global $moduleDisplayUpper;
	global $moduleDisplayLower;
	global $dbuser;
	global $dbconf;
	global $vendor;
	global $admin;
	global $herr;

	// Determine the editing mode.
	switch($mode)
	{
		case MODE_VIEW:			// View
			$msg1 = 'Viewing ' . $moduleDisplayUpper . ' Data For';
			$msg2 = $rxa['username'];
			$warn = '';
			$btnset = html::BTNTYP_VIEW;
			$action = '';
			$hideValue = $rxa['username'];
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
			$hideValue = $rxa['username'];
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
			$hideValue = '';
			$disable = false;
			$default = false;
			$key = false;
			break;
		case MODE_DELETE:		// Delete
			$msg1 = 'Deleting ' . $moduleDisplayUpper . ' Data For';
			$msg2 = $rxa['username'];
			$warn = '';
			$btnset = html::BTNTYP_DELETE;
			$action = 'submitDelete()';
			$hideValue = $rxa['user'];
			$disable = true;
			$default = true;
			$key = true;
			break;
		default:
			handleError('Internal Error: Contact your administrator.  XX82747');
			break;
	}

	// This generate the provider list for OAuth.  We may need this if
	// the edit mode is insert or update because the user's login method
	// can change during those operations.
	if ($mode == MODE_INSERT || $mode == MODE_UPDATE)
	{
		$optlist = array();
		$rxpa = $dbconf->queryOAuthAll();
		if ($rxpa == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessages());
			$optlist = array(
				'None Available' => 'XXX-NONE-XXX',
			);
		}
		else
		{
			foreach($rxpa as $kx => $vx)
			{
				$temp = array(
					$vx['provider'] => $vx['provider'],
				);
				array_push($optlist, $temp);
			}
		}
		$providerList = array(
			'type' => html::TYPE_PULLDN,
			'label' => 'Provider',
			'name' => 'provider',
			'fsize' => 4,
			'lsize' => 4,
			'optlist' => $optlist,
			'tooltip' => 'The OAuth provider name.',
			'disable' => $disable,
		);
	}
	else
	{
		$providerList = '';
	}

	// Load and outfit the form based on login method.
	switch ($rxa['method'])
	{
		case LOGIN_METHOD_NATIVE:
			// Native login method with user name and password.
			$rxl = $dbuser->queryLogin($rxa['userid']);
			if ($rxl == false)
			{
				if ($herr->checkState())
					handleError($herr->errorGetMessages());
				else
					handleError('Database Error: Unable to retrieve required '
						. $moduleDisplayLower . ' login data.');
			}

			// Datafill the native login information.
			$db_active = $rxl['active'];
			$db_locked = $rxl['locked'];
			$db_locktime = timedate::unix2canonical($rxl['locktime']);
			$db_lastlog = timedate::unix2canonical($rxl['lastlog']);
			$db_timeout = timedate::unix2canonical($rxl['timeout']);
			$db_failcount = $rxl['failcount'];

			// Datafill the other login types so we don't get errors.
			// OAuth
			$db_state = '';
			$db_oatok = '';
			$db_oatoktype = '';
			$db_tokissue = '';
			$db_tokexpire = '';
			$db_refresh = '';
			$db_scope = '';
			$db_provider = '';

			// OpenID (None)

			// Mark the visibility of the login types appropriately.
			$hideNative = false;
			$hideOauth = true;
			$hideOpenid = true;
			break;
		case LOGIN_METHOD_OAUTH:
			// Login method using OAuth which is basically one type of single
			// signin (Kerberos from Unix, LDAP from Microsoft), method which
			// uses an authentication provider other than ourselves to
			// authenticate the user.
			$rxl = $dbuser->queryOAuth($rxa['userid']);
			if ($rxl == false)
			{
				if ($herr->checkState())
					handleError($herr->errorGetMessages());
				else
					handleError('Database Error: Unable to retrieve required OAuth '
						. ' user data.');
			}

			// Datafill the OAuth information.
			$db_state = $rxl['state'];
			$db_oatok = $rxl['token'];
			$db_oatoktype = $rxl['tokentype'];
			$db_tokissue = timedate::unix2canonical($rxl['issue']);
			$db_tokexpire = timedate::unix2canonical($rxl['expire']);
			$db_refresh = $rxl['refresh'];
			$db_scope = $rxl['scope'];
			$db_provider = $rxl['provider'];

			// Now datafill the other login types so we don't get errors.
			// Native
			$db_active = '';
			$db_locked = '';
			$db_locktime = '';
			$db_lastlog = '';
			$db_timeout = '';
			$db_failcount = '';

			// OpenID (None)

			// Mark the login types appropriately.
			$hideNative = true;
			$hideOauth = false;
			$hideOpenid = true;
			break;
		case LOGIN_METHOD_OPENID:
			// Currently none.

			// Now datafill the other login types so we don't get errors.
			// Native
			$db_active = '';
			$db_locked = '';
			$db_locktime = '';
			$db_lastlog = '';
			$db_timeout = '';
			$db_failcount = '';

			// OAuth
			$state = '';
			$oatok = '';
			$oatoktype = '';
			$tokissue = '';
			$tokexpire = '';
			$refresh = '';
			$scope = '';
			$db_provider = '';

			// Mark the login types appropriately.
			$hideNative = true;
			$hideOauth = true;
			$hideOpenid = false;
			break;
		default:
			handleError('Invalid Login Method.');
			break;
	}

	// Load contact information from the database.
	$rxc = $dbuser->queryContact($rxa['userid']);
	if ($rxc == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessages());
		else
			handleError('Database Error: Unable to retrieve required '
				. $moduleDisplayLower . ' contact data.');
	}

	// Load profiles from database.
	$rxp = $dbconf->queryProfileAll();
	if ($rxp == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessages());
		else
			handleError('Database Error: Unable to retrieve required '
				. $moduleDisplayLower . ' profile data.');
	}

	// Process profile information into option list format.
	$optlist = array();
	foreach ($rxp as $kx => $vx)
	{
		$optlist[$vx['name']] = $vx['profileid'];
	}

	// Hidden field to pass key data
	if (!empty($hideValue))
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
			'' => '',
		);
	}

	// Custom field rendering code
	// Identity (This is passed in $rxa)
	$userid = generateField(html::TYPE_TEXT, 'userid', 'User ID', 3, $rxa['userid'],
		'The numeric user id which uniqly identifies the user.', $default, $key);
	$uname = generateField(html::TYPE_TEXT, 'username', 'User Name', 6,
		$rxa['username'], 'The name the user logs in with.', $default, $disable);
	$profid = array(
		'type' => html::TYPE_PULLDN,
		'label' => 'Profile',
		'default' => $rxa['profileid'],
		'name' => 'profid',
		'fsize' => 4,
		'lsize' => 4,
		'optlist' => $optlist,
		'tooltip' => 'The profile that the user is assigned to.',
		'disable' => $disable,
	);
	$method = array(
		'type' => html::TYPE_PULLDN,
		'label' => 'Login Method',
		'default' => $rxa['method'],
		'name' => 'method',
		'fsize' => 4,
		'lsize' => 4,
		'optlist' => array(
			'Native' => LOGIN_METHOD_NATIVE,
			'OAuth' => LOGIN_METHOD_OAUTH,
			'OpenID' => LOGIN_METHOD_OPENID,
		),
		'tooltip' => 'The method that the user uses to login with.',
		'disable' => $disable,
		'event' => 'onchange',
		'action' => 'setHidden()',
		);
	
	// Native login method fields.
	$newpass1 = generateField(html::TYPE_PASS, 'newpass1', 'New Password', 6,
		'', 'Enter new password for user.', $default, $disable);
	$newpass2 = generateField(html::TYPE_PASS, 'newpass2', 'New Password Again',
		6, '', 'Repeat new password entry for user.', $default, $disable);
	$active = generateField(html::TYPE_CHECK, 'active', 'Account Active', 1,
		$db_active, 'Indicates if the account is active or not.',
		$default, $disable);
	$active['sidemode'] = true;
	$active['side'] = 0;
	$locked = generateField(html::TYPE_CHECK, 'locked', 'Account Locked', 1,
		$db_locked, 'Indicates if the account has been locked out.',
		$default, true);
	$locked['sidemode'] = true;
	$locked['side'] = 1;
	$locktime = generateField(html::TYPE_TEXT, 'locktime', 'Lockout Time',
		4, $db_locktime, 'View the time that the user\'s locked out ' .
		'account is reenabled.', true, true);
	$lastlog = generateField(html::TYPE_TEXT, 'lastlog', 'Last Login Time',
		4, $db_lastlog, 'View the user\'s last successful login attempt.',
		true, true);
	$timeout = generateField(html::TYPE_TEXT, 'timeout', 'Password Timeout',
		4, $db_timeout, 'View when the user\'s current password will ' .
		'time out.', true, true);
	$failcount = generateField(html::TYPE_TEXT, 'failcount',
		'Failed Login Attempts', 4, $db_failcount, 'View the time ' .
		'that the user\'s locked out account is reenabled.', true, true);

	// OAuth login method fields.
	$state = generateField(html::TYPE_TEXT, 'state', 'OAuth State',
		4, $db_state, 'View the user\'s state string which helps' .
		' enable user authentication.', true, true);
	$oatok = generateField(html::TYPE_TEXT, 'oatok', 'OAuth Token', 4,
		$db_oatok, 'The OAuth token that the provider responded with.',
		true, true);
	$oatoktype = generateField(html::TYPE_TEXT, 'oatoktype',
		'OAuth Token Type', 4, $db_oatoktype, 'The OAuth token that ' .
		'the provider responded with.', true, true);
	$tokissue = generateField(html::TYPE_TEXT, 'tokissue', 'Token ' .
		'Issue Time', 4, $db_tokissue, 'View the time the authentication ' .
		'token was issued by the provider.', true, true);
	$tokexpire = generateField(html::TYPE_TEXT, 'tokexpire', 'Token ' .
		'Expire Time', 4, $db_tokexpire,
		'View  the time the authentication token was issued by the ' .
		'provider.', true, true);
	$refresh = generateField(html::TYPE_TEXT, 'refresh', 'OAuth Refresh',
		4, $db_refresh, 'View the OAuth refresh data.', true, true);
	$scope = generateField(html::TYPE_TEXT, 'scope', 'Access Scope',
		4, $db_scope, 'View the granted access scope that the ' .
		'user has granted to their profile.', true, true);
	switch ($mode)
	{
		case MODE_VIEW:
		case MODE_DELETE:
			$provider = generateField(html::TYPE_TEXT, 'provider',
				'OAuth Provider', 3, $db_provider, 'The OAuth ' .
				'provider which authenticates the user to the application',
				true, true);
			break;
		case MODE_INSERT:
			$provider = $providerList;
			break;
		case MODE_UPDATE:
			$provider = $providerList;
			$provider['default'] = $db_provider;
			break;
		default:
			handleError('Invalid Mode Specified');
			break;
	}

	// OpenID login method fields.

	// Contact Information
	$name = generateField(html::TYPE_TEXT, 'name', 'Name', 6, $rxc['name'],
		'The user\'s real name.', $default, $disable);
	$haddr = generateField(html::TYPE_AREA, 'haddr', 'Home Address', 6,
		$rxc['haddr'], 'The user\'s home address.', $default, $disable);
	$haddr['rows'] = 5;
	$maddr = generateField(html::TYPE_AREA, 'maddr', 'Mailing Address', 6,
		$rxc['maddr'], 'The user\'s mailing address.', $default, $disable);
	$maddr['rows'] = 5;
		$email = generateField(html::TYPE_TEXT, 'email', 'E-Mail Address', 6,
		$rxc['email'], 'The user\'s e-mail address.', $default, $disable);
	$hphone = generateField(html::TYPE_TEXT, 'hphone', 'Home Phone Number', 4,
		$rxc['hphone'], 'The user\'s home phone number', $default, $disable);
	$cphone = generateField(html::TYPE_TEXT, 'cphone', 'Mobile Phone Number', 4,
		$rxc['cphone'], 'The user\'s mobile phone number', $default, $disable);
	$wphone = generateField(html::TYPE_TEXT, 'wphone', 'Work Phone Number', 4,
		$rxc['wphone'], 'The user\'s work phone number', $default, $disable);


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
			'name' => 'Identity',
		),
		$uname,
		$userid,
		$profid,
		$method,
		array(
			'type' => html::TYPE_FSETCLOSE,
		),

		array(
			'type' => html::TYPE_FSETOPEN,
			'name' => 'Login',
		),
		array(
			'type' => html::TYPE_HIDEOPEN,
			'name' => 'nativeLogin',
			'hidden' => $hideNative,
		),
		$newpass1,
		$newpass2,
		$active,
		$locked,
		$locktime,
		$lastlog,
		$timeout,
		$failcount,
		array(
			'type' => html::TYPE_HIDECLOSE,
		),
		array(
			'type' => html::TYPE_HIDEOPEN,
			'name' => 'oauthLogin',
			'hidden' => $hideOauth,
		),
		$provider,
		$state,
		$oatok,
		$oatoktype,
		$tokissue,
		$tokexpire,
		$refresh,
		$scope,
		array(
			'type' => html::TYPE_HIDECLOSE,
		),
		array(
			'type' => html::TYPE_HIDEOPEN,
			'name' => 'openidLogin',
			'hidden' => $hideOpenid,
		),
		array(
			'type' => html::TYPE_HIDECLOSE,
		),
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
		array('type' => html::TYPE_BOTB2)
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
	if ($default != false) $data['value'] = $value;
	if (!empty($tooltip)) $data['tooltip'] = $tooltip;
	$data['lsize'] = 4;
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