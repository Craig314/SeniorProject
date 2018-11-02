

// Custom application landing page activator

// This is used to expose a callback function in the global context.
// XXX: This is a hack, but it is the only option...for now.
var eventCallback;

// Set function access URLs.
functionList[0].setUrlPath('/application/func.assignments.php');
functionList[1].setUrlPath('/application/func.caljson.php');


// Custom command handler.  Called from ajax.js:ajaxProcessData.parseCommand
function customCmdProc(command, txt) {
	switch(command) {
		case 121:
			activateCalendar();
			break;
		case 122:
			try {
				var obj = JSON.parse(txt);
			}
			catch (error) {
				writeError(error.message);
				return;
			}
			objectJsonPost152(obj);
			break;
		default:
			return false;
			break;
	}
	return true;
}

function activateCalendar() {
	// Activate calendar.
	$('#calendar').fullCalendar({
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'month,basicWeek,basicDay listDay,listWeek',
		},
		defaultView: 'month',
		navLinks: true,
		eventLimit: true,
		// customize the button names,
		// otherwise they'd all just say "list"
		views: {
			listDay: { buttonText: 'list day' },
			listWeek: { buttonText: 'list week' }
		},
		eventClick: function(calEvent, jsEvent, view) {
			funcSendCommand(0, 25, 'assignment=' + calEvent.assignment);
		},
		events: function(start, end, timezone, callback) {
			eventCallback = callback;
			var minTime = 'timemin=' + start.unix();
			var maxTime = 'timemax=' + end.unix();
			funcSendCommand(1, 130, minTime, maxTime);
		},
	});
}

// Updates the calendar events.
// This is called by the AJAX response handler in ajax.js.
// Initiated by fullcalendar.
function objectJsonPost152(jsonObject) {
	eventCallback(jsonObject);
}

// Loads assignment details from the server when the user
// clicks on a calendar event.
function loadAssignment(assignId) {
	funcSendCommand(0, 25, 'assignment=' + assignId);
}

