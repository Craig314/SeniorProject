/*

AJAX Command and Response System

Sends and receivces commands, status codes, and data,
to and from, the server.

*/

// Some of the HTTP Status Codes.  Taken from
// https://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
var httpStatus = {
	400:"400 - Bad Request",
	401:"401 - Unauthorized",
	403:"403 - Forbidden",
	404:"404 - Not Found",
	405:"405 - Method Not Allowed",
	406:"406 - Not Acceptable",
	408:"408 - Request Timeout",
	500:"500 - Internal Server Error",
	501:"501 - Not Implemented",
	503:"503 - Service Unavailable"
};

// These are predifined commands that the server will always
// recognize.
var serverCommands = {
	'loadContent': -1,
	'heartbeat': -2,
	'logout': -3,
	'goHome': -4,
};








// AJAX Control Data Class
var serverLinkObject = ({
	link: new XMLHttpRequest(),
	url: "",
	main: "main",
	response: "responseTarget",
	error: "errorTarget",

	// Sets the URL path for AJAX to communicate with.  This should be the
	// first thing done on page loading.
	setUrlPath: function(url) {
		this.url = url;
	},

	// Sets the target ID for HTML content injection.
	setTargetMain: function(targetId) {
		this.main = targetId;
	},

	// Sets the target ID for response messages.
	setTargetResponse: function(targetId) {
		this.response = targetId;
	},

	// Sets the target ID for error messages.
	setTargetError: function(targetId) {
		this.error = targetId;
	},

	// Returns the previously set URL.
	getUrlPath: function() {
		return this.url;
	},

	// Returns the target ID of the main display panel.
	getTargetMain: function() {
		return this.main;
	},

	// Returns the target ID for response messages.
	getTargetResponse: function() {
		return this.response;
	},

	// Returns the target ID for error messages.
	getTargetError: function() {
		return this.error;
	},

	// Returns the embedded object which represents the communications link.
	getCommLink: function() {
		return this.link;
	},
});








// Class to handle the actual sending of commands and the responses
// of those requests.
var ajaxServerCommand = {

	// Sends a command to the server. This function takes a variable
	// number of parameters. Each additional parameter beyond cmd must
	// be a key=value pair.
	sendCommand: function(cmd) {
		var param = "COMMAND=" + cmd;
		var i;
		if (arguments.length > 1)
		{
			param += "&";
			for (i = 1; i < arguments.length; i++)
			{
				param += arguments[i];
				if (i < (arguments.length - 1)) param += "&";
			}
		}
		ajaxServerSend.post(serverLinkObject, param);
	},

	// Sends a command and preformatted bulk data to the
	// server for processing.
	sendCommandBulk: function(cmd, name, data) {
		var param = "COMMAND=" + cmd + "&" + name + "=" + data;
		ajaxServerSend.post(serverLinkObject, param);
	},

	// Sends the return home command to the server.
	returnHome: function() {
		this.sendCommand(serverCommands['goHome']);
	},

	// Logs out the user.
	logoutUser: function() {
		this.sendCommand(serverCommands['logout']);
	},

	// Sends the heartbeat command to the server.  This is sent periodically to
	// prevent the session from timing out.
	heartbeat: function() {
		this.sendCommand(serverCommands['heartbeat']);
	},

	// Takes a variable amount of parameters.  If specified, the
	// first one is the url that Ajax should use for XHR requests.
	// The second parameter is to change the default write target
	// from main to something else.  The third parameter, if
	// specified, changes the command that is sent.
	loadAdditionalContent: function() {
		cmd = serverCommands['loadContent'];
		if (arguments.length > 0) {
			switch (arguments.length) {
				case 3:
					cmd = arguments[2];
				case 2:
					serverLinkObject.setTargetMain(arguments[1]);
				case 1:
					serverLinkObject.setUrlPath(arguments[0]);
					break;
				default:
					serverLinkObject.setUrlPath(arguments[0]);
					serverLinkObject.setTargetMain(arguments[1]);
					cmd = arguments[2];
					break;
			}
		}
		this.sendCommand(cmd);
	},
};








