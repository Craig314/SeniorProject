/*

SEA-CORE International Ltd.
SEA-CORE Development Group

Common Core JavaScript File

*/

// Don't change this.  Override in the module specific Javascript
// file if needed.
var ident = [
	'select_table',
	'hiddenForm',
];

// Don't change this.  Override in the module specific Javascript
// file if needed.
var dataForm = [
	'dataForm',
];

// Returns all parameters from forms, hidden fields, etc...
// where the array ident contains the form tag's name and/
// or id of the forms to be checked.
function getParameters() {
	var params;

	if (typeof overrideIdent != 'undefined') {
		params = treeWalker(overrideIdent);
	} else {
		params = treeWalker(ident);
	}
	return(params);
}

// Same as getParameters above, but also checks the forms
// that are identified by the data array.
function getFormData() {
	var id;
	var params;

	if (typeof overrideIdent != 'undefined') {
		id = treeWalker(overrideIdent);
	} else {
		id = treeWalker(ident);
	}
	if (typeof overrideIdent != 'undefined') {
		params = treeWalker(overrideDataForm);
	} else {
		params = treeWalker(dataForm);
	}
	if (id != '' && params != '') return(id + '&' + params);
	if (id != '') return(id);
	if (params != '') return(params);
	return '';
}

// When working with a selection table listing, when
// clicking anywhere in the row, this function marks
// the corresponding item as selected, and then
// deselects any other item.
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

// When working with a selection table listing, when
// clicking anywhere in the row, this function toggles
// the corresponding item selection status.
function selectItemCheck(item) {
	var nodeObject;

	nodeObject = document.getElementById(item);
	if (nodeObject != null) {
		if (nodeObject.checked == true) nodeObject.checked = false;
		else nodeObject.checked = true;
	}
}

// When working with a selection table listing, when
// clicking anywhere in the row, this function immediately
// sends the selection to the server for processing.
function selectItemClick(stage, last, item) {
	switch (stage) {
		case 1:
			ajaxServerCommand.sendCommand(10, 'select_item=' + item);
			break;
		case 2:
			ajaxServerCommand.sendCommand(11, 'select_item=' + item);
			break;
		case 3:
			ajaxServerCommand.sendCommand(12, 'select_item=' + item);
			break;
		case 4:
			ajaxServerCommand.sendCommand(13, 'select_item=' + item);
			break;
		default:
			ajaxServerCommand.sendCommand(91, 'select_item=' + item);
			showFuncBar();
	}
	if (stage >= last) {
		showFuncBar();
	} else {
		hideFuncBar();
	}
}

// Sends the command to move to a particular stage.
function selectStage(stage, item) {
	switch (stage) {
		case 1:
			ajaxServerCommand.sendCommand(10, 'select_item=' + item);
			break;
		case 2:
			ajaxServerCommand.sendCommand(11, 'select_item=' + item);
			break;
		case 3:
			ajaxServerCommand.sendCommand(12, 'select_item=' + item);
			break;
		case 4:
			ajaxServerCommand.sendCommand(13, 'select_item=' + item);
			break;
	}
	if (stage >= last) {
		showFuncBar();
	} else {
		hideFuncBar();
	}
}

// Shows the function bars.
function showFuncBar() {
	funcBarObject = document.getElementById('functionBar1');
	if (funcBarObject != null) {
		funcBarObject.hidden = false;
	}
	funcBarObject = document.getElementById('functionBar2');
	if (funcBarObject != null) {
		funcBarObject.hidden = false;
	}
	funcBarObject = document.getElementById('functionBar3');
	if (funcBarObject != null) {
		funcBarObject.hidden = false;
	}
	if (typeof windowResize === 'function') {
		windowResize();
	}
}

// Hides the function bars.
function hideFuncBar() {
	funcBarObject = document.getElementById('functionBar1');
	if (funcBarObject != null) {
		funcBarObject.hidden = true;
	}
	funcBarObject = document.getElementById('functionBar2');
	if (funcBarObject != null) {
		funcBarObject.hidden = true;
	}
	funcBarObject = document.getElementById('functionBar3');
	if (funcBarObject != null) {
		funcBarObject.hidden = true;
	}
	if (typeof windowResize === 'function') {
		windowResize();
	}
}

// Button click handler for List.
$('#listDataItems').on('click', function () {
	writeError('');
	writeResponse('');
	ajaxServerCommand.sendCommand(-1);
});

// Button click handler for View.
$('#viewDataItem').on('click', function() {
	var params;

	writeError('');
	writeResponse('');
	params = getParameters();
	ajaxServerCommand.sendCommand(1, params);
});

// Button click handler for Edit.
$('#updateDataItem').on('click', function() {
	var params;

	writeError('');
	writeResponse('');
	params = getParameters();
	ajaxServerCommand.sendCommand(2, params);
});

// Button click handler for Add.
$('#insertDataItem').on('click', function() {
	var params;

	writeError('');
	writeResponse('');
	params = getParameters();
	ajaxServerCommand.sendCommand(3, params);
});

// Button click handler for Delete.
$('#deleteDataItem').on('click', function() {
	var params;

	writeError('');
	writeResponse('');
	params = getParameters();
	ajaxServerCommand.sendCommand(4, params);

});

// Button click handler for submit edit.
function submitUpdate() {
	var params;

	writeError('');
	writeResponse('');
	if (verifyData.verify(VERIFY_MODE_OTHER) == false) return;
	params = getFormData();
	ajaxServerCommand.sendCommand(5, params);
}

