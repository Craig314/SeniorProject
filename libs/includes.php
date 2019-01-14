<?php

/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application Library Includes File

This file contains all the libraries that the application uses
and is for including system library files only.  Application libraries
should be included first.

** ORDER MATTERS **

*/

require_once 'confbase.php';
require_once 'shmem.php';
require_once 'error.php';
require_once 'database.php';
require_once 'dbaseconf.php';
require_once 'dbaseuser.php';
require_once 'utility.php';
require_once 'confload.php';
require_once 'vfystr.php';
require_once 'account.php';
require_once 'ajax.php';
require_once 'flag.php';
require_once 'html.php';
require_once 'security.php';
require_once 'session.php';
require_once 'timedate.php';

// These are commented out because they are for specific
// types of modules.
// require_once 'files.php';
// require_once 'mime.php';
// require_once 'oauth.php';
// require_once 'openid.php';

// Must be last
require_once 'modhead.php';

?>