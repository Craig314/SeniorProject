

// Custom application landing page activator


function customCmdProc(command, txt) {
	switch(command) {
		case 121:
			$('#calendar').fullCalendar({
				defaultView: 'month',
			});
			break;
		default:
			return false;
			break;
	}
	return true;
}

