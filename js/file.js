/*

SEA-CORE International Ltd.
SEA-CORE Development Group

File Finder JavaScript File

*/

// Don't change this.
var ident = [
	'select_table',
	'hiddenForm',
]

var inputTag;

function getParameters() {
	params = treeWalker(ident);
	return(params);
}

function getNewName(type) {
	switch (type) {
		case 0:
			tn = ' directory ';
			break;
		case 1:
			tn = ' file ';
			break;
		default:
			tn = ' ';
			break;
	}
	name = prompt('Enter new' + tn + 'name.', '');
	if (name == null || name == '') return null;
	params = 'name=' + encodeURIComponent(name);
	return params;
}

// Button click handler for moving to the home directory.
$('#directoryHome').on('click', function() {
	ajaxServerCommand.sendCommand(-1);
});

// Button click handler for moving up one directory level.
$('#directoryUp').on('click', function() {
	params = getParameters();
	ajaxServerCommand.sendCommand(1, params);
});

// Button click handler for moving down one directory level.
$('#directoryDown').on('click', function() {
	params = getParameters();
	ajaxServerCommand.sendCommand(2, params);
});

// Button click handler for file upload.
$('#fileUpload').on('click', function() {
	fileField = document.getElementById('fileInputDiv');
	if (fileField != null) {
		state = fileField.hidden;
		state = (state) ? false : true;
		fileField.hidden = state;
	}
});

// Button click handler for downloading a file.
$('#fileDownload').on('click', function() {
	params = getParameters()
	ajaxServerCommand.sendCommand(11, params);
});

// Button click handler for renaming a file.
$('#fileRename').on('click', function() {
	params = getParameters();
	filename = getNewName(1);
	if (filename != null && filename != '') {
		conf = window.confirm('Are you sure that you want to rename the ' +
			'selected file?\Doing so can have an adverse effect on ' +
			'the application');
		if (conf == true) {
			filename = 'filename=' + filename;
			ajaxServerCommand.sendCommand(12, filename, params);
		}
	}
});

// Button click handler for deleting a file.
$('#fileDelete').on('click', function() {
	params = getParameters();
	conf = window.confirm('Are you sure that you want to remove the ' +
		'selected file?\nDoing so can have an adverse effect on ' +
		'the application.');
	if (conf == true) {
		ajaxServerCommand.sendCommand(13, params);
	}
});

// Button click handler for creating a directory.
$('#directoryCreate').on('click', function() {
	params = getParameters();
	dirname = getNewName(0);
	if (dirname != null && dirname != '') {
		ajaxServerCommand.sendCommand(20, dirname, params);
	}
});

// Button click handler for renaming a directory.
$('#directoryRename').on('click', function() {
	params = getParameters();
	dirname = 'dirname=' + getNewName(0);
	if (dirname != null && dirname != '') {
		conf = window.confirm('Are you sure that you want to rename the ' +
			'selected directory?\Doing so can have an adverse effect on ' +
			'the application');
		if (conf == true) {
			dirname = 'dirname=' + dirname;
			ajaxServerCommand.sendCommand(21, dirname, params);
		}
	}
});

// Button click handler for deleting a directory.
$('#directoryDelete').on('click', function() {
	params = getParameters();
	conf = window.confirm('Are you sure that you want to remove the ' +
		'selected directory?\nDoing so can have an adverse effect on ' +
		'the application.');
	if (conf == true) {
		ajaxServerCommand.sendCommand(22, params);
	}
});

// Button click handler for pulling from the GitHub repository.
// Note: Git must be setup and working on the system.
$('#gitPull').on('click', function() {
});

// File upload action
function fileUpload() {
	// Get our necessary IDs.
	var fileButton = document.getElementById('fileSubmit');
	fileSelect = document.getElementById('fileInput');

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
		ajaxServerSend.filePut(serverLinkObject, formData, fileButton, secToken, 10);
	}
}

// Opens a new window which initiates a download.
function fileDownload(txt) {
	feats = 'height=300';
	feats += ',width=500';
	feats += ',location=no';
	feats += ',menubar=no';
	feats += ',resizable=no';
	feats += ',scrollbars=no';
	feats += ',status=no';
	feats += ',titlebar=no'
	feats += ',toolbar=no';
	popupWindow = window.open(txt, '_blank', feats, false);
	setTimeout(function () {popupWindow.close();}, 1000);
}

// Custom command processor
function customCmdProc(cmd, txt)
{
	switch (cmd)
	{
		case 46:
			fileDownload(txt);
			break;
		default:
			alert("Unknown command " + cmdnum + " returned by server.");
			break;
	}
}