<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application Application Database Driver


*/


require_once '../libs/database.php';


interface database_application_interface
{
	// Assignment Table
	public function queryAssignment($assign);
	public function queryAssignmentCourseAll($course);
	public function updateAssignment($assign, $desc, $descfile, $duedate,
		$lockdate, $grdw, $grdwgrp, $curve, $points, $exempt);
	public function insertAssignment($course, $desc, $descfile, $duedate,
		$lockdate, $grdw, $grdwgrp, $curve, $points, $exempt);
	public function deleteAssignment($assign, $course);
	
	// Assignment-Step Table
	public function queryAssignstep($assign, $step);
	public function queryAssignstepAssignAll($assign);
	public function updateAssignstep($assign, $step, $date, $desc);
	public function insertAssignstep($assign, $step, $date, $desc);
	public function deleteAssignstep($assign, $step);
	public function deleteAssignstepAll($assign);
	
	// Course Table
	public function queryCourse($course);
	public function queryCourseInstructAll($instruct);
	public function updateCourseAdmin($course, $class, $sect, $name,
		$instruct, $scale, $curve);
	public function updateCourseInstruct($course, $instruct);
	public function updateCourse($course, $scale, $curve);
	public function deleteCourse($course);

	// Filename Table
	public function queryFilename($student, $assign, $step, $count, $fnumb);
	public function queryFilenameSubmitAll($student, $assign, $step, $count);
	public function queryFilenameStepAll($student, $assign, $step);
	public function queryFilenameAssignAll($student, $assign);
	public function insertFilename($student, $assign, $step, $count, $stdfile,
		$sysfile);
	public function deleteFilenameAssign($student, $assign);
	public function deleteFilenameStep($student, $assign, $step);
	public function deleteFilenameCount($student, $assign, $step, $count);
	public function deleteFilenameStudent($student);

	// Grades Table
	public function queryGradesAssign($student, $assign);
	public function queryGradesCourse($course);
	public function queryGradesStudent($student, $course);
	public function updateGrades($student, $assign, $comment, $grade);
	public function insertGrades($student, $assign, $course, $comment, $grade);
	public function deleteGrades($student, $assign, $course);
	public function deleteGradesAssign($assign, $course);
	public function deleteGradesStudent($student);

	// Grade-Scale Table
	public function queryGradescale($scale, $instruct);
	public function queryGradescaleAll($instruct);
	public function updateGradescale($scale, $instruct, $name, $desc, $gap, $ga, $gam,
		$gbp, $gb, $gbm, $gcp, $gc, $gcm, $gdp, $gd, $gdm);
	public function insertGradescale($scale, $instruct, $name, $desc, $gap, $ga, $gam,
		$gbp, $gb, $gbm, $gcp, $gc, $gcm, $gdp, $gd, $gdm);
	public function deleteGradescale($scale, $instruct);

	// Student-Class Table
	public function queryStudentclassStudentAll($student);
	public function queryStudentclassCourseAll($course);
	public function queryStudentClass($student, $course);
	public function insertStudentclass($student, $course);
	public function deleteStudentclass($student, $course);
	public function deleteStudentclassStudent($student);
	public function deleteStudentclassCourse($course);

	// Turn-In Table
	public function queryTurninStudentAssignAll($student, $assign);
	public function insertTurnin($student, $assign, $step, $course, $timedate);
	public function deleteTurninStudentAssign($student, $assign, $course);
	public function deleteTurninStudentStep($student, $assign, $course, $step);
	public function deleteTurninStudentCount($student, $assign, $course, $step, $count);
	public function deleteTurninStudentAll($student);

	// Weight-Group Table
	public function queryWeightgroup($group, $instruct);
	public function queryWeightgroupInstructAll($instruct);
	public function updateWeightGroup($group, $weight, $desc, $name);
	public function insertWeightgroup($instruct, $weight, $desc, $name);
	public function deleteWeightgroup($group, $instruct);

	// Meta Functions
	
}


class database_application implements database_application_interface
{

	private $tablebase = APP_DATABASE_APPLICATION;



	/* ******** ASSIGNMENT TABLE ******** */

	/* The assignment table contains information about assignments. */

