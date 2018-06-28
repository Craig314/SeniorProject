/*

Initiates periodic communication with the server to keep the
session alive.

*/

var timer_heartbeat = window.setInterval(heartBeat, 600000);

// Heartbeat to keep session alive
function heartBeat()
{
	sendCommand(-2);
}

