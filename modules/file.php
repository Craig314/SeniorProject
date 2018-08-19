<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application 

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
$moduleFilename = 'file.php';

// The name of the module.  It shows in the title bar of the web
// browser and other places.
$moduleTitle = 'File Finder';

// $moduleId must be a unique positive integer. Module IDs < 1000 are
// reserved for system use.  Therefore application module IDs will
// start at 1000.
$moduleId = 8;

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
require_once BASEDIR . 'timedate.php';
require_once BASEDIR . 'utility.php';
require_once BASEDIR . 'mime.php';
require_once BASEDIR . 'modhead.php';

// Called when the client sends a GET request to the server.
// This call comes from modhead.php.
function loadInitialContent()
{
	global $htmlInjectFile;
	global $CONFIGVAR;
	global $vfystr;
	global $herr;

	// Additonal
	// This is probably the only time that we pass parameters with
	// the GET method, so we need to check if there is a download
	// request for it.
	if (count($_GET) > 0)
	{
		// Set the activity mode.
		$mode = NULL;
		if (isset($_GET['download'])) $mode = 'download';
		if (isset($_GET['view'])) $mode = 'view';

		// Process.
		switch($mode)
		{
			case 'download':
				$file = $_GET['download'];
				$filename = $CONFIGVAR['server_document_root']['value'] . $file;
				$result = $vfystr->strchk($filename, '', '', verifyString::STR_PATHNAME);
				if ($result == false)
					handleError($herr->errorGetMessage());

				// Send the file
				if (file_exists($filename)) {
					header('Content-Description: File Transfer');
					header('Content-Type: application/octet-stream');
					header('Content-Disposition: attachment; filename=' . basename($filename));
					header('Expires: 0');
					header('Cache-Control: must-revalidate');
					header('Pragma: public');
					header('Content-Length: ' . filesize($filename));
					readfile($filename);
				}
				else http_response_code(404);
				break;
			case 'view':
				$file = $_GET['view'];
				$filename = $CONFIGVAR['server_document_root']['value'] . $file;
				$result = $vfystr->strchk($filename, '', '', verifyString::STR_PATHNAME);
				if ($result == false)
					handleError($herr->errorGetMessage());
				
				if (file_exists($filename)) {
					$ctype = mimeTypes::determineMime($filename);
					header('Content-Type: ' . $ctype);
					header('Content-Disposition: inline');
					header('Expires: 0');
					header('Cache-Control: must-revalidate');
					header('Pragma: public');
					header('Content-Length: ' . filesize($filename));
					readfile($filename);
				}
				else http_response_code(404);
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
			'Git Pull' => 'gitPull',
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
				'Rename' => 'fileRename',
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
				'Delete' => 'directoryDelete',
			),
		);

		// jsfiles is an associtive array which contains additional
		// JavaScript filenames that should be included in the head
		// section of the HTML page.
		$jsFiles = array(
			'/js/file.js',
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
		if (file_exists($htmlInjectFile))
		{
			$result = readfile($htmlInjectFile);
		}
		else printErrorImmediately('Internal System Error: ' . $htmlInjectFile .
			' was not found.<br>Contact your system administrator.  XX34226');
	}
}

// Called when server is issued command -1. This call comes from
// modhead.php.
function loadAdditionalContent()
{
	$path = '/';
	buildDirectoryList($path);
	exit(0);
}

// Called when the initial command processor doesn't have the
// command number. This call comes from modhead.php.
function commandProcessor($commandId)
{
	global $ajax;

	switch ((int)$commandId)
	{
		case 1:
			directoryMoveUp();
			break;
		case 2:
			directoryMoveDown();
			break;
		case 3:
			directoryCreate();
			break;
		case 4:
			directoryRename();
			break;
		case 5:
			directoryDelete();
			break;
		case 10:
			fileUpload();
			break;
		case 11:
			fileDownload();
			break;
		case 12:
			fileView();
			break;
		case 13:
			fileRename();
			break;
		case 14:
			fileDelete();
			break;
		case 15:
			fileDetail();
			break;
		default:
			// If we get here, then the command is undefined.
			$ajax->sendCode(ajaxClass::CODE_NOTIMP,
				'The command ' . $commandId . ' has not been implemented');
			exit(1);
			break;
	}
}

