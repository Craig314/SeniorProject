<?php

/*

Load Configuration Database

This checks if a predefined shared memory region is defined and
active.  If not, then the region is created and the configuration
settings are loaded into it.  This is a hot-wired library that
performs the specified operation on loading.

*/

require_once 'confbase.php';
require_once 'dbaseconf.php';
require_once 'shmem.php';

if (APP_DEBUG_STATUS)
{
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	ini_set('display_startup_errors', '1');
}

// Define the global configuration variable.
$CONFIGVAR = NULL;

// Begin shared memory processing.
//var_dump($CONFIGVAR);
processSharedMemoryInitial();
//var_dump($CONFIGVAR);

// Perform the initial processing of the shared memory region.
function processSharedMemoryInitial()
{
	$shmem = new shared_memory(APP_SYSV_SHAREDMEMORY_KEY, APP_SYSV_SHAREDMEMORY_SIZE);
	switch ($shmem->getstatus())
	{
		case 0:			// Invalid Parameters
			echo 'shared_memory: invalid parameters';
			exit(1);
			break;
		case 1:		// Valid: Load config into shared memory, then fall through.
			loadConfigurationDatabase($shmem);
		case 2:		// Valid: Load global variable with config data.
			loadConfigurationVariable($shmem);
			break;
		case 3:
			echo 'shared_memory: shmop_open failure';
			exit(1);
			break;
		default:
			echo 'shared_memory: unknown error';
			exit(1);
			break;
	}
}

// Reloads the configuration data into shared memory.
function processSharedMemoryReload()
{
	$shmem = new shared_memory(APP_SYSV_SHAREDMEMORY_KEY, APP_SYSV_SHAREDMEMORY_SIZE);
	switch ($shmem->getstatus())
	{
		case 0:			// Invalid Parameters
			echo 'shared_memory: invalid parameters';
			exit(1);
			break;
		case 1:		// Valid: Falls through.
		case 2:		// Valid: 2-Step:
					// 1) Load configuration data from database into shared memory.
					// 2) Load configuration data from shared memory into global variable.
			loadConfigurationDatabase($shmem);
			loadConfigurationVariable($shmem);
			break;
		case 3:
			echo 'shared_memory: shmop_open failure';
			exit(1);
			break;
		default:
			echo 'shared_memory: unknown error';
			exit(1);
			break;
	}
}

// Loads the configuration from the database into shared memory.
function loadConfigurationDatabase($shmem)
{
	global $dbconf;
	$cfg = array();
	$rxa = $dbconf->queryConfigAll();
	if ($rxa === false)
	{
		echo 'database:configuration.config: unknown error or no data.';
		$shmem->remove();
		exit(1);
	}
	foreach($rxa as $kx => $vx)
	{
		$cfg[$vx['name']] = $vx;
	}
	$result = $shmem->putdata($cfg);
	if ($result == false)
	{
		echo 'shared_memory: shmop_write failure';
		$shmem->remove();
		exit(1);
	}
}

// Load the configuration from shared memory into a global variable.
function loadConfigurationVariable($shmem)
{
	global $CONFIGVAR;

	$result = $shmem->getdata();
	if ($result === false)
	{
		echo 'shared_memory: shmop_read failure';
		$shmem->remove();
		exit(1);
	}
	$CONFIGVAR = $result;
}

?>