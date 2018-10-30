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
	const CMD_WNAVPANEL		= 957;	// Write HTML to navigation panel
	const CMD_WSTATPANEL	= 958;	// Write HTML to status panel
	const CMD_WMAINPANEL	= 959;	// Write HTML to main panel
	const CMD_LDFLDATA		= 960;	// Loads field checking data

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
	public function writePanelsImmediate($nav, $stat, $main, $fields = NULL);
	public function writeMainPanelImmediate($main, $fields);
	public function writeStatusPanelImmediate($status);
}


class ajaxClass implements ajaxInterface
{
	private $queue = array();
	private $codeStatus = false;

	function _contstruct()
	{
		$this->queue = array();
		$this->codeStatus = false;
	}

	// Sends a redirect command to the client.
	// Note: Filename must begin with a /
	public function redirect($filename)	{
		$url = html::getBaseURL();
		$this->sendCode(self::CODE_REDIR, $url . $filename);
	}

	// Sends a code to the client.
	public function sendCode($code, $data = NULL)
	{
		if (empty($data)) echo self::TYPE_CODE . $code;
		else echo self::TYPE_CODE . $code . ' ' . $data;
	}

	// Sends a command to the client.
	public function sendCommand($cmd, $data = NULL)
	{
		if (empty($data)) echo self::TYPE_COMMAND . $cmd;
		else echo self::TYPE_COMMAND . $cmd . ' ' . $data;
	}

	// Sends error status to the client.
	public function sendStatus($data)
	{
		if (empty($data)) return;
		$str = json_encode($data);
		if ($str === false)
		{
			sendCommand(self::CMD_ERRDISP, 'JSON encoding error: (' .
				json_last_error() . ') ' . json_last_error_msg());
			exit(1);
		}
		echo self::TYPE_STATUS . $str;
	}

	// Sends JSON format data to the server.
	public function sendJSON($jnbr, $data)
	{
		echo self::JSON . $jnbr . ' ' . $data;
	}

	// Loads a code into the send queue.
	public function loadQueueCode($code, $data = NULL)
	{
		if ($this->codeStatus) return;
		if (empty($data)) $str = self::TYPE_CODE . $code;
		else $str = self::TYPE_CODE . $code . ' ' . $data;
		array_push($this->queue, $str);
		$this->codeStatus = true;
	}

	// Loads a command into the send queue.
	public function loadQueueCommand($cmd, $data = NULL)
	{
		if (empty($data)) $str = self::TYPE_COMMAND . $cmd;
		else $str = self::TYPE_COMMAND . $cmd . ' ' . $data;
		array_push($this->queue, $str);
	}

	// Loads error status into the send queue.
	public function loadQueueStatus($data)
	{
		if (empty($data)) return;
		$str = json_encode($data);
		if ($str === false)
		{
			sendCommand(self::CMD_ERRDISP, 'JSON encoding error: (' .
				json_last_error() . ') ' . json_last_error_msg());
			exit(1);
		}
		$str = self::TYPE_STATUS . $str;
		array_push($this->queue, $str);
	}

	// Loads the field list into the queue.
	public function loadQueueFieldList($data)
	{
	}

	// Loads JSON formatted data into the send queue.
	public function loadQueueJSON($jnbr, $data)
	{
		$str = self::TYPE_JSON . $jnbr . ' ' . $data;
		array_push($this->queue, $str);
	}

	// Sends the queue to the client.
	public function sendQueue()
	{
		if (count($this->queue) == 0) return;
		$str = json_encode($this->queue);
		if ($str === false)
		{
			sendCommand(self::CMD_ERRDISP, 'JSON encoding error: (' .
				json_last_error() . ') ' . json_last_error_msg());
			exit(1);
		}
		echo self::TYPE_MULTI . $str;
		$this->queue = array();
		$this->codeStatus = false;
	}

	// Writes the given data to the three panels.
	public function writePanelsImmediate($nav, $stat, $main, $fields = NULL)
	{
		$data = array(
			0 => self::TYPE_COMMAND . self::CMD_WNAVPANEL . ' ' . $nav,
			1 => self::TYPE_COMMAND . self::CMD_WSTATPANEL . ' ' . $stat,
			2 => self::TYPE_COMMAND . self::CMD_WMAINPANEL . ' ' . $main,
		);
		if (!empty($fields)) $data[3] = self::TYPE_COMMAND .
			self::CMD_LDFLDATA . ' ' . $fields;
		$sendData = json_encode($data);
		echo self::TYPE_MULTI . $sendData;
	}

	public function writeMainPanelImmediate($main, $fields)
	{
		if (isarray($fields))
		{
			$data = array(
				0 => self::TYPE_COMMAND . self::CMD_WMAINPANEL . ' ' . $main,
				1 => self::TYPE_COMMAND . self::CMD_LDFLDATA . ' ' . $fields,
			);
			$sendData = json_encode($data);
			echo self::TYPE_MULTI . $sendData;
		}
		else
		{
			echo self::TYPE_COMMAND . self::CMD_WMAINPANEL . ' ' . $main;
		}
	}

	public function writeStatusPanelImmediate($status)
	{
		echo self::TYPE_COMMAND . self::CMD_WSTATPANEL . ' ' . $status;
	}

}


// Auto instantiate the class
$ajax = new ajaxClass();


?>