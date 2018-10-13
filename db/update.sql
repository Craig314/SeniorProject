
use configuration;
delete from config;
delete from module;

-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.7.22-log - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             9.5.0.5280
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping data for table configuration.config: ~46 rows (approximately)
/*!40000 ALTER TABLE `config` DISABLE KEYS */;
INSERT INTO `config` (`setting`, `type`, `name`, `dispname`, `value`, `description`, `admin`) VALUES
	(0, 0, 'server_document_root', 'Appliation Root Directory', '/Servers/webdocs', 'This sets the application root directory on the server.', 1),
	(1, 0, 'server_hostname', 'Server Hostname', 'localhost', 'This is the server hostname.  It is used to build the base\r\nURL which is used throughout the application.', 1),
	(2, 2, 'server_secure', 'Use HTTPS', '0', 'The flag which indicates that the encrypted HTTPS protocol is to be used.', 1),
	(3, 1, 'server_http_port', 'HTTP Port Number', '22080', 'The network port number that the application is to use\r\nwhen using the unencrypted HTTP protocol.  Default is\r\n80.', 1),
	(4, 1, 'server_https_port', 'HTTPS Port Number', '443', 'The network port number that the application is to use\r\nwhen using the encrypted HTTPS protocol. Default is\r\n443.', 1),
	(10, 1, 'html_default_label_size', 'Default HTML Label Size', '3', 'Default size of field text labels on web forms.', 0),
	(11, 1, 'html_default_field_size', 'Default HTML Field Size', '4', 'Default size of fields on web forms.', 0),
	(12, 0, 'html_login_page', 'Application Login Page', 'login.php', 'The main login page of the application.  All\r\nunauthenticated users are redirected here.', 0),
	(13, 0, 'html_banner_page', 'Application Banner Page', 'banner.php', 'The page that is displayed right after the user\r\nis authenticated on the main login page.', 0),
	(14, 0, 'html_gridportal_page', 'Application Grid Portal Page', 'gridportal.php', 'The grid portal page.  This shows the available modules in grid format.', 0),
	(15, 0, 'html_linkportal_page', 'Application Link Portal Page', 'linkportal.php', 'The link portal page.  This shows the available modules in link format.', 0),
	(16, 0, 'html_appportal_page', 'Application App Portal Page', 'appportal.php', 'The landing portal page.  This shows the available modules in link format with additional content.', 0),
	(17, 1, 'html_grid_mod_id', 'Application Grid Portal Module ID', '1', 'The module ID for the grid portal page.', 0),
	(18, 1, 'html_link_mod_id', 'Application Link Portal Module ID', '2', 'The module ID for the link portal page.', 0),
	(20, 0, 'openssl_digests', 'OpenSSL Digests', 'SHA256 SHA1 RIPEMD160 MD5', 'A list of acceptable message digest algorithms\r\nwhich are used for password hashing.', 1),
	(25, 0, 'login_banner_title', 'Login Banner Title', '! ! ! ! WARNING ! ! ! !\r\n', 'The banner title that is displayed when the user logs into the system.', 1),
	(26, 3, 'login_banner_subtitle', 'Login Banner Subtitle', 'THIS IS A PRIVATE COMPUTER SYSTEM<br>\r\nUNAUTHORIZED ACCESS IS STRICTLY PROHIBITED\r\n', 'The banner subtitle that is displayed when the user logs into the system.', 1),
	(27, 3, 'login_banner_message', 'Login Banner Message', 'Any unauthorized use or access or any attempted<br>\r\nunauthorized use or access of this computer system<br>\r\nis a violation of state and federal law and will be<br>\r\ninvestigated and procecuted under the maximum extent<br>\r\nprovided by said law.  This system is subject to<br>\r\nmonitoring and audit.  By continuing to use this system<br>\r\nyou expressly agree to such monitoring.<br>\r\n', 'The banner message that is displayed when the user logs into the system.', 1),
	(30, 1, 'security_username_minlen', 'Username Minimum Length', '3', 'Minimum acceptable length of usernames.', 1),
	(31, 1, 'security_username_maxlen', 'Username Maximum Length', '32', 'Maximum acceptable length of usernames.', 1),
	(32, 1, 'security_passwd_minlen', 'Password Minimum Length', '8', 'Minimum acceptable length of passwords.', 1),
	(33, 1, 'security_passwd_maxlen', 'Password Maximum Length', '128', 'Maximum acceptable length of passwords.', 1),
	(34, 1, 'security_passexp_timeout', 'Password Expire Timeout', '7776000', 'Maximum allowed time between password changes.\r\nWhen this is exceeded, the user will be forced to\r\nchange their password.', 1),
	(35, 1, 'security_passwd_complex_level', 'Password Complexity Level', '0', 'The complexity of the user password.\r\n0: None\r\n1: Letters and numbers\r\n2: Upper and lower case letter and numbers\r\n3: Same as 2 plus symbols.', 1),
	(36, 2, 'security_passchg_new', 'Force Password Change on Reset', '1', 'Forces the user to change their password if their account\r\nis newly created or if the password has been reset by an\r\nadministrator.', 1),
	(37, 1, 'security_salt_len', 'Password Salt Length', '32', 'The length of the password salt in bytes.', 1),
	(38, 1, 'security_hash_rounds', 'Password Hash Rounds', '100', 'Number of times to hash password + salt for password encryption.', 1),
	(39, 1, 'security_hashtime_min', 'Password Hashtime Minimum', '10', 'Minimum jitter time for password hashing.', 0),
	(40, 1, 'security_hashtime_max', 'Password Hashtime Maximum', '500', 'Maximum jitter time for password hashing.', 0),
	(41, 1, 'security_login_failure_lockout', 'Allowed Login Failure Attempts', '5', 'The maximum number of login failure attempts before the account is locked out.', 1),
	(42, 1, 'security_lockout_time', 'Account Lockout Time', '900', 'The amount of time the account is locked out after the\r\nnumber of login failure attempts have been exceeded.', 1),
	(50, 2, 'session_regen_enable', 'Session Regenerate Enable', '0', 'Enables or disables session ID regeneration.', 1),
	(51, 1, 'session_regen_time', 'Session Regenerate Timeout', '30', 'The time interval between session ID regeneration.\r\nThis helps to prevent session fixation and session\r\nhijacking.', 1),
	(52, 1, 'session_expire_time', 'Session Expire Timeout', '60', 'The time in seconds that the old session before ID regeneration is still valid.', 1),
	(53, 1, 'session_nonce_len', 'Session Nonce Length', '32', 'The length of the session nonce in bytes.', 0),
	(54, 1, 'session_cookie_expire_time', 'Session Cookie Timeout', '900', 'The time in seconds before the client cookie expires on a per page basis.', 1),
	(55, 2, 'session_use_tokens', 'Use Session Tokens', '1', 'Tells the system to use session tokens in addition to session IDs.', 1),
	(60, 10, 'timezone_default', 'Time Displacement From UTC', '-08:00', 'This sets the timezone displacement from UTC.', 1),
	(70, 1, 'account_id_none', 'Null Account ID', '0', 'The account ID number for a NULL user which has not yet logged in.', 0),
	(71, 1, 'account_id_vendor', 'Vendor Account ID', '1', 'The account ID number of the vendor account.', 0),
	(72, 1, 'account_id_admin', 'Admin Account ID', '2', 'The account ID number of the administrator account.', 0),
	(75, 1, 'profile_id_none', 'Null Profile ID', '0', 'The profile ID number for a NULL user who has not yet logged in.', 0),
	(76, 1, 'profile_id_vendor', 'Vendor Profile ID', '1', 'The profile ID number of the vendor account.', 0),
	(77, 1, 'profile_id_admin', 'Admin Profile ID', '2', 'The profile ID number of the administrator account.', 0),
	(80, 2, 'oauth_enable', 'OAuth Login Enabled', '0', 'Specifies whether OAuth is a login method.', 1),
	(90, 2, 'openid_enable', 'OpenID Login Enabled', '0', 'Specifies whether OpenID is a login method.', 1);
