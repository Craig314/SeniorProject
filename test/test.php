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
	$title = 'Test Page';
	$url = html::getBaseURL();
	$fname = 'test.php';
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
		'datepick' => true,
	);
	$jsfiles = array(
		'/js/baseline/common.js',
		'/test/test.js',
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
			case 20:
				var_dump($_SERVER, $_POST);
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
		array(
			'type' => html::TYPE_HEADING,
			'message1' => 'HTML Generator',
			'message2' => 'System Test',
			'warning' => 'Test Warning',
		),
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
					'class' => html::BTNCLR_WHITE,
					'action' => 'setStatusDefault()',
				),
				array(
					'name' => 'btn02',
					'dispname' => 'Set Ok',
					'class' => html::BTNCLR_GREEN,
					'action' => 'setStatusOk()',
				),
				array(
					'name' => 'btn03',
					'dispname' => 'Set Warning',
					'class' => html::BTNCLR_YELLOW,
					'action' => 'setStatusWarn()',
				),
				array(
					'name' => 'btn04',
					'dispname' => 'Set Error',
					'class' => html::BTNCLR_RED,
					'action' => 'setStatusError()',
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
			// Date Picker Test
			'type' => html::TYPE_DATETIME,
			'label' => 'Test Field 3',
			'name' => 'field3',
			'lsize' => 2,
			'fsize' => 4,
			'icon' => 'calendar',
			'tooltip' => 'Enter a date.  Datepicker will popup.',
			'date_highlight' => true,
			'date_autoclose' => true,
			'date_todaybtn' => true,
			'date_clearbtn' => true,
			//'date_format' => 'dd/mm/yyyy',
			'value' => time(),
			//'disable' => true,
		),
		array(
			// Pulldown Menu
			'type' => html::TYPE_PULLDN,
			'label' => 'Test Field 4',
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
			'label' => 'Test Field 5',
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
					'class' => html::BTNCLR_WHITE,
					'onclick' => '',
				),
				array(
					'name' => 'btn12',
					'dispname' => 'Primary',
					'class' => html::BTNCLR_BLUE,
					'onclick' => '',
				),
				array(
					'name' => 'btn13',
					'dispname' => 'Success',
					'class' => html::BTNCLR_GREEN,
					'onclick' => '',
				),
				array(
					'name' => 'btn14',
					'dispname' => 'Info',
					'class' => html::BTNCLR_LTBLUE,
					'onclick' => '',
				),
				array(
					'name' => 'btn15',
					'dispname' => 'Warning',
					'class' => html::BTNCLR_YELLOW,
					'onclick' => '',
				),
				array(
					'name' => 'btn16',
					'dispname' => 'Danger',
					'class' => html::BTNCLR_RED,
					'onclick' => '',
				),
			),
		),
		array(		// Buttons
			'type' => html::TYPE_BUTTON,
			'width' => 4,
			'direction' => 0,
			'button_data' => array(
				array(
					'name' => 'btn20',
					'dispname' => 'None',
					'class' => html::BTNCLR_GREY,
					'onclick' => '',
				),
				array(
					'name' => 'btn21',
					'dispname' => 'Default',
					'class' => html::BTNCLR_WHITE,
					'onclick' => '',
				),
				array(
					'name' => 'btn22',
					'dispname' => 'Primary',
					'class' => html::BTNCLR_BLUE,
					'onclick' => '',
				),
				array(
					'name' => 'btn23',
					'dispname' => 'Success',
					'class' => html::BTNCLR_GREEN,
					'onclick' => '',
				),
				array(
					'name' => 'btn24',
					'dispname' => 'Info',
					'class' => html::BTNCLR_LTBLUE,
					'onclick' => '',
				),
				array(
					'name' => 'btn25',
					'dispname' => 'Warning',
					'class' => html::BTNCLR_YELLOW,
					'onclick' => '',
				),
				array(
					'name' => 'btn26',
					'dispname' => 'Danger',
					'class' => html::BTNCLR_RED,
					'onclick' => '',
				),
				array(
					'name' => 'btn27',
					'dispname' => 'Link',
					'class' => html::BTNCLR_LINK,
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
			'fsize' => 3,
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

		array(
			'type' => html::TYPE_FSETOPEN,
			'name' => 'Block 5: Radio Buttons',
		),
		array(
			'type' => html::TYPE_RADIO,
			'name' => 'RadioButtons',
			'data' => array(
				'Button 1' => 'btn1',
				'Button 2' => 'btn2',
				'Button 3' => 'btn3',
				'Button 4' => 'btn4',
			),
		),
		array('type' => html::TYPE_FSETCLOSE),
		array('type' => html::TYPE_VTAB5),

		array(
			'type' => html::TYPE_FSETOPEN,
			'name' => 'Block 6: Radio Selection Table',
		),
		array('type' => html::TYPE_RADTABLE,
			'name' => 'RadioSelectionTable',
			'mode' => 0,
			'hover' => true,
			'condense' => true,
			'clickset' => true,
			'titles' => array(
				'Data 1',
				'Data 2',
				'Data 3',
				'Data 4'
			),
			'tdata' => array(
				array(
					'one',
					'1.1',
					'1.2',
					'1.3',
					'1.4',
				),
				array(
					'two',
					'2.1',
					'2.2',
					'2.3',
					'2.4',
				),
				array(
					'three',
					'3.1',
					'3.2',
					'3.3',
					'3.4',
				),
				array(
					'four',
					'4.1',
					'4.2',
					'4.3',
					'4.4',
				),
			),
		),
		array('type' => html::TYPE_FSETCLOSE),


		array(
			'type' => html::TYPE_FSETOPEN,
			'name' => 'Block 7: Checkbox Selection Table',
		),
		array('type' => html::TYPE_RADTABLE,
			'name' => 'CheckSelectionTable',
			'mode' => 1,
			'hover' => true,
			'condense' => true,
			'chkbox' => true,
			'clickset' => true,
			'titles' => array(
				'Data 1',
				'Data 2',
				'Data 3',
				'Data 4'
			),
			'tdata' => array(
				array(
					'one',
					'1.1',
					'1.2',
					'1.3',
					'1.4',
				),
				array(
					'two',
					'2.1',
					'2.2',
					'2.3',
					'2.4',
				),
				array(
					'three',
					'3.1',
					'3.2',
					'3.3',
					'3.4',
				),
				array(
					'four',
					'4.1',
					'4.2',
					'4.3',
					'4.4',
				),
			),
		),
		array('type' => html::TYPE_FSETCLOSE),

		array(
			'type' => html::TYPE_FSETOPEN,
			'name' => 'Block 8: Click Selection Table',
		),
		array('type' => html::TYPE_RADTABLE,
			'name' => 'ClickSelectionTable',
			'mode' => 2,
			'hover' => true,
			'condense' => true,
			'chkbox' => 2,
			'clickset' => true,
			'titles' => array(
				'Data 1',
				'Data 2',
				'Data 3',
				'Data 4',
			),
			'tdata' => array(
				array(
					'one',
					'1.1',
					'1.2',
					'1.3',
					'1.4',
				),
				array(
					'two',
					'2.1',
					'2.2',
					'2.3',
					'2.4',
				),
				array(
					'three',
					'3.1',
					'3.2',
					'3.3',
					'3.4',
				),
				array(
					'four',
					'4.1',
					'4.2',
					'4.3',
					'4.4',
				),
			),
		),
		array('type' => html::TYPE_FSETCLOSE),

		array(
			'type' => html::TYPE_FSETOPEN,
			'name' => 'Block 9: Data Table',
		),
		array('type' => html::TYPE_DATATAB,
			'size' => 1,
			'hover' => true,
			'condense' => true,
			'clickset' => true,
			'titles' => array(
				array(
					'name' => 'Data 1',
					'type' => 1,
					'prefix' => 'data',
				),
				array(
					'name' => 'Data 2',
					'type' => 0,
				),
				array(
					'name' => 'Data 3',
					'type' => 0,
				),
				array(
					'name' => 'Data 4',
					'type' => 0,
				),
				array(
					'name' => 'Data 5',
					'type' => 0,
				),
				array(
					'name' => 'Data 6',
					'type' => 1,
					'prefix' => 'xdata',
				),
			),
			'tdata' => array(
				array(
					'one',
					'1.1',
					'1.2',
					'1.3',
					'1.4',
					'one',
				),
				array(
					'two',
					'2.1',
					'2.2',
					'2.3',
					'2.4',
					'two',
				),
				array(
					'three',
					'3.1',
					'3.2',
					'3.3',
					'3.4',
					'three',
				),
				array(
					'four',
					'4.1',
					'4.2',
					'4.3',
					'4.4',
					'four',
				),
			),
			'value' => array(
				// The 0-3 is for each row.
				0 => array(
					4, 0, 0, 0, 0, 11,	// <- data for each column in the row.
				),
				1 => array(
					3, 0, 0, 0, 0, 12,
				),
				2 => array(
					2, 0, 0, 0, 0, 13,
				),
				3 => array(
					0 => 1,
					5 => 14,
				),
			),
		),
		array('type' => html::TYPE_FSETCLOSE),




		array(
			'type' => html::TYPE_SBUTTON,
			'width' => 4,
			'offset' => 4,
			'name' => 'submit',
			'dispname' => 'Submit',
			'class' => html::BTNCLR_BLUE,
			'action' => 'testSubmit()',
		),
		array('type' => html::TYPE_FORMCLOSE),
		array('type' => html::TYPE_WDCLOSE),
		array('type' => html::TYPE_BOTB2)
	);

	// Render
	echo html::pageAutoGenerate($data);
}

?>