// Moves up one directory
function directoryMoveUp()
{
	global $herr;
	global $vfystr;

	// Get the current path.
	$currentPath = getPostValue('hidden');
	if (empty($currentPath))
		handleError('Missing path data.');

	// Check to make sure that there are no invalid characters.
	$result = $vfystr->strchk($currentPath, '', '', verifyString::STR_PATHNAME);
	if ($result == false)
		handleError($herr->errorGetMessage());

	// Take the last directory out of the string.
	$pathArray = explode('/', $currentPath);
	unset($pathArray[count($pathArray) - 1]);
	$newPath = implode('/', $pathArray);

	// Check to make sure that we are allowed to go into the given
	// directory.
	$result = checkDirectory($newPath);
	if ($result == false)
		handleError('Filesystem Error: You are not allowed to exit ' .
			'the server document root.');

	// Show new listing
	buildDirectoryList($newPath);
}

// Moves down a directory
function directoryMoveDown()
{
	global $herr;
	global $vfystr;
	global $CONFIGVAR;


	// Set the base path.
	$basePath = $CONFIGVAR['server_document_root']['value'];

	// Get current path
	$currentPath = getPostValue('hidden');
	if (empty($currentPath))
		handleError('Missing path data.');

	// Get the selected item.
	$select = getPostValue('select_item');
	if ($select == NULL)
		handleError('You must select an item from the list in order ' .
			'to use this command.');
	
	// Check to make sure that there are no invalid characters.
	$result = $vfystr->strchk($select, '', '', verifyString::STR_FILENAME);
	if ($result == false)
		$herr->puterrmsg('Invalid characters in file/directory name.');
	$result = $vfystr->strchk($currentPath, '', '', verifyString::STR_PATHNAME);
	if ($result == false)
		$herr->puterrmsg('Invalid characters in directory path.');
	if ($herr->checkState())
		handleError($herr->errorGetMessage());

	// Generate new path
	if ($currentPath == '/')
		$newPath = '/' . $select;
	else
		$newPath = $currentPath . '/' . $select;

	// Check to make sure that the selected item is a directory.
	if (!is_dir($basePath . $newPath))
		handleError('Filesystem Error: You must select a directory ' .
			'in order to use this command.');

	// Check to make sure that we are allowed to go into the given
	// directory.
	$result = checkDirectory($newPath);
	if ($result == false)
		handleError('Filesystem Error: You are not allowed to exit the ' .
			'server document root.');

	// Show new listing
	buildDirectoryList($newPath);
}

// Creates a new directory.
function directoryCreate()
{
	global $herr;
	global $vfystr;
	global $CONFIGVAR;

	// Get the current path.
	$currentPath = getPostValue('hidden');
	if (empty($currentPath))
		handleError('Missing path data.');
	if ($currentPath == '/')
		$realPath = $CONFIGVAR['server_document_root']['value'];
	else
		$realPath = $CONFIGVAR['server_document_root']['value'] . $currentPath;

	// Get the directory name.
	$dirName = getPostValue('dirname');
	if (empty($dirName))
		handleError('Missing new directory name.');

	// Check to make sure that there are no invalid characters.
	$result = $vfystr->strchk($currentPath, '', '', verifyString::STR_PATHNAME);
	if ($result == false)
		$herr->puterrmsg('Invalid characters in directory path.');
	$result = $vfystr->strchk($dirName, '', '', verifyString::STR_FILENAME);
	if ($result == false)
		$herr->puterrmsg('Invalid characters in directory name.');
	if ($herr->checkState())
		handleError($herr->errorGetMessage());

	// Check to make sure that the current path is valid.
	$result = checkDirectory($realPath);
	if ($result == false)
		handleError('Filesystem Error: You are not allowed to exit the ' .
			'server document root.');
	
	// Everything is good, so create the directory and set the group.
	$result = mkdir($realPath . '/' . $dirName, 0775);
	if ($result == false)
		handleError('Filesystem Error: Make directory failed.');
	if (identOS() == 0)
	{
		$result = chgrp($realPath . '/' . $dirName, 'www');
		if ($result == false)
			handleError('Filesystem Error: Unable to set directory GID.');
	}
	
	// Refresh listing
	buildDirectoryList($currentPath);
}

