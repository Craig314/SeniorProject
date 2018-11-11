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
$moduleFilename = 'filefinder.php';

// The name of the module.  It shows in the title bar of the web
// browser and other places.
$moduleTitle = 'File Finder';

// $moduleId must be a unique positive integer. Module IDs < 1000 are
// reserved for system use.  Therefore application module IDs will
// start at 1000.
$moduleId = 14;

// The capitalized short display name of the module.  This shows up
// on buttons, and some error messages.
$moduleDisplayUpper = 'File Finder';

// The lowercase short display name of the module.  This shows up in
// various messages.
$moduleDisplayLower = 'file finder';

// Set to true if this is a system module.
$moduleSystem = true;

// Flags in the permissions bitmap of what permissions the user
// has in this module.  Currently not implemented.
$modulePermissions = array();

// This is to enable the PUT method
$httpMethod_PUT_ENABLE = true;


// Some operational constants
const BLOCKSIZE = 4096;
const BLOCKBACK = 512;

// This setting indicates that a file will be used instead of the
// default template.  Set to the name of the file to be used.
//$inject_html_file = '../dat/somefile.html';
$htmlInjectFile = false;

// Order matters here.  The modhead library needs to be loaded last.
// If additional libraries are needed, then load them before.
const BASEDIR = '../libs/';
require_once BASEDIR . 'files.php';
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
			),
		);

		// jsfiles is an associtive array which contains additional
		// JavaScript filenames that should be included in the head
		// section of the HTML page.
		$jsFiles = array(
			'/js/module/filefinder.js',
		);

		// cssfiles is an associtive array which contains additional
		// cascading style sheets that should be included in the head
		// section of the HTML page.
		$cssFiles = array(
			'/css/tooltip-linebreak.css',
			'/css/tooltip-left.css',
			'/css/tooltip-mono.css',
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
	directoryHome();
	exit(0);
}

// Called when the initial command processor doesn't have the
// command number. This call comes from modhead.php.
function commandProcessor($commandId)
{
	global $ajax;

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
		default:
			// If we get here, then the command is undefined.
			$ajax->sendCode(ajaxClass::CODE_NOTIMP,
				'The command ' . $commandId . ' has not been implemented');
			exit(1);
			break;
	}
}

// Returns the basePath.
function getBasePath()
{
	global $CONFIGVAR;

	return $CONFIGVAR['server_document_root']['value'];
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

	$basePath = getBasePath();
	$currentPath = '/';
	echo $files->buildDirectoryList($basePath, $currentPath);
}

// Moves up one directory
function directoryMoveUp()
{
	global $files;

	$basePath = getBasePath();
	$currentPath = getCurrentPath();
	$currentPath = $files->directoryMoveUp($basePath, $currentPath);
	echo $files->buildDirectoryList($basePath, $currentPath);
}

// Moves down a directory
function directoryMoveDown()
{
	global $files;

	$basePath = getBasePath();
	$currentPath = getCurrentPath();
	$selectedItem = getSelectedItem();
	$currentPath = $files->directoryMoveDown($basePath, $currentPath, $selectedItem);
	echo $files->buildDirectoryList($basePath, $currentPath);
}

// Creates a new directory.
function directoryCreate()
{
	global $files;

	$basePath = getBasePath();
	$currentPath = getCurrentPath();
	$dirname = getDirname();
	$files->directoryCreate($basePath, $currentPath, $dirname);
	echo $files->buildDirectoryList($basePath, $currentPath);
}

// Renames an existing directory.
function directoryRename()
{
	global $files;

	$basePath = getBasePath();
	$currentPath = getCurrentPath();
	$selectedItem = getSelectedItem();
	$dirname = getDirname();
	$files->directoryRename($basePath, $currentPath, $selectedItem, $dirname);
	echo $files->buildDirectoryList($basePath, $currentPath);
}

// Moves a directory to another location in the tree.
function directoryMove()
{
	global $files;

	$basePath = getBasePath();
	$currentPath = getCurrentPath();
	$selectedItem = getSelectedItem();
	$pathname = getPathname();
	$files->directoryMove($basePath, $currentPath, $selectedItem, $pathname);
	echo $files->buildDirectoryList($basePath, $currentPath);
}

// Removes an existing directory.
function directoryDelete()
{
	global $files;

	$basePath = getBasePath();
	$currentPath = getCurrentPath();
	$selectedItem = getSelectedItem();
	$files->directoryRemove($basePath, $currentPath, $selectedItem);
	echo $files->buildDirectoryList($basePath, $currentPath);
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
		$selectedItem, html::getBaseUrl(), '/modules/' .
		$moduleFilename);
	$ajax->sendCommand(47, $url);
}

// Provides details of the given file.
function fileDetail()
{
	global $ajax;
	global $files;

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

	$basePath = getBasePath();
	$currentPath = getCurrentPath();
	$selectedItem = getSelectedItem();
	$filename = getFilename();
	$files->fileRename($basePath, $currentPath, $selectedItem, $filename);
	echo $files->buildDirectoryList($basePath, $currentPath);
}

// Moves an existing file to a new location.
function fileMove()
{
	global $files;

	$basePath = getBasePath();
	$currentPath = getCurrentPath();
	$selectedItem = getSelectedItem();
	$pathname = getPathname();
	$files->fileMove($basePath, $currentPath, $selectedItem, $pathname);
	echo $files->buildDirectoryList($basePath, $currentPath);
}

// Copies a file.
function fileCopy()
{
	global $files;

	$basePath = getBasePath();
	$currentPath = getCurrentPath();
	$selectedItem = getSelectedItem();
	$pathname = getPathname();
	$files->fileCopy($basePath, $currentPath, $selectedItem, $pathname);
	echo $files->buildDirectoryList($basePath, $currentPath);
}

// Removes a file.
function fileDelete()
{
	global $files;

	$basePath = getBasePath();
	$currentPath = getCurrentPath();
	$selectedItem = getSelectedItem();
	$files->fileDelete($basePath, $currentPath, $selectedItem);
	echo $files->buildDirectoryList($basePath, $currentPath);
}

// Uploads the file.
function fileUpload()
{
	global $files;

	$basePath = getBasePath();
	$currentPath = getCurrentPathX();
	$files->fileUpload($basePath, $currentPath, 0);
	echo $files->buildDirectoryList($basePath, $currentPath);
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
		$selectedItem, html::getBaseUrl(), '/modules/' .
		$moduleFilename);
	$ajax->sendCommand(46, $url);
}


?>