<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application Base Configuration File

All values defined in this file should be constants only.  Furthermore,
only values which are required to access shared memory and the database
should be in here as all other settings are stored in the database.  This
allows for a module to configure settings.

*/


// Debugging
const APP_DEBUG_STATUS = true;

// Unix System V Shared Memory (available on Windows too)
const APP_SYSV_SHAREDMEMORY_KEY = 0x15E7AC03;
const APP_SYSV_SHAREDMEMORY_SIZE = 32768;

// Database
const APP_DATABASE_TYPE = 'mysql';				// Database type
const APP_DATABASE_CONNECT = 'default';			// Must be inet, sock, or default
const APP_DATABASE_HOST = 'localhost';			// Hostname of DB server for inet
const APP_DATABASE_PORT = NULL;					// Port number of DB server for inet
const APP_DATABASE_SOCKET = NULL;				// Socket of DB server for sock
const APP_DATABASE_USER = 'default_db_user';	// Database username
const APP_DATABASE_PASSWORD = '4otOxis2S1p5';	// Database password for username
const APP_DATABASE_CHARSET = 'utf8';			// Default DB character set
const APP_DATABASE_CONFIG = 'configuration';	// Name of configuration database
const APP_DATABASE_USERDATA = 'userdata';		// Name of userdata database
const APP_DATABASE_APPLICATION = 'application';	// Name of application database

// Database: Activation (Remove for production use)
const APP_DATABASE_ACTIVATE = 'activation';


?>