// Renames an existing directory.
function directoryRename()
{
	global $herr;
	global $vfystr;
	global $CONFIGVAR;

	// Get the current path.
	$currentPath = getPostValue('hidden');
	if (empty($currentPath))
		handleError('Missing path data.');
	if ($currentPath == '/')
		$realPath = $CONFIGVAR['server_document_root']['value'];
	else
		$realPath = $CONFIGVAR['server_document_root']['value'] . $currentPath;

	// Get the selected item.
	$select = getPostValue('select_item');
	if ($select == NULL)
		handleError('You must select an item from the list in order ' .
			'to use this command.');
	
	// Get the directory name.
	$dirName = getPostValue('dirname');
	if (empty($dirName))
		handleError('Missing new directory name.');
	$dirName = $CONFIGVAR['server_document_root']['value'] . $dirName;

	// Check to make sure that there are no invalid characters.
	$result = $vfystr->strchk($currentPath, '', '', verifyString::STR_PATHNAME);
	if ($result == false)
		$herr->puterrmsg('Invalid characters in directory path.');
	$result = $vfystr->strchk($select, '', '', verifyString::STR_FILENAME);
	if ($result == false)
		$herr->puterrmsg('Invalid characters in old directory name.');
	$result = $vfystr->strchk($dirName, '', '', verifyString::STR_PATHNAME);
	if ($result == false)
		$herr->puterrmsg('Invalid characters in new directory name.');
	if ($herr->checkState())
		handleError($herr->errorGetMessage());

	// Check to make sure that the current path is valid.
	$result = checkDirectory($realPath);
	if ($result == false)
		handleError('Filesystem Error: You are not allowed to exit the ' .
			'server document root.');
	
	// Everything is good, so rename the directory
	$result = rename($realPath . '/' . $select, $realPath . '/' . $dirName);
	if ($result == false)
		handleError('Filesystem Error: Rename directory failed.');
	
	// Refresh listing
	buildDirectoryList($currentPath);
}

// Removes an existing directory.
function directoryDelete()
{
	global $herr;
	global $vfystr;
	global $CONFIGVAR;

	// Get the current path.
	$currentPath = getPostValue('hidden');
	if (empty($currentPath))
		handleError('Missing path data.');
	if ($currentPath == '/')
		$realPath = $CONFIGVAR['server_document_root']['value'];
	else
		$realPath = $CONFIGVAR['server_document_root']['value'] . $currentPath;

	// Get the selected item.
	$select = getPostValue('select_item');
	if ($select == NULL)
		handleError('You must select an item from the list in order ' .
			'to use this command.');
	
	// Check to make sure that there are no invalid characters.
	$result = $vfystr->strchk($currentPath, '', '', verifyString::STR_PATHNAME);
	if ($result == false)
		$herr->puterrmsg('Invalid characters in directory path.');
	$result = $vfystr->strchk($select, '', '', verifyString::STR_FILENAME);
	if ($result == false)
		$herr->puterrmsg('Invalid characters in directory name.');
	if ($herr->checkState())
		handleError($herr->errorGetMessage());

	// Check to make sure that the current path is valid.
	$result = checkDirectory($realPath);
	if ($result == false)
		handleError('Filesystem Error: You are not allowed to exit the ' .
			'server document root.');
	
	// check to make sure that the selected item is a directory.
	if (!is_dir($realPath . '/' . $select))
		handleError('Filesystem Error: This command can only be used with ' .
			'directories.');
	
	// Check to make sure that the directory is empty.
	$rxa = scandir($realPath . '/' . $select);
	if (count($rxa) > 2)
		handleError('Filesystem Error: Unable to remove directory ' .
			'because it is not empty.');

	// Everything is good, remove the directory.
	$result = rmdir($realPath . '/' . $select);
	if ($result == false)
		handleError('Filesystem Error: Remove directory failed.');
	
	// Refresh listing
	buildDirectoryList($currentPath);
}

// Views a text file.
function fileView()
{
	global $CONFIGVAR;
	global $baseUrl;
	global $moduleFilename;
	global $ajax;
	global $vfystr;
	global $herr;

	// Get the current path.
	$currentPath = getPostValue('hidden');
	if (empty($currentPath))
		handleError('Missing path data.');
	if ($currentPath == '/')
		$realPath = $CONFIGVAR['server_document_root']['value'];
	else
		$realPath = $CONFIGVAR['server_document_root']['value'] . $currentPath;

	// Get the selected item.
	$select = getPostValue('select_item');
	if ($select == NULL)
		handleError('You must select an item from the list in order ' .
			'to use this command.');
	
	// Check to make sure that there are no invalid characters.
	$result = $vfystr->strchk($currentPath, '', '', verifyString::STR_PATHNAME);
	if ($result == false)
		$herr->puterrmsg('Invalid characters in directory path.');
	$result = $vfystr->strchk($select, '', '', verifyString::STR_FILENAME);
	if ($result == false)
		$herr->puterrmsg('Invalid characters in directory name.');
	if ($herr->checkState())
		handleError($herr->errorGetMessage());

	// Check to make sure that the current path is valid.
	$result = checkDirectory($realPath);
	if ($result == false)
		handleError('Filesystem Error: You are not allowed to exit the ' .
			'server document root.');
	
	// Check to make sure that the filename is in fact a file and not
	// a directory.
	if (!is_file($realPath. '/' . $select))
		handleError('Filesystem Error: This command works with files only.<br>' .
			'It does not work with directories.');
	
	// Now build the URL
	$url = $baseUrl . '/modules/' . $moduleFilename . '?view=' . $currentPath
		. '/' . $select;

	// Initiate the download
	$ajax->sendCommand(47, $url);
}