/*!40000 ALTER TABLE `config` ENABLE KEYS */;

-- Dumping data for table configuration.module: ~15 rows (approximately)
/*!40000 ALTER TABLE `module` DISABLE KEYS */;
INSERT INTO `module` (`moduleid`, `name`, `description`, `filename`, `iconname`, `active`, `allusers`, `system`, `vendor`) VALUES
	(1, 'Grid Portal', 'Views the available modules in grid format.', 'gridportal.php', 'icon_grid', 1, 1, 1, 0),
	(2, 'Link Portal', 'Views the available modules in link format.', 'linkportal.php', 'icon_chain2', 1, 1, 1, 0),
	(3, 'Application Portal', 'The application landing page that most users see&period;  Similar to link portal but with additional content in the main panel&period;', 'appportal.php', 'icon_chart', 1, 1, 0, 0),
	(10, 'Module Data Editor', 'Edits module data in the database.', 'modedit.php', 'icon_gears', 1, 0, 1, 1),
	(11, 'Configuration Editor', 'Edits the application configuration parameters&period;&NewLine;This allows inserting and deleting of configuration&NewLine;items in the database&period;', 'configedit.php', 'icon_tools3', 1, 0, 1, 1),
	(12, 'System Flags', 'This module edits the system flags which&NewLine;are used by the various user profiles&period;', 'sysflag.php', 'icon_checklist', 1, 0, 1, 1),
	(13, 'Application Flags', 'This module edits the application flags which are used by the various user profiles&period;', 'appflag.php', 'icon_checklist', 1, 0, 1, 1),
	(14, 'File Finder', 'Allows access to the server file system&period;', 'filefinder.php', 'icon_harddisk', 1, 0, 1, 1),
	(20, 'Parameter Editor', 'Edits the configuration parameter values for the application&period;&NewLine;This only allows changing the configuration parameter values&period;', 'paramedit.php', 'icon_tools4', 1, 0, 1, 0),
	(21, 'Profile Editor', 'Edits the available profiles that defines&NewLine;what access rights a user has&period;', 'profedit.php', 'icon_keys', 1, 0, 1, 0),
	(22, 'OAuth Provider Edit', 'Edits the known list of external OAuth authentication providers&period;', 'oauthedit.php', 'icon_oauth', 1, 0, 1, 0),
	(23, 'OpenID Provider Edit', 'Edits the known list of external OpenID authentication providers&period;', 'openidedit.php', 'icon_openid', 1, 0, 1, 0),
	(24, 'User Editor', 'Edits the user database', 'useredit.php', 'icon_users2', 1, 0, 1, 0),
	(30, 'Change Password', 'Allows a user to change their login password&period;', 'passwd.php', 'icon_padlock', 1, 1, 1, 0),
	(31, 'User Data Editor', 'Allows the user to edit some of their own information&period;', 'userdata.php', 'icon_user', 1, 1, 1, 0);
/*!40000 ALTER TABLE `module` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
