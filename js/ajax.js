/*

AJAX Command and Response System

Sends and receivces commands, status codes, and data,
to and from, the server.

*/

// Some of the HTTP Status Codes.  Taken from
// https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
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


// AJAX Control Data
var serverLinkObject = {
	link: new XMLHttpRequest(),
	url: "",
	target: "main"
}

// Sets the URL path for AJAX to communicate with.
// This should be the first thing that is done on page load.
function setUrlPath(url)
{
	serverLinkObject[url] = url;
}

// Sets the target ID for content injection.
function setTargetMain(targetId)
{
	serverLinkObject[target] = targetId;
}

// Sends a command to the server.
// Variable Parameter.
// Each additional parameter must be a key=value pair.
function sendCommand(cmd)
{
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
	ajaxServerSendPOST(serverLinkObject, param);
}

// Sends a command and preformatted bulk data to the
// server for processing.
function sendCommandBulk(cmd, name, data)
{
	var param = "COMMAND=" + cmd + "&" + name + "=" + data;
	ajaxServerSendPOST(serverLinkObject, param);
}

// Initiates an Ajax POST call to the server.
// This operates in async mode only.  This accepts
// a variable number of arguments.  Additional arguments
// must be in key=value pairs for each one.  This will
// format all additional arguments for sending to the
// server.
function ajaxServerSendPOST(linkObject)
{
	linkObject.link.open("POST", linkObject.url);
	linkObject.link.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	linkObject.link.onreadystatechange = function(){ajaxResponseHandler(linkObject);};
	switch (arguments.length)
	{
		case 1:		// No extra arguments
			linkObject.link.send();
			break;
		case 2:		// Single extra argument
			linkObject.link.send(arguments[2]);
			break;
		default:	// Multiple extra arguments
			var str = "";
			var i;
			for (i = 1; i < arguments.length; i++)
			{
				str += arguments[i];
				if (i < (arguments.length - 1)) str += "&";
			}
			linkObject.link.send(str);
			break;
	}
}

// This is called when the server updates the client through
// a previous Ajax call.  
function ajaxResponseHandler(dataObject)
{
	var space;
	var string;
	if (dataObject.link.readyState == 4)
	{
		if (dataObject.link.status == 200)
		{
			space = dataObject.link.responseText.indexOf(" ");
			string = dataObject.link.responseText.slice(0, space);
			switch(string)
			{
				case "CODE":	// Status Code
					parseCode(dataObject.link.responseText);
					return;
					break;
				case "CMD":		// Command with/without data
					parseCommand(dataObject.link.responseText);
					return;
					break;
				case "STAT":	// JSON formatted form field error data.
					parseStatus(dataObject.link.responseText);
					return;
					break;
				case "JSON":	// JSON formatted data
					parseJSON(dataObject.link.responseText);
					return;
					break;
				case "MULTI":	// Multiformat in JSON (uses loop)
					parseMultiformat(dataObject.link.responseText);
					return;
					break;
				default:
					document.getElementById(dataObject.target).innerHTML = dataObject.link.responseText;
					writeError("");
					writeResponse("");
					// Feature Activations
					if (typeof ajaxResponseHandler == typeof featureCheckbox)
					{
						featureCheckbox();
					}
					if (typeof ajaxResponseHandler == typeof featureTooltip)
					{
						featureTooltip();
					}
					break;
			}

			// Feature Activations
			if (typeof ajaxResponseHandler == typeof featureCheckbox)
			{
				featureCheckbox();
			}
		}
		else
		{
			writeError("ERROR: " + dataObject.link.status + " - " + dataObject.link.statusText);
		}
	}
}

