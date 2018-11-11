<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application HTML Generation/Handling Library

This library uses a class with static methods to perform it's functions.
Do not instantiate.

This script file contains code dealing with HTTP, HTTPS, and HTML
aspects of a web application.

*/


require_once 'confload.php';
require_once 'session.php';
require_once 'timedate.php';


interface html_interface
{
	// Some misculanious constants.
	const CHECKFLAG_NAME = 'chkbox_';

	// Button Color Constants
	const BTNCLR_GREY		= 0;
	const BTNCLR_WHITE		= 1;
	const BTNCLR_BLUE		= 2;
	const BTNCLR_GREEN		= 3;
	const BTNCLR_LTBLUE		= 4;
	const BTNCLR_YELLOW		= 5;
	const BTNCLR_RED		= 6;
	const BTNCLR_LINK		= 7; // White button with blue underlined text

	// Field error status.  These constants must match values in both
	// error.php and ajax.js
	const STAT_NONE			= 0;
	const STAT_DEFAULT		= 0;
	const STAT_OK			= 1;
	const STAT_WARN			= 2;
	const STAT_ERROR		= 3;
	const STAT_GENERAL		= 4;

	// Predefined button set types for insertActionButtons.
	const BTNTYP_NONE		= 0;
	const BTNTYP_VIEW		= 1;
	const BTNTYP_UPDATE		= 2;
	const BTNTYP_INSERT		= 3;
	const BTNTYP_DELETE		= 4;

	// Field type definitions used by pageAutoGenerate.
	const TYPE_HIDE			= 0;	// Hidden input field
	const TYPE_TEXT			= 1;	// Text input field
	const TYPE_PASS			= 2;	// Password input field
	const TYPE_DATE			= 3;	// Date input field (datepicker plugin)
	const TYPE_FILE			= 4;	// File input field
	const TYPE_AREA			= 5;	// Textarea tag input field
	const TYPE_CHECK		= 6;	// Checkbox
	const TYPE_RADIO		= 7;	// Radio button group
	const TYPE_PULLDN		= 8;	// Pulldown selection menu/list
	const TYPE_BLIST		= 9;	// Standard nested list
	const TYPE_BUTTON		= 10;	// Button group
	const TYPE_SBUTTON		= 11;	// Single button
	const TYPE_ACTBTN		= 12;	// Action button set
	const TYPE_RADTABLE		= 13;	// Radio button item selection table
	const TYPE_HEADING		= 14;	// Banner headings
	const TYPE_CHECKLIST	= 15;	// Checkbox Lists
	const TYPE_IMAGE		= 16;	// Image File
	const TYPE_DATETIME		= 17;	// Date & Time input field
	const TYPE_BARPROG		= 18;	// Progress bar
	const TYPE_BARMETER		= 19;	// Meter (gauge) bar

	const TYPE_FORMOPEN		= 30;	// Open form with name
	const TYPE_FORMCLOSE	= 31;	// Close form
	const TYPE_FSETOPEN		= 32;	// Open field set with title
	const TYPE_FSETCLOSE	= 33;	// Close field set
	const TYPE_HIDEOPEN		= 34;	// Opens a named hidden block
	const TYPE_HIDECLOSE	= 35;	// Close named hidden block

	const TYPE_TOPB1		= 40;	// Top border type 1
	const TYPE_TOPB2		= 41;	// Top border type 2
	const TYPE_BOTB1		= 42;	// Bottom border type 1
	const TYPE_BOTB2		= 43;	// Bottom border type 2

	const TYPE_WD75OPEN		= 50;	// Open a area of 75% width
	const TYPE_WD50OPEN		= 51;	// Open a area of 50% width
	const TYPE_WDCLOSE		= 59;	// Close width area
	const TYPE_VTAB5		= 60;	// Vertical tab 5%
	const TYPE_VTAB10		= 61;	// Vertical tab 10%

	// Utility
	static public function initialize();
	static public function setLFDefaultSize($label, $field);
	static public function getLabelDefaultSize();
	static public function getFieldDefaultSize();
	static public function redirect($filename);
	static public function ishttps();
	static public function buildURL($filename);
	static public function getBaseURL();
	static public function checkRequestPort();
	static public function sendCode($code);

	// HTML Input Controls
	static public function insertToken($token);
	static public function insertFieldHidden($data);
	static public function insertFieldText($data);
	static public function insertFieldPassword($data);
	static public function insertFieldDate($data);
	static public function insertFieldFile($data);
	static public function insertFieldTextArea($data);
	static public function insertFieldCheckbox($data);
	static public function insertRadioButtons($data);
	static public function insertFieldDropList($data);
	static public function insertList($data, $indent = "");
	static public function insertButtons($data);
	static public function insertButtonSingle($data);
	static public function insertActionButtons($data);
	static public function insertSelectionTable($data);
	static public function insertHeadingBanner($data);
	static public function insertCheckList($data);
	static public function insertImage($data);
	static public function insertFieldDateTime($data);
	static public function insertProgressBar($data);
	static public function insertMeterBar($data);

	// Other HTML Constructs
	static public function openForm($data);
	static public function closeForm();
	static public function openFieldset($data);
	static public function closeFieldset();
	static public function border1top();
	static public function border2top();
	static public function border1bottom();
	static public function border2bottom();
	static public function width50open();
	static public function width75open();
	static public function widthClose();
	static public function verticalTab5();
	static public function verticalTab10();

	// Templates and Engines
	static public function pageAutoGenerate($data);
	static public function loadTemplatePage($title, $url, $fname, $left,
		$right, $funcbar, $js_files, $css_files, $html_flags, $funcbar2 = NULL,
		$funcbar3 = NULL);
}


class html implements html_interface
{


	/* ******** CONSTANTS ******** */

	const DEFTYPE_TEXTBOX = 0;
	const DEFTYPE_PULLDOWN = 1;
	const DEFTYPE_CHECKBOX = 2;


	/* ******** ATTRIBUTES ******** */

	static private $lsize_default = false;
	static private $fsize_default = false;
	static private $base_url = NULL;
	static private $base_url2 = NULL;
	static private $networkPort = 0;
	static private $buttonClass = array(
		0 => '',
		1 => 'btn-default',
		2 => 'btn-primary',
		3 => 'btn-success',
		4 => 'btn-info',
		5 => 'btn-warning',
		6 => 'btn-danger',
		7 => 'btn-link',
	);


	/* ******** CONSTRUCTOR METHOD ******** */

	public function __constructor()
	{
		echo 'Internal Programming Error: HTML: Attempt to instantiate static class.';
		exit(1);
	}


	/* ******** PRIVATE METHODS ******** */


	// Helper function for insertFieldSelect
	static private function insertFieldSelectHelper($kx, $vx, $default)
	{
		if (strlen($default) != 0)
		{
			if (strcasecmp($default, $vx) != 0 && strcasecmp($default, $kx) != 0)
			{
				$html = "
						<option value=\"$vx\">$kx</option>";
			}
			else
			{
				$html = "
						<option value=\"$vx\" selected=\"selected\">$kx</option>";
			}
		}
		else
		{
				$html = "
						<option value=\"$vx\">$kx</option>";
		}
		return $html;
	}

	// Helper: Name and ID fields
	static private function helperNameId($data, &$name, &$forx)
	{
		if (!empty($data['name']))
		{
			$name = ' name="' . $data['name'] . '" id="' . $data['name'] . '"';
			$forx = ' for="' . $data['name'] . '"';
		}
		else
		{
			$name = NULL;
			$forx = '';
		}
	}

	// Helper: Data Control Mark (Most elements use this)
	static private function helperDCM($data, $type, &$dcmGL, &$dcmST, &$dcmMS)
	{
		if (!empty($data['name']))
		{
			$dcmGL = 'id="dcmGL-' . $data['name'] . '"';
			$dcmST = 'id="dcmST-' . $data['name'] . '"';
			$dcmMS = 'id="dcmMS-' . $data['name'] . '"';
		}
		else
		{
			$dcmGL = '';
			$dcmST = '';
			$dcmMS = '';
		}
	}

	// Helper: Field Value
	static private function helperValue($data, &$value)
	{
		if (isset($data['value']))
			$value = ' value="' . $data['value'] . '"';
		else
			$value = '';
	}

	// Helper: On-Click Event Action
	static private function helperOnEvent($data, &$event)
	{
		if (!empty($data['event']) && !empty($data['action']))
		{
			$event = ' ' . $data['event'] .  '="' . $data['action'] . '"';
		}
		else
		{
			$event = '';
		}
	}

	// Helper: Disabled
	static private function helperDisabled($data, &$disable)
	{
		if (!empty($data['disable']))
		{
			if ($data['disable'] == true) $disable = ' disabled';
			else $disable = '';
		}
		else $disable = '';
	}

	// Helper: State
	// ***NOTE*** State numbers *MUST* match in /js/ajax.js script
	// for the client side.
	static private function helperState($data, &$stx, &$gix)
	{
		if (!empty($data['state']))
		{
			switch ($data['state'])
			{
				case self::STAT_OK:
					$stx = ' has-success has-feedback';
					$gix = ' glyphicon-ok';
					break;
				case self::STAT_WARN:
					$stx = ' has-warning has-feedback';
					$gix = ' glyphicon-warning-sign';
					break;
				case self::STAT_ERROR:
					$stx = ' has-error has-feedback';
					$gix = ' glyphicon-remove';
					break;
				default:
					$stx = '';
					$gix = '';
					break;
			}
		}
		else
		{
			$stx = '';
			$gix = '';
		}
	}

	// Helper: Default Value
	static private function helperDefault($data, $type, &$default)
	{
		if (isset($data['default']))
		{
			switch($type)
			{
				case self::DEFTYPE_TEXTBOX:
					$default = ' placeholder="' . $data['default'] . '"';
					break;
				case self::DEFTYPE_PULLDOWN:
					$default = $data['default'];
					break;
				case self::DEFTYPE_CHECKBOX:
					if ($data['default']) $default = ' checked';
						else $default = '';
					break;
				default:
					$default = '';
					break;
			}
		}
		else $default = '';
	}

