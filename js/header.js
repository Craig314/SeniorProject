

// JavaScript file for the page header default functionality.
// This is in jQuery.


// Logout Function
$("#logout").on('click', function()
{
	sendCommand(-3);
});


// Navigate to Home Function
$("#returnHome").on('click', function()
{
	returnHome();
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
	setTimeout(function() { load_additional_content($(document.body).attr("href-link")); }, 250);
});


// This is called when the DOM is ready.
// Gets the vertical size of the viewing area and sets the
// content scrolling in the ajaxTarget div.
$(document).ready(function()
{
	var window_height = $(window).height();
	var header_height = $('nav#navigationBar.navbar.navbar-default').height();
	var funbar_height = 0;
	var fbarObj = document.getElementById("functionBar");
	if (fbarObj != null)
		funbar_height = $("nav#functionBar.nav.nav-inline").height();
	var content_height = window_height - (header_height + funbar_height + 2);
	$(".main-wrapper-div").height(content_height);
});


// This is called on widow resize.
// Gets the vertical size of the viewing area and sets the
// content scrolling in the ajaxTarget div.
$(window).resize(function()
{
	var window_height = $(window).height();
	var header_height = $('nav#navigationBar.navbar.navbar-default').height();
	var funbar_height = 0;
	var fbarObj = document.getElementById("functionBar");
	if (fbarObj != null)
		funbar_height = $("nav#functionBar.nav.nav-inline").height();
	var content_height = window_height - (header_height + funbar_height + 2);
	$(".main-wrapper-div").height(content_height);
});



