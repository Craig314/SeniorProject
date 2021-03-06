-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.7.22-log - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             9.5.0.5196
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for application
CREATE DATABASE IF NOT EXISTS `application` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `application`;

-- Dumping structure for table application.assignment
CREATE TABLE IF NOT EXISTS `assignment` (
  `assignment` int(11) NOT NULL AUTO_INCREMENT COMMENT 'The assignment number for the course.',
  `courseid` int(11) NOT NULL COMMENT 'The unique course ID number.',
  `name` varchar(30) NOT NULL COMMENT 'The name of the assignment.',
  `description` varchar(512) DEFAULT NULL COMMENT 'A type in description for the assignment.',
  `descfile` varchar(256) DEFAULT NULL COMMENT 'A description file for the assignment that the ',
  `allowext` varchar(128) DEFAULT NULL COMMENT 'A list of allowed extensions for student submissions.',
  `duedate` bigint(20) NOT NULL COMMENT 'Assignment due date.',
  `lockdate` bigint(20) DEFAULT NULL COMMENT 'The date where students can no longer submit assignments.',
  `gradeweight` int(11) DEFAULT NULL COMMENT 'The total grade weight of the assignment.',
  `gwgroup` int(11) DEFAULT '0' COMMENT 'The grade weight group if the course has one.',
  `curve` int(11) NOT NULL DEFAULT '0' COMMENT 'Any curve that the instructor sets for the assignment.',
  `points` int(11) NOT NULL DEFAULT '0' COMMENT 'The numbere of points the assignment is worth.',
  `exempt` int(11) NOT NULL DEFAULT '0' COMMENT 'Indicates if this assignment is exempt from the total grade.',
  `maxturnin` int(11) NOT NULL DEFAULT '3' COMMENT 'The maximum number of submissions that a student can make.',
  PRIMARY KEY (`assignment`),
  KEY `assignment` (`assignment`),
  KEY `FK1_asssignment_courseid_course_courseid` (`courseid`),
  KEY `FK2_assignment_gwgroup_weightgroup_group` (`gwgroup`),
  KEY `duedate` (`duedate`),
  CONSTRAINT `FK1_asssignment_courseid_course_courseid` FOREIGN KEY (`courseid`) REFERENCES `course` (`courseid`) ON UPDATE CASCADE,
  CONSTRAINT `FK2_assignment_gwgroup_weightgroup_group` FOREIGN KEY (`gwgroup`) REFERENCES `weightgroup` (`group`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='The assignments that the instructor assigns to students.';

-- Dumping data for table application.assignment: ~11 rows (approximately)
/*!40000 ALTER TABLE `assignment` DISABLE KEYS */;
INSERT INTO `assignment` (`assignment`, `courseid`, `name`, `description`, `descfile`, `allowext`, `duedate`, `lockdate`, `gradeweight`, `gwgroup`, `curve`, `points`, `exempt`, `maxturnin`) VALUES
	(1, 20284, 'Assignment 1', 'Assignment 1', NULL, NULL, 1543910399, NULL, 10, 0, 0, 10, 0, 3),
	(2, 14298, 'Assignment 1', 'Assignment 1', NULL, NULL, 1544169599, NULL, 10, 0, 0, 10, 0, 3),
	(3, 21924, 'Assignment 1', 'Assignment 1', NULL, NULL, 1545379199, NULL, 10, 0, 0, 10, 0, 3),
	(4, 21924, 'Assignment 2', 'Assignment 2', NULL, NULL, 1544083199, NULL, 10, 0, 0, 10, 0, 3),
	(5, 14298, 'Assignment 2', 'Assignment 2', NULL, NULL, 1545379199, NULL, 10, 0, 0, 10, 0, 3),
	(6, 20284, 'Assignment 3', 'Assignment 3', NULL, NULL, 1544234400, NULL, 10, 0, 0, 10, 0, 3),
	(7, 14298, 'Assignment 3', 'Assignment 3', NULL, NULL, 1544234400, NULL, 10, 0, 0, 10, 0, 3),
	(8, 14298, 'Assignment 4', 'Assignment 4', NULL, NULL, 1544342399, NULL, 10, 0, 0, 10, 0, 3),
	(9, 14298, 'Assignment 5', 'Assignment 5', NULL, NULL, 1544428799, NULL, 10, 0, 0, 10, 0, 3),
	(10, 14298, 'Assignment 6', 'Assignment 6', NULL, NULL, 1544687999, NULL, 10, 0, 0, 10, 0, 3),
	(11, 21924, 'Assignment 3', 'Assignment 3', NULL, NULL, 1544947199, NULL, 10, 0, 0, 10, 0, 3);
/*!40000 ALTER TABLE `assignment` ENABLE KEYS */;

-- Dumping structure for table application.assignstep
CREATE TABLE IF NOT EXISTS `assignstep` (
  `assignment` int(11) NOT NULL COMMENT 'The assignment number.',
  `step` int(11) NOT NULL COMMENT 'The step number.',
  `date` bigint(20) NOT NULL COMMENT 'The date the step should be completed.',
  `description` varchar(512) NOT NULL COMMENT 'A description of the step.',
  `turninreq` int(11) NOT NULL DEFAULT '1' COMMENT 'Indicates if a turnin is required for this step.',
  `maxturnin` int(11) NOT NULL DEFAULT '3' COMMENT 'The maximum allowed submissions that a student can make.',
  PRIMARY KEY (`assignment`,`step`),
  KEY `step` (`step`),
  CONSTRAINT `FK1_assignstep_assignment_assignment_assignment` FOREIGN KEY (`assignment`) REFERENCES `assignment` (`assignment`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Steps or milestones the assignment requires for completetion.';

-- Dumping data for table application.assignstep: ~4 rows (approximately)
/*!40000 ALTER TABLE `assignstep` DISABLE KEYS */;
INSERT INTO `assignstep` (`assignment`, `step`, `date`, `description`, `turninreq`, `maxturnin`) VALUES
	(5, 1, 1541108753, 'Assignment 2 Step 1', 0, 3),
	(5, 2, 1541195453, 'Assignment 2 Step 2', 0, 3),
	(5, 3, 1541282153, 'Assignment 2 Step 3', 1, 3),
	(5, 4, 1541368853, 'Assignment 2 Step 4', 0, 3);
/*!40000 ALTER TABLE `assignstep` ENABLE KEYS */;

-- Dumping structure for table application.course
CREATE TABLE IF NOT EXISTS `course` (
  `courseid` int(11) NOT NULL COMMENT 'The unique ID number of the course.',
  `class` varchar(12) NOT NULL COMMENT 'The course code.  Ex: CSC 190',
  `section` int(11) NOT NULL COMMENT 'The course section number.',
  `name` varchar(50) NOT NULL COMMENT 'The name of the course.',
  `syllabus` varchar(256) DEFAULT NULL COMMENT 'The name of the course syllabus file.',
  `instructor` int(11) NOT NULL COMMENT 'The userid of the course instructor.',
  `scale` int(11) DEFAULT NULL COMMENT 'The grading scale for this course.',
  `curve` int(11) DEFAULT NULL COMMENT 'The grading curve for this course.',
  PRIMARY KEY (`courseid`),
  UNIQUE KEY `callid` (`courseid`),
  KEY `instructor` (`instructor`),
  KEY `FK2_course_scale_gradescale_scale` (`scale`),
  CONSTRAINT `FK1_course_instructor_users_userid` FOREIGN KEY (`instructor`) REFERENCES `userdata`.`users` (`userid`) ON UPDATE CASCADE,
  CONSTRAINT `FK2_course_scale_gradescale_scale` FOREIGN KEY (`scale`) REFERENCES `gradescale` (`scale`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table defines the course that a student takes.';

-- Dumping data for table application.course: ~7 rows (approximately)
/*!40000 ALTER TABLE `course` DISABLE KEYS */;
INSERT INTO `course` (`courseid`, `class`, `section`, `name`, `syllabus`, `instructor`, `scale`, `curve`) VALUES
	(14298, 'CSC-152', 1, 'Cryptography', '', 1000, 0, NULL),
	(19376, 'CSC-151', 1, 'Compiler Construction', '', 1001, 0, NULL),
	(20284, 'CSC-152', 2, 'Cryptography', '', 1000, 0, 0),
	(21474, 'PHIL-103', 1, 'Ethics for Business and Computer Science', '', 1001, 0, 0),
	(21924, 'CSC-130', 1, 'Data Structures and Algorithms', '', 1000, 0, NULL),
	(21925, 'CSC-130', 2, 'Data Structures and Algorithms', '', 1000, 0, NULL),
	(83745, 'CSC-131', 1, 'Software Engineering', '', 1000, 0, NULL),
	(83746, 'CSC-131', 2, 'Software Engineering', '', 1001, 0, NULL);
/*!40000 ALTER TABLE `course` ENABLE KEYS */;

-- Dumping structure for table application.filename
CREATE TABLE IF NOT EXISTS `filename` (
  `studentid` int(11) NOT NULL COMMENT 'The student''s user ID.',
  `assignment` int(11) NOT NULL COMMENT 'The assignment this file belongs to.',
  `step` int(11) NOT NULL COMMENT 'The assignment step this file belongs to.',
  `subcount` int(11) NOT NULL COMMENT 'The assignment turnin number this file belongs to.',
  `filenumber` int(11) NOT NULL COMMENT 'The filenumber the file is.',
  `studentfile` varchar(256) NOT NULL COMMENT 'The filename that the student submitted.',
  `sysfile` varchar(256) NOT NULL COMMENT 'The system assigned filename.',
  PRIMARY KEY (`studentid`,`assignment`,`step`,`subcount`,`filenumber`),
  KEY `FK2_filename_assignment_assignment_assignment` (`assignment`),
  KEY `FK3_filename_step_assignstep_step` (`step`),
  KEY `FK4_filename_subcount_turnin_subcount` (`subcount`),
  CONSTRAINT `FK1_filename_studentid_users_userid` FOREIGN KEY (`studentid`) REFERENCES `userdata`.`users` (`userid`) ON UPDATE CASCADE,
  CONSTRAINT `FK2_filename_assignment_assignment_assignment` FOREIGN KEY (`assignment`) REFERENCES `assignment` (`assignment`) ON UPDATE CASCADE,
  CONSTRAINT `FK3_filename_step_assignstep_step` FOREIGN KEY (`step`) REFERENCES `assignstep` (`step`) ON UPDATE CASCADE,
  CONSTRAINT `FK4_filename_subcount_turnin_subcount` FOREIGN KEY (`subcount`) REFERENCES `turnin` (`subcount`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contains the filenames of files that the student submits.';

-- Dumping data for table application.filename: ~0 rows (approximately)
/*!40000 ALTER TABLE `filename` DISABLE KEYS */;
/*!40000 ALTER TABLE `filename` ENABLE KEYS */;

-- Dumping structure for table application.grades
CREATE TABLE IF NOT EXISTS `grades` (
  `studentid` int(11) NOT NULL COMMENT 'The student''s userid.',
  `assignment` int(11) NOT NULL COMMENT 'The assignment.',
  `course` int(11) NOT NULL COMMENT 'The course the assignment is associated with.',
  `comment` varchar(512) DEFAULT NULL COMMENT 'Instructor''s comments',
  `grade` int(11) DEFAULT NULL COMMENT 'The grade.',
  PRIMARY KEY (`studentid`,`assignment`),
  KEY `FK3_grades_assignment_assignment_assignment` (`assignment`),
  KEY `FK3_grades_course_course_courseid` (`course`),
  CONSTRAINT `FK1_grades_studentid_users_userid` FOREIGN KEY (`studentid`) REFERENCES `userdata`.`users` (`userid`) ON UPDATE CASCADE,
  CONSTRAINT `FK2_grades_assignment_assignment_assignment` FOREIGN KEY (`assignment`) REFERENCES `assignment` (`assignment`) ON UPDATE CASCADE,
  CONSTRAINT `FK3_grades_course_course_courseid` FOREIGN KEY (`course`) REFERENCES `course` (`courseid`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Student grades table';

-- Dumping data for table application.grades: ~0 rows (approximately)
/*!40000 ALTER TABLE `grades` DISABLE KEYS */;
/*!40000 ALTER TABLE `grades` ENABLE KEYS */;

-- Dumping structure for table application.gradescale
CREATE TABLE IF NOT EXISTS `gradescale` (
  `scale` int(11) NOT NULL AUTO_INCREMENT COMMENT 'The grade scale ID.',
  `instructor` int(11) NOT NULL COMMENT 'The instructor this scale belongs to.',
  `name` varchar(32) NOT NULL COMMENT 'The name of this grading scale.',
  `description` varchar(512) NOT NULL COMMENT 'The description of this grading scale.',
  `grade_ap` int(11) DEFAULT NULL COMMENT 'Grade A+',
  `grade_a` int(11) DEFAULT NULL COMMENT 'Grade A',
  `grade_am` int(11) DEFAULT NULL COMMENT 'Grade A-',
  `grade_bp` int(11) DEFAULT NULL COMMENT 'Grade B+',
  `grade_b` int(11) DEFAULT NULL COMMENT 'Grade B',
  `grade_bm` int(11) DEFAULT NULL COMMENT 'Grade B-',
  `grade_cp` int(11) DEFAULT NULL COMMENT 'Grade C+',
  `grade_c` int(11) DEFAULT NULL COMMENT 'Grade C',
  `grade_cm` int(11) DEFAULT NULL COMMENT 'Grade C-',
  `grade_dp` int(11) DEFAULT NULL COMMENT 'Grade D+',
  `grade_d` int(11) DEFAULT NULL COMMENT 'Grade D',
  `grade_dm` int(11) DEFAULT NULL COMMENT 'Grade D-',
  PRIMARY KEY (`scale`),
  KEY `FK1_gradescale_instructor_users_userid` (`instructor`),
  CONSTRAINT `FK1_gradescale_instructor_users_userid` FOREIGN KEY (`instructor`) REFERENCES `userdata`.`users` (`userid`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='The instructor''s grade scale.';

-- Dumping data for table application.gradescale: ~3 rows (approximately)
/*!40000 ALTER TABLE `gradescale` DISABLE KEYS */;
INSERT INTO `gradescale` (`scale`, `instructor`, `name`, `description`, `grade_ap`, `grade_a`, `grade_am`, `grade_bp`, `grade_b`, `grade_bm`, `grade_cp`, `grade_c`, `grade_cm`, `grade_dp`, `grade_d`, `grade_dm`) VALUES
	(0, 0, 'Default Grade Scale', 'Default grade scale when there is no instructor assigned to a course.', 100, 92, 90, 87, 83, 80, 77, 73, 70, 67, 63, 60),
	(1, 1000, 'Some Scale Teacher One', 'This is a grading scale used by Teacher One&period;', 0, 92, 90, 87, 83, 80, 77, 73, 70, 67, 63, 60),
	(2, 1001, 'Teach Two Scale', 'Grade scale for teacher two&period;', 0, 90, 85, 80, 75, 70, 65, 60, 55, 50, 45, 40);
/*!40000 ALTER TABLE `gradescale` ENABLE KEYS */;

-- Dumping structure for table application.studentclass
CREATE TABLE IF NOT EXISTS `studentclass` (
  `studentid` int(11) NOT NULL,
  `courseid` int(11) NOT NULL,
  PRIMARY KEY (`studentid`,`courseid`),
  KEY `FK2_studentclass_courseid_course_courseid` (`courseid`),
  CONSTRAINT `FK1_studentclass_studentid_users_userid` FOREIGN KEY (`studentid`) REFERENCES `userdata`.`users` (`userid`) ON UPDATE CASCADE,
  CONSTRAINT `FK2_studentclass_courseid_course_courseid` FOREIGN KEY (`courseid`) REFERENCES `course` (`courseid`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Mapping table to map students to their classes.';

-- Dumping data for table application.studentclass: ~4 rows (approximately)
/*!40000 ALTER TABLE `studentclass` DISABLE KEYS */;
INSERT INTO `studentclass` (`studentid`, `courseid`) VALUES
	(2000, 14298),
	(2000, 19376),
	(2000, 21924),
	(2000, 83746);
/*!40000 ALTER TABLE `studentclass` ENABLE KEYS */;

-- Dumping structure for table application.turnin
CREATE TABLE IF NOT EXISTS `turnin` (
  `studentid` int(11) NOT NULL COMMENT 'The student''s user ID.',
  `assignment` int(11) NOT NULL COMMENT 'The assignment number',
  `step` int(11) NOT NULL COMMENT 'Indicates what step of the assignment was turned in.',
  `subcount` int(11) NOT NULL COMMENT 'The index of how many times the student turned the assignment in.',
  `course` int(11) NOT NULL COMMENT 'The course the assignment is associated with.',
  `timedate` bigint(20) NOT NULL COMMENT 'The time/date of the turnin.',
  PRIMARY KEY (`studentid`,`assignment`,`subcount`,`step`),
  KEY `FK2_turnin_assignment_assignment_assignment` (`assignment`),
  KEY `step` (`step`),
  KEY `subcount` (`subcount`),
  KEY `FK3_turnin_course` (`course`),
  CONSTRAINT `FK1_turnin_studentid_users_userid` FOREIGN KEY (`studentid`) REFERENCES `userdata`.`users` (`userid`) ON UPDATE CASCADE,
  CONSTRAINT `FK2_turnin_assignment_assignment_assignment` FOREIGN KEY (`assignment`) REFERENCES `assignment` (`assignment`) ON UPDATE CASCADE,
  CONSTRAINT `FK3_turnin_course` FOREIGN KEY (`course`) REFERENCES `course` (`courseid`) ON UPDATE CASCADE,
  CONSTRAINT `FK4_turnin_step_assignstep_step` FOREIGN KEY (`step`) REFERENCES `assignstep` (`step`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Maintains a log of assignments that the student has turned in.';

-- Dumping data for table application.turnin: ~1 rows (approximately)
/*!40000 ALTER TABLE `turnin` DISABLE KEYS */;
/*!40000 ALTER TABLE `turnin` ENABLE KEYS */;

-- Dumping structure for table application.weightgroup
CREATE TABLE IF NOT EXISTS `weightgroup` (
  `group` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Grade weight group ID.',
  `instructor` int(11) NOT NULL COMMENT 'The instructor that this weight group belongs to.',
  `weight` int(11) NOT NULL COMMENT 'Weight to total overall course grade.',
  `description` varchar(512) DEFAULT NULL COMMENT 'A description of this weight group.',
  `name` varchar(32) DEFAULT NULL COMMENT 'The name of this weight group.',
  PRIMARY KEY (`group`),
  KEY `FK1_weightgroup_instructor_users_userid` (`instructor`),
  CONSTRAINT `FK1_weightgroup_instructor_users_userid` FOREIGN KEY (`instructor`) REFERENCES `userdata`.`users` (`userid`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='The grade weight group definition.';

-- Dumping data for table application.weightgroup: ~1 rows (approximately)
/*!40000 ALTER TABLE `weightgroup` DISABLE KEYS */;
INSERT INTO `weightgroup` (`group`, `instructor`, `weight`, `description`, `name`) VALUES
	(0, 0, 0, 'THis grade weight group is used when there is no weight group.', 'No Grade Weight Group'),
	(2, 1000, 42, 'For tests&period;', 'Tests');
/*!40000 ALTER TABLE `weightgroup` ENABLE KEYS */;

-- Dumping structure for trigger application.trig_assignstep_step
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trig_assignstep_step` BEFORE INSERT ON `assignstep` FOR EACH ROW BEGIN
		SET NEW.step = (
			SELECT IFNULL(MAX(step), 0) + 1
			FROM assignstep
			WHERE assignment = NEW.assignment
			);
end//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger application.trig_filename_filenumber
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trig_filename_filenumber` BEFORE INSERT ON `filename` FOR EACH ROW BEGIN
		SET NEW.filenumber = (
			SELECT IFNULL(MAX(filenumber), 0) + 1
			FROM filename
			WHERE studentid = NEW.studentid
			AND assignment = NEW.assignment
			AND step = NEW.step
			AND subcount = NEW.subcount
			);
end//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger application.trig_turnin_subcount
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trig_turnin_subcount` BEFORE INSERT ON `turnin` FOR EACH ROW BEGIN
		SET NEW.subcount= (
			SELECT IFNULL(MAX(subcount), 0) + 1
			FROM turnin
			WHERE studentid = NEW.studentid
			AND assignment = NEW.assignment
			AND step = NEW.step
			);
end//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;


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

-- Dumping data for table configuration.config: ~62 rows (approximately)
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
	(43, 2, 'security_chap_enable', 'Enable Challenge Authenticaion Protocol', '1', 'Enables a one-time-password encoding method.', 1),
	(44, 1, 'security_chap_length', 'CHAP Challenge Length', '32', 'Number of random bytes for the CHAP challenge length.', 1),
	(50, 2, 'session_regen_enable', 'Session Regenerate Enable', '1', 'Enables or disables session ID regeneration.', 1),
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
	(78, 2, 'admin_allow_alts', 'Allow Alternate Admins', '1', 'Allows users other than Admin to also be administrators.', 1),
	(80, 2, 'oauth_enable', 'OAuth Login Enabled', '0', 'Specifies whether OAuth is a login method.', 1),
	(90, 2, 'openid_enable', 'OpenID Login Enabled', '0', 'Specifies whether OpenID is a login method.', 1),
	(100, 1, 'files_max_upload_size', 'Maximum upload file size', '4194304', 'The maximum file size, in bytes, that the system will accept for uploaded files.', 1),
	(101, 1, 'files_random_filename_length', 'Random Filename Length', '16', 'The number of bytes used to generate random filenames.  The bytes are converted into base64, so the filename will be about 33 percent longer then the number specified.', 1),
	(102, 1, 'download_token_length', 'Download Token Length', '16', 'The number of bytes used to generate a random download token.  The raw bytes are coverted to base64 encoding.', 1),
	(1000, 1, 'assign_duedate_lookahead', 'Assignment Due Date Look Ahead Time', '1209600', 'The assignment due date look ahead time for the status panel', 1),
	(1001, 1, 'assign_past_due_time', 'Assignment Past Due Time', '604800', 'The amount of time in seconds to display past due assignments.', 1),
	(1002, 1, 'assign_priority_high', 'Assignment Priority High', '86400', 'The amount of time before the due date where the assignment has high priority.', 1),
	(1003, 1, 'assign_priority_medium', 'Assignment Priority Medium', '259200', 'The amount of time before the due date where the assignment has medium priority.', 1),
	(1004, 1, 'assign_priority_low', 'Assignment Priority Low', '604800', 'The amount of time before the due date where the assignment has low priority.', 1),
	(1020, 0, 'files_base_path', 'File Base Path', '/Servers/webdocs/files', 'The base path for user files and directories.  For security reasons, this should exist outside of the web server document root.', 1),
	(1021, 0, 'files_course', 'Course Files Path', 'course', 'This is where the files for various courses are stored.  Courses have sub-directories inside this directory, and is added onto the base path.', 1),
	(1022, 0, 'files_turned_in', 'Turned in Files Location', 'turnin', 'This is the directory where student file submissions for assignments are uploaded too.', 1),
	(1039, 3, 'files_allowed_extensions', 'Upload Files Allowed Extensions', 'pdf zip doc docx xls xlsx ppt pptx pub xps odt ods odp odf odc', 'A space separated list of allowed extensions for uploaded files.  Can be overridden by the instructor on a per-assignment basis.', 1),
	(1040, 1, 'app_profile_instruct', 'Instructor Profile ID', '100', 'The profile ID that course instructors use.', 0),
	(1041, 1, 'app_profile_student', 'Student Profile ID', '200', 'The profile ID that students use.', 0),
	(1050, 1, 'default_gradescale', 'Default Grading Scale', '0', 'The default grading scale that is used if nothing is selected.', 1),
	(1051, 1, 'gradescale_mode', 'Grade Scale Mode', '0', 'Determines the type of grading scale in use. 0: Fractional grades (B+, B-, etc...) 1: Whole grades (A, B, C, etc...)', 1),
	(1060, 1, 'default_weightgroup', 'Default Grade Weight Group', '0', 'The default grade weight group that is used if nothing is specified.', 1);
/*!40000 ALTER TABLE `config` ENABLE KEYS */;

-- Dumping structure for table configuration.flagdesc_app
CREATE TABLE IF NOT EXISTS `flagdesc_app` (
  `flag` int(10) unsigned NOT NULL COMMENT 'Unique flag identifier',
  `name` varchar(32) DEFAULT NULL COMMENT 'Display name of flag',
  `description` varchar(256) DEFAULT NULL COMMENT 'Description of the flag',
  PRIMARY KEY (`flag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table defines the names and descriptions of application flags from the userdata.profile table.  The flag attribute is the bit position of the flag.';

-- Dumping data for table configuration.flagdesc_app: ~2 rows (approximately)
/*!40000 ALTER TABLE `flagdesc_app` DISABLE KEYS */;
INSERT INTO `flagdesc_app` (`flag`, `name`, `description`) VALUES
	(0, 'Files Full Control', 'If this flag is set&comma; the user is allowed to upload&comma; rename&comma; or delete files and directories on the server&period;'),
	(1, 'Course Editing Full Control', 'If this flag is set then the user is allowed to insert&comma; update&comma; and delete information pertaining to a course&period;'),
	(2, 'Course Editing Instructor', 'If this flag is set&comma; then the user can edit course information that is useful for an instructor&period;');
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

-- Dumping data for table configuration.modaccess: ~2 rows (approximately)
/*!40000 ALTER TABLE `modaccess` DISABLE KEYS */;
INSERT INTO `modaccess` (`moduleid`, `profileid`) VALUES
	(1501, 100),
	(1502, 100);
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

-- Dumping data for table configuration.module: ~18 rows (approximately)
/*!40000 ALTER TABLE `module` DISABLE KEYS */;
INSERT INTO `module` (`moduleid`, `name`, `description`, `filename`, `iconname`, `active`, `allusers`, `system`, `vendor`) VALUES
	(1, 'Grid Portal', 'Views the available modules in grid format.', 'gridportal.php', 'icon_grid', 1, 1, 1, 0),
	(2, 'Link Portal', 'Views the available modules in link format.', 'linkportal.php', 'icon_chain2', 1, 1, 1, 0),
	(3, 'Application Portal', 'The application landing page that most users see&period;  Similar to link portal but with additional content in the main panel&period;', 'appportal.php', 'icon_calendar3', 1, 1, 0, 0),
	(10, 'Module Data Editor', 'Edits module data in the database.', 'modedit.php', 'icon_gears', 1, 0, 1, 1),
	(11, 'Configuration Editor', 'Edits the application configuration parameters&period;&NewLine;This allows inserting and deleting of configuration&NewLine;items in the database&period;', 'configedit.php', 'icon_tools3', 1, 0, 1, 1),
	(12, 'System Flags', 'This module edits the system flags which&NewLine;are used by the various user profiles&period;', 'sysflag.php', 'icon_checklist', 1, 0, 1, 1),
	(13, 'Application Flags', 'This module edits the application flags which are used by the various user profiles&period;', 'appflag.php', 'icon_checklist2', 1, 0, 1, 1),
	(14, 'File Finder', 'Allows access to the server file system&period;', 'filefinder.php', 'icon_file_manager5', 1, 0, 1, 1),
	(20, 'Parameter Editor', 'Edits the configuration parameter values for the application&period;&NewLine;This only allows changing the configuration parameter values&period;', 'paramedit.php', 'icon_tools4', 1, 0, 1, 0),
	(21, 'Profile Editor', 'Edits the available profiles that defines what access rights a user has&period;', 'profedit.php', 'icon_profile2', 1, 0, 1, 0),
	(22, 'OAuth Provider Edit', 'Edits the known list of external OAuth authentication providers&period;', 'oauthedit.php', 'icon_oauth', 1, 0, 1, 0),
	(23, 'OpenID Provider Edit', 'Edits the known list of external OpenID authentication providers&period;', 'openidedit.php', 'icon_openid', 1, 0, 1, 0),
	(24, 'User Editor', 'Edits the user database', 'useredit.php', 'icon_users2', 1, 0, 1, 0),
	(30, 'Change Password', 'Allows a user to change their login password&period;', 'passwd.php', 'icon_padlock', 1, 1, 1, 0),
	(31, 'User Data Editor', 'Allows the user to edit some of their own information&period;', 'userdata.php', 'icon_user_female', 1, 1, 1, 0),
	(1100, 'File Manager', 'Manages online course files and materials&period;', 'filemanager.php', 'icon_file_manager', 1, 1, 0, 0),
	(1101, 'Courses', 'Allows editing and viewing of courses&period;', 'course.php', 'icon_course', 1, 1, 0, 0),
	(1102, 'Enrollment', 'Edits the course enrollment&period;', 'enrollment.php', 'icon_enrollment2', 1, 0, 0, 0),
	(1500, 'Grades', 'Grades', 'grades.php', 'icon_grade', 1, 1, 0, 0),
	(1501, 'Grading Scale', 'Allows creating&comma; viewing&comma; and editing of grading scales&period;', 'gradescale.php', 'icon_grade3', 1, 0, 0, 0),
	(1502, 'Grade Weight Groups', 'Allows editing of the grade weight groups so different assignments&comma; quizes&comma; tests&comma; etc&period;&period;&period; contribute different amounts to the total course grade&period;', 'gradeweight.php', 'icon_grade2', 1, 0, 0, 0),
	(1600, 'Assignments', 'Assignments', 'assignments.php', 'icon_homework', 1, 1, 0, 0);
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

-- Dumping data for table configuration.profile: ~6 rows (approximately)
/*!40000 ALTER TABLE `profile` DISABLE KEYS */;
INSERT INTO `profile` (`profileid`, `name`, `description`, `portal`, `bitmap_core`, `bitmap_app`) VALUES
	(0, 'NONE', NULL, 1, NULL, NULL),
	(1, 'Vendor', 'Profile for use only by the application vendor.', 0, _binary 0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF, _binary 0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF),
	(2, 'Admin', 'Profile for use only by the application admin.', 0, _binary 0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF, _binary 0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF),
	(100, 'Instructor', 'The profile for course instructors&period;', 2, _binary 0x00000000000000000000000000000000, _binary 0x05000000000000000000000000000000),
	(200, 'Student', 'The profile for students&period;', 2, _binary 0x00000000000000000000000000000000, _binary 0x00000000000000000000000000000000),
	(435, 'Test Profile', 'This profile is for development testing purposes&period;', 2, _binary 0x00000000000000000000000000000000, _binary 0x00000000000000000000000000000000);
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

-- Dumping data for table userdata.contact: ~8 rows (approximately)
/*!40000 ALTER TABLE `contact` DISABLE KEYS */;
INSERT INTO `contact` (`userid`, `name`, `haddr`, `maddr`, `email`, `hphone`, `cphone`, `wphone`) VALUES
	(0, 'No User', NULL, NULL, NULL, NULL, NULL, NULL),
	(1, 'Application Vendor', 'SEA-CORE International LTD&period;', '', 'seacoregroup&commat;gmail&period;com', '', '', ''),
	(2, 'Application Admin', 'SEA-CORE International LTD&period;', 'SEA-CORE International LTD&period;', 'seacoregroup&commat;gmail&period;com', '', '', ''),
	(34, 'Test User', 'Test user for developmental purposes only&period;', 'Test user for developmental purposes only&period;', 'testuser&commat;localhost&period;com', '707-555-3343', '916-555-1212', '916-278-6000'),
	(1000, 'Teacher One', '', '', '', '', '', ''),
	(1001, 'Teacher Two', '', '', '', '', '', ''),
	(2000, 'Student One', '', '', '', '', '', ''),
	(2001, 'Student Two', '', '', '', '', '', ''),
	(2003, 'Student Three', '', '', '', '', '', '');
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

-- Dumping data for table userdata.login: ~8 rows (approximately)
/*!40000 ALTER TABLE `login` DISABLE KEYS */;
INSERT INTO `login` (`userid`, `locked`, `locktime`, `lastlog`, `failcount`, `timeout`, `digest`, `count`, `salt`, `passwd`) VALUES
	(0, 0, 0, 0, 0, -1, 'SHA256', 100, '0000000000000000000000000000000000000000000000000000000000000000', '0000000000000000000000000000000000000000000000000000000000000000'),
	(1, 0, 1547293947, 1547466007, 0, -1, 'SHA256', 100, 'db63f993e8a1b7de7926ac01b89b743b11ad0674b850cae778ff36684e0d1bc3', 'a0f843907bc5276d68afa04b24935f8f33a7f1c3ec7344d1851ba02f11d2cb13'),
	(2, 0, 0, 1536731231, 0, -1, 'SHA256', 100, '265273ef2fabd48ffbd3a8218a09af8b7a1187b53acfa51626ddb63d0cf4dc8c', 'ea008ac085fe70a84e8a183ca8d3d943a74f3dff278a0768b5ebcc167abdce73'),
	(34, 0, 1541017469, 1547465273, 0, 1555241875, 'SHA256', 100, 'a64d7ff2e115673b4bce4e31858a2ffa4b9e51ee8b0c55098c4f01ca0bc2752e', '8e79341620debda579a721638053a052254032c87fd2aa65fa97e0d7ca80e570'),
	(1000, 0, 0, 1544044101, 0, 2147483647, 'SHA256', 100, '3d85c8641e61e8b8c5ddb6b2b52f717736415cad7ceae3486fa835d59b18a6f8', 'db8812f0f4d975c2ea9172ea0611169dc3ff27475b5057ca21762d16402c61bc'),
	(1001, 0, 0, 0, 0, 2147483647, 'SHA256', 100, 'fae2455c525bf8364cda5a95bd38287461ee8ca3662ecfa1285bdb0141e73592', '15452a7755e9d68edff36d20d573b662ac2f8b037cb67629437a4f8cc4681a4c'),
	(2000, 0, 1541183684, 1544122399, 0, 2147483647, 'SHA256', 100, '61942ac2e51e43516d325b4aa4cd33f9c3d829f666cbac140a200452df3f9373', '6ead0b2e7c114c0bdbcf55595cc7115af2a436669381fb51c77a2b4af12a37e1'),
	(2001, 0, 0, 0, 0, 2147483647, 'SHA256', 100, '6f0724ece7ba6ac9296c1e32fc6e1054e45a493ff684a8cab049fc8e8b10d0ef', '5922976a8119338be7716654f7cb8726ff21d129cf853f8d9ef4d5143ada1f86'),
	(2003, 0, 0, 0, 0, 2147483647, 'SHA256', 100, '9c2b3ddfc3d1e8caf1ca5797eda8eb27fb431f1b5031f1fa1918da972b181689', '956430a38a95241f3175eae6e3ff1e4f3c5080fb13935a7e0c48e50b43b72717');
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
  `handle` varchar(256) DEFAULT NULL COMMENT 'Randomly generated transaction ID.',
  `invalid` varchar(256) DEFAULT NULL COMMENT 'An invalidation handle.',
  `nonce` varchar(250) DEFAULT NULL COMMENT 'OpenID response nonce.',
  `issue` bigint(20) NOT NULL DEFAULT '0' COMMENT 'Time/Date that the authentication was issued.',
  `expire` bigint(20) NOT NULL DEFAULT '0' COMMENT 'Time/Date that the authentication is to expire.',
  PRIMARY KEY (`userid`),
  KEY `FK_openid_provider_configuration.openid_provider` (`provider`),
  KEY `handle` (`handle`),
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

-- Dumping data for table userdata.users: ~8 rows (approximately)
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`userid`, `username`, `profileid`, `method`, `active`, `orgid`) VALUES
	(0, 'NONE', 0, 0, 0, 'NoUser'),
	(1, 'vendor', 1, 0, 1, NULL),
	(2, 'admin', 2, 0, 1, NULL),
	(34, 'testuser', 435, 0, 1, 'TestUserDeveloper3854755'),
	(1000, 'teacher_one', 100, 0, 1, ''),
	(1001, 'teacher_two', 100, 0, 1, ''),
	(2000, 'student_one', 200, 0, 1, ''),
	(2001, 'student_two', 200, 0, 1, ''),
	(2003, 'student_three', 200, 0, 1, '');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