// Provides details of the given file.
function fileDetail()
{
	global $CONFIGVAR;
	global $baseUrl;
	global $moduleFilename;
	global $ajax;
	global $vfystr;
	global $herr;

	// Get the current path.
	$currentPath = getPostValue('hidden');
	if (empty($currentPath))
		handleError('Missing path data.');
	if ($currentPath == '/')
		$realPath = $CONFIGVAR['server_document_root']['value'];
	else
		$realPath = $CONFIGVAR['server_document_root']['value'] . $currentPath;

	// Get the selected item.
	$select = getPostValue('select_item');
	if ($select == NULL)
		handleError('You must select an item from the list in order ' .
			'to use this command.');
	
	// Check to make sure that there are no invalid characters.
	$result = $vfystr->strchk($currentPath, '', '', verifyString::STR_PATHNAME);
	if ($result == false)
		$herr->puterrmsg('Invalid characters in directory path.');
	$result = $vfystr->strchk($select, '', '', verifyString::STR_FILENAME);
	if ($result == false)
		$herr->puterrmsg('Invalid characters in directory name.');
	if ($herr->checkState())
		handleError($herr->errorGetMessage());

	// Check to make sure that the current path is valid.
	$result = checkDirectory($realPath);
	if ($result == false)
		handleError('Filesystem Error: You are not allowed to exit the ' .
			'server document root.');
	
	$filename = $realPath . '/' . $select;
	if ($currentPath == '/')
		$filepath = $currentPath . $select;
	else
		$filepath = $currentPath . '/' . $select;
	$filestat = stat($filename);
	if ($filestat === false)
		handleError('Filesystem Error: Unable to get file status.');
	$finfo = finfo_open(FILEINFO_MIME);
	if (is_resource($finfo) == true)
	{
		$mtype = finfo_file($finfo, $filename, FILEINFO_MIME_TYPE);
		$mencode = finfo_file($finfo, $filename, FILEINFO_MIME_ENCODING);
	}
	else
	{
		$mtype = mimeTypes::determineMime($filename);
		$mencode = 'unknown';
	}
	
	// Now build the information text.
	$fileinfo = 'Name: ' . $select . chr(13);
	$fileinfo .= 'Path: ' . $currentPath . chr(13);
	$fileinfo .= 'Type: ' . determineFileType($filepath) . chr(13);
	$fileinfo .= 'MIME Type: ' . $mtype . chr(13);
	$fileinfo .= 'MIME Encoding: ' . $mencode . chr(13);
	$fileinfo .= 'Size: ' . $filestat['size'] . chr(13);
	$fileinfo .= 'Blocks: ' . $filestat['blocks'] . chr(13);
	$fileinfo .= 'Mode: ' . convertMode($filestat['mode']) . ' (' . $filestat['mode']
		. ')' . chr(13);
	$fileinfo .= 'UserID: ' . $filestat['uid'] . chr(13);
	$fileinfo .= 'GroupID: ' . $filestat['gid'] . chr(13);
	$fileinfo .= 'Link Count: ' . $filestat['nlink'] . chr(13);
	$fileinfo .= 'Access Time: ' . timedate::unix2canonical($filestat['atime']) . chr(13);
	$fileinfo .= 'Modify Time: ' . timedate::unix2canonical($filestat['mtime']) . chr(13);
	$fileinfo .= 'iNode  Time: ' . timedate::unix2canonical($filestat['ctime']) . chr(13);
	$fileinfo .= 'Device No: ' . $filestat['dev'] . chr(13);
	$fileinfo .= 'Device Type: ' . $filestat['rdev'] . chr(13);
	$fileinfo .= 'iNode No: ' . $filestat['ino'] . chr(13);
	$fileinfo .= 'Block Size: ' . $filestat['blksize'] . chr(13);

	// Initiate the download
	$ajax->sendCommand(48, $fileinfo);
}

