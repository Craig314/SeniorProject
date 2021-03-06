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
$moduleId = 24;

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

// These are flags to indicate what is in the database and what tables
// are being changed for an update operation.  The following values
// have the given meanings.
// 0 - no change
// 1 - insert
// 2 - delete
// 3 - update
const DBCHG_NONE	= 0;
const DBCHG_INS		= 1;
const DBCHG_DEL		= 2;
const DBCHG_UPD		= 3;

// This setting indicates that a file will be used instead of the
// default template.  Set to the name of the file to be used.
//$inject_html_file = '../dat/somefile.html';
$htmlInjectFile = false;

// Order matters here.  The modhead library needs to be loaded last.
// If additional libraries are needed, then load them before.
if (file_exists('../applibs/dbaseapp.php'))
	include_once('../applibs/dbaseapp.php');
require_once '../libs/password.php';
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
			'/js/module/useredit.js',
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
	global $dbcore;
	global $dbuser;
	global $dbconf;
	global $vendor;
	global $admin;
	global $CONFIGVAR;
	global $herr;

	// Query the database for user information.
	$rxu = $dbuser->queryUsersAll();
	if ($rxu === false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Unable to read users table.');
	}
	$rxc = $dbuser->queryContactAll();
	if ($rxc === false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Unable to read contact table.');
	}
	$rxpa = $dbconf->queryOAuthAll();
	if ($rxpa === false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Unable to read OAuth providers table.');
	}
	$rxpo = $dbconf->queryOpenIdAll();
	if ($rxpo === false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Unable to read OpenID providers table.');
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
	$provOAuth = array();
	foreach ($rxpa as $kx => $vx)
	{
		$provOAuth[$vx['provider']] = $vx;
	}
	$provOpenId = array();
	foreach ($rxpo as $kx => $vx)
	{
		$provOpenId[$vx['provider']] = $vx;
	}
	unset($rxu, $rxc, $rxpa, $rxpo);

	// Generate selection table.
	$list = array(
		'type' => html::TYPE_RADTABLE,
		'name' => 'select_item',
		'clickset' => true,
		'condense' => true,
		'hover' => true,
		'titles' => array(
			'User Name',
			'User ID',
			'Profile ID',
			'Name',
			'Login Method',
			'Provider',
		),
		'tdata' => array(),
		'tooltip' => '',
	);
	//$users = array_reverse($users);
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
		switch ($vx['method'])
		{
			case LOGIN_METHOD_NATIVE:
				$method = 'Native';
				$provname = 'Native';
				break;
			case LOGIN_METHOD_OAUTH:
				$method = 'OAuth';
				$rxa = $dbuser->queryOAuth($vx['userid']);
				if ($rxa == false) $provname = '**ERROR**';
					else $provname = $rxa['name'];
				break;
			case LOGIN_METHOD_OPENID:
				$method = 'OpenID';
				$rxa = $dbuser->queryOpenId($vx['userid']);
				if ($rxa == false) $provname = '**ERROR**';
					else $provname = $rxa['name'];
				break;
			default:
				$method = '**ERROR**';
				break;
		}
		$tdata = array(
			$vx['userid'],
			$vx['username'],
			$vx['userid'],
			$vx['profileid'],
			$contacts[$vx['userid']]['name'],
			$method,
			$provname,
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
	global $dbuser;

	$key = getPostValue('select_item', 'hidden');
	if ($key == NULL)
		handleError('You must select a ' . $moduleDisplayLower .
			' from the list view.');

	$rxa = $dbuser->queryUsersUserId($key);
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
	global $dbcore;
	global $dbuser;
	global $dbconf;
	global $vendor;
	global $admin;

	// Get the field check data.
	$fieldCheck = generateFieldCheck(FIELDCHK_ARRAY);

	// Set initial database modes.
	$dbChangeNative = DBCHG_NONE;
	$dbChangeOAuth = DBCHG_NONE;
	$dbChangeOpenId = DBCHG_NONE;

	// Get identity data
	$key = getPostValue('hidden');
	$username = getPostValue('username');
	$userid = getPostValue('userid');
	$profid = getPostValue('profid');
	$method = getPostValue('method');
	$orgid = getPostValue('orgid');
	$active = getPostValue('active');

	// Check key data.
	if ($key == NULL)
		handleError('Missing ' . $moduleDisplayLower . ' selection data.');
	if ($userid == NULL)
		handleError('Missing ' . $moduleDisplayLower . ' selection data.');
	$vfystr->strchk($key, 'Selection Data', '', verifyString::STR_PINTEGER,
		true, 2147483647, 1);
	if ($vfystr->errstat())
		handleError($herr->errorGetMessage());
	$vfystr->fieldchk($fieldCheck, 0, $userid);
	if ($vfystr->errstat())
	{
		$rxe = $herr->errorGetData();
		$ajax->sendStatus($rxe);
		exit(1);
	}
	if ($key != $userid)
		handleError('Database key mismatch.');
	
	// Check the rest of the data.
	$vfystr->fieldchk($fieldCheck, 1, $username);
	$vfystr->fieldchk($fieldCheck, 3, $profid);
	$vfystr->fieldchk($fieldCheck, 4, $method);
	$vfystr->fieldchk($fieldCheck, 2, $orgid);
	if (!empty($active)) $active = true; else $active = false;
	if ($vendor || $admin) $active = true;
	if ($vfystr->errstat())
	{
		$rxe = $herr->errorGetData();
		$ajax->sendStatus($rxe);
		exit(1);
	}
	
	// We need to make sure that the given profile ID exists.
	checkProfileId($profid);

	switch ($method)
	{
		case LOGIN_METHOD_NATIVE:
			$newpass1 = getPostValue('newpass1');
			$newpass2 = getPostValue('newpass2');
			$pwdflag = 0;
			if (!empty($newpass1) || !empty($newpass2))
			{
				// This does some basic checking of passwords if either
				// password field is filled in.
				$pwdflag = 1;
				if (empty($newpass1))
				{
					$herr->errorPutMessage(handleErrors::ETFORM,
						'Password field cannot be blank.',
						handleErrors::ESFAIL, '', 'newpass1');
					$pwdflag = 2;
				}
				if (empty($newpass2))
				{
					$herr->errorPutMessage(handleErrors::ETFORM,
						'Password field cannot be blank.',
						handleErrors::ESFAIL, '', 'newpass2');
					$pwdflag = 2;
				}
				if ($pwdflag != 2)
				{
					$res1 = $vfystr->fieldchk($fieldChk, 6, $newpass1);
					$res2 = $vfystr->fieldchk($fieldChk, 7, $newpass2);
					if ($res1 == false || $res2 == false) $pwdflag = 2;
					unset($res1, $res2);
				}
				if ($pwdflag != 2)
				{
					if (strcmp($newpass1, $newpass2) != 0)
					{
						$herr->errorPutMessage(handleErrors::ETFORM,
							'Passwords do not match.',
							handleErrors::ESFAIL, '', 'newpass1');
						$herr->errorPutMessage(handleErrors::ETFORM,
							'Passwords do not match.',
							handleErrors::ESFAIL, '', 'newpass2');
						$pwdflag = 2;
					}
				}
			}
			$dbChangeNative = DBCHG_UPD;
			break;
		case LOGIN_METHOD_OAUTH:
			$provider = getPostValue('oaprovider');
			$result = $vfystr->fieldchk($fieldChk, 8, $provider);
			if ($result)
			{
				// We have to do a database read to make sure the provider
				// is actually in the database.
				checkOAuthProvider($provider);
			}
			$dbChangeOAuth = DBCHG_UPD;
			break;
		case LOGIN_METHOD_OPENID:
			$provider = getPostValue('opprovider');
			$result = $vfystr->fieldchk($fieldChk, 9, $provider);
			if ($result)
			{
				// We have to do a database read to make sure the provider
				// is actually in the database.
				checkOpenIdProvider($provider);
			}
			$opident = getPostValue('opident');
			$vfystr->fieldchk($fieldChk, 10, $opident);
			$dbChangeOpenId = DBCHG_UPD;
			break;
		default:
			$herr->errorPutMessage(handleErrors::ETFORM,
				'Invalid login method.', handleErrors::ESFAIL,
				'Method', 'method', $method);
			break;
	}

	// Get contact data.
	$name = getPostValue('name');
	$haddr = getPostValue('haddr');
	$maddr = getPostValue('maddr');
	$email = getPostValue('email');
	$hphone = getPostValue('hphone');
	$cphone = getPostValue('cphone');
	$wphone = getPostValue('wphone');

	// Check contact data fields.
	$vfystr->fieldchk($fieldCheck, 11, $name);
	$vfystr->fieldchk($fieldCheck, 12, $haddr);
	$vfystr->fieldchk($fieldCheck, 13, $maddr);
	$vfystr->fieldchk($fieldCheck, 14, $email);
	$vfystr->fieldchk($fieldCheck, 15, $hphone);
	$vfystr->fieldchk($fieldCheck, 16, $cphone);
	$vfystr->fieldchk($fieldCheck, 17, $wphone);

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

	// If we get to this point, then all field checks have passed.  We now
	// perform safe encoding of the strings and then submit them to the
	// database.  Because of the multiple tables involved in the database
	// update, this code will be somewhat complicated.

	// Safely encode all strings to prevent XSS attacks.
	switch ($method)
	{
		case LOGIN_METHOD_NATIVE:
			break;
		case LOGIN_METHOD_OAUTH:
			break;
		case LOGIN_METHOD_OPENID:
			break;
		default;
			break;
	}
	$name = safeEncodeString($name);
	$haddr = safeEncodeString($haddr);
	$maddr = safeEncodeString($maddr);
	$email = safeEncodeString($email);
	$hphone = safeEncodeString($hphone);
	$cphone = safeEncodeString($cphone);
	$wphone = safeEncodeString($wphone);

	// Now we need to check what is present in the database and
	// update the internal variables.
	$rxLogin = $dbuser->queryLogin($userid);
	$rxOAuth = $dbuser->queryOAuth($userid);
	$rxOpenId = $dbuser->queryOpenId($userid);
	$rxContact = $dbuser->queryContact($userid);
	$rxUsers = $dbuser->queryUsersUserId($userid);
	if ($rxLogin !== false) $dbPresentNative = true; else $dbPresentNative = false;
	if ($rxOAuth !== false) $dbPresentOAuth = true; else $dbPresentOAuth = false;
	if ($rxOpenId !== false) $dbPresentOpenId = true; else $dbPresentOpenId = false;

	// Now comes the logic of what to update, insert, and delete.
	if ($dbChangeNative == DBCHG_UPD)
	{
		if ($dbPresentOAuth)
		{
			$dbChangeNative = DBCHG_INS;
			$dbChangeOAuth = DBCHG_DEL;
		}
		if ($dbPresentOpenId)
		{
			$dbChangeNative = DBCHG_INS;
			$dbChangeOpenId = DBCHG_DEL;
		}
	}
	if ($dbChangeOAuth == DBCHG_UPD)
	{
		if ($dbPresentLogin)
		{
			$dbChangeOAuth = DBCHG_INS;
			$dbChangeNative = DBCHG_DEL;
		}
		if ($dbPresentOpenId)
		{
			$dbChangeOAuth = DBCHG_INS;
			$dbChangeOpenId = DBCHG_DEL;
		}
	}
	if ($dbChangeOpenId == DBCHG_UPD)
	{
		if ($dbPresentNative)
		{
			$dbChangeOpenId = DBCHG_INS;
			$dbChangeNative = DBCHG_DEL;
		}
		if ($dbPresentOAuth)
		{
			$dbChangeOpenId = DBCHG_INS;
			$dbChangeOAuth = DBCHG_DEL;
		}
	}

	// We are good, update the record

	// Open the database transaction.
	$result = $dbcore->transOpen();
	if ($result == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Unable to open database transaction.');
	}
	$result = true;

	// Native Login Table Transactions
	switch ($dbChangeNative)
	{
		case DBCHG_UPD:
			if ($pwdflag == 1)
			{
				$pwdcount = $CONFIGVAR['security_hash_rounds']['value'];
				$pwddigest = NULL;
				$pwdsalt = NULL;
				$pwdpasswd = NULL;
				password::encryptNew($newpass1, $pwdsalt, $pwdpasswd, $pwddigest, $pwdcount);
				if ($CONFIGVAR['security_passchg_new']['value'] == 1)
					$timeout = 0;
				else
					$timeout = time() + $CONFIGVAR['security_passexp_timeout']['value'];
			}
			else
			{
				$pwdcount = $rxLogin['count'];
				$pwddigest = $rxLogin['digest'];
				$pwdsalt = $rxLogin['salt'];
				$pwdpasswd = $rxLogin['passwd'];
				$timeout = $rxLogin['timeout'];
			}
			$lock = $rxLogin['locked'];
			$locktime = $rxLogin['locktime'];
			$failcount = $rxLogin['failcount'];
			$lastlog = $rxLogin['lastlog'];
			$res = $dbuser->updateLogin($userid, $lock, $locktime, $failcount,
				$lastlog, $timeout, $pwddigest, $pwdcount, $pwdsalt, $pwdpasswd);
			break;
		case DBCHG_INS:
			if ($pwdflag != 1)
			{
				// Password is required when inserting into the login table.
				$dbcore->transRollback();
				$herr->errorPutMessage(handleErrors::ETFORM,
					'Password required when changing login method to native.',
					handleErrors::ESFAIL, '', 'newpass1');
				$herr->errorPutMessage(handleErrors::ETFORM,
					'Password required when changing login method to native.',
					handleErrors::ESFAIL, '', 'newpass2');
				$rxe = $herr->errorGetData();
				$ajax->sendStatus($rxe);
				exit(1);
			}
			$pwdcount = $CONFIGVAR['security_hash_rounds']['value'];
			$pwddigest = NULL;
			$pwdsalt = NULL;
			$pwdpasswd = NULL;
			password::encryptNew($newpass1, $pwdsalt, $pwdpasswd, $pwddigest, $pwdcount);
			$timeout = time() + $CONFIGVAR['security_passexp_timeout']['value'];
			$lock = $rxLogin[''];
			$locktime = $rxLogin[''];
			$failcount = $rxLogin[''];
			$lastlog = $rxLogin[''];
			$res = $dbuser->insertLogin($userid, $lock, $locktime, $failcount,
				$lastlog, $timeout, $pwddigest, $pwdcount, $pwdsalt, $pwdpasswd);
			break;
		case DBCHG_DEL:
			$res = $dbuser->deleteLogin($userid);
			break;
		default:
			break;
	}
	$result = ($res) ? $result : false;

	// OAuth Table Transactions
	switch ($dbChangeOAuth)
	{
		case DBCHG_UPD:
			if ($provider != $rxOAuth['provider'])
			{
				$state = '';
				$oatok = '';
				$oatoktype = '';
				$oaissue = 0;
				$oaexpire = 0;
				$refresh = '';
				$scope = '';
				$challenge = '';
			}
			else
			{
				$state = $rxOAuth['state'];
				$oatok = $rxOAuth['token'];
				$oatoktype = $rxOAuth['tokentype'];
				$oaissue = $rxOAuth['issue'];
				$oaexpire = $rxOAuth['expire'];
				$refresh = $rxOAuth['refresh'];
				$scope = $rxOAuth['scope'];
				$challenge = $rxOAuth['challenge'];
			}
			$res = $dbuser->updateOAuth($userid, $state, $provider, $oatok,
				$oatoktype, $oaissue, $oaexpire, $refresh, $scope, $challenge);
			break;
		case DBCHG_INS:
			$state = '';
			$oatok = '';
			$oatoktype = '';
			$oaissue = 0;
			$oaexpire = 0;
			$refresh = '';
			$scope = '';
			$challenge = '';
			$res = $dbuser->insertOAuth($userid, $state, $provider, $oatok,
				$oatoktype, $oaissue, $oaexpire, $refresh, $scope, $challenge);
		break;
		case DBCHG_DEL:
			$res = $dbuser->deleteOAuth($userid);
			break;
		default:
			break;
	}
	$result = ($res) ? $result : false;

	// OpenID Table Transactions
	switch ($dbChangeOpenId)
	{
		case DBCHG_UPD:
			if ($provider != rxOpenId['provider'])
			{
				$opissue = 0;
				$opexpire = 0;
			}
			else
			{
				$opissue = $rxOpenId['issue'];
				$opexipre = $rxOpenId['expire'];
			}
			$res = $dbuser->updateOpenId($userid, $provider, $opident, $opissue, $opexpire);
			break;
		case DBCHG_INS:
			$opissue = 0;
			$opexpire = 0;
			$res = $dbuser->insertOpenId($userid, $provider, $opident, $opissue, $opexpire);
			break;
		case DBCHG_DEL:
			$res = $dbuser->deleteOpenId($userid);
			break;
		default:
			break;
	}
	$result = ($res) ? $result : false;

	// Users Table Transaction
	$res = $dbuser->updateUsers($username, $userid, $profid, $method, $active, $orgid);
	$result = ($res) ? $result : false;

	// Contact Table Transaction
	$res = $dbuser->updateContact($userid, $name, $haddr, $maddr, $email,
		$hphone, $wphone, $cphone);
	$result = ($res) ? $result : false;

	// Commit or rollback database changes.
	if ($result)
	{
		$res = $dbcore->transCommit();
		if ($result == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage());
			else
				handleError('Database: Record update failed. Key = ' . $key);
		}
		sendResponse($moduleDisplayUpper . ' update completed: key = ' . $key);
	}
	else
	{
		$dbcore->transRollback();
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database: Record update failed. Key = ' . $key);
	}
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
	global $dbuser;
	global $dbcore;
	global $admin;
	global $vendor;

	$fieldCheck = generateFieldCheck(FIELDCHK_ARRAY);

	// Get identiy data...
	$userid = getPostValue('userid');
	$username = getPostValue('username');
	$profid = getPostValue('profid');
	$method = getPostValue('method');
	$orgid = getPostValue('orgid');
	$active = getPostValue('active');

	// ...and check it.
	$vfystr->fieldchk($fieldCheck, 0, $userid);
	$vfystr->fieldchk($fieldCheck, 1, $username);
	$vfystr->fieldchk($fieldCheck, 3, $profid);
	$vfystr->fieldchk($fieldCheck, 4, $method);
	$vfystr->fieldchk($fieldCheck, 2, $orgid);
	if (!empty($active)) $active = true; else $active = false;
	if ($vendor || $admin) $active = true;	

	// We need to make sure that we have a valid profile Id.
	checkProfileId($profid);

	// Get login method specific data and check it at the same time.
	switch ($method)
	{
		case LOGIN_METHOD_NATIVE:
			// Password
			$newpass1 = getPostValue('newpass1');
			$newpass2 = getPostValue('newpass2');
			$pwdflag = 1;
			if (empty($newpass1))
			{
				$herr->errorPutMessage(handleErrors::ETFORM,
					'Password field cannot be blank.',
					handleErrors::ESFAIL, '', 'newpass1');
				$pwdflag = 2;
			}
			if (empty($newpass2))
			{
				$herr->errorPutMessage(handleErrors::ETFORM,
					'Password field cannot be blank.',
					handleErrors::ESFAIL, '', 'newpass2');
				$pwdflag = 2;
			}
			if ($pwdflag != 2)
			{
				$res1 = $vfystr->fieldchk($fieldCheck, 6, $newpass1);
				$res2 = $vfystr->fieldchk($fieldCheck, 7, $newpass2);
				if ($res1 == false || $res2 == false) $pwdflag = 2;
				unset($res1, $res2);
			}
			if ($pwdflag != 2)
			{
				if (strcmp($newpass1, $newpass2) != 0)
				{
					$herr->errorPutMessage(handleErrors::ETFORM,
						'Passwords do not match.',
						handleErrors::ESFAIL, '', 'newpass1');
					$herr->errorPutMessage(handleErrors::ETFORM,
						'Passwords do not match.',
						handleErrors::ESFAIL, '', 'newpass2');
					$pwdflag = 2;
				}
			}

			// Default Values
			$lock = 0;
			$locktime = 0;
			$lastlog = 0;
			$failcount = 0;
			if ($CONFIGVAR['security_passchg_new']['value'] == 1)
				$timeout = 0;
			else
				$timeout = time() + $CONFIGVAR['security_passexp_timeout']['value'];

			// Encrypt the password
			$pwdhash = '';
			$pwdsalt = '';
			$pwdpasswd = '';
			$pwdcount = $CONFIGVAR['security_hash_rounds']['value'];
			password::encryptNew($newpass1, $pwdsalt, $pwdpasswd, $pwdhash, $pwdcount);
			break;
		case LOGIN_METHOD_OAUTH:
			// OAuth Provider
			$provider = getPostValue('oaprovider');
			$result = $vfystr->fieldchk($fieldCheck, 8, $provider);
			if ($result)
			{
				// We have to do a database read to make sure the provider
				// is actually in the database.
				checkOAuthProvider($provider);
			}

			// Default Values
			$state = '';
			$oatok = '';
			$oatoktype = '';
			$oaissue = 0;
			$oaexpire = 0;
			$refresh = '';
			$scope = '';
			$challenge = '';
			break;
		case LOGIN_METHOD_OPENID:
			// OpenID Provider
			$provider = getPostValue('opprovider');
			$result = $vfystr->fieldchk($fieldCheck, 9, $provider);
			if ($result)
			{
				// We have to do a database read to make sure the provider
				// is actually in the database.
				checkOpenIdProvider($provider);
			}

			// OpenID Identity
			$opident = getPostValue('opident');
			$vfystr->fieldchk($fieldCheck, 10, $opident);

			// Default Values
			$opissue = 0;
			$opexpire = 0;
			break;
		default:
			$herr->errorPutMessage(handleErrors::ETFORM,
			'Invalid login method.', handleErrors::ESFAIL,
			'Method', 'method', $method);
			break;
	}

	// Get contact data.
	$name = getPostValue('name');
	$haddr = getPostValue('haddr');
	$maddr = getPostValue('maddr');
	$email = getPostValue('email');
	$hphone = getPostValue('hphone');
	$cphone = getPostValue('cphone');
	$wphone = getPostValue('wphone');

	// Check contact data fields.
	$vfystr->fieldchk($fieldCheck, 11, $name);
	$vfystr->fieldchk($fieldCheck, 12, $haddr);
	$vfystr->fieldchk($fieldCheck, 13, $maddr);
	$vfystr->fieldchk($fieldCheck, 14, $email);
	$vfystr->fieldchk($fieldCheck, 15, $hphone);
	$vfystr->fieldchk($fieldCheck, 16, $cphone);
	$vfystr->fieldchk($fieldCheck, 17, $wphone);

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

	// If we get to this point, then all the data has been evaluated as being
	// good.  Now we can proceed to insert the user into the database.

	// Safely encode all strings to prevent XSS attacks.
	switch ($method)
	{
		case LOGIN_METHOD_NATIVE:
			break;
		case LOGIN_METHOD_OAUTH:
			break;
		case LOGIN_METHOD_OPENID:
			break;
		default;
			break;
	}
	$name = safeEncodeString($name);
	$haddr = safeEncodeString($haddr);
	$maddr = safeEncodeString($maddr);
	$email = safeEncodeString($email);
	$hphone = safeEncodeString($hphone);
	$cphone = safeEncodeString($cphone);
	$wphone = safeEncodeString($wphone);

	// Open the transaction.
	$result = $dbcore->transOpen();
	if ($result == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Unable to open database transaction.');
	}
	$result = true;

	// ** ORDER MATTERS HERE **
	// First we put the user in the users table.
	$res = $dbuser->insertUsers($username, $userid, $profid, $method, $active, $orgid);
	$result = ($res) ? $result : false;

	// Then contacts
	$res = $dbuser->insertContact($userid, $name, $haddr, $maddr, $email,
		$hphone, $wphone, $cphone);
	$result = ($res) ? $result : false;	

	// Finally, the login specific data.
	switch ($method)
	{
		case LOGIN_METHOD_NATIVE:
			$res = $dbuser->insertLogin($userid, $lock, $locktime,
				$failcount, $lastlog, $timeout, $pwdhash, $pwdcount, $pwdsalt,
				$pwdpasswd);
			break;
		case LOGIN_METHOD_OAUTH:
			$res = $dbuser->inserOAuth($userid, $state, $provider, $oatok,
				$oatoktype, $oaissue, $oaexpire, $refresh, $scope, $challenge);
			break;
		case LOGIN_METHOD_OPENID:
			$res = $dbuser->insertOpenId($userid, $provider, $opident,
				$opissue, $opexpire);
			break;
	}
	$result = ($res) ? $result : false;

	// Now we either commit the transaction to make all the updates
	// to the database happen at the same time, or we roll back the
	// changes if one of the actions failed.
	if ($result)
	{
		$res = $dbcore->transCommit();
		if ($res == false)
		{
			if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Unable to insert ' . $moduleDisplayLower .
				' data. Key = ' . $key);
		}
		sendResponse($moduleDisplayUpper . ' insert completed: key = ' . $userid);
	}
	else
	{
		$dbcore->transRollback();
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Unable to insert ' . $moduleDisplayLower .
				' data. Key = ' . $userid);
	}
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
	global $dbcore;
	global $dbuser;
	global $dbapp;

	// Gather data...
	$key = getPostValue('hidden');
	$userid = getPostValue('userid');

	// ...and check it.
	if ($key == NULL)
		handleError('Missing ' . $moduleDisplayLower . ' selection data.');
	if ($userid == NULL)
		handleError('Missing ' . $moduleDisplayLower . ' selection data.');
	$vfystr->strchk($key, 'Selection Data', '', verifyString::STR_PINTEGER,
		true, 2147483647, 1);
	if ($vfystr->errstat())
		handleError($herr->errorGetMessage());
	$vfystr->strchk($userid, 'User ID', 'userid', verifyString::STR_PINTEGER,
		true, 2147483647, 1);
	if ($vfystr->errstat())
	{
		$rxe = $herr->errorGetData();
		$ajax->sendStatus($rxe);
		exit(1);
	}
	if ($key != $userid)
		handleError('Database key mismatch.');
	
	// Cannot delete vendor or admin user.
	if ($key == $CONFIGVAR['account_id_none']['value'] ||
		$key == $CONFIGVAR['account_id_vendor']['value'] ||
		$key == $CONFIGVAR['account_id_admin']['value'])
		handleError('You are not allowed to delete system accounts.');

	// Now remove the user from the database.

	// Check if we have an application associated with the core.
	// If so, then we need to take action on the application first.
	if ($dbapp != NULL)
	{
		$result = $dbapp->metaDeleteUser($userid);
		if ($result == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage());
			else
				handleError('Database Error: Unable to delete user from application.');
		}
	}

	// We need to check what is present in the database and
	// update the internal variables.
	$rxLogin = $dbuser->queryLogin($userid);
	$rxOAuth = $dbuser->queryOAuth($userid);
	$rxOpenId = $dbuser->queryOpenId($userid);
	$rxContact = $dbuser->queryContact($userid);
	$rxUsers = $dbuser->queryUsersUserId($userid);
	if ($rxLogin !== false) $dbPresentNative = true; else $dbPresentNative = false;
	if ($rxOAuth !== false) $dbPresentOAuth = true; else $dbPresentOAuth = false;
	if ($rxOpenId !== false) $dbPresentOpenId = true; else $dbPresentOpenId = false;
	if ($rxContact !== false) $dbPresentContact = true; else $dbPresentContact = false;
	if ($rxUsers !== false) $dbPresentUsers = true; else $dbPresentUsers = false;


	// This must be done as a transaction because we are updating
	// multiple tables
	$result = $dbcore->transOpen();
	if ($result == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Unable to open database transaction.');
	}
	$result = true;

	// Removes native login information, if present...
	if ($dbPresentNative)
	{
		$res = $dbuser->deleteLogin($userid);
		$result = ($res) ? $result : false;
	}

	// Removes OAuth login information, if present...
	if ($dbPresentOAuth)
	{
		$res = $dbuser->deleteOAuth($userid);
		$result = ($res) ? $result : false;
	}

	// Removes OpenID login information, if present...
	if ($dbPresentOpenId)
	{
		$res = $dbuser->deleteOpenId($userid);
		$result = ($res) ? $result : false;
	}

	// Removes contact information, if present...
	if ($dbPresentContact)
	{
		$res = $dbuser->deleteContact($userid);
		$result = ($res) ? $result : false;
	}

	// Remove user login information, if present...
	if ($dbPresentUsers)
	{
		$res = $dbuser->deleteUsers($userid);
		$result = ($res) ? $result : false;
	}

	// Now we either commit the transaction to make all the updates
	// to the database happen at the same time, or we roll back the
	// changes if one of the actions failed.
	if ($result)
	{
		$res = $dbcore->transCommit();
		if ($res == false)
		{
			if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Unable to delete ' . $moduleDisplayLower .
				' data. Key = ' . $key);
		}
		sendResponse($moduleDisplayUpper . ' delete completed: key = ' . $key);
	}
	else
	{
		$dbcore->transRollback();
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Unable to delete ' . $moduleDisplayLower .
				' data. Key = ' . $key);
	}
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
	global $ajax;

	// Determine the editing mode.
	switch($mode)
	{
		case MODE_VIEW:			// View
			$msg1 = 'Viewing ' . $moduleDisplayUpper . ' Data For';
			$msg2 = $rxa['username'];
			$warn = '';
			$btnset = html::BTNTYP_VIEW;
			$action = '';
			$hideValue = $rxa['userid'];
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
			$hideValue = $rxa['userid'];
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
			$msg2 = $rxa['username'];
			$warn = '';
			$btnset = html::BTNTYP_DELETE;
			$action = 'submitDelete()';
			$hideValue = $rxa['userid'];
			$disable = true;
			$default = true;
			$key = true;
			break;
		default:
			handleError('Internal Error: Contact your administrator.  XX82747');
			break;
	}

	// In all editing modes, we have to load the profiles from
	// the database so we can get the profile names.
	$rxp = $dbconf->queryProfileAll();
	if ($rxp == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Unable to retrieve required '
				. $moduleDisplayLower . ' profile data.');
	}
	$optlist = array();
	foreach ($rxp as $kx => $vx)
	{
		$profileList[$vx['name']] = $vx['profileid'];
		$profileIndex[$vx['profileid']] = $vx['name'];
	}

	// For insert or update mode, we need to query several databases to
	// build pulldown lists for various options.  Mainly the profiles,
	// available OAuth and OpenID providers.
	if ($mode == MODE_INSERT || $mode == MODE_UPDATE)
	{
		// Load and process OAuth providers.
		$oaOptlist = array();
		$rxpa = $dbconf->queryOAuthAll();
		if ($rxpa == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage());
		}
		else
		{
			foreach($rxpa as $kx => $vx)
			{
				$oaOptlist[$vx['name']] = $vx['provider'];
			}
		}
		$oaProviderList = array(
			'type' => html::TYPE_PULLDN,
			'label' => 'Provider',
			'name' => 'oaprovider',
			'fsize' => 4,
			'optlist' => $oaOptlist,
			'tooltip' => 'The OAuth provider name.',
			'disable' => $disable,
		);

		// Load and process OpenID providers.
		$opOptlist = array();
		$rxpo = $dbconf->queryOpenIdAll();
		if ($rxpo == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage());
		}
		else
		{
			foreach($rxpo as $kx => $vx)
			{
				$opOptlist[$vx['name']] = $vx['provider'];
			}
		}
		$opProviderList = array(
			'type' => html::TYPE_PULLDN,
			'label' => 'Provider',
			'name' => 'opprovider',
			'fsize' => 4,
			'optlist' => $opOptlist,
			'tooltip' => 'The OAuth provider name.',
			'disable' => $disable,
		);
		$profid = array(
			'type' => html::TYPE_PULLDN,
			'label' => 'Profile',
			'default' => $rxa['profileid'],
			'name' => 'profid',
			'fsize' => 4,
			'optlist' => $profileList,
			'tooltip' => 'The profile that the user is assigned to.',
			'disable' => $disable,
		);
		$method = array(
			'type' => html::TYPE_PULLDN,
			'label' => 'Login Method',
			'default' => $rxa['method'],
			'name' => 'method',
			'fsize' => 4,
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
	}
	else
	{
		$oaProviderList = '';
		$opProviderList = '';
		$profid = generateField(html::TYPE_TEXT, 'profid', 'Profile', 4,
			$profileIndex[$rxa['profileid']], 'The profile the user is assigned to.',
			$default, $disable);
		switch ($rxa['method'])
		{
			case LOGIN_METHOD_NATIVE:
				$logMethod = 'Native';
				break;
			case LOGIN_METHOD_OAUTH:
				$logMethod = 'OAuth';
				break;
			case LOGIN_METHOD_OPENID:
				$logMethod = 'OpenID';
				break;
			default:
				break;
		}
		$method = generateField(html::TYPE_TEXT, 'method', 'Login Method',
			2, $logMethod, 'The method that the user uses to login with.',
			$default, $disable);
		unset($logMethod);
	}

	// Load default form data based on login method
	// and editing mode.
	switch ($rxa['method'])
	{
		case LOGIN_METHOD_NATIVE:
			if ($mode == MODE_INSERT)
			{
				$db_locked = '';
				$db_locktime = '';
				$db_lastlog = '';
				$db_timeout = '';
				$db_failcount = '';
			}
			else
			{
				// Native login method with user name and password.
				$rxl = $dbuser->queryLogin($rxa['userid']);
				if ($rxl == false)
				{
					if ($herr->checkState())
						handleError($herr->errorGetMessage());
					else
						handleError('Database Error: Unable to retrieve required '
							. $moduleDisplayLower . ' login data.');
				}

				// Datafill the native login information.
				$db_locked = $rxl['locked'];
				$db_locktime = timedate::unix2canonical($rxl['locktime']);
				$db_lastlog = timedate::unix2canonical($rxl['lastlog']);
				$db_timeout = timedate::unix2canonical($rxl['timeout']);
				$db_failcount = $rxl['failcount'];
			}

			// Datafill the other login types so we don't get errors.
			// OAuth
			$db_state = '';
			$db_oatok = '';
			$db_oatoktype = '';
			$db_tokissue = '';
			$db_tokexpire = '';
			$db_refresh = '';
			$db_scope = '';
			$db_oaprovider = '';

			// OpenID
			$db_opident = '';
			$db_opissue = '';
			$db_opexire = '';
			$db_opprovider = '';

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
			if ($mode == MODE_INSERT)
			{
				$db_state = '';
				$db_oatok = '';
				$db_oatoktype = '';
				$db_tokissue = '';
				$db_tokexpire = '';
				$db_refresh = '';
				$db_scope = '';
				$db_oaprovider = '';
			}
			else
			{
				$rxl = $dbuser->queryOAuth($rxa['userid']);
				if ($rxl == false)
				{
					if ($herr->checkState())
						handleError($herr->errorGetMessage());
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
				$db_oaprovider = $rxl['provider'];
			}

			// Now datafill the other login types so we don't get errors.
			// Native
			$db_locked = '';
			$db_locktime = '';
			$db_lastlog = '';
			$db_timeout = '';
			$db_failcount = '';

			// OpenID
			$db_opident = '';
			$db_opissue = '';
			$db_opexire = '';
			$db_opprovider = '';

			// Mark the login types appropriately.
			$hideNative = true;
			$hideOauth = false;
			$hideOpenid = true;
			break;
		case LOGIN_METHOD_OPENID:
			// Login method using OAuth which is basically one type of single
			// signin (Kerberos from Unix, LDAP from Microsoft), method which
			// uses an authentication provider other than ourselves to
			// authenticate the user.
			if ($mode == MODE_INSERT)
			{
				$db_opident = '';
				$db_opissue = '';
				$db_opexire = '';
				$db_opprovider = '';
			}
			else
			{
				$rxl = $dbuser->queryOpenId($rxa['userid']);
				if ($rxl == false)
				{
					if ($herr->checkState())
						handleError($herr->errorGetMessage());
					else
						handleError('Database Error: Unable to retrieve required OpenID '
							. ' user data.');
				}

				// Datafill the OpenID information.
				$db_opident = $rxl['ident'];
				$db_opissue = timedate::unix2canonical($rxl['issue']);
				$db_opexire = timedate::unix2canonical($rxl['expire']);
				$db_opprovider = $rxl['provider'];
			}

			// Now datafill the other login types so we don't get errors.
			// Native
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
			$db_oaprovider = '';

			// Mark the login types appropriately.
			$hideNative = true;
			$hideOauth = true;
			$hideOpenid = false;
			break;
		default:
			handleError('Database Error: Invalid Login Method.');
			break;
	}

	// Load contact information from the database.
	if ($mode == MODE_INSERT)
	{
		$rxc = array(
			'name' => '',
			'haddr' => '',
			'maddr' => '',
			'email' => '',
			'hphone' => '',
			'cphone' => '',
			'wphone' => '',
		);
	}
	else
	{
		$rxc = $dbuser->queryContact($rxa['userid']);
		if ($rxc == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage());
			else
				handleError('Database Error: Unable to retrieve required '
					. $moduleDisplayLower . ' contact data.');
		}
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
			'userid' => '',
			'username' => '',
			'profileid' => '',
			'method' => '',
			'active' => true,
			'orgid' => '',
		);
	}

	// Custom field rendering code
	// Identity (This is passed in $rxa)
	$userid = generateField(html::TYPE_TEXT, 'userid', 'User ID', 3, $rxa['userid'],
		'The numeric user id which uniquely identifies the user.', $default, $key);
	$uname = generateField(html::TYPE_TEXT, 'username', 'User Name', 6,
		$rxa['username'], 'The name the user logs in with.', $default, $disable);
	$active = generateField(html::TYPE_CHECK, 'active', 'Account Active', 1,
		$rxa['active'], 'Indicates if the account is active or not.',
		$default, $disable);
	$orgid = generateField(html::TYPE_TEXT, 'orgid', 'Organization ID', 6,
		$rxa['orgid'], 'The organizational ID the user has been assigned.' .
		'<br>This is different from the user ID.', $default, $disable);
	
	
	// Native login method fields.

	// OAuth login method fields.
	switch ($mode)
	{
		case MODE_VIEW:
		case MODE_DELETE:
			$oaProvider = generateField(html::TYPE_TEXT, 'provider',
				'OAuth Provider', 3, $db_oaprovider, 'The OAuth ' .
				'provider which authenticates the user to the application',
				true, true);
			break;
		case MODE_INSERT:
			$oaProvider = $oaProviderList;
			break;
		case MODE_UPDATE:
			$oaProvider = $oaProviderList;
			$oaProvider['default'] = $db_oaprovider;
			break;
		default:
			handleError('Invalid Mode Specified');
			break;
	}

	// OpenID login method fields.
	switch ($mode)
	{
		case MODE_VIEW:
		case MODE_DELETE:
			$opProvider = generateField(html::TYPE_TEXT, 'provider',
				'OpenID Provider', 3, $db_opprovider, 'The OpenID ' .
				'provider which authenticates the user to the application',
				true, true);
			break;
		case MODE_INSERT:
			$opProvider = $opProviderList;
			break;
		case MODE_UPDATE:
			$opProvider = $opProviderList;
			$opProvider['default'] = $db_opprovider;
			break;
		default:
			handleError('Invalid Mode Specified');
			break;
	}
	$opident = generateField(html::TYPE_TEXT, 'opident', 'OpenID Identity',
	4, $db_opident, 'The user\'s OpenID identity.', $default, $disable);

	// Different fields are displayed in different editing modes.
	if ($mode == MODE_VIEW || $mode == MODE_DELETE)
	{
		// Native
		$newpass1 = NULL;
		$newpass2 = NULL;
		$locked = generateField(html::TYPE_CHECK, 'locked', 'Account Locked', 1,
			$db_locked, 'Indicates if the account has been locked out.',
			$default, true);
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

		// OAuth
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
			'View the time the authentication will expire.', 'provider.',
			true, true);
		$refresh = generateField(html::TYPE_TEXT, 'refresh', 'OAuth Refresh',
			4, $db_refresh, 'View the OAuth refresh data.', true, true);
		$scope = generateField(html::TYPE_TEXT, 'scope', 'Access Scope',
			4, $db_scope, 'View the granted access scope that the ' .
			'user has granted to their profile.', true, true);
		
		// OpenID
		$opissue = generateField(html::TYPE_TEXT, 'opissue', 'OpenID ' .
			'Issue Time', 4, $db_tokissue, 'View the time the authentication ' .
			'was issued by the provider.', true, true);
		$opexpire = generateField(html::TYPE_TEXT, 'opexpire', 'OpenID ' .
			'Expire Time', 4, $db_tokissue, 'View the time the authentication ' .
			'will expire.', true, true);
	}
	else
	{
		// Native
		$newpass1 = generateField(html::TYPE_PASS, 'newpass1', 'New Password', 6,
			'', 'Enter new password for user.', $default, $disable);
		$newpass2 = generateField(html::TYPE_PASS, 'newpass2', 'New Password Again',
			6, '', 'Repeat new password entry for user.', $default, $disable);
		$locked = NULL;
		$locktime = NULL;
		$lastlog = NULL;
		$timeout = NULL;
		$failcount = NULL;

		// OAuth
		$state = NULL;
		$oatok = NULL;
		$oatoktype = NULL;
		$tokissue = NULL;
		$tokexpire = NULL;
		$refresh = NULL;
		$scope = NULL;

		// OpenID
		$opissue = NULL;
		$opexpire = NULL;
	}
	
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
		$userid,
		$uname,
		$profid,
		$method,
		$orgid,
		$active,
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
		$oaProvider,
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
		$opProvider,
		$opident,
		$opissue,
		$opexpire,
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
		array('type' => html::TYPE_BOTB2),
		array('type' => html::TYPE_VTAB10),
	);

	// Render
	$ajax->writeMainPanelImmediate(html::pageAutoGenerate($data),
		generateFieldCheck());
}

