<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application Application Database Driver


*/


require_once '../libs/database.php';


interface database_application_interface
{
}


class database_application implements database_application_interface
{

	private $tablebase = APP_DATABASE_APPLICATION;



	/* ********   ******** */

	/* */

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
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Updates an assignment.
	public function updateAssignment($assign, $desc, $descfile, $duedate,
		$lockdate, $grdw, $grdwgrp)
	{
		global $dbcore;
		$table = $this->tablebase . '.assignment';
		$qxa = $dbcore->buildArray('desc', $desc, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('descfile', $descfile, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('duedate', $duedate, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('lockdate', $lockdate, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('gradeweight', $grdw, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('gwgroup', $grdwgrp, databaseCore::PTINT);
		return($dbcore->launchUpdateSingle($table, 'assignment', $assign,
			databaseCore::PT, $qxa));
	}

	// Insert
	public function insert($course, $desc, $descfile, $duedate, $lockdate,
		$grdw, $grdwgrp)
	{
		global $dbcore;
		$table = $this->tablebase . '.assignment';
		$qxa = $dbcore->buildArray('course', $course, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('', $desc, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('', $descfile, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('', $duedate, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('', $lockdate, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('', $grdw, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('', $grdwgrp, databaseCore::PTINT, $qxa);
		return($dbcore->launchInsertSingle($table, $qxa));
	}

	// Delete
	public function delete()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchDeleteSingle($table, '', $,
			databaseCore::PT, $qxa));
	}


	/* ********   ******** */

	/* */

	// Query
	public function query()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$column = '*';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Query all
	public function queryAll()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$column = '*';
		return($dbcore->launchQueryDumpTable($table, $column));
	}

	// Update
	public function update()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchUpdateSingle($table, '', $,
			databaseCore::PT, $qxa));
	}

	// Insert
	public function insert()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchInsertSingle($table, $qxa));
	}

	// Delete
	public function delete()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchDeleteSingle($table, '', $,
			databaseCore::PT, $qxa));
	}


	/* ********   ******** */

	/* */

	// Query
	public function query()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$column = '*';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Query all
	public function queryAll()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$column = '*';
		return($dbcore->launchQueryDumpTable($table, $column));
	}

	// Update
	public function update()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchUpdateSingle($table, '', $,
			databaseCore::PT, $qxa));
	}

	// Insert
	public function insert()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchInsertSingle($table, $qxa));
	}

	// Delete
	public function delete()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchDeleteSingle($table, '', $,
			databaseCore::PT, $qxa));
	}


	/* ********   ******** */

	/* */

	// Query
	public function query()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$column = '*';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Query all
	public function queryAll()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$column = '*';
		return($dbcore->launchQueryDumpTable($table, $column));
	}

	// Update
	public function update()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchUpdateSingle($table, '', $,
			databaseCore::PT, $qxa));
	}

	// Insert
	public function insert()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchInsertSingle($table, $qxa));
	}

	// Delete
	public function delete()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchDeleteSingle($table, '', $,
			databaseCore::PT, $qxa));
	}


	/* ********   ******** */

	/* */

	// Query
	public function query()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$column = '*';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Query all
	public function queryAll()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$column = '*';
		return($dbcore->launchQueryDumpTable($table, $column));
	}

	// Update
	public function update()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchUpdateSingle($table, '', $,
			databaseCore::PT, $qxa));
	}

	// Insert
	public function insert()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchInsertSingle($table, $qxa));
	}

	// Delete
	public function delete()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchDeleteSingle($table, '', $,
			databaseCore::PT, $qxa));
	}


	/* ********   ******** */

	/* */

	// Query
	public function query()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$column = '*';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Query all
	public function queryAll()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$column = '*';
		return($dbcore->launchQueryDumpTable($table, $column));
	}

	// Update
	public function update()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchUpdateSingle($table, '', $,
			databaseCore::PT, $qxa));
	}

	// Insert
	public function insert()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchInsertSingle($table, $qxa));
	}

	// Delete
	public function delete()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchDeleteSingle($table, '', $,
			databaseCore::PT, $qxa));
	}


	/* ********   ******** */

	/* */

	// Query
	public function query()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$column = '*';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Query all
	public function queryAll()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$column = '*';
		return($dbcore->launchQueryDumpTable($table, $column));
	}

	// Update
	public function update()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchUpdateSingle($table, '', $,
			databaseCore::PT, $qxa));
	}

	// Insert
	public function insert()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchInsertSingle($table, $qxa));
	}

	// Delete
	public function delete()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchDeleteSingle($table, '', $,
			databaseCore::PT, $qxa));
	}


	/* ********   ******** */

	/* */

	// Query
	public function query()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$column = '*';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Query all
	public function queryAll()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$column = '*';
		return($dbcore->launchQueryDumpTable($table, $column));
	}

	// Update
	public function update()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchUpdateSingle($table, '', $,
			databaseCore::PT, $qxa));
	}

	// Insert
	public function insert()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchInsertSingle($table, $qxa));
	}

	// Delete
	public function delete()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchDeleteSingle($table, '', $,
			databaseCore::PT, $qxa));
	}


	/* ********   ******** */

	/* */

	// Query
	public function query()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$column = '*';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Query all
	public function queryAll()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$column = '*';
		return($dbcore->launchQueryDumpTable($table, $column));
	}

	// Update
	public function update()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchUpdateSingle($table, '', $,
			databaseCore::PT, $qxa));
	}

	// Insert
	public function insert()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchInsertSingle($table, $qxa));
	}

	// Delete
	public function delete()
	{
		global $dbcore;
		$table = $this->tablebase . '.';
		$qxa = $dbcore->buildArray('', $, databaseCore::PT);
		return($dbcore->launchDeleteSingle($table, '', $,
			databaseCore::PT, $qxa));
	}



}

// Auto-instantiate the class
$dbapp = new database_application();


?>