// Renames an existing file.
function fileRename()
{
	global $herr;
	global $vfystr;
	global $CONFIGVAR;

	// Get the current path.
	$currentPath = getPostValue('hidden');
	if (empty($currentPath))
		handleError('Missing path data.');
	if ($currentPath == '/')
		$realPath = $CONFIGVAR['server_document_root']['value'];
	else
		$realPath = $CONFIGVAR['server_document_root']['value'] . $currentPath;

	// Get the selected item.
	$select = getPostValue('select_item');
	if ($select == NULL)
		handleError('You must select an item from the list in order ' .
			'to use this command.');
	
	// Get the directory name.
	$fileName = getPostValue('filename');
	if (empty($fileName))
		handleError('Missing new filename.');

	var_dump($_POST);

	// Check to make sure that there are no invalid characters.
	$result = $vfystr->strchk($currentPath, '', '', verifyString::STR_PATHNAME);
	if ($result == false)
		$herr->puterrmsg('Invalid characters in directory path.');
	$result = $vfystr->strchk($select, '', '', verifyString::STR_FILENAME);
	if ($result == false)
		$herr->puterrmsg('Invalid characters in old filename.');
	$result = $vfystr->strchk($fileName, '', '', verifyString::STR_PATHNAME);
	if ($result == false)
		$herr->puterrmsg('Invalid characters in new filename.');
	if ($herr->checkState())
		handleError($herr->errorGetMessage());

	// Check to make sure that the current path is valid.
	$result = checkDirectory($realPath);
	if ($result == false)
		handleError('Filesystem Error: You are not allowed to exit the ' .
			'server document root.');
	
	// Everything is good, so rename the file.
	$result = rename($realPath . '/' . $select, $realPath . '/' . $fileName);
	if ($result == false)
		handleError('Filesystem Error: Rename file failed.');
	
	// Refresh listing
	buildDirectoryList($currentPath);
}

// Removes a file.
function fileDelete()
{
	global $herr;
	global $vfystr;
	global $CONFIGVAR;

	// Get the current path.
	$currentPath = getPostValue('hidden');
	if (empty($currentPath))
		handleError('Missing path data.');
	if ($currentPath == '/')
		$realPath = $CONFIGVAR['server_document_root']['value'];
	else
		$realPath = $CONFIGVAR['server_document_root']['value'] . $currentPath;

	// Get the selected item.
	$select = getPostValue('select_item');
	if ($select == NULL)
		handleError('You must select an item from the list in order ' .
			'to use this command.');
	
	// Check to make sure that there are no invalid characters.
	$result = $vfystr->strchk($currentPath, '', '', verifyString::STR_PATHNAME);
	if ($result == false)
		$herr->puterrmsg('Invalid characters in directory path.');
	$result = $vfystr->strchk($select, '', '', verifyString::STR_FILENAME);
	if ($result == false)
		$herr->puterrmsg('Invalid characters in file name.');
	if ($herr->checkState())
		handleError($herr->errorGetMessage());

	// Check to make sure that the current path is valid.
	$result = checkDirectory($realPath);
	if ($result == false)
		handleError('Filesystem Error: You are not allowed to exit the ' .
			'server document root.');
	
	// check to make sure that the selected item is a file.
	if (!is_file($realPath . '/' . $select))
		handleError('Filesystem Error: This command can only be used with ' .
			'files.');
	
	// Everything is good, remove the file.
	$result = unlink($realPath . '/' . $select);
	if ($result == false)
		handleError('Filesystem Error: Remove file failed.');
	
	// Refresh listing
	buildDirectoryList($currentPath);
}

