/*

SEA-CORE International Ltd.
SEA-CORE Development Group

Periodic Heartbeat

Initiates periodic communication with the server to keep the
session alive.

*/


var timer_heartbeat;
var timer_initial = window.setTimeout(heartbeatBootstrap(), 60000);


// Bootstraps the heartbeat timer.  Starts off as a delay before the
// interval timer is initiated.
function heartbeatBootstrap() {
	timer_heartbeat = window.setInterval(heartbeatTimer, 600000);
}

// Calls the heartbeat method to send it to the server.
function heartbeatTimer() {
	if (typeof ajaxServerCommand.heartbeat !== 'function') return;
	ajaxServerCommand.heartbeat();
}
