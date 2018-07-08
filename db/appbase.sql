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

-- Dumping data for table configuration.config: ~38 rows (approximately)
/*!40000 ALTER TABLE `config` DISABLE KEYS */;
INSERT INTO `config` (`setting`, `type`, `name`, `dispname`, `value`, `description`, `admin`) VALUES
	(0, 0, 'server_document_root', 'Appliation Root Directory', '/Servers/webdocs', 'This sets the application root directory on the server.', 1),
	(1, 0, 'server_hostname', 'Server Hostname', 'localhost', 'This is the server hostname.  It is used to build the base URL which is used throughout the application.', 1),
	(2, 2, 'server_secure', 'Use HTTPS', '0', 'The flag which indicates that the encrypted HTTPS protocol is to be used.', 1),
	(3, 1, 'server_http_port', 'HTTP Port Number', '22080', 'The network port number that the application is to use when using the unencrypted HTTP protocol. Default is 80.', 1),
	(4, 1, 'server_https_port', 'HTTPS Port Number', '443', 'The network port number that the application is to use when using the encrypted HTTPS protocol. Default is 443.', 1),
	(10, 1, 'html_default_label_size', 'Default HTML Label Size', '2', 'Default size of field text labels on web forms.', 0),
	(11, 1, 'html_default_field_size', 'Default HTML Field Size', '4', 'Default size of fields on web forms.', 0),
	(12, 0, 'html_login_page', 'Application Login Page', 'login.php', 'The main login page of the application.  All unauthenticated users are redirected here.', 0),
	(13, 0, 'html_banner_page', 'Application Banner Page', 'banner.php', 'The page that is displayed right after the user is authenticated on the main login page.', 0),
	(14, 0, 'html_gridportal_page', 'Application Grid Portal Page', 'gridportal.php', 'The grid portal page.  This shows the available modules in grid format.', 0),
	(15, 0, 'html_linkportal_page', 'Application Link Portal Page', 'linkportal.php', 'The link portal page.  This shows the available modules in link format.', 0),
	(20, 0, 'openssl_digests', 'OpenSSL Digests', 'SHA256 SHA1 RIPEMD160 MD5', 'A list of acceptable message digest algorithms which are used for password hashing.', 1),
	(25, 0, 'login_banner_title', 'Login Banner Title', '! ! ! ! WARNING ! ! ! !\r\n', 'The banner title that is displayed when the user logs into the system.', 1),
	(26, 0, 'login_banner_subtitle', 'Login Banner Subtitle', 'THIS IS A PRIVATE COMPUTER SYSTEM<br>\r\nUNAUTHORIZED ACCESS IS STRICTLY PROHIBITED\r\n', 'The banner subtitle that is displayed when the user logs into the system.', 1),
	(27, 0, 'login_banner_message', 'Login Banner Message', 'Any unauthorized use or access or any attempted<br>\r\nunauthorized use or access of this computer system<br>\r\nis a violation of state and federal law and will be<br>\r\ninvestigated and procecuted under the maximum extent<br>\r\nprovided by said law.  This system is subject to<br>\r\nmonitoring and audit.  By continuing to use this system<br>\r\nyou expressly agree to such monitoring.<br>\r\n', 'The banner message that is displayed when the user logs into the system.', 1),
	(30, 1, 'security_username_minlen', 'Username Minimum Length', '3', 'Minimum acceptable length of usernames.', 1),
	(31, 1, 'security_username_maxlen', 'Username Maximum Length', '32', 'Maximum acceptable length of usernames.', 1),
	(32, 1, 'security_passwd_minlen', 'Password Minimum Length', '8', 'Minimum acceptable length of passwords.', 1),
	(33, 1, 'security_passwd_maxlen', 'Password Maximum Length', '128', 'Maximum acceptable length of passwords.', 1),
	(34, 1, 'security_passexp_timeout', 'Password Expire Timeout', '7776000', 'Maximum allowed time between password changes. When this is exceeded, the user will be forced to change their password.', 1),
	(35, 1, 'security_passwd_complex_level', 'Password Complexity Level', '0', 'The complexity of the user password. 0: None, 1: Letters and numbers, 2: Upper and lower case letter and numbers, 3: Same as 2 plus symbols.', 1),
	(36, 1, 'security_salt_len', 'Password Salt Length', '32', 'The length of the password salt in bytes.', 1),
	(37, 1, 'security_hash_rounds', 'Password Hash Rounds', '100', 'Number of times to hash password + salt for password encryption.', 1),
	(38, 1, 'security_hashtime_min', 'Password Hashtime Minimum', '10', 'Minimum jitter time for password hashing.', 0),
	(39, 1, 'security_hashtime_max', 'Password Hashtime Maximum', '500', 'Maximum jitter time for password hashing.', 0),
	(40, 1, 'security_login_failure_lockout', 'Allowed Login Failure Attempts', '5', 'The maximum number of login failure attempts before the account is locked out.', 1),
	(41, 1, 'security_lockout_time', 'Account Lockout Time', '900', 'The amount of time the account is locked out after the number of login failure attempts have been exceeded.', 1),
	(50, 1, 'session_regen_time', 'Session Regenerate Timeout', '30', 'The time interval between session ID regeneration. This helps to prevent session fixation and session hijacking.', 1),
	(51, 1, 'session_expire_time', 'Session Expire Timeout', '60', 'The time in seconds that the old session before ID regeneration is still valid.', 1),
	(52, 1, 'session_nonce_len', 'Session Nonce Length', '32', 'The length of the session nonce in bytes.', 0),
	(53, 1, 'session_cookie_expire_time', 'Session Cookie Timeout', '600', 'The time in seconds before the client cookie expires on a per page basis.', 1),
	(60, 10, 'timezone_default', 'Time Displacement From UTC', '-8:00', 'This sets the timezone displacement from UTC.', 1),
	(70, 1, 'account_id_vendor', 'Vendor Account ID', '-1', 'The account ID number of the vendor account.', 0),
	(71, 1, 'account_id_admin', 'Admin Account ID', '-2', 'The account ID number of the administrator account.', 0),
	(72, 1, 'profile_id_vendor', 'Vendor Profile ID', '-1', 'The profile ID number of the vendor account.', 0),
	(73, 1, 'profile_id_admin', 'Admin Profile ID', '-2', 'The profile ID number of the administrator account.', 0),
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

