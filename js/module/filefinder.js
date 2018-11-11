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

// Issues a prompt to the user for a new file name.
function getNewFileName() {
	var pname;
	var params;

	pname = window.prompt('Enter new filename.', '');
	if (pname == null || pname == '') return null;
	params = 'filename=' + encodeURIComponent(pname);
	return params;
}

// Issues a prompt to the user for the new location to
// move/copy a file to.
function getNewFilePath(mode) {
	var pname;
	var params;

	switch (mode) {
		case 0:
			pname = window.prompt('Enter new path for file.\nDo not enter'
				+ ' new filename.', '');
			break;
		case 1:
			pname = window.prompt('Enter new location to copy the file to.'
				+ '\nDo not enter new filename.', '');
			break;
		default:
			pname = null;
	}
	if (pname == null || pname == '') return null;
	params = 'pathname=' + encodeURIComponent(pname);
	return params;
}

// Issues a prompt to the user for a new directory name.
function getNewDirName() {
	var pname;
	var params;

	pname = window.prompt('Enter new directory name.', '');
	if (pname == null || pname == '') return null;
	params = 'dirname=' + encodeURIComponent(pname);
	return params;
}

// Issues a prompt to the user for the new location to move
// a directory to.
function getNewDirPath() {
	var pname;
	var params;

	pname = window.prompt('Enter new path for directory.\nDo not enter'
		+ ' new directory name.', '');
	if (pname == null || pname == '') return null;
	params = 'pathname=' + encodeURIComponent(pname);
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
		case 2:
			md = ' move ';
			break;
		default:
			md = ' ';
	}
	message = 'Are you sure that you want to' + md;
	message += 'the selected' + tn + '?\nDoing so can ';
	message += 'have an adverse effect on the application.';
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
	ajaxServerCommand.sendCommand(20);
});

// Button click handler for moving up one directory level.
$('#directoryUp').on('click', function() {
	var params;

	params = getParameters();
	ajaxServerCommand.sendCommand(21, params);
});

// Button click handler for moving down one directory level.
$('#directoryDown').on('click', function() {
	var params;

	params = getParameters();
	ajaxServerCommand.sendCommand(22, params);
});

// Button click handler for creating a directory.
$('#directoryCreate').on('click', function() {
	var params;
	var dirname;

	params = getParameters();
	dirname = getNewDirName();
	if (dirname != null) {
		ajaxServerCommand.sendCommand(23, dirname, params);
	}
});

// Button click handler for renaming a directory.
$('#directoryRename').on('click', function() {
	var params;
	var dirname;
	var conf;

	params = getParameters();
	dirname = getNewDirName();
	if (dirname != null) {
		conf = getConfirmation(0, 0);
		if (conf == true) {
			ajaxServerCommand.sendCommand(24, dirname, params);
		}
	}
});

// Button click handler for moving a directory.
$('#directoryMove').on('click', function() {
	var params;
	var pathname;
	var conf;

	params = getParameters();
	pathname = getNewDirPath();
	if (pathname != null) {
		conf = getConfirmation(0, 2);
		if (conf == true) {
			ajaxServerCommand.sendCommand(25, pathname, params);
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
		ajaxServerCommand.sendCommand(26, params);
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
		ajaxServerSend.filePut(serverLinkObject, path, formData, fileButton, secToken, 30);
	}
}

// Button click handler for downloading a file.
$('#fileDownload').on('click', function() {
	var params;

	params = getParameters();
	ajaxServerCommand.sendCommand(31, params);
});

// Button click handler for viewing a file.
$('#fileView').on('click', function() {
	var params;

	params = getParameters();
	ajaxServerCommand.sendCommand(32, params);
});

// Button click handler for file details.
$('#fileDetail').on('click', function() {
	var params;

	params = getParameters();
	ajaxServerCommand.sendCommand(33, params);
});

// Button click handler for renaming a file.
$('#fileRename').on('click', function() {
	var params;
	var conf;
	var filename;

	params = getParameters();
	filename = getNewFileName();
	if (filename != null) {
		conf = getConfirmation(1, 0);
		if (conf == true) {
			ajaxServerCommand.sendCommand(34, filename, params);
		}
	}
});

// Button click handler for moving a file.
$('#fileMove').on('click', function() {
	var params;
	var conf;
	var pathname;

	params = getParameters();
	pathname = getNewFilePath(0);
	if (pathname != null) {
		conf = getConfirmation(1, 2);
		if (conf == true) {
			ajaxServerCommand.sendCommand(35, pathname, params);
		}
	}
});

// Button click handler for copying a file.
$('#fileCopy').on('click', function() {
	var params;
	var conf;
	var pathname;

	params = getParameters();
	pathname = getNewFilePath(1);
	if (pathname != null) {
		conf = getConfirmation(1, 2);
		if (conf == true) {
			ajaxServerCommand.sendCommand(36, pathname, params);
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
		ajaxServerCommand.sendCommand(37, params);
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
