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


-- Dumping database structure for application
CREATE DATABASE IF NOT EXISTS `application` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `application`;

-- Dumping structure for table application.assignment
CREATE TABLE IF NOT EXISTS `assignment` (
  `assignment` int(11) NOT NULL AUTO_INCREMENT COMMENT 'The assignment number for the course.',
  `courseid` int(11) NOT NULL COMMENT 'The unique course ID number.',
  `name` varchar(30) NOT NULL COMMENT 'The name of the assignment.',
  `desc` varchar(512) DEFAULT NULL COMMENT 'A type in description for the assignment.',
  `descfile` varchar(50) DEFAULT NULL COMMENT 'A description file for the assignment that the ',
  `duedate` bigint(20) NOT NULL COMMENT 'Assignment due date.',
  `lockdate` bigint(20) DEFAULT NULL COMMENT 'The date where students can no longer submit assignments.',
  `gradeweight` int(11) DEFAULT NULL COMMENT 'The total grade weight of the assignment.',
  `gwgroup` int(11) DEFAULT '0' COMMENT 'The grade weight group if the course has one.',
  `curve` int(11) DEFAULT NULL COMMENT 'Any curve that the instructor sets for the assignment.',
  `points` int(11) DEFAULT NULL COMMENT 'The numbere of points the assignment is worth.',
  `exempt` int(11) DEFAULT NULL COMMENT 'Indicates if this assignment is exempt from the total grade.',
  PRIMARY KEY (`assignment`),
  KEY `assignment` (`assignment`),
  KEY `FK1_asssignment_courseid_course_courseid` (`courseid`),
  KEY `FK2_assignment_gwgroup_weightgroup_group` (`gwgroup`),
  KEY `duedate` (`duedate`),
  CONSTRAINT `FK1_asssignment_courseid_course_courseid` FOREIGN KEY (`courseid`) REFERENCES `course` (`courseid`) ON UPDATE CASCADE,
  CONSTRAINT `FK2_assignment_gwgroup_weightgroup_group` FOREIGN KEY (`gwgroup`) REFERENCES `weightgroup` (`group`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='The assignments that the instructor assigns to students.';

-- Dumping data for table application.assignment: ~1 rows (approximately)
/*!40000 ALTER TABLE `assignment` DISABLE KEYS */;
INSERT INTO `assignment` (`assignment`, `courseid`, `name`, `desc`, `descfile`, `duedate`, `lockdate`, `gradeweight`, `gwgroup`, `curve`, `points`, `exempt`) VALUES
	(1, 20284, 'Assignment 1', 'Assignment 1', NULL, 1540763960, NULL, 10, 0, NULL, 10, 0),
	(2, 14298, 'Assignment 1', 'Assignment 1', NULL, 1540763960, NULL, 10, 0, NULL, 10, 0),
	(3, 21924, 'Assignment 1', 'Assignment 1', NULL, 1540995920, NULL, 10, 0, NULL, 10, 0),
	(4, 21924, 'Assignment 2', 'Assignment 2', NULL, 1541427920, NULL, 10, 0, NULL, 10, 0);
/*!40000 ALTER TABLE `assignment` ENABLE KEYS */;

-- Dumping structure for table application.assignstep
CREATE TABLE IF NOT EXISTS `assignstep` (
  `assignment` int(11) NOT NULL COMMENT 'The assignment number.',
  `step` int(11) NOT NULL COMMENT 'The step number.',
  `date` bigint(20) NOT NULL COMMENT 'The date the step should be completed.',
  `desc` varchar(512) NOT NULL COMMENT 'A description of the step.',
  PRIMARY KEY (`assignment`,`step`),
  KEY `step` (`step`),
  CONSTRAINT `FK1_assignstep_assignment_assignment_assignment` FOREIGN KEY (`assignment`) REFERENCES `assignment` (`assignment`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Steps or milestones the assignment requires for completetion.';

-- Dumping data for table application.assignstep: ~0 rows (approximately)
/*!40000 ALTER TABLE `assignstep` DISABLE KEYS */;
/*!40000 ALTER TABLE `assignstep` ENABLE KEYS */;

-- Dumping structure for table application.course
CREATE TABLE IF NOT EXISTS `course` (
  `courseid` int(11) NOT NULL COMMENT 'The unique ID number of the course.',
  `class` varchar(12) NOT NULL COMMENT 'The course code.  Ex: CSC 190',
  `section` int(11) NOT NULL COMMENT 'The course section number.',
  `name` varchar(50) NOT NULL COMMENT 'The name of the course.',
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

-- Dumping data for table application.course: ~1 rows (approximately)
/*!40000 ALTER TABLE `course` DISABLE KEYS */;
INSERT INTO `course` (`courseid`, `class`, `section`, `name`, `instructor`, `scale`, `curve`) VALUES
	(14298, 'CSC-152', 1, 'Cryptography', 1000, 0, NULL),
	(19376, 'CSC-151', 1, 'Compiler Construction', 1001, 0, NULL),
	(20284, 'CSC-152', 2, 'Cryptography', 1000, 0, NULL),
	(21924, 'CSC-130', 1, 'Data Structures and Algorithms', 1000, 0, NULL),
	(21925, 'CSC-130', 2, 'Data Structures and Algorithms', 1000, 0, NULL),
	(83745, 'CSC-131', 1, 'Software Engineering', 1000, 0, NULL),
	(83746, 'CSC-131', 2, 'Software Engineering', 1001, 0, NULL);
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
  `desc` varchar(512) NOT NULL COMMENT 'The description of this grading scale.',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='The instructor''s grade scale.';

-- Dumping data for table application.gradescale: ~1 rows (approximately)
/*!40000 ALTER TABLE `gradescale` DISABLE KEYS */;
INSERT INTO `gradescale` (`scale`, `instructor`, `name`, `desc`, `grade_ap`, `grade_a`, `grade_am`, `grade_bp`, `grade_b`, `grade_bm`, `grade_cp`, `grade_c`, `grade_cm`, `grade_dp`, `grade_d`, `grade_dm`) VALUES
	(0, 0, 'Default Grade Scale', 'Default grade scale when there is no instructor assigned to a course.', 100, 92, 90, 87, 83, 80, 77, 73, 70, 67, 63, 60);
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

-- Dumping data for table application.studentclass: ~0 rows (approximately)
/*!40000 ALTER TABLE `studentclass` DISABLE KEYS */;
INSERT INTO `studentclass` (`studentid`, `courseid`) VALUES
	(2000, 20284),
	(2000, 21924);
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

-- Dumping data for table application.turnin: ~0 rows (approximately)
/*!40000 ALTER TABLE `turnin` DISABLE KEYS */;
/*!40000 ALTER TABLE `turnin` ENABLE KEYS */;

-- Dumping structure for table application.weightgroup
CREATE TABLE IF NOT EXISTS `weightgroup` (
  `group` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Grade weight group ID.',
  `instructor` int(11) NOT NULL COMMENT 'The instructor that this weight group belongs to.',
  `weight` int(11) NOT NULL COMMENT 'Weight to total overall course grade.',
  `desc` varchar(512) DEFAULT NULL COMMENT 'A description of this weight group.',
  `name` varchar(32) DEFAULT NULL COMMENT 'The name of this weight group.',
  PRIMARY KEY (`group`),
  KEY `FK1_weightgroup_instructor_users_userid` (`instructor`),
  CONSTRAINT `FK1_weightgroup_instructor_users_userid` FOREIGN KEY (`instructor`) REFERENCES `userdata`.`users` (`userid`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='The grade weight group definition.';

-- Dumping data for table application.weightgroup: ~1 rows (approximately)
/*!40000 ALTER TABLE `weightgroup` DISABLE KEYS */;
INSERT INTO `weightgroup` (`group`, `instructor`, `weight`, `desc`, `name`) VALUES
	(0, 0, 0, 'THis grade weight group is used when there is no weight group.', 'No Grade Weight Group');
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

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
