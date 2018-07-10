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

*/



// These variables must be set for every module. The variable moduleId
// must be a unique positive integer. Module IDs < 1000 are reserved for
// system use.  Therefore application module IDs will start at 1000.
$moduleFilename = 'modedit.php';
$moduleTitle = 'Module Data Editor';
$moduleId = 3;

// These are the data editing modes.
const MODE_VIEW	= 0;
const MODE_UPDATE	= 1;
const MODE_INSERT	= 2;
const MODE_DELETE	= 3;

// This setting indicates that a file will be used instead of the
// default template.  Set to the name of the file to be used.
//$inject_html_file = '../dat/somefile.html';
$htmlInjectFile = false;

// Order matter here.  The modhead library needs to be loaded first.
// If additional libraries are needed, then load them afterwards.
const DIR = '../libs/';
require_once DIR . 'modhead.php';

// Called when the client sends a GET request to the server.
// This call comes from modhead.php.
function loadInitialContent()
{
	global $htmlInjectFile;

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
		$funcBar = array(
			'Add' => 'addModule',
			'Edit' => 'editModule',
			'Delete' => 'deleteModule',
			'View' => 'viewModule',
		);

		// jsfiles is an associtive array which contains additional
		// JavaScript filenames that should be included in the head
		// section of the HTML page.
		$jsFiles = array(
			'/js/modedit.js',
		);

		// cssfiles is an associtive array which contains additional
		// cascading style sheets that should be included in the head
		// section of the HTML page.
		// $cssFiles = array();

		// The final option, htmlFlags, is an array that holds the names
		// of supported options.  Currently, those options are checkbox,
		// datepick, and tooltip.
		// $htmlFiles = array(
		// 	'checkbox',
		// 	'datepick',
		// 	'tooltip',
		// );
	
		//html::loadTemplatePage($moduleTitle, $htmlUrl, $moduleFilename,
		//  $left, $right, $funcBar, $jsFiles, $cssFiles, $htmlFlags);
		html::loadTemplatePage($moduleTitle, $baseUrl, $moduleFilename,
			$left, '', $funcBar, $jsFiles, '', '');
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
	global $baseUrl;
	global $dbconf;
	global $herr;

	// Dump the module database and process it.
	$rxm = $dbconf->queryModuleAll();
	if ($rxm == false)
	{
		if ($herr->checkState())
			handleError($herr->errorGetMessages());
		else
			handleError('There are no modules in the database to edit.');
	}

	// Generate selection table.
	$list = array(
		'type' => html::TYPE_RADTABLE,
		'titles' => array(
			'Name',
			'ID',
			'File',
			'Icon',
			'Active',
			'Vendor',
			'All Users',
		),
		'tdata' => array(),
	);
	foreach($rxm as $kx => $vx)
	{
		$tdata = array(
			$vx['moduleid'],	// Needed for the radio button.
			$vx['name'],
			$vx['moduleid'],
			$vx['filename'],
			$vx['iconname'],
			$vx['active'],
			$vx['vendor'],
			$vx['allusers'],
		);
		array_push($list['tdata'], $tdata);
	}

	// Generate rest of page.
	$data = array(
		array(
			'type' => html::TYPE_HEADING,
			'message1' => 'Module Data Editor',
			'warning' => 'Editing a module can have drastic consequences on application functionality.<br>Proceed With Caution.',
		),
		array('type' => html::TYPE_TOPB1),
		array('type' => html::TYPE_WD75OPEN),
		array('type' => html::TYPE_FORMOPEN),
		$list,
		array('type' => html::TYPE_FORMCLOSE),
		array('type' => html::TYPE_WDCLOSE),
		array('type' => html::TYPE_BOTB1)
	);

	// Render
	html::pageAutoGenerate($data);

	exit(0);
}

// Called when the initial command processor doesn't have the
// command number. This call comes from modhead.php.
function commandProcessor($commandId)
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

// Generate generic form page.
function formPage($mode, $rxa)
{
	// Set these
	$dispName = '';
	$hideName = '';

	// Determine the editing mode.
	switch($mode)
	{
		case MODE_VIEW:			// View
			$msg1 = '';
			$msg2 = '';
			$warn = '';
			$btnset = html::BTNTYP_VIEW;
			$action = '';
			$hideValue = '';
			break;
		case MODE_UPDATE:		// Update
			$msg1 = '';
			$msg2 = '';
			$warn = '';
			$btnset = html::BTNTYP_UPDATE;
			$action = '';
			$hideValue = '';
			break;
		case MODE_INSERT:		// Insert
			$msg1 = '';
			$msg2 = '';
			$warn = '';
			$btnset = html::BTNTYP_INSERT;
			$action = '';
			$hideValue = '';
			break;
		case MODE_DELETE:		// Delete
			$msg1 = '';
			$msg2 = '';
			$warn = '';
			$btnset = html::BTNTYP_DELETE;
			$action = '';
			$hideValue = '';
			break;
		default:
			handleError('Internal Error: Contact your administrator.  XX82747');
			break;
	}

	// Build out the form array.
	$data = array(
		array(
			'type' => html::TYPE_HEADING,
			'message1' => $msg1,
			'message2' => $msg2,
			'warning' => $warn,
		),
		array('type' => html::TYPE_TOPB2),
		array('type' => html::TYPE_WD75OPEN),
		array('type' => html::TYPE_FORMOPEN),

		// Enter custom field data here.


		array(
			'type' => html::TYPE_ACTBTN,
			'dispname' => $dispName,
			'btnset' => $btnset,
			'action' => $action,
		),
		array('type' => html::TYPE_FORMCLOSE),
		array('type' => html::TYPE_WDCLOSE),
		array('type' => html::TYPE_BOTB2)
	);

	// Render
	html::pageAutoGenerate($data);
}


?>