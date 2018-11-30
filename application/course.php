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
$moduleFilename = 'course.php';		// XXX Set This

// The name of the module.  It shows in the title bar of the web
// browser and other places.
$moduleTitle = 'Courses';		// XXX Set This

// $moduleId must be a unique positive integer. Module IDs < 1000 are
// reserved for system use.  Therefore application module IDs will
// start at 1000.
$moduleId = 1101;		// XXX Set This

// The capitalized short display name of the module.  This shows up
// on buttons, and some error messages.
$moduleDisplayUpper = 'Course';		// XXX Set This

// The lowercase short display name of the module.  This shows up in
// various messages.
$moduleDisplayLower = 'course';		// XXX Set This

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
//const BASEDIR = '../libs/';
const BASEAPP = '../applibs/';
require_once BASEAPP . 'dbutils.php';
require_once BASEDIR . 'dbaseuser.php';
require_once BASEDIR . 'flag.php';
require_once BASEDIR . 'files.php';
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
		if (flag::sessionGetApp(1))		// Full Control
		{
			$funcBar = array(
				array(
					'Insert' => 'insertDataItem',
					'Update' => 'updateDataItem',
					'Delete' => 'deleteDataItem',
				),
				'View' => 'viewDataItem',
				'List' => 'listDataItems',
			);
		}
		else if (flag::sessionGetApp(2))	// Partial Update
		{
			$funcBar = array(
				array(
					'Update' => 'updateDataItem',
				),
				'View' => 'viewDataItem',
				'List' => 'listDataItems',
			);
		}
		else	// View Only
		{
			$funcBar = array(
				'View' => 'viewDataItem',
				'List' => 'listDataItems',
			);
		}

		// jsfiles is an associtive array which contains additional
		// JavaScript filenames that should be included in the head
		// section of the HTML page.
		$jsFiles = array(
			'/js/baseline/common.js',
			'/js/baseline/files.js',
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
	global $herr;
	global $dbapp;
	global $dbuser;
	global $vendor;
	global $admin;

	// Get data from database.

	if ($vendor || $admin)
	{
		// Vendor and Admin accounts have access to all courses.
		$rxb = $dbapp->queryCourseAll();
		if ($rxb == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage());
			else
			{
				handleError('There are no ' . $moduleDisplayLower .
					's in the database to edit.');
			}
		}
	}
	else
	{
		// Query for students first, since this will be the most used
		// query, for performance reasons.
		$rxa = $dbapp->queryStudentclassStudentAll($_SESSION['userId']);
		if ($rxa == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage());
			else
			{
				// If nothing comes back for a student, then we probably have
				// an instructor.
				$rxb = $dbapp->queryCourseInstructAll($_SESSION['userId']);
				if ($rxb == false)
				{
					if ($herr->checkState())
						handleError($herr->errorGetMessage());
					else
					{
						handleError('There are no ' . $moduleDisplayLower .
							's in the database to edit.');
					}
				}
			}
		}
		else
		{
			// This is a student, so we need to walk through the results and
			// build the course list.
			$rxb = array();
			foreach ($rxa as $kxa => $vxa)
			{
				$rxc = $dbapp->queryCourse($vxa['courseid']);
				if ($rxc == false)
				{
					if ($herr->checkState())
						handleError($herr->errorGetMessage());
					else
					{
						handleError('There are no ' . $moduleDisplayLower .
							's in the database to edit.');
					}
				}
				array_push($rxb, $rxc);
			}
		}
	}

	// Generate Selection Table.
	$list = array(
		'type' => html::TYPE_RADTABLE,
		'name' => 'select_item',
		'clickset' => true,
		'condense' => true,
		'hover' => true,
		'titles' => array(
			'Course',
			'Class',
			'Section',
			'Name',
			'Instructor',
		),
		'tdata' => array(),
		'tooltip' => array(),
	);
	foreach ($rxb as $kx => $vx)
	{
		$rxa = $dbuser->queryContact($vx['instructor']);
		if ($rxa == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage());
			else
				handleError('Database Error: Missing contact information.'
					. '<br>USERID=' . $vx['instruct'] . '<br>Contact your'
					. ' administrator.');
		}

		$tdata = array(
			// These are the values that show up under the columns above.
			// The *FIRST* value is the value that is sent when a row
			// is selected.  AKA Key Field.
			$vx['courseid'],
			$vx['courseid'],
			$vx['class'],
			$vx['section'],
			$vx['name'],
			$rxa['name'],
		);
		$tooltip  = 'Course: ' . $vx['courseid'] . '<br>';
		$tooltip .= 'Class: ' . $vx['class'] . '<br>';
		$tooltip .= 'Section: ' . $vx['section'] . '<br>';
		$tooltip .= 'Name: ' . $vx['name'] . '<br>';
		$tooltip .= 'Instructor: ' . $rxa['name'];

		array_push($list['tdata'], $tdata);
		array_push($list['tooltip'], $tooltip);
	}

	// Generate rest of page.
	$data = array(
		array(
			'type' => html::TYPE_HEADING,
			'message1' => 'Courses',
			'warning' => 'Under Development',
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
		handleError('You must select a ' . $moduleDisplayLower . ' from the list view.');
	// The below line requires customization for database loading.	
	$rxa = $dbapp->queryCourse($key);		// XXX Set This
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

// Checks user security.
function checkSecurity($mode)
{
	global $vendor;
	global $admin;

	if ($vendor || $admin) return;
	else
	{
		switch ($mode)
		{
			case 0:
				if (!flag::sessionGetApp(1))
					handleError('Security Violation: You do not have '
						. 'permission to perform that action.');
				break;
			case 1:
				if (!flag::sessionGetApp(2))
					handleError('Security Violation: You do not have '
						. 'permission to perform that action.');
				break;
		}
	}
}

// Generates file paths.
function generatePaths($course)
{
	global $CONFIGVAR;

	$base = $CONFIGVAR['files_base_path']['value'] . '/' .
		$CONFIGVAR['files_course']['value'] . '/' . $course;
	$curr = '/';
	$result = array(
		'base' => $base,
		'curr' => $curr,
	);
	return $result;
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
	checkSecurity(1);
	$rxa = databaseLoad();
	formPage(MODE_UPDATE, $rxa);
}

// The Add Record view.
function insertRecordView()
{
	checkSecurity(0);
	formPage(MODE_INSERT, NULL);
}

// The Delete Record view.
function deleteRecordView()
{
	checkSecurity(0);
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
	global $vendor;
	global $admin;
	global $dbapp;

	// Check user security to make sure they can do this.
	checkSecurity(1);

	// Get field data.
	$fieldData = generateFieldCheck(FIELDCHK_ARRAY);

	// Get data
	$key = getPostValue('hidden');
	$id = getPostValue('courseid');
	$classid = getPostValue('classid');
	$section = getPostValue('section');
	$cname = getPostValue('coursename');
	$instruct = getPostValue('userlist');
	$syllabus = getPostValue('syllabus');
	$gscale = getPostValue('gradescale');
	$gcurve = getPostValue('curve');

	// We need to retranslate the syllabus input if the selection
	// was '--' which means no file.
	if ($syllabus == '--') $syllabus = '';

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

	// Security checks
	if ($vendor || $admin)
	{
		// Vendor, Admin can change all fields
		$vfystr->fieldchk($fieldData, 1, $classid);
		$vfystr->fieldchk($fieldData, 2, $section);
		$vfystr->fieldchk($fieldData, 3, $cname);
		$vfystr->fieldchk($fieldData, 4, $instruct);
		$vfystr->fieldchk($fieldData, 5, $syllabus);
		$vfystr->fieldchk($fieldData, 6, $gscale);
		$vfystr->fieldchk($fieldData, 7, $gcurve);
	}
	else
	{
		if (flag::sessionGetApp(1))
		{
			$vfystr->fieldchk($fieldData, 1, $classid);
			$vfystr->fieldchk($fieldData, 2, $section);
			$vfystr->fieldchk($fieldData, 3, $cname);
			$vfystr->fieldchk($fieldData, 4, $instruct);
			$vfystr->fieldchk($fieldData, 5, $syllabus);
			$vfystr->fieldchk($fieldData, 6, $gscale);
			$vfystr->fieldchk($fieldData, 7, $gcurve);
		}
		else if (flag::sessionGetApp(2))
		{
			$rxa = $dbapp->queryCourse($id);
			$classid = $rxa['class'];
			$section = $rxa['section'];
			$cname = $rxa['name'];
			$instruct = $rxa['instructor'];
			$vfystr->fieldchk($fieldData, 5, $syllabus);
			$vfystr->fieldchk($fieldData, 6, $gscale);
			$vfystr->fieldchk($fieldData, 7, $gcurve);
		}
		else
			// Should not be able to get here.
			handleError('Security Violation: You do not have permission to perform that action.');
	}

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
	$cname = safeEncodeString($cname);
	
	// We are good, update the record
	$result = $dbapp->updateCourse($key, $classid, $section, $cname, $syllabus,
		$instruct, $gscale, $gcurve);
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
	global $files;

	// Check user security to make sure they can do this.
	checkSecurity(0);

	// Get field data.
	$fieldData = generateFieldCheck(FIELDCHK_ARRAY);

	// Get data
	$id = getPostValue('courseid');
	$classid = getPostValue('classid');
	$section = getPostValue('section');
	$cname = getPostValue('coursename');
	$instruct = getPostValue('userlist');
	$gscale = getPostValue('gradescale');
	$gcurve = getPostValue('curve');

	// Check field data.
	$vfystr->fieldchk($fieldData, 0, $id);
	$vfystr->fieldchk($fieldData, 1, $classid);
	$vfystr->fieldchk($fieldData, 2, $section);
	$vfystr->fieldchk($fieldData, 3, $cname);
	$vfystr->fieldchk($fieldData, 4, $instruct);
	$vfystr->fieldchk($fieldData, 6, $gscale);
	$vfystr->fieldchk($fieldData, 7, $gcurve);

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

	// Since the course directory has not yet been created, there
	// can be no syllabus file, so it's blocked on the update, and
	// we manually set it to blank here.
	$syllabus = '';

	// Safely encode all strings to prevent XSS attacks.
	$cname = safeEncodeString($cname);
	
	// We are good, insert the record
	$result = $dbapp->insertCourse($id, $classid, $section, $cname,
		$syllabus, $instruct, $gscale, $gcurve);
	if ($result == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database: Record insert failed. Key = ' . $id);
	}

	// Create the course directory.
	$result = mkdir($CONFIGVAR['files_base_path']['value'] . '/' .
		$CONFIGVAR['files_course']['value'] . '/' . $id, 0775);
	if ($result == false)
		handleError('Filesystem Error: Unable to create course directory');

	// Everything is good so notify the user.
	sendResponseClear($moduleDisplayUpper . ' insert completed: key = '
		. $id);
	exit(0);
}

// Deletes the record from the database.
// XXX: Requires customization.
function deleteRecordAction()
{
	global $ajax;
	global $herr;
	global $vfystr;
	global $dbapp;
	global $CONFIGVAR;
	global $moduleDisplayUpper;
	global $moduleDisplayLower;

	// Check user security to make sure they can do this.
	checkSecurity(0);

	// Gather data...
	$key = getPostValue('hidden');
	$id = getPostValue('courseid');		// XXX Set This

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
	$result = $dbapp->metaDeleteCourse($key);		// XXX Set This
	if ($result == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
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
	global $herr;
	global $dbuser;
	global $dbapp;
	global $files;

	// Determine the editing mode.
	switch($mode)
	{
		case MODE_VIEW:			// View
			$msg1 = 'Viewing ' . $moduleDisplayUpper . ' Data For';
			$msg2 = '';
			$warn = '';
			$btnset = html::BTNTYP_VIEW;
			$action = '';
			$hideValue = $rxa['courseid'];
			$disable1 = true;
			$disable2 = true;
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
			// Set security parameters.
			// Only an admin can perform an insert or delete.
			// An instructor and an admin can do an update, but for
			// different groups of fields, which we need to code for.
			// Everybody can view.
			if (flag::sessionGetApp(1))
			{
				$disable1 = false;
				$disable2 = false;
			}
			else if (flag::sessionGetApp(2))
			{
				$disable1 = true;
				$disable2 = false;
			}
			else
			{
				$disable1 = true;
				$disable2 = true;
			}
			$default = true;
			$key = true;
			break;
		case MODE_INSERT:		// Insert
			$msg1 = 'Inserting ' . $moduleDisplayUpper . ' Data';
			$msg2 = '';
			$warn = '';
			$btnset = html::BTNTYP_INSERT;
			$action = 'submitInsert()';
			$disable1 = false;
			$disable2 = false;
			$default = true;
			$key = false;
			break;
		case MODE_DELETE:		// Delete
			$msg1 = 'Deleting ' . $moduleDisplayUpper . ' Data For';
			$msg2 = '';
			$warn = '';
			$btnset = html::BTNTYP_DELETE;
			$action = 'submitDelete()';
			$hideValue = $rxa['courseid'];
			$disable1 = true;
			$disable2 = true;
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
			'courseid' => '',
			'class' => '',
			'section' => 1,
			'name' => '',
			'syllabus' => '',
			'instructor' => '',
			'scale' => 0,
			'curve' => 0,
		);
	}

	// XXX Custom field rendering code

	if ($mode == MODE_UPDATE)
	{
		// File list for syllabus.
		$paths = generatePaths($rxa['courseid']);
		$files = $files->getFileList($paths['base'], $paths['curr'],
			filesClass::FILELIST_RECURSIVE);
		$files = array_merge(array('--' => '--'), $files);
		$syllabus = array(
			'type' => html::TYPE_PULLDN,
			'name' => 'syllabus',
			'label' => 'Syllabus File',
			'lsize' => 3,
			'fsize' => 6,
			'disable' => $disable2,
			'optlist' => $files,
			'tooltip' => 'The course syllabus file.',
		);
		if ($default) $syllabus['default'] = $rxa['syllabus'];
	}
	else
	{
		$syllabus = generateField(html::TYPE_TEXT, 'syllabus', 'Syllabus File', 6,
			'', 'The course syllabus file.', $default, true);
	}

	if ($mode == MODE_UPDATE || $mode == MODE_INSERT)
	{
		// Instructor list
		$userdata = getUsersByProfile($CONFIGVAR['app_profile_instruct']['value']);
		$userlist = array(
			'type' => html::TYPE_PULLDN,
			'name' => 'userlist',
			'label' => 'Instructor List',
			'lsize' => 3,
			'fsize' => 6,
			'disable' => $disable1,
			'optlist' => $userdata,
			'tooltip' => 'The instructor for the course.',
		);
		if ($default) $userlist['default'] = $rxa['instructor'];

		// Grading Scale
		$rxb = $dbapp->queryGradescaleAll($rxa['instructor']);
		if ($rxb == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage());
		}
		$scalelist = array(
			'Default Grade Scale' => 0,
		);
		if ($rxb != false)
		{
			foreach ($rxb as $kx => $vx)
			{
				$scalelist[$vx['name']] = $vx['scale'];
			}
		}
		$scale = array(
			'type' => html::TYPE_PULLDN,
			'name' => 'gradescale',
			'label' => 'Grading Scale',
			'lsize' => 3,
			'fsize' => 4,
			'disable' => $disable2,
			'optlist' => $scalelist,
			'tooltip' => 'The course grading scale.',
		);
		if ($default) $scale['default'] = $rxa['scale'];
	}
	else
	{
		$contact = $dbuser->queryContact($rxa['instructor']);
		if ($contact == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage());
			else
				handleError('Database Error: User contact information missing.'
					. '<br>USERID=' . $vx['userid']
					. '<br>Contact your administrator.');
		}
		$userlist = generateField(html::TYPE_TEXT, 'userlist', 'Instructor', 6,
			$contact['name'], 'The instructor for the course.', $default,
			$disable1);
		$gradescale = $dbapp->queryGradescale($rxa['scale']);
		if ($gradescale === false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage());
			else
				handleError('Database Error: Grading scale information missing.');
		}
		$scale = generateField(html::TYPE_TEXT, 'gradescale', 'Grading Scale', 4,
			$gradescale['name'], 'This is the grading scale for the course.',
			$default, $disable2);
	}

	$courseid = generateField(html::TYPE_TEXT, 'courseid', 'Course ID', 4,
		$rxa['courseid'], 'The course call number. Key value.', $default,
		$disable1);
	$classid = generateField(html::TYPE_TEXT, 'classid', 'Class', 4,
		$rxa['class'], 'The class department and number.', $default,
		$disable1);
	$section = generateField(html::TYPE_TEXT, 'section', 'Section', 2,
		$rxa['section'], 'The section number of the course.', $default,
		$disable1);
	$name = generateField(html::TYPE_TEXT, 'coursename', 'Name', 6,
		$rxa['name'], 'The name of the course', $default, $disable1);
	$curve = generateField(html::TYPE_TEXT, 'curve', 'Grading Curve', 2,
		$rxa['curve'], 'The grading curve for the class', $default,
		$disable2);

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
			'type' => html::TYPE_FSETOPEN,
			'name' => 'Basic Course Information',
		),
		$courseid,
		$classid,
		$section,
		$name,
		$userlist,
		array(
			'type' => html::TYPE_FSETCLOSE,
		),
		array(
			'type' => html::TYPE_FSETOPEN,
			'name' => 'Instructor Configured Information',
		),
		$syllabus,
		$scale,
		$curve,
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

// Generate the field definitions for client side error checking.
function generateFieldCheck($returnType = 0)
{
	global $CONFIGVAR;
	global $vfystr;

	$data = array(
		0 => array(
			'dispname' => 'Course ID',
			'name' => 'courseid',
			'type' => $vfystr::STR_PINTEGER,
			'noblank' => true,
			'max' => 999999,
			'min' => 1000,
		),
		1 => array(
			'dispname' => 'Class',
			'name' => 'classid',
			'type' => $vfystr::STR_ALPHANUMPUNCT,
			'noblank' => true,
			'max' => 12,
			'min' => 3,
		),
		2 => array(
			'dispname' => 'Section',
			'name' => 'section',
			'type' => $vfystr::STR_PINTEGER,
			'noblank' => true,
			'max' => 99,
			'min' => 1,
		),
		3 => array(
			'dispname' => 'Name',
			'name' => 'coursename',
			'type' => $vfystr::STR_ALPHANUMPUNCT,
			'noblank' => true,
			'max' => 50,
			'min' => 1,
		),
		4 => array(
			'dispname' => 'Instructor List',
			'name' => 'userlist',
			'type' => $vfystr::STR_PINTEGER,
			'noblank' => true,
			'max' => 2147483647,
			'min' => 1,
		),
		5 => array(
			'dispname' => 'Syllabus File',
			'name' => 'syllabus',
			'type' => $vfystr::STR_PATHNAME,
			'noblank' => false,
			'max' => 256,
			'min' => 0,
		),
		6 => array(
			'dispname' => 'Grading Scale',
			'name' => 'gradescale',
			'type' => $vfystr::STR_PINTEGER,
			'noblank' => true,
			'max' => 2147483647,
			'min' => 0,
		),
		7 => array(
			'dispname' => 'Grading Curve',
			'name' => 'curve',
			'type' => $vfystr::STR_INTEGER,
			'noblank' => true,
			'max' => 100,
			'min' => -100,
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