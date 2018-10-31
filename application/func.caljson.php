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
$functionFilename = 'func.caljson.php';			// XXX Set This

// The capitalized short display name of the function.  This shows up
// on buttons, and some error messages.
$functionDisplayUpper = 'func.caljson';		// XXX Set This

// The lowercase short display name of the function.  This shows up in
// various messages.
$functionDisplayLower = 'func.caljson';		// XXX Set This

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
require_once BASEDIR . 'timedate.php';
require_once BASEDIR . 'funchead.php';

// Aways called on a HTTP GET method request.
// Do not remove
// XXX: Requires customization.
function commandProcessorGet($commandId)
{
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
	switch ((int)$commandId)
	{
		case 130:
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
}

// Performs the primary action for this function
// XXX: Requires customization.
function performDataAction()
{
	global $CONFIGVAR;
	global $ajax;
	global $herr;
	global $dbapp;
	global $dbuser;
	global $dbconf;

	// Get requested min/max times.
	$min = GetPostValue('timemin');
	$max = GetPostValue('timemax');

	// $min = (int)($min / 100);
	// $max = (int)($min / 100);
	// var_dump($min, $max);

	// Setup
	$student = $_SESSION['userId'];
	$time = time();
	$data = array();

	// Courses
	$rxa = $dbapp->queryStudentclassStudentAll($_SESSION['userId']);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		$rxb = $dbapp->queryCourseInstructAll($_SESSION['userId']);
		if ($rxb == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage());
			else
				$ajax->sendJSON(152, '[]');
			return;
		}
	}
	else
	{
		$rxb = array();
		foreach($rxa as $kx => $vx)
		{
			$rxc = $dbapp->queryCourse($vx['courseid']);
			if ($rxc == false)
			{
				if ($herr->checkState())
					handleError($herr->errorGetMessage());
				else
					$ajax->sendJSON(152, '[]');
				return;
			}
			else array_push($rxb, $rxc);
		}
	}

	// Process data into arrays which are required for JSON format.
	$priorityHigh = $CONFIGVAR['assign_priority_high']['value'] + $time;
	$priorityMed = $CONFIGVAR['assign_priority_medium']['value'] + $time;
	$priorityLow = $CONFIGVAR['assign_priority_low']['value'] + $time;
	foreach($rxb as $kx => $vx)
	{
		$rxa = $dbapp->queryAssignmentRangeDue($vx['courseid'], $min, $max);
		if ($rxa == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage());
			continue;
		}
		foreach($rxa as $kxa => $vxa)
		{
			// Mandatory eventObject data.
			$event = array(
				'title' => $vxa['name'],
				'start' => timedate::unix2moment($vxa['duedate']),
				'editable' => false,
				'assignment' => $vxa['assignment'],
			);

			// Priority coloring for assignments
			if ($time > $vxa['duedate'])
			{
				// Past due
				$event['backgroundColor'] = 'black';
				$event['borderColor'] = 'red';
				$event['textColor'] = 'white';
			}
			else if ($vxa['duedate'] < $priorityHigh)
			{
				// High Priority
				$event['backgroundColor'] = 'red';
				$event['borderColor'] = 'red';
				$event['textColor'] = 'white';
			}
			else if ($vxa['duedate'] < $priorityMed)
			{
				// Medium Priority
				$event['backgroundColor'] = '#ff9900';
				$event['borderColor'] = '#ff9900';
				$event['textColor'] = 'black';
			}
			else if ($vxa['duedate'] < $priorityLow)
			{
				// Low Priority
				$event['backgroundColor'] = '#006600';
				$event['borderColor'] = '#006600';
				$event['textColor'] = 'white';
			}
			else
			{
				// Low-Low Priority
				$event['backgroundColor'] = 'blue';
				$event['borderColor'] = 'blue';
				$event['textColor'] = 'white';
			}

			// Push assignment event onto the data array.
			array_push($data, $event);

			// Assignment Step
			$rxc = $dbapp->queryAssignstepAssignAll($vxa['assignment']);
			if ($rxc == false)
			{
				if ($herr->checkState())
					handleError($herr->errorGetMessage());
				continue;
			}
			foreach ($rxc as $kxb => $vxb)
			{
				// Mandatory eventObject data.
				$event = array(
					'title' => $vxa['name'] . ' Step ' . $vxb['step'],
					'start' => timedate::unix2moment($vxb['date']),
					'editable' => false,
					'assignment' => $vxa['assignment'],
				);
	
				// Priority coloring for assignment steps.
				if ($time > $vxb['date'])
				{
					// Past due
					$event['backgroundColor'] = 'black';
					$event['borderColor'] = 'yellow';
					$event['textColor'] = 'yellow';
				}
				else if ($vxb['date'] < $priorityHigh)
				{
					// High Priority
					$event['backgroundColor'] = 'red';
					$event['borderColor'] = 'yellow';
					$event['textColor'] = 'yellow';
				}
				else if ($vxb['date'] < $priorityMed)
				{
					// Medium Priority
					$event['backgroundColor'] = '#ff9900';
					$event['borderColor'] = 'yellow';
					$event['textColor'] = 'black';
				}
				else if ($vxb['date'] < $priorityLow)
				{
					// Low Priority
					$event['backgroundColor'] = '#006600';
					$event['borderColor'] = 'yellow';
					$event['textColor'] = 'yellow';
				}
				else
				{
					// Low-Low Priority
					$event['backgroundColor'] = 'blue';
					$event['borderColor'] = 'yellow';
					$event['textColor'] = 'yellow';
				}

				// Push assignment step event onto the data array.
				array_push($data, $event);
			}
		}
	}

	// Convert to JSON and send.
	$json = json_encode($data);
	$ajax->sendJSON(152, $json);
}



?>