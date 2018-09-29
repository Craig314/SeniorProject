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


-- Dumping database structure for application
CREATE DATABASE IF NOT EXISTS `application` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `application`;

-- Dumping structure for table application.assignment
CREATE TABLE IF NOT EXISTS `assignment` (
  `assignment` int(11) NOT NULL AUTO_INCREMENT COMMENT 'The assignment number for the course.',
  `courseid` int(11) NOT NULL COMMENT 'The unique course ID number.',
  `desc` varchar(512) DEFAULT NULL COMMENT 'A type in description for the assignment.',
  `descfile` mediumblob COMMENT 'A description file for the assignment that the ',
  `duedate` bigint(20) NOT NULL COMMENT 'Assignment due date.',
  `lockdate` bigint(20) DEFAULT NULL COMMENT 'The date where students can no longer submit assignments.',
  `gradeweight` int(11) DEFAULT NULL COMMENT 'The total grade weight of the assignment.',
  `gwgroup` int(11) DEFAULT '0' COMMENT 'The grade weight group if the course has one.',
  PRIMARY KEY (`assignment`),
  KEY `assignment` (`assignment`),
  KEY `FK1_asssignment_courseid_course_courseid` (`courseid`),
  KEY `FK2_assignment_gwgroup_weightgroup_group` (`gwgroup`),
  CONSTRAINT `FK1_asssignment_courseid_course_courseid` FOREIGN KEY (`courseid`) REFERENCES `course` (`courseid`) ON UPDATE CASCADE,
  CONSTRAINT `FK2_assignment_gwgroup_weightgroup_group` FOREIGN KEY (`gwgroup`) REFERENCES `weightgroup` (`group`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='The assignments that the instructor assigns to students.';

-- Dumping data for table application.assignment: ~0 rows (approximately)
/*!40000 ALTER TABLE `assignment` DISABLE KEYS */;
/*!40000 ALTER TABLE `assignment` ENABLE KEYS */;

-- Dumping structure for table application.assignstep
CREATE TABLE IF NOT EXISTS `assignstep` (
  `assignment` int(11) NOT NULL COMMENT 'The assignment number.',
  `step` int(11) NOT NULL COMMENT 'The step number.',
  `date` bigint(20) NOT NULL COMMENT 'The date the step should be completed.',
  `desc` varchar(512) NOT NULL COMMENT 'A description of the step.',
  PRIMARY KEY (`assignment`,`step`),
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

-- Dumping data for table application.course: ~0 rows (approximately)
/*!40000 ALTER TABLE `course` DISABLE KEYS */;
/*!40000 ALTER TABLE `course` ENABLE KEYS */;

-- Dumping structure for table application.grades
CREATE TABLE IF NOT EXISTS `grades` (
  `studentid` int(11) NOT NULL COMMENT 'The student''s userid.',
  `courseid` int(11) NOT NULL COMMENT 'The course that the student is enrolled in.',
  `assignment` int(11) NOT NULL COMMENT 'The assignment.',
  `comment` varchar(512) DEFAULT NULL COMMENT 'Instructor''s comments',
  `grade` int(11) DEFAULT NULL COMMENT 'The grade.',
  PRIMARY KEY (`studentid`,`courseid`,`assignment`),
  KEY `FK2_grades_courseid_course_courseid` (`courseid`),
  KEY `FK3_grades_assignment_assignment_assignment` (`assignment`),
  CONSTRAINT `FK1_grades_studentid_users_userid` FOREIGN KEY (`studentid`) REFERENCES `userdata`.`users` (`userid`) ON UPDATE CASCADE,
  CONSTRAINT `FK2_grades_courseid_course_courseid` FOREIGN KEY (`courseid`) REFERENCES `course` (`courseid`) ON UPDATE CASCADE,
  CONSTRAINT `FK3_grades_assignment_assignment_assignment` FOREIGN KEY (`assignment`) REFERENCES `assignment` (`assignment`) ON UPDATE CASCADE
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

-- Dumping data for table application.gradescale: ~0 rows (approximately)
/*!40000 ALTER TABLE `gradescale` DISABLE KEYS */;
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
/*!40000 ALTER TABLE `studentclass` ENABLE KEYS */;

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

-- Dumping data for table application.weightgroup: ~0 rows (approximately)
/*!40000 ALTER TABLE `weightgroup` DISABLE KEYS */;
/*!40000 ALTER TABLE `weightgroup` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
