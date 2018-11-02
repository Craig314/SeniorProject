<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application Function Template

This is a function template that does one thing and one thing only.
This does not exist in the module database.

*/



// These variables must be set for every function.

// The executable file for the function.  Filename and extension only,
// no path component.
$functionFilename = '';			// XXX Set This

// The capitalized short display name of the function.  This shows up
// on buttons, and some error messages.
$functionDisplayUpper = '';		// XXX Set This

// The lowercase short display name of the function.  This shows up in
// various messages.
$functionDisplayLower = '';		// XXX Set This

// Single flag number that defines if the profile that the user
// is assigned to has permission to access this function or not.
// -1 means that all users have access.
$functionPermission = -1;		// XXX Set this

// Indicates if this function is part of the system core or the
// application.  It is very important that this gets set correctly.
$functionSystem = false;


// These are the data editing modes.
const MODE_VIEW		= 0;
const MODE_UPDATE	= 1;
const MODE_INSERT	= 2;
const MODE_DELETE	= 3;

// Field check generation data formats.
const FIELDCHK_JSON		= 0;
const FIELDCHK_ARRAY	= 1;

// Order matters here.  The modhead library needs to be loaded last.
// If additional libraries are needed, then load them before.
// Freeform execute stops at modhead.php
const BASEDIR = '../libs/';
const BASEAPP = '../applibs/';
require_once BASEAPP . 'dbaseapp.php';
require_once BASEDIR . 'funchead.php';

// Aways called on a HTTP GET method request.
// Do not remove
// XXX: Requires customization.
function commandProcessorGet($commandId)
{
	global $ajax;

	switch ((int)$commandId)
	{
		default:
			// If we get here, then the command is undefined.
			$ajax->sendCode(ajaxClass::CODE_NOTIMP,
				'The command ' . $_POST['COMMAND'] .
				' has not been implemented');
			exit(1);
			break;
	}
}

// Always called on a HTTP POST method request.
// Do not remove
// XXX: Requires customization.
function commandProcessorPost($commandId)
{
	global $ajax;

	switch ((int)$commandId)
	{
		case 12:	// Submit Update. XXX: Remove if not being used.
			updateRecordAction();
			break;
		case 13:	// Submit Insert XXX: Remove if not being used.
			insertRecordAction();
			break;
		case 14:	// Submit Delete XXX: Remove if not being used.
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

// Aways called on a HTTP PUT method request.
// Do not remove
// XXX: Requires customization.
function commandProcessorPut($commandId)
{
	global $ajax;

	switch ((int)$commandId)
	{
		default:
			// If we get here, then the command is undefined.
			$ajax->sendCode(ajaxClass::CODE_NOTIMP,
				'The command ' . $_POST['COMMAND'] .
				' has not been implemented');
			exit(1);
			break;
	}
}

// Performs the primary action for this function
// XXX: Requires customization.
function performDataAction()
{
	// XXX Set one and delete the other.  This directly ties into
	// what HTTP method is being used: POST or GET.
	$key = GetPostValue('');
	$key = GetGetValue('');

	// Read from database.
	$rxa = databaseLoad($key);

	// Render page.
	// XXX Change VIEW_MODE to a different mode if needed.
	formPage(VIEW_MODE, $rxa);	
}

// Helper function for the view functions below that loads information
// from the database and check for errors.
// XXX: Requires customization.
function databaseLoad($key)
{
	global $herr;
	global $functionDisplayLower;
	global $dbapp;
	global $dbuser;
	global $dbconf;

	if ($key == NULL)
		handleError('There is no data that was specified for '
			. $functionDisplayLower . ' to act on.');
	// The below line requires customization for database loading.	
	$rxa = $DATABASE_QUERY_OPERATION($key);		// XXX Set This
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Unable to retrieve required '
				. $functionDisplayLower . ' data.');
	}
	return $rxa;
}

// Updates the record in the database.
// XXX: Requires customization. Remove if not using update mode.
function updateRecordAction()
{
	global $ajax;
	global $herr;
	global $vfystr;
	global $CONFIGVAR;
	global $functionDisplayUpper;
	global $functionDisplayLower;
	global $dbapp;
	global $dbuser;
	global $dbconf;

	// Get field data.
	$fieldData = generateFieldCheck(FIELDCHK_ARRAY);

	// Get data
	$key = getPostValue('hidden');
	$id = getPostValue('');

	// Check key data.
	if ($key == NULL)
		handleError('Missing ' . $functionDisplayLower . ' selection data.');
	if ($id == NULL)
		handleError('Missing ' . $functionDisplayLower . ' selection data.');
	if (!is_numeric($key))
		handleError('Malformed key sequence.');
	if (!is_numeric($id))
		handleError('Malformed key sequence.');
	if ($key != $id)
		handleError('Database key mismatch.');

	// Check field data.
	$vfystr->fieldchk($fieldData, $index, $postData);

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
	// $ = safeEncodeString($);
	// $ = safeEncodeString($);
	// $ = safeEncodeString($);
	// $ = safeEncodeString($);
	
	// We are good, update the record
	$result = $DATABASE_UPDATE_OPERATION($key);		// XXX Set This
	if ($result == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database: Record update failed. Key = ' . $key);
	}
	sendResponse($functionDisplayUpper . ' update completed: key = ' . $key);
	exit(0);
}

// Inserts the record into the database.
// XXX: Requires customization. Remove if not using insert mode.
function insertRecordAction()
{
	global $ajax;
	global $herr;
	global $vfystr;
	global $CONFIGVAR;
	global $functionDisplayUpper;
	global $functionDisplayLower;
	global $dbapp;
	global $dbuser;
	global $dbconf;

	// Get field data.
	$fieldData = generateFieldCheck(FIELDCHK_ARRAY);

	// Get data
	$id = getPostValue('');

	// Check field data.
	$vfystr->fieldchk($fieldData, $index, $postData);

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
	// $ = safeEncodeString($);
	// $ = safeEncodeString($);
	// $ = safeEncodeString($);
	// $ = safeEncodeString($);
	
	// We are good, insert the record
	$result = $DATABASE_INSERT_OPERATION($id);		// XXX Set This
	if ($result == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database: Record insert failed. Key = ' . $id);
	}
	sendResponseClear($functionDisplayUpper . ' insert completed: key = '
		. $id);
	exit(0);
}

// Deletes the record from the database.
// XXX: Requires customization. Remove if not using delete mode.
function deleteRecordAction()
{
	global $ajax;
	global $herr;
	global $vfystr;
	global $CONFIGVAR;
	global $functionDisplayUpper;
	global $functionDisplayLower;
	global $dbapp;
	global $dbuser;
	global $dbconf;

	// Gather data...
	$key = getPostValue('hidden');
	$id = getPostValue('');		// XXX Set This

	// ...and check it.
	if ($key == NULL)
		handleError('Missing ' . $functionDisplayLower . ' module selection data.');
	if ($id == NULL)
		handleError('Missing ' . $functionDisplayLower . ' selection data.');
	if (!is_numeric($key))
		handleError('Malformed key sequence.');
	if (!is_numeric($id))
		handleError('Malformed key sequence.');
	if ($key != $id)
		handleError('Database key mismatch.');
	
	// Now remove the record from the database.
	$result = $DATABASE_DELETE_OPERATION($key);		// XXX Set This
	if ($result == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Unable to delete ' . $functionDisplayLower .
				' data. Key = ' . $key);
	}
	sendResponse($functionDisplayUpper . ' delete completed: key = ' . $key);
	exit(0);
}

// Generate generic form page and render data to it.
// XXX: Requires customization
function formPage($mode, $rxa)
{
	global $CONFIGVAR;
	global $functionDisplayUpper;
	global $functionDisplayLower;
	global $ajax;
	global $dbapp;
	global $dbuser;
	global $dbconf;

	// Determine the editing mode.
	switch($mode)
	{
		case MODE_VIEW:			// View
			$msg1 = 'Viewing ' . $functionDisplayUpper . ' Data For';
			$msg2 = '';
			$warn = '';
			$btnset = html::BTNTYP_VIEW;
			$action = '';
			$hideValue = '';
			$disable = true;
			$default = true;
			$key = true;
			break;
		case MODE_UPDATE:		// Update
			$msg1 = 'Updating ' . $functionDisplayUpper . ' Data For';
			$msg2 = '';
			$warn = '';
			$btnset = html::BTNTYP_UPDATE;
			$action = 'submitUpdate()';
			$hideValue = '';
			$disable = false;
			$default = true;
			$key = true;
			break;
		case MODE_INSERT:		// Insert
			$msg1 = 'Inserting ' . $functionDisplayUpper . ' Data';
			$msg2 = '';
			$warn = '';
			$btnset = html::BTNTYP_INSERT;
			$action = 'submitInsert()';
			$disable = false;
			$default = false;
			$key = false;
			break;
		case MODE_DELETE:		// Delete
			$msg1 = 'Deleting ' . $functionDisplayUpper . ' Data For';
			$msg2 = '';
			$warn = '';
			$btnset = html::BTNTYP_DELETE;
			$action = 'submitDelete()';
			$hideValue = '';
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
			'' => '',
		);
	}

	// XXX Custom field rendering code


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


		array(
			'type' => html::TYPE_ACTBTN,
			'dispname' => $functionDisplayUpper,
			'btnset' => $btnset,
			'action' => $action,
		),
		array('type' => html::TYPE_FORMCLOSE),
		array('type' => html::TYPE_WDCLOSE),
		array('type' => html::TYPE_BOTB2),
		array('type' => html::TYPE_VTAB10),
	);

	// Render
	// XXX: Choose One
	$ajax->writeMainPanelImmediate(html::pageAutoGenerate($data),
		generateFieldCheck(0));
	$ajax->writeStatusPanelImmediate(html::pageAutoGenerate($data));
}

// Generate the field definitions for client side error checking.
// Remove if not using update or insert functions.
// XXX: Requires Customization. Remove if not using insert or update.
function generateFieldCheck($returnType = 0)
{
	global $CONFIGVAR;
	global $vfystr;

	$data = array(
		0 => array(
			// This is the display name of the field.
			'dispname' => '',
			// The internal name of the field.
			'name' => '',
			// The type of the field.
			'type' => $vfystr::STR_,
			// If special handling of this field is required, then set type
			// to $vfystr::STR_CUSTOM and this field to the actual datatype.
			// Remove if this field is not needed.
			'ctype' => $vfystr::STR_,
			// True if this field cannot be blank.  False otherwise.
			'noblank' => true,
			// Same as noblank above, except this is only for insert mode.
			// Use only if different from noblank.
			// Remove if this field is not needed.
			'noblankins' => true,
			// Maximum allowable field length (or numeric value).
			'max' => 0,
			// Minimum allowable field length (or numeric value).
			// In all cases, if min > max, then no checking is done.
			'min' => 0,
		),
		0 => array(
			'dispname' => '',
			'name' => '',
			'type' => $vfystr::STR_,
			'ctype' => $vfystr::STR_,
			'noblank' => true,
			'noblankins' => true,
			'max' => 0,
			'min' => 0,
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