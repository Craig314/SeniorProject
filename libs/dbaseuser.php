<?php

/*

PHP Web Application User Database Driver

*/

require_once 'database.php';


interface database_userdata_interface
{
	// Table: contact
	// Table: login
	// Table: users
	public function queryUsers($username);
	public function queryUsersAll();
	public function updateUsers($username, $userid, $profid);
	public function insertUsers($username, $userid, $profid);
	public function deleteUsers($username);
}

class database_user implements database_userdata_interface
{

	private $tablebase = 'userdata';


	/* ******** LOGIN TABLE ******** */

	/* The login table contains all the information that is needed
	   to process the user's login credentials, account status, and
	   lockout status.  Note that there is no method to dump this
	   table as there is no need for it, and it would pose a security
	   risk anyways. */

	// Queries a single login entry.
	public function queryLogin($userid)
	{
		global $dbcore;
		$table = $tablebase . 'login';
		$column = '*';
		$qxa = $dbcore->buildArray('userid', $userid, databaseCore::PT);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Updates the fields associated with the user's password.
	public function updateLoginPassword($userid, $change, $digest, $count, $salt, $passwd)
	{
		global $dbcore;
		$table = $tablebase . 'login';
		$qxa = $dbcore->buildArray('change', $change, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('digest', $digest, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('count', $count, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('salt', $salt, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('passwd', $passwd, databaseCore::PTSTR, $qxa);
		return($dbcore->launchUpdateSingle($table, 'userid', $userid, databaseCore::PTINT, $qxa));
	}

	// Updates the account's active status.
	public function updateLoginActive($userid, $active)
	{
		global $dbcore;
		$table = $tablebase . 'login';
		$qxa = $dbcore->buildArray('active', $active, databaseCore::PTINT);
		return($dbcore->launchUpdateSingle($table, 'userid', $userid, databaseCore::PTINT, $qxa));
	}

	// Updates the account lockout status.
	public function updateLoginLockout($userid, $lock, $locktime)
	{
		global $dbcore;
		$table = $tablebase . 'login';
		$qxa = $dbcore->buildArray('locked', $lock, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('locktime', $locktime, databaseCore::PTINT, $qxa);
		return($dbcore->launchUpdateSingle($table, 'userid', $userid, databaseCore::PTINT, $qxa));
	}

	// Updates the login fail counter.
	public function updateLoginFail($userid, $failcount)
	{
		global $dbcore;
		$table = $tablebase . 'login';
		$qxa = $dbcore->buildArray('failcount', $failcount, databaseCore::PTINT);
		return($dbcore->launchUpdateSingle($table, 'userid', $userid, databaseCore::PTINT, $qxa));
	}

	// Updates the last successful login field.
	public function updateLoginLastlog($userid, $lastlog)
	{
		global $dbcore;
		$table = $tablebase . 'login';
		$qxa = $dbcore->buildArray('lastlog', $lastlog, databaseCore::PTINT);
		return($dbcore->launchUpdateSingle($table, 'userid', $userid, databaseCore::PTINT, $qxa));
	}

	// Updates all fields.
	public function updateLogin($userid, $active, $lock, $locktime, $failcount, $lastlog, $change, $digest, $count, $salt, $pass)
	{
		global $dbcore;
		$table = $tablebase . 'login';
		$qxa = $dbcore->buildArray('active', $active, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('locked', $lock, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('locktime', $locktime, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('lastlog', $lastlog, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('failcount', $failcount, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('change', $change, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('digest', $digest, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('count', $count, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('salt', $salt, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('passwd', $passwd, databaseCore::PTSTR, $qxa);
		return($dbcore->launchUpdateSingle($table, 'userid', $userid, databaseCore::PTINT, $qxa));
	}

	// Inserts a new user.
	public function insertLogin($userid, $active, $lock, $locktime, $failcount, $lastlog, $change, $digest, $count, $salt, $pass)
	{
		global $dbcore;
		$table = $tablebase . 'login';
		$qxa = $dbcore->buildArray('userid', $userid, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('active', $active, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('locked', $lock, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('locktime', $locktime, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('lastlog', $lastlog, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('failcount', $failcount, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('change', $change, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('digest', $digest, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('count', $count, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('salt', $salt, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('passwd', $passwd, databaseCore::PTSTR, $qxa);
		return($dbcore->launchInsert($table, $qxa));
	}

	// Deletes the specified user.
	public function deleteLogin($userid)
	{
		global $dbcore;
		$table = $tablebase . 'login';
		return($dbcore->launchDeleteSingle($table, 'userid', $userid, databaseCore::PTINT));
	}



	/* ******** CONTACT TABLE ******** */

	/* The contact table contains the contact information for the
	   users.  Not many fields here, but the application should
	   parse the field data if separate fields are needed for first
	   name, last name, etc... */

	// Returns the contact information for the specified user ID.
	public function queryContact($userid)
	{
		global $dbcore;
		$table = $tablebase . '.contact';
		$column = '*';
		$qxa = $dbcore->buildArray('userid', $userid, databaseCore::PTINT);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Updates the contact information for the specified user ID.
	public function updateContact($userid, $name, $address, $email, $hphone, $wphone, $cphone)
	{
		global $dbcore;
		$table = $tablebase . '.contact';
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR);
		$qxa = $dbcore->buildArray('address', $address, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('email', $email, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('hphone', $hphone, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('cphone', $cphone, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('wphone', $wphone, databaseCore::PTSTR, $qxa);
		return($dbcore->launchUpdateSingle($table, 'userid', $userid, databaseCore::PTINT, $qxa));
	}

	// Inserts new contact information.
	public function insertContact($userid, $name, $address, $email, $hphone, $wphone, $cphone)
	{
		global $dbcore;
		$table = $tablebase . '.contact';
		$qxa = $dbcore->buildArray('userid', $userid, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('address', $address, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('email', $email, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('hphone', $hphone, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('cphone', $cphone, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('wphone', $wphone, databaseCore::PTSTR, $qxa);
		return($dbcore->launchInsert($table, $qxa));
	}

	// Deletes the contact information for the specified user ID.
	public function deleteContact($userid)
	{
		global $dbcore;
		$table = $tablebase . '.contact';
		return($dbcore->launchDeleteSingle($table, 'userid', $userid, databaseCore::PTINT));
	}



	/* ******** USERS TABLE ******** */

	/* The users table maps the username to the user ID and profile ID. */

	// Queries a specific username.
	public function queryUsers($username)
	{
		global $dbcore;
		$table = $tablecore . '.users';
		$column = '*';
		$qxa = $dbcore->buildArray('username', $username, databaseCore::PTSTR);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Queries all users existing in the table.
	public function queryUsersAll()
	{
		global $dbcore;
		$table = $tablecore . '.users';
		$column = '*';
		return($dbcore->launchQueryDumpTable($table, $column));
	}

	// Updates the users table for the specified username.
	public function updateUsers($username, $userid, $profid)
	{
		global $dbcore;
		$table = $tablecore . '.users';
		$qxa = $db_core->buildArray('userid', $userid, databaseCore::PTINT);
		$qxa = $db_core->buildArray('profileid', $profid, databaseCore::PTINT, $qxa);
		return($dbcore->launchUpdateSingle($table, 'username', $username, databaseCore::PTSTR, $qxa));
	}	

	// Inserts a user.
	public function insertUsers($username, $userid, $profid)
	{
		global $dbcore;
		$table = $tablecore . '.users';
		$qxa = $db_core->buildArray('username', $username, databaseCore::PTSTR, $qxa);
		$qxa = $db_core->buildArray('userid', $userid, databaseCore::PTINT, $qxa);
		$qxa = $db_core->buildArray('profileid', $profid, databaseCore::PTINT, $qxa);
		return($dbcore->launchInsert($table, $qxa));
	}	

	// Deletes a user.
	public function deleteUsers($username)
	{
		global $dbcore;
		$table = $tablecore . '.users';
		return($dbcore->launchUpdateSingle($table, 'username', $username, databaseCore::PTSTR));
	}
}

?>