-- Dumping data for table configuration.flagdesc_app: ~0 rows (approximately)
/*!40000 ALTER TABLE `flagdesc_app` DISABLE KEYS */;
/*!40000 ALTER TABLE `flagdesc_app` ENABLE KEYS */;

-- Dumping structure for table configuration.flagdesc_core
CREATE TABLE IF NOT EXISTS `flagdesc_core` (
  `flag` int(10) unsigned NOT NULL COMMENT 'Unique flag identifier',
  `name` varchar(32) DEFAULT NULL COMMENT 'Display name of flag',
  `description` varchar(256) DEFAULT NULL COMMENT 'Description of the flag',
  PRIMARY KEY (`flag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table defines the names and descriptions of core system flags from the userdata.profile table.  The flag attribute is the bit position of the flag.';

-- Dumping data for table configuration.flagdesc_core: ~0 rows (approximately)
/*!40000 ALTER TABLE `flagdesc_core` DISABLE KEYS */;
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

-- Dumping data for table configuration.module: ~0 rows (approximately)
/*!40000 ALTER TABLE `module` DISABLE KEYS */;
/*!40000 ALTER TABLE `module` ENABLE KEYS */;

-- Dumping structure for table configuration.oauth
CREATE TABLE IF NOT EXISTS `oauth` (
  `provider` varchar(32) NOT NULL COMMENT 'Key Field: The OAuth provider',
  `module` varchar(32) NOT NULL COMMENT 'The OAuth API module in the OAuth directory to use.',
  `expire` bigint(20) unsigned NOT NULL COMMENT 'The default expire time if it is not given in the token.',
  `clientid` varchar(32) NOT NULL COMMENT 'The Client ID of this application that is given by the provider.',
  `clientsecret` varchar(64) DEFAULT NULL COMMENT 'The Client Secret that is supplied by the provider.',
  `scope` varchar(256) NOT NULL COMMENT 'The scope of the request.',
  `authtype` varchar(16) NOT NULL COMMENT 'The type of authorization requested.',
  `authurl` varchar(512) NOT NULL COMMENT 'The URL that the application will redirect the client to when logging in.',
  `redirecturl` varchar(512) NOT NULL COMMENT 'The URL that the provider redirects to when the user logs in and authorizes this application to access their data.',
  `resourceurl1` varchar(512) NOT NULL COMMENT 'The resource URL that the application uses to access the provider''s APIs.',
  `resourceurl2` varchar(512) DEFAULT NULL COMMENT 'The resource URL that the application uses to access the provider\\\\\\\\',
  `resourceurl3` varchar(512) DEFAULT NULL COMMENT 'The resource URL that the application uses to access the provider\\\\\\\\',
  `resourceurl4` varchar(512) DEFAULT NULL COMMENT 'The resource URL that the application uses to access the provider\\\\\\\\',
  PRIMARY KEY (`provider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contains OAuth information about this client';

-- Dumping data for table configuration.oauth: ~0 rows (approximately)
/*!40000 ALTER TABLE `oauth` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth` ENABLE KEYS */;

-- Dumping structure for table configuration.openid
CREATE TABLE IF NOT EXISTS `openid` (
  `provider` varchar(32) NOT NULL COMMENT 'Key Field: The OpenID provider.',
  PRIMARY KEY (`provider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This hold information on each provider for the OpenID login protocol.';

-- Dumping data for table configuration.openid: ~0 rows (approximately)
/*!40000 ALTER TABLE `openid` DISABLE KEYS */;
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

-- Dumping data for table configuration.profile: ~4 rows (approximately)
/*!40000 ALTER TABLE `profile` DISABLE KEYS */;
INSERT INTO `profile` (`profileid`, `name`, `description`, `portal`, `bitmap_core`, `bitmap_app`) VALUES
	(-2, 'Admin', 'Profile for use only by the application admin.', 0, _binary 0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF, _binary 0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF),
	(-1, 'Vendor', 'Profile for use only by the application vendor.', 0, _binary 0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF, _binary 0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF),
	(0, 'NONE', NULL, 1, NULL, NULL),
	(1, 'manager', NULL, 1, NULL, NULL);
/*!40000 ALTER TABLE `profile` ENABLE KEYS */;


-- Dumping database structure for userdata
CREATE DATABASE IF NOT EXISTS `userdata` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `userdata`;

-- Dumping structure for table userdata.contact
CREATE TABLE IF NOT EXISTS `contact` (
  `userid` int(11) NOT NULL COMMENT 'The numeric user ID from the users table.',
  `method` int(11) NOT NULL COMMENT 'The type of user ID (0: native, 1: OAuth, 2: OpenID)',
  `name` varchar(50) DEFAULT NULL COMMENT 'The name of the user.',
  `address` varchar(100) DEFAULT NULL COMMENT 'The user''s home address.',
  `email` varchar(50) DEFAULT NULL COMMENT 'The user''s email address.',
  `hphone` varchar(30) DEFAULT NULL COMMENT 'Home phone number',
  `cphone` varchar(30) DEFAULT NULL COMMENT 'Mobile phone number',
  `wphone` varchar(30) DEFAULT NULL COMMENT 'Work phone number',
  PRIMARY KEY (`userid`,`method`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table contains the user contact information.';

-- Dumping data for table userdata.contact: ~2 rows (approximately)
/*!40000 ALTER TABLE `contact` DISABLE KEYS */;
INSERT INTO `contact` (`userid`, `method`, `name`, `address`, `email`, `hphone`, `cphone`, `wphone`) VALUES
	(-2, 0, 'Application Admin', 'SEA-CORE International LTD.', 'seacoregroup@gmail.com', '', '', ''),
	(-1, 0, 'Application Vendor', 'SEA-CORE International LTD.', 'seacoregroup@gmail.com', '', '', '');
/*!40000 ALTER TABLE `contact` ENABLE KEYS */;

-- Dumping structure for table userdata.login
CREATE TABLE IF NOT EXISTS `login` (
  `userid` int(11) NOT NULL COMMENT 'The numeric user ID.',
  `active` int(11) DEFAULT NULL COMMENT 'Indicates if the account is active or not.  0: not active, 1: active.',
  `locked` int(11) DEFAULT NULL COMMENT 'Indicates if the account has been locked out.  0: not locked out, 1: locked out',
  `locktime` int(10) DEFAULT NULL COMMENT 'Time that the account will be locked out after the maximum number of login failures has been exceeded.',
  `lastlog` bigint(20) NOT NULL COMMENT 'Time and date of the user''s last successful login.',
  `failcount` int(11) unsigned NOT NULL COMMENT 'Number of login failures since last successful login.',
  `timeout` bigint(20) NOT NULL COMMENT 'Shows the timeout when the user password expires and must be changed.',
  `digest` varchar(16) DEFAULT NULL COMMENT 'Message digest algoritm to use when encrypting the password.',
  `count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Number of times to hash password+salt.',
  `salt` varchar(256) DEFAULT NULL COMMENT 'The salt to use when encrypting the password.',
  `passwd` varchar(256) DEFAULT NULL COMMENT 'The encrypted password.',
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table contains the user''s login data.';

-- Dumping data for table userdata.login: ~2 rows (approximately)
/*!40000 ALTER TABLE `login` DISABLE KEYS */;
INSERT INTO `login` (`userid`, `active`, `locked`, `locktime`, `lastlog`, `failcount`, `timeout`, `digest`, `count`, `salt`, `passwd`) VALUES
	(-2, 1, 0, 0, -1, 0, -1, 'SHA256', 100, 'fa5e2b860bf98843ee48d897b75eab462a678614c0e5a504f384d745405dff09', 'cd9ce68a11ecfe09b55edcc1a34c9ac580ba97c10a2f13f4c57717b54dcd86a5'),
	(-1, 1, 0, 1530778102, 1530977988, 0, -1, 'SHA256', 100, 'e85fd8fd956ca25b28488c20969a87ec87b851a1c8e40634577e5570d31dc7e8', '89b8ab210266813d474d8920651627ad37765ea0199aeea175c2d13bcb029311');
/*!40000 ALTER TABLE `login` ENABLE KEYS */;

-- Dumping structure for table userdata.oauth
CREATE TABLE IF NOT EXISTS `oauth` (
  `userid` int(11) NOT NULL COMMENT 'The numerical user ID from the users table.',
  `state` varchar(16) NOT NULL COMMENT 'Random set of bytes for the connection.',
  `provider` varchar(32) NOT NULL COMMENT 'The OAuth provider identifier.',
  `token` varchar(32) NOT NULL COMMENT 'The OAuth token.',
  `tokentype` varchar(32) NOT NULL COMMENT 'The OAuth token type.',
  `issue` bigint(20) NOT NULL COMMENT 'The time that the OAuth token was issued.',
  `expire` bigint(20) NOT NULL COMMENT 'The time that the OAuth token will expire.',
  `refresh` varchar(50) NOT NULL,
  `scope` varchar(256) NOT NULL COMMENT 'The allowed access scope that the user permits to their information.',
  PRIMARY KEY (`userid`),
  KEY `FK_oauth_configuration.oauth` (`provider`),
  KEY `state` (`state`),
  CONSTRAINT `FK_oauth_configuration.oauth` FOREIGN KEY (`provider`) REFERENCES `configuration`.`oauth` (`provider`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contains per-user OAuth data.';

-- Dumping data for table userdata.oauth: ~0 rows (approximately)
/*!40000 ALTER TABLE `oauth` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth` ENABLE KEYS */;

-- Dumping structure for table userdata.openid
CREATE TABLE IF NOT EXISTS `openid` (
  `userid` int(11) NOT NULL,
  `provider` varchar(32) NOT NULL,
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Holds OpenID information for the user.';

-- Dumping data for table userdata.openid: ~0 rows (approximately)
/*!40000 ALTER TABLE `openid` DISABLE KEYS */;
/*!40000 ALTER TABLE `openid` ENABLE KEYS */;

-- Dumping structure for table userdata.users
CREATE TABLE IF NOT EXISTS `users` (
  `username` varchar(32) NOT NULL COMMENT 'The name that the user logs in with.',
  `userid` int(11) NOT NULL DEFAULT '0' COMMENT 'The numeric user ID.',
  `profileid` int(11) NOT NULL COMMENT 'The numeric profile ID.',
  `method` int(11) NOT NULL DEFAULT '0' COMMENT 'The login method.  0: native, 1: OAuth, 2: OpenID',
  PRIMARY KEY (`username`),
  KEY `username` (`username`),
  KEY `FK_users_login` (`userid`),
  KEY `FK_users_profile` (`profileid`),
  CONSTRAINT `FK_users_profile` FOREIGN KEY (`profileid`) REFERENCES `configuration`.`profile` (`profileid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table maps the user''s login name to their User ID and Profile ID.';

-- Dumping data for table userdata.users: ~2 rows (approximately)
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`username`, `userid`, `profileid`, `method`) VALUES
	('admin', -2, -2, 0),
	('vendor', -1, -1, 0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
