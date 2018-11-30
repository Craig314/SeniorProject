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
require_once BASEDIR . 'timedate.php';
require_once BASEDIR . 'modhead.php';

$list = false;

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
			'funchide',
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
	
	global $list;

	//hideFuncBar();
	// Get data from database.

	//$list = false;
	$list = array(
		'type' => html::TYPE_RADTABLE,
		'name' => 'select_item',
		'clickset' => true,
		'condense' => true,
		'hover' => true,
		'titles' => array(
			'Course ID',
			'Class',
			'Section',
			'Name',
		),
		'tdata' => array(),
		'tooltip' => array(),
		'stage' => 1,
		'stagelast' => 4,
		'mode' => 2,
	);

	if($list['tdata'] == false) 
	{
		/*$list = array(
			'type' => html::TYPE_BOTB1,
			'data' => 'There are no ' . $moduleDisplayLower . '\'s in the database to query.',
		);*/
		showStage1();
	}
}

// Called when the initial command processor doesn't have the
// command number. This call comes from modhead.php.
function commandProcessor($commandId)
{
	global $moduleLoad;
	global $ajax;

	global $list;

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
		case 10:
			$list['stage'] = 1;
			showStage1();
			break;
		case 11:
			$list['stage'] = 2;
			showStage2();
			break;
		case 12:
			$list['stage'] = 3;
			showStage3();
			break;
		case 13:
			$list['stage'] = 4;
			showStage4();
			break;
		case 90:		// Load Module
			$moduleLoad->loadModule();
			break;
		case 91:
			$list['stage'] = 2;
			showStage2();
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

function showStage1() {
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

	global $list;

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
	//Check if instructor
	else if ($dbapp->queryCourseInstructAll($_SESSION['userId']) == true)
	{
		$rxa = $dbapp->queryCourseInstructAll($_SESSION['userId']);
		if ($rxa == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage());
			else
				handleError('There are no ' . $moduleDisplayLower . '\'s in the database to query.');
		}
		foreach ($rxa as $kxa => $vxa)
		{
			$tdata = array(
				$vxa['courseid'],
				$vxa['courseid'],
				$vxa['class'],
				$vxa['section'],
				$vxa['name'],
			);
			array_push($list['tdata'], $tdata);
		}
	}
	//Check if student
	else if ($dbapp->queryStudentclassStudentAll($_SESSION['userId']) == true) {
		$rxa = $dbapp->queryCourseStudentAll($_SESSION['userId']);
	}
	//Else nothing to query
	else {
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('There are no ' . $moduleDisplayLower . '\'s in the database to query.');
	}

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
			'name' => 'Your Courses'
		),
		$list,

		//End of custom data

		array('type' => html::TYPE_FORMCLOSE),
		array('type' => html::TYPE_WDCLOSE),
		array('type' => html::TYPE_BOTB1)
	);

	// Get panel content
	//$navContent = $panels->getLinks();
	//$statusContent = $panels->getStatus();
	$mainContent = html::pageAutoGenerate($data);
	//$testContent = html::insertRadioButtons($data);

	echo $mainContent;
}

function showStage2()
{
	global $dbapp;
	global $list;

	$key = getPostValue('select_item');
	$_SESSION['courseid'] = $key;

	$rxa = $dbapp->queryAssignmentCourseAll($key);

	$list = array(
		'type' => html::TYPE_RADTABLE,
		'name' => 'select_item',
		'clickset' => true,
		'condense' => true,
		'hover' => true,
		'titles' => array(
			'Assign ID',
			'Name',
			'Due Date',
			'Max Points',
		),
		'tdata' => array(),
		'tooltip' => array(),
		'stage' => 2,
		'stagelast' => 4,
		'mode' => 2,
	);


	foreach ($rxa as $kxa => $vxa)
	{
		$tdata = array(
			$vxa['assignment'],
			$vxa['assignment'],
			$vxa['name'],
			timedate::unix2canonical($vxa['duedate']),
			$vxa['points'],
		);
		array_push($list['tdata'], $tdata);
	}


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
			'name' => 'Graded Assignments'
		),
		$list,

		//End of custom data

		array('type' => html::TYPE_FORMCLOSE),
		array('type' => html::TYPE_WDCLOSE),
		array('type' => html::TYPE_BOTB1)
	);
	// Get panel content
	//$navContent = $panels->getLinks();
	//$statusContent = $panels->getStatus();
	$mainContent = html::pageAutoGenerate($data);
	//$testContent = html::insertRadioButtons($data);

	echo $mainContent;
	//exit(0);
	
	// Queue content in ajax transmit buffer.
	/*$ajax->loadQueueCommand(ajaxClass::CMD_WMAINPANEL, $mainContent);
	$ajax->loadQueueCommand(ajaxClass::CMD_WNAVPANEL, $navContent);
	$ajax->loadQueueCommand(ajaxClass::CMD_WSTATPANEL, $statusContent);
	
	//Don't need this if using loadQueueCommand
	
	//$ajax->writePanelsImmediate($navContent, $statusContent, $mainContent);
	
	// Render
	$ajax->sendQueue();*/

	// Render
	//echo html::pageAutoGenerate($data);
	//exit(0);

}

