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


interface html_interface
{
	// Some misculanious constants.
	const CHECKFLAG_NAME = 'chkbox_';

	// Field error status.  These constants must match values in both
	// error.php and ajax.js
	const STAT_NONE = 0;
	const STAT_DEFAULT = 0;
	const STAT_OK = 1;
	const STAT_WARN = 2;
	const STAT_ERROR = 3;
	const STAT_GENERAL = 4;

	// Predefined button set types for insertActionButtons.
	const BTNTYP_VIEW		= 0;
	const BTNTYP_UPDATE		= 1;
	const BTNTYP_INSERT		= 2;
	const BTNTYP_DELETE		= 3;

	// Field type definitions used by pageAutoGenerate.
	const TYPE_HIDE			= 0;	// Hidden input field
	const TYPE_TEXT			= 1;	// Text input field
	const TYPE_PASS			= 2;	// Password input field
	const TYPE_FILE			= 3;	// File input field
	const TYPE_AREA			= 4;	// Textarea tag input field
	const TYPE_CHECK		= 5;	// Checkbox
	const TYPE_RADIO		= 6;	// Radio button group
	const TYPE_PULLDN		= 7;	// Pulldown selection menu/list
	const TYPE_BLIST		= 8;	// Standard nested list
	const TYPE_BUTTON		= 9;	// Button group
	const TYPE_ACTBTN		= 10;	// Action button set
	const TYPE_RADTABLE		= 11;	// Radio button item selection table
	const TYPE_HEADING		= 12;	// Banner headings
	const TYPE_CHECKLIST	= 13;	// Checkbox Lists
	const TYPE_IMAGE		= 14;	// Image File
	const TYPE_FORMOPEN		= 20;	// Open form with name
	const TYPE_FORMCLOSE	= 21;	// Close form
	const TYPE_FSETOPEN		= 22;	// Open field set with title
	const TYPE_FSETCLOSE	= 23;	// Close field set
	const TYPE_HIDEOPEN		= 24;	// Opens a named hidden block
	const TYPE_HIDECLOSE	= 25;	// Close named hidden block
	const TYPE_TOPB1		= 30;	// Top border type 1
	const TYPE_TOPB2		= 31;	// Top border type 2
	const TYPE_BOTB1		= 32;	// Bottom border type 1
	const TYPE_BOTB2		= 33;	// Bottom border type 2
	const TYPE_WD50OPEN		= 40;	// Open a area of 50% width
	const TYPE_WD75OPEN		= 41;	// Open a area of 75% width
	const TYPE_WDCLOSE		= 49;	// Close width area

	// Utility
	static public function initialize();
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
	static public function insertFieldFile($data);
	static public function insertFieldTextArea($data);
	static public function insertFieldCheckbox($data);
	static public function insertRadioButtons($data);
	static public function insertFieldDropList($data);
	static public function insertList($data, $indent = "");
	static public function insertButtons($data);
	static public function insertActionButtons($data);
	static public function insertSelectionTable($data);
	static public function insertHeadingBanner($data);
	static public function insertCheckList($data);
	static public function insertImage($data);

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

	// Templates and Engines
	static public function pageAutoGenerate($data);
	static public function loadTemplatePage($title, $url, $fname, $left, $right, $funcbar,
		$js_files, $css_files, $html_flags);
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
?>
						<option value="<?php echo $vx; ?>"><?php echo $kx; ?></option>
<?php
			}
			else
			{
?>
						<option value="<?php echo $vx; ?>" selected="selected"><?php echo $kx; ?></option>
<?php
			}
		}
		else
		{
?>
						<option value="<?php echo $vx; ?>"><?php echo $kx; ?></option>
<?php
		}
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
?>
		<div class="row">
			<div <?php echo $dcmST; ?> class="form-group<?php echo $stx; ?>">
				<label <?php echo $lclass . $forx ?>><?php echo $label; ?></label>
				<div<?php echo $fclass; ?>>
					<span<?php echo $icons; ?>><i<?php echo $icond; ?>></i></span>
					<input type="<?php echo $type; ?>" class="form-control"<?php echo $printout;?>>
					<span <?php echo $dcmGL; ?> class="glyphicon<?php echo $gix; ?> form-control-feedback"></span>
					<span <?php echo $dcmMS; ?>></span>
				</div>
			</div>
		</div>
