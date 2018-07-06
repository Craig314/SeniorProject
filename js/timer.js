/*

SEA-CORE International Ltd.
SEA-CORE Development Group

Display Local System Time

Writes the current system time of the client to the browser window.

*/

// Setup
var datedata = new Date();
var timer_daytime = window.setInterval(dayTime, 1000);

// Kickstart the display on loading.
document.getElementById("timeday").innerHTML = datedata.toLocaleTimeString();

// Displays the time on the screen.
function dayTime()
{
	var d = new Date();
	document.getElementById("timeday").innerHTML = d.toLocaleTimeString();
}
