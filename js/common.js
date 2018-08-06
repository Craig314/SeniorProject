/*

SEA-CORE International Ltd.
SEA-CORE Development Group

Common Core JavaScript File

*/

// Returns the parameters of the forms whose IDs are in the
// ident array.
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

function selectItem(item) {
	nodeList = document.getElementsByName('select_item');
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
	params = getParameters();
	ajaxServerCommand.sendCommand(3, params);
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

// This is called when the reset button is pressed on the
// form.  This resets all fields to their default values.
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
				console.log('clearForm(): Unknown Input Type: ' +
					nodeObject.nodeName.toLowerCase());
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

	// Reset hidden objects if needed.
	if (hiddenList.length > 0) setHidden();

	// Call a custom reset form if needed
	if (typeof customResetForm == 'function') customResetForm();
}

// Resets the error status of all fields.
function resetErrorStatus() {
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
		case 'select':
			ajaxProcessData.setStatusTextDefault(fields[i], '');
			break;
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
	selectObject = document.getElementById(hiddenSelect);
	optionList = selectObject.children;
	if (optionList != null) {
		if (optionList.length != hiddenList.length) {
			console.log('WANRING: Length of hiddenList and optionList do not match.');
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

