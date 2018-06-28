<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

/*

PHP Web Application Configuration Settings

Add anything for configuration here.

**NOTE: When these are used in a function, the global keyword is needed.

*/

// Debugging
$config_debug = true;

// Unix System V Semaphore, Shared Memory, Interprocess Communications
$sysv_semaphore_key = 0x485F2B14;
$sysv_sharemem_key = 0x15E7AC03;
$sysv_sharemem_size = 32768;


// Database
$dbase_type = "mysql";
$dbase_host = "athena";
$dbase_db = "seacore_db";
$dbase_uid = "seacore_db_user";
$dbase_passwd = "tide5346";
$dbase_charset = "utf8";


// OpenSSL
$ssl_digests = "SHA256 SHA1 RIPEMD160 MD5";


// Security Parameters
$security_userid_minlen = 3;	  // Minimum username length
$security_userid_maxlen = 50;	  // Maximum username length
$security_passwd_minlen = 8;	  // Minimum password length
$security_passwd_maxlen = 128;	  // Maximum password length
$security_salt_len = 32;	  // Default salt length
$security_hash_rounds = 100;	  // Password hash rounds
$security_hashtime_min = 10;	  // Minimum hash time rounds
$security_hashtime_max = 500;	  // Maximum hash time rounds
$security_password_expire_timeout = 7776000;  // Password Timeout
$security_session_regen_interval = 30;	      // Session ID regenerate interval (seconds)


// HTML
$html_site_base_url = "http://athena.ecs.csus.edu/~seacore/";
$html_site_base_url_secure = "https://athena.ecs.csus.edu/~seacore/";
$html_base_url = $html_site_base_url_secure;
$html_login_page = "login.php";
$html_portal_page = "portal.php";
$html_banner_page = "banner.php";


// Special Users
$users_vendor_posid = -1;
$users_admin_posid = -2;


// Time Card
$timecard_enable_breaks = true;
$timecard_logic_mode = 0;

// Misc
$timezone_default = "-08:00";

?>