// Uploads the file.
function fileUpload()
{
	global $herr;
	global $vfystr;
	global $CONFIGVAR;

	// Sets the time limit.  Needed on Windows only.
	if (identOS() == 2) set_time_limit(14400);

	// Check the request method.  This function only works with the
	// PUT method.
	if ($_SERVER['REQUEST_METHOD'] != 'PUT')
		handleError('Protocol Error: Uploading files requires the ' .
			'PUT method.');

	// Retrieve the current path, if possible.
	$currentPath = getPostValue('hidden');
	if (empty($currentPath))
	{
		$currentPath = $_SERVER['HTTP_X_PATH'];
		if (empty($currentPath))
			handleError('Missing path data.');
	}
	if ($currentPath == '/')
		$realPath = $CONFIGVAR['server_document_root']['value'];
	else
		$realPath = $CONFIGVAR['server_document_root']['value'] . $currentPath;

	// Parse out our multipart form marker.
	$content = $_SERVER['CONTENT_TYPE'];
	$cxa = explode(';', $content);
	foreach ($cxa as $kx => $vx)
	{
		$position = strpos($vx, 'boundary=');
		if ($position === false) continue;
		$cxb = explode('=', $vx);
		break;
	}
	$markerStart = $cxb[1];
	$markerEnd = '--' . $cxb[1] . '--';
	unset($cxa, $cxb);

	// Save file to temp file
	$tempFile = tmpfile();
	if ($tempFile === false)
		handleError('Filesystem Error: Unable to open temporary file.' .
			'<br>XX23001');
	$inputStream = fopen('php://input', 'r');
	if ($inputStream === false)
		handleError('Network Error: Unable to open file input stream.' .
			'<br>XX23002');
	while (!feof($inputStream))
	{
		$data = fread($inputStream, BLOCKSIZE);
		if ($data === false)
			handleError('Network Error: Unable to read from network ' .
				'stream.<br>XX23003');
		$result = fwrite($tempFile, $data);
		if ($result === false)
			handleError('Filesystem Error: Unable to write to temporary ' .
				'file.<br>XX23004');
	}
	fclose($inputStream);
	fflush($tempFile);

	// Now perform the analysis on the temp file that we received
	// from the network.  The analysis phase basically looks for the
	// content boundary which denotes the beginning the multipart/form
	// header in the file and marks the location in an array.
	// This is used to split the temporary file into the individual
	// component files.
	$result = fseek($tempFile, 0, SEEK_SET);
	if ($result != 0)
		handleError('Filesystem Error: Unable to perform seek on temporary ' .
			'file.<br>XX23005');
	$formpart = array();
	$filepos = 0;
	while (!feof($tempFile))
	{
		// Read the tempfile data.
		$data = fread($tempFile, BLOCKSIZE);
		if ($data === false)
		{
			// If we get nothing, then we either got an eof or an error.
			if (feof($tempFile)) break;
			handleError('Filesystem Error: Unable to read temporary file.' .
				'<br>XX23006');
		}

		// Look for the marker.
		$position = strpos($data, $markerEnd);
		if ($position !== false) array_push($formpart, $position + $filepos);
		else
		{
			$position = strpos($data, $markerStart);
			if ($position !== false) array_push($formpart, $position + $filepos);
		}

		// If we hit the EOF, then we bail.
		if (feof($tempFile)) break;

		// Backup the file pointer just in case a marker was split across
		// reads.
		$result = fseek($tempFile, -BLOCKBACK, SEEK_CUR);
		if ($result != 0)
			handleError('Filesystem Error: Unable to perform seek on' .
			' temporary file.<br>XX23007');
		$filepos += BLOCKSIZE - BLOCKBACK;
	}

	// Now that we have everything that we need, we now jump to each
	// header location looking for filenames.  When we find one, we
	// compute the length to the next header and extract the data
	// into the file whose name is contained in the filename parameter.
	foreach($formpart as $kx => $vx)
	{
		// Set the file position to the start of the header.
		$result = fseek($tempFile, $vx, SEEK_SET);
		if ($result != 0)
			handleError('Filesystem Error: Unable to perform seek on' .
			' temporary file.<br>XX23008');

		// Read in the 4 lines of the header which consits of the
		// marker, content disposition, content type, and a blank
		// line.
		$header = array(
			0 => fgets($tempFile, BLOCKSIZE),
			1 => fgets($tempFile, BLOCKSIZE),
			2 => fgets($tempFile, BLOCKSIZE),
			3 => fgets($tempFile, BLOCKSIZE),
		);
		if (strcmp($header[0], $markerEnd) == 0) break;
		$filepos = ftell($tempFile);
		if ($filepos === false)
			handleError('Filesystem Error: Unable to retreive seek position' .
				' for temporary file.<br>XX23009');

		// The only one that we care about is 1.  If filename is not
		// present, then it's not a file and we skip it.
		$position = strpos($header[1], 'filename="');
		if ($position === false) continue;

		// Extract the filename.  Once we get to $fxa, because the filename
		// is in double quotes, it *should* be in index 1.
		$contDisp = explode(';', $header[1]);
		foreach($contDisp as $k2 => $v2)
		{
			$position = strpos($v2, 'filename');
			if ($position === false) continue;
			$fxa = explode('"', $v2);
			$filename = $fxa[1];
			$result = $vfystr->strchk($filename, '', '', verifyString::STR_FILENAME);
			if (!$result)
			{
				$herr->puterrmsg('Processing Error: Multi-Part form header is ' .
					'malformed.<br>XX23010 OFFSET=' . $filepos);
				handleError($herr->errorGetMessage());
			}
			break;
		}

		// Now that we have the filename, we need to get the location of the
		// next marker from $formpart which should be at $kx + 1.  This will
		// allow us to compute the length of the data stream that we need
		// to copy into the new file.
		$fileLength = $formpart[$kx + 1] - $filepos - 2;	// XXX May need adjustment.
		if ($fileLength < 0)
			handlerError('Processing Error: File length is negative!<br>' .
			'XX23011 FILE=' . $filename);

		// Now we copy the data from the temp file over to the new file.
		$outputStream = fopen($realPath . '/' . $filename, 'w');
		if ($outputStream === false)
			handleError('Filesystem Error: Unable to open output stream for ' .
				'writing<br>XX23012 FILE=' . $filename);
		while ($fileLength > 0)
		{
			if ($fileLength < BLOCKSIZE)
			{
				$data = fread($tempFile, $fileLength);
				if ($data === false)
					handleError('Filesystem Error: Unable to read from ' .
						'temporary file.<br>XX23013');
				$fileLength = 0;
			}
			else
			{
				$data = fread($tempFile, BLOCKSIZE);
				if ($data === false)
					handleError('Filesystem Error: Unable to read from ' .
						'temporary file.<br>XX23014');
				$fileLength -= BLOCKSIZE;
			}
			fwrite($outputStream, $data);
			if ($result === false)
				handleError('Filesystem Error: Unable to write to output ' .
					'stream.<br>XX23004 FILE=' . $filename);
		}
		fflush($outputStream);
		fclose($outputStream);
	}

	// Close the temp file.
	fclose($tempFile);

	// Refresh listing
	buildDirectoryList($currentPath);

}