	// Helper: Text Label
	static private function helperLabel($data, &$label)
	{
		if (!empty($data['label'])) $label = $data['label']; else $label = '';
	}

	// Helper: Label Size Text
	static private function helperLabelSizeText($data, &$lclass)
	{
		if (!empty($data['lsize']))
		{
			$lclass = ' class="control-label col-xs-' . $data['lsize'] . ' text-right"';
		}
		else if (self::$lsize_default !== false)
		{
			$lclass = ' class="control-label col-xs-' . self::$lsize_default . ' text-right"';
		}
		else
		{
			$lclass = ' class="control-label text-right"';
		}
	}

	// Helper: Label Size Checkbox/Radio Button
	static private function helperLabelSizeCheck($data, &$lsize, &$lclass, &$lclassL, &$lclassR)
	{
		if (!empty($data['lsize']))
		{
			$lsize = $data['lsize'];
			$lclass = ' class="control-label col-xs-' . $data['lsize'] . '"';
			$lclassL = ' class="control-label col-xs-' . $data['lsize'] . ' text-left"';
			$lclassR = ' class="control-label col-xs-' . $data['lsize'] . ' text-right"';
		}
		else if (self::$lsize_default !== false)
		{
			$lsize = self::$lsize_default;
			$lclass = ' class="control-label col-xs-' . self::$lsize_default . '"';
			$lclassL = ' class="control-label col-xs-' . self::$lsize_default . ' text-left"';
			$lclassR = ' class="control-label col-xs-' . self::$lsize_default . ' text-right"';
		}
		else
		{
			$lsize = 0;
			$lclass = ' class="control-label"';
			$lclassL = ' class="control-label text-left"';
			$lclassR = ' class="control-label text-right"';
		}
	}

	// Helper: Field Size Text
	static private function helperFieldSizeText($data, &$fclass)
	{
		if (!empty($data['fsize'])) $fclass = ' class="input-group col-xs-' . $data['fsize'] . '"';
			else if (self::$fsize_default !== false)
				$fclass = ' class="input-group col-xs-' . self::$lsize_default . '"';
			else
				$fclass = ' class="input-group"';
	}

	// Helper: Field Size Checkbox/Radio Button
	static private function helperFieldSizeCheck($data, &$fsize, &$fclass)
	{
		if (!empty($data['fsize']))
		{
			$fsize = $data['fsize'];
			$fclass = ' class="col-xs-' . $data['fsize'] . '"';
		}
		else if (self::$fsize_default !== false)
		{
			$fsize = self::$fsize_default;
			$fclass = ' class="col-xs-' . self::$lsize_default . '"';
		}
		else
		{
			$fsize = 0;
			$fclass = '';
		}
	}

	// Helper: Tooltips
	static private function helperTooltip($data, &$tooltip)
	{
		if (!empty($data['tooltip'])) $tooltip = ' data-toggle="tooltip" data-html="true" title="' . $data['tooltip'] . '"';
			else $tooltip = '';
	}

	// Helper: Icons
	static private function helperIcon($data, &$icons, &$icond)
	{
		if (!empty($data['icon']))
		{
			$icond = ' class="glyphicon glyphicon-' . $data['icon'] . '"';
			$icons = ' class="input-group-addon"';
		}
		else
		{
			$icond = '';
			$icons = '';
		}
	}

	// Helper: Rows
	static private function helperRow($data, &$rows)
	{
		if (!empty($data['rows'])) $rows = $data['rows']; else $rows = 1;
	}

	// Helper: Side
	static private function helperSide($data, &$side)
	{
		if (isset($data['sidemode']) && isset($data['side']))
		{
			if ($data['sidemode'] == true) $side = $data['side'];
				else $side = 2;
		}
		else $side = 2;
	}

	// Helper: Toggle Mode
	static private function helperToggle($data, &$toggle)
	{
		if (!empty($data['togglemode'])) $toggle = $data['togglemode'];
			else $toggle = 0;
	}

	// Generates HTML for a general text field
	// Common function
	// name - Name and ID for the field
	// label - Text label for the field
	// icon - Icon to use.  For complete list, goto
	//   http://www.w3schools.com/bootstrap/bootstrap_ref_comp_glyphs.asp
	// state - State to display
	//   0 - normal
	//   1 - Ok
	//   2 - Warning
	//   3 - Error
	// lsize - Size of the label (Bootstrap)
	// fsize - Size of the field (Bootstrap)
	// default - Default field value
	// event - Event to watch for
	// action - Function to call when event happens
	// tooltip - Popup tool tip text
	static private function insertFieldTextCommon($type, $data)
	{
		// Check Input
		if (!is_array($data)) return;

		// Setup
		$name = NULL;
		$forx = NULL;
		$event = NULL;
		$disabled = NULL;
		$value = NULL;
		$stx = NULL;
		$gix = NULL;
		$default = NULL;
		$label = NULL;
		$lclass = NULL;
		$fclass = NULL;
		$lclass = NULL;

		$tooltip = NULL;
		$icond = NULL;
		$icons = NULL;
		$dcmGL = NULL;
		$dcmST = NULL;
		$dcmMS = NULL;

		// Parameters
		self::helperNameId($data, $name, $forx);
		self::helperDCM($data, 'text', $dcmGL, $dcmST, $dcmMS);
		self::helperOnEvent($data, $event);
		self::helperDisabled($data, $disabled);
		self::helperValue($data, $value);
		self::helperState($data, $stx, $gix);
		self::helperDefault($data, self::DEFTYPE_TEXTBOX, $default);
		self::helperLabel($data, $label);
		self::helperLabelSizeText($data, $lclass);
		self::helperFieldSizeText($data, $fclass);
		self::helperTooltip($data, $tooltip);
		self::helperIcon($data, $icons, $icond);

		// Combine
		$printout = $name . $value . $default . $event . $tooltip . $disabled;

		// Render
		if (empty($data['date']))
		{
			$html = "
		<div class=\"row\">
			<div $dcmST class=\"form-group $stx\">
				<label $lclass $forx>$label</label>
				<div $fclass>
					<span $icons><i $icond></i></span>
					<input type=\"$type\" class=\"form-control\" $printout>
					<span $dcmGL class=\"glyphicon $gix form-control-feedback\"></span>
					<span $dcmMS></span>
				</div>
			</div>
		</div>";
		}
		else
		{
			$datePickOptions = 'data-provide="datepicker"';
			if (!empty($data['date_format'])) $datePickOptions .= ' data-date-format="' . $data['date_format'] . '"';
				else $datePickOptions .= ' data-date-format="mm/dd/yyyy"';
			if (!empty($data['date_highlight'])) $datePickOptions .= ' data-date-today-highlight="true"';
			if (!empty($data['date_autoclose'])) $datePickOptions .= ' data-date-autoclose="true"';
			if (!empty($data['date_todaybtn'])) $datePickOptions .= ' data-date-today-btn="true"';
			if (!empty($data['date_clearbtn'])) $datePickOptions .= ' data-date-clear-btn="true"';
			$html = "
		<div class=\"row\">
			<div $dcmST class=\"form-group $stx\">
				<label $lclass $forx>$label</label>
				<div $fclass>
					<div class=\"input-group date\" $datePickOptions>
						<span $icons><i $icond></i></span>
						<input type=\"$type\" class=\"form-control\" $printout>
						<span class=\"input-group-addon\">
							<i class=\"glyphicon glyphicon-th\"></i>
						</span>
						<span $dcmGL class=\"glyphicon $gix form-control-feedback\"></span>
						<span $dcmMS></span>
					</div>
				</div>
			</div>
		</div>";
		}
		return $html;
	}




	/* ******** PUBLIC METHODS ******** */


	// Initializes the HTML module.  This should be called before
	// anything else.
	static public function initialize()
	{
		global $CONFIGVAR;

		// Build Base URL
		if ($CONFIGVAR['server_secure']['value'] == 1)
		{
			// Encrypted Connection
			$protocol = 'https';
			$defaultPort = 443;
			$port = $CONFIGVAR['server_https_port']['value'];
		}
		else
		{
			// Unencrypted Connection
			$protocol = 'http';
			$defaultPort = 80;
			$port = $CONFIGVAR['server_http_port']['value'];
		}
		$hostname = $CONFIGVAR['server_hostname']['value'];
		if ($port != $defaultPort)
			$url = $protocol . '://' . $hostname . ':' . $port;
		else
			$url = $protocol . '://' . $hostname;
		self::$base_url2 = $url . '/';
		self::$base_url = $url;
		self::$networkPort = $port;
		
		// Set default sizes
		self::$lsize_default = $CONFIGVAR['html_default_label_size']['value'];
		self::$fsize_default = $CONFIGVAR['html_default_field_size']['value'];
	}

	// Sets default Label/Field sizes.
	static public function setLFDefaultSize($label, $field)
	{
		if (!empty($label)) self::$lsize_default = $label;
		if (!empty($field)) self::$fsize_default = $field;
	}

	// Returns the default Label size.
	static public function getLabelDefaultSize()
	{
		return self::$lsize_default;
	}

	// Returns the default Field size.
	static public function getFieldDefaultSize()
	{
		return self::$fsize_default;
	}

	// Redirects the client web browser to the specified file name.
	static public function redirect($filename)
	{
		header("Location: " . self::$base_url . $filename);
	}

	// Checks to see if the connection is secure.
	// Returns true if it is, false if it is not.
	static public function ishttps()
	{
		global $CONFIGVAR;

		if (strcasecmp($_SERVER['REQUEST_SCHEME'], 'https') == 0)
			return true;
		if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'))
			return true;
		if ($_SERVER['SERVER_PORT'] == $CONFIGVAR['server_https_port']['value'])
			return true;

		// Above tests failed, so we are not on a secure connection.
		return false;
	}

