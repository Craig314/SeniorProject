/*

JavaScript file for portal.php

*/



// Sends the load module command to the server.
function loadModule(module)
{
	var param = "COMMAND=137&MODULE=" + module;
	ajaxServerSendPOST(serverLink, serverUrl, param);
}



