/*

SEA-CORE International Ltd.
SEA-CORE Development Group

Verify Data, Client Side

Object to perform checks on client side data before
server submission.

*/

// Operational Modes
const VERIFY_MODE_OTHER		= 0;	// All modes not insert.
const VERIFY_MODE_INSERT	= 1;	// Insert Mode

// String comparison constant types.
// This must **EXACTLY** match the type constants in vfystr.php.
const VFYSTR_NONE			= 0;	// No Verification
const VFYSTR_USERID			= 1;	// User ID
const VFYSTR_PASSWD			= 2;	// Password
const VFYSTR_NAME			= 3;	// Name
const VFYSTR_ADDR			= 4;	// Address
const VFYSTR_PHONE			= 5;	// Phone Number
const VFYSTR_EMAIL			= 6;	// EMail Address
const VFYSTR_ASCII			= 7;	// ASCII String
const VFYSTR_ALPHA			= 8;	// Alpha Only
const VFYSTR_NUMERIC		= 9;	// Numeric Only
const VFYSTR_ALPHANUM		= 10;	// Alphanumeric
const VFYSTR_ALPHANUMPUNCT	= 11;	// Alphanumeric + Punctuation.
const VFYSTR_PINTEGER		= 12;	// Positive Integer
const VFYSTR_INTEGER		= 13;	// Integer (decimal or 0x hex format)
const VFYSTR_FLOAT			= 14;	// Floating Point
const VFYSTR_DATE			= 15;	// Date
const VFYSTR_DATEUNIX		= 16;	// Unix Date Timestamp
const VFYSTR_FILENAME		= 17;	// Filename
const VFYSTR_PATHNAME		= 18;	// Pathname
const VFYSTR_URI			= 19;	// Uniform Resource Identifier
const VFYSTR_URL			= 20;	// Uniform Resource Location
const VFYSTR_TIMEDISP		= 21;	// Time Displacement
const VFYSTR_ALPHASPEC		= 22;	// Alpha Spec'd (a-z_ only)
const VFYSTR_DESC			= 23;	// Description
const VFYSTR_CUSTOM			= 99;	// Custom verifications

// Custom field types start at 100.

// Verify data object definition.
var verifyData = ({
	loadFlag: false,		// Flag to indicate the data was loaded.
	fieldData: null,		// Field checking information.
	verifyStatus: true,		// Verification status.

	// Loads data for later processing
	loadFieldData: function(strJson) {
		try {
			this.fieldData = JSON.parse(strJson);
		}
		catch (error) {
			writeError('Error decoding JSON field data<br>' + error.message);
			return;
		}
		this.loadFlag = true;
	},

	// Returns the current load status of the field data.
	getLoadStatus: function() {
		return this.loadFlag;
	},

	// Returns an array listing of the field names.
	getFieldData: function() {
		if (this.loadFlag == false || this.fieldData == null) {
			console.warn('verifyData.getFieldNames called with no field data.');
			return null;
		}
		return this.fieldData;
	},

	// Starts the process of verifying the field data.
	// Returns true if no errors were detected.
	// Returns false if errors were detected.
	verify: function(mode) {
		var i;
		var result;
		var itemObject;

		// Make sure that something was loaded.
		if (this.loadFlag == false || this.fieldData == null) {
			console.warn('verifyData.verify called with no field data.');
			return true;
		}

		// First clear all field errors.
		resetErrorStatus();

		// Now we look for errors.
		result = true;
		for (i = 0; i < this.fieldData.length; i++) {
			itemObject = JSON.parse(JSON.stringify(this.fieldData[i]));
			result = (this.launchVerify(itemObject, mode) == false)
				? false : result;
		}

		// If there are any final verification tasks that need to be done,
		// we do them here.
		if (typeof customVerifyFinal == 'function') {
			result = (customVerifyFinal() == false) ? false : result;
		}

		// If errors were found, then we notify the user.
		if (result == false) {
			writeResponse('');
			writeError('The form that you are trying to submit contains errors.' +
				'<br>Correct the hghlighted fields and try again.');
		}

		return result;
	},

	// Used by customVerifyData for data verification.
	customVerify: function(item) {
		var result;

		item.type = item.ctype;
		console.log(item);
		result = this.launchVerify(item, VERIFY_MODE_OTHER);
		item.type = VFYSTR_CUSTOM;
		return result;
	},

	// This is where the actual verification takes place.
	launchVerify: function(item, mode) {
		var fieldObject;
		var result;
		var value;
		
		// Retrieve the field object id.
		fieldObject = document.getElementById(item.name);
		if (fieldObject == null) {
			console.warn('verifyData.launchVerify: Missing field.  FIELD=' + item.name);
			writeError('Missing field from server.  Contact your administrator.');
			return false;
		}

		// Checkboxes, radio buttons, and buttons are ignored.
		switch (fieldObject.type.toLowerCase()) {
			case 'checkbox':
			case 'radio':
			case 'button':
				return true;
				break;
			default:
				break;
		}
		value = fieldObject.value;

		// For custom object verifications, this handles the twopass flag.
		if (typeof item.twopass === 'undefined') {
			if (item.type == VFYSTR_CUSTOM) {
				item.twopass = true;
				if (typeof customVerifyData == 'function') {
					result = customVerifyData(item);
				} else {
					console.warn('Data specification from server indicates ' +
						'that custom verification is required, but the ' +
						'function customdDataVerify is missing.');
					result = false;
				}
			}
		}

		// Check if the field is blank if blank fields are not allowed.
		switch (mode) {
			case VERIFY_MODE_INSERT:
				if (typeof item.noblankins !== 'undefined') {
					if (item.noblankins == true) {
						if (this.verify_blank(item, value) == false) return false;
					} else if (value.length == 0) return true;
				}
				if (item.noblank == true) {
					if (this.verify_blank(item, value) == false) return false;
				} else if (value.length == 0) return true;
				break;
			default:
				if (item.noblank == true) {
					if (this.verify_blank(item, value) == false) return false;
				} else if (value.length == 0) return true;
				break;
		}

		// Check the length of string value types.  If the type is one of
		// the numeric types, then the number ranges are checked in their
		// respective verification methods.
		switch (item.type) {
			case VFYSTR_NUMERIC:
			case VFYSTR_PINTEGER:
			case VFYSTR_INTEGER:
			case VFYSTR_FLOAT:
				break;
			default:
				if (item.min < item.max) {
					if (this.verify_minlen(item, value) == false) return false;
					if (this.verify_maxlen(item, value) == false) return false;
				}
				break;
		}

		// This is where the data type is looked at and the verification
		// method called to verify the data.
		switch (item.type) {
			case VFYSTR_NONE:
				result = true;
				break;
			case VFYSTR_USERID:
				result = this.verify_userid(item, value);
				break;
			case VFYSTR_PASSWD:
				result = this.verify_password(item, value);
				break;
			case VFYSTR_NAME:
				result = this.verify_name(item, value);
				break;
			case VFYSTR_ADDR:
				result = this.verify_address(item, value);
				break;
			case VFYSTR_PHONE:
				result = this.verify_phone(item, value);
				break;
			case VFYSTR_EMAIL:
				result = this.verify_email(item, value);
				break;
			case VFYSTR_ASCII:
				result = this.verify_ascii(item, value);
				break;
			case VFYSTR_ALPHA:
				result = this.verify_alpha(item, value);
				break;
			case VFYSTR_NUMERIC:
				result = this.verify_numeric(item, value);
				break;
			case VFYSTR_ALPHANUM:
				result = this.verify_alphanum(item, value);
				break;
			case VFYSTR_ALPHANUMPUNCT:
				result = this.verify_alphanumpunct(item, value);
				break;
			case VFYSTR_PINTEGER:
				result = this.verify_pinteger(item, value);
				break;
			case VFYSTR_INTEGER:
				result = this.verify_integer(item, value);
				break;
			case VFYSTR_FLOAT:
				result = this.verify_float(item, value);
				break;
			case VFYSTR_DATE:
				result = this.verify_date(item, value);
				break;
			case VFYSTR_DATEUNIX:
				result = this.verify_dateunix(item, value);
				break;
			case VFYSTR_FILENAME:
				result = this.verify_filename(item, value);
				break;
			case VFYSTR_PATHNAME:
				result = this.verify_pathname(item, value);
				break;
			case VFYSTR_URI:
				result = this.verify_uri(item, value);
				break;
			case VFYSTR_URL:
				result = this.verify_url(item, value);
				break;
			case VFYSTR_TIMEDISP:
				result = this.verify_timedisp(item, value);
				break;
			case VFYSTR_ALPHASPEC:
				result = this.verify_alphaspec(item, value);
				break;
			case VFYSTR_DESC:
				result = this.verify_description(item, value);
				break;
			case VFYSTR_CUSTOM:
				if (typeof customVerifyData == 'function') {
					result = customVerifyData(item);
				} else {
					console.warn('Data specification from server indicates ' +
						'that custom verification is required, but the ' +
						'function customdDataVerify is missing.');
					result = false;
				}
				break;
			// case :
			// 	result = this.verify_(item, value);
			// 	break;
			default:
				console.warn('verifyData.launchVerify: Unknown type' +
				' specified.  FIELD=' + item.value);
				result = false;
		}
		return result;
	},

	// Checks for blank field.
	verify_blank: function(item, value) {
		if (value.length == 0) {
			ajaxProcessData.setStatusTextError(item.name,
				'Blank field is not allowed.');
			return false;
		}
		return true;
	},

	// Checks the minimum string length.
	verify_minlen: function(item, value) {
		if (item.min >= 0) {
			if (value.length < item.min) {
				ajaxProcessData.setStatusTextError(item.name,
					'Length too short. Minimum length is ' + item.min +
					' characters.');
				return false;
			}
		}
		return true;
	},

	// Checks the maximum string length.
	verify_maxlen: function(item, value) {
		if (item.max > 0) {
			if (value.length > item.max) {
				ajaxProcessData.setStatusTextError(item.name,
					'Length too long. Maximum length is ' + item.max +
					' characters.');
				return false;
			}
		}
		return true;
	},

	// Checks for invalid characters in a username.
	verify_userid: function(item, value) {
		var regex = /^[A-Za-z0-9\_\-]+$/;
		var validChars = 'A-Za-z0-9_-';
		var result;

		result = regex.test(value);
		if (result == false) {
			ajaxProcessData.setStatusTextError(item.name,
				'Invalid characters detected. Valid characters: ' + validChars);
		}
		return result;
	},

	// Checks for invalid characters in a password.
	verify_password: function(item, value) {
		var regex = /^[\x20-\x7E]*$/;
		var validChars = 'ASCII 32-126';
		var result;

		result = regex.test(value);
		if (result == false) {
			ajaxProcessData.setStatusTextError(item.name,
				'Invalid characters detected. Valid characters: ' + validChars);
		}
		return result;
	},

	// Checks for invalid characters in a person's name.
	verify_name: function(item, value) {
		var regex = /^[A-Za-z\ ]+$/;
		var validChars = 'A-Za-z <SPACE>';
		var result;

		result = regex.test(value);
		if (result == false) {
			ajaxProcessData.setStatusTextError(item.name,
				'Invalid characters detected. Valid characters: ' + validChars);
		}
		return result;
	},

	// Checks for invalid characters in an address.
	verify_address: function(item, value) {
		var regex = /^[\t\n\r\x20-\x7E]*$/;
		var validChars = 'ASCII 32-126 <TAB> <ENTER>';
		var result;

		result = regex.test(value);
		if (result == false) {
			ajaxProcessData.setStatusTextError(item.name,
				'Invalid characters detected. Valid characters: ' + validChars);
		}
		return result;
	},

	// Checks for invalid characters in a phone number.
	verify_phone: function(item, value) {
		var regex = /^[0-9\(\)\-\ ]+$/;
		var validChars = '0-9( )-';
		var result;

		result = regex.test(value);
		if (result == false) {
			ajaxProcessData.setStatusTextError(item.name,
				'Invalid characters detected. Valid characters: ' + validChars);
		}
		return result;
	},

	// Checks for invalid characters in a email address.
	verify_email: function(item, value) {
		var regex = /^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w{2,}([-.]\w+)*$/;
		var validChars = 'A-Za-z0-0+-.@';
		var result;

		result = regex.test(value);
		if (result == false) {
			ajaxProcessData.setStatusTextError(item.name,
				'Invalid characters detected. Valid characters: ' + validChars);
		}
		return result;
	},

	// Checks for invalid characters in an ASCII string.
	verify_ascii: function(item, value) {
		var regex = /^[\x20-\x7E]*$/;
		var validChars = 'ASCII 32-126';
		var result;

		result = regex.test(value);
		if (result == false) {
			ajaxProcessData.setStatusTextError(item.name,
				'Invalid characters detected. Valid characters: ' + validChars);
		}
		return result;
	},

	// Checks for invalid characters in a alpha only string.
	verify_alpha: function(item, value) {
		var regex = /^[A-Za-z\ ]+$/;
		var validChars = 'A-Za-z';
		var result;

		result = regex.test(value);
		if (result == false) {
			ajaxProcessData.setStatusTextError(item.name,
				'Invalid characters detected. Valid characters: ' + validChars);
		}
		return result;
	},

	// Checks for invalid characters in a numeric string.
	verify_numeric: function(item, value) {
		var regex = /^[+|-]?[0-9]+$/;
		var validChars = '+-0-9';
		var result;

		result = regex.test(value);
		if (result == false) {
			ajaxProcessData.setStatusTextError(item.name,
				'Invalid characters detected. Valid characters: ' + validChars);
		} else {
			if (parseInt() == 'NaN') {
				ajaxProcessData.setStatusTextError(item.name,
					'Invalid number format.');
				result = false;
			} else if (item.max > item.min) {
				if (parseInt() < item.min) {
					ajaxProcessData.setStatusTextError(item.name,
						'Numeric value too small.  The minimum allowed value is ' +
						item.min + '.');
					result = false;
				} else if (parseInt() > item.max) {
					ajaxProcessData.setStatusTextError(item.name,
						'Numeric value too large.  The maximum allowed value is ' +
						item.max + '.');
					result = false;
				}
			}
		}
		return result;
	},

	// Checks for invalid characters in a alphanumeric string.
	verify_alphanum: function(item, value) {
		var regex = /^[A-Za-z0-9\ _-]+$/;
		var validChars = 'A-Za-z0-9 _-';
		var result;

		result = regex.test(value);
		if (result == false) {
			ajaxProcessData.setStatusTextError(item.name,
				'Invalid characters detected. Valid characters: ' + validChars);
		}
		return result;
	},

	// Checks for invalid characters in an alphanumeric string
	// with punctuation.
	verify_alphanumpunct: function(item, value) {
		var regex = /^[A-Za-z0-9\ \_\-\,\.\!\;\:\'\"\`\?]+$/;
		var validChars = 'A-Za-z0-9 _-,.!;:\'"?';
		var result;

		result = regex.test(value);
		if (result == false) {
			ajaxProcessData.setStatusTextError(item.name,
				'Invalid characters detected. Valid characters: ' + validChars);
		}
		return result;
	},

	// Checks for invalid characters in positive integers.
	verify_pinteger: function(item, value) {
		var regex = /^([1-9][0-9]*)|([0-9])$/;
		var validChars = '0-9';
		var result;

		result = regex.test(value);
		if (result == false) {
			ajaxProcessData.setStatusTextError(item.name,
				'Invalid characters detected. Valid characters: ' + validChars);
		} else {
			if (parseInt() == 'NaN') {
				ajaxProcessData.setStatusTextError(item.name,
					'Invalid number format.');
				result = false;
			} else if (item.max > item.min) {
				if (parseInt() < item.min) {
					ajaxProcessData.setStatusTextError(item.name,
						'Numeric value too small.  The minimum allowed value is ' +
						item.min + '.');
					result = false;
				} else if (parseInt() > item.max) {
					ajaxProcessData.setStatusTextError(item.name,
						'Numeric value too large.  The maximum allowed value is ' +
						item.max + '.');
					result = false;
				}
			}
		}
		return result;
	},

	// Checks for invalid characters in integers.
	verify_integer: function(item, value) {
		var regex = /^((0x[0-9A-Fa-f]+)|([+|-]?(([1-9][0-9]+)|([0-9]))))$/;
		var validChars = '+-0-9 OR Hex 0x0-9A-Fa-f';
		var result;

		result = regex.test(value);
		if (result == false) {
			ajaxProcessData.setStatusTextError(item.name,
				'Invalid characters detected. Valid characters: ' + validChars);
		} else {
			if (parseInt() == 'NaN') {
				ajaxProcessData.setStatusTextError(item.name,
					'Invalid number format.');
				result = false;
			} else if (item.max > item.min) {
				if (parseInt() < item.min) {
					ajaxProcessData.setStatusTextError(item.name,
						'Numeric value too small.  The minimum allowed value is ' +
						item.min + '.');
					result = false;
				} else if (parseInt() > item.max) {
					ajaxProcessData.setStatusTextError(item.name,
						'Numeric value too large.  The maximum allowed value is ' +
						item.max + '.');
					result = false;
				}
			}
		}
		return result;
	},

	// Checks for invalid characters in float numbers.
	verify_float: function(item, value) {
		var regex = /^[+|-]?(([1-9][0-9]*(\.[0-9]+)?)|(0(\.[0-9]+)?))([E|e][+|-][0-9]+)?$/;
		var validChars = '0-9Ee+-.';
		var result;

		result = regex.test(value);
		if (result == false) {
			ajaxProcessData.setStatusTextError(item.name,
				'Invalid characters detected. Valid characters: ' + validChars);
		} else {
			if (parseFloat() == 'NaN') {
				ajaxProcessData.setStatusTextError(item.name,
					'Invalid number format.');
				result = false;
			} else if (item.max > item.min) {
				if (parseFloat() < item.min) {
					ajaxProcessData.setStatusTextError(item.name,
						'Numeric value too small.  The minimum allowed value is ' +
						item.min + '.');
					result = false;
				} else if (parseFloat() > item.max) {
					ajaxProcessData.setStatusTextError(item.name,
						'Numeric value too large.  The maximum allowed value is ' +
						item.max + '.');
					result = false;
				}
			}
		}
		return result;
	},

	// Checks for invalid characters in a date.
	verify_date: function(item, value) {
		return this.date_helper(item, value, 1600, 9999);
	},

	// Checks for invalid characters in Unix style date.
	verify_dateunix: function(item, value) {
		return this.date_helper(item, value, 1970, 2038);
	},

	// Checks for invalid characters in a filename.
	verify_filename: function(item, value) {
		var regex = /^[A-Za-z0-9\.\_\-]+$/;
		var validChars = 'A-Za-z0-9.-_';
		var result;

		result = regex.test(value);
		if (result == false) {
			ajaxProcessData.setStatusTextError(item.name,
				'Invalid characters detected. Valid characters: ' + validChars);
		}
		return result;
	},

	// Checks for invalid characters in a pathname.
	verify_pathname: function(item, value) {
		var regex = /^[A-Za-z0-9\/\.\_\-]+$/;
		var validChars = 'A-Za-z0-9/.-_';
		var result;

		result = regex.test(value);
		if (result == false) {
			ajaxProcessData.setStatusTextError(item.name,
				'Invalid characters detected. Valid characters: ' + validChars);
		}
		return result;
	},

	// Checks for invalid characters in URIs.
	verify_uri: function(item, value) {
		var regex = /^(http|https)\:\/\/([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?([a-z0-9-.]*)\.([a-z]{2,3})(\:[0-9]{2,5})?(\/([a-z0-9+\$_-]\.?)+)*\/?(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?(#[a-z_.-][a-z0-9+\$_.-]*)?$/;
		var validChars = '';
		var result;

		result = regex.test(value);
		if (result == false) {
			ajaxProcessData.setStatusTextError(item.name,
				'Invalid format or characters detected in Universal Resource Information.');
		}
		return result;
	},

	// Checks for invalid characters in URLs.
	verify_url: function(item, value) {
		var regex = /^(http|https)\:\/\/([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?([a-z0-9-.]*)\.([a-z]{2,3})(\:[0-9]{2,5})?(\/([a-z0-9+\$_-]\.?)+)*\/?$/;
		var result;

		result = regex.test(value);
		if (result == false) {
			ajaxProcessData.setStatusTextError(item.name,
				'Invalid format or characters detected in Universal Resource Locator.');
		}
		return result;
	},

	// Checks for invalid characters in a time displacement.
	verify_timedisp: function(item, value) {
		var regex = /^[+|-](([0-1]?[0-9])|(2[0-3])):[0-5][0-9]$/;
		var validChars = '+-00:00 to +-23:59';
		var result;

		result = regex.test(value);
		if (result == false) {
			ajaxProcessData.setStatusTextError(item.name,
				'Invalid format or characters detected.<br>' +
				'Valid format/characters: ' + validChars);
		}
		return result;
	},

	// Checks for invalid characters in a alphaspec string.
	verify_alphaspec: function(item, value) {
		var regex = /^[a-z\_]+$/;
		var validChars = 'a-z_';
		var result;

		result = regex.test(value);
		if (result == false) {
			ajaxProcessData.setStatusTextError(item.name,
				'Invalid characters detected. Valid characters: ' + validChars);
		}
		return result;
	},

	// Checks for invalid characters in a description.
	verify_description: function(item, value) {
		var regex = /^[\x20-\x7E]*$/;
		var validChars = 'ASCII 32-126';
		var result;

		result = regex.test(value);
		if (result == false) {
			ajaxProcessData.setStatusTextError(item.name,
				'Invalid characters detected. Valid characters: ' + validChars);
		}
		return result;
	},

		// Checks for invalid characters in
		// verify_: function(item, value) {
		// 	var regex = //;
		// 	var validChars = '';
		// 	var result;
	
		// 	result = regex.test(value);
		// 	if (result == false) {
		// 		ajaxProcessData.setStatusTextError(item.name,
		// 			'Invalid characters detected. Valid characters: ' + validChars);
		// 	}
		// 	return result;
		// },
	
	date_helper: function(item, value, minyear, maxyear) {
		var days = [0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
		var separator = ['/', '\\', '=', '-', '.', '|', ':', '+'];
		var data;
		var i;
		var result;
		var found;
		var sepChar;

		// Determine which separator is in use.
		found = false;
		for (i = 0; i < separator.length; i++) {
			result = value.indexOf(separator[i]);
			if (result >= 0) {
				found = true;
				sepChar = separator[i];
				break;
			}
		}
		if (found == false) {
			ajaxProcessData.setStatusTextError(item.name,
				'Invalid date format detected.  Date should be in MM/DD/YYYY format.');
			return false;
		}

		// Now break the date up into it's component parts and analyze them.
		data = value.split(sepChar);
		if (parseInt(data[0]) < 1 || parseInt(data[0]) > 12) {
			ajaxProcessData.setStatusTextError(item.name,
				'Month value is out of range.  Must be between 1 and 12 inclusive.');
			return false;
		}
		if (parseInt(data[1]) < 1 || parseInt(data[1]) > days[parseInt(data[0])]) {
			ajaxProcessData.setStatusTextError(item.name,
				'Day value is out of range for specified month.<br>' +
				'Must be between 1 and ' + days[parseInt(data[0])] + ' inclusive.');
			return false;
		}
		if (item.min < item.max) {
			minyear = item.min;
			maxyear = item.max;
		}
		if (parseInt(data[2]) < minyear || parseInt(data[2]) > maxyear) {
			ajaxProcessData.setStatusTextError(item.name,
				'Year value is out of range.  Must be between ' + minyear +
				' and ' + maxyear + 'inclusive.');
			return false;
		} 
		return true;
	},


});