	// Returns a fully qualified URL based on the given filename.
	static public function buildURL($filename)
	{
		return self::$base_url . $filename;
	}

	// Returns the base URL.
	static public function getBaseURL()
	{
		return self::$base_url;
	}

	// Checks the requested port against the application port
	// and redirects if necessary.
	static public function checkRequestPort()
	{
		global $CONFIGVAR;

		//var_dump($_SERVER, self::$networkPort);
		if ($_SERVER['SERVER_PORT'] != self::$networkPort)
		{
			self::redirect($_SERVER['REQUEST_URI']);
			exit(0);
		}
		if ($CONFIGVAR['server_secure']['value'] == 1)
		{
			if (!self::ishttps())
			{
				self::redirect($_SERVER['REQUEST_URI']);
				exit(0);
			}
		}
		else
		{
			if (self::ishttps())
			{
				self::redirect($_SERVER['REQUEST_URI']);
				exit(0);
			}
		}
		//echo '<br>checkRequestPort Finished<br>';
	}

	// Sends a response code to the client.
	static public function sendCode($code)
	{
		http_response_code($code);
	}

	// Inserts a security token in the page.
	static public function insertToken($token)
	{
		$html = "
	<div>
		<form id=\"token_form\">
			<input type=\"hidden\" name=\"token_data\" id=\"token_data\" value=\"$token\">
		</form>
	</div>";
		return $html;
	}

	// Incorporates a hidden form with a hidden field to pass
	// data between pages.
	// fname - Form name
	// name - Field name/Id
	// data - field data
	static public function insertFieldHidden($data)
	{
		if (!empty($data['fname'])) $fname = 'name="' . $data['fname'] . '"';
			else $fname = '';
		if (!empty($data['name'])) $name = 'name="' . $data['name'] . '" id="' . $data['name'] . '"';
			else $name = '';
		if (isset($data['data'])) $value = $data['data'];
			else $value = '';
		$html = "
	<div>
		<form $fname>
			<input type=\"hidden\" $name value=\"$value\">
		</form>
	</div>";
		return $html;
	}
	
	// Inserts a text field
	static public function insertFieldText($data)
	{
		return self::insertFieldTextCommon('text', $data);
	}

	// Inserts a password field
	static public function insertFieldPassword($data)
	{
		return self::insertFieldTextCommon('password', $data);
	}

	// Inserts a date field
	static public function insertFieldDate($data)
	{
		$data['date'] = true;
		return self::insertFieldTextCommon('text', $data);
	}

	// Inserts a file picker field
	static public function insertFieldFile($data)
	{
		$fclass = NULL;
		$lclass = NULL;
		$label = NULL;

		self::helperLabel($data, $label);
		self::helperLabelSizeText($data, $lclass);
		self::helperFieldSizeText($data, $fclass);
		if (!empty($data['fname'])) $fname = 'name="' . $data['fname'] . '"';
			else $fname = '';
		if (!empty($data['name'])) $name = 'id="' . $data['name'] . '"';
			else $name = '';
		if (!empty($data['bname'])) $bname = 'id="' . $data['bname'] . '"';
			else $bname = '';
		if (!empty($data['action'])) $action = ' onclick="' . $data['action'] . '"';
			else $action = '';
		if (!empty($fname))
		{
			$html = "
		<form $fname>
			<div class=\"row\">
				<div class=\"form-group\">
					<label $lclass> $label</label>
					<div $fclass>
						<input type=\"file\" $name class=\"form-control\" multiple>
					</div>
				</div>
			</div>
		</form>
		<div class=\"row\">
			<div class=\"form-group\">
				<span $lclass></span>
				<button $bname $action class=\"btn btn-default\">Upload</button>
			</div>
		</div>";
		}
		else
		{
			$html = "
		<div class=\"row\">
			<div class=\"form-group\">
				<label $lclass> $label</label>
				<div $fclass>
					<input type=\"file\" $name class=\"form-control\" multiple>
				</div>
			</div>
		</div>
		<div class=\"row\">
			<div class=\"form-group\">
				<span $lclass></span>
				<button $bname $action class=\"btn btn-default\">Upload</button>
			</div>
		</div>";
		}
		return $html;
	}

	// Inserts a mutli-line text box.
	static public function insertFieldTextArea($data)
	{
		// Check Input
		if (!is_array($data)) return;

		// Setup
		$name = NULL;
		$forx = NULL;
		$event = NULL;
		$disabled = NULL;
		$stx = NULL;
		$gix = NULL;
		$default = NULL;
		$label = NULL;
		$lclass = NULL;
		$fclass = NULL;
		$tooltip = NULL;
		$icond = NULL;
		$icons = NULL;
		$rows = 1;
		$dcmGL = NULL;
		$dcmST = NULL;
		$dcmMS = NULL;

		// Parameters
		self::helperNameId($data, $name, $forx);
		self::helperDCM($data, 'text', $dcmGL, $dcmST, $dcmMS);
		self::helperOnEvent($data, $event);
		self::helperDisabled($data, $disabled);
		self::helperState($data, $stx, $gix);
		self::helperDefault($data, self::DEFTYPE_TEXTBOX, $default);
		self::helperLabel($data, $label);
		self::helperLabelSizeText($data, $lclass);
		self::helperFieldSizeText($data, $fclass);
		self::helperTooltip($data, $tooltip);
		self::helperIcon($data, $icons, $icond);
		self::helperRow($data, $rows);

		// Value
		if (!empty($data['value'])) $value = $data['value'];
			else $value = '';

		// Combine
		$printout = $name . $default . $event . $tooltip . $disabled;

		// Render
		$html = "
		<div class=\"row\">
			<div $dcmST class=\"form-group $stx\">
				<label $lclass $forx>$label</label>
				<div $fclass>
					<span $icons><i $icond></i></span>
					<textarea rows=\"$rows\" class=\"form-control\" $printout>$value</textarea>
					<span $dcmGL class=\"glyphicon$gix form-control-feedback\"></span>
					<span $dcmMS></span>
				</div>
			</div>
		</div>";
		return $html;
	}

	// Uses the following parameters:
	// ** name, label, lsize, fsize, default, disable, tooltip
	// sidemode - Left/Right mode (boolean true/false)
	// side - Determines left or right side
	// 	 0 - left
	//   1 - right
	// togglemode - Sets the style of the checkbox.
	//   0 - Normal
	//   1 - Round
	//   2 - Square
	static public function insertFieldCheckbox($data)
	{
		// Check Input
		if (!is_array($data)) return;

		// Setup
		$name = NULL;
		$forx = NULL;
		$disabled = NULL;
		$default = NULL;
		$label = NULL;
		$lsize = NULL;
		$lclass = NULL;
		$lclass_l = NULL;
		$lclass_r = NULL;
		$fsize = NULL;
		$fclass = NULL;
		$tooltip = NULL;
		$side = NULL;
		$toggle = NULL;
		$event = NULL;

		// Parameters
		self::helperNameId($data, $name, $forx);
		self::helperOnEvent($data, $event);
		self::helperDisabled($data, $disabled);
		self::helperDefault($data, self::DEFTYPE_CHECKBOX, $default);
		self::helperLabel($data, $label);
		self::helperLabelSizeCheck($data, $lsize, $lclass, $lclass_l, $lclass_r);
		self::helperFieldSizeCheck($data, $fsize, $fclass);
		self::helperTooltip($data, $tooltip);
		self::helperSide($data, $side);
		self::helperToggle($data, $toggle);

		// Compute Padding
		$pad_total = $fsize + $lsize;
		if ($pad_total > 0)
		{
			if ($side == 2) $pad_value = (integer)((12 - $pad_total) / 2);
			else $pad_value = (integer)((12 - ($pad_total * 2)) / 2);
			if ($pad_value > 0) $padding = ' class="col-xs-' . $pad_value . '"';
			else $padding = '';
		}
		else $padding = '';

		// Combine
		$printout = $name . $default . $tooltip . $disabled . $event;

		// Render
		switch ($side)
		{
			case 0:		// Dual Mode: Left
				$html = "		<div class=\"row\">
			<div class=\"form-group\">
				<span $padding></span>
				<label $lclass_r $forx>$label</label>
				<div $fclass>";
				if ($toggle != 0)
				{
					$html .= "
					<label class=\"switch\">";
				}
				$html .= "
					<input type=\"checkbox\" value=\"true\" class=\"form-control\" $printout>";
				if ($toggle == 1)
				{
					$html .= "
					<span class=\"slider round\">
					</label>";
				}
				if ($toggle == 2)
				{
					$html .= "
					<span class=\"slider\">
					</label>";
				}
				$html .= "
				</div>";
				break;
			case 1:		// Dual Mode: Right
				$html = "
				<div $fclass>";
				if ($toggle != 0)
				{
					$html .= "
					<label class=\"switch\">";
				}
				$html .= "
					<input type=\"checkbox\" value=\"true\" class=\"form-control\" $printout>";
				if ($toggle == 1)
				{
					$html .= "
					<span class=\"slider round\">
					</label>";
				}
				if ($toggle == 2)
				{
					$html .= "
					<span class=\"slider\">
					</label>";
				}
					$html .= "
				</div>
				<label $lclass_l $forx>$label</label>
				<span $padding></span>
			</div>
		</div>";
				break;
			case 2:		// Single Mode
				$html = "
		<div class=\"row\">
			<div class=\"form-group\">
				<label $lclass $forx>$label</label>
				<div $fclass>";
				if ($toggle != 0)
				{
					$html .= "
					<label class=\"switch\">";
				}
					$html .= "
					<input type=\"checkbox\" value=\"true\" class=\"form-control\" $printout>";
				if ($toggle == 1)
				{
					$html .= "
					<span class=\"slider round\">
					</label>";
				}
				if ($toggle == 2)
				{
					$html .= "
					<span class=\"slider\">
					</label>";
				}
				$html .= "
				</div>
			</div>
		</div>";
				break;
		}
		return $html;
	}

