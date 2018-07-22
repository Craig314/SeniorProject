/*

SEA-CORE International Ltd.
SEA-CORE Development Group

Template JavaScript File

*/

var ident = [
	'select_table',
	'hiddenForm',
]

var data = [
	'dataForm',
]

// List of field names/IDs (except radio buttons)
var fields = [
	'userid',
	'username',
	'profid',
	'method',
	'newpass1',
	'newpass2',
	'active',
	'locked',
	'provider',
	'name',
	'haddr',
	'maddr',
	'email',
	'hphone',
	'cphone',
	'wphone',
]

// List of radio button names
var radios = [
]


// Additional functionality
var loginList = [
	'nativeLogin',
	'oauthLogin',
	'openidLogin',
]

function setHidden() {
	selectObject = document.getElementById('method');
	optionList = selectObject.children;
	if (optionList != null) {
		for (i = 0; i < optionList.length; i++) {
			targetId = document.getElementById(loginList[i]);
			if (optionList[i].selected) {
				targetId.hidden = false;
			} else {
				targetId.hidden = true;
			}
		}
	}
}