// Parses a code response from the server.
// Should only be called from the Ajax Reponse Handler.
function parseCode(str)
{
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
	switch (codenum)
	{
		case 200:   // Status OK - Do Nothing
			break;
		case 302:   // Found
		case 303:	// See Other
			var urlpos;
			urlpos = str.indexof("https://");
			if (urlpos < 0) urlpos = str.indexOf("http://");
			if (urlpos < 0)
			{
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
}

// Parses a command response from the server.
// Should only be called from the Ajax Reponse Handler.
// NOTE: Commands 950-999 are reserved by the system.
function parseCommand(str)
{
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
	switch (cmdnum)
	{
		case 950:   // OK, call clearForm (if available), display to responseTarget
			var txt = str.slice(8);
			writeError("");
			writeResponse(txt);
			if (typeof clearForm === "function") clearForm();
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
			if (typeof clearForm === "function") clearForm();
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
			if (typeof customCmdProc === "function")
			{
				var result = customCmdProc(cmdnum);
				if (result == false) alert("Unknown command " + cmdnum + " returned by server.");
			}
			else alert("Unknown command " + cmdnum + " returned by server.");
			break;
	}
}

// Parses a status array object and updates the client
// field status accordingly.
function parseStatus(str)
{
	var cst;
	var est;
	var entity;
	var i;
	var message;
	var msg;
	var objHtml;
	var objParent;
	var objSpan;
	entity = str.indexOf("STAT");
	if (entity != 0) return;
	cst = str.indexOf(" ")
	if (cst < 0)
	{
		writeError("JSON data format error from server.");
		return;
	}
	txt = str.slice(0, cst);
	try
	{
		var objArray = JSON.parse(txt);
	}
	catch (error)
	{
		writeError(error.message);
		return;
	}
	message = '';
	for (i = 0; i < objArray.length; i++)
	{
		/* This assumes the following structure:
			<div>
				<label>
				<div>
					<input>
					<span>
				</div>
			</div>
		*/
		objHtml = document.getElementById(objArray[i].field);
		if (objHtml == null) continue;
		objParent = objHtml.parentNode.parentNode;
		objSpan = objHtml.nextSibling;
		if (objParent.nodeName.toUpperCase != 'DIV') continue;
		if (objSpan.nodeName.toUpperCase != 'SPAN') continue;
		switch (objArray[i].status)
		{
			case 0:
				classNameDiv = "form-group";
				classNameSpan = "glyphicon glyphicon-ok form-control-feedback";
				msg = null;
				break;
			case 1:
				classNameDiv = "form-group has-success has-feedback";
				classNameSpan = "glyphicon form-control-feedback";
				msg = null;
				break;
			case 2:
				classNameDiv = "form-group has-warning has-feedback";
				classNameSpan = "glyphicon glyphicon-warning-sign form-control-feedback";
				msg = objArray[i].msg;
				break;
			case 3:
				classNameDiv = "form-group has-error has-feedback";
				classNameSpan = "glyphicon glyphicon-remove form-control-feedback";
				msg = objArray[i].msg;
				break;
			default:
				classNameDiv = "form-group";
				classNameSpan = "glyphicon form-control-feedback";
				msg = null;
				break;
		}
		objParent.className = classNameDiv;
		objSpan.className = classNameSpan;
		if (msg != null)
		{
			if (message == '') message = msg;
				else message += '<br>' + msg;
		}
	}
	writeError(message);	
}

// Parses the JSON data from text format into object format
// and calls a function according to the JSON number.
// Internal function, do not call directly.
function parseJSON(str)
{
	var cst;
	var est;
	var entity;
	var jfnum;
	entity = str.indexOf("JSON");
	if (entity != 0) return;
	cst = str.indexOf(" ") + 1;
	est = str.indexOf(" ", cst);
	if (est < 0)
	{
		writeError("JSON data format error from server.");
		return;
	}
	jfnum = str.parseInt(str.slice(cst, est));
	jfunc = 'objectJsonPost' + jfnum.toString();
	if (typeof window[jfunc] == typeof parseJSON)
	{
		try
		{
			var obj = JSON.parse(txt);
		}
		catch (error)
		{
			writeError(error.message);
			return;
		}
		window[jfunc](obj);
	}
	return;
}

// Performs parsing of multiple types within a loop.
// The valid types are CODE, CMD, STAT, and JSON.  Invalid
// or unknown types are ignored.
function parseMultiformat(str)
{
	var cst;
	var est;
	var entity;
	var i;
	entity = str.indexOf("MULTI");
	if (entity != 0) return;
	cst = str.indexOf(" ")
	if (cst < 0)
	{
		writeError("JSON data format error from server.");
		return;
	}
	txt = str.slice(0, cst);
	try
	{
		var objArray = JSON.parse(txt);
	}
	catch (error)
	{
		writeError(error.message);
		return;
	}
	for (i = 0; i < objArray.length; i++)
	{
		space = objArray[i].indexOf(" ");
		string = objArray[i].slice(0, space);
		switch(string)
		{
			case "CODE":	// Status Code
				parseCode(objArray[i]);
				return;
				break;
			case "CMD":		// Command with/without data
				parseCommand(objArray[i]);
				return;
				break;
			case "JSON":	// JSON formatted data
				parseJSON(objArray[i]);
				return;
				break;
			default:
				// Invalid data is ignored
				break;
		}
	}
}

// Writes an error to the error target field.
function writeError(msg)
{
	document.getElementById("errorTarget").innerHTML = msg;
}

// Writes a message to the response target field.
function writeResponse(msg)
{
	document.getElementById('responseTarget').innerHTML = msg;
}

// Writes HTML to the main view.
function writeHTML(msg)
{
	document.getElementById(ajaxTarget).innerHTML = msg;
}

// Sends the return home command to the server.
function returnHome()
{
	sendCommand(-4);
}

// Logs out the user.
function logoutUser()
{
	sendCommand(-3);
}

// Takes a variable amount of parameters.  If specified, the
// first one is the url that Ajax should use for XHR requests.
// The second parameter is to change the default write target
// from main to something else.  The third parameter, if
// specified, changes the command that is sent.
function load_additional_content()
{
	var cmd = -1;
	if (arguments.length > 0)
	{
		switch (arguments.length)
		{
			case 3:
				cmd = arguments[2];
			case 2:
				setTargetMain(arguments[1]);
			case 1:
				setUrlPath(arguments[0]);
				break;
			default:
				setUrlPath(arguments[0]);
				setTargetMain(arguments[1]);
				cmd = arguments[2];
				break;
		}
	}
	sendCommand(cmd);
}