	// Inserts a group of radio buttons.
	static public function insertRadioButtons($data)
	{
		$disabled = NULL;
		$html = '';

		// Parameters
		self::helperDisabled($data, $disabled);
		if (isset($data['name'])) $name = 'name="' . $data['name'] .'"';
			else $name = '';
		if (!empty($data['default'])) $value = $data['default'];
			else $value = '';
		if (isset($data['data']))
		{
			$index = 0;
			foreach($data['data'] as $kx => $vx)
			{
				if (!empty($data['tooltip']))
				{
					if (is_array($data['tooltip']))
					{
						if (!empty($data['tooltip'][$index]))
						{
							$ttText = $data['tooltip'][$index];
							$tooltip = ' data-toggle="tooltip" data-html="true" title="' . $ttText . '"'; 
						}
					}
					else $tooltip = '';
				}
				else $tooltip = '';
				if ($vx == $value)
				{
					$html .= "
		<div class=\"radio\" $tooltip>
			<label><input type=\"radio\" $name $disabled value=\"$vx\" checked>$kx</label>
		</div>";
				}
				else
				{
					$html .= "
		<div class=\"radio\" $tooltip>
			<label><input type=\"radio\" $name value=\"$vx\">$kx</label>
		</div>";
				}
				$index++;
			}
		}
		return $html;
	}

	// Inserts a drop down list control.
	// name - Name and ID for the field
	// label - Text label for the field
	// icon - Icon to use.  For complete list, goto
	//   http://www.w3schools.com/bootstrap/bootstrap_ref_comp_glyphs.asp
	// state - State to display
	//   0 - normal
	//   1 - Ok
	//   2 - Warning
	//   3 - Error
	// lsize - Size of the label (Bootstrap)
	// fsize - Size of the field (Bootstrap)
	// default - Default list item selection
	// optlist - Options list of key=value pairs
	static public function insertFieldDropList($data)
	{
		// Check Input
		if (!is_array($data)) return;

		// Setup
		$name = NULL;
		$forx = NULL;
		$event = NULL;
		$disabled = NULL;
		$stx = NULL;
		$gix = NULL;
		$default = NULL;
		$label = NULL;
		$lclass = NULL;
		$fclass = NULL;
		$tooltip = NULL;
		$icond = NULL;
		$icons = NULL;
		$dcmST = NULL;
		$dcmGL = NULL;
		$dcmMS = NULL;

		// Parameters
		self::helperNameId($data, $name, $forx);
		self::helperOnEvent($data, $event);
		self::helperDCM($data, 'text', $dcmGL, $dcmST, $dcmMS);
		self::helperDisabled($data, $disabled);
		self::helperState($data, $stx, $gix);
		self::helperDefault($data, self::DEFTYPE_PULLDOWN, $default);
		self::helperLabel($data, $label);
		self::helperLabelSizeText($data, $lclass);
		self::helperFieldSizeText($data, $fclass);
		self::helperTooltip($data, $tooltip);
		self::helperIcon($data, $icons, $icond);
		if (isset($data['blank']))
		{
			if ($data['blank'] == true) $blank = true;
				else $blank = false;
		}
		else $blank = false;

		// Combine
		$printout = $name . $tooltip . $event . $disabled;

		// Render
		$html = "
		<div class=\"row\">
			<div $dcmST class=\"form-group$stx\">
				<label $lclass $forx>$label</label>
				<div $fclass>
					<span $icons><i $icond></i></span>
					<select class=\"form-control\" $printout>";
		if ($blank == true)
		{
			$html .= "
						<option value=\"----\">----</option>";
		}
		if (!empty($data['optlist']))
		{
			foreach($data['optlist'] as $kx => $vx)
			{
				if (is_array($vx))
				{
					$html .= "
						<optgroup label=\"$kx\">";
					foreach($vx as $kxa => $vxa)
					{
						$html .= self::insertFieldSelectHelper($kxa, $vxa, $default);
					}
					$html .= "
						</optgroup>";
				}
				else
				{
					$html .= self::insertFieldSelectHelper($kx, $vx, $default);
				}
			}
		}
		$html .= "
					</select>
					<span $dcmGL class=\"glyphicon$gix form-control-feedback\"></span>
					<span $dcmMS></span>
				</div>
			</div>
		</div>";
		return $html;
	}

	// Inserts a static bulleted list.
	// This function is recursive.
	static public function insertList($data, $indent = "")
	{
		// Check Input
		if (!is_array($data)) return;

		// Render
		$html = $indent . "\t<ul>\n";
		foreach($data as $vx)
		{
			if (is_array($vx)) self::insertList($vx, $indent . "\t");
			else
			{
				$html .= $indent . "\t\t<li>" . $vx . "</li>\n";
			}
		}
		$html .= $indent . "\t</ul>\n";
		return $html;
	}

	// Inserts multiple button controls.
	static public function insertButtons($data)
	{
		$html = '';
		// Button width
		// Horizontal spacing is 1
		if (!empty($data['width']))
			$width = $data['width'];
		else
			$width = 3;
		
		// Button Size
		if (isset($data['size']))
		{
			switch($data['size'])
			{
				case 0:
					$size = ' btn-xs';
					break;
				case 1:
					$size = ' btn-sm';
					break;
				case 2:
					$size = ' btn-md';
					break;
				case 3:
					$size = ' btn-lg';
					break;
				default:
					$size = '';
					break;
			}
		}
		else
			$size = '';
			
		// Button Direction
		if (!empty($data['direction']))
			$direction = $data['direction'];
		else
			// Defaults to vertical
			$direction = 0;
		
		// If set, no spacing in horizontal mode
		if (!empty($data['nospace']))
		{
			if ($data['nospace'] == true)
				$space = false;
			else
				$space = true;
		}
		else
			$space = true;

		if ($direction == 1)
		{
			$html .= "
		<div class=\"button\">
			<div class=\"form-group\">";
		}
		foreach($data['button_data'] as $btn => $btndat)
		{
			// Button Name
			if (!empty($btndat['name']))
				$btnname = ' name="' . $btndat['name'] . '"';
			else
				$btnname = '';
			
			// Button Display Name
			if (!empty($btndat['dispname']))
				$btnvalue = ' value="' . $btndat['dispname'] . '"';
			else
				$btnvalue = '';

			// Button Type
			if (!empty($btndat['class']))
				$btnclass = ' class="btn ' . self::$buttonClass[$btndat['class']]
					. ' col-xs-' . $width . $size . '"';
			else
				$btnclass = ' class="btn col-xs-' . $width . $size . '"';

			// Button Click Action
			if (!empty($btndat['action']))
				$btnclick = ' onclick="' . $btndat['action'] . '"';
			else
				$btnclick = '';

			// Render
			$printout = $btnclass . $btnname . $btnvalue . $btnclick;
			switch($direction)
			{
				case 0:	// Vertical
					$html .= "
		<div class=\"row\">
			<div class=\"button\">
				<div class=\"form-group\">";
					if ($space)
					{
						$html .= "
					<span class=\"col-xs-1\"></span>";
					}
					$html .= "
					<input type=\"button\" $printout>
				</div>
			</div>
		</div>";
					break;
				case 1: // Horizontal
					if ($space)
					{
						$html .= "
				<span class=\"col-xs-1\"></span>";
					}
					$html .= "
				<input type=\"button\" $printout>";
					break;
				default:	// Unknown
					break;
			}
		}
		if ($direction == 1)
		{
			$html .= "
			</div>
		</div>";
		}
		return $html;
	}

	// Inserts a single button control.
	static public function insertButtonSingle($data)
	{
		$html = '';
		// Button width
		// Horizontal spacing is 1
		if (!empty($data['width']))
			$width = $data['width'];
		else
			$width = 3;
		
		// Button Size
		if (isset($data['size']))
		{
			switch($data['size'])
			{
				case 0:
					$size = ' btn-xs';
					break;
				case 1:
					$size = ' btn-sm';
					break;
				case 2:
					$size = ' btn-md';
					break;
				case 3:
					$size = ' btn-lg';
					break;
				default:
					$size = '';
					break;
			}
		}
		else $size = '';

		// Position Offset
		if (isset($data['offset']))
			$offset = $data['offset'];
		else
			$offset = '';

		// Button Name
		if (!empty($data['name']))
			$btnname = ' name="' . $data['name'] . '"';
		else
			$btnname = '';
		
		// Button Display Name
		if (!empty($data['dispname']))
			$btnvalue = ' value="' . $data['dispname'] . '"';
		else
			$btnvalue = '';

		// Button Type
		if (!empty($data['class']))
			$btnclass = ' class="btn ' . self::$buttonClass[$data['class']]
				. ' col-xs-' . $width . $size . '"';
		else
			$btnclass = ' class="btn col-xs-' . $width . $size . '"';

		// Button Click Action
		if (!empty($data['action']))
			$btnclick = ' onclick="' . $data['action'] . '"';
		else
			$btnclick = '';

		// Render
		$printout = $btnclass . $btnname . $btnvalue . $btnclick;
		$html .= "
		<div class=\"button\">
			<div class=\"form-group\">";
		if (!empty($offset))
		{
			$html .= "
				<span class=\"col-xs-$offset\"></span>";
		}
		$html .= "
				<input type=\"button\" $printout>
			</div>
		</div>";
		return $html;
	}

