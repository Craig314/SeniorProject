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
$functionFilename = 'func.assignments.php';			// XXX Set This

// The capitalized short display name of the function.  This shows up
// on buttons, and some error messages.
$functionDisplayUpper = 'Assignment';		// XXX Set This

// The lowercase short display name of the function.  This shows up in
// various messages.
$functionDisplayLower = 'assignment';		// XXX Set This

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

// Order matters here.  The funchead library needs to be loaded last.
// If additional libraries are needed, then load them before.
// Freeform execute stops at funchead.php
const BASEDIR = '../libs/';
const BASEAPP = '../applibs/';
require_once BASEAPP . 'dbaseapp.php';
require_once BASEDIR . 'dbaseuser.php';
require_once BASEDIR . 'timedate.php';
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
				'The command ' . $commandId . 
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
		case 25:
			performDataAction();
			break;
		default:
			// If we get here, then the command is undefined.
			$ajax->sendCode(ajaxClass::CODE_NOTIMP,
				'The command ' . $commandId .
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
	$key = GetPostValue('assignment');

	// Read from database.
	$rxa = databaseLoad($key);

	// Render page.
	// XXX Change VIEW_MODE to a different mode if needed.
	formPage(MODE_VIEW, $rxa);	
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
	$rxa = $dbapp->queryAssignment($key);
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

// Generate generic form page and render data to it.
// XXX: Requires customization
function formPage($mode, $rxa)
{
	global $CONFIGVAR;
	global $functionDisplayUpper;
	global $functionDisplayLower;
	global $ajax;
	global $herr;
	global $dbapp;
	global $dbuser;
	global $dbconf;

	// Determine the editing mode.
	switch($mode)
	{
		case MODE_VIEW:			// View
			$msg1 = 'Viewing ' . $functionDisplayUpper . ' Data For';
			$msg2 = $rxa['name'];
			$warn = '';
			$btnset = html::BTNTYP_VIEW;
			$action = '';
			$hideValue = '';
			$disable = true;
			$default = true;
			$key = true;
			break;
		default:
			handleError('Internal Error: Contact your administrator.  XX82747');
			break;
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
			if ($vx['turninreq'] != 0)
			{
				$ascomp = generateField(html::TYPE_CHECK, '', 'Completed', 1,
					$vx['complete'], 'Indicates if this step has been turned in.',
					$default, $disable);
				$formcode = 'A' . $vx['assignment'] . 'S' . $vx['step'];
				$asupload = array(
					'type' => html::TYPE_FILE,
					'bname' => 'fileSubmit_' . $formcode,
					'fname' => 'fileInputForm_' . $formcode,
					'name' => 'fileInput_' . $formcode,
					'action' => 'fileUpload(' . $vx['assignment'] . ', ' . $vx['step'] . ')',
					'fsize' => 8,
				);
			}
			else
			{
				$ascomp = NULL;
				$asupload = NULL;
			}
			array_push($data2, $fsetopen, $astep, $asdate, $asdesc, $ascomp,
				$asupload, $fsetclose);
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
			'dispname' => $functionDisplayUpper,
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
	// XXX: Choose One
	$ajax->writeMainPanelImmediate(html::pageAutoGenerate($data), NULL);
}


?>