// Checks to make sure that the given OAuth provider is valid.
function checkOAuthProvider($provider)
{
	global $dbconf;

	$rxa = $dbconf->queryOAuth($provider);
	if ($rxa == false)
	{
		$herr->errorPutMessage(handleErrors::ETFORM,
			'Specified provider is not valid.',
			handleErrors::ESFAIL, '', 'provider');
	}
}

// Checks to make sure that the given OpenID provider is valid.
function checkOpenIdProvider($provider)
{
	global $dbconf;

	$rxa = $dbconf->queryOpenId($provider);
	if ($rxa == false)
	{
		$herr->errorPutMessage(handleErrors::ETFORM,
			'Specified provider is not valid.',
			handleErrors::ESFAIL, '', 'provider');
	}
}

// Checks to make sure that the given Profile ID is valid.
function checkProfileId($profid)
{
	global $dbconf;

	$rxa = $dbconf->queryProfile($profid);
	if ($rxa == false)
	{
		$herr->errorPutMessage(handleErrors::ETFORM,
			'Specified profile does not exist.',
			handleErrors::ESFAIL, '', 'profid');
	}
}

// Generate the field definitions for client side error checking.
function fcData()
{
	global $CONFIGVAR;
	global $vfystr;

	$data = array(
		0 => array(
			'dispname' => 'User ID',
			'name' => 'userid',
			'type' => $vfystr::STR_PINTEGER,
			'noblank' => true,
			'max' => 2147483647,
			'min' => 1,
		),
		1 => array(
			'dispname' => 'Username',
			'name' => 'username',
			'type' => $vfystr::STR_USERID,
			'noblank' => true,
			'max' => $CONFIGVAR['security_username_maxlen']['value'],
			'min' => $CONFIGVAR['security_username_minlen']['value'],
		),
		2 => array(
			'dispname' => 'Organization ID',
			'name' => 'orgid',
			'type' => $vfystr::STR_USERID,
			'noblank' => false,
			'max' => 32,
			'min' => 0,
		),
		3 => array(
			'dispname' => 'Profile ID',
			'name' => 'profid',
			'type' => $vfystr::STR_PINTEGER,
			'noblank' => true,
			'max' => 2147483647,
			'min' => 1,
		),
		4 => array(
			'dispname' => 'Login Method',
			'name' => 'method',
			'type' => $vfystr::STR_PINTEGER,
			'noblank' => true,
			'max' => 2,
			'min' => 0,
		),
		5 => array(
			'dispname' => 'Account Active',
			'name' => 'active',
			'type' => $vfystr::STR_NONE,
			'noblank' => false,
			'max' => 0,
			'min' => 1,
		),
		6 => array(
			'dispname' => 'New Password',
			'name' => 'newpass1',
			'type' => $vfystr::STR_CUSTOM,
			'ctype' => $vfystr::STR_PASSWD,
			'noblank' => false,
			'max' => $CONFIGVAR['security_passwd_maxlen']['value'],
			'min' => $CONFIGVAR['security_passwd_minlen']['value'],
		),
		7 => array(
			'dispname' => 'New Password Again',
			'name' => 'newpass2',
			'type' => $vfystr::STR_CUSTOM,
			'ctype' => $vfystr::STR_PASSWD,
			'noblank' => false,
			'max' => $CONFIGVAR['security_passwd_maxlen']['value'],
			'min' => $CONFIGVAR['security_passwd_minlen']['value'],
		),
		8 => array(
			'dispname' => 'Provider',
			'name' => 'oaprovider',
			'type' => $vfystr::STR_CUSTOM,
			'ctype' => $vfystr::STR_PINTEGER,
			'noblank' => false,
			'max' => 2147483647,
			'min' => 0,
		),
		9 => array(
			'dispname' => 'Provider',
			'name' => 'opprovider',
			'type' => $vfystr::STR_CUSTOM,
			'ctype' => $vfystr::STR_PINTEGER,
			'noblank' => false,
			'max' => 2147483647,
			'min' => 0,
		),
		10 => array(
			'dispname' => 'OpenID Identifier',
			'name' => 'opident',
			'type' => $vfystr::STR_CUSTOM,
			'ctype' => $vfystr::STR_URI,
			'noblank' => false,
			'max' => 512,
			'min' => 0,
		),
		11 => array(
			'dispname' => 'Name',
			'name' => 'name',
			'type' => $vfystr::STR_NAME,
			'noblank' => true,
			'max' => 50,
			'min' => 0,
		),
		12 => array(
			'dispname' => 'Home Address',
			'name' => 'haddr',
			'type' => $vfystr::STR_ADDR,
			'noblank' => false,
			'max' => 100,
			'min' => 0,
		),
		13 => array(
			'dispname' => 'Mailing Address',
			'name' => 'maddr',
			'type' => $vfystr::STR_ADDR,
			'noblank' => false,
			'max' => 100,
			'min' => 0,
		),
		14 => array(
			'dispname' => 'EMail Address',
			'name' => 'email',
			'type' => $vfystr::STR_EMAIL,
			'noblank' => false,
			'max' => 50,
			'min' => 0,
		),
		15 => array(
			'dispname' => 'Home Phone',
			'name' => 'hphone',
			'type' => $vfystr::STR_PHONE,
			'noblank' => false,
			'max' => 30,
			'min' => 0,
		),
		16 => array(
			'dispname' => 'Cell Phone',
			'name' => 'cphone',
			'type' => $vfystr::STR_PHONE,
			'noblank' => false,
			'max' => 30,
			'min' => 0,
		),
		17 => array(
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