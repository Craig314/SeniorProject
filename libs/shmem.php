<?php

/*

PHP Web Application Shared Memory Access


To use this class, insert the following line into your code:

	$sharemem = new SharedMemory();

When calling the first time in the scripts, you must specify
the ID and Size parameters.

	$sharemem = new SharedMemory(SHMID, SHMSZ);

Replace shremem with your variable name.

**ALERT NOTICE**

Use only this class to access shared memory.  Using other methods may
cause a race condition that can result in one or more of the following
events taking place:

  1.  The PHP interpreter crashes with a segmentation fault and causes
      a core dump.  This can cause #2...
  2.  The web server process crashes and drops a core file which can
      cause #3 to happen.
  3.  The kernel panics and brings the entire machine down.


NOTES:

This class is available on both Unix and Windows platforms.

*/

require_once 'util.php';

interface sharedMemoryInterface
{
	public function getstatus();
	public function remove();
	public function putdata($data);
	public function getdata();
}

class sharedMemory implements sharedMemoryInterface
{
	const STATUS_INVALID = 0;
	const STATUS_NOTLOADED = 1;
	const STATUS_NOVAR = 2;
	const STATUS_ERROR = 3;

	private $shmres;	// Shared Memory Resource
	private $shmsize;	// Shared Memory Size
	private $length;	// Size of object in shared memory
	private $status;	// Shared Memory Status

	// Class Constructor
	function __construct($shmid = false, $shmsz = false)
	{
		$valid = true;

		// Check Input
		$valid = ($shmid == false) ? false : $valid;
		$valid = ($shmsz == false) ? false : $valid;

		// Validate Input
		if ($valid == true)
		{
			$shres = @shmop_open($shmid, "n", 0644, $shmsz);
			if ($shres == false)
			{
				$shres = shmop_open($shmid, "w", 0, 0);
				if ($shres == false)
				{
					$valid = false;
					$this->status = sharedMemory::STATUS_ERROR;
				}
				else $this->status = sharedMemory::STATUS_NOVAR;
				
			}
			else $this->status = sharedMemory::STATUS_NOTLOADED;
			$this->shmres = $shres;
			$this->shmsize = $shmsz;
		}
		if ($valid == false)
		{
			$this->shmres = false;
			$this->shmsize = false;
			$this->status = sharedMemory::STATUS_INVALID;
		}

		// Return
		return;
	}

	// Class Destructor
	function __destruct()
	{
		if ($this->shmres != false)
		{
			shmop_close($this->shmres);
		}

		// Housekeeping
		$this->shmires = false;
		$this->shmsize = false;

		// Return
		return;
	}

	// Returns the status code of the shared memory class.
	public function getstatus()
	{
		return $this->status;
	}

	// Removes the shared memory region from the server's memory.
	public function remove()
	{
		// Check Input
		if ($this->shmres == false) return true;

		// Delete shared memory resource
		$result = shmop_delete($this->shmres);
		if ($result == true)
		{
			$this->shmres = false;
			$this->shmsize = false;
		}
		
		// Return
		return $result;
	}

	// Saves a variable into shared memory.
	// Returns true if successful or false if there was an error.
	public function putdata($data)
	{
		// Check Input
		if ($this->shmres == false) return false;

		// Save value
		$value = serialize($data);
		$value = str_to_nts($value);
		$this->length = strlen($value);
		$result = shmop_write($this->shmres, $value, 0);
		if ($result != false) $result = true;
		
		// Return
		return $result;
	}

	// Loads a variable from shared memory.
	// Returns the variable data or false if there was an error.
	public function getdata()
	{
		// Check Input
		if ($this->shmres == false) return false;

		// Load variable from shared memory
		$value = shmop_read($this->shmres, 0, 0);
		if ($value == false) return false;
		$value = nts_to_str($value);
		$data = unserialize($value);

		// Return
		return $data;
	}
}

?>