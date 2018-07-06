<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application AJAX Handling Library

This library contains code that deals with the AJAX server communications
system.  This allows various codes, commands, and other data to be sent
from the server to the client.

*/


require_once 'confload.php';
require_once 'html.php';


interface ajaxInterface
{
	// **** Constants must match values in ajax.js.

	// Data Types
	const TYPE_COMMAND	= 'CMD ';	// Command Data Type
	const TYPE_CODE		= 'CODE ';	// HTTP Code Data type
	const TYPE_STATUS	= 'STAT ';	// Error Status Data Type
	const TYPE_JSON		= 'JSON ';	// JSON Data Type
	const TYPE_MULTI	= 'MULTI ';	// Multiple Data Types

	// System Commands
	const CMD_OKCLRDISP		= 950;	// Status OK, Clear Form, Display
	const CMD_OKDISP		= 951;	// Status OK, Display
	const CMD_ERRCLRDISP	= 952;	// Status Error, Clear Form, Display
	const CMD_ERRDISP		= 953;	// Status Error, Display
	const CMD_CLRMSG		= 954;	// Clear Messages
	const CMD_CLRMSGHTML	= 955;	// Clear Messeges, Write HTML
	const CMD_ERRCLRCHTML	= 956;	// Status Error, Clear Form, Display, Clear HTML

	// HTTP Status Codes: Special
	const CODE_OK			= 200;	// Status OK
	const CODE_FOUND		= 302;	// Redirect
	const CODE_REDIR		= 303;	// Redirect

	// HTTP Status Codes: Client
	const CODE_BADREQ		= 400;	// Bad Request
	const CODE_NOAUTH		= 401;	// Not Authorized
	const CODE_FORBID		= 403;	// Forbidden
	const CODE_NOFILE		= 404;	// Not Found
	const CODE_NOMETH		= 405;	// Method Not Allowed
	const CODE_NOACPT		= 406;	// Not Acceptable
	const CODE_NOTIME		= 408;	// Request Timed Out

	// HTTP Status Codes: Server
	const CODE_INTERR		= 500;	// Internal Server Error
	const CODE_NOTIMP		= 501;	// Not Implemented
	const CODE_NOSERV		= 503;	// Service Unavailable

	// Public Methods
	public function redirect($filename);
	public function sendCode($code, $data = NULL);
	public function sendCommand($cmd, $data = NULL);
	public function sendStatus($data);
	public function sendJSON($jnbr, $data);
	public function loadQueueCode($code, $data = NULL);
	public function loadQueueCommand($cmd, $data = NULL);
	public function loadQueueStatus($data);
	public function loadQueueJSON($jnbr, $data);
	public function sendQueue();

}


class ajaxClass implements ajaxInterface
{
	private $queue = NULL;
	private $codeStatus = false;

	function __contstruct()
	{
		$this->queue = array();
		$this->codeStatus = false;
	}

	// Sends a redirect command to the client.
	// Note: Filename must begin with a /
	public function redirect($filename)	{
		$url = html::getBaseURL();
		$this->sendCode(ajaxClass::CODE_REDIR, $url . $filename);
	}

	// Sends a code to the client.
	public function sendCode($code, $data = NULL)
	{
		if (empty($data)) echo ajaxClass::TYPE_CODE . $code;
		else echo ajaxClass::TYPE_CODE . $code . ' ' . $data;
	}

	// Sends a command to the client.
	public function sendCommand($cmd, $data = NULL)
	{
		if (empty($data)) echo ajaxClass::TYPE_COMMAND . $cmd;
		else echo ajaxClass::TYPE_COMMAND . $cmd . ' ' . $data;
	}

	// Sends error status to the client.
	public function sendStatus($data)
	{
		if (empty($data)) return;
		$str = jason_encode($data);
		if ($str === false)
		{
			sendCommand(ajaxClass::CMD_ERRDISP, 'JSON encoding error: (' .
				json_last_error() . ') ' . json_last_error_msg());
			exit(1);
		}
		echo ajaxClass::TYPE_STATUS . $str;
	}

	// Sends JSON format data to the server.
	public function sendJSON($jnbr, $data)
	{
		echo ajaxClass::JSON . $jnbr . ' ' . $data;
	}

	// Loads a code into the send queue.
	public function loadQueueCode($code, $data = NULL)
	{
		if ($this->codeStatus) return;
		if (empty($data)) $str = ajaxClass::TYPE_CODE . $code;
		else $str = ajaxClass::TYPE_CODE . $code . ' ' . $data;
		array_push($this->queue, $str);
		$this->codeStatus = true;
	}

	// Loads a command into the send queue.
	public function loadQueueCommand($cmd, $data = NULL)
	{
		if (empty($data)) $str = ajaxClass::TYPE_COMMAND . $cmd;
		else $str = ajaxClass::TYPE_COMMAND . $cmd . ' ' . $data;
		array_push($this->queue, $str);
	}

	// Loads error status into the send queue.
	public function loadQueueStatus($data)
	{
		if (empty($data)) return;
		$str = jason_encode($data);
		if ($str === false)
		{
			sendCommand(ajaxClass::CMD_ERRDISP, 'JSON encoding error: (' .
				json_last_error() . ') ' . json_last_error_msg());
			exit(1);
		}
		$str = ajaxClass::TYPE_STATUS . $str;
		array_push($this->queue, $str);
	}

	// Loads JSON formatted data into the send queue.
	public function loadQueueJSON($jnbr, $data)
	{
		$str = ajaxClass::TYPE_JASON . $jnbr . ' ' . $data;
		array_push($this->queue, $str);
	}

	// Sends the queue to the client.
	public function sendQueue()
	{
		if (length($this->queue) == 0) return;
		$str = json_encode($this->queue);
		if ($str === false)
		{
			sendCommand(ajaxClass::CMD_ERRDISP, 'JSON encoding error: (' .
				json_last_error() . ') ' . json_last_error_msg());
			exit(1);
		}
		echo ajaxClass::TYPE_MULTI . $str;
		$this->queue = array();
		$this->codeStatus = false;
	}

}


// Auto instantiate the class
$ajax = new ajaxClass();


?>