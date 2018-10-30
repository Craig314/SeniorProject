/*

SEA-CORE International Ltd.
SEA-CORE Development Group

Function Command Handling

Send and recieves commands and data to and from the server.
The server side file must be a function file.
Requires ajax.js to function correctly

*/

// 16 independent link objects for AJAX to use.
var functionList = {
	0  : Object.assign({}, serverLinkObject),
	1  : Object.assign({}, serverLinkObject),
	2  : Object.assign({}, serverLinkObject),
	3  : Object.assign({}, serverLinkObject),
	4  : Object.assign({}, serverLinkObject),
	5  : Object.assign({}, serverLinkObject),
	6  : Object.assign({}, serverLinkObject),
	7  : Object.assign({}, serverLinkObject),
	8  : Object.assign({}, serverLinkObject),
	9  : Object.assign({}, serverLinkObject),
	10 : Object.assign({}, serverLinkObject),
	11 : Object.assign({}, serverLinkObject),
	12 : Object.assign({}, serverLinkObject),
	13 : Object.assign({}, serverLinkObject),
	14 : Object.assign({}, serverLinkObject),
	15 : Object.assign({}, serverLinkObject),
};

// Sends a command with data to the server using the specified
// link.
function funcSendCommand(link, command) {
	var param = "COMMAND=" + command;
	var token = document.getElementById('token_data');
	var i;
	if (token != null) {
		param += '&token=' + token.value;
	}
	if (arguments.length > 2) {
		param += "&";
		for (i = 2; i < arguments.length; i++) {
			param += arguments[i];
			if (i < (arguments.length - 1)) param += "&";
		}
	}
	ajaxServerSend.post(functionList[link], param);
}

// Similar to submitUpdate in common.js but is designed to use
// a function instead of a module.
function funcSubmitUpdate(link) {
	var params;

	writeError('');
	writeResponse('');
	if (verifyData.verify(VERIFY_MODE_OTHER) == false) return;
	params = getFormData();
	funcSendCommand(link, 12, params);
}

// Similar to submitInsert in common.js but is designed to use
// a function instead of a module.
function funcSubmitInsert(link) {
	var params;
	var result;

	writeError('');
	writeResponse('');
	if (verifyData.verify(VERIFY_MODE_INSERT) == false) return;
	params = getFormData();
	funcSendCommand(link, 13, params);
}

// Similar to submitDelete in common.js but is designed to use
// a function instead of a module.
function funcSubmitDelete(link) {
	var params;

	writeError('');
	writeResponse('');
	params = getFormData();
	funcSendCommand(link, 14, params);
}