// Button click hander for submit add.
function submitInsert() {
	var params;
	var result;

	writeError('');
	writeResponse('');
	if (verifyData.verify(VERIFY_MODE_INSERT) == false) return;
	params = getFormData();
	ajaxServerCommand.sendCommand(6, params);
}

// Button click handler for submit delete.
function submitDelete() {
	var params;

	writeError('');
	writeResponse('');
	params = getFormData();
	ajaxServerCommand.sendCommand(7, params);
}

// This is called when the reset button is pressed on the
// form.  This resets all fields to their default values.
function clearForm() {
	var i;
	var j;
	var k;
	var nodeObject;
	var optionList;
	var nodeList;
	var fieldData;

	// Setup
	writeError('');
	writeResponse('');
	resetErrorStatus();
	fieldData = verifyData.getFieldData();
	if (fieldData == null) return;

	// Reset all fields.
	for (i = 0; i < fieldData.length; i++) {
		nodeObject = document.getElementById(fieldData[i].name);
		if (nodeObject == null) continue;
		switch (nodeObject.nodeName.toLowerCase()) {
			case 'input':
				switch (nodeObject.type.toLowerCase()) {
					case 'checkbox':	// Checkbox is special.
						nodeObject.checked = nodeObject.defaultChecked;
						break;
					case 'radio':		// Radio buttons require a bit of work.
						nodeList = document.getElementsByName(fieldData[i].name);
						if (nodeList == null) continue;
						for (j = 0; j < nodeList.length; j++) {
							nodeObject = nodeList[j];
							nodeObject.checked = nodeObject.defaultChecked;
						}
						break;
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
			case 'select':
				optionList = nodeObject.children;
				if (optionList != null) {
					for (j = 0; j < optionList.length; j++) {
						if (optionList[j].defaultSelected) {
							nodeObject.selectedIndex = optionList[j].index;
							break;
						}
					}
				}
				break;
			default:
				console.warn('clearForm(): Unknown Input Type: ' +
					nodeObject.nodeName.toLowerCase());
				break;
		}
	}

	// Reset hidden objects.
	setHidden();

	// Call a custom reset form if needed
	if (typeof customResetForm == 'function') {
		customResetForm();
	}
}

// Resets the error status of all fields.
function resetErrorStatus() {
	var nodeObject;
	var i;
	var fieldData;

	writeError('');
	writeResponse('');
	if (verifyData.getLoadStatus() == false) return;
	fieldData = verifyData.getFieldData();
	if (fieldData == null) return;
	for (i = 0; i < fieldData.length; i++) {
		nodeObject = document.getElementById(fieldData[i].name);
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
						ajaxProcessData.setStatusTextDefault(fieldData[i].name, '');
				}
				break;
			case 'textarea':
				ajaxProcessData.setStatusTextDefault(fieldData[i].name, '');
				break;
			case 'select':
				ajaxProcessData.setStatusTextDefault(fieldData[i].name, '');
				break;
			default:
		}
	}
}

// Hides/Shows different form elements based on a selection.
// The length of hiddenList and optionList (from the select
// tag on the page HTML) must match and the items must
// correspond.  Otherwise, unpredictible results may occurr.
// If the action of an option is repeated, then repeat that
// option in hiddenList.
function setHidden() {
	var selectObject;
	var optionList;
	var loopterm;
	var repeat;
	var targetId;
	var i;

	// Check to make sure everything has been defined.
	i = 0;
	if (typeof hiddenList == 'undefined') return;
	if (hiddenList.length == 0) {
		console.warn('Variable hiddenList defined but empty.');
		i++;
	}
	if (typeof hiddenSelect == 'undefined') {
		colsole.warn('Variable hiddenList defined, but variable hiddenSelect isn\'t.');
		i++;
	}
	if (i > 0) return;

	// Now we go through and reset the hidden stuff.
	selectObject = document.getElementById(hiddenSelect);
	optionList = selectObject.children;
	if (optionList != null) {
		if (optionList.length != hiddenList.length) {
			console.warn('Length of hiddenList and optionList do not match.');
			loopterm = Math.max(hiddenList.length, optionList.length);
		} else {
			loopterm = optionList.length;
		}
		repeat = null;
		for (i = 0; i < loopterm; i++) {
			targetId = document.getElementById(hiddenList[i]);
			if (optionList[i].selected) {
				targetId.hidden = false;
				repeat = targetId;
			} else {
				if (targetId === repeat) continue;
				targetId.hidden = true;
			}
		}
	}
}

// This returns the current selection of which selection is currently
// visible in the client.
function getHidden() {
	var selectObject;
	var optionList;
	var i;

	// Check to make sure everything has been defined.
	i = 0;
	if (typeof hiddenList == 'undefined') return;
	if (hiddenList.length == 0) {
		console.warn('Variable hiddenList defined but empty.');
		i++;
	}
	if (typeof hiddenSelect == 'undefined') {
		colsole.warn('Variable hiddenList defined, but variable hiddenSelect isn\'t.');
		i++;
	}
	if (i > 0) return;

	// Now we go through and find which one is selected.
	selectObject = document.getElementById(hiddenSelect);
	optionList = selectObject.children;
	if (optionList != null) {
		if (optionList.length != hiddenList.length) {
			console.warn('Length of hiddenList and optionList do not match.');
		}
		for (i = 0; i < optionList.length; i++) {
			if (optionList[i].selected) {
				return optionList[i];
			}
		}
	}
}

