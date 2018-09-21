-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.7.17-log - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             9.5.0.5280
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for configuration
CREATE DATABASE IF NOT EXISTS `configuration` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `configuration`;

-- Dumping structure for table configuration.config
CREATE TABLE IF NOT EXISTS `config` (
  `setting` int(10) unsigned NOT NULL COMMENT 'Setting identifier, key field',
  `type` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Datatype of setting. 0: string, 1: integer, 2: boolean, 10: timezone displacement',
  `name` varchar(32) DEFAULT NULL COMMENT 'Internal name of setting',
  `dispname` varchar(64) DEFAULT NULL COMMENT 'Display name of setting',
  `value` varchar(512) DEFAULT NULL COMMENT 'Setting value',
  `description` varchar(256) DEFAULT NULL COMMENT 'Long description of setting',
  `admin` int(11) NOT NULL DEFAULT '0' COMMENT 'Allow administrator access',
  PRIMARY KEY (`setting`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table contains configuration information about the application.\r\nThe following numerical ranges are defined:\r\n0-9  Server\r\n10-19 HTML\r\n20-29 SSL\r\n30-49 Security\r\n50-59 Session\r\n60-69 Time/Timezone\r\n70-79 Account/Profile\r\n1000+ Application Specific';

-- Dumping data for table configuration.config: ~44 rows (approximately)
/*!40000 ALTER TABLE `config` DISABLE KEYS */;
INSERT INTO `config` (`setting`, `type`, `name`, `dispname`, `value`, `description`, `admin`) VALUES
	(0, 0, 'server_document_root', 'Appliation Root Directory', '/usr/local/www/apache24/data', 'This sets the application root directory on the server.', 1),
	(1, 0, 'server_hostname', 'Server Hostname', 'strata.danielrudy.org', 'This is the server hostname.  It is used to build the base\r\nURL which is used throughout the application.', 1),
	(2, 2, 'server_secure', 'Use HTTPS', '1', 'The flag which indicates that the encrypted HTTPS protocol is to be used.', 1),
	(3, 1, 'server_http_port', 'HTTP Port Number', '22080', 'The network port number that the application is to use\r\nwhen using the unencrypted HTTP protocol.  Default is\r\n80.', 1),
	(4, 1, 'server_https_port', 'HTTPS Port Number', '443', 'The network port number that the application is to use\r\nwhen using the encrypted HTTPS protocol. Default is\r\n443.', 1),
	(10, 1, 'html_default_label_size', 'Default HTML Label Size', '2', 'Default size of field text labels on web forms.', 0),
	(11, 1, 'html_default_field_size', 'Default HTML Field Size', '4', 'Default size of fields on web forms.', 0),
	(12, 0, 'html_login_page', 'Application Login Page', 'login.php', 'The main login page of the application.  All\r\nunauthenticated users are redirected here.', 0),
	(13, 0, 'html_banner_page', 'Application Banner Page', 'banner.php', 'The page that is displayed right after the user\r\nis authenticated on the main login page.', 0),
	(14, 0, 'html_gridportal_page', 'Application Grid Portal Page', 'gridportal.php', 'The grid portal page.  This shows the available modules in grid format.', 0),
	(15, 0, 'html_linkportal_page', 'Application Link Portal Page', 'linkportal.php', 'The link portal page.  This shows the available modules in link format.', 0),
	(16, 1, 'html_grid_mod_id', 'Application Grid Portal Module ID', '1', 'The module ID for the grid portal page.', 0),
	(17, 1, 'html_link_mod_id', 'Application Link Portal Module ID', '2', 'The module ID for the link portal page.', 0),
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
	(50, 1, 'session_regen_time', 'Session Regenerate Timeout', '30', 'The time interval between session ID regeneration.\r\nThis helps to prevent session fixation and session\r\nhijacking.', 1),
	(51, 1, 'session_expire_time', 'Session Expire Timeout', '60', 'The time in seconds that the old session before ID regeneration is still valid.', 1),
	(52, 1, 'session_nonce_len', 'Session Nonce Length', '32', 'The length of the session nonce in bytes.', 0),
	(53, 1, 'session_cookie_expire_time', 'Session Cookie Timeout', '900', 'The time in seconds before the client cookie expires on a per page basis.', 1),
	(54, 2, 'session_use_tokens', 'Use Session Tokens', '1', 'Tells the system to use session tokens in addition to session IDs.', 1),
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

-- Dumping structure for table configuration.flagdesc_app
CREATE TABLE IF NOT EXISTS `flagdesc_app` (
  `flag` int(10) unsigned NOT NULL COMMENT 'Unique flag identifier',
  `name` varchar(32) DEFAULT NULL COMMENT 'Display name of flag',
  `description` varchar(256) DEFAULT NULL COMMENT 'Description of the flag',
  PRIMARY KEY (`flag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table defines the names and descriptions of application flags from the userdata.profile table.  The flag attribute is the bit position of the flag.';

-- Dumping data for table configuration.flagdesc_app: ~4 rows (approximately)
/*!40000 ALTER TABLE `flagdesc_app` DISABLE KEYS */;
INSERT INTO `flagdesc_app` (`flag`, `name`, `description`) VALUES
	(0, 'Test Flag A1', 'This flag is for testing purposes only&period;'),
	(1, 'Test Flag A2', 'This flag is for testing purposes only&period;'),
	(2, 'Test Flag A3', 'This flag is for testing purposes only&period;'),
	(8, 'Test Flag A8', 'This flag is for testing purposes only&period;');
/*!40000 ALTER TABLE `flagdesc_app` ENABLE KEYS */;

-- Dumping structure for table configuration.flagdesc_core
CREATE TABLE IF NOT EXISTS `flagdesc_core` (
  `flag` int(10) unsigned NOT NULL COMMENT 'Unique flag identifier',
  `name` varchar(32) DEFAULT NULL COMMENT 'Display name of flag',
  `description` varchar(256) DEFAULT NULL COMMENT 'Description of the flag',
  PRIMARY KEY (`flag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table defines the names and descriptions of core system flags from the userdata.profile table.  The flag attribute is the bit position of the flag.';

-- Dumping data for table configuration.flagdesc_core: ~6 rows (approximately)
/*!40000 ALTER TABLE `flagdesc_core` DISABLE KEYS */;
INSERT INTO `flagdesc_core` (`flag`, `name`, `description`) VALUES
	(0, 'Test Flag S1', 'This flag is for testing purposes only&period;'),
	(1, 'Test Flag S2', 'This flag is for testing purposes only&period;'),
	(2, 'Test Flag S3', 'This flag is for testing purposes only&period;'),
	(3, 'Test Flag S4', 'This flag is for testing purposes only&period;'),
	(4, 'Test Flag S5', 'This flag is for testing purposes only&period;'),
	(25, 'Test Flag S6', 'This flag is for testing purposes only&period;'),
	(26, 'Test Flag S7', 'This flag is for testing purposes only&period;');
/*!40000 ALTER TABLE `flagdesc_core` ENABLE KEYS */;

-- Dumping structure for table configuration.modaccess
CREATE TABLE IF NOT EXISTS `modaccess` (
  `moduleid` int(11) NOT NULL COMMENT 'Unique module ID',
  `profileid` int(11) NOT NULL COMMENT 'Unique profile ID',
  PRIMARY KEY (`moduleid`,`profileid`),
  KEY `FK_modaccess_profile` (`profileid`),
  CONSTRAINT `FK_modaccess_configuration.module` FOREIGN KEY (`moduleid`) REFERENCES `module` (`moduleid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_modaccess_profile` FOREIGN KEY (`profileid`) REFERENCES `profile` (`profileid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table determines which modules a particular user profile has access to.  If the tuple is present for the module ID and profile ID combination, then all users on that profile ID have access to that module.';

-- Dumping data for table configuration.modaccess: ~0 rows (approximately)
/*!40000 ALTER TABLE `modaccess` DISABLE KEYS */;
/*!40000 ALTER TABLE `modaccess` ENABLE KEYS */;

-- Dumping structure for table configuration.module
CREATE TABLE IF NOT EXISTS `module` (
  `moduleid` int(11) NOT NULL COMMENT 'Unique module ID',
  `name` varchar(32) NOT NULL COMMENT 'Display name of the module',
  `description` varchar(256) DEFAULT NULL COMMENT 'A description of the module.',
  `filename` varchar(50) NOT NULL COMMENT 'Filename of the module',
  `iconname` varchar(50) NOT NULL COMMENT 'Icon file for the module',
  `active` int(11) NOT NULL DEFAULT '1' COMMENT 'Module activation status.  0: not activated, 1: activated.',
  `allusers` int(11) NOT NULL DEFAULT '0' COMMENT 'When set to 1, all users have access to module.',
  `system` int(11) NOT NULL DEFAULT '0' COMMENT 'Indicates if this is a system module. 0: application module, 1: system module.',
  `vendor` int(11) NOT NULL DEFAULT '0' COMMENT 'Indicates that only the vendor has access to this module.',
  PRIMARY KEY (`moduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This defines all modules that are available in the application.';

-- Dumping data for table configuration.module: ~11 rows (approximately)
/*!40000 ALTER TABLE `module` DISABLE KEYS */;
INSERT INTO `module` (`moduleid`, `name`, `description`, `filename`, `iconname`, `active`, `allusers`, `system`, `vendor`) VALUES
	(1, 'Grid Portal', 'Views the available modules in grid format.', 'gridportal.php', 'icon_grid', 1, 1, 1, 0),
	(2, 'Link Portal', 'Views the available modules in link format.', 'linkportal.php', 'icon_chain2', 1, 1, 1, 0),
	(3, 'Module Data Editor', 'Edits module data in the database.', 'modedit.php', 'icon_gears', 1, 0, 1, 1),
	(4, 'Configuration Editor', 'Edits the application configuration parameters&period;&NewLine;This allows inserting and deleting of configuration&NewLine;items in the database&period;', 'configedit.php', 'icon_tools3', 1, 0, 1, 1),
	(5, 'Parameter Editor', 'Edits the configuration parameter values for the application&period;&NewLine;This only allows changing the configuration parameter values&period;', 'paramedit.php', 'icon_tools4', 1, 0, 1, 0),
	(6, 'System Flags', 'This module edits the system flags which&NewLine;are used by the various user profiles&period;', 'sysflag.php', 'icon_checklist', 1, 0, 1, 1),
	(7, 'Application Flags', 'This module edits the application flags which are used by the various user profiles&period;', 'appflag.php', 'icon_checklist', 1, 0, 1, 1),
	(8, 'File Finder', 'Allows access to the server file system&period;', 'file.php', 'icon_harddisk', 1, 0, 1, 1),
	(10, 'Change Password', 'Allows a user to change their login password&period;', 'passwd.php', 'icon_padlock', 1, 1, 1, 0),
	(11, 'Profile Editor', 'Edits the available profiles that defines&NewLine;what access rights a user has&period;', 'profedit.php', 'icon_keys', 1, 0, 1, 0),
	(12, 'OAuth Provider Edit', 'Edits the known list of external OAuth authentication providers&period;', 'oauthedit.php', 'icon_oauth', 1, 0, 1, 0),
	(13, 'OpenID Provider Edit', 'Edits the known list of external OpenID authentication providers&period;', 'openidedit.php', 'icon_openid', 1, 0, 1, 0),
	(14, 'User Editor', 'Edits the user database', 'useredit.php', 'icon_users2', 1, 0, 1, 0);
/*!40000 ALTER TABLE `module` ENABLE KEYS */;

-- Dumping structure for table configuration.oauth
CREATE TABLE IF NOT EXISTS `oauth` (
  `provider` int(11) NOT NULL COMMENT 'Key Field: The OAuth provider',
  `name` varchar(50) NOT NULL COMMENT 'The name of the OAuth provider.',
  `module` varchar(64) NOT NULL COMMENT 'The OAuth API module in the OAuth directory to use.',
  `expire` bigint(20) NOT NULL COMMENT 'The default expire time if it is not given in the token.',
  `clientid` varchar(32) NOT NULL COMMENT 'The Client ID of this application that is given by the provider.',
  `clientsecret` varchar(64) DEFAULT NULL COMMENT 'The Client Secret that is supplied by the provider.',
  `scope` varchar(256) NOT NULL COMMENT 'The scope of the request.',
  `authtype` int(11) NOT NULL COMMENT 'The type of authorization requested.  This is usually one of the following: 0: authcode; 1: implicit; 2: password; 3: client.',
  `authurl` varchar(512) NOT NULL COMMENT 'The URL that the application will redirect the client to when logging in.',
  `exchangeurl` varchar(512) DEFAULT NULL COMMENT 'The URL that the application communictes with to exchange an authorization code for a token.',
  `redirecturl` varchar(512) NOT NULL COMMENT 'The URL that the provider redirects to when the user logs in and authorizes this application to access their data.',
  `resourceurl1` varchar(512) NOT NULL COMMENT 'The resource URL that the application uses to access the provider''s APIs.',
  `resourceurl2` varchar(512) DEFAULT NULL COMMENT 'The resource URL that the application uses to access the provider\\\\\\\\',
  `resourceurl3` varchar(512) DEFAULT NULL COMMENT 'The resource URL that the application uses to access the provider\\\\\\\\',
  `resourceurl4` varchar(512) DEFAULT NULL COMMENT 'The resource URL that the application uses to access the provider\\\\\\\\',
  `usepkce` int(11) NOT NULL COMMENT 'Indicates that PKCE is in use for this provider.',
  `pkcemethod` int(11) NOT NULL COMMENT 'PKCE hashing method to use:  0: none; 1: SHA256',
  PRIMARY KEY (`provider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contains OAuth information about this client';

-- Dumping data for table configuration.oauth: ~1 rows (approximately)
/*!40000 ALTER TABLE `oauth` DISABLE KEYS */;
INSERT INTO `oauth` (`provider`, `name`, `module`, `expire`, `clientid`, `clientsecret`, `scope`, `authtype`, `authurl`, `exchangeurl`, `redirecturl`, `resourceurl1`, `resourceurl2`, `resourceurl3`, `resourceurl4`, `usepkce`, `pkcemethod`) VALUES
	(0, 'Test Provider', 'generic', 3600, '3hdh2S6FKD8574qjpzx', '', 'real_name,home_address,mailing_address,email,home_phone,mobile_phone,work_phone', 0, 'https://oauth.someprovider.com/oauth/authenticate.php', '', 'https://strata.danielrudy.org/oauth/redir_response.php', 'https://oauth.someprovider.com/oauth/resources.php', 'https://oauth.someprovider.com/oauth/resources.php', 'https://oauth.someprovider.com/oauth/resources.php', 'https://oauth.someprovider.com/oauth/resources.php', 1, 1);
/*!40000 ALTER TABLE `oauth` ENABLE KEYS */;

-- Dumping structure for table configuration.openid
CREATE TABLE IF NOT EXISTS `openid` (
  `provider` int(11) NOT NULL COMMENT 'Key Field: The OpenID provider.',
  `name` varchar(50) NOT NULL COMMENT 'The name of the OpenID Provider.',
  `module` varchar(32) NOT NULL COMMENT 'The OpenID module ID that this provider uses.',
  `expire` bigint(20) NOT NULL COMMENT 'The default expire time of this provider.',
  `serverurl` varchar(512) NOT NULL COMMENT 'The OpenID provider''s server URL to redirect the user to.',
  `redirecturl` varchar(512) NOT NULL COMMENT 'The URL that the provider redirects to when the user logs in and authorizes this application to access their data.',
  PRIMARY KEY (`provider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This hold information on each provider for the OpenID login protocol.';

-- Dumping data for table configuration.openid: ~2 rows (approximately)
/*!40000 ALTER TABLE `openid` DISABLE KEYS */;
INSERT INTO `openid` (`provider`, `name`, `module`, `expire`, `serverurl`, `redirecturl`) VALUES
	(0, 'No Provider', 'XXXX', 0, 'NONE', 'NONE'),
	(1, 'OpenID Test User', 'XXXX', 3600, 'https://openid.someprovider.net/openid/users/authenticate.php', 'https://strata.danielrudy.org/openid/response.php');
/*!40000 ALTER TABLE `openid` ENABLE KEYS */;

-- Dumping structure for table configuration.profile
CREATE TABLE IF NOT EXISTS `profile` (
  `profileid` int(10) NOT NULL COMMENT 'The unique profile ID',
  `name` varchar(32) DEFAULT NULL COMMENT 'The name of the profile.',
  `description` varchar(256) DEFAULT NULL COMMENT 'A brief description of the profile.',
  `portal` int(11) NOT NULL DEFAULT '1' COMMENT 'Determines which portal is the user''s home page. 0: grid, 1: link',
  `bitmap_core` varbinary(16) DEFAULT NULL COMMENT 'Core system capabilities bitmap.',
  `bitmap_app` varbinary(16) DEFAULT NULL COMMENT 'Application capabilities bitmap.',
  PRIMARY KEY (`profileid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='All user accounts must be assigned one of the profiles which are defined in this table.';

-- Dumping data for table configuration.profile: ~3 rows (approximately)
/*!40000 ALTER TABLE `profile` DISABLE KEYS */;
INSERT INTO `profile` (`profileid`, `name`, `description`, `portal`, `bitmap_core`, `bitmap_app`) VALUES
	(0, 'NONE', NULL, 1, NULL, NULL),
	(1, 'Vendor', 'Profile for use only by the application vendor.', 0, _binary 0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF, _binary 0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF),
	(2, 'Admin', 'Profile for use only by the application admin.', 0, _binary 0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF, _binary 0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF),
	(435, 'Test Profile', 'This profile is for development testing purposes&period;', 1, _binary 0x02000006000000000000000000000000, _binary 0x00010000000000000000000000000000);
/*!40000 ALTER TABLE `profile` ENABLE KEYS */;


-- Dumping database structure for userdata
CREATE DATABASE IF NOT EXISTS `userdata` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `userdata`;

-- Dumping structure for table userdata.contact
CREATE TABLE IF NOT EXISTS `contact` (
  `userid` int(11) NOT NULL COMMENT 'The numeric user ID from the users table.',
  `name` varchar(50) DEFAULT NULL COMMENT 'The name of the user.',
  `haddr` varchar(100) DEFAULT NULL COMMENT 'The user''s home address.',
  `maddr` varchar(100) DEFAULT NULL COMMENT 'The user''s mailing address.',
  `email` varchar(50) DEFAULT NULL COMMENT 'The user''s email address.',
  `hphone` varchar(30) DEFAULT NULL COMMENT 'Home phone number',
  `cphone` varchar(30) DEFAULT NULL COMMENT 'Mobile phone number',
  `wphone` varchar(30) DEFAULT NULL COMMENT 'Work phone number',
  PRIMARY KEY (`userid`),
  CONSTRAINT `FK_contact_userid_users_userid` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table contains the user contact information.';

-- Dumping data for table userdata.contact: ~3 rows (approximately)
/*!40000 ALTER TABLE `contact` DISABLE KEYS */;
INSERT INTO `contact` (`userid`, `name`, `haddr`, `maddr`, `email`, `hphone`, `cphone`, `wphone`) VALUES
	(1, 'Application Vendor', 'SEA-CORE International LTD.', NULL, 'seacoregroup@gmail.com', '', '', ''),
	(2, 'Application Admin', 'SEA-CORE International LTD&period;', 'SEA-CORE International LTD&period;', 'seacoregroup&commat;gmail&period;com', '', '', ''),
	(34, 'Test User', 'Test user for developmental purposes only&period;', 'Test user for developmental purposes only&period;', 'testuser&commat;localhost&period;com', '707-555-3343', '916-555-1212', '916-278-6000'),
	(324, 'Test User Two', '', '', '', '', '', '');
/*!40000 ALTER TABLE `contact` ENABLE KEYS */;

-- Dumping structure for table userdata.login
CREATE TABLE IF NOT EXISTS `login` (
  `userid` int(11) NOT NULL COMMENT 'The numeric user ID.',
  `locked` int(11) DEFAULT NULL COMMENT 'Indicates if the account has been locked out.  0: not locked out, 1: locked out',
  `locktime` int(10) DEFAULT NULL COMMENT 'Time that the account will be locked out after the maximum number of login failures has been exceeded.',
  `lastlog` bigint(20) NOT NULL COMMENT 'Time and date of the user''s last successful login.',
  `failcount` int(11) unsigned NOT NULL COMMENT 'Number of login failures since last successful login.',
  `timeout` bigint(20) NOT NULL COMMENT 'Shows the timeout when the user password expires and must be changed.',
  `digest` varchar(16) DEFAULT NULL COMMENT 'Message digest algoritm to use when encrypting the password.',
  `count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Number of times to hash password+salt.',
  `salt` varchar(256) DEFAULT NULL COMMENT 'The salt to use when encrypting the password.',
  `passwd` varchar(256) DEFAULT NULL COMMENT 'The encrypted password.',
  PRIMARY KEY (`userid`),
  CONSTRAINT `FK_login_userid_users_userid` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table contains the user''s login data.';

-- Dumping data for table userdata.login: ~4 rows (approximately)
/*!40000 ALTER TABLE `login` DISABLE KEYS */;
INSERT INTO `login` (`userid`, `locked`, `locktime`, `lastlog`, `failcount`, `timeout`, `digest`, `count`, `salt`, `passwd`) VALUES
	(1, 0, 1532714506, 1537516014, 0, -1, 'SHA256', 100, 'db63f993e8a1b7de7926ac01b89b743b11ad0674b850cae778ff36684e0d1bc3', 'a0f843907bc5276d68afa04b24935f8f33a7f1c3ec7344d1851ba02f11d2cb13'),
	(2, NULL, 0, 1536731231, 0, -1, 'SHA256', 100, '265273ef2fabd48ffbd3a8218a09af8b7a1187b53acfa51626ddb63d0cf4dc8c', 'ea008ac085fe70a84e8a183ca8d3d943a74f3dff278a0768b5ebcc167abdce73'),
	(34, 0, 1532817751, 1537509577, 0, 1545285599, 'SHA256', 100, 'de8392b2e9263d1e9bee3a93db10da0bf7ce5e4c8c365a9b6acd3c0c02de5f33', '36c24f59957f3a980a837b6daea6522677454bd06e90b20cc51ce93155eba347'),
	(324, 0, 0, 0, 0, 0, 'SHA256', 100, '59590d7e1f679dde2bc7a373ba5f25f60bf4060540fb50a2841b98a322cd1f71', 'e5bc3e1b91b086fda581afb04f16d32cbd89797adebd2b4b118cbff86675f2e0');
/*!40000 ALTER TABLE `login` ENABLE KEYS */;

-- Dumping structure for table userdata.oauth
CREATE TABLE IF NOT EXISTS `oauth` (
  `userid` int(11) NOT NULL COMMENT 'The numerical user ID from the users table.',
  `provider` int(11) NOT NULL COMMENT 'The OAuth provider identifier.',
  `state` varchar(16) NOT NULL COMMENT 'Random set of bytes for the connection.',
  `token` varchar(64) NOT NULL COMMENT 'The OAuth token.',
  `tokentype` varchar(32) NOT NULL COMMENT 'The OAuth token type.',
  `issue` bigint(20) NOT NULL COMMENT 'The time that the OAuth token was issued.',
  `expire` bigint(20) NOT NULL COMMENT 'The time that the OAuth token will expire.',
  `refresh` varchar(64) NOT NULL,
  `scope` varchar(256) NOT NULL COMMENT 'The allowed access scope that the user permits to their information.',
  `challenge` varchar(64) DEFAULT NULL COMMENT 'Plain text of the challenge string.',
  PRIMARY KEY (`userid`),
  KEY `state` (`state`),
  KEY `FK_oauth_provider_configuration.oauth_provider` (`provider`),
  CONSTRAINT `FK_oauth_provider_configuration.oauth_provider` FOREIGN KEY (`provider`) REFERENCES `configuration`.`oauth` (`provider`) ON UPDATE CASCADE,
  CONSTRAINT `FK_oauth_userid_users_userid` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contains per-user OAuth data.';

-- Dumping data for table userdata.oauth: ~0 rows (approximately)
/*!40000 ALTER TABLE `oauth` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth` ENABLE KEYS */;

-- Dumping structure for table userdata.openid
CREATE TABLE IF NOT EXISTS `openid` (
  `userid` int(11) NOT NULL COMMENT 'Key Field: The user''s ID number.',
  `provider` int(11) NOT NULL COMMENT 'The OpenID provider identification key.',
  `ident` varchar(512) NOT NULL COMMENT 'The user''s OpenID ID.',
  `issue` bigint(20) NOT NULL COMMENT 'Time/Date that the authentication was issued.',
  `expire` bigint(20) NOT NULL COMMENT 'Time/Date that the authentication is to expire.',
  PRIMARY KEY (`userid`),
  KEY `FK_openid_provider_configuration.openid_provider` (`provider`),
  CONSTRAINT `FK_openid_provider_configuration.openid_provider` FOREIGN KEY (`provider`) REFERENCES `configuration`.`openid` (`provider`) ON UPDATE CASCADE,
  CONSTRAINT `FK_openid_userid_users_userid` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Holds OpenID information for the user.';

-- Dumping data for table userdata.openid: ~0 rows (approximately)
/*!40000 ALTER TABLE `openid` DISABLE KEYS */;
/*!40000 ALTER TABLE `openid` ENABLE KEYS */;

-- Dumping structure for table userdata.users
CREATE TABLE IF NOT EXISTS `users` (
  `userid` int(11) NOT NULL COMMENT 'The numeric user ID.',
  `username` varchar(32) NOT NULL COMMENT 'The name that the user logs in with.',
  `profileid` int(11) NOT NULL DEFAULT '0' COMMENT 'The numeric profile ID.',
  `method` int(11) NOT NULL DEFAULT '0' COMMENT 'The login method.  0: native, 1: OAuth, 2: OpenID',
  `active` int(11) NOT NULL DEFAULT '1' COMMENT 'Indicates if the account is active or not.  0: not active, 1: active.',
  `orgid` varchar(32) DEFAULT NULL COMMENT 'The organizational ID of the user.',
  PRIMARY KEY (`userid`),
  UNIQUE KEY `UNIQUE` (`userid`,`username`),
  KEY `username` (`username`),
  KEY `FK_users_login` (`userid`),
  KEY `FK_users_profile` (`profileid`),
  CONSTRAINT `FK_users_profile` FOREIGN KEY (`profileid`) REFERENCES `configuration`.`profile` (`profileid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table maps the user''s login name to their User ID and Profile ID.';

-- Dumping data for table userdata.users: ~4 rows (approximately)
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`userid`, `username`, `profileid`, `method`, `active`, `orgid`) VALUES
	(1, 'vendor', 1, 0, 1, NULL),
	(2, 'admin', 2, 0, 1, NULL),
	(34, 'testuser', 435, 0, 1, 'TestUserDeveloper3854755'),
	(324, 'test2', 435, 0, 1, NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