	// Inserts action buttons
	static public function insertActionButtons($data)
	{
		$html = '';

		if (isset($data['dispname']))
			$dispname = $data['dispname'];
		else
			$dispname = '';
		if (isset($data['action']))
			$action = $data['action'];
		else
			$action = '';
		if (isset($data['btnset']))
		{
			switch($data['btnset'])
			{
				case self::BTNTYP_NONE:
					$html = "";
					break;
				case self::BTNTYP_VIEW:
					$html = "
		<div class=\"row\">
			<div class=\"button\">
				<div class=\"form-group\">
					<span class=\"col-xs-4\"></span>
					<input type=\"button\" class=\"btn btn-success col-xs-4\" name=\"initialview\" value=\"Go Back\" onclick=\"ajaxServerCommand.sendCommand(-1)\">
					<span class=\"col-xs-4\"></span>
				</div>
			</div>
		</div>";

					break;
				case self::BTNTYP_UPDATE:
					$html = "
			<div class=\"row\">
				<div class=\"button\">
					<div class=\"form-group\">
						<span class=\"col-xs-1\"></span>
						<input type=\"button\" class=\"btn btn-danger col-xs-3\" name=\"Submit\" value=\"Submit Changes\" onclick=\"$action\">
						<span class=\"col-xs-1\"></span>
						<input type=\"button\" class=\"btn btn-info col-xs-3\" name=\"Reset\" value=\"Reset\" onclick=\"clearForm()\">
						<span class=\"col-xs-1\"></span>
						<input type=\"button\" class=\"btn btn-success col-xs-3\" name=\"initialview\" value=\"Go Back\" onclick=\"ajaxServerCommand.sendCommand(-1)\">
					</div>
				</div>
			</div>";
					break;
				case self::BTNTYP_INSERT:
					$html = "
		<div class=\"row\">
			<div class=\"button\">
				<div class=\"form-group\">
					<span class=\"col-xs-1\"></span>
					<input type=\"button\" class=\"btn btn-danger col-xs-3\" name=\"Submit\" value=\"Insert $dispname\" onclick=\"$action\">
					<span class=\"col-xs-1\"></span>
					<input type=\"button\" class=\"btn btn-info col-xs-3\" name=\"Reset\" value=\"Reset\" onclick=\"clearForm()\">
					<span class=\"col-xs-1\"></span>
					<input type=\"button\" class=\"btn btn-success col-xs-3\" name=\"initialview\" value=\"Go Back\" onclick=\"ajaxServerCommand.sendCommand(-1)\">
				</div>
			</div>
		</div>";
					break;
				case self::BTNTYP_DELETE:
					$html = "
			<div class=\"row\">
				<div class=\"button\">
					<div class=\"form-group\">
						<span class=\"col-xs-2\"></span>
						<input type=\"button\" class=\"btn btn-danger col-xs-3\" name=\"Submit\" value=\"Delete $dispname\" onclick=\"$action\">
						<span class=\"col-xs-2\"></span>
						<input type=\"button\" class=\"btn btn-success col-xs-3\" name=\"initialview\" value=\"Go Back\" onclick=\"ajaxServerCommand.sendCommand(-1)\">
						<span class=\"col-xs-2\"></span>
					</div>
				</div>
			</div>";
					break;
				default:
					break;
			}
		}
		return $html;
	}

	// Inserts a selection table with radio buttons and field names.
	static public function insertSelectionTable($data)
	{
		$tooltip = NULL;
		$html = '';
		$bsFeatures = '';

		// Parameters
		if (isset($data['chkbox']))
		{
			if ($data['chkbox'] === true)
				$checkmode = 1;
			else
				$checkmode = $data['chkbox'];
		}
		else $checkmode = 0;
		if (isset($data['name']))
		{
			$tableName = $data['name'];
			if (!$checkmode)
				$name = 'name="' . $data['name'] . '"';
		}
		else $name = '';
		if (isset($data['stage'])) $stage = $data['stage'];
			else $stage = 0;
		if (isset($data['stagelast'])) $stageLast = $data['stage'];
			else $stageLast = 0;
		if (isset($data['clickset'])) $clickset = true;
			else $clickset = false;
		if (isset($data['stripe'])) $bsFeatures .= ' table-striped';
		if (isset($data['condense'])) $bsFeatures .= ' table-condensed';
		if (isset($data['hover'])) $bsFeatures .= ' table-hover';
		if (isset($data['border'])) $bsFeatures .= ' table-bordered';

		// Setup
		switch ($checkmode)
		{
			case 0:
				$clickCall = 'selectItemRadio';
				break;
			case 1:
				$clickCall = 'selectitemCheck';
				break;
			case 2:
				$clickCall = 'selectItemClick';
				break;
			default:
				$clickCall = 'selectItemRadio';
				$checkmode = 0;
				break;
		}

		// Title Row
		if (isset($data['titles']))
		{
			$html .= "
		<table class=\"table $bsFeatures\">
			<thead> 
				<tr>";
			if ($checkmode != 2)
			{
				$html .= "
					<th class=\"text-center\">Select</th>";
			}
			foreach($data['titles'] as $kx)
			{
				$html .= "
					<th class=\"text-center\">$kx</th>";
			}
			$html .= "
				</tr>
			</thead>";
		}

		// Table Data
		if (isset($data['tdata']))
		{
			$html .= "
			<tbody>";

			// Row
			$index = 0;
			foreach($data['tdata'] as $kxr)
			{
				if (!empty($data['tooltip']))
				{
					if (is_array($data['tooltip']))
					{
						if (!empty($data['tooltip'][$index]))
						{
							$ttText = $data['tooltip'][$index];
							$tooltip = ' data-toggle="tooltip" data-html="true" title="'
								. $ttText . '"'; 
						}
						else $tooltip = '';
					}
					else $tooltip = '';
				}
				else $tooltip = '';
				$keydata = $kxr[0];
				switch ($checkmode)
				{
					case 0:
						$name = 'name="' . $data['name'] . '"';
						if ($clickset)
						{
							$html .= "
				<tr $tooltip onclick=\"selectItemRadio('$tableName', '$keydata');\">";
						}
						else
							$html .= "
				<tr $tooltip>";
						break;
					case 1:
						$name = 'id="' . $data['name'] . '_' . $keydata . '"';
						if ($clickset)
						{
							$checkKey = $tableName . '_' . $keydata;
							$checkBox = "onclick=\"selectItemCheck('$checkKey');\"";
							$html .= "
				<tr $tooltip onclick=\"selectItemCheck('$checkKey');\">";
						}
						else
							$html .= "
				<tr $tooltip>";
						break;
					case 2:
						$name = 'id="' . $data['name'] . '_' . $keydata . '"';
						$html .= "
				<tr $tooltip onclick=\"selectItemClick('$stage', '$stageLast', '$keydata');\">";
					break;
				}

				// Column
				$count = 0;
				foreach($kxr as $kxc)
				{
					if ($count == 0)
					{
						switch ($checkmode)
						{
							case 0:
								$html .= "
					<td class=\"text-center\">
						<div class=\"radio\">
							<label><input type=\"radio\" $name value=\"$kxc\"></label>
						</div>
					</td>";
								break;
							case 1:
								$html .= "
					<td class=\"text-center\">
						<div class=\"checkbox\">
							<label><input type=\"checkbox\" $name value=\"true\" $checkBox></label>
						</div>
					</td>";
								break;
							default:
					// 			$html .= "
					// <td class=\"text-center\">$kxc</td>";
								break;
						}
					}
					else
					{
						$html .= "
					<td class=\"text-center\">$kxc</td>";
					}
					$count++;
				}
				$html .= "
				</tr>";
				$index++;
			}
			$html .= "
			</tbody>
		</table>";
		}
		return $html;
	}

	// Inserts the heading banner
	static public function insertHeadingBanner($data)
	{
		if (isset($data['message1']))
			$msg1 = $data['message1'];
		else
			$msg1 = '';
		if (isset($data['message2']))
			$msg2 = ' ' . $data['message2'];
		else
			$msg2 = '';
		if (isset($data['warning']))
			$warn = $data['warning'];
		else
			$warn = '';

		$html = '';
		if (!empty($msg1) || !empty($msg2))
		{
			$html .= "
		<h1 class=\"text-center\">$msg1<span class=\"color-blue\">$msg2</span></h1>";
		}
		if (!empty($warn))
		{
			$html .= "
		<h4 class=\"text-center color-red\">WARNING<br>$warn</h4>";
		}
		return $html;
	}

	// Generates a list of checkboxes.
	// lsize, fsize, and the list array.
	// The list array uses the same format for each entry:
	// flag - flag number
	// label - display label
	// tooltip - popup description
	// default - indicates if the item is checked or not
	static public function insertCheckList($data)
	{
		$lsize = NULL;
		$fsize = NULL;
		$default = NULL;
		$disable = NULL;

		// Check to make sure list is an array.  If it's not, then
		// there is no point in running the rest of the code.
		if (!is_array($data['list'])) return;
		$count = count($data['list']);
		if ($count == 0) return;

		// Parameters
		if (isset($data['lsize'])) $lsize = $data['lsize'];
			else if (self::$lsize_default !== false) $lsize = self::$lsize_default;
			else $lsize = 0;
		if (isset($data['fsize'])) $fsize = $data['fsize'];
			else if (self::$fsize_default !== false) $fsize = self::$fsize_default;
			else $fsize = 1;
		if (isset($data['default'])) $default = $data['default'];
			else $default = false;
		if (isset($data['disable'])) $disable = $data['disable'];
			else $disable = false;

		// Loop
		$loopterm = ($count & 0x00000001) ? $count - 1 : $count;
		$count = 0;
		$html = '';
		foreach ($data['list'] as $kx => $vx)
		{
			if (empty($vx['event']) || empty($vx['action']))
			{
				$event = '';
				$action = '';
			}
			else
			{
				$event = $vx['event'];
				$action = $vx['action'];
			}
			if ($default == false)
				$vxdef = false;
			else
				$vxdef = $vx['default'];
			if (empty($vx['tooltip']))
				$vxtt = false;
			else
				$vxtt = $vx['tooltip'];
			$dxa = array(
				'name' => self::CHECKFLAG_NAME . $vx['flag'],
				'label' => $vx['label'],
				'lsize' => $lsize,
				'fsize' => $fsize,
				'default' => $vxdef,
				'disable' => $disable,
				'tooltip' => $vxtt,
				'event' => $event,
				'action' => $action,
			);
			if ($count < $loopterm)
			{
				$dxa['sidemode'] = true;
				$dxa['side'] = ($count & 0x00000001) ? 1 : 0;
			}
			$html .= self::insertFieldCheckbox($dxa);
			$count++;
		}
		return $html;
	}

