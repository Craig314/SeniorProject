/*

SEA-CORE International Ltd.
SEA-CORE Development Group

Template JavaScript File

*/

// List of hidden divs controlled by drop down list.
// This must match the hiddenSelect ID select tag in item count and
// the items must correspond to each other.
// Do not remove this or other things will break;
var hiddenList = [
	'dataOther',
	'dataOther',
	'dataBoolean',
	'dataLongString',
	'dataOther',
];
var hiddenSelect = 'datatype';

// Performs custom data varification.
function customVerifyData(item) {
	var select;
	var result;

	select = getHidden();
	switch (parseInt(select.value)) {
		case 0:		// String
			if (item.name == 'datavalue3') {
				item.ctype = VFYSTR_ASCII;
				item.noblank = true;
				item.min = 1;
				item.max = 512;
				result = verifyData.customVerify(item);
			} else result = true;
			break;
		case 1:		// Integer
			if (item.name == 'datavalue3') {
				item.ctype = VFYSTR_INTEGER;
				item.noblank = true;
				result = verifyData.customVerify(item);
			} else result = true;
			break;
		case 2:		// Boolean
			result = true;
			break;
		case 3:		// Long String
			if (item.name == 'datavalue1') {
				item.ctype = VFYSTR_ASCII;
				item.noblank = true;
				item.min = 1;
				item.max = 512;
				result = verifyData.customVerify(item);
			} else result = true;
			break;
		case 10:	// Time Display
			if (item.name == 'datavalue3') {
				item.ctype = VFYSTR_INTEGER;
				item.noblank = true;
				item.min = 5;
				item.max = 6;
				result = verifyData.customVerify(item);
			} else result = true;
			break;
		default:
			console.warn('Invalid selection detected. SELECT=' + select.value);
			result = false;
			break;
	}
	return result;
}

