/*

SEA-CORE International Ltd.
SEA-CORE Development Group

User Editor JavaScript File

*/


// List of hidden divs controlled by drop down list.
// This must match the hiddenSelect ID select tag in item count and
// the items must correspond to each other.
var hiddenList = [
	'nativeLogin',
	'oauthLogin',
	'openidLogin',
];
var hiddenSelect = 'method';

// This is called by the verify class in verify.js when the datatype
// specified is custom.
function customVerifyData(item) {
	var select;
	var result;

	select = getHidden();
	switch(select.text) {
		case 'Native':
			switch (item.name) {
				case 'newpass1':
					item.noblank = true;
					result = verifyData.customVerify(item);
					break;
				case 'newpass2':
					item.noblank = true;
					result = verifyData.customVerify(item);
					break;
				default:
					result = true;
					break;
			}
			break;
		case 'OAuth':
			switch (item.name) {
				case 'oaprovider':
					item.noblank = true;
					result = verifyData.customVerify(item);
					break;
				default:
					result = true;
					break;
			}
			break;
		case 'OpenID':
			switch (item.name) {
				case 'opprovider':
					item.noblank = true;
					result = verifyData.customVerify(item);
					break;
				case 'opident':
					item.noblank = true;
					result = verifyData.customVerify(item);
					break;
				default:
					result = true;
					break;
			}
			break;
		default:
			console.warn('Invalid selection detected. SELECT=' + select.text);
			result = false;
			break;
	}
	return result;
}

// If this function is defined, then after the standard data verification
// has completed, this is called from verify in verify.js.
function customVerifyFinal() {
	var select;
	var pass1;
	var pass2;

	select = getHidden();
	switch(select.text) {
		case 'nativeLogin':
			// Running preliminary checks on passwords, if needed.
			pass1 = document.getElementById('newpass1').value;
			pass2 = document.getElementById('newpass2').value;
			
			// If both fields are blank, then don't bother.
			if (pass1.length == 0 && pass2.length == 0) {
				return true;
			}

			// Now we run some checks.
			if (pass1.length == 0) {
				ajaxProcessData.setStatusTextError('newpass1',
					'This password field cannot be blank while the other ' +
					'password field is not.');
				return false;
			}
			if (pass2.length == 0) {
				ajaxProcessData.setStatusTextError('newpass2',
					'This password field cannot be blank while the other ' +
					'password field is not.');
				return false;
			}
			if (pass1 != pass2) {
				ajaxProcessData.setStatusTextError('newpass1',
					'Passwords do not match.');
				ajaxProcessData.setStatusTextError('newpass2',
					'Passwords do not match.');
				return false;
			}
	}
	return true;
}
