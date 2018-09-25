/*

SEA-CORE International Ltd.
SEA-CORE Development Group

Template JavaScript File

This file is only needed on a per-module basis if the associated
module has hidden elements in it's views or if the client-side
data verification has fields which require special handling.

*/

// List of hidden divs controlled by drop down list.
// This must match the hiddenSelect ID select tag in item count and
// the items must correspond to each other.
// Do not remove this or other things will break;
var hiddenList = [
];
var hiddenSelect = '';

// This is called from verifyData.verify in verify.js if a field is
// marked for special handling.
function customVerifyData(item, mode) {
}

// This is called from verifyData.verify in verify.js for any final
// verification tasks that may need to be completed.
function customVerifyFinal() {
}

