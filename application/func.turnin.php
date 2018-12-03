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
$functionFilename = 'func.turnin.php';			// XXX Set This

// The capitalized short display name of the function.  This shows up
// on buttons, and some error messages.
$functionDisplayUpper = 'Turn In';		// XXX Set This

// The lowercase short display name of the function.  This shows up in
// various messages.
$functionDisplayLower = 'turn in';		// XXX Set This

// Single flag number that defines if the profile that the user
// is assigned to has permission to access this function or not.
// -1 means that all users have access.
$functionPermission = -1;		// XXX Set this

// Indicates if this function is part of the system core or the
// application.  It is very important that this gets set correctly.
$functionSystem = false;


// Order matters here.  The modhead library needs to be loaded last.
// If additional libraries are needed, then load them before.
// Freeform execute stops at modhead.php
const BASEDIR = '../libs/';
const BASEAPP = '../applibs/';
require_once BASEAPP . '../applibs/dbaseapp.php';
require_once BASEDIR . '../libs/files.php';
require_once BASEDIR . '../libs/funchead.php';

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
		case 132:
			performDataAction();
			break;
		default:
			// If we get here, then the command is undefined.
			$ajax->sendCode(ajaxClass::CODE_NOTIMP,
				'The command ' . $_POST['COMMAND'] .
				' has not been implemented');
			exit(1);
			break;
	}
	exit;
}

// Aways called on a HTTP PUT method request.
// Do not remove
// XXX: Requires customization.
function commandProcessorPut($commandId)
{
	global $ajax;

	switch ((int)$commandId)
	{
		case 30:
			fileUpload();
			break;
		default:
			// If we get here, then the command is undefined.
			$ajax->sendCode(ajaxClass::CODE_NOTIMP,
				'The command ' . $_POST['COMMAND'] .
				' has not been implemented');
			exit(1);
			break;
	}
	exit;
}

// Recieves the file(s) from the client.
function fileUpload()
{
	global $files;
	global $herr;
	global $CONFIGVAR;
	global $dbapp;
	global $ajax;

	// Setup
	$basePath = $CONFIGVAR['files_base_path']['value'] . '/' .
		$CONFIGVAR['files_turned_in']['value'];
	$maxSize = $CONFIGVAR['files_max_upload_size']['value'];
	$extensions = $CONFIGVAR['files_allowed_extensions']['value'];
	$extList = explode(' ', $extensions);

	// Upload
	$filelist = $files->fileUpload($basePath, '/', $maxSize, true);

	// Verify that the extension of the file(s) are allowed.
	if (is_array($filelist))
	{
		foreach($filelist as $kx => $vx)
		{
			// Get the file extension
			$filename = basename($kx);
			$component = explode('.', $filename);
			if (!is_array($component)) continue;
			if (count($component) == 0) continue;
			$ext = $component[count($component) - 1];

			// Filter through the extension list.  If it's not
			// on the list, then add to the error message.
			$eflag = true;
			foreach($extList as $kxa)
			{
				if (strcasecmp($ext, $kxa) == 0)
				{
					$eflag = false;
					break;
				}
			}
			if ($eflag)
			{
				// Not on the list, so delete the file.
				$herr->puterrmsg('The file ' . $filename .
					' does not have a valid extension. File has been removed.');
				unlink($basePath . '/' . $vx);
				$filelist[$kx] = NULL;
			}
		}
	}
	else
	{
		handleError('Upload Error: No files received.');
	}

	// Parse the parameter block in the request header.
	$params = $_SERVER['HTTP_X_PARAMETER'];
	parse_str($params, $_POST);
	$student = $_SESSION['userId'];
	$assign = getPostValue('assignment');
	$step = getPostValue('step');

	// Get the assignment information
	$rxa = $dbapp->queryAssignment($assign);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Unable to retrieve assignment information.');
	}

	// Write the turnin data into the database.
	$result = $dbapp->insertTurnin($student, $assign, $step, $rxa['courseid'], time());
	if ($result == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Unable to insert turnin information.');
	}
	$rxa = $dbapp->queryTurninStudentAssignAll($student, $assign);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleEerror('Database Error: Unable to retrieve turnin information.');
	}

	$maxCount = -1;
	foreach($rxa as $kx => $vx)
	{
		if ($vx['subcount'] > $maxCount) $maxCount = $vx['subcount'];
	}

	foreach($filelist as $kx => $vx)
	{
		$result = $dbapp->insertFilename($student, $assign, $step,
			$maxCount, $kx, $vx);
		if ($result == false)
		{
			$dbapp->deleteFilenameCount($student, $assign, $step, $maxCount);
			fileError($basePath, $filelist);
			if ($herr->checkState())
				handleError($herr->errorGetMessage());
			else
				handleEerror('Database Error: Unable to save file information.');
		}
	}

	// Refresh page
	$mainContent = formPage($assign, $step);
	$ajax->loadQueueCommand(ajaxClass::CMD_OKDISP,
		'Assignment has been submitted successfully.');
	$ajax->loadQueueCommand(ajaxClass::CMD_WMAINPANEL, $mainContent);
	$ajax->sendQueue();
}

// This is called when a database error occurrs in fileUpload.
// Removes all the files that were uploaded and were not already
// removed.
function fileError($basePath, $filelist)
{
	foreach($filelist as $kx => $vx)
	{
		if ($vx != NULL)
		{
			unlink($basePath . '/' . $vx);
		}
	}
}

// Performs the primary action for this function
// XXX: Requires customization.
function performDataAction()
{
	global $ajax;

	// XXX Set one and delete the other.  This directly ties into
	// what HTTP method is being used: POST or GET.
	$key = getPostValue('assignment');
	$step = getPostValue('assignstep');

	// Render page.
	$html = formPage($key, $step);	
	$ajax->sendCommand(ajaxClass::CMD_WMAINPANEL, $html);
}

// Generate generic form page and render data to it.
// XXX: Requires customization
function formPage($key, $step)
{
	global $CONFIGVAR;
	global $functionDisplayUpper;
	global $functionDisplayLower;
	global $ajax;
	global $herr;
	global $dbapp;
	global $dbuser;
	global $dbconf;

	$default = true;
	$disable = true;

	// Assignment
	$rxb = $dbapp->queryAssignment($key);
	if ($rxb == false)
	{
		if ($herr->checkState())
		{
			handleError($herr->errorGetMessage());
		}
		else
		{
			handleError('Database Error: Stored Data Conflict: Specified ' .
				'assignment does not exist.<br>Contact your administrator.' .
				'<br>ASSIGN_ID=' . $key);
		}
	}

	// Assignment Step
	if ($step > 0)
	{
		$rxc = $dbapp->queryAssignstep($key, $step);
		if ($rxc == false)
		{
			if ($herr->checkState())
			{
				handleError($herr->errorGetMessage());
			}
			else
			{
				handleError('Database Error: Stored Data Conflict: Assignment ' .
					'step does not exist.<br>Contact your administrator.' .
					'<br>ASSIGN=' . $key . ' STEP=' . $step);
			}
		}
	}
	else $rxc = NULL;
	
	// Course
	$rxd = $dbapp->queryCourse($rxb['courseid']);
	if ($rxd == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Stored Data Conflict: Specified ' .
				'course does not exist.<br>Contact your administrator.' .
				'<br>COURSE_ID=' . $rxb['courseid']);
	}

	// Turnin Data
	$rxe = $dbapp->queryTurninStudentAssignAll($_SESSION['userId'], $key);
	if ($rxe == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
	}
	
	// Close Field Set
	$fsetclose = array('type' => html::TYPE_FSETCLOSE);

	// Course
	$course_fs = array(
		'type' => html::TYPE_FSETOPEN,
		'name' => 'Course Information',
	);
	$course = generateField(html::TYPE_TEXT, '', 'Course Number', 2,
		$rxd['courseid'], 'The course ID number that is assigned by the institution.',
		$default, $disable);
	$class = generateField(html::TYPE_TEXT, '', 'Class', 3, $rxd['class'],
		'The class designation that is assigned by the institution.',
		$default, $disable);
	$section = generateField(html::TYPE_TEXT, '', 'Section', 2,
		$rxd['section'], 'The course section number.', $default, $disable);
	$name = generateField(html::TYPE_TEXT, '', 'Course Name', 4,
		$rxd['name'], 'The name of the course.', $default, $disable);
	$data2 = array(
		$course_fs,
		$course,
		$class,
		$section,
		$name,
		$fsetclose,
	);

	// Assignment
	$assign_fs = array(
		'type' => html::TYPE_FSETOPEN,
		'name' => 'Assignment Information',
	);
	$aname = generateField(html::TYPE_TEXT, '', 'Name', 4,
		$rxb['name'], 'The name of the assignment', $default, $disable);
	if (!empty($rxb['description']))
	{
		$adesc = generateField(html::TYPE_AREA, '', 'Description', 6,
			$rxb['description'], 'The description of the assignment.', $default,
			$disable);
	}
	else $adesc = NULL;
	$adesc['rows'] = 6;
	$adue = generateField(html::TYPE_TEXT, '', 'Due Date/Time', 3,
		timedate::unix2canonical($rxb['duedate']),
		'The date and time the assignment is due.', $default, $disable);
	$apoints = generateField(html::TYPE_TEXT, '', 'Points', 2,
		$rxb['points'], 'The number of points the assignment is worth.',
		$default, $disable);
	$aexempt = generateField(html::TYPE_TEXT, '', 'Exempt', 2,
		convBooleanValue($rxb['exempt']),
		'Indicates if the assignment is exempt from grading.',
		$default, $disable);
	$data3 = array(
		$assign_fs,
		$aname,
		$adesc,
		$adue,
		$apoints,
		$aexempt,
		$fsetclose,
	);

	// Assignment Step
	if (is_array($rxc))
	{
		$assignstep_fs = array(
			'type' => html::TYPE_FSETOPEN,
			'name' => 'Assignment Step Information',
		);
		$astep = generateField(html::TYPE_TEXT, '', 'Step', 2, $rxc['step'],
			'The assignment step number.', $default, $disable);
		$asdate = generateField(html::TYPE_TEXT, '', 'Date', 3,
			timedate::unix2canonical($rxc['date']),
			'The time/date that this step should be completed by.',
			$default, $disable);
		if (!empty($rxc['description']))
		{
			$asdesc = generateField(html::TYPE_AREA, '', 'Description', 6,
				$rxc['description'], 'The description of this step.', $default,
				$disable);
			$asdesc['rows'] = 6;
		}
		else
			$asdesc = NULL;
		$data4 = array(
			$assignstep_fs,
			$astep,
			$asdate,
			$asdesc,
			$fsetclose,
		);
	}
	else $data4 = array();

	// File submissions
	$message = '';
	$data5 = array();
	if (is_array($rxe))
	{
		$currSub = 0;
		foreach($rxe as $kx => $vx)
		{
			if ($vx['step'] != $step) continue;
			$rxf = $dbapp->queryFilenameSubmitAll($_SESSION['userId'], $key, $step,
				$vx['subcount']);
			if ($rxf == false)
			{
				if ($herr->checkState())
					handleError($herr->getErrorMessage());
			}
			if (is_array($rxf))
			{
				$file_fs = array(
					'type' => html::TYPE_FSETOPEN,
					'name' => 'Submission Number ' . $vx['subcount'] .
						' Submitted: ' . timedate::unix2canonical(
							$vx['timedate']),
				);
				$filelist = array(
					'type' => html::TYPE_BLIST,
					'data' => array(),
				);
				foreach($rxf as $kxa => $vxa)
				{
					array_push($filelist['data'], $vxa['studentfile']);
				}
				array_push($data5, $file_fs, $filelist, $fsetclose);
			}
		}
	}
	else
	{
		if ($step > 0)
			$message = 'There has been no submissions for this step.';
		else
			$message = 'There has been no submissions for this assignment.';
		$msg = array(
			'type' => html::TYPE_MESSAGE,
			'message' => $message,
		);
		array_push($data5, $msg);
	}

	// Header Messages
	$msg1 = 'Submit Assignment<br>';
	$msg2 = $rxd['class'] . '-' . $rxd['section'] . ': ' . $rxb['name'];
	if ($step > 0) $msg2 .= ' Step ' . $step;
	$warn = 'Submitted files cannot be deleted.<br>' .
		'If changes are needed, then make another submission.';

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
	);

		// XXX Enter custom field data here.


	$data6 = array(
		array(
			'type' => html::TYPE_FSETOPEN,
			'name' => 'Upload Files',
		),
		array(
			'type' => html::TYPE_HIDEOPEN,
			'name' => 'fileInputDiv',
			'hidden' => false,
		),
		array(
			'type' => html::TYPE_FILE,
			'bname' => 'fileSubmit',
			'fname' => 'fileInputForm',
			'name' => 'fileInput',
			'action' => 'funcUploadFile(2, \'assignment=' . $key . '\', \'step='
				. $step . '\')',
			'fsize' => 8,
			'lsize' => 2,
		),
		$fsetclose,
		array(
			'type' => html::TYPE_ACTBTN,
			'dispname' => $functionDisplayUpper,
			'btnset' => html::BTNTYP_VIEW,
		),

		array('type' => html::TYPE_HIDECLOSE),
		array('type' => html::TYPE_FORMCLOSE),
		array('type' => html::TYPE_WDCLOSE),
		array('type' => html::TYPE_BOTB2),
		array('type' => html::TYPE_VTAB10),
	);


	// Render
	$data = array_merge($data1, $data2, $data3, $data4, $data5, $data6);
	return html::pageAutoGenerate($data);
}



?>