// Generates a URL for file download.
// The way that this works is that this function generates the URL which
// is sent to the client with a client command.  Then the client opens a
// popup window with the provided URL which vectors back into this script
// via GET method.  Then in the new window, headers are sent to kick off
// the download.
function fileDownload()
{
	global $CONFIGVAR;
	global $baseUrl;
	global $moduleFilename;
	global $ajax;

	// Get the current path.
	$currentPath = getPostValue('hidden');
	if (empty($currentPath))
		handleError('Missing path data.');
	if ($currentPath == '/')
		$realPath = $CONFIGVAR['server_document_root']['value'];
	else
		$realPath = $CONFIGVAR['server_document_root']['value'] . $currentPath;

	// Get the selected item.
	$select = getPostValue('select_item');
	if ($select == NULL)
		handleError('You must select an item from the list in order ' .
			'to use this command.');
	
	// Check to make sure that there are no invalid characters.
	$result = $vfystr->strchk($currentPath, '', '', verifyString::STR_PATHNAME);
	if ($result == false)
		$herr->puterrmsg('Invalid characters in directory path.');
	$result = $vfystr->strchk($select, '', '', verifyString::STR_FILENAME);
	if ($result == false)
		$herr->puterrmsg('Invalid characters in directory name.');
	if ($herr->checkState())
		handleError($herr->errorGetMessage());

	// Check to make sure that the current path is valid.
	$result = checkDirectory($realPath);
	if ($result == false)
		handleError('Filesystem Error: You are not allowed to exit the ' .
			'server document root.');
	
	// Check to make sure that the filename is in fact a file and not
	// a directory.
	if (!is_file($realPath. '/' . $select))
		handleError('Filesystem Error: This command works with files only.<br>' .
			'It does not work with directories.');
	
	// Now build the URL
	$url = $baseUrl . '/modules/' . $moduleFilename . '?download=' .
		$currentPath . '/' . $select;

	// Initiate the download.
	$ajax->sendCommand(46, $url);
}

// Returns the first argument match of a $_POST value.  If no
// values are found, then returns null.
function getPostValue(...$list)
{
	foreach($list as $param)
	{
		if (isset($_POST[$param])) return $_POST[$param];
	}
	return NULL;
}

// Checks if the given path is outside the web server document
// root directory.  Returns false if it is, true if not.
function checkDirectory($path)
{
	// Run a few checks
	if (strpos($path, '..', 0) != false) return false;

	return true;
}

// Determines the type of the given filename and returns a text
// string indicating the type.  Returns unknown if the type
// cannot be determined.
function determineFileType($file)
{
	global $CONFIGVAR;

	$basePath = $CONFIGVAR['server_document_root']['value'];
	$realname = $basePath . $file;
	if (is_dir($realname)) return 'Directory';
	if (is_link($realname)) return 'Symlink';
	if (is_executable($realname)) return 'Program';
	if (is_file($realname)) return 'File';
	return 'Unknown';
}

