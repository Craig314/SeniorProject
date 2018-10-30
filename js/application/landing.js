

// Custom application landing page activator

var eventObjects;

function customCmdProc(command, txt) {
	switch(command) {
		case 121:
			activateCalendar();
			objectJsonPost152(generateEvents());
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
		navLinks: true,
		eventLimit: true,
		// customize the button names,
		// otherwise they'd all just say "list"
		views: {
			listDay: { buttonText: 'list day' },
			listWeek: { buttonText: 'list week' }
		},
		eventClick: function(calEvent, jsEvent, view) {
			console.log(calEvent);
			console.log(jsEvent);
			console.log(view);
		},
	});
}

// Updates the calendar events.
function objectJsonPost152(object) {
	$('#calendar').fullCalendar('renderEvents', object);
}




// For testing purposes.  Remove in production code.
function generateEvents() {
	// Events here are for testing.
	var events = [
		{
			title: 'All Day Event',
			start: '2018-10-01',
			controlData: 203323
		},
		{
			title: 'Long Event',
			start: '2018-10-07',
			end: '2018-10-10',
			controlData: 203324
		},
		{
			id: 999,
			title: 'Repeating Event',
			start: '2018-10-09T16:00:00',
			controlData: 201225,
		},
		{
			id: 999,
			title: 'Repeating Event',
			start: '2018-10-16T16:00:00',
			controlData: 203926,
		},
		{
			title: 'Conference',
			start: '2018-10-11',
			end: '2018-10-13',
			controlData: 203383,
		},
		{
			title: 'Meeting',
			start: '2018-10-12T10:30:00',
			end: '2018-10-12T12:30:00',
			controlData: 203387
		},
		{
			title: 'Lunch',
			start: '2018-10-12T12:00:00',
			controlData: 203388
		},
		{
			title: 'Meeting',
			start: '2018-10-12T14:30:00',
			controlData: 203389
		},
		{
			title: 'Happy Hour',
			start: '2018-10-12T17:30:00',
			controlData: 203390
		},
		{
			title: 'Dinner',
			start: '2018-10-12T20:00:00',
			controlData: 203391
		},
		{
			title: 'Birthday Party',
			start: '2018-10-13T07:00:00',
			controlData: 203392
		},
		{
			title: 'Click for Google',
			url: 'http://google.com/',
			start: '2018-10-28',
			controlData: 203393
		}
	];
	return events;
}