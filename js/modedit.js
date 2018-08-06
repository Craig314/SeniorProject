/*

SEA-CORE International Ltd.
SEA-CORE Development Group

Module Data Editor JavaScript File

Inside the application, only the vendor has access to this.
Not even the administrator can load it.
*/

// Don't change this.
var ident = [
	'select_table',
	'hiddenForm',
];

// Don't change this.
var data = [
	'dataForm',
];

// List of field names/IDs (except radio buttons)
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
];

// List of radio button names
var radios = [
];

// List of hidden divs controlled by drop down list.
var hiddenList = [
];
var hiddenSelect = '';

// This is a custom clear form function
function customResetForm() {
	changeImage();
}

// Change icon image based on what is selected.
function changeImage() {
	var baseUrl;
	var selectObject;
	var imageObject;
	var optionList;
	var loopterm;
	var fileLink;
	var i;

	baseUrl = document.getElementById('base_url').value;
	selectObject = document.getElementById('modicon');
	imageObject = document.getElementById('icon_image');
	optionList = selectObject.children;
	if (optionList != null) {
		loopterm = optionList.length;
		for (i = 0; i < loopterm; i++) {
			if (optionList[i].selected) {
				fileLink = baseUrl + '/images/icon128/' + optionList[i].value + '.png';
				imageObject.src = fileLink;
				imageObject.alt = optionList[i].value;
				break;
			}
		}
	}
}