// Converts the given mode to rwx format for unix systems.
function convertMode($mode)
{
	$modelist = array('x', 'w', 'r', 'x', 'w', 'r', 'x', 'w', 'r');
	$text = '';
	for ($i = 0; $i < 9; $i++)
	{
		if (($mode & 0x00000001) != 0)
			$text = $modelist[$i] . $text;
		else
			$text = '-' . $text;
		$mode >>= 1;
	}
	return $text;
}

// Builds a file listing selection table of the given path.
function buildDirectoryList($path)
{
	global $CONFIGVAR;

	// Set default path if path variable is empty.
	if (empty($path)) $path = '/';

	// Set the current path as hidden data.
	$hidden = array(
		'type' => html::TYPE_HIDE,
		'fname'=> 'hiddenForm',
		'name' => 'hidden',
		'data' => $path,
	);

	// Set the real path.
	$realpath = $CONFIGVAR['server_document_root']['value'] . $path;


	// Check to make sure that we are allowed to go into the given
	// directory.
	$result = checkDirectory($realpath);
	if ($result == false)
		handleError('Filesystem Error: Target path is outside the server ' .
			'document root.');

	// Setup
	$filelist = array();
	
	// Get directory listing
	$file = scandir($realpath);
	if ($file === false)
		handleError('Filesystem Error: Unable to get directory listing: ' .
			$path);
	
	// Process entries
	foreach($file as $kx => $vx)
	{
		if ($vx == '.') continue;
		if ($vx == '..') continue;
		$stat = stat($realpath . '/' . $vx);
		if ($stat == false) continue;
		$temp = array(
			'name' =>		$vx,
			'type' =>		determineFileType($path . '/' . $vx),
			'size' =>		$stat['size'],
			'ctime' =>		timedate::unix2canonical($stat['mtime']),
			'mode' =>		convertMode($stat['mode']),
			'desc' =>
				'Name: ' .			$vx . '<br>' .
				'Type: ' .			determineFileType($path . '/' . $vx) . chr(13) .
				'Size: ' .			$stat['size'] . chr(13) .
				'Mode: ' .			convertMode($stat['mode']) .
					'  (' . $stat['mode'] . ')' . chr(13) .
				'UID: ' . 			$stat['uid'] . chr(13) .
				'GID: ' . 			$stat['gid'] . chr(13) .
				'Access Time: ' .	timedate::unix2canonical($stat['atime']) . chr(13) .
				'Modify Time: ' .	timedate::unix2canonical($stat['mtime']) . chr(13),
		);
		$filelist[$vx] = $temp;
	}
	
	// Setup selection table
	$list = array(
		'type' => html::TYPE_RADTABLE,
		'name' => 'select_item',
		'titles' => array(
			// Add column titles here
			'Name',
			'Type',
			'Size',
			'Changed',
			'Mode',
		),
		'tdata' => array(),
		'tooltip' => array(),
	);

	if (count($filelist) > 0)
	{
		foreach ($filelist as $kx => $vx)
		{
			$tdata = array(
				// These are the values that show up under the columns above.
				// The *FIRST* value is the value that is sent when a row
				// is selected.  AKA Key Field.
				$vx['name'],
				$vx['name'],
				$vx['type'],
				$vx['size'],
				$vx['ctime'],
				$vx['mode'],
			);
			array_push($list['tdata'], $tdata);
			array_push($list['tooltip'], $vx['desc']);
		}
	}

	// Generate rest of page.
	$data = array(
		$hidden,
		array(
			'type' => html::TYPE_HEADING,
			'message1' => 'Path: ',
			'message2' => $path,
			'warning' => 'Deleting files can have an adverse impact on the applicaiton.',
		),
		array('type' => html::TYPE_TOPB1),
		array('type' => html::TYPE_WD75OPEN),
		array(
			'type' => html::TYPE_HIDEOPEN,
			'name' => 'fileInputDiv',
			'hidden' => true,
		),
		array(
			'type' => html::TYPE_FILE,
			'bname' => 'fileSubmit',
			'fname' => 'fileInputForm',
			'name' => 'fileInput',
			'action' => 'fileUpload()',
			'fsize' => 8,
			'lsize' => 2,
		),
		array('type' => html::TYPE_TOPB2),
		array('type' => html::TYPE_HIDECLOSE),
		array(
			'type' => html::TYPE_FORMOPEN,
			'name' => 'select_table',
		),

		// Enter custom data here.
		$list,


		array('type' => html::TYPE_FORMCLOSE),
		array('type' => html::TYPE_WDCLOSE),
		array('type' => html::TYPE_BOTB1),
		array('type' => html::TYPE_VTAB10),
	);

	// Render
	echo html::pageAutoGenerate($data);
}

?>