<?php
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
?>
	<div>
		<form id="token_form">
			<input type="hidden" name="token_data" id="token_data" value="<?php echo $token; ?>">
		</form>
	</div>
<?php
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
?>
	<div>
		<form <?php echo $fname; ?>>
			<input type="hidden" <?php echo $name; ?> value="<?php echo $value; ?>">
		</form>
	</div>
<?php
	}
	
	// Inserts a text field
	static public function insertFieldText($data)
	{
		self::insertFieldTextCommon('text', $data);
	}

	// Inserts a password field
	static public function insertFieldPassword($data)
	{
		self::insertFieldTextCommon('password', $data);
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
?>
		<form <?php echo $fname; ?>>
			<div class="row">
				<div class="form-group">
					<label <?php echo $lclass; ?>><?php echo $label; ?></label>
					<div<?php echo $fclass; ?>>
						<input type="file" <?php echo $name; ?> class="form-control" multiple>
					</div>
				</div>
			</div>
		</form>
		<div class="row">
			<div class="form-group">
				<span <?php echo $lclass; ?>></span>
				<button <?php echo $bname . $action; ?> class="btn btn-default">Upload</button>
			</div>
		</div>
<?php
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
?>
		<div class="row">
			<div <?php echo $dcmST; ?>class="form-group<?php echo $stx; ?>">
				<label <?php echo $lclass . $forx ?>><?php echo $label; ?></label>
				<div<?php echo $fclass; ?>>
					<span<?php echo $icons; ?>><i<?php echo $icond; ?>></i></span>
					<textarea rows="<?php echo $rows; ?>" class="form-control"<?php echo $printout;?>><?php echo $value; ?></textarea>
					<span <?php echo $dcmGL; ?> class="glyphicon<?php echo $gix; ?> form-control-feedback"></span>
					<span <?php echo $dcmMS; ?>></span>
				</div>
			</div>
		</div>
<?php
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

		// Parameters
		self::helperNameId($data, $name, $forx);
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
		$printout = $name . $default . $tooltip . $disabled;

		// Render
		switch ($side)
		{
			case 0:		// Dual Mode: Left
?>
		<div class="row">
			<div class="form-group">
				<span<?php echo $padding; ?>></span>
				<label <?php echo $lclass_r . $forx; ?>><?php echo $label; ?></label>
				<div<?php echo $fclass; ?>>
<?php
				if ($toggle != 0)
				{
?>
					<label class="switch">
<?php
				}
?>
					<input type="checkbox" value="true" class="form-control"<?php echo $printout;?>>
<?php
				if ($toggle == 1)
				{
?>
					<span class="slider round">
					</label>
<?php
				}
				if ($toggle == 2)
				{
?>
					<span class="slider">
					</label>
<?php
				}
?>
				</div>
<?php
				break;
			case 1:		// Dual Mode: Right
?>
				<div<?php echo $fclass; ?>>
<?php
				if ($toggle != 0)
				{
?>
					<label class="switch">
<?php
				}
?>
					<input type="checkbox" value="true" class="form-control"<?php echo $printout;?>>
<?php
				if ($toggle == 1)
				{
?>
					<span class="slider round">
					</label>
<?php
				}
				if ($toggle == 2)
				{
?>
					<span class="slider">
					</label>
<?php
				}
?>
				</div>
				<label <?php echo $lclass_l . $forx; ?>><?php echo $label; ?></label>
				<span<?php echo $padding; ?>></span>
			</div>
		</div>
<?php
				break;
			case 2:		// Single Mode
?>
		<div class="row">
			<div class="form-group">
				<label <?php echo $lclass . $forx; ?>><?php echo $label; ?></label>
				<div<?php echo $fclass; ?>>
<?php
				if ($toggle != 0)
				{
?>
					<label class="switch">
<?php
				}
?>
					<input type="checkbox" value="true" class="form-control"<?php echo $printout;?>>
<?php
				if ($toggle == 1)
				{
?>
					<span class="slider round">
					</label>
<?php
				}
				if ($toggle == 2)
				{
?>
					<span class="slider">
					</label>
<?php
				}
?>
				</div>
			</div>
		</div>
<?php
				break;
		}
	}

	// Inserts a group of radio buttons.
	static public function insertRadioButtons($data)
	{
		$disabled = NULL;

		// Parameters
		helperDisabled($data, $disabled);
		if (isset($data['name'])) $name = 'name="' . $data['name'] .'"';
			else $name = '';
		if (!empty($data['default'])) $value = $data['default'];
			else $value = '';
		if (isset($data['data']))
		{
			$index = 0;
			foreach($data['data'] as $kx => $vx)
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
				if ($vx == $value)
				{
?>
		<div class="radio"<?php echo $tooltip; ?>>
			<label><input type="radio" <?php echo $name; echo $disabled; ?> value="<?php echo $vx; ?>" checked><?php echo $kx; ?></label>
		</div>
<?php
				}
				else
				{
?>
		<div class="radio"<?php echo $tooltip; ?>>
			<label><input type="radio" <?php echo $name; ?> value="<?php echo $vx; ?>"><?php echo $kx; ?></label>
		</div>
<?php
				}
				$index++;
			}
		}
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