	// Inserts a image type.
	// name - id of the image tag
	// src - URL of the image source
	// alt - Alternate text for the image.
	// width - width of the image
	// height - height of the image
	// lsize - size offset from left side
	static public function insertImage($data)
	{
		$lclass = NULL;
		$event = NULL;
		
		self::helperLabelSizeText($data, $lclass);
		self::helperOnEvent($data, $event);
		if (!empty($data['name'])) $name = 'id="' . $data['name'] . '"';
			else $name = '';
		if (!empty($data['src'])) $source = ' src="' . $data['src'] . '"';
			else $source = '';
		if (!empty($data['alt'])) $altxt = ' alt=' . $data['alt'] . '"';
			else $altxt = '';
		if (!empty($data['width'])) $width = ' width="' . $data['width'] . '"';
			else $width = '';
		if (!empty($data['height'])) $height = ' height="' . $data['height'] . '"';
			else $height = '';
		$printout = $name . $source . $altxt . $width . $height . $event;
		$html = "
		<div class=\"row\">
			<div class=\"form-group\">
				<label $lclass></label>
				<div>
					<img $printout>
				</div>
			</div>
		</div>";
		return $html;
	}

	// Inserts date and time (hour, minute) input fields.
	static public function insertFieldDateTime($data)
	{
		// Check Input
		if (!is_array($data)) return;

		// Setup
		$name = NULL;
		$forx = NULL;
		$event = NULL;
		$disabled = NULL;
		$value = NULL;
		$stx = NULL;
		$gix = NULL;
		$default = NULL;
		$label = NULL;
		$lclass = NULL;
		$fclass = NULL;
		$lclass = NULL;

		$tooltip = NULL;
		$icond = NULL;
		$icons = NULL;
		$dcmGL = NULL;
		$dcmST = NULL;
		$dcmMS = NULL;

		// Parameters
		self::helperNameId($data, $name, $forx);
		self::helperDCM($data, 'text', $dcmGL, $dcmST, $dcmMS);
		self::helperOnEvent($data, $event);
		self::helperDisabled($data, $disabled);
		self::helperState($data, $stx, $gix);
		self::helperLabel($data, $label);
		self::helperLabelSizeText($data, $lclass);
		self::helperFieldSizeText($data, $fclass);
		self::helperTooltip($data, $tooltip);
		self::helperIcon($data, $icons, $icond);

		// Custom
		if (!empty($data['name']))
		{
			$namehour = $data['name'] . '_timehour';
			$namemin = $data['name'] . '_timemin';
		}
		else
		{
			$namehour = '';
			$namemin = '';
		}
		if (!empty($data['value']))
		{
			$value = ' value="' . timedate::unix2day($data['value']) . '"';
			$thour = timedate::unix2todhour($data['value']);
			$tmin = timedate::unix2todmin($data['value']);
		}
		else
		{
			$value = '';
			$thour = -1;
			$tmin = -1;
		}

		// Combine
		$printout = $name . $value . $default . $event . $tooltip . $disabled;

		// Date Picker Options
		$datePickOptions = 'data-provide="datepicker"';
		if (!empty($data['date_format'])) $datePickOptions .= ' data-date-format="' . $data['date_format'] . '"';
			else $datePickOptions .= ' data-date-format="mm/dd/yyyy"';
		if (!empty($data['date_highlight'])) $datePickOptions .= ' data-date-today-highlight="true"';
		if (!empty($data['date_autoclose'])) $datePickOptions .= ' data-date-autoclose="true"';
		if (!empty($data['date_todaybtn'])) $datePickOptions .= ' data-date-today-btn="true"';
		if (!empty($data['date_clearbtn'])) $datePickOptions .= ' data-date-clear-btn="true"';

		// Generate Time Selection
		$optlisthour = "
							<option value=\"none\">--</option>";
		$optlistmin = "
							<option value=\"none\">--</option>";
		for ($i = 0; $i < 24; $i++)
		{
			if ($i == $thour)
			{
				if ($i > 12)
				{
					$j = $i - 12;
					$optlisthour .= "
							<option value=\"th_$i\" selected=\"selected\">$i, $j PM</option>";
				}
				else
				{
					$optlisthour .= "
							<option value=\"th_$i\" selected=\"selected\">$i</option>";
				}
			}
			else
			{
				if ($i > 12)
				{
					$j = $i - 12;
					$optlisthour .= "
							<option value=\"th_$i\">$i, $j PM</option>";
				}
				else
				{
					$optlisthour .= "
							<option value=\"th_$i\">$i</option>";
				}
			}
		}
		for ($i = 0; $i < 60; $i++)
		{
			if ($i == $tmin)
				$optlistmin .= "
							<option value=\"tm_$i\" selected=\"selected\">$i</option>";
			else
				$optlistmin .= "
							<option value=\"tm_$i\">$i</option>";
		}
		$html = "
		<div class=\"row\">
			<div $dcmST class=\"form-group $stx\">
				<label $lclass $forx>$label</label>
				<div $fclass>
					<div class=\"input-group date\" $datePickOptions>
						<span $icons><i $icond></i></span>
						<label class=\"control-label col-xs-1\">Date</label>
						<input type=\"text\" class=\"form-control\" $printout>
						<label class=\"control-label col-xs-1\">Hour</label>
						<select class=\"form-control col-xs-1\" name=\"$namehour\" id=\"$namehour\" $disabled>$optlisthour
						</select>
						<label class=\"control-label col-xs-1\">Minute</label>
						<select class=\"form-control col-xs-1\" name=\"$namemin\" id=\"$namemin\" $disabled>$optlistmin
						</select>
						<span class=\"input-group-addon\">
							<i class=\"glyphicon glyphicon-th\"></i>
						</span>
						<span $dcmGL class=\"glyphicon $gix form-control-feedback\"></span>
						<span $dcmMS></span>
					</div>
				</div>
			</div>
		</div>";
		return $html;
	}

	// Generates a progress bar
	// name - Name and ID for the field
	// label - Text label for the field
	// icon - Icon to use.  For complete list, goto
	//   http://www.w3schools.com/bootstrap/bootstrap_ref_comp_glyphs.asp
	// lsize - Size of the label (Bootstrap)
	// fsize - Size of the field (Bootstrap)
	// value - current value to display
	// max - maximum value
	static public function insertProgressBar($data)
	{
		$name = NULL;
		$forx = NULL;
		$label = NULL;
		$lclass = NULL;
		$fclass = NULL;
		$value = NULL;
		$max = NULL;
		$icons = NULL;
		$icond = NULL;

		self::helperNameId($data, $name, $forx);
		self::helperLabel($data, $label);
		self::helperLabelSizeText($data, $lclass);
		self::helperFieldSizeText($data, $fclass);
		self::helperValue($data, $value);
		self::helperIcon($data, $icons, $icond);

		if (isset($max)) $max = ' max="' . $data['max'] . '"';

		$printout = $name . $value . $max;

		$html = "
		<div class=\"row\">
			<div class=\"form-group\">
				<label $lclass $forx>$label</label>
				<div $fclass>
					<div class=\"input-group\">
						<span $icons><i $icond></i></span>
						<meter $printout></meter>
					</div>
				</div>
			</div>
		</div>";
		return $html;
	}

	// Generates a meter bar
	// name - Name and ID for the field
	// label - Text label for the field
	// icon - Icon to use.  For complete list, goto
	//   http://www.w3schools.com/bootstrap/bootstrap_ref_comp_glyphs.asp
	// lsize - Size of the label (Bootstrap)
	// fsize - Size of the field (Bootstrap)
	// value - current value to display
	// min - minimum value
	// max - maximum value
	// low - low value threshold
	// high - high value threshold
	static public function insertMeterBar($data)
	{
		$name = NULL;
		$forx = NULL;
		$label = NULL;
		$lclass = NULL;
		$fclass = NULL;
		$value = NULL;
		$min = NULL;
		$max = NULL;
		$low = NULL;
		$high = NULL;
		$icons = NULL;
		$icond = NULL;

		self::helperNameId($data, $name, $forx);
		self::helperLabel($data, $label);
		self::helperLabelSizeText($data, $lclass);
		self::helperFieldSizeText($data, $fclass);
		self::helperValue($data, $value);
		self::helperIcon($data, $icons, $icond);

		if (isset($min)) $min = ' min="' . $data['min'] . '"';
		if (isset($max)) $max = ' max="' . $data['max'] . '"';
		if (isset($low)) $low = ' low="' . $data['low'] . '"';
		if (isset($high)) $high = ' high="' . $data['high'] . '"';

		$printout = $name . $value . $min . $max . $low . $high;

		$html = "
		<div class=\"row\">
			<div class=\"form-group\">
				<label $lclass $forx>$label</label>
				<div $fclass>
					<div class=\"input-group\">
						<span $icons><i $icond></i></span>
						<meter $printout></meter>
					</div>
				</div>
			</div>
		</div>";
		return $html;
	}

	// Opens a form element.
	// name - name of the form
	// method - send method (GET/POST)
	// action - URL to send to
	// class - The class of the form
	static public function openForm($data)
	{
		// Form Name
		if (!empty($data['name'])) $name = ' name="' . $data['name'] . '"';
			else $name = '';
		// Sending Method
		if (!empty($data['method'])) $method = ' method="' . $data['method'] . '"';
			else $method = '';
		// Send to URL
		if (!empty($data['action'])) $action = ' action="' . $data['action'] . '"';
			else $action = '';
		// CSS Class
		if (!empty($data['class'])) $class = ' class="' . $data['class'] . ' form-horizontal"';
			else $class = ' class="form-horizontal"';
		$html = "
	<form $name $method $action $class>";
		return $html;
	}

