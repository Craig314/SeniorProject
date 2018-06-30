<?php

/*

PHP Web Application User Database Driver

*/

require_once 'database.php';


interface database_userdata_interface
{
	// Table: contact
	// Table: login
	// Table: modaccess
	// Table: profile
	// Table: users
}

class database_user implements database_userdata_interface
{

	private $tablebase = 'userdata';



	/* ******** USERS TABLE ******** */

	/* The users table maps  */
	public function queryUsers($username)
	{
		global $dbcore;
		$table = $tablecore . '.users';
		$column = '*';
		$qxa = $dbcore->buildArray('username', $username, databaseCore::PTSTR);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	public function queryUsersAll()
	{
		global $dbcore;
		$table = $tablecore . '.users';
		$column = '*';
		return($dbcore->launchQueryDumpTable($table, $column));
	}

	public function updateUsers($username, $userid, $profid, $appid)
	{
		global $dbcore;
		$table = $tablecore . '.users';
		$qxa = $db_core->buildArray('userid', $userid, databaseCore::PTINT);
		$qxa = $db_core->buildArray('profileid', $profid, databaseCore::PTINT, $qxa);
		$qxa = $db_core->buildArray('appid', $appid, databaseCore::PTINT, $qxa);
		return($dbcore->launchUpdateSingle($table, 'username', $username, databaseCore::PTSTR, $qxa));
	}	

	public function insertUsers($username, $userid, $profid)
	{
		global $dbcore;
		$table = $tablecore . '.users';
		$qxa = $db_core->buildArray('username', $username, databaseCore::PTSTR, $qxa);
		$qxa = $db_core->buildArray('userid', $userid, databaseCore::PTINT, $qxa);
		$qxa = $db_core->buildArray('profileid', $profid, databaseCore::PTINT, $qxa);
		$qxa = $db_core->buildArray('appid', $appid, databaseCore::PTINT, $qxa);
		return($dbcore->launchInsert($table, $qxa));
	}	

	public function deleteUsers($username)
	{
		global $dbcore;
		$table = $tablecore . '.users';
		return($dbcore->launchUpdateSingle($table, 'username', $username, databaseCore::PTSTR));
	}
}

?>