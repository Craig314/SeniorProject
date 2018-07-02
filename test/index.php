<?php


require_once '../libs/confload.php';
require_once '../libs/session.php';
require_once '../libs/security.php';
require_once '../libs/html.php';
require_once '../libs/ajax.php';

$session->start('', '', '', '');
html::checkRequestPort(true);
if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
	// Initial Call
	$title = 'Template Page';
	$url = html::getBaseURL();
	$fname = 'index.php';
	$left = array(
		'Home' => 'returnHome',
	);
	$fbar = array(
		'Item 1' => 'something',
		'Item 2' => 'something',
		array(
			'Item 3.1' => 'something',
			'Item 3.2' => 'something'
			),
		array(
			'Item 4.1' => 'something',
			'Item 4.2' => 'something',
			'Item 4.3' => 'something',
			'Item 4.4' => 'something'
			)
		);
	$flags = array(
		'checkbox' => true,
		'tooltip' => true,
	);
	$jsfiles = array(
		'/js/index.js',
	);

	html::loadTemplatePage($title, $url, $fname, $left, '', $fbar, $jsfiles, '', $flags);
}
else if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	if (isset($_POST['COMMAND']))
	{
		switch((int)$_POST['COMMAND'])
		{
			case -1:
				content();
				break;
			case -2:
				$ajax->sendCode(200, 'Heartbeat Ok');
				break;
			case -3:
				$ajax->sendCode(200, 'Logout Ok');
				break;
			case -4:
				$ajax->sendCode(200, 'Go Home Ok');
				break;
			default:

		}
	}
}
else
{
	echo "unknown method";
	exit(1);
}





