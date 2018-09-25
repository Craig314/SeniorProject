/*

SEA-CORE International Ltd.
SEA-CORE Development Group

File Finder JavaScript File

*/

// Don't change this.
var ident = [
	'select_table',
	'hiddenForm',
];

// Returns all parameters from forms, hidden fields, etc...
// where the array ident contains the form tag's name and/
// or id of the forms to be checked.
function getParameters() {
	var params = treeWalker(ident);
	return(params);
}

// Issues a prompt to the user for a new file/directory
// name.
function getNewName(type) {
	var pname;
	var params;
	var tn;
	var pm;

	switch (type) {
		case 0:
			tn = ' directory ';
			pm = 'dirname';
			break;
		case 1:
			tn = ' file ';
			pm = 'filename';
			break;
		default:
			tn = ' ';
	}
	pname = window.prompt('Enter new' + tn + 'name.', '');
	if (pname == null || pname == '') return null;
	params = pm + '=' + encodeURIComponent(pname);
	return params;
}

// Prompts the user to make sure they want to proceed when
// performing a potentially dangerous action.
function getConfirmation(type, method) {
	var message;
	var tn;
	var md;

	switch (type) {
		case 0:
			tn = ' directory';
			break;
		case 1:
			tn = ' file';
			break;
		default:
			tn = ' ';
	}
	switch (method) {
		case 0:
			md = ' rename ';
			break;
		case 1:
			md = ' delete ';
			break;
		default:
			md = ' ';
	}
	message = 'Are you sure that you want to' + md;
	message += 'the selected' + tn + '?\nDoing so can ';
	message += 'have an adverse effect on the application';
	return window.confirm(message);
}

// When the user clicks anywhere in a row of a selection
// list table, this will set the click status of the radio
// button.
function selectItemRadio(name, item) {
	var nodeList;
	var nodeObject;
	var i;

	nodeList = document.getElementsByName(name);
	if (nodeList == null) return;
	for (i = 0; i < nodeList.length; i++) {
		nodeObject = nodeList[i];
		if (nodeObject.value == item) {
			nodeObject.checked = true;
		} else {
			nodeObject.checked = false;
		}
	}
}


// Button click handler for moving to the home directory.
$('#directoryHome').on('click', function() {
	ajaxServerCommand.sendCommand(-1);
});

// Button click handler for moving up one directory level.
$('#directoryUp').on('click', function() {
	var params;

	params = getParameters();
	ajaxServerCommand.sendCommand(1, params);
});

// Button click handler for moving down one directory level.
$('#directoryDown').on('click', function() {
	var params;

	params = getParameters();
	ajaxServerCommand.sendCommand(2, params);
});

// Button click handler for creating a directory.
$('#directoryCreate').on('click', function() {
	var params;
	var dirname;

	params = getParameters();
	dirname = getNewName(0);
	if (dirname != null) {
		ajaxServerCommand.sendCommand(3, dirname, params);
	}
});

// Button click handler for renaming a directory.
$('#directoryRename').on('click', function() {
	var params;
	var dirname;
	var conf;

	params = getParameters();
	dirname = getNewName(0);
	if (dirname != null) {
		conf = getConfirmation(0, 0);
		if (conf == true) {
			ajaxServerCommand.sendCommand(4, dirname, params);
		}
	}
});

// Button click handler for deleting a directory.
$('#directoryDelete').on('click', function() {
	var params;
	var conf;

	params = getParameters();
	conf = getConfirmation(0, 1);
	if (conf == true) {
		ajaxServerCommand.sendCommand(5, params);
	}
});

// Button click handler for file upload.
$('#fileUpload').on('click', function() {
	var fileField;
	var state;
	 
	fileField = document.getElementById('fileInputDiv');
 	if (fileField != null) {
		state = fileField.hidden;
		state = (state) ? false : true;
		fileField.hidden = state;
	}
});

// Button click handler for file upload action.
function fileUpload() {
	var fileButton;
	var fileSelect;
	var tokenObject;
	var secToken;
	var formData;
	var fileList;
	var path;
	var file;
	var i;

	// Get our necessary IDs.
	fileSelect = document.getElementById('fileInput');
	fileButton = document.getElementById('fileSubmit');
	path = document.getElementById('hidden');
	// Get the security token.
	tokenObject = document.getElementById('token_data');
	if (tokenObject != null) {
		secToken = tokenObject.value;
	} else {
		secToken = '';
	}

	// Start processing the input.
	formData = new FormData();
	fileList = fileSelect.files;
	if (fileList.length > 0) {
		fileButton.defaultText = fileButton.innerHTML;
		fileButton.innerHTML = 'Uploading...';
		for (i = 0; i < fileList.length; i++) {
			file = fileList[i];
			formData.append('uploadFiles', file, file.name);
		}
		ajaxServerSend.filePut(serverLinkObject, path, formData, fileButton, secToken, 10);
	}
}

// Button click handler for downloading a file.
$('#fileDownload').on('click', function() {
	var params;

	params = getParameters();
	ajaxServerCommand.sendCommand(11, params);
});

// Button click handler for viewing a file.
$('#fileView').on('click', function() {
	var params;

	params = getParameters();
	ajaxServerCommand.sendCommand(12, params);
});

// Button click handler for file details.
$('#fileDetail').on('click', function() {
	var params;

	params = getParameters();
	ajaxServerCommand.sendCommand(15, params);
});

// Button click handler for renaming a file.
$('#fileRename').on('click', function() {
	var params;
	var conf;
	var filename;

	params = getParameters();
	filename = getNewName(1);
	if (filename != null) {
		conf = getConfirmation(1, 0);
		if (conf == true) {
			ajaxServerCommand.sendCommand(13, filename, params);
		}
	}
});

// Button click handler for deleting a file.
$('#fileDelete').on('click', function() {
	var params;
	var conf;

	params = getParameters();
	conf = getConfirmation(1, 1);
	if (conf == true) {
		ajaxServerCommand.sendCommand(14, params);
	}
});

// Button click handler for pulling from the GitHub repository.
// Note: Git must be setup and working on the system.
$('#gitPull').on('click', function() {
});

// Opens a new window which initiates a download.
function fileDownload(txt) {
	var feats;
	var popupWindow;
	
	feats = 'height=300';
	feats += ',width=500';
	feats += ',location=no';
	feats += ',menubar=no';
	feats += ',resizable=no';
	feats += ',scrollbars=no';
	feats += ',status=no';
	feats += ',titlebar=no';
	feats += ',toolbar=no';
	popupWindow = window.open(txt, '_blank', feats, false);
	setTimeout(function () {popupWindow.close();}, 1000);
}

function fileView(txt) {
	var feats;

	feats = '';
	window.open(txt, '_blank', feats);
}

// Custom command processor
function customCmdProc(cmd, txt) {
	switch (cmd) {
		case 46:
			fileDownload(txt);
			break;
		case 47:
			fileView(txt);
			break;
		case 48:
			window.alert(txt);
			break;
		default:
			window.alert("Unknown command " + cmd + " returned by server.");
	}
}
