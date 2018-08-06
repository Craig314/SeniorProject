/*

SEA-CORE International Ltd.
SEA-CORE Development Group

Treewalker Form Data Collector

This walks the DOM tree looking for any and all input fields.  It will
convert the input data into the proper format for sending to the server
via a POST operation.  In order for this to work properly, this requires
that the name attribute be set on all supported input elements.

On the server side, rawurldecode (for PHP) needs to be called on the
string to decode any special characters.

*/


function treeWalker(nodeArray) {
	var params = '';
	var nodeObject;
	var nameId;
	var i;

	// Searches the array for a valid ID or name.  The first one found
	// is the one that is used.  If none are found, then it returns
	// a blank string.
	for (i = 0; i < nodeArray.length; i++) {
		nodeObject = document.getElementById(nodeArray[i]);
		if (nodeObject == null) {
			// The ID doesn't exist, so we try the name.
			nameId = document.getElementsByName(nodeArray[i]);
			if (nameId == null) continue;
			nodeObject = nameId[0];
		}
		if (nodeObject != null) {
			if (i > 0) {
				params += '&' + treeWalkerMainFunction(nodeObject);
			} else {
				params = treeWalkerMainFunction(nodeObject);
			}
		}
	}
	return params;
}

function treeWalkerMainFunction(nodeObject) {
	var nodeObjStart;
	var params = '';

	// Set the start object to the given node object.
	nodeObjStart = nodeObject;

	// Now we walk to DOM tree and look for any and all supported
	// field types.
	while (nodeObject) {
		if (nodeObject.children.length > 0) {
			// Check for children
			nodeObject = nodeObject.firstElementChild;
		}
		else if (nodeObject.nextElementSibling) {
			// Check for siblings
			nodeObject = nodeObject.nextElementSibling;
		}
		else {
			// If we can't find either, then go up the tree.
			do {
				nodeObject = nodeObject.parentNode;
				if (nodeObject === nodeObjStart) return(params);
			} while (!nodeObject.nextElementSibling);
			nodeObject = nodeObject.nextElementSibling;
		}
		if (nodeObject == null) return(params);

		// Look for any objects in the DOM with a tag name of INPUT.
		if (nodeObject.nodeName.toLowerCase() === 'input') {
			switch (nodeObject.type.toLowerCase()) {
				// String input types
				case 'hidden':
					// Token data is handled through AJAX.
					if (nodeObject.id === 'token_data') return params;
					else {
						if (params.length > 0) params += '&';
						params += nodeObject.name + '=' + encodeURIComponent(nodeObject.value);
					}
					break;

				// Boolean input types
				case 'checkbox':
				case 'radio':
					if (nodeObject.checked == true) {
						if (params.length > 0) params += '&';
						params += nodeObject.name + '=' + encodeURIComponent(nodeObject.value);
					}
					break;

				// Input types we do nothing with.
				case 'button':
					break;

				// All other input types.
				default:
					if (params.length > 0) params += '&';
					params += nodeObject.name + '=' + encodeURIComponent(nodeObject.value);
					break;
			}
		}

		// Look for any objects in the DOM with a tag name of TEXTAREA.
		if (nodeObject.nodeName.toLowerCase() === 'textarea') {
			if (params.length > 0) params += '&';
			params += nodeObject.name + '=' + encodeURIComponent(nodeObject.value);
		}

		// Selected Lists
		if (nodeObject.nodeName.toLowerCase() === 'select') {
			if (params.length > 0) params += '&';
			params += nodeObject.name + '=' + encodeURIComponent(nodeObject.value);
		}
	}
}