// Additional Content
function content()
{
	$data = array(
		array('type' => html::TYPE_TOPB1),
		array('type' => html::TYPE_WD75OPEN),
		array('type' => html::TYPE_FORMOPEN),
		array(
			'type' => html::TYPE_FSETOPEN,
			'name' => 'Block 1: Text Fields'
		),
		array(
			// Text Box
			'type' => html::TYPE_TEXT,
			'label' => 'Test Field 1',
			'default' => 'Default okay field value',
			'name' => 'field1',
			'icon' => 'user',
			'lsize' => 2,
			'fsize' => 9,
			'state' => html::STAT_OK,
			'tooltip' => 'Enter User Name',
		),
		array(
			'type' => html::TYPE_BUTTON,
			'width' => 2,
			'size' => 4,
			'direction' => 1,
			'nospace' => false,
			'button_data' => array(
				array(
					'name' => 'btn01',
					'dispname' => 'Set Default',
					'type' => 'default',
					'onclick' => 'setStatusDefault()',
				),
				array(
					'name' => 'btn02',
					'dispname' => 'Set Ok',
					'type' => 'success',
					'onclick' => 'setStatusOk()',
				),
				array(
					'name' => 'btn03',
					'dispname' => 'Set Warning',
					'type' => 'warning',
					'onclick' => 'setStatusWarn()',
				),
				array(
					'name' => 'btn04',
					'dispname' => 'Set Error',
					'type' => 'danger',
					'onclick' => 'setStatusError()',
				),
			),
		),
		array(
			// Password Box
			'type' => html::TYPE_PASS,
			'label' => 'Test Field 2',
			'default' => 'Default error field value',
			'name' => 'field2',
			'icon' => 'lock',
			'lsize' => 2,
			'fsize' => 9,
			'state' => html::STAT_ERROR,
			'tooltip' => 'Enter Password',
		),
		array(
			// Pulldown Menu
			'type' => html::TYPE_PULLDN,
			'label' => 'Test Field 3',
			'default' => 3,
			'name' => 'list1',
			'icon' => 'check',
			'lsize' => 2,
			'fsize' => 9,
			'state' => html::STAT_WARN,
			'optlist' => array(
				'One' => 1,
				'Two' => 2,
				'Three' => 3,
				'Four' => array(
					'a' => 'a',
					'b' => 'b',
					'c' => 'c',
					'd' => 'd',
				),
				'Five' => 5,
			),
			'tooltip' => 'Select Something',
		),
		array(
			// Text Area
			'type' => html::TYPE_AREA,
			'label' => 'Test Field 4',
			'default' => 'Default field value',
			'name' => 'field4',
			'icon' => 'user',
			'lsize' => 2,
			'fsize' => 9,
			'rows' => 5,
			'state' => html::STAT_DEFAULT,
			'tooltip' => 'Type Something',
		),
		array('type' => html::TYPE_FSETCLOSE),


		array(
			'type' => html::TYPE_FSETOPEN,
			'name' => 'Block 2: Buttons',
		),
		array(
			'type' => html::TYPE_BUTTON,
			'width' => 2,
			'size' => 0,
			'direction' => 1,
			'nospace' => true,
			'button_data' => array(
				array(
					'name' => 'btn11',
					'dispname' => 'Default',
					'type' => 'default',
					'onclick' => '',
				),
				array(
					'name' => 'btn12',
					'dispname' => 'Primary',
					'type' => 'primary',
					'onclick' => '',
				),
				array(
					'name' => 'btn13',
					'dispname' => 'Success',
					'type' => 'success',
					'onclick' => '',
				),
				array(
					'name' => 'btn14',
					'dispname' => 'Info',
					'type' => 'info',
					'onclick' => '',
				),
				array(
					'name' => 'btn15',
					'dispname' => 'Warning',
					'type' => 'warning',
					'onclick' => '',
				),
				array(
					'name' => 'btn16',
					'dispname' => 'Danger',
					'type' => 'danger',
					'onclick' => '',
				),
			),
		),
		array(
			'type' => html::TYPE_BUTTON,
			'width' => 1,
			'direction' => 0,
			'button_data' => array(
				array(
					'name' => 'btn21',
					'dispname' => 'Default',
					'type' => 'default',
					'onclick' => '',
				),
				array(
					'name' => 'btn22',
					'dispname' => 'Primary',
					'type' => 'primary',
					'onclick' => '',
				),
				array(
					'name' => 'btn23',
					'dispname' => 'Success',
					'type' => 'success',
					'onclick' => '',
				),
				array(
					'name' => 'btn24',
					'dispname' => 'Info',
					'type' => 'info',
					'onclick' => '',
				),
				array(
					'name' => 'btn25',
					'dispname' => 'Warning',
					'type' => 'warning',
					'onclick' => '',
				),
				array(
					'name' => 'btn26',
					'dispname' => 'Danger',
					'type' => 'danger',
					'onclick' => '',
				),
			),
		),
		array('type' => html::TYPE_FSETCLOSE),


		array(
			'type' => html::TYPE_FSETOPEN,
			'name' => 'Block 3: Checkboxes',
		),
		array('type' => html::TYPE_CHECK,
			// Standard Checkbox
			'label' => 'Test Field 5',
			'default' => true,
			'name' => 'cb1',
			'lsize' => 3,
			'fsize' => 2,
		),
		array('type' => html::TYPE_CHECK,
			// Left Checkbox
			'label' => 'Test Field Left',
			'default' => true,
			'name' => 'cbLeft',
			'lsize' => 3,
			'fsize' => 2,
			'sidemode' => true,
			'side' => 0,
			'togglemode' => 1,
		),
		array('type' => html::TYPE_CHECK,
			// Right Checkbox
			'label' => 'Test Field Right',
			'default' => true,
			'name' => 'cbRight',
			'lsize' => 3,
			'fsize' => 2,
			'sidemode' => true,
			'side' => 1,
			'togglemode' => 2,
		),
		array('type' => html::TYPE_FSETCLOSE),


		array(
			'type' => html::TYPE_FSETOPEN,
			'name' => 'Block 4: Unordered List',
		),
		array(
			// Unordered, Nested List
			'type' => html::TYPE_BLIST,
			'data' => array(
				'list item 1',
				'list item 2',
				array(
					'list item 2.1',
					'list item 2.2',
					'list item 2.3',
					'list item 2.4',
				),
				'list item 3',
				'list item 4',
				'list item 5',
			)
		),
		array('type' => html::TYPE_FSETCLOSE),
		array('type' => html::TYPE_FORMCLOSE),
		array('type' => html::TYPE_WDCLOSE),
		array('type' => html::TYPE_BOTB2)
	);

	// Render
	html::pageAutoGenerate($data);
}

?>