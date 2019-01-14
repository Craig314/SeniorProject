/*

SEA-CORE International Ltd.
SEA-CORE Development Group

Login Page JavaScript File

*/

// Data object for the challenge authentication protocol.
var chapdata = ({
	salt: null,
	digest: null,
	count: null,
	challenge: null,
});

// AJAX custom command handling
function customCmdProc(cmd, txt) {
	var passObj;

	switch (cmd) {
		case 900:		// Load CHAP Data
			try {
				chapdata = JSON.parse(txt);
			} catch (error) {
				writeError(error.message);
			}
			break;
		case 901:		// Send Password
			passObj = document.getElementById('password');
			if (passObj != null) {
				loginAPI.submit_password(passObj);
			}
			break;
		default:
			window.alert("Unknown command " + cmdnum + " returned by server.");
			break;
	}
}

// This is called when the DOM is ready.
// It calls the loadAdditionalContent method in the ajaxServerCommand
// object from ajax.js
function initialRun() {
	ajaxServerCommand.loadAdditionalContent($(document.body).attr("href-link"));
}


var loginAPI = ({

	// Clears all fields in the login form.
	resetForm: function() {
		userObj = document.getElementById('username');
		passObj = document.getElementById('password');
		if (userObj != null) {
			userObj.value = '';
		}
		if (passObj != null) {
			passObj.value = '';
		}
	},

	// Collects data from the form and submits it to the server
	submitForm: function() {
		var userObj;
		var passObj;

		// Setup
		userObj = document.getElementById('username');
		passObj = document.getElementById('password');

		// Determine what we need to do.
		if (userObj != null && passObj != null) {
			this.submit_username(userObj);
		} else {
			if (userObj != null) {
				this.submit_username(userObj);
			}
			if (passObj != null) {
				this.submit_password(passObj);
			}
		}
	},

	// Submits the username to the server.
	submit_username: async function (userObj) {
		var username;
		var b64flag;

		username = this.encode_username(userObj);
		b64flag = this.check_base64();
		ajaxServerCommand.sendCommand(1, username, b64flag);
	},

	// Submits the password to the server.
	submit_password: async function(passObj) {
		var password;
		var b64flag;
		var chapflag;

		b64flag = this.check_base64();
		password = await this.chap_password(passObj);
		if (password == null) {
			password = this.encode_password(passObj);
			chapflag = 'use_chap=0';
		} else {
			chapflag = 'use_chap=1';
		}
		ajaxServerCommand.sendCommand(2, password, b64flag, chapflag);
	},

	// Computes the password challenge and returns a hex string.
	// If there is an error, returns null.
	chap_password: async function(passObj) {
		var password;
		var data;

		// Check to see if browser supports the crypto functions.
		if (chapAPI.checkCrypto() == false) return null;

		// Check to make sure we have all the data that we need.
		if (chapdata.salt == null) {
			return null;
		}
		if (chapdata.digest == null) {
			return null;
		}
		if (chapdata.count == null) {
			return null;
		}
		if (chapdata.challenge == null) {
			return null;
		}

		// Check to make sure that the digest is valid.
		chapdata.digest = chapAPI.convertDigest(chapdata.digest);
		if (chapAPI.checkDigest(chapdata.digest) == false) {
			console.warn('Server specified unsupported hash function.');
			return null;
		}

		// Compute the CHAP result.
		if (passObj == null) return null;
		password = await chapAPI.calcCHAPResponse(passObj.value,
			chapdata.digest, chapdata.salt, chapdata.count, chapdata.challenge);
		
		// Encode the result.
		if (typeof btoa === 'function') {
			data = 'password=' + encodeURIComponent(btoa(password));
		} else {
			data = 'password=' + encodeURIComponent(password);
		}

		// Return the result.
		return data;
	},

	// Returns the encoded result if the browser supports base64 encoding.
	check_base64: function() {
		var data

		if (typeof btoa === 'function') {
			data = 'base64=1';
		} else {
			data = 'base64=0';
		}
		return data;
	},

	// Returns an encoded username.
	encode_username: function(objUser) {
		var data

		if (typeof btoa === 'function') {
			data = 'username=' + encodeURIComponent(btoa(objUser.value));
		} else {
			data = 'username=' + encodeURIComponent(objUser.value);
		}
		return data;
	},

	// Returns an encoded password.
	encode_password: function(objPass) {
		var data

		if (typeof btoa === 'function') {
			data = 'password=' + encodeURIComponent(btoa(objPass.value));
		} else {
			data = 'password=' + encodeURIComponent(objPass.value);
		}
		return data;
	},

});
