/*

SEA-CORE International Ltd.
SEA-CORE Development Group

JavaScript File for index.php test file
Written by Daniel Rudy

*/


var id='field1';

function setStatusOk()
{
	document.getElementById('dcmGL-' + id).setAttribute('class', 'glyphicon glyphicon-ok form-control-feedback');
	document.getElementById('dcmST-' + id).setAttribute('class', 'form-group has-success has-feedback');
	document.getElementById('dcmMS-' + id).innerHTML = '';
}

function setStatusWarn()
{
	document.getElementById('dcmGL-' + id).setAttribute('class', 'glyphicon glyphicon-check form-control-feedback');
	document.getElementById('dcmST-' + id).setAttribute('class', 'form-group has-warning has-feedback');
	document.getElementById('dcmMS-' + id).innerHTML = "Field status set to Warning.";
}

function setStatusError()
{
	document.getElementById('dcmGL-' + id).setAttribute('class', 'glyphicon glyphicon-remove form-control-feedback');
	document.getElementById('dcmST-' + id).setAttribute('class', 'form-group has-error has-feedback');
	document.getElementById('dcmMS-' + id).innerHTML = 'Field status set to Error.';
}

function setStatusDefault()
{
	document.getElementById('dcmGL-' + id).setAttribute('class', 'glyphicon form-control-feedback');
	document.getElementById('dcmST-' + id).setAttribute('class', 'form-group');
	document.getElementById('dcmMS-' + id).innerHTML = '';
}

function testSubmit() {
	var params;

	params = treeWalker();
	ajaxServerCommand.sendCommand(20, params);
}