?>
		<div class="row">
			<div <?php echo $dcmST; ?> class="form-group<?php echo $stx; ?>">
				<label <?php echo $lclass . $forx ?>><?php echo $label; ?></label>
				<div<?php echo $fclass; ?>>
					<span<?php echo $icons; ?>><i<?php echo $icond; ?>></i></span>
					<select class="form-control"<?php echo $printout; ?>>
<?php
		if ($blank == true)
		{
?>
						<option value="----">----</option>
<?php
		}
		if (!empty($data['optlist']))
		{
			foreach($data['optlist'] as $kx => $vx)
			{
				if (is_array($vx))
				{
?>
						<optgroup label="<?php echo $kx; ?>">
<?php
					foreach($vx as $kxa => $vxa)
					{
						self::insertFieldSelectHelper($kxa, $vxa, $default);
					}
?>
						</optgroup>
<?php
				}
				else
				{
					self::insertFieldSelectHelper($kx, $vx, $default);
				}
			}
		}
?>
					</select>
					<span <?php echo $dcmGL; ?> class="glyphicon<?php echo $gix; ?> form-control-feedback"></span>
					<span <?php echo $dcmMS; ?>></span>
				</div>
			</div>
		</div>
<?php
	}

	// Inserts a static bulleted list.
	// This function is recursive.
	static public function insertList($data, $indent = "")
	{
		// Check Input
		if (!is_array($data)) return;

		// Render
		echo $indent . "\t<ul>\n";
		foreach($data as $vx)
		{
			if (is_array($vx)) self::insertList($vx, $indent . "\t");
			else
			{
				echo $indent . "\t\t<li>" . $vx . "</li>\n";
			}
		}
		echo $indent . "\t</ul>\n";
	}

	// Inserts one or more button controls.
	static public function insertButtons($data)
	{
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
?>
		<div class="button">
			<div class="form-group">
<?php
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
			if (!empty($btndat['type']))
				$btnclass = ' class="btn btn-' . $btndat['type'] . ' col-xs-' . $width . $size . '"';
			else
				$btnclass = ' class="btn col-xs-' . $width . $size . '"';

			// Button Click Action
			if (!empty($btndat['onclick']))
				$btnclick = ' onclick="' . $btndat['onclick'] . '"';
			else
				$btnclick = '';

			// Render
			$printout = $btnclass . $btnname . $btnvalue . $btnclick;
			switch($direction)
			{
				case 0:	// Vertical
?>
		<div class="row">
			<div class="button">
				<div class="form-group">
<?php
					if ($space)
					{
?>
					<span class="col-xs-1"></span>
<?php
					}
?>
					<input type="button" <?php echo $printout; ?>>
				</div>
			</div>
		</div>
<?php
					break;
				case 1: // Horizontal
?>
<?php
					if ($space)
					{
?>
				<span class="col-xs-1"></span>
<?php
					}
?>
				<input type="button" <?php echo $printout; ?>>
<?php
					break;
				default:	// Unknown
					break;
			}
		}
		if ($direction == 1)
		{
?>
			</div>
		</div>
<?php
		}
	}

	// Inserts action buttons
	static public function insertActionButtons($data)
	{
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
				case self::BTNTYP_VIEW:
?>
		<div class="row">
			<div class="button">
				<div class="form-group">
					<span class="col-xs-4"></span>
					<input type="button" class="btn btn-success col-xs-4" name="initialview" value="Go Back" onclick="ajaxServerCommand.sendCommand(-1)">
					<span class="col-xs-4"></span>
				</div>
			</div>
		</div>
<?php

					break;
				case self::BTNTYP_UPDATE:
?>
			<div class="row">
				<div class="button">
					<div class="form-group">
						<span class="col-xs-1"></span>
						<input type="button" class="btn btn-danger col-xs-3" name="Submit" value="Submit Changes" onclick="<?php echo $action; ?>">
						<span class="col-xs-1"></span>
						<input type="button" class="btn btn-info col-xs-3" name="Reset" value="Reset" onclick="clearForm()">
						<span class="col-xs-1"></span>
						<input type="button" class="btn btn-success col-xs-3" name="initialview" value="Go Back" onclick="ajaxServerCommand.sendCommand(-1)">
					</div>
				</div>
			</div>
<?php
					break;
				case self::BTNTYP_INSERT:
?>
		<div class="row">
			<div class="button">
				<div class="form-group">
					<span class="col-xs-1"></span>
					<input type="button" class="btn btn-danger col-xs-3" name="Submit" value="Insert<?php echo ' ' . $dispname; ?>" onclick="<?php echo $action; ?>">
					<span class="col-xs-1"></span>
					<input type="button" class="btn btn-info col-xs-3" name="Reset" value="Reset" onclick="clearForm()">
					<span class="col-xs-1"></span>
					<input type="button" class="btn btn-success col-xs-3" name="initialview" value="Go Back" onclick="ajaxServerCommand.sendCommand(-1)">
				</div>
			</div>
		</div>
<?php
					break;
				case self::BTNTYP_DELETE:
?>
			<div class="row">
				<div class="button">
					<div class="form-group">
						<span class="col-xs-2"></span>
						<input type="button" class="btn btn-danger col-xs-3" name="Submit" value="Delete<?php echo ' ' . $dispname; ?>" onclick="<?php echo $action; ?>">
						<span class="col-xs-2"></span>
						<input type="button" class="btn btn-success col-xs-3" name="initialview" value="Go Back" onclick="ajaxServerCommand.sendCommand(-1)">
						<span class="col-xs-2"></span>
					</div>
				</div>
			</div>
<?php
					break;
				default:
					break;
			}
		}
	}

	// Inserts a selection table with radio buttons and field names.
	static public function insertSelectionTable($data)
	{
		$tooltip = NULL;

		// Parameters
		if (isset($data['name'])) $name = 'name="' . $data['name'] .'"';
			else $name = '';

		// Title Row
		if (isset($data['titles']))
		{
?>
		<table class="table table-hover table-condensed">
			<thead> 
				<tr>
					<th class="text-center">Select</th>
<?php
			foreach($data['titles'] as $kx)
			{
?>
					<th class="text-center"><?php echo $kx; ?></th>
<?php
			}
?>
				</tr>
			</thead>
<?php
		}

		// Table Data
		if (isset($data['tdata']))
		{
?>
			<tbody>
<?php
			// Row
			$index = 0;
			foreach($data['tdata'] as $kxr)
			{
				if (is_array($data['tooltip']))
				{
					if (!empty($data['tooltip'][$index]))
					{
						$ttText = $data['tooltip'][$index];
						$tooltip = ' data-toggle="tooltip" data-html="true" title="' . $ttText . '"'; 
					}
					else $tooltip = '';
				}
				else $tooltip = '';
?>
				<tr<?php echo $tooltip; ?>>
<?php
				// Column
				$count = 0;
				foreach($kxr as $kxc)
				{
					if ($count == 0)
					{
?>
					<td class="text-center">
						<div class="radio">
							<label><input type="radio" <?php echo $name; ?> value="<?php echo $kxc; ?>"></label>
						</div>
					</td>
<?php
					}
					else
					{
?>
					<td class="text-center"><?php echo $kxc; ?></td>
<?php
					}
					$count++;
				}
?>
				</tr>
<?php
				$index++;
			}
?>
			</tbody>
		</table>
<?php
		}
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
		
		if (!empty($msg1) || !empty($msg2))
		{
?>
		<h1 class="text-center"><?php echo $msg1; ?><span class="color-blue"><?php echo $msg2; ?></span></h1>
<?php
		}
		if (!empty($warn))
		{
?>
		<h4 class="text-center color-red">WARNING<br><?php echo $warn; ?></h4>
<?php
		}
	}

	// Generates a list of checkboxes.
	// lsize, fsize, and the list array.
	// The list array uses the same format for each entry:
	// flag - flag number
	// label - display label
	// tooltip - popup description
	// - indicates if the item is checked or not
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
		foreach ($data['list'] as $kx => $vx)
		{
			$dxa = array(
				'name' => self::CHECKFLAG_NAME . $vx['flag'],
				'label' => $vx['label'],
				'lsize' => $lsize,
				'fsize' => $fsize,
				'default' => ($default) ? $vx['default'] : false,
				'disable' => $disable,
				'tooltip' => $vx['tooltip'],
			);
			if ($count < $loopterm)
			{
				$dxa['sidemode'] = true;
				$dxa['side'] = ($count & 0x00000001) ? 1 : 0;
			}
			self::insertFieldCheckbox($dxa);
			$count++;
		}
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
		
		self::helperLabelSizeText($data, $lclass);
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
		$printout = $name . $source . $altxt . $width . $height;
	?>
		<div class="row">
			<div class="form-group">
				<label <?php echo $lclass; ?>></label>
				<div>
					<img <?php echo $printout; ?>>
				</div>
			</div>
		</div>
	<?php
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
?>
	<form<?php echo $name . $method . $action . $class; ?>>