	// Closes a form tag
	static public function closeForm()
	{
		$html = "
	</form>";
		return $html;
	}
	
	// Opens a field set within a form
	// name - Displayed name of the set
	// disable - Indicates that the field set is disabled
	static public function openFieldset($data)
	{
		if (!empty($data['name'])) $name = $data['name'];
			else $name = '';
		// Disabled Status
		if (!empty($data['disable']))
		{
			if ($data['disable'] == true) $disabled = ' disabled';
			else $disabled = '';
		}
		else $disabled = '';
		$html = "
	<fieldset $disabled>";
		if (!empty($name))
		{
			$html .= "
		<legend>$name</legend>";
		}
		return $html;
	}

	// Closes a fieldset tag	
	static public function closeFieldset()
	{
		$html = "
	</fieldset>";
		return $html;
	}

	// Generates a hidden block
	static public function openHiddenBlock($data)
	{
		$name = NULL;
		$forx = NULL;
		$disable = NULL;

		self::helperNameId($data, $name, $forx);
		self::helperDisabled($data, $disable);
		if (isset($data['hidden']))
		{
			if ($data['hidden'] == true)
				$hidden = ' hidden';
			else
				$hidden = '';
		}
		else $hidden = '';

		$printout = $name . $disable . $hidden;
		$html = "
	<div $printout>";
		return $html;
	}

	// Closes a hidden block
	static public function closeHiddenBlock()
	{
		$html = "
	</div>";
		return $html;
	}

	// Renders a top border type 1
	static public function border1top()
	{
		$html = "
<div class=\"image-border-top\">
	<img src=\"" . self::$base_url . "/images/border/border1a.gif\" alt=\"border1a\">
</div>";
		return $html;
	}

	// Renders a top border type 1
	static public function border2top()
	{
		$html = "
<div class=\"image-border-top\">
	<img src=\"" . self::$base_url . "/images/border/border2a.gif\" alt=\"border2a\">
</div>";
		return $html;
	}
	
	// Renders a bottom border type 1
	static public function border1bottom()
	{
		$html = "
<div class=\"image-border-bottom\">
	<img src=\"" . self::$base_url . "/images/border/border1b.gif\" alt=\"border1b\">
</div>";
		return $html;
	}
	
	// Renders a bottom border type 1
	static public function border2bottom()
	{
		$html = "
<div class=\"image-border-bottom\">
	<img src=\"" . self::$base_url . "/images/border/border2b.gif\" alt=\"border2b\">
</div>";
		return $html;
	}

	// Create an area that is 50% the width of the browser window.
	static public function width50open()
	{
		$html = "
<div class=\"width50\">";
		return $html;
	}
	
	// Create an area that is 75% the width of the browser window.
	static public function width75open()
	{
		$html = "
<div class=\"width75\">";
		return $html;
	}

	// Closes a previously opened width area.
	static public function widthClose()
	{
		$html = "
</div>";
		return $html;
	}

	// Inserts a 5% vertical tab
	static public function verticalTab5()
	{
		$html = "
<div class=\"vspace5\"></div>";
		return $html;
	}
	
	// Inserts a 10% vertical tab
	static public function verticalTab10()
	{
		$html = "
<div class=\"vspace10\"></div>";
		return $html;
	}
	
	// Generates an HTML page according to input data.
	// Each element requires a type parameter
	static public function pageAutoGenerate($data)
	{
		// Check Input
		if (!is_array($data)) return;

		// Set the HTML collection variable.
		$htmlCollection = '';

		// Loop through each element of the array, calling the
		// different component generation methods.
		foreach($data as $kx => $vx)
		{
			if (isset($vx['type']))
			{
				$type = $vx['type'];
				unset($vx['type']);
				switch ($type)
				{
					case self::TYPE_HIDE:
						$htmlCollection .= self::insertFieldHidden($vx);
						break;
					case self::TYPE_TEXT:
						$htmlCollection .= self::insertFieldText($vx);
						break;
					case self::TYPE_PASS:
						$htmlCollection .= self::insertFieldPassword($vx);
						break;
					case self::TYPE_DATE:
						$htmlCollection .= self::insertFieldDate($vx);
						break;
					case self::TYPE_FILE:
						$htmlCollection .= self::insertFieldFile($vx);
						break;
					case self::TYPE_AREA:
						$htmlCollection .= self::insertFieldTextArea($vx);
						break;
					case self::TYPE_CHECK:
						$htmlCollection .= self::insertFieldCheckbox($vx);
						break;
					case self::TYPE_RADIO:
						$htmlCollection .= self::insertRadioButtons($vx);
						break;
					case self::TYPE_PULLDN:
						$htmlCollection .= self::insertFieldDropList($vx);
						break;
					case self::TYPE_BLIST:
						$htmlCollection .= self::insertList($vx['data']);
						break;
					case self::TYPE_BUTTON:
						$htmlCollection .= self::insertButtons($vx);
						break;
					case self::TYPE_SBUTTON:
						$htmlCollection .= self::insertButtonSingle($vx);
						break;
					case self::TYPE_RADTABLE:
						$htmlCollection .= self::insertSelectionTable($vx);
						break;
					case self::TYPE_ACTBTN:
						$htmlCollection .= self::insertActionButtons($vx);
						break;
					case self::TYPE_HEADING:
						$htmlCollection .= self::insertHeadingBanner($vx);
						break;
					case self::TYPE_CHECKLIST:
						$htmlCollection .= self::insertCheckList($vx);
						break;
					case self::TYPE_IMAGE:
						$htmlCollection .= self::insertImage($vx);
						break;
					case self::TYPE_DATETIME:
						$htmlCollection .= self::insertFieldDateTime($vx);
						break;
					case self::TYPE_BARPROG:
						$htmlCollection .= self::insertProgressBar($vx);
						break;
					case self::TYPE_BARMETER:
						$htmlCollection .= self::insertMeterBar($vx);
						break;
					case self::TYPE_FORMOPEN:
						$htmlCollection .= self::openForm($vx);
						break;
					case self::TYPE_FORMCLOSE:
						$htmlCollection .= self::closeForm();
						break;
					case self::TYPE_FSETOPEN:
						$htmlCollection .= self::openFieldset($vx);
						break;
					case self::TYPE_FSETCLOSE:
						$htmlCollection .= self::closeFieldset();
						break;
					case self::TYPE_HIDEOPEN:
						$htmlCollection .= self::openHiddenBlock($vx);
						break;
					case self::TYPE_HIDECLOSE:
						$htmlCollection .= self::closeHiddenBlock();
						break;
					case self::TYPE_TOPB1:
						$htmlCollection .= self::border1top();
						break;
					case self::TYPE_TOPB2:
						$htmlCollection .= self::border2top();
						break;
					case self::TYPE_BOTB1:
						$htmlCollection .= self::border1bottom();
						break;
					case self::TYPE_BOTB2:
						$htmlCollection .= self::border2bottom();
						break;
					case self::TYPE_WD50OPEN:
						$htmlCollection .= self::width50open();
						break;
					case self::TYPE_WD75OPEN:
						$htmlCollection .= self::width75open();
						break;
					case self::TYPE_WDCLOSE:
						$htmlCollection .= self::widthClose();
						break;
					case self::TYPE_VTAB5:
						$htmlCollection .= self::verticalTab5();
						break;
					case self::TYPE_VTAB10:
						$htmlCollection .= self::verticalTab10();
						break;
					default:
				}
			}
		}
		return $htmlCollection;
	}

