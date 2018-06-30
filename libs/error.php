<?php

/*

PHP Web Application Error Handling Library

Error messages are collected into an array as they are
encountered.  Then they are pulled out and sent to
the user.


*/

interface handleErrorsInterface
{
	// These constants must match value in ajax.js.
	const ETMISC	= 0;	// Misc Errors
	const ETDBASE	= 1;	// Error in database
	const ETFORM	= 2;	// Form Input

	// These constants must match values in both html.php and ajax.js.
	const ESDEFAULT	= 0;	// Default State
	const ESOK		= 1;	// Ok State
	const ESWARN	= 2;	// Warning State
	const ESFAIL	= 3;	// Failure State
	const ESGEN		= 4;	// General Error State (Displays in common area)

	public function setLineTerm($lntchr);
	public function reset();
	public function checkState();
	public function errorGetMessage();
	public function errorGetData();
	public function errorPutMessage($type, $msg = '', $state = 0, $field = '', $id = '');
	public function puterrmsg($message);

}

class handleErrors implements handleErrorsInterface
{
	private $status = false;
	private $errmsg = array();
	private $lnterm = '<br>';

	function __constructor()
	{
		$this->status = false;
		$this->errmsg = array();
		$this->lnterm = '<br>';
	}

	// Sets the error message line terminator.
	public function setlineterm($lntchr)
	{
		$this->lnterm = $lntchr;
	}

	// Resets the error subsystem.
	public function reset()
	{
		$this->status = false;
		$this->errmsg = array();
	}

	// Returns the current state of the error handling system.
	// False if no errors are in the buffer, true if there are.
	public function checkState()
	{
		return($this->status);
	}

	// Returns any messages accumilated by the error handling system.
	public function errorGetMessage()
	{
		$str = "";
		if (count($this->errmsg) > 0)
		{
			foreach($this->errmsg as $kx => $vx)
			{
				$str .= $vx['msg'] . $this->lnterm;
			}
		}
		return $str;
	}

	// Returns all data that has been accumilated by the error
	// handling system.
	public function errorGetData()
	{
		$rxe = array();
		if (count($this->errmsg) > 0)
		{
			foreach($this->errmsg as $kx => $vx)
			{
				array_push($rxe, $vx);
			}
		}
		return $rxe;
	}

	// Adds an error message.
	public function errorPutMessage($type, $msg = '', $state = 0, $field = '', $id = '')
	{
		array_push(
			$this->errmsg,
			array(
				'type'		=> $type,
				'message'	=> $msg,
				'status'	=> $state,
				'field'		=> $field,
				'id'		=> $id,
			)
		);
		if ($state != errorHandling::ESOK) $this->status = true;
	}

	// Adds an error message with default values.
	public function puterrmsg($message)
	{
		array_push(
			$this->errmsg,
			array(
				'type'		=> handleErrors::ETMISC,
				'message'	=> $message,
				'status'	=> handleErrors::ESFAIL,
				'field'		=> '',
				'id'		=> '',
			)
		);
		$this->status = true;
	}
}

// Auto instantiate the class
$herr = new handleErrors();


?>