	// Query an assignment.
	public function queryAssignment($assign)
	{
		global $dbcore;
		$table = $this->tablebase . '.assignment';
		$column = '*';
		$qxa = $dbcore->buildArray('assignment', $assign, databaseCore::PTINT);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Query all assignments for the specified course.
	public function queryAssignmentCourseAll($course)
	{
		global $dbcore;
		$table = $this->tablebase . '.assignment';
		$column = '*';
		$qxa = $dbcore->buildArray('course', $course, databaseCore::PTINT);
		return($dbcore->launchQueryMultiple($table, $column, $qxa));
	}

	// Updates an assignment.
	public function updateAssignment($assign, $desc, $descfile, $duedate,
		$lockdate, $grdw, $grdwgrp, $curve, $points, $exempt)
	{
		global $dbcore;
		$table = $this->tablebase . '.assignment';
		$qxa = $dbcore->buildArray('desc', $desc, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('descfile', $descfile, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('duedate', $duedate, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('lockdate', $lockdate, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('gradeweight', $grdw, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('gwgroup', $grdwgrp, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('curve', $curve, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('points', $points, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('exempt', $exempt, databaseCore::PTINT, $qxa);
		return($dbcore->launchUpdateSingle($table, 'assignment', $assign,
			databaseCore::PTINT, $qxa));
	}

	// Inserts a new assignment.
	public function insertAssignment($course, $desc, $descfile, $duedate,
		$lockdate, $grdw, $grdwgrp, $curve, $points, $exempt)
	{
		global $dbcore;
		$table = $this->tablebase . '.assignment';
		$qxa = $dbcore->buildArray('course', $course, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('desc', $desc, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('descfile', $descfile, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('duedate', $duedate, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('lockdate', $lockdate, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('grdw', $grdw, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('grdwgrp', $grdwgrp, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('curve', $curve, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('points', $points, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('exempt', $exempt, databaseCore::PTINT, $qxa);
		return($dbcore->launchInsert($table, $qxa));
	}

	// Deletes an assignment.
	public function deleteAssignment($assign, $course)
	{
		global $dbcore;
		$table = $this->tablebase . '.assignment';
		$qxk = $dbcore->buildArray('assignment', $assign, databaseCore::PTINT);
		$qxk = $dbcore->buildArray('course', $course, databaseCore::PTINT, $qxk);
		return($dbcore->launchDeleteMultiple($table, $qxk));
	}


	/* ******** ASSIGNMENT STEP TABLE ******** */

		/* Optional: Allows an instructor to define the steps to complete an
		   assignment so that students can gauge their progress. */

	// Queries a step to an assignment.
	public function queryAssignstep($assign, $step)
	{
		global $dbcore;
		$table = $this->tablebase . '.assignstep';
		$column = '*';
		$qxa = $dbcore->buildArray('assignment', $assign, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('step', $step, databaseCore::PTINT, $qxa);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Queries all steps to an assignment.
	public function queryAssignstepAssignAll($assign)
	{
		global $dbcore;
		$table = $this->tablebase . '.assignstep';
		$column = '*';
		$qxa = $dbcore->buildArray('assignment', $assign, databaseCore::PTINT);
		return($dbcore->launchQueryMultiple($table, $column, $qxa));
	}

	// Updates an assignment step.
	public function updateAssignstep($assign, $step, $date, $desc)
	{
		global $dbcore;
		$table = $this->tablebase . '.assignstep';
		$qxk = $dbcore->buildArray('assignment', $assign, databaseCore::PTINT);
		$qxk = $dbcore->buildArray('step', $step, databaseCore::PTINT, $qxk);
		$qxa = $dbcore->buildArray('date', $date, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('desc', $desc, databaseCore::PTSTR, $qxa);
		return($dbcore->launchUpdateMutiple($table, $qxk, $qxa));
	}

	// Inserts a assignment step.
	public function insertAssignstep($assign, $step, $date, $desc)
	{
		global $dbcore;
		$table = $this->tablebase . '.assignstep';
		$qxa = $dbcore->buildArray('assignment', $assign, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('step', $step, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('date', $date, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('desc', $desc, databaseCore::PTSTR, $qxa);
		return($dbcore->launchInsert($table, $qxa));
	}

	// Deletes an assignment step.
	public function deleteAssignstep($assign, $step)
	{
		global $dbcore;
		$table = $this->tablebase . '.assignstep';
		$qxa = $dbcore->buildArray('assignment', $assign, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('step', $step, databaseCore::PTINT, $qxa);
		return($dbcore->launchDeleteMultiple($table, $qxa));
	}

	// Deletes all steps to an assignment.
	public function deleteAssignstepAll($assign)
	{
		global $dbcore;
		$table = $this->tablebase . '.assignstep';
		return($dbcore->launchDeleteSingle($table, 'assignment', $assign,
			databaseCore::PTINT));
	}



	/* ******** COURSE TABLE ******** */

	/* The course table defines a course (class) that the student takes.  It also
	   maps to which instructor owns (teaches) the course so only the owner can
	   make modifications to course data such as assignments, grading scale, and
	   grading curve. */

	// Queries a course.
	public function queryCourse($course)
	{
		global $dbcore;
		$table = $this->tablebase . '.course';
		$column = '*';
		$qxa = $dbcore->buildArray('courseid', $course, databaseCore::PTINT);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Queries all courses taught by an instructor.
	public function queryCourseInstructAll($instruct)
	{
		global $dbcore;
		$table = $this->tablebase . '.course';
		$column = '*';
		$qxa = $dbcore->buildArray('instructor', $instruct, databaseCore::PTINT);
		return($dbcore->launchQueryMultiple($table, $column, $qxa));
	}

	// Updates information for a course. (Admin)
	public function updateCourseAdmin($course, $class, $sect, $name,
		$instruct, $scale, $curve)
	{
		global $dbcore;
		$table = $this->tablebase . '.course';
		$qxa = $dbcore->buildArray('class', $class, databaseCore::PTSTR);
		$qxa = $dbcore->buildArray('section', $sect, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('instructor', $instruct, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('scale', $scale, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('curve', $curve, databaseCore::PTINT, $qxa);
		return($dbcore->launchUpdateSingle($table, 'courseid', $course,
			databaseCore::PTINT, $qxa));
	}

	// Updates the instructor for a course (Admin).
	public function updateCourseInstruct($course, $instruct)
	{
		global $dbcore;
		$table = $this->tablebase . '.course';
		$qxa = $dbcore->buildArray('instructor', $instruct, databaseCore::PTINT);
		return($dbcore->launchUpdateSingle($table, 'courseid', $course,
			databaseCore::PTINT, $qxa));
	}

	// Updates information for a course. (Instructor)
	public function updateCourse($course, $scale, $curve)
	{
		global $dbcore;
		$table = $this->tablebase . '.course';
		$qxa = $dbcore->buildArray('class', $class, databaseCore::PTSTR);
		$qxa = $dbcore->buildArray('section', $sect, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('instructor', $instruct, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('scale', $scale, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('curve', $curve, databaseCore::PTINT, $qxa);
		return($dbcore->launchUpdateSingle($table, 'courseid', $course,
			databaseCore::PTINT, $qxa));
	}

	// Inserts a course.
	public function insert($course, $class, $sect, $name, $instruct, $scale, $curve)
	{
		global $dbcore;
		$table = $this->tablebase . '.course';
		$qxa = $dbcore->buildArray('courseid', $course, databaseCore::PT);
		$qxa = $dbcore->buildArray('class', $class, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('section', $sect, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('instructor', $instruct, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('scale', $scale, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('curve', $curve, databaseCore::PTINT, $qxa);
		return($dbcore->launchInsert($table, $qxa));
	}

	// Deletes a course.
	public function deleteCourse($course)
	{
		global $dbcore;
		$table = $this->tablebase . '.course';
		return($dbcore->launchDeleteSingle($table, 'courseid', $course,
			databaseCore::PTINT, $qxa));
	}


	/* ******** FILENAME TABLE ******** */

	/* This table maps a student submitted filename to a system generated
	   filename.  Supports step based turnins. */

	// Queries a filename.
	public function queryFilename($student, $assign, $step, $count, $fnumb)
	{
		global $dbcore;
		$table = $this->tablebase . '.filename';
		$column = '*';
		$qxa = $dbcore->buildArray('studentid', $student, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('assignment', $assign, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('step', $step, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('subcount', $count, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('filenumber', $fnumb, databaseCore::PTINT, $qxa);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Queries all filenames associated with a submission.
	public function queryFilenameSubmitAll($student, $assign, $step, $count)
	{
		global $dbcore;
		$table = $this->tablebase . '.filename';
		$column = '*';
		$qxa = $dbcore->buildArray('studentid', $student, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('assignment', $assign, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('step', $step, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('subcount', $count, databaseCore::PTINT, $qxa);
		return($dbcore->launchQueryMultiple($table, $column, $qxa));
	}

	// Queries all filenames associated with a step.
	public function queryFilenameStepAll($student, $assign, $step)
	{
		global $dbcore;
		$table = $this->tablebase . '.filename';
		$column = '*';
		$qxa = $dbcore->buildArray('studentid', $student, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('assignment', $assign, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('step', $step, databaseCore::PTINT, $qxa);
		return($dbcore->launchQueryMultiple($table, $column, $qxa));
	}

	// Queries all filenames associated with an assignment.
	public function queryFilenameAssignAll($student, $assign)
	{
		global $dbcore;
		$table = $this->tablebase . '.filename';
		$column = '*';
		$qxa = $dbcore->buildArray('studentid', $student, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('assignment', $assign, databaseCore::PTINT, $qxa);
		return($dbcore->launchQueryMultiple($table, $column, $qxa));
	}

	// Inserts a file association.
	public function insertFilename($student, $assign, $step, $count, $stdfile,
		$sysfile)
	{
		global $dbcore;
		$table = $this->tablebase . '.filename';
		$qxa = $dbcore->buildArray('studentid', $student, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('assignment', $assign, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('step', $step, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('subcount', $count, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('studentfile', $stdfile, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('sysfile', $sysfile, databaseCore::PTSTR, $qxa);
		return($dbcore->launchInsert($table, $qxa));
	}

	// Deletes all filenames associated with an assignment.
	public function deleteFilenameAssign($student, $assign)
	{
		global $dbcore;
		$table = $this->tablebase . '.filename';
		$qxa = $dbcore->buildArray('studentid', $student, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('assignment', $assign, databaseCore::PTINT, $qxa);
		return($dbcore->launchDeleteMultiple($table, $qxa));
	}

	// Deletes all filenames associated with an assignment step.
	public function deleteFilenameStep($student, $assign, $step)
	{
		global $dbcore;
		$table = $this->tablebase . '.filename';
		$qxa = $dbcore->buildArray('studentid', $student, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('assignment', $assign, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('step', $step, databaseCore::PTINT, $qxa);
		return($dbcore->launchDeleteMultiple($table, $qxa));
	}

	// Deletes all filenames associated with an assignment submission.
	public function deleteFilenameCount($student, $assign, $step, $count)
	{
		global $dbcore;
		$table = $this->tablebase . '.filename';
		$qxa = $dbcore->buildArray('studentid', $student, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('assignment', $assign, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('step', $step, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('subcount', $count, databaseCore::PTINT, $qxa);
		return($dbcore->launchDeleteMultiple($table, $qxa));
	}

	// Deletes all filenames associated with a student.
	public function deleteFilenameStudent($student)
	{
		global $dbcore;
		$table = $this->tablebase . '.filename';
		return($dbcore->launchDelete($table, 'studentid', $student, databaseCore::PTINIT));
	}


	/* ******** GRADES TABLE ******** */

	/* This table holds the grade earned for an assignent. */

	// Queries a grade associated with an assignment.
	public function queryGradesAssign($student, $assign)
	{
		global $dbcore;
		$table = $this->tablebase . '.grades';
		$column = '*';
		$qxa = $dbcore->buildArray('studentid', $student, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('assignment', $assign, databaseCore::PTINT, $qxa);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Queries all grades associated with a course.
	public function queryGradesCourse($course)
	{
		global $dbcore;
		$table = $this->tablebase . '.grades';
		$column = '*';
		$qxa = $dbcore->buildArray('course', $course, databaseCore::PTINT);
		return($dbcore->launchQueryMultiple($table, $column, $qxa));
	}

	// Queries all grades associated with a student and course.
	public function queryGradesStudent($student, $course)
	{
		global $dbcore;
		$table = $this->tablebase . '.grades';
		$column = '*';
		$qxa = $dbcore->buildArray('studentid', $student, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('course', $course, databaseCore::PTINT, $qxa);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Updates a student grade on an assignment.
	public function updateGrades($student, $assign, $comment, $grade)
	{
		global $dbcore;
		$table = $this->tablebase . '.grades';
		$qxk = $dbcore->buildArray('student', $student, databaseCore::PTINT);
		$qxk = $dbcore->buildArray('assignment', $assign, databaseCore::PTINT, $qxk);
		$qxa = $dbcore->buildArray('comment', $comment, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('grade', $grade, databaseCore::PTINT, $qxa);
		return($dbcore->launchUpdateMultiple($table, $qxk, $qxa));
	}

	// Inserts an assignment grade
	public function insertGrades($student, $assign, $course, $comment, $grade)
	{
		global $dbcore;
		$table = $this->tablebase . '.grades';
		$qxa = $dbcore->buildArray('student', $student, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('assignment', $assign, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('course', $course, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('comment', $comment, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('grade', $grade, databaseCore::PTINT, $qxa);
		return($dbcore->launchInsert($table, $qxa));
	}

	// Deletes a grade
	public function deleteGrades($student, $assign, $course)
	{
		global $dbcore;
		$table = $this->tablebase . '.grades';
		$qxa = $dbcore->buildArray('studentid', $student, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('assignment', $assign, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('course', $course, databaseCore::PTINT, $qxa);
		return($dbcore->launchDeleteMultiple($table, $qxa));
	}

	// Deletes all grades associated with an assignment.
	public function deleteGradesAssign($assign, $course)
	{
		global $dbcore;
		$table = $this->tablebase . '.grades';
		$qxa = $dbcore->buildArray('assignment', $assign, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('course', $course, databaseCore::PTINT, $qxa);
		return($dbcore->launchDeleteMultiple($table, $qxa));
	}

	// Deletes all grades associated with a student.
	// Generally used when deleting a user from the system.
	public function deleteGradesStudent($student)
	{
		global $dbcore;
		$table = $this->tablebase . '.grades';
		return($dbcore->launchDeleteSingle($table, 'studentid', $student, databaseCore::PTINT));
	}


	/* ******** GRADESCALE TABLE ******** */

	/* This table allows an instructor to manage their gradescales for the
	   courses that they teach. */

	// Queries an instructor's grade scale.
	public function queryGradescale($scale, $instruct)
	{
		global $dbcore;
		$table = $this->tablebase . '.gradescale';
		$column = '*';
		$qxa = $dbcore->buildArray('scale', $scale, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('instructor', $instruct, databaseCore::PTINT, $qxa);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Queries all grade scales belonging to the specified instructor.
	public function queryGradescaleAll($instruct)
	{
		global $dbcore;
		$table = $this->tablebase . '.gradescale';
		$column = '*';
		$qxa = $dbcore->buildArray('instructor', $instruct, databaseCore::PTINT);
		return($dbcore->launchQueryMultiple($table, $column, $qxa));
	}

	// Updates an instructor's gradescale.
	public function updateGradescale($scale, $instruct, $name, $desc, $gap, $ga, $gam,
		$gbp, $gb, $gbm, $gcp, $gc, $gcm, $gdp, $gd, $gdm)
	{
		global $dbcore;
		$table = $this->tablebase . '.gradescale';
		$qxk = $dbcore->buildArray('scale', $scale, databaseCore::PTINT);
		$qxk = $dbcore->buildArray('instructor', $instruct, databaseCore::PTINT, $qxk);
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR);
		$qxa = $dbcore->buildArray('desc', $desc, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('grade_ap', $gap, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('grade_a', $ga, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('grade_am', $gam, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('grade_bp', $gbp, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('grade_b', $gb, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('grade_bm', $gbm, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('grade_cp', $gcp, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('grade_c', $gc, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('grade_cm', $gcm, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('grade_dp', $gdp, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('grade_d', $gd, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('grade_dm', $gdm, databaseCore::PTINT, $qxa);
		return($dbcore->launchUpdateMultiple($table, $qxk, $qxa));
	}

	// Inserts an instructor's grade scale.
	public function insertGradescale($scale, $instruct, $name, $desc, $gap, $ga, $gam,
		$gbp, $gb, $gbm, $gcp, $gc, $gcm, $gdp, $gd, $gdm)
	{
		global $dbcore;
		$table = $this->tablebase . '.gradescale';
		$qxa = $dbcore->buildArray('scale', $scale, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('instructor', $instruct, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('desc', $desc, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('grade_ap', $gap, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('grade_a', $ga, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('grade_am', $gam, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('grade_bp', $gbp, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('grade_b', $gb, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('grade_bm', $gbm, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('grade_cp', $gcp, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('grade_c', $gc, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('grade_cm', $gcm, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('grade_dp', $gdp, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('grade_d', $gd, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('grade_dm', $gdm, databaseCore::PTINT, $qxa);
		return($dbcore->launchInsert($table, $qxa));
	}

	// Deletes an instructor's grade scale.
	public function deleteGradescale($scale, $instruct)
	{
		global $dbcore;
		$table = $this->tablebase . '.gradescale';
		$qxa = $dbcore->buildArray('scale', $scale, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('instructor', $instruct, databaseCore::PTINT, $qxa);
		return($dbcore->launchDeleteMultiple($table, $qxa));
	}


	/* ******** STUDENTCLASS TABLE ******** */

	/* This table maps between students and the classes that they are
	   enrolled in. */

	// Queries all classes that a student is enrolled in.
	public function queryStudentclassStudentAll($student)
	{
		global $dbcore;
		$table = $this->tablebase . '.studentclass';
		$column = '*';
		$qxa = $dbcore->buildArray('studentid', $student, databaseCore::PTINT);
		return($dbcore->launchQueryMultiple($table, $column, $qxa));
	}

	// Queries all students that are enrolled in a class.
	public function queryStudentclassCourseAll($course)
	{
		global $dbcore;
		$table = $this->tablebase . '.studentclass';
		$column = '*';
		$qxa = $dbcore->buildArray('courseid', $course, databaseCore::PTINT);
		return($dbcore->launchQueryMultiple($table, $column, $qxa));
	}

	// Queries if a student is in a class.
	public function queryStudentClass($student, $course)
	{
		global $dbcore;
		$table = $this->tablebase . '.studentclass';
		$column = '*';
		$qxa = $dbcore->buildArray('studentid', $student, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('courseid', $course, databaseCore::PTINT, $qxa);
		return($dbcore->launchQueryMultiple($table, $column, $qxa));
	}

	// Inserts a student/course pair.
	public function insertStudentclass($student, $course)
	{
		global $dbcore;
		$table = $this->tablebase . '.studentclass';
		$qxa = $dbcore->buildArray('studentid', $student, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('courseid', $course, databaseCore::PTINT, $qxa);
		return($dbcore->launchInsert($table, $qxa));
	}

	// Deletes a student/course pair.
	public function deleteStudentclass($student, $course)
	{
		global $dbcore;
		$table = $this->tablebase . '.studentclass';
		$qxa = $dbcore->buildArray('studentid', $student, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('courseid', $course, databaseCore::PTINT, $qxa);
		return($dbcore->launchDeleteMultiple($table, $qxa));
	}

	// Deletes all courses a student is enrolled in.
	public function deleteStudentclassStudent($student)
	{
		global $dbcore;
		$table = $this->tablebase . '.studentclass';
		return($dbcore->launchDeleteSingle($table, 'studentid', $student,
			databaseCore::PTINT));
	}

	// Deletes all students from a course.
	public function deleteStudentclassCourse($course)
	{
		global $dbcore;
		$table = $this->tablebase . '.studentclass';
		return($dbcore->launchDeleteSingle($table, 'courseid', $course,
			databaseCore::PTINT));
	}


	/* ******** TURNIN TABLE ******** */

	/* This table contains information about when a student turns in an
	   assignment. */

	// Queries all turnins that a student made against an assignment.
	public function queryTurninStudentAssignAll($student, $assign)
	{
		global $dbcore;
		$table = $this->tablebase . '.turnin';
		$column = '*';
		$qxa = $dbcore->buildArray('studentid', $student, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('assignment', $assign, databaseCore::PTINT, $qxa);
		return($dbcore->launchQueryMultiple($table, $column, $qxa));
	}

	// Inserts a student's turned in assignment.
	public function insertTurnin($student, $assign, $step, $course, $timedate)
	{
		global $dbcore;
		$table = $this->tablebase . '.turnin';
		$qxa = $dbcore->buildArray('studentid', $student, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('assignment', $assign, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('step', $step, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('course', $course, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('timedate', $timedate, databaseCore::PTINT, $qxa);
		return($dbcore->launchInsert($table, $qxa));
	}

	// Deletes all student assignment submissions relating to an assignment and course.
	public function deleteTurninStudentAssign($student, $assign, $course)
	{
		global $dbcore;
		$table = $this->tablebase . '.turnin';
		$qxa = $dbcore->buildArray('studentid', $student, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('assignment', $assign, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('course', $course, databaseCore::PTINT, $qxa);
		return($dbcore->launchDeleteMultiple($table, $qxa));
	}

	// Deletes student assignment submissions for a particular step.
	public function deleteTurninStudentStep($student, $assign, $course, $step)
	{
		global $dbcore;
		$table = $this->tablebase . '.turnin';
		$qxa = $dbcore->buildArray('studentid', $student, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('assignment', $assign, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('course', $course, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('step', $step, databaseCore::PTINT, $qxa);
		return($dbcore->launchDeleteMultiple($table, $qxa));
	}

	// Deletes student assignment submissions for a particular submission.
	public function deleteTurninStudentCount($student, $assign, $course, $step, $count)
	{
		global $dbcore;
		$table = $this->tablebase . '.turnin';
		$qxa = $dbcore->buildArray('studentid', $student, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('assignment', $assign, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('course', $course, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('step', $step, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('subcount', $count, databaseCore::PTINT, $qxa);
		return($dbcore->launchDeleteMultiple($table, $qxa));
	}

	// Deletes all student assignment submissions for all courses.
	// Generally used when deleting a user.
	public function deleteTurninStudentAll($student)
	{
		global $dbcore;
		$table = $this->tablebase . '.turnin';
		return($dbcore->launchDeleteSingle($table, 'studentid', $student,
			databaseCore::PINT));
	}


	/* ******** WEIGHTGROUP TABLE ******** */

	/* This table defines a particular weight group.  What this means is that
	   assignments can be grouped together and assigned a weight group that is
	   collectively a percentage of the total grade. */

	// Queries a weightgroup.
	public function queryWeightgroup($group, $instruct)
	{
		global $dbcore;
		$table = $this->tablebase . '.weightgroup';
		$column = '*';
		$qxa = $dbcore->buildArray('group', $group, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('instructor', $instruct, databaseCore::PTINT, $qxa);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Queries all weightgroups that an instructor owns.
	public function queryWeightgroupInstructAll($instruct)
	{
		global $dbcore;
		$table = $this->tablebase . '.weightgroup';
		$column = '*';
		$qxa = $dbcore->buildArray('instructor', $instruct, databaseCore::PTINT);
		return($dbcore->launchQueryMultiple($table, $column, $qxa));
	}

	// Updates a weightgroup.
	public function updateWeightGroup($group, $weight, $desc, $name)
	{
		global $dbcore;
		$table = $this->tablebase . '.weightgroup';
		$qxa = $dbcore->buildArray('weight', $weight, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('desc', $desc, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR, $qxa);
		return($dbcore->launchUpdateSingle($table, 'group', $group,
			databaseCore::PTINT, $qxa));
	}

	// Inserts a new weightgroup.
	public function insertWeightgroup($instruct, $weight, $desc, $name)
	{
		global $dbcore;
		$table = $this->tablebase . '.weightgroup';
		$qxa = $dbcore->buildArray('instructor', $instruct, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('weight', $weight, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('desc', $desc, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR, $qxa);
		return($dbcore->launchInsert($table, $qxa));
	}

	// Deletes a weightgroup.
	public function deleteWeightgroup($group, $instruct)
	{
		global $dbcore;
		$table = $this->tablebase . '.weightgroup';
		$qxa = $dbcore->buildArray('group', $group, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('instructor', $instruct, databaseCore::PTINT, $qxa);
		return($dbcore->launchDeleteMultiple($table, $qxa));
	}



}

// Auto-instantiate the class
$dbapp = new database_application();


?>