// This directly handles communications to/from the server.
var ajaxServerSend = {

	// Initiates an Ajax POST call to the server.  This operates in async mode
	// only which means that this happens in the background in a seperate
	// browser thread.  This accepts a variable number of arguments.
	// Additional arguments must be in key=value pairs for each one.  This
	// will format all additional arguments for sending to the server.
	post: function(linkObject) {
		link = linkObject.getCommLink();
		link.open("POST", linkObject.getUrlPath());
		link.setRequestHeader("Content-type",
			"application/x-www-form-urlencoded");
		link.onreadystatechange = function(){this.responseHandler(link,
			linkObject);}.bind(this);
		switch (arguments.length) {
			case 1:		// No extra arguments
				link.send();
				break;
			case 2:		// Single extra argument
				link.send(arguments[1]);
				break;
			default:	// Multiple extra arguments
				var str = "";
				var i;
				for (i = 1; i < arguments.length; i++) {
					str += arguments[i];
					if (i < (arguments.length - 1)) str += "&";
				}
				link.send(str);
				break;
		}
	},

	// This is called when the server updates the client through
	// a previous Ajax call.  
	responseHandler: function(link, dataObject) {
		var space;
		var string;
		if (link.readyState == 4) {
			if (link.status == 200) {
				space = link.responseText.indexOf(" ");
				string = link.responseText.slice(0, space);
				switch(string) {
					case "CODE":	// Status Code
						ajaxProcessData.parseCode(link.responseText);
						return;
						break;
					case "CMD":		// Command with/without data
						ajaxProcessData.parseCommand(link.responseText);
						return;
						break;
					case "STAT":	// JSON formatted form field error data.
						ajaxProcessData.parseStatus(link.responseText);
						return;
						break;
					case "JSON":	// JSON formatted data
						ajaxProcessData.parseJSON(link.responseText);
						return;
						break;
					case "MULTI":	// Multiformat in JSON (uses loop)
						ajaxProcessData.parseMultiformat(link.responseText);
						return;
						break;
					default:
						document.getElementById(dataObject.getTargetMain()).innerHTML = link.responseText;
						writeError("");
						writeResponse("");
						// Feature Activations
						if (typeof featureCheckbox === 'function') {
							featureCheckbox();
						}
						if (typeof featureTooltip === 'function') {
							featureTooltip();
						}
						break;
				}

				// Feature Activations
				if (typeof featureCheckbox === 'function') {
					featureCheckbox();
				}
			}
			else {
				writeError("ERROR: " + link.status + " - " + link.statusText);
			}
		}
	},
}








