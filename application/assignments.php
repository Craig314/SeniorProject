<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application Module Student Assignments

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
$moduleFilename = 'assignments.php';		// XXX Set This

// The name of the module.  It shows in the title bar of the web
// browser and other places.
$moduleTitle = 'Assignments';		// XXX Set This

// $moduleId must be a unique positive integer. Module IDs < 1000 are
// reserved for system use.  Therefore application module IDs will
// start at 1000.
$moduleId = 1600;		// XXX Set This

// The capitalized short display name of the module.  This shows up
// on buttons, and some error messages.
$moduleDisplayUpper = 'Assignment';		// XXX Set This

// The lowercase short display name of the module.  This shows up in
// various messages.
$moduleDisplayLower = 'assignment';		// XXX Set This

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
require_once BASEDIR . 'dbaseuser.php';
require_once BASEDIR . 'timedate.php';
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
			'View' => 'viewDataItem',
			'List' => 'listDataItems',
		);

		// jsfiles is an associtive array which contains additional
		// JavaScript filenames that should be included in the head
		// section of the HTML page.
		$jsFiles = array(
			'/js/baseline/common.js',
			'/js/baseline/function.js',
			'/js/module/portal.js',
			'/js/application/landing.js',
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
	global $panels;
	global $ajax;
	global $herr;
	global $dbapp;
	global $dbuser;

	// Get data from database.
	$rxa = $dbapp->queryStudentclassStudentAll($_SESSION['userId']);	// XXX Set This
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessages());
		else
			handleError('You are not enrolled in any courses.');
	}

	// We step through each course the student has and generate a separate
	// list of assignments for each course.
	$data2 = array();
	$fsetclose = array('type' => html::TYPE_FSETCLOSE);

	foreach ($rxa as $kxa => $vxa)
	{
		$rxb = $dbapp->queryCourse($vxa['courseid']);
		if ($rxb == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessages());
			continue;
		}
		$rxc = $dbuser->queryContact($rxb['instructor']);
		if ($rxc == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessages());
			else
				handleError('Database Error: Missing contact record for instructor.');
		}
		$rxd = $dbapp->queryAssignmentCourseAll($vxa['courseid']);

		// Build field set header
		$fsetxt = $rxb['courseid'] . ': ' . $rxb['class'] . '-' . $rxb['section']
			. ' ' . $rxb['name'] . '<br>' . 'Instructor: ' . $rxc['name'];
		$fsetopen = array(
			'type' => html::TYPE_FSETOPEN,
			'name' => $fsetxt,
		);

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
				'Due Date',
			),
			'tdata' => array(),
			'tooltip' => array(),
		);
		foreach ($rxd as $kxb => $vxb)
		{
			$tdata = array(
				// These are the values that show up under the columns above.
				// The *FIRST* value is the value that is sent when a row
				// is selected.  AKA Key Field.
				$vxb['assignment'],
				$vxb['name'],
				timedate::unix2canonical($vxb['duedate']),
			);
			array_push($list['tdata'], $tdata);
			//array_push($list['tooltip'], $vx['description']);
		}

		// Add onto data array.
		array_push($data2, $fsetopen, $list, $fsetclose);
	}

	// Generate rest of page.
	$data1 = array(
		array(
			'type' => html::TYPE_HEADING,
			'message1' => 'Assignments',
		),
		array('type' => html::TYPE_TOPB1),
		array('type' => html::TYPE_WD75OPEN),
		array(
			'type' => html::TYPE_FORMOPEN,
			'name' => 'select_table',
		),
	);
	$data3 = array(	
		array('type' => html::TYPE_FORMCLOSE),
		array('type' => html::TYPE_WDCLOSE),
		array('type' => html::TYPE_BOTB2)
	);
	$data = array_merge($data1, $data2, $data3);

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
		handleError('You must select an ' . $moduleDisplayLower . ' from the list view.');
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

// Generate generic form page.
// XXX Requires customization
function formPage($mode, $rxa)
{
	global $CONFIGVAR;
	global $moduleDisplayUpper;
	global $moduleDisplayLower;
	global $ajax;
	global $herr;
	global $dbapp;
	global $dbuser;
	global $dbconf;

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
		if ($_SESSION['userId'] != $rxb[''])
			handleError('Security Violation: You are not enrolled in the requested course.');
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

	// Render
	$ajax->writeMainPanelImmediate(html::pageAutoGenerate($data), NULL);
}

// Generate the field definitions for client side error checking.
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