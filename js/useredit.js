/*

SEA-CORE International Ltd.
SEA-CORE Development Group

User Editor JavaScript File

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
	'userid',
	'username',
	'profid',
	'orgid',
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
];

// List of radio button names.
var radios = [
];


// List of hidden divs controlled by drop down list.
// This must match the hiddenSelect ID select tag in item count and
// the items must correspond to each other.
// Do not remove this or other things will break;
var hiddenList = [
	'nativeLogin',
	'oauthLogin',
	'openidLogin',
];
var hiddenSelect = 'method';