// This class contains routines that process AJAX data from the server.  This
// should only be called from ajaxServerSend::responseHandler.
var ajaxProcessData = ({

	// Parses a code response from the server.
	// Should only be called from the Ajax Reponse Handler.
	parseCode: function(str) {
		var cst;
		var est;
		var entity;
		var codenum;
		entity = str.indexOf("CODE");
		if (entity != 0) return;
		cst = str.indexOf(" ") + 1;
		est = str.indexOf(" ", cst);
		if (est < 0)
			codenum = parseInt(str.slice(cst));
		else
			codenum = parseInt(str.slice(cst, est));
		//alert(codenum + " " + cst + " " + est);
		switch (codenum) {
			case 200:   // Status OK - Do Nothing
				break;
			case 302:   // Found
			case 303:	// See Other
				var urlpos;
				urlpos = str.indexof("https://");
				if (urlpos < 0) urlpos = str.indexOf("http://");
				if (urlpos < 0) {
					writeError("Unknown protocol for redirect returned by server.");
					writeResponse("");
					return;
				}
				var url = str.slice(urlpos);
				window.location.href = url;
				break;
			default:
				if (httpStatus[codenum] == "")
					alert("Unknown code " + codenum + " returned by server.");
				else writeError(httpStatus[codenum]);
			break;
		}
	},

	// Parses a command response from the server.
	// Should only be called from the Ajax Reponse Handler.
	// NOTE: Commands 950-999 are reserved by the system.
	parseCommand: function(str) {
		var cst;
		var est;
		var entity;
		var cmdnum;
		entity = str.indexOf("CMD");
		if (entity != 0) return;
		cst = str.indexOf(" ") + 1;
		est = str.indexOf(" ", cst);
		if (est < 0) cmdnum = parseInt(str.slice(cst));
			else cmdnum = parseInt(str.slice(cst, est));
		switch (cmdnum) {
			case 950:   // OK, call clearForm (if available), display to responseTarget
				var txt = str.slice(8);
				writeError("");
				writeResponse(txt);
				if (typeof clearForm === 'function') clearForm();
				break;
			case 951:   // Ok, display to responseTarget
				var txt = str.slice(8);
				writeError("");
				writeResponse(txt);
				break;
			case 952:   // Error, call clearForm (if available), display to errorTarget
				var txt = str.slice(8);
				writeError(txt);
				writeResponse("");
				if (typeof clearForm === 'function') clearForm();
				break;
			case 953:   // Error, display to errorTarget
				var txt = str.slice(8);
				writeError(txt);
				writeResponse("");
				break;
			case 954:   // Clear all messages
				writeError("");
				writeResponse("");
				break;
			case 955:   // Clear all messages, write HTML
				var txt = str.slice(8);
				writeError("");
				writeResponse("");
				writeHTML(txt);
				break;
			default:
				// If the command is none of the above, then we are dealing
				// with a possible custom command, or an invalid command.
				if (typeof customCmdProc === 'function') {
					var result = customCmdProc(cmdnum);
					if (result == false) alert("Unknown command " + cmdnum + " returned by server.");
				}
				else alert("Unknown command " + cmdnum + " returned by server.");
				break;
		}
	},

	// Parses a status array object and updates the client
	// field status accordingly.
	parseStatus: function(str) {
		var cst;
		var entity;
		var i;
		var text;
		var objArray;
		var message;
		entity = str.indexOf("STAT");
		if (entity != 0) return;
		cst = str.indexOf(" ")
		if (cst < 0) {
			writeError("JSON data format error from server.");
			return;
		}
		text = str.slice(0, cst);
		try {
			var objArray = JSON.parse(text);
		}
		catch (error) {
			writeError(error.message);
			return;
		}
		message = '';
		for (i = 0; i < objArray.length; i++) {
			this.setValueText(objArray[i].id, objArray[i].value);
			switch (objArray[i].status) {
				case 0:			// Default Status
					if (objArray[i].message.length() > 0)
						this.setStatusTextDefault(objArray[i].id, objArray[i].message);
					else
						this.setStatusTextDefault(objArray[i].id);
					break;
				case 1:			// Ok Status
					if (objArray[i].message.length() > 0)
						this.setStatusTextOk(objArray[i].id, objArray[i].message);
					else
						this.setStatusTextOk(objArray[i].id);
					break;
				case 2:			// Warning Status
					this.setStatusTextWarn(objArray[i].id, objArray[i].message);
					break;
				case 3:			// Error Status
					this.setStatusTextError(objArray[i].id, objArray[i].message);
					break;
				case 4:			// General Error Message
					message += '<br>' . objArray[i].message;
					break;
				default:		// Default Status
					if (objArray[i].message.length() > 0)
						this.setStatusTextDefault(objArray[i].id, objArray[i].message);
					else
						this.setStatusTextDefault(objArray[i].id);
					break;
			}
		}
		if (message.length > 0) writeError(message);	
	},

	// Parses the JSON data from text format into object format
	// and calls a function according to the JSON number.
	// Internal function, do not call directly.
	parseJSON: function(str) {
		var cst;
		var est;
		var entity;
		var jfnum;
		entity = str.indexOf("JSON");
		if (entity != 0) return;
		cst = str.indexOf(" ") + 1;
		est = str.indexOf(" ", cst);
		if (est < 0) {
			writeError("JSON data format error from server.");
			return;
		}
		jfnum = str.parseInt(str.slice(cst, est));
		jfunc = 'objectJsonPost' + jfnum.toString();
		if (typeof window[jfunc] == typeof parseJSON) {
			try {
				var obj = JSON.parse(txt);
			}
			catch (error) {
				writeError(error.message);
				return;
			}
			window[jfunc](obj);
		}
		return;
	},

	// Performs parsing of multiple types within a loop.
	// The valid types are CODE, CMD, STAT, and JSON.  Invalid
	// or unknown types are ignored.
	parseMultiformat: function(str) {
		var cst;
		var est;
		var entity;
		var i;
		entity = str.indexOf("MULTI");
		if (entity != 0) return;
		cst = str.indexOf(" ")
		if (cst < 0) {
			writeError("JSON data format error from server.");
			return;
		} txt = str.slice(0, cst);
		try {
			var objArray = JSON.parse(txt);
		}
		catch (error) {
			writeError(error.message);
			return;
		}
		for (i = 0; i < objArray.length; i++) {
			space = objArray[i].indexOf(" ");
			string = objArray[i].slice(0, space);
			switch(string) {
				case "CODE":	// Status Code
					this.parseCode(objArray[i]);
					return;
					break;
				case "CMD":		// Command with/without data
					this.parseCommand(objArray[i]);
					return;
					break;
				case 'STAT':	// JSON formatted error data
					this.parseStatus(objArray[i]);
					return;
					break;
				case "JSON":	// JSON formatted data
					this.parseJSON(objArray[i]);
					return;
					break;
				default:
					// Invalid data is ignored
					break;
			}
		}
	},

	// Sets the status of a text field to Ok.
	setStatusTextOk: function(id) {
		document.getElementById('dcmGL-' + id).setAttribute('class', 'glyphicon glyphicon-ok form-control-feedback');
		document.getElementById('dcmST-' + id).setAttribute('class', 'form-group has-success has-feedback');
		if (arguments > 1)
			document.getElementById('dcmMS-' + id).innerHTML = arguments[1];
	},
	
	// Sets the status of a text field to Warning.
	setStatusTextWarn: function(id, message) {
		document.getElementById('dcmGL-' + id).setAttribute('class', 'glyphicon glyphicon-check form-control-feedback');
		document.getElementById('dcmST-' + id).setAttribute('class', 'form-group has-warning has-feedback');
		document.getElementById('dcmMS-' + id).innerHTML = message;
	},
	
	// Sets the status of a text field to Error.
	setStatusTextError: function(id, message) {
		document.getElementById('dcmGL-' + id).setAttribute('class', 'glyphicon glyphicon-remove form-control-feedback');
		document.getElementById('dcmST-' + id).setAttribute('class', 'form-group has-error has-feedback');
		document.getElementById('dcmMS-' + id).innerHTML = message;
	},
	
	// Sets the status of a text field to Default.
	setStatusTextDefault: function(id) {
		document.getElementById('dcmGL-' + id).setAttribute('class', 'glyphicon form-control-feedback');
		document.getElementById('dcmST-' + id).setAttribute('class', 'form-group');
		if (arguments > 1)
			document.getElementById('dcmMS-' + id).innerHTML = arguments[1];
	},

	// Sets the value of a text field.
	setValueText: function(id, value) {
		if (value.length() > 0) document.getElementById(id).value = value;
	}
	
});








// Writes an error message to the error target field.
function writeError(msg) {
	document.getElementById(serverLinkObject.getTargetError()).innerHTML = msg;
}

// Writes a message to the response target field.
function writeResponse(msg) {
	document.getElementById(serverLinkObject.getTargetResponse()).innerHTML = msg;
}

// Writes HTML to the main view.
function writeHTML(msg) {
	document.getElementById(serverLinkObject.getTargetMain()).innerHTML = msg;
}