	// Writes the initial page to the output stream.  This page is the standard
	// page that all modules should use.  The parameters are as follows:
	// title - page title
	// url - base url
	// fname - module filename
	// left - menu items on left side of nav bar
	// right - menu items on right side of nav bar
	// funcbar - menu items on function bar
	// js_file - additional javascript files to load
	// css_files - additional css files to load
	// html_flags - various flags such as feature activations
	static public function loadTemplatePage($title, $url, $fname, $left,
		$right, $funcbar, $js_files, $css_files, $html_flags, $funcbar2 = NULL,
		$funcbar3 = NULL)
	{
		global $session;

		// Set flags
		if (is_array($left))       $flag_left = true;     else $flag_left = false;
		if (is_array($right))      $flag_right = true;    else $flag_right = false;
		if (is_array($funcbar))    $flag_fbar = true;     else $flag_fbar = false;
		if (is_array($funcbar2))   $flag_fbar2 = true;    else $flag_fbar2 = false;
		if (is_array($funcbar3))   $flag_fbar3 = true;    else $flag_fbar3 = false;
		if (is_array($js_files))   $flag_jsfile = true;   else $flag_jsfile = false;
		if (is_array($css_files))  $flag_cssfile = true;  else $flag_cssfile = false;

		// Used to activate features
		if (is_array($html_flags))
		{
			$flag_checkbox = in_array('checkbox', $html_flags);
			$flag_datepick = in_array('datepick', $html_flags);
			$flag_tooltip = in_array('tooltip', $html_flags);
			$flag_type2 = in_array('type2', $html_flags);
			$flag_funchide = in_array('funchide', $html_flags);
		}
		else
		{
			$flag_checkbox = false;
			$flag_datepick = false;
			$flag_tooltip = false;
			$flag_type2 = false;
			$flag_funchide = false;
		}

		// Hide navigation buttons
		if ($flag_funchide)
			$funchide = ' hidden';
		else
			$funchide = '';

?>
<!DOCTYPE html>
<html lang="enUS">
	<head>
		<!-- Page Title (Shows up in title bar of browser) -->
		<title><?php echo $title; ?></title>
		<!-- Favirotie Icons (Shows up next to the URL in the browser address bar)
			 Generated from https://realfavicongenerator.net/ -->
		<link rel="apple-touch-icon" sizes="180x180" href="<?php echo $url; ?>/images/favicons/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="<?php echo $url; ?>/images/favicons/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="<?php echo $url; ?>/images/favicons/favicon-16x16.png">
		<link rel="manifest" href="<?php echo $url; ?>/images/favicons/site.webmanifest">
		<link rel="mask-icon" href="<?php echo $url; ?>/images/favicons/safari-pinned-tab.svg" color="#5bbad5">
		<link rel="shortcut icon" href="<?php echo $url; ?>/images/favicons/favicon.ico">
		<meta name="msapplication-TileColor" content="#da532c">
		<meta name="msapplication-config" content="<?php echo $url; ?>/images/favicons/browserconfig.xml">
		<meta name="theme-color" content="#ffffff">
		<!-- JavaScript Core Files -->
		<script type="text/javascript" src="<?php echo $url; ?>/js/baseline/ajax.js"></script>
		<script type="text/javascript" src="<?php echo $url; ?>/js/baseline/heartbeat.js"></script>
		<script type="text/javascript" src="<?php echo $url; ?>/js/baseline/treewalker.js"></script>
		<script type="text/javascript" src="<?php echo $url; ?>/js/baseline/verify.js"></script>
		<!-- Install Bootstrap CSS -->
		<link rel="stylesheet" type="text/css" href="<?php echo $url; ?>/APIs/Bootstrap/bootstrap-3.3.7-dist/css/bootstrap.css">
		<!-- Install Custom Common CSS -->
		<link rel="stylesheet" type="text/css" href="<?php echo $url; ?>/css/common.css">
		<!-- Install Custom Header CSS -->
		<link rel="stylesheet" type="text/css" href="<?php echo $url; ?>/css/header.css">
		<!-- Install Slider CSS -->
		<link rel="stylesheet" type="text/css" href="<?php echo $url; ?>/css/toggle.css">
<?php
		if ($flag_cssfile)
		{
			foreach($css_files as $kx)
			{
?>
		<link rel="stylesheet" type="text/css" href="<?php echo $url . $kx; ?>">
<?php
			}
		}
		// Features: Bootstrap Date Picker
		if ($flag_datepick)
		{
?>
		<!-- Install Bootstrap Datepicker -->
		<link rel="stylesheet" type="text/css" href="<?php echo $url; ?>/APIs/datepicker/css/bootstrap-datepicker3.min.css">
<?php
		}
?>
	</head>
	<body href-link="<?php echo $fname; ?>">
		<!-- Beginning of header Nav Bar -->
		<div id="navigationBarHeader">
			<!-- Navbar header with logo, time, and logout button -->
			<nav id="navigationBar" class="navbar navbar-default">
				<div class="container-fluid">
					<div class="navbar-header">
						<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
						<!-- Sea-Core Logo Image -->
						<img class="navbar-brand" alt="Brand" src="<?php echo $url; ?>/images/branding/base_small.png">
						<!-- Div for the time -->
						<div class="navbar-text" id="timeday"></div>
					</div>
					<!-- Collect the nav links, forms, and other content for toggling -->
					<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
<?php
		// Navigation Bar, Left Side
		if ($flag_left)
		{
?>
						<!-- Nav Bar, Left Side -->
						<ul class="nav navbar-nav">
<?php
			foreach($left as $kx => $vx)
			{
?>
							<button type="button" id="<?php echo $vx; ?>" class="btn btn-default navbar-btn"><?php echo $kx; ?></button>
<?php
			}
?>
						</ul>
<?php
		}
?>
						<!-- Nav Bar, Right Side -->
						<ul class="nav navbar-nav navbar-right">
							<li>
<?php
		if ($flag_right)
		{
			foreach($right as $kx => $vx)
			{
?>
								<button type="button" id="<?php echo $vx; ?>" class="btn btn-default navbar-btn"><?php echo $kx; ?></button>
<?php
			}
		}
?>
								<!-- All pages have the logout button -->
								<button type="button" id="logout" class="btn btn-default navbar-btn">Logout</button>
							</li>
						</ul>
						<span class="icon-bar"> </span>
					</div><!-- /.navbar-collapse -->
				</div><!-- /.container-fluid -->
			</nav>
			<!-- End of header Nav Bar -->
<?php
		if ($flag_fbar)
		{
?>
			<!-- Beginning of function bar -->
			<nav id="functionBar1" class="nav nav-inline" <?php echo $funchide; ?>>
<?php
			foreach($funcbar as $kx => $vx)
			{
				if (is_array($vx))
				{
?>
				<div class="btn-group" role="group" aria-label="...">
<?php
					foreach($vx as $kxa => $vxa)
					{
?>
					<button type="button" id="<?php echo $vxa; ?>" class="btn btn-default navbar-btn"><?php echo $kxa; ?></button>
<?php
					}
?>
				</div>
<?php
				}
				else
				{
?>
				<button type="button" id="<?php echo $vx; ?>" class="btn btn-default navbar-btn"><?php echo $kx; ?></button>
<?php
				}
			}
?>
			</nav>
			<!-- End of function bar -->
<?php
		}
		if ($flag_fbar2)
		{
?>
			<!-- Beginning of function bar -->
			<nav id="functionBar2" class="nav nav-inline" <?php echo $funchide; ?>>
<?php
			foreach($funcbar2 as $kx => $vx)
			{
				if (is_array($vx))
				{
?>
				<div class="btn-group" role="group" aria-label="...">
<?php
					foreach($vx as $kxa => $vxa)
					{
?>
					<button type="button" id="<?php echo $vxa; ?>" class="btn btn-default navbar-btn"><?php echo $kxa; ?></button>
<?php
					}
?>
				</div>
<?php
				}
				else
				{
?>
				<button type="button" id="<?php echo $vx; ?>" class="btn btn-default navbar-btn"><?php echo $kx; ?></button>
<?php
				}
			}
?>
			</nav>
			<!-- End of function bar -->
<?php
		}
		if ($flag_fbar3)
		{
?>
			<!-- Beginning of function bar 3 -->
			<nav id="functionBar3" class="nav nav-inline" <?php echo $funchide; ?>>
<?php
			foreach($funcbar3 as $kx => $vx)
			{
				if (is_array($vx))
				{
?>
				<div class="btn-group" role="group" aria-label="...">
<?php
					foreach($vx as $kxa => $vxa)
					{
?>
					<button type="button" id="<?php echo $vxa; ?>" class="btn btn-default navbar-btn"><?php echo $kxa; ?></button>
<?php
					}
?>
				</div>
<?php
				}
				else
				{
?>
				<button type="button" id="<?php echo $vx; ?>" class="btn btn-default navbar-btn"><?php echo $kx; ?></button>
<?php
				}
			}
?>
			</nav>
			<!-- End of function bar -->
<?php
		}
?>
		</div>
		<!-- Start of main content area -->
		<div id="mainFrame">
			<div style="color: red;" id="errorTarget"></div>
			<div style="color: blue;" id="responseTarget"></div>
<?php
	if ($flag_type2)
	{
?>
			<div class="row row-list">
				<div id="link-nav" class="col-xs-2 link-nav-div"></div>
				<div id="main" class="col-xs-8 link-main-div"></div>
				<div id="link-stat" class="col-xs-2 link-status-div"></div>
			</div>
<?php
	}
	else
	{
?>
			<div id="main" class="main-wrapper-div"></div>
<?php
	}
?>
		</div>
		<!-- End of main content area -->
<?php
		$token = $session->getToken();
		if ($token != false)
		{
			echo self::insertToken($token);
		}
?>
		<!-- Install Timer -->
		<script type="text/javascript" src="<?php echo $url; ?>/js/baseline/timer.js"></script>
		<!-- Install regular jQuery -->
		<script type="text/javascript" src="<?php echo $url; ?>/APIs/JQuery/BaseJQuery/jquery-3.3.1.min.js"></script>
		<!-- Install Bootstrap -->
		<script type="text/javascript" src="<?php echo $url; ?>/APIs/Bootstrap/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
<?php
		// Features: Checkbox
		if ($flag_checkbox == true)
		{
?>
		<!-- Install Custom Checkbox -->
		<script src="<?php echo $url; ?>/APIs/checkbox/dist/js/bootstrap-checkbox.min.js"></script>
<?php
		}
		// Features: Bootstrap Date Picker
		if ($flag_datepick)
		{
?>
		<!-- Install Datepicker -->
		<script src="<?php echo $url; ?>/APIs/datepicker/js/bootstrap-datepicker.min.js"></script>
<?php
		}
?>
		<!-- Install Header Custom JavaScript -->
		<script type="text/javascript" src="<?php echo $url; ?>/js/baseline/header.js"></script>
<?php
		if ($flag_jsfile)
		{
			foreach($js_files as $kx)
			{
?>
		<script type="text/javascript" src="<?php echo $url . $kx; ?>"></script>
<?php
			}
		}
		if ($flag_checkbox == true)
		{
?>
		<!-- Checkbox Boot Script -->
		<script type="text/javascript">
			function featureCheckbox() {
				$(':checkbox').checkboxpicker();
			}
		</script>
<?php
		}
		if ($flag_tooltip == true)
		{
?>
		<!-- Tooltip Boot Script -->
		<script type="text/javascript">
			function featureTooltip() {
				$('[data-toggle="tooltip"]').tooltip();
			}
		</script>
<?php
		}
		if ($flag_tooltip == true)
		{
?>
		<!-- Datepicker Boot Script -->
		<script type="text/javascript">
			function featureDatepicker() {
				$('.datepicker').datepicker();
			}
		</script>
<?php
		}
?>
	</body>
</html>
<?php
	}

}


// Initialize the HTML library.
html::initialize();


?>