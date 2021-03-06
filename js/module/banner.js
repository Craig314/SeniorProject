/*

SEA-CORE International Ltd.
SEA-CORE Development Group

Banner Page JavaScript File

*/


// This is called when the page first loads.
function initialRun() {
	ajaxServerCommand.loadAdditionalContent($(document.body).attr("href-link"));
}

// Submits the Native form to the server for processing.
function submitPasswordChange() {
	var oldpass;
	var newpass1;
	var newpass2;
	var data1;
	var data2;
	var data3;
	var data4;

	oldpass = document.getElementById('oldpass').value;
	newpass1 = document.getElementById('newpass1').value;
	newpass2 = document.getElementById('newpass2').value;
	if (typeof btoa === 'function') {
		data1 = 'oldpassword=' + encodeURIComponent(btoa(oldpass));
		data2 = 'newpassword1=' + encodeURIComponent(btoa(newpass1));
		data3 = 'newpassword2=' + encodeURIComponent(btoa(newpass2));
		data4 = 'base64=1';
	} else {
		// For browsers that do not have a btoa function.
		data1 = 'oldpassword=' + encodeURIComponent(oldpass);
		data2 = 'newpassword1=' + encodeURIComponent(newpass1);
		data3 = 'newpassword2=' + encodeURIComponent(newpass2);
		data4 = 'base64=0';
	}
	ajaxServerCommand.sendCommand(1, data1, data2, data3, data4);
}

// Clears the form
function clearForm() {
	var reset;

	reset = document.getElementById('oldpass');
	if (reset != null) reset.value = "";
	reset = document.getElementById('newpass1');
	if (reset != null) reset.value = "";
	reset = document.getElementById('newpass2');
	if (reset != null) reset.value = "";
}

// Continue button functionality
function submitContinue() {
	ajaxServerCommand.sendCommand(2);
}