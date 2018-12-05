<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application Enrollment Module

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
$moduleFilename = 'enrollment.php';		// XXX Set This

// The name of the module.  It shows in the title bar of the web
// browser and other places.
$moduleTitle = 'Class Entrollment';		// XXX Set This

// $moduleId must be a unique positive integer. Module IDs < 1000 are
// reserved for system use.  Therefore application module IDs will
// start at 1000.
$moduleId = 1102;		// XXX Set This

// The capitalized short display name of the module.  This shows up
// on buttons, and some error messages.
$moduleDisplayUpper = 'Enrollment';		// XXX Set This

// The lowercase short display name of the module.  This shows up in
// various messages.
$moduleDisplayLower = 'enrollment';		// XXX Set This

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
	global $panels;
	global $ajax;
	global $dbapp;

	// Get data from database.
	$rxa = $dbapp->queryCourseAll();
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
		'mode' => 2,
		'condense' => true,
		'hover' => true,
		'titles' => array(
			// Add column titles here
			'Course ID',
			'Class',
			'Section',
			'Name',
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
			$vx['courseid'],
			$vx['courseid'],
			$vx['class'],
			$vx['section'],
			$vx['name'],
		);
		$tooltip  = 'Course: ' . $vx['courseid'] . '<br>';
		$tooltip .= 'Class: ' . $vx['class'] . '<br>';
		$tooltip .= 'Section: ' . $vx['section'] . '<br>';
		$tooltip .= 'Name: ' . $vx['name'] . '<br>';
		array_push($list['tdata'], $tdata);
		array_push($list['tooltip'], $tooltip);
	}

	// Generate rest of page.
	$data = array(
		array(
			'type' => html::TYPE_HEADING,
			'message1' => 'Class Enrollment',
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

	exit(0);}

// Called when the initial command processor doesn't have the
// command number. This call comes from modhead.php.
function commandProcessor($commandId)
{
	global $ajax;
	global $moduleLoad;

	switch ((int)$commandId)
	{
		case 5:		// Submit Update
			updateRecordAction();
			break;
		case 90:	// Load Module
			$moduleLoad->loadModule();
			break;
		case 91:	// Next Stage
			listStudents();
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

// Lists all students and lists the ones that are enrolled in the course.
function listStudents($key = NULL)
{
	global $CONFIGVAR;
	global $dbapp;
	global $herr;
	global $ajax;

	// Gather data
	if ($key == NULL)
	{
		$course = getPostValue('select_item');
		if ($course == NULL)
			handleError('Missing ' . $moduleDisplayLower . ' selection data.');
		if (!is_numeric($course))
			handleError('Malformed key sequence.');
	}
	else $course = $key;
	
	// Query course information to make sure it's still there.
	$rxa = $dbapp->queryCourse($course);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage);
		else
			handleError('Database Error: Failed to retrieve course information');
	}

	// Gather student information.
	$rxu = getUsersByProfile($CONFIGVAR['app_profile_student']['value']);
	$rxe = $dbapp->queryStudentclassCourseAll($course);
	if ($rxe == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage);
	}

	// Process student enrollments
	$enrolled = array();
	if (is_array($rxe))
	{
		foreach ($rxe as $kx => $vx)
		{
			$enrolled[$vx['studentid']] = 1;
		}
	}

	// Generate Selection Table.
	$list = array(
		'type' => html::TYPE_RADTABLE,
		'name' => 'select_item',
		'mode' => 1,
		'clickset' => true,
		'condense' => true,
		'hover' => true,
		'titles' => array(
			// Add column titles here
			'Student ID',
			'Name',
		),
		'tdata' => array(),
		'tooltip' => array(),
		'default' => $enrolled,
	);
	foreach ($rxu as $kx => $vx)
	{
		$tdata = array(
			// These are the values that show up under the columns above.
			// The *FIRST* value is the value that is sent when a row
			// is selected.  AKA Key Field.
			$vx,
			$vx,
			$kx,
		);
		array_push($list['tdata'], $tdata);
	}

	// Generate rest of page.
	$msg2 = $rxa['courseid'] . ' ' . $rxa['class'] . '-' . $rxa['section']
		. ': ' . $rxa['name'];
	$data = array(
		array(
			'type' => html::TYPE_HIDE,
			'fname'=> 'hiddenForm',
			'name' => 'hidden',
			'data' => $course,
		),
		array(
			'type' => html::TYPE_HEADING,
			'message1' => 'Class Enrollment Student Selection<br>',
			'message2' => $msg2,
			'warning' => 'Removing a student from a course will also remove<br>' .
				'all of their submitted work in that class.',
		),
		array('type' => html::TYPE_TOPB1),
		array('type' => html::TYPE_WD75OPEN),
		array(
			'type' => html::TYPE_FORMOPEN,
			'name' => 'select_table',
		),

		// Enter custom data here.
		$list,

		array(
			'type' => html::TYPE_ACTBTN,
			'dispname' => 'Submit Enrollment Data',
			'btnset' => html::BTNTYP_UPDATE,
			'action' => 'submitUpdate()',
		),
		array('type' => html::TYPE_FORMCLOSE),
		array('type' => html::TYPE_WDCLOSE),
		array('type' => html::TYPE_BOTB1)
	);

	// Get panel content
	$mainContent = html::pageAutoGenerate($data);

	// Render
	$ajax->writeMainPanelImmediate($mainContent, NULL);

	exit(0);

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

	// Get and check key value (course id).
	$key = getPostValue('hidden');
	if ($key == NULL)
		handleError('Missing ' . $moduleDisplayLower . ' selection data.');
	if (!is_numeric($key))
		handleError('Malformed key sequence.');

	// Query course information to make sure it's still there.
	$rxa = $dbapp->queryCourse($key);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage);
		else
			handleError('Database Error: Failed to retrieve course information');
	}

	// Query all students in the course
	$rxb = $dbapp->queryStudentclassCourseAll($key);
	if ($rxb == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage);
	}

	// Process student enrollments
	$dbenrolled = array();
	if (is_array($rxb))
	{
		foreach ($rxb as $kx => $vx)
		{
			$dbenrolled[$vx['studentid']] = 1;
		}
	}

	// Process post information
	$str = 'select_item';
	$delim = '_';
	$length = strlen($str);
	$postenrolled = array();
	foreach ($_POST as $kx => $vx)
	{
		if (substr_compare($kx, $str, 0, $length, true) == 0)
		{
			$stx = explode($delim, $kx);
			$pos = count($stx) - 1;
			$student = $stx[$pos];
			if (!is_numeric($student)) continue;
			$postenrolled[$student] = 1;
		}
	}

	// There are two conditions that we have to consider.
	// 1. The student is to be added to a class.
	// 2. The student is to be removed from a class.
	// The other two don't matter because they don't change
	// the database.
	$insert = array_diff_key($postenrolled, $dbenrolled);
	$delete = array_diff_key($dbenrolled, $postenrolled);

	// We have everything that we need, so update the database.
	// First we insert new students.
	if (is_array($insert))
	{
		if (count($insert) > 0)
		{
			foreach ($insert as $kx => $vx)
			{
				$result = $dbapp->insertStudentclass($kx, $key);
				if ($result == false)
				{
					if ($herr->checkState())
						handleError($herr->errorGetMessage());
					else
						handleError('Database: Record insert failed. Key = ' . $kx);
				}
			}
		}
	}

	// Then we remove students from the course.
	if (is_array($delete))
	{
		if (count($delete) > 0)
		{
			foreach ($delete as $kx => $vx)
			{
				var_dump($kx);
				$result = $dbapp->metaDeleteStudentCourse($kx, $key);
				if ($result == false)
				{
					if ($herr->checkState())
						handleError($herr->errorGetMessage());
					else
						handleError('Database: Record delete failed. Key = ' . $kx);
				}
			}
		}
	}

	sendResponse('Student enrollment processing completed.');
	exit(0);
}



?>