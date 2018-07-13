/*

SEA-CORE International Ltd.
SEA-CORE Development Group

Module Data Editor JavaScript File

Inside the application, only the vendor has access to this.
Not even the administrator can load it.
*/

var ident = [
	'select_table',
	'hiddenForm',
]

var data = [
	'dataForm',
]

var fields = [
	'modid',
	'modname',
	'moddesc',
	'modfile',
	'modicon',
	'modact',
	'modalluser',
	'modvend',
	'modsys',
]

var radios = [
]

function getParameters() {
	params = treeWalker(ident);
	return(params);
}

function getFormData() {
	id = treeWalker(ident);
	params = treeWalker(data);
	if (id != '' && params != '') return(id + '&' + params);
	if (id != '') return(id);
	if (params != '') return(params);
	return '';
}

// Button click handler for List.
$('#listDataItems').on('click', function () {
	ajaxServerCommand.sendCommand(-1);
});

// Button click handler for View.
$('#viewDataItem').on('click', function() {
	params = getParameters();
	ajaxServerCommand.sendCommand(1, params);
});

// Button click handler for Edit.
$('#updateDataItem').on('click', function() {
	params = getParameters();
	ajaxServerCommand.sendCommand(2, params);
});

// Button click handler for Add.
$('#insertDataItem').on('click', function() {
	ajaxServerCommand.sendCommand(3);
});

// Button click handler for Delete.
$('#deleteDataItem').on('click', function() {
	params = getParameters();
	ajaxServerCommand.sendCommand(4, params);

});

// Button click handler for submit edit.
function submitUpdate() {
	params = getFormData();
	ajaxServerCommand.sendCommand(12, params);
}

// Button click hander for submit add.
function submitInsert() {
	params = getFormData();
	ajaxServerCommand.sendCommand(13, params);
}

// Button click handler for submit delete.
function submitDelete() {
	params = getFormData();
	ajaxServerCommand.sendCommand(14, params);
}

function clearForm() {
	// Non-Radio fields.
	for (i = 0; i < fields.length; i++) {
		nodeObject = document.getElementById(fields[i]);
		if (nodeObject == null) continue;
		switch (nodeObject.nodeName.toLowerCase()) {
			case 'input':
				switch (nodeObject.type.toLowerCase()) {
					case 'checkbox':	// Checkbox is special.
						nodeObject.checked = nodeObject.defaultChecked;
						break;
					case 'radio':		// We handle this separately below.
					case 'button':		// This is ignored.
						break;
					default:			// All other field types.
						nodeObject.value = nodeObject.defaultValue;
						break;
				}
				break;
			case 'textarea':
				nodeObject.value = nodeObject.defaultValue;
				break;
		}
	}

	// Radio fields.
	for (i = 0; i < radios.length; i++) {
		nodeList = document.getElementsByName(radios[i]);
		if (nodeList == null) continue;
		for (j = 0; j < nodeList.length; j++) {
			nodeObject = nodeList[j];
			nodeObject.checked = nodeObject.defaultChecked;
		}
	}
}

// Resets the error status of all fields.
function resetErrorStatus() {
	console.log('resetErrorStatus()');
	for (i = 0; i < fields.length; i++) {
	nodeObject = document.getElementById(fields[i]);
	if (nodeObject == null) continue;
	switch (nodeObject.nodeName.toLowerCase()) {
		case 'input':
			switch (nodeObject.type.toLowerCase()) {
				// These types are ignored.
				case 'checkbox':
				case 'radio':
				case 'button':
					break;
				default:
					ajaxProcessData.setStatusTextDefault(fields[i], '');
					break;
			}
			break;
		case 'textarea':
			ajaxProcessData.setStatusTextDefault(fields[i], '');
			break;
		}
	}
}