/*

SEA-CORE International Ltd.
SEA-CORE Development Group

Parameter Edit JavaScript File

*/

// Performs custom data varification.
function customVerifyData(item, mode) {
	var select;
	var result;

	switch (parseInt(item.dtype)) {
		case 0:		// String
			if (item.name == 'datavalue') {
				item.ctype = VFYSTR_ASCII;
				item.noblank = true;
				item.min = 1;
				item.max = 512;
				result = verifyData.customVerify(item);
			} else result = true;
			break;
		case 1:		// Integer
			if (item.name == 'datavalue') {
				item.ctype = VFYSTR_INTEGER;
				item.noblank = true;
				result = verifyData.customVerify(item);
			} else result = true;
			break;
		case 2:		// Boolean
			result = true;
			break;
		case 3:		// Long String
			if (item.name == 'datavalue') {
				item.ctype = VFYSTR_ASCII;
				item.noblank = true;
				item.min = 1;
				item.max = 512;
				result = verifyData.customVerify(item);
			} else result = true;
			break;
		case 10:	// Time Display
			if (item.name == 'datavalue') {
				item.ctype = VFYSTR_INTEGER;
				item.noblank = true;
				item.min = 5;
				item.max = 6;
				result = verifyData.customVerify(item);
			} else result = true;
			break;
		default:
			console.warn('Invalid selection detected. SELECT=' + item.dtype);
			result = false;
			break;
	}
	return result;
}