function showStage3() {
	global $dbapp;
	global $list;

	$assignment = getPostValue('select_item');
	$course = $_SESSION['courseid'];

	//var_dump($course);
	#$rxa = $dbapp->queryGradesAssign($key);
	$rxa = $dbapp->queryGradesInstructAssign($course, $assignment);
	//var_dump($rxa);

	$list = array(
		'type' => html::TYPE_RADTABLE,
		'name' => 'select_item',
		'clickset' => true,
		'condense' => true,
		'hover' => true,
		'titles' => array(
			'StudentID',
			'Assignment',
			'Comments',
			'Grade',
		),
		'tdata' => array(),
		'tooltip' => array(),
		'stage' => 3,
		'stagelast' => 4,
		'mode' => 0,
	);

	foreach ($rxa as $kxa => $vxa)
	{
		$tdata = array(
			$vxa['studentid'],
			$vxa['studentid'],
			$vxa['assignment'],
			$vxa['comment'],
			$vxa['grade'],
		);
		array_push($list['tdata'], $tdata);
	}

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
			'name' => 'List of Students\' Grades'
		),
		$list,

		//End of custom data

		array('type' => html::TYPE_FORMCLOSE),
		array('type' => html::TYPE_WDCLOSE),
		array('type' => html::TYPE_BOTB1)
	);

	// Get panel content
	//$navContent = $panels->getLinks();
	//$statusContent = $panels->getStatus();
	$mainContent = html::pageAutoGenerate($data);
	//$testContent = html::insertRadioButtons($data);

	echo $mainContent;
}

function showStage4() {

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
	$rxa = $dbapp->queryAssignment($key);		// XXX Set This
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
	global $dbapp;
	global $herr;

	global $dbuser;

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
			$hideValue = $rxa['courseid'];
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

	$data = array();
	
	switch($mode)
	{
		case MODE_INSERT:
			// Load $rxa with dummy values for insert mode.
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
			break;
		case MODE_VIEW:
			// Retrieve additional information from the database.
			$rxb = $dbapp->queryCourse($rxa['courseid']);
			if ($rxb == false)
			{
				if ($herr->checkState())
					handleError($herr->errorGetMessage());
				else
					handleError('Database Error: Unable to retrieve course information.');
			}
			
			$rxe = $dbapp->queryStudentclass($_SESSION['userId'], $rxa['courseid']);
			if ($rxe == false)
			{
				if ($herr->checkState())
					handleError($herr->errorGetMessage());
				//if ($_SESSION['userId'] != $rxb[''])
				//	handleError('Security Violation: You are not enrolled in the requested course.');
			}
			
			$rxc = $dbuser->queryContact($rxb['instructor']);
			if ($rxc == false)
			{
				if ($herr->checkState())
					handleError($herr->errorGetMessage());
				else
					handleError('Database Error: Unable to retrieve instructor information.');
			}
			
			$rxd = $dbapp->queryAssignstepAssignAll($rxa['assignment']);
			if ($rxd == false)
			{
				if ($herr->checkState())
					handleError($herr->errorGetMessage());
			}
			
			$rxf = $dbapp->queryTurninStudentAssignAll($_SESSION['userId'], $rxa['assignment']);
			if ($rxf == false)
			{
				if ($herr->checkState())
					handleError($herr->errorGetMessage());
				$turnedin = 'No';
			}
			else $turnedin = 'Yes';
			
			$rxg = $dbapp->queryGradesAssign($_SESSION['userId'], $rxa['assignment']);
			if ($rxg == false)
			{
				if ($herr->checkState())
					handleError($herr->errorGetMessage());
				$graded = false;
			}
			else $graded = true;

			// XXX Custom field rendering code
			// Course
			$course = generateField(html::TYPE_TEXT, '', 'Course Number', 2,
				$rxb['courseid'], 'The course ID number that is assigned by the institution.',
				$default, $disable);
			$class = generateField(html::TYPE_TEXT, '', 'Class', 3, $rxb['class'],
				'The class designation that is assigned by the institution.',
				$default, $disable);
			$section = generateField(html::TYPE_TEXT, '', 'Section', 2,
				$rxb['section'], 'The course section number.', $default, $disable);
			$name = generateField(html::TYPE_TEXT, '', 'Course Name', 4,
				$rxb['name'], 'The name of the course.', $default, $disable);
			$instruct = generateField(html::TYPE_TEXT, '', 'Instructor Name',
				4, $rxc['name'], 'The name of the course instructor.', $default,
				$disable);

			// Assignment
			$aname = generateField(html::TYPE_TEXT, '', 'Name', 4,
				$rxa['name'], 'The name of the assignment', $default, $disable);
			$adesc = generateField(html::TYPE_AREA, '', 'Description', 6,
				$rxa['desc'], 'The description of the assignment.', $default,
				$disable);
			$adesc['rows'] = 6;
			$adue = generateField(html::TYPE_TEXT, '', 'Due Date/Time', 3,
				timedate::unix2canonical($rxa['duedate']),
				'The date and time the assignment is due.', $default, $disable);
			$apoints = generateField(html::TYPE_TEXT, '', 'Points', 2,
				$rxa['points'], 'The number of points the assignment is worth.',
				$default, $disable);
			$aexempt = generateField(html::TYPE_TEXT, '', 'Exempt', 2,
				convBooleanValue($rxa['exempt']),
				'Indicates if the assignment is exempt from grading.',
				$default, $disable);
			$turnin = generateField(html::TYPE_TEXT, '', 'Turned In', 2,
				$turnedin, 'Indicates if the assignment was turned in.',
				$default, $disable);
			if ($graded == false) $grade = NULL;
			else
			{
				$grade = generateField(html::TYPE_TEXT, '', 'Grade', 2, $rxg['grade'],
				'The grade that has been awarded for the assignment', $default, $disable);
			}

			// Assignment Steps
			$data2 = array();
			if (is_array($rxd))
			{
				$fsetclose = array('type' => html::TYPE_FSETCLOSE);
				foreach($rxd as $kx => $vx)
				{
					$astep = generateField(html::TYPE_TEXT, '', 'Step', 2, $vx['step'],
						'The assignment step number.', $default, $disable);
					$asdate = generateField(html::TYPE_TEXT, '', 'Date', 3,
						timedate::unix2canonical($vx['date']),
						'The time/date that this step should be completed by.',
						$default, $disable);
					if (!empty($vx['desc']))
					{
						$asdesc = generateField(html::TYPE_AREA, '', 'Description', 6,
							$vx['desc'], 'The description of this step.', $default,
							$disable);
						$asdesc['rows'] = 6;
					}
					else
						$asdesc = NULL;
					$fsetopen = array(
						'type' => html::TYPE_FSETOPEN,
						'name' => 'Assignment Step ' . $vx['step'],
					);
					array_push($data2, $fsetopen, $astep, $asdate, $asdesc, $fsetclose);
				}
			}

			// Build out the form array.
			$data1 = array(
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
				array(
					'type' => html::TYPE_FSETOPEN,
					'name' => 'Course Information',
				),
				$course,
				$class,
				$section,
				$name,
				$instruct,
				array('type' => html::TYPE_FSETCLOSE),
				array(
					'type' => html::TYPE_FSETOPEN,
					'name' => 'Assignment Information',
				),
				$aname,
				$adesc,
				$adue,
				$apoints,
				$aexempt,
				$turnin,
				$grade,
				array('type' => html::TYPE_FSETCLOSE),
			);

			// XXX Enter custom field data here.

			$data3 = array(
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

			// Merge Arrays
			$data = array_merge($data1, $data2, $data3);
					break;

		default:
			handleError('Internal Error: Contact your administrator.  XX82747');
			break;
	}
	// Render
	$ajax->writeMainPanelImmediate(html::pageAutoGenerate($data),
		generateFieldCheck($mode));
}

// Generate the field definitions for client side error checking.
// Use $mode passed in from formpage to check whether doing an insert,
// view, update, or delete.
function generateFieldCheck($mode, $returnType = 0)
{
	global $CONFIGVAR;
	global $vfystr;

	$data = array();

	switch($mode) 
	{
		case MODE_INSERT:
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
			break;
		case MODE_VIEW:
			break;
		default:
			break;
	}
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