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

Note on filenames:  Filename conflicts between files in the modules
directory and the application directory are resolved by the files in
the module directory taking precidence.

*/



// These variables must be set for every module.

// The executable file for the module.  Filename and extension only,
// no path component.
$moduleFilename = 'grades.php';		// XXX Set This

// The name of the module.  It shows in the title bar of the web
// browser and other places.
$moduleTitle = 'Grades';		// XXX Set This

// $moduleId must be a unique positive integer. Module IDs < 1000 are
// reserved for system use.  Therefore application module IDs will
// start at 1000.
$moduleId = 1500;		// XXX Set This

// The capitalized short display name of the module.  This shows up
// on buttons, and some error messages.
$moduleDisplayUpper = 'Grades';		// XXX Set This

// The lowercase short display name of the module.  This shows up in
// various messages.
$moduleDisplayLower = 'grades';		// XXX Set This

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
require_once BASEAPP . 'panels.php';
require_once BASEAPP . 'loadmodule.php';
require_once BASEAPP . 'dbaseapp.php';
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
			//'/js/application/.js',
		);

		// cssfiles is an associtive array which contains additional
		// cascading style sheets that should be included in the head
		// section of the HTML page.
		$cssFiles = array(
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

	global $dbapp;	//Need to use dbaseapp.php functions
	global $herr;   

	global $baseUrl;
	global $dbconf;
	global $moduleTitle;
	global $moduleDisplayLower;
	global $dbuser;
	global $admin;
	global $vendor;

	global $panels;
	global $ajax;

	// Get data from database.

	$list = false;

	// If the currently logged user is vendor or admin, then show all courses
	if ($vendor || $admin)
	{
		//$rxa = $dbapp->queryCourseAll();	//Querying the database
		$rxa = $dbapp->queryCourseAll();
		if ($rxa == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage());
			else
				handleError('There are no ' . $moduleDisplayLower . '\'s in the database to query.');

		}
	}
	else
	{
		//Checks the course query is for a student first.
		$rxb = $dbapp->queryStudentclassStudentAll($_SESSION['userId']);
		if ($rxb == false)	//If false then there is an error or query is for an instructor's courses.
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage());
			else
			{
				//Handling case that user is an instructor
				$rxa = $dbapp->queryCourseInstructAll($_SESSION['userId']);	// 
				if ($rxa == false)
				{
					if ($herr->checkState())
						handleError($herr->errorGetMessage());
					else
						handleError('There are no ' . $moduleDisplayLower . '\'s in the database to query.');
				}
				//var_dump($rxa);

			}
		}
		else
		{
			//Handle case that user is a student
			//$rxa = array();
			$rxa = $dbapp->queryGradesAll($_SESSION['userId']);
			if($rxa == false)
			{
				if ($herr->checkState())
						handleError($herr->errorGetMessage());
				else
					handleError('There are no ' . $moduleDisplayLower . '\'s in the database to query.');
			}
			else {
				// Generate Selection Table.
				$list = array(
					'type' => html::TYPE_RADTABLE,
					'name' => 'select_item',
					'clickset' => true,
					'condense' => true,
					'hover' => true,
					'titles' => array(
						// Add column titles here
						'Assignment',
						'Course',
						'Comment',
						'Grade',
					),
					'tdata' => array(),
					'tooltip' => array(),
				);
				foreach ($rxa as $kx => $vx)
				{
					//foreach ($kx as $vx) {
						$tdata = array(
							// These are the values that show up under the columns above.
							// The *FIRST* value is the value that is sent when a row
							// is selected.  AKA Key Field.
							$vx['studentid'],
							$vx['assignment'],
							$vx['course'],
							$vx['comment'],
							$vx['grade'],
						);
						array_push($list['tdata'], $tdata);
						//array_push($list['tooltip'], $vx['description']);
					//}
				}
			}
		}
	}

	if($list == false) 
	{
		$list = array(
			'type' => html::TYPE_BOTB1,
			'data' => 'There are no ' . $moduleDisplayLower . '\'s in the database to query.',
		);
	}

	// Generate rest of page. (Title, headers, etc)
	$data = array(
		array(
			'type' => html::TYPE_HEADING,
			'message1' => 'Grades',
			'message2' => 'Test',	// Delete if not needed.
			'warning' => 'Still in development',
		),
		array('type' => html::TYPE_TOPB1),
		array('type' => html::TYPE_WD75OPEN),
		array(
			'type' => html::TYPE_FORMOPEN,
			'name' => 'select_table',
		),

		// Enter custom data here.
		array(
			'type' => html::TYPE_FSETOPEN,
			'name' => 'Current Grades'
		),
		$list,

		//End of custom data

		array('type' => html::TYPE_FORMCLOSE),
		array('type' => html::TYPE_WDCLOSE),
		array('type' => html::TYPE_BOTB1)
	);

	// Get panel content
	$navContent = $panels->getLinks();
	$statusContent = $panels->getStatus();
	$mainContent = html::pageAutoGenerate($data);
	//$testContent = html::insertRadioButtons($data);

	// Queue content in ajax transmit buffer.
	$ajax->loadQueueCommand(ajaxClass::CMD_WMAINPANEL, $mainContent);
	$ajax->loadQueueCommand(ajaxClass::CMD_WNAVPANEL, $navContent);
	$ajax->loadQueueCommand(ajaxClass::CMD_WSTATPANEL, $statusContent);
	
	//Don't need this if using loadQueueCommand
	
	//$ajax->writePanelsImmediate($navContent, $statusContent, $mainContent);
	
	// Render
	$ajax->sendQueue();

	// Render
	//echo html::pageAutoGenerate($data);
	exit(0);
}

