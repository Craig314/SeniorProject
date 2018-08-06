/*

SEA-CORE International Ltd.
SEA-CORE Development Group

Login Page JavaScript File

*/


// **** Native Login

// Shows the Native Login Page to the user.
function unhideFormNative() {
	document.getElementById('method_chooser').hidden = true;
	document.getElementById('native_form').hidden = false;
}

// Clears all entries on the Native Login Page.
function resetFormNative() {
	document.getElementById('native_username').value = "";
	document.getElementById('native_password').value = "";
}

// Submits the Native form to the server for processing.
function submitFormNative() {
	var username;
	var password;
	var data1;
	var data2;
	var data3;

	username = document.getElementById('native_username').value;
	password = document.getElementById('native_password').value;
	if (typeof btoa === 'function') {
		data1 = 'native_username=' + encodeURIComponent(btoa(username));
		data2 = 'native_password=' + encodeURIComponent(btoa(password));
		data3 = 'base64=1';
	} else {
		// For browsers that do not have a btoa function.
		data1 = 'native_username=' + encodeURIComponent(username);
		data2 = 'native_password=' + encodeURIComponent(password);
		data3 = 'base64=0';
	}
	ajaxServerCommand.sendCommand(1, data1, data2, data3);
}


// **** OpenID Login

// Shows the OpenID Login Page to the user.
function unhideFormOpenID() {
	document.getElementById('method_chooser').hidden = true;
	document.getElementById('openid_form').hidden = false;
}

// Clears all entries on the OpenID Login Page.
function resetFormOpenID() {
	document.getElementById('openid_url').value = "";
}

// Submits the OpenID form to the server for processing.
function submitFormOpenID() {
}


// **** OAuth Login

// Shows the OAuth Login Page to the user.
function unhideFormOAuth() {
	document.getElementById('method_chooser').hidden = true;
	document.getElementById('oauth_form').hidden = false;
}


// **** Method Chooser

// Shows the login method chooser to the user.
function returnChooser() {
	writeError("");
	writeResponse("");
	writeHTML("");
	document.getElementById('native_form').hidden = true;
	document.getElementById('oauth_form').hidden = true;
	document.getElementById('openid_form').hidden = true;
	document.getElementById('method_chooser').hidden = false;
}


// **** Other

function clearForm() {
	var reset;

	reset = document.getElementById('native_username');
	if (reset != null) reset.value = "";
	reset = document.getElementById('native_password');
	if (reset != null) reset.value = "";
	reset = document.getElementById('openid_url');
	if (reset != null) reset.value = "";
}
