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


function treeWalker(nodeId) {
	var nodeObject = document.getElementById(nodeId);
	var nodeObjStart = nodeObject;
	var params = "";

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
				if (nodeObject === nodeObjStart)
				return(params);
			} while (!nodeObject.nextElementSibling)
			nodeObject = nodeObject.nextElementSibling;
		}

		// Look for any objects in the DOM with a tag name of INPUT.
		if (nodeObject.nodeName.toLowerCase() === "input") {
			switch (nodeObject.type.toLowerCase()) {
				// String input types
				case "text":
				case "password":
				case "hidden":
				case "color":		// This and below: HTML 5
				case "date":
				case "datetime":
				case "datetime-local":
				case "email":
				case "file":
				case "image":
				case "month":
				case "number":
				case "range":
				case "search":
				case "time":
				case "url":
				case "week":
					if (params.length > 0) params += "&";
					params += nodeObject.name + "=" + encodeURIComponent(nodeObject.value);
					break;

				// Boolean input types
				case "checkbox":
				case "radio":
					if (nodeObject.checked == true) {
						if (params.length > 0) params += "&";
						params += nodeObject.name + "=" + encodeURIComponent(nodeObject.value);
					}
					break;

				// Input types we do nothing with.
				case "button":
					break;

				// Unrecognized input type
				default:
					console.log("Unsupported input type: " + nodeObject.type.toLowerCase());
			}
		}

		// Look for any objects in the DOM with a tag name of TEXTAREA.
		if (nodeObject.nodeName.toLowerCase() === "textarea") {
			if (params.length > 0) params += "&";
			params += nodeObject.name + "=" + encodeURIComponent(nodeObject.value);
		}

		// Selected Lists
		if (nodeObject.nodeName.toLowerCase() === "select") {
			if (params.length > 0) params += "&";
			params += nodeObject.name + "=" + encodeURIComponent(nodeObject.value);
		}
	}
}

