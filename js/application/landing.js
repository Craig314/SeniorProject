

// Custom application landing page activator

var eventObjects;

function customCmdProc(command, txt) {
	switch(command) {
		case 121:
			objectJsonPost152(null);
			activateCalendar();
			break;
		default:
			return false;
			break;
	}
	return true;
}

function activateCalendar() {
	$('#calendar').fullCalendar({
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'month,basicWeek,basicDay listDay,listWeek',
		},
		defaultView: 'month',
		defaultDate: '2018-03-05',	// Testing purposes
		navLinks: true,
		eventLimit: true,
		events: eventObjects,
		// customize the button names,
		// otherwise they'd all just say "list"
		views: {
			listDay: { buttonText: 'list day' },
			listWeek: { buttonText: 'list week' }
		},
	});
}

// Updates the calendar events.
function objectJsonPost152(object) {
	eventObjects = generateEvents();
}




// For testing purposes.  Remove in production code.
function generateEvents() {
	// Events here are for testing.
	var events = [
		{
			title: 'All Day Event',
			start: '2018-03-01'
		},
		{
			title: 'Long Event',
			start: '2018-03-07',
			end: '2018-03-10'
		},
		{
			id: 999,
			title: 'Repeating Event',
			start: '2018-03-09T16:00:00'
		},
		{
			id: 999,
			title: 'Repeating Event',
			start: '2018-03-16T16:00:00'
		},
		{
			title: 'Conference',
			start: '2018-03-11',
			end: '2018-03-13'
		},
		{
			title: 'Meeting',
			start: '2018-03-12T10:30:00',
			end: '2018-03-12T12:30:00'
		},
		{
			title: 'Lunch',
			start: '2018-03-12T12:00:00'
		},
		{
			title: 'Meeting',
			start: '2018-03-12T14:30:00'
		},
		{
			title: 'Happy Hour',
			start: '2018-03-12T17:30:00'
		},
		{
			title: 'Dinner',
			start: '2018-03-12T20:00:00'
		},
		{
			title: 'Birthday Party',
			start: '2018-03-13T07:00:00'
		},
		{
			title: 'Click for Google',
			url: 'http://google.com/',
			start: '2018-03-28'
		}
	];
	return events;
}