<?php
	}

	// Closes a form tag
	static public function closeForm()
	{
?>
	</form>
<?php
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
?>
	<fieldset<?php echo $disabled; ?>>
<?php
		if (!empty($name))
		{
?>
		<legend><?php echo $name; ?></legend>
<?php
		}
	}

	// Closes a fieldset tag	
	static public function closeFieldset()
	{
?>
	</fieldset>
<?php
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
?>
	<div <?php echo $printout; ?>>
<?php
	}

	// Closes a hidden block
	static public function closeHiddenBlock()
	{
?>
	</div>
<?php
	}

	// Renders a top border type 1
	static public function border1top()
	{
?>
<div class="image-border-top">
	<img src="<?php echo self::$base_url; ?>/images/border1a.gif" alt="border1a">
</div>
<?php
	}

	// Renders a top border type 1
	static public function border2top()
	{
?>
<div class="image-border-top">
	<img src="<?php echo self::$base_url; ?>/images/border2a.gif" alt="border2a">
</div>
<?php
	}
	
	// Renders a bottom border type 1
	static public function border1bottom()
	{
?>
<div class="image-border-bottom">
	<img src="<?php echo self::$base_url; ?>/images/border1b.gif" alt="border1b">
</div>
<?php
	}
	
	// Renders a bottom border type 1
	static public function border2bottom()
	{
?>
<div class="image-border-bottom">
	<img src="<?php echo self::$base_url; ?>/images/border2b.gif" alt="border2b">
</div>
<?php
	}

	// Create an area that is 50% the width of the browser window.
	static public function width50open()
	{
?>
<div class="width50">
<?php
	}
	
	// Create an area that is 75% the width of the browser window.
	static public function width75open()
	{
?>
<div class="width75">
<?php
	}

	// Closes a previously opened width area.
	static public function widthClose()
	{
?>
</div>
<?php
	}
	
	// Generates an HTML page according to input data.
	// Each element requires a type parameter
	static public function pageAutoGenerate($data)
	{
		// Check Input
		if (!is_array($data)) return;

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
						self::insertFieldHidden($vx);
						break;
					case self::TYPE_TEXT:
						self::insertFieldText($vx);
						break;
					case self::TYPE_PASS:
						self::insertFieldPassword($vx);
						break;
					case self::TYPE_FILE:
						self::insertFieldFile($vx);
						break;
					case self::TYPE_AREA:
						self::insertFieldTextArea($vx);
						break;
					case self::TYPE_CHECK:
						self::insertFieldCheckbox($vx);
						break;
					case self::TYPE_RADIO:
						self::insertRadioButtons($vx);
						break;
					case self::TYPE_PULLDN:
						self::insertFieldDropList($vx);
						break;
					case self::TYPE_BLIST:
						self::insertList($vx['data']);
						break;
					case self::TYPE_BUTTON:
						self::insertButtons($vx);
						break;
					case self::TYPE_RADTABLE:
						self::insertSelectionTable($vx);
						break;
					case self::TYPE_ACTBTN:
						self::insertActionButtons($vx);
						break;
					case self::TYPE_HEADING:
						self::insertHeadingBanner($vx);
						break;
					case self::TYPE_CHECKLIST:
						self::insertCheckList($vx);
						break;
					case self::TYPE_IMAGE:
						self::insertImage($vx);
						break;
					case self::TYPE_FORMOPEN:
						self::openForm($vx);
						break;
					case self::TYPE_FORMCLOSE:
						self::closeForm();
						break;
					case self::TYPE_FSETOPEN:
						self::openFieldset($vx);
						break;
					case self::TYPE_FSETCLOSE:
						self::closeFieldset();
						break;
					case self::TYPE_HIDEOPEN:
						self::openHiddenBlock($vx);
						break;
					case self::TYPE_HIDECLOSE:
						self::closeHiddenBlock();
						break;
					case self::TYPE_TOPB1:
						self::border1top();
						break;
					case self::TYPE_TOPB2:
						self::border2top();
						break;
					case self::TYPE_BOTB1:
						self::border1bottom();
						break;
					case self::TYPE_BOTB2:
						self::border2bottom();
						break;
					case self::TYPE_WD50OPEN:
						self::width50open();
						break;
					case self::TYPE_WD75OPEN:
						self::width75open();
						break;
					case self::TYPE_WDCLOSE:
						self::widthClose();
						break;
					default:
				}
			}
		}
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
	static public function loadTemplatePage($title, $url, $fname, $left, $right, $funcbar,
		$js_files, $css_files, $html_flags)
	{
		global $session;

		// Set flags
		if (is_array($left))       $flag_left = true;     else $flag_left = false;
		if (is_array($right))      $flag_right = true;    else $flag_right = false;
		if (is_array($funcbar))    $flag_fbar = true;     else $flag_fbar = false;
		if (is_array($js_files))   $flag_jsfile = true;   else $flag_jsfile = false;
		if (is_array($css_files))  $flag_cssfile = true;  else $flag_cssfile = false;
		// Used to activate features
		if (is_array($html_flags))
		{
			if (!empty($html_flags['checkbox'])) $flag_checkbox = true; else $flag_checkbox = false;
			if (!empty($html_flags['datepick'])) $flag_datepick = true; else $flag_datepick = false;
			if (!empty($html_flags['tooltip'])) $flag_tooltip = true; else $flag_tooltip = false;
		}
		else
		{
			$flag_checkbox = false;
			$flag_datepick = false;
			$flag_tooltip = false;
		}

?>
<!DOCTYPE html>
<html lang="enUS">
	<head>
		<title><?php echo $title; ?></title>
		<!-- Install baseline Ajax system -->
		<script type="text/javascript" src="<?php echo $url; ?>/js/ajax.js"></script>
		<script type="text/javascript" src="<?php echo $url; ?>/js/heartbeat.js"></script>
		<script type="text/javascript" src="<?php echo $url; ?>/js/treewalker.js"></script>
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
?>
	</head>
	<body href-link="<?php echo $fname; ?>">
		<!-- Beginning of header Nav Bar -->
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
					<img class="navbar-brand" alt="Brand" src="<?php echo $url; ?>/images/seacore_logo_base_small.png">
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
		<nav id="functionBar" class="nav nav-inline">
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
?>
		<!-- Start of main content area -->
		<div id="mainFrame">
			<div style="color: red;" id="errorTarget"></div>
			<div style="color: blue;" id="responseTarget"></div>
			<div id="main" class="main-wrapper-div"></div>
		</div>
		<!-- End of main content area -->
<?php
		$token = $session->getToken();
		if ($token != false)
		{
			self::insertToken($token);
		}
?>
		<!-- Install Timer -->
		<script type="text/javascript" src="<?php echo $url; ?>/js/timer.js"></script>
		<!-- Install regular jQuery -->
		<script type="text/javascript" src="<?php echo $url; ?>/APIs/JQuery/BaseJQuery/jquery-3.1.0.min.js"></script>
		<!-- Install Bootstrap -->
		<script type="text/javascript" src="<?php echo $url; ?>/APIs/Bootstrap/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
<?php
		if ($flag_checkbox == true)
		{
?>
		<!-- Install Custom Checkbox -->
		<script src="<?php echo $url; ?>/APIs/checkbox/dist/js/bootstrap-checkbox.min.js"></script>
<?php
		}
?>
		<!-- Install Header Custom JavaScript -->
		<script type="text/javascript" src="<?php echo $url; ?>/js/header.js"></script>
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
		<script type="text/javascript">
			function featureTooltip() {
				$('[data-toggle="tooltip"]').tooltip();
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