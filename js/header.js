/*

SEA-CORE International Ltd.
SEA-CORE Development Group

JavaScript file for the page header default functionality.
This is in jQuery.

*/


// Logout Function
$("#logout").on('click', function()
{
	ajaxServerCommand.logoutUser();
});


// Navigate to Home Function
$("#returnHome").on('click', function()
{
	ajaxServerCommand.returnHome();
});


// Remove Focus on Click
$("button").mouseup(function()
{
	this.blur();
});
$("checkbox").mouseup(function(){
	this.blur();
});


// This is called when the DOM is ready.
// It calls the load_initial function from ajax.js
$(document).ready(function()
{
	setTimeout(function() { ajaxServerCommand.loadAdditionalContent(
		$(document.body).attr("href-link")); }, 250);
});


// This is called when the DOM is ready.
// Gets the vertical size of the viewing area and sets the
// content scrolling in the ajaxTarget div.
$(document).ready(function()
{
	windowResize();
});


// This is called on widow resize.
// Gets the vertical size of the viewing area and sets the
// content scrolling in the ajaxTarget div.
$(window).resize(function()
{
	windowResize();
});

// Adjusts the content height so it will scroll down the window
// without the header scrolling.
function windowResize() {
	heightWindow = $(window).height();
	heightHeader = $('div#navigationBarHeader').height();
	heightContent = heightWindow - heightHeader;
	$(".main-wrapper-div").height(heightContent);
}
