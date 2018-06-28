<?php

require_once 'confload.php';
require_once 'html.php';

/*

This library contains code that deals with the AJAX server communications
system.  This allows various codes, commands, and other data to be sent
from the server to the client.

*/

interface ajax_interface
{
	const CMD_ERROR = 953;
}

class ajaxClass implements ajax_interface
{
	private $queue = NULL;

	function __contstruct()
	{
		$this->queue = array();
	}

	// Sends a redirect command to the client.
	public function redirect($filename)
	{
		global $CONFIGVAR;

		$url = html::getBaseURL();
		$this->sendCode(303, $url . $filename);
	}

	// Sends a code to the client.
	public function sendCode($code, $data = NULL)
	{
		if (empty($data)) echo "CODE " . $code;
		else echo "CODE " . $code . " " . $data;
	}

	// Sends a command to the client.
	public function sendCommand($cmd, $data = NULL)
	{
		if (empty($data)) echo "CMD " . $cmd;
		else echo "CMD " . $cmd . " " . $data;
	}

	// Sends JSON format data to the server.
	public function sendJSON($jnbr, $data)
	{
		echo "JSON" . $jnbr . " " . $data;
	}

	// Loads a code into the send queue.
	public function loadQueueCode($code, $data = NULL)
	{
		if (empty($data)) $str = "CODE " . $code;
		else $str = "CODE " . $code . " " . $data;
		array_push($this->queue, $str);
	}

	// Loads a command into the send queue.
	public function loadQueueCommand($cmd, $data = NULL)
	{
		if (empty($data)) $str = "CMD " . $cmd;
		else $str = "CMD " . $cmd . " " . $data;
		array_push($this->queue, $str);
	}

	// Loads JSON formatted data into the send queue.
	public function loadQueueJSOSN($jnbr, $data)
	{
		$str = "JSON" . $jnbr . " " . $data;
		array_push($this->queue, $str);
	}

	// Sends the queue to the client.
	public function sendQueue()
	{
		if (length($this->queue) == 0) return;
		$str = json_encode($this->queue);
		if ($str === false)
		{
			sendCommand(ajaxClass::CMD_ERROR, "JSON encoding error: (" . json_last_error()
				. ") " . json_last_error_msg());
			exit(1);
		}
		echo "MULTI " . $str;
		$this->queue = array();
	}

}

// Auto instantiate the class
$ajax = new ajaxClass();


?>