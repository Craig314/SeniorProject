/*

SEA-CORE International Ltd.
SEA-CORE Development Group

JavaScript file for portal.php

*/


// Sends the load module command to the server.
function loadModule(module)
{
	var param;

	param = "MODULE=" + module;
	ajaxServerCommand.sendCommand(5, param);
}

