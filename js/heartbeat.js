/*

Initiates periodic communication with the server to keep the
session alive.

*/

var timer_heartbeat = window.setInterval(ajaxServerCommand.heartbeat, 600000);