// Called when the initial command processor doesn't have the
// command number. This call comes from modhead.php.
function commandProcessor($commandId)
{
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
		case 5:		// Load Module
			$moduleLoad->loadModule();
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

	$key = getPostValue('select_item', 'hidden');
	if ($key == NULL)
		handleError('You must select a ' . $moduleDisplayLower . ' from the list view.');
	// The below line requires customization for database loading.	
	$rxa = $DATABASE_QUERY_OPERATION($key);		// XXX Set This
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessages());
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
// XXX: Requires customization.
function updateRecordAction()
{
	global $ajax;
	global $herr;
	global $vfystr;
	global $CONFIGVAR;
	global $moduleDisplayUpper;
	global $moduleDisplayLower;

	// Get field data.
	$fieldData = generateFieldCheck(FIELDCHK_ARRAY);

	// Get data
	$key = getPostValue('hidden');
	$id = getPostValue('');

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
	global $admin;
	global $vendor;

	// Get field data.
	$fieldData = generateFieldCheck(FIELDCHK_ARRAY);

	// Get data
	$studentid = getPostValue('studentid');
	$assignment = getPostValue('assignment');
	$course = getPostValue('course');
	$comment = getPostValue('comment');
	$grade = getPostValue('grade');

	// Check field data.
	$vfystr->fieldchk($fieldData, 0, $studentid);
	$vfystr->fieldchk($fieldData, 1, $assignment);
	$vfystr->fieldchk($fieldData, 2, $course);
	$vfystr->fieldchk($fieldData, 3, $comment);
	$vfystr->fieldchk($fieldData, 4, $grade);

	if (!empty($active)) $active = true; else $active = false;
	if ($vendor || $admin) $active = true;

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
	$studentid = safeEncodeString($studentid);
	$assignment = safeEncodeString($assignment);
	$course = safeEncodeString($course);
	$comment = safeEncodeString($comment);
	$grade = safeEncodeString($grade);
	
	// We are good, insert the record
	$result = $dbapp->insertGrades($studentid, $assignment, $course, $comment, $grade);		// XXX Set This
	if ($result == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database: Record insert failed. Key = ' . $studentid);
	}
	sendResponseClear($moduleDisplayUpper . ' insert completed: key = '
		. $studentid);
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

	// Gather data...
	$key = getPostValue('hidden');
	$id = getPostValue('');		// XXX Set This

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
	$result = $DATABASE_DELETE_OPERATION($key);		// XXX Set This
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
// XXX Requires customization
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
			$msg1 = 'Updating ' . $moduleDisplayUpper . ' Data For';
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

	// Generating fields to insert grades

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
			'studentid' => '',
			'assignment' => '',
			'course' => '',
			'comment' => '',
			'grade' => '',
		);
	}

	// XXX Custom field rendering code
	// Associating the grade table columns with the fields 
	$studentid = generateField(html::TYPE_TEXT, 'studentid', 'Student ID', 6, $rxa['studentid'],
		'The numeric user id which uniquely identifies the student.', $default, $key);
	$assignment = generateField(html::TYPE_TEXT, 'assignment', 'Assignment Number.', 4,
		$rxa['assignment'], 'The assignment number.', $default, $disable);
	$courseNum = generateField(html::TYPE_TEXT, 'course', 'Course Number.', 6,
		$rxa['course'], 'The course the assignment is associated with.',
		$default, $disable);
	$comment = generateField(html::TYPE_TEXT, 'comment', 'Comments', 1000,
		$rxa['comment'], 'Comments.', $default, $disable);
	$grade = generateField(html::TYPE_TEXT, 'grade', 'Grade', 3,
		$rxa['grade'], 'The grade.', $default, $disable);

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
		$studentid,
		$assignment,
		$courseNum,
		$comment,
		$grade,

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
			// This is the display name of the field.
			'dispname' => 'Student ID',
			// The internal name of the field.
			'name' => 'studentid',
			// The type of the field.
			'type' => $vfystr::STR_USERID,
			// If special handling of this field is required, then set type
			// to $vfystr::STR_CUSTOM and this field to the actual datatype.
			// Remove if this field is not needed.
			//'ctype' => $vfystr::STR_,
			// True if this field cannot be blank.  False otherwise.
			'noblank' => true,
			// Same as noblank above, except this is only for insert mode.
			// Use only if different from noblank.
			// Remove if this field is not needed.
			'noblankins' => true,
			// Maximum allowable field length (or numeric value).
			'max' => 1000,
			// Minimum allowable field length (or numeric value).
			// In all cases, if min > max, then no checking is done.
			'min' => 0,
		),
		1 => array(
			'dispname' => 'Assignment Number',
			'name' => 'assignment',
			'type' => $vfystr::STR_PINTEGER,
			'noblank' => true,
			'noblankins' => true,
			'max' => 10,
			'min' => 0,
		),
		2 => array(
			'dispname' => 'Course Number',
			'name' => 'course',
			'type' => $vfystr::STR_PINTEGER,
			'noblank' => true,
			'noblankins' => true,
			'max' => 100000,
			'min' => 0,
		),
		3 => array(
			'dispname' => 'Comments',
			'name' => 'comment',
			'type' => $vfystr::STR_DESC,
			'noblank' => true,
			'noblankins' => true,
			'max' => 1000,
			'min' => 0,
		),
		4 => array(
			'dispname' => 'Grade',
			'name' => 'grade',
			'type' => $vfystr::STR_PINTEGER,
			'noblank' => true,
			'noblankins' => true,
			'max' => 20,
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