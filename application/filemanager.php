<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application File Management Utility

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
$moduleFilename = 'filemanager.php';

// The name of the module.  It shows in the title bar of the web
// browser and other places.
$moduleTitle = 'File Manager';

// $moduleId must be a unique positive integer. Module IDs < 1000 are
// reserved for system use.  Therefore application module IDs will
// start at 1000.
$moduleId = 1100;

// The capitalized short display name of the module.  This shows up
// on buttons, and some error messages.
$moduleDisplayUpper = 'File Manager';

// The lowercase short display name of the module.  This shows up in
// various messages.
$moduleDisplayLower = 'file manager';

// Set to true if this is a system module.
$moduleSystem = false;

// This is to enable the PUT method.
$httpMethod_PUT_ENABLE = true;

// Some operational constants
const BLOCKSIZE = 4096;

// This setting indicates that a file will be used instead of the
// default template.  Set to the name of the file to be used.
//$inject_html_file = '../dat/somefile.html';
$htmlInjectFile = false;

// Order matters here.  The modhead library needs to be loaded last.
// If additional libraries are needed, then load them before.
const BASEDIR = '../libs/';
const APPSDIR = '../applibs/';
require_once BASEDIR . 'files.php';
require_once BASEDIR . 'flag.php';
require_once APPSDIR . 'dbaseapp.php';
require_once APPSDIR . 'panels.php';
require_once APPSDIR . 'loadmodule.php';

// Flags in the permissions bitmap of what permissions the user
// has in this module.
$fullcontrol = flag::sessionGetApp(0);

// Now load the module header.
require_once BASEDIR . 'modhead.php';

// Called when the client sends a GET request to the server.
// This call comes from modhead.php.
function loadInitialContent()
{
	global $htmlInjectFile;
	global $inject_html_file;
	global $CONFIGVAR;
	global $vfystr;
	global $herr;
	global $files;
	global $fullcontrol;

	// Additonal
	// This is probably the only time that we pass parameters with
	// the GET method, so we need to check if there is a download
	// request for it.
	if (count($_GET) > 0)
	{
		// Set the activity mode.
		$mode = NULL;
		$token = NULL;
		if (isset($_GET['mode'])) $mode = $_GET['mode'];
		if (isset($_GET['token'])) $token = $_GET['token'];

		// Process.
		switch($mode)
		{
			case 'download':
				$files->fileDownload($token);
				break;
			case 'view':
				$files->fileView($token);
				break;
			default:
				http_response_code(400);
				exit(1);

		}
		exit(0);
	}

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
		if ($fullcontrol)
		{
			// Full Control
			$funcBar = array(
				'FILE' => 'noAction',
				array(
					'Upload' => 'fileUpload',
					'Download' => 'fileDownload',
				),
				array(
					'View' => 'fileView',
					'Detail' => 'fileDetail',
				),
				array(
					'Move' => 'fileMove',
					'Rename' => 'fileRename',
					'Copy' => 'fileCopy',
					'Delete' => 'fileDelete',
				),
			);
			$funcBar2 = array(
				'DIR' => 'noAction',
				array(
					'Home' => 'directoryHome',
					'UP' => 'directoryUp',
					'DN' => 'directoryDown',
				),
				array(
					'Create' => 'directoryCreate',
					'Rename' => 'directoryRename',
					'Move' => 'directoryMove',
					'Delete' => 'directoryDelete',
					'Del Tree' => 'directoryDeleteAll',
				),
			);
		}
		else
		{
			// Restricted Permissions
			$funcBar = array(
				'FILE' => 'noAction',
				array(
					'Download' => 'fileDownload',
				),
				array(
					'View' => 'fileView',
				),
			);
			$funcBar2 = array(
				'DIR' => 'noAction',
				array(
					'Home' => 'directoryHome',
					'UP' => 'directoryUp',
					'DN' => 'directoryDown',
				),
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
		// datepick, and tooltip.
		// $htmlFlags= array(
		// 	'checkbox',
		// 	'datepick',
		// 	'tooltip',
		// );
		$htmlFlags = array(
			'tooltip',
			'type2',
			'funchide',
		);

		//html::loadTemplatePage($moduleTitle, $htmlUrl, $moduleFilename,
		//  $left, $right, $funcBar, $jsFiles, $cssFiles, $htmlFlags);
		html::loadTemplatePage($moduleTitle, $baseUrl, $moduleFilename,
			$left, '', $funcBar, $jsFiles, $cssFiles, $htmlFlags, $funcBar2);
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
	global $dbapp;
	global $herr;
	global $moduleTitle;
	global $moduleDisplayLower;
	global $panels;
	global $ajax;
	global $fullcontrol;

	// For performance reasons, we check to see if the user is a student
	// first, then we check for an instructor.  If neither, then we fail.
	$rxa = $dbapp->queryStudentclassStudentAll($_SESSION['userId']);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
		{
			$rxb = $dbapp->queryCourseInstructAll($_SESSION['userId']);
			if ($rxb == false)
			{
				if ($herr->checkState())
					handleError($herr->errorGetMessage());
				else
					handleError('You are not enrolled and/or not teaching any courses.');
			}
		}
	}
	else
	{
		$rxb = array();
		foreach ($rxa as $kxa => $vxa)
		{
			$rxc = $dbapp->queryCourse($vxa['courseid']);
			if ($rxc == false)
			{
				if ($herr->checkState())
					handleError($herr->errorGetMessage());
				else
					handleError('Database Error: (Stored Data Conflict)<br>' .
						'TABLE: studentclass<br>UserID: ' . $_SESSION['userId'] .
						'<br>CourseID: ' . $vxa['courseid'] .
						';--> Missing course information.<br>' .
						'Contact your administrator');
			}
			array_push($rxb, $rxc);
		}
	}

	// Generate the selection table.
	$list = array(
		'type' => html::TYPE_RADTABLE,
		'name' => 'select_item',
		'clickset' => true,
		'condense' => true,
		'hover' => true,
		'mode' => 2,
		'titles' => array(
			'Course',
			'Class',
			'Section',
			'Name',
		),
		'tdata' => array(),
		'tooltip' => array(),
	);
	foreach ($rxb as $kxb => $vxb)
	{
		$tdata = array(
			$vxb['courseid'],
			$vxb['courseid'],
			$vxb['class'],
			$vxb['section'],
			$vxb['name'],
		);
		$tooltip  = 'Course:  ' . $vxb['courseid'] . '<br>';
		$tooltip .= 'Class:   ' . $vxb['class'] . '<br>';
		$tooltip .= 'Section: ' . $vxb['section'] . '<br>';
		$tooltip .= 'Name:    ' . $vxb['name'] . '<br>';
		if ($fullcontrol)
		{
			$tooltip .= 'Grading Scale: ' . $vxb['scale'] . '<br>';
			$tooltip .= 'Grading Curve: ' . $vxb['curve'] . '<br>';
		}
		array_push($list['tdata'], $tdata);
		array_push($list['tooltip'], $tooltip);
	}

	// Generate rest of page. (Title, headers, etc)
	$data = array(
		array(
			'type' => html::TYPE_HEADING,
			'message1' => 'File Manager',
			'message3' => 'Click to select course.',
		),
		array('type' => html::TYPE_TOPB1),
		//array('type' => html::TYPE_WD75OPEN),
		array(
			'type' => html::TYPE_FORMOPEN,
			'name' => 'select_table',
		),

		// Enter custom data here.
		$list,

		//End of custom data

		array('type' => html::TYPE_FORMCLOSE),
		//array('type' => html::TYPE_WDCLOSE),
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
		case 20:
			directoryHome();
			break;
		case 21:
			directoryMoveUp();
			break;
		case 22:
			directoryMoveDown();
			break;
		case 23:
			directoryCreate();
			break;
		case 24:
			directoryRename();
			break;
		case 25:
			directoryMove();
			break;
		case 26:
			directoryDelete();
			break;
		case 27:
			directoryDeleteAll();
			break;
		case 30:
			fileUpload();
			break;
		case 31:
			fileDownload();
			break;
		case 32:
			fileView();
			break;
		case 33:
			fileDetail();
			break;
		case 34:
			fileRename();
			break;
		case 35:
			fileMove();
			break;
		case 36:
			fileCopy();
			break;
		case 37:
			fileDelete();
			break;
		case 90:
			$moduleLoad->loadModule();
			break;
		case 91:
			showFileList();
			break;
		default:
			// If we get here, then the command is undefined.
			$ajax->sendCode(ajaxClass::CODE_NOTIMP,
				'The command ' . $commandId . ' has not been implemented');
			exit(1);
			break;
	}
}

// Builds the global variables and then shows the initial file list.
function showFileList()
{
	global $courseDir;
	global $courseMsg;
	global $dbapp;
	global $herr;
	global $vfystr;

	// Get course information from database.
	$course = getSelectedItem();
	$result = $vfystr->strchk($course, '', '', verifyString::STR_PINTEGER);
	if ($result == false)
		handleError($herr->errorGetMessage());
	$rxa = $dbapp->queryCourse($course);
	if ($rxa == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
		else
			handleError('Database Error: Query course data failed. COURSEID='
				. $course);
	}

	// Set global course information in the user's session.
	$_SESSION['courseDir'] = $course;
	$_SESSION['courseMsg'] = $course . ': ' . $rxa['class'] . '-' . $rxa['section']
		. ': ' . $rxa['name'];

	// Show the files.
	directoryHome();
}

// Checks the user's security
function securityCheck()
{
	global $fullcontrol;

	if (!$fullcontrol)
		handleError('Security Violation: You do not have permission to perform that action');
}

// Returns the basePath.
function getBasePath()
{
	global $CONFIGVAR;

	$path = $CONFIGVAR['files_base_path']['value'];
	$path .= '/' . $CONFIGVAR['files_course']['value'];
	$path .= '/' . $_SESSION['courseDir'];
	return $path;
}

// Returns the currentPath.
function getCurrentPath()
{
	$currentPath = getPostValue('hidden');
	if (empty($currentPath))
		handleError('Missing path data.');
	return $currentPath;
}

// Returns the currentPath.  Only used for uploads.
function getCurrentPathX()
{
	$currentPath = $_SERVER['HTTP_X_PATH'];
	if (empty($currentPath))
		handleError('Missing path data.');
	return $currentPath;
}

// Gets the selected item.
function getSelectedItem()
{
	$select = getPostValue('select_item');
	if ($select == NULL)
		handleError('You must select an item from the list in order ' .
			'to use this command.');
	return $select;
}

// Returns a user supplied filename.
function getFilename()
{
	$filename = getPostValue('filename');
	if (empty($filename))
		handleError('Missing new filename.');
	return $filename;
}

// Returns a user supplied directory name.
function getDirname()
{
	$dirname = getPostValue('dirname');
	if (empty($dirname))
		handleError('Missing new directory name.');
	return $dirname;
}

// Returns a user supplied path name.
function getPathname()
{
	$pathname = getPostValue('pathname');
	if (empty($pathname))
		handleError('Missing path name.');
	return $pathname;
}

// Sets the current directory to /.
function directoryHome()
{
	global $files;
	global $courseMsg;

	$basePath = getBasePath();
	$currentPath = '/';
	echo $files->buildDirectoryList($basePath, $currentPath, $courseMsg);
}

// Moves up one directory
function directoryMoveUp()
{
	global $files;

	$basePath = getBasePath();
	$currentPath = getCurrentPath();
	$currentPath = $files->directoryMoveUp($basePath, $currentPath);
	echo $files->buildDirectoryList($basePath, $currentPath, $_SESSION['courseMsg']);
}

// Moves down a directory
function directoryMoveDown()
{
	global $files;

	$basePath = getBasePath();
	$currentPath = getCurrentPath();
	$selectedItem = getSelectedItem();
	$currentPath = $files->directoryMoveDown($basePath, $currentPath, $selectedItem);
	echo $files->buildDirectoryList($basePath, $currentPath, $_SESSION['courseMsg']);
}

// Creates a new directory.
function directoryCreate()
{
	global $files;

	securityCheck();
	$basePath = getBasePath();
	$currentPath = getCurrentPath();
	$dirname = getDirname();
	$files->directoryCreate($basePath, $currentPath, $dirname);
	echo $files->buildDirectoryList($basePath, $currentPath, $_SESSION['courseMsg']);
}

// Renames an existing directory.
function directoryRename()
{
	global $files;

	securityCheck();
	$basePath = getBasePath();
	$currentPath = getCurrentPath();
	$selectedItem = getSelectedItem();
	$dirname = getDirname();
	$files->directoryRename($basePath, $currentPath, $selectedItem, $dirname);
	echo $files->buildDirectoryList($basePath, $currentPath, $_SESSION['courseMsg']);
}

// Moves a directory to another location in the tree.
function directoryMove()
{
	global $files;

	securityCheck();
	$basePath = getBasePath();
	$currentPath = getCurrentPath();
	$selectedItem = getSelectedItem();
	$pathname = getPathname();
	$files->directoryMove($basePath, $currentPath, $selectedItem, $pathname);
	echo $files->buildDirectoryList($basePath, $currentPath, $_SESSION['courseMsg']);
}

// Removes an existing directory.
function directoryDelete()
{
	global $files;

	securityCheck();
	$basePath = getBasePath();
	$currentPath = getCurrentPath();
	$selectedItem = getSelectedItem();
	$files->directoryRemove($basePath, $currentPath, $selectedItem);
	echo $files->buildDirectoryList($basePath, $currentPath, $_SESSION['courseMsg']);
}

// Removes an existing directory and everything in it.
function directoryDeleteAll()
{
	global $files;

	securityCheck();
	$basePath = getBasePath();
	$currentPath = getCurrentPath();
	$selectedItem = getSelectedItem();
	$files->directoryRemoveAll($basePath, $currentPath, $selectedItem);
	echo $files->buildDirectoryList($basePath, $currentPath, $_SESSION['courseMsg']);
}

// Views a text file.
function fileView()
{
	global $ajax;
	global $files;
	global $moduleFilename;

	$basePath = getBasePath();
	$currentPath = getCurrentPath();
	$selectedItem = getSelectedItem();
	$url = $files->fileViewUrl($basePath, $currentPath,
		$selectedItem, html::getBaseUrl(), '/application/' .
		$moduleFilename);
	$ajax->sendCommand(47, $url);
}

// Provides details of the given file.
function fileDetail()
{
	global $ajax;
	global $files;

	securityCheck();
	$basePath = getBasePath();
	$currentPath = getCurrentPath();
	$selectedItem = getSelectedItem();
	$fileinfo = $files->fileDetail($basePath, $currentPath, $selectedItem);
	$ajax->sendCommand(48, $fileinfo);
}

// Renames an existing file.
function fileRename()
{
	global $files;

	securityCheck();
	$basePath = getBasePath();
	$currentPath = getCurrentPath();
	$selectedItem = getSelectedItem();
	$filename = getFilename();
	$files->fileRename($basePath, $currentPath, $selectedItem, $filename);
	echo $files->buildDirectoryList($basePath, $currentPath, $_SESSION['courseMsg']);
}

// Moves an existing file to a new location.
function fileMove()
{
	global $files;

	securityCheck();
	$basePath = getBasePath();
	$currentPath = getCurrentPath();
	$selectedItem = getSelectedItem();
	$pathname = getPathname();
	$files->fileMove($basePath, $currentPath, $selectedItem, $pathname);
	echo $files->buildDirectoryList($basePath, $currentPath, $_SESSION['courseMsg']);
}

// Copies a file.
function fileCopy()
{
	global $files;

	securityCheck();
	$basePath = getBasePath();
	$currentPath = getCurrentPath();
	$selectedItem = getSelectedItem();
	$pathname = getPathname();
	$files->fileCopy($basePath, $currentPath, $selectedItem, $pathname);
	echo $files->buildDirectoryList($basePath, $currentPath, $_SESSION['courseMsg']);
}

// Removes a file.
function fileDelete()
{
	global $files;

	securityCheck();
	$basePath = getBasePath();
	$currentPath = getCurrentPath();
	$selectedItem = getSelectedItem();
	$files->fileDelete($basePath, $currentPath, $selectedItem);
	echo $files->buildDirectoryList($basePath, $currentPath, $_SESSION['courseMsg']);
}

// Uploads the file.
function fileUpload()
{
	global $files;

	securityCheck();
	$basePath = getBasePath();
	$currentPath = getCurrentPathX();
	$files->fileUpload($basePath, $currentPath, 0);
	echo $files->buildDirectoryList($basePath, $currentPath, $_SESSION['courseMsg']);
}

// Generates a URL for file download.
// The way that this works is that this function generates the URL which
// is sent to the client with a client command.  Then the client opens a
// popup window with the provided URL which vectors back into this script
// via GET method.  Then in the new window, headers are sent to kick off
// the download.
function fileDownload()
{
	global $ajax;
	global $files;
	global $moduleFilename;

	$basePath = getBasePath();
	$currentPath = getCurrentPath();
	$selectedItem = getSelectedItem();
	$url = $files->fileDownloadUrl($basePath, $currentPath,
		$selectedItem, html::getBaseUrl(), '/application/' .
		$moduleFilename);
	$ajax->sendCommand(46, $url);
}


?>