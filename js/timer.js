/*

Writes the current system time to the browser window.

*/

var datedata = new Date();
var timer_daytime = window.setInterval(dayTime, 1000);
document.getElementById("timeday").innerHTML = datedata.toLocaleTimeString();

// Displays the time on the screen.
function dayTime()
{
	var d = new Date();
	document.getElementById("timeday").innerHTML = d.toLocaleTimeString();
}

