<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application User Database Driver

*/


require_once 'database.php';


interface database_userdata_interface
{
	// Table: contact
	public function queryContact($userid);
	public function queryContactAll();
	public function updateContact($userid, $name, $haddr, $maddr, $email,
		$hphone, $wphone, $cphone);
	public function insertContact($userid, $name, $haddr, $maddr, $email,
		$hphone, $wphone, $cphone);
	public function deleteContact($userid);

	// Table: login
	public function queryLogin($userid);
	public function updateLoginPassword($userid, $change, $digest, $count, $salt, $passwd);
	public function updateLoginLockout($userid, $lock, $locktime);
	public function updateLoginFail($userid, $failcount);
	public function updateLoginLastlog($userid, $lastlog);
	public function updateLogin($userid, $lock, $locktime, $failcount,
		$lastlog, $timeout, $digest, $count, $salt, $passwd);
	public function insertLogin($userid, $lock, $locktime, $failcount,
		$lastlog, $timeout, $digest, $count, $salt, $passwd);
	public function deleteLogin($userid);

	// Table: oath
	public function queryOAuth($userid);
	public function queryOAuthProvAll($provider);
	public function updateOAuth($userid, $state, $provider, $token, $tokentype,
		$issue, $expire, $refresh, $scope, $challenge);
	public function updateOAuthLogin($userid, $state, $token, $tokentype,
		$issue, $expire, $refresh, $scope, $challenge);
	public function updateOAuthProvider($userid, $provider);
	public function updateOAuthState($userid, $state);
	public function updateOAuthChallenge($userid, $challenge);
	public function insertOAuth($userid, $state, $provider, $token, $tokentype,
		$issue, $expire, $refresh, $scope, $challenge);
	public function deleteOAuth($userid);

	// Table: openid
	public function queryOpenId($userid);
	public function queryOpenIdState($state);
	public function queryOpenIdProvAll($provider);
	public function updateOpenId($userid, $provider, $ident, $handle,
		$invalid, $nonce, $issue, $expire);
	public function updateOpenIdLogin($userid, $handle, $invalid, $nonce,
		$issue, $expire);
	public function insertOpenId($userid, $provider, $ident);
	public function deleteOpenId($userid);

	// Table: users
	public function queryUsers($username);
	public function queryUsersUserId($userid);
	public function queryUsersProfId($profid);
	public function queryUsersAll();
	public function updateUsers($username, $userid, $profid, $method, $active, $orgid);
	public function updateUsersActive($userid, $active);
	public function insertUsers($username, $userid, $profid, $method, $active, $orgid);
	public function deleteUsers($userid);
}


class database_user implements database_userdata_interface
{

	private $tablebase = APP_DATABASE_USERDATA;


	/* ******** CONTACT TABLE ******** */

	/* The contact table contains the contact information for the
	   users.  Not many fields here, but the application should
	   parse the field data if separate fields are needed for first
	   name, last name, etc... */

	// Returns the contact information for the specified user ID.
	public function queryContact($userid)
	{
		global $dbcore;
		$table = $this->tablebase . '.contact';
		$column = '*';
		$qxa = $dbcore->buildArray('userid', $userid, databaseCore::PTINT);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Returns all data in the contact table.
	public function queryContactAll()
	{
		global $dbcore;
		$table = $this->tablebase . '.contact';
		$column = '*';
		return($dbcore->launchQueryDumpTable($table, $column));
	}
	
	// Updates the contact information for the specified user ID.
	public function updateContact($userid, $name, $haddr, $maddr, $email,
		$hphone, $wphone, $cphone)
	{
		global $dbcore;
		$table = $this->tablebase . '.contact';
		$qxk = $dbcore->buildArray('userid', $userid, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR);
		$qxa = $dbcore->buildArray('haddr', $haddr, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('maddr', $maddr, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('email', $email, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('hphone', $hphone, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('cphone', $cphone, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('wphone', $wphone, databaseCore::PTSTR, $qxa);
		return($dbcore->launchUpdateMultiple($table, $qxk, $qxa));
	}

	// Inserts new contact information.
	public function insertContact($userid, $name, $haddr, $maddr, $email,
		$hphone, $wphone, $cphone)
	{
		global $dbcore;
		$table = $this->tablebase . '.contact';
		$qxa = $dbcore->buildArray('userid', $userid, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('haddr', $haddr, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('maddr', $maddr, databaseCore::PTSTR, $qxa);
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
		$table = $this->tablebase . '.contact';
		return($dbcore->launchDeleteSingle($table, 'userid', $userid, databaseCore::PTINT));
	}



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
		$table = $this->tablebase . '.login';
		$column = '*';
		$qxa = $dbcore->buildArray('userid', $userid, databaseCore::PTINT);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Updates the fields associated with the user's password.
	public function updateLoginPassword($userid, $timeout, $digest, $count, $salt, $passwd)
	{
		global $dbcore;
		$table = $this->tablebase . '.login';
		$qxa = $dbcore->buildArray('timeout', $timeout, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('digest', $digest, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('count', $count, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('salt', $salt, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('passwd', $passwd, databaseCore::PTSTR, $qxa);
		return($dbcore->launchUpdateSingle($table, 'userid', $userid, databaseCore::PTINT, $qxa));
	}

	// Updates the account lockout status.
	public function updateLoginLockout($userid, $lock, $locktime)
	{
		global $dbcore;
		$table = $this->tablebase . '.login';
		$qxa = $dbcore->buildArray('locked', $lock, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('locktime', $locktime, databaseCore::PTINT, $qxa);
		return($dbcore->launchUpdateSingle($table, 'userid', $userid, databaseCore::PTINT, $qxa));
	}

	// Updates the login fail counter.
	public function updateLoginFail($userid, $failcount)
	{
		global $dbcore;
		$table = $this->tablebase . '.login';
		$qxa = $dbcore->buildArray('failcount', $failcount, databaseCore::PTINT);
		return($dbcore->launchUpdateSingle($table, 'userid', $userid, databaseCore::PTINT, $qxa));
	}

	// Updates the last successful login field.
	public function updateLoginLastlog($userid, $lastlog)
	{
		global $dbcore;
		$table = $this->tablebase . '.login';
		$qxa = $dbcore->buildArray('lastlog', $lastlog, databaseCore::PTINT);
		return($dbcore->launchUpdateSingle($table, 'userid', $userid, databaseCore::PTINT, $qxa));
	}

	// Updates all fields.
	public function updateLogin($userid, $lock, $locktime, $failcount,
		$lastlog, $timeout, $digest, $count, $salt, $passwd)
	{
		global $dbcore;
		$table = $this->tablebase . '.login';
		$qxa = $dbcore->buildArray('locked', $lock, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('locktime', $locktime, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('lastlog', $lastlog, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('failcount', $failcount, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('timeout', $timeout, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('digest', $digest, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('count', $count, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('salt', $salt, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('passwd', $passwd, databaseCore::PTSTR, $qxa);
		return($dbcore->launchUpdateSingle($table, 'userid', $userid, databaseCore::PTINT, $qxa));
	}

	// Inserts a new user.
	public function insertLogin($userid, $lock, $locktime, $failcount,
		$lastlog, $timeout, $digest, $count, $salt, $passwd)
	{
		global $dbcore;
		$table = $this->tablebase . '.login';
		$qxa = $dbcore->buildArray('userid', $userid, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('locked', $lock, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('locktime', $locktime, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('lastlog', $lastlog, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('failcount', $failcount, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('timeout', $timeout, databaseCore::PTINT, $qxa);
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
		$table = $this->tablebase . '.login';
		return($dbcore->launchDeleteSingle($table, 'userid', $userid, databaseCore::PTINT));
	}


	/* ******** OAUTH TABLE ******** */

	/* The OAuth table contains the user's login informatioin given
	   by the OAuth provider. */
	
	// Queries the OAuth information about a specific user.
	public function queryOAuth($userid)
	{
		global $dbcore;
		$table = $this->tablebase . '.oauth';
		$column = '*';
		$qxa = $dbcore->buildArray('userid', $userid, databaseCore::PTINT);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Multi-Query that returns all user IDs which are using the
	// specified provider.
	public function queryOAuthProvAll($provider)
	{
		global $dbcore;
		$table = $this->tablebase . '.oauth';
		$column = 'userid,provider';
		$qxa = $dbcore->buildArray('provider', $provider, databaseCore::PTINT);
		return($dbcore->launchQueryMultiple($table, $column, $qxa));
	}

	public function updateOAuth($userid, $state, $provider, $token, $tokentype,
		$issue, $expire, $refresh, $scope, $challenge)
	{
		global $dbcore;
		$table = $this->tablebase . '.oauth';
		$qxa = $dbcore->buildArray('state', $state, databaseCore::PTSTR);
		$qxa = $dbcore->buildArray('provider', $provider, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('token', $token, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('tokentype', $tokentype, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('issue', $issue, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('expire', $expire, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('refresh', $refresh, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('scope', $scope, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('challenge', $challenge, databaseCore::PTSTR, $qxa);
		return($dbcore->launchUpdateSingle($table, 'userid', $userid, databaseCore::PTINT, $qxa));
	}

	public function updateOAuthLogin($userid, $state, $token, $tokentype,
		$issue, $expire, $refresh, $scope, $challenge)
	{
		global $dbcore;
		$table = $this->tablebase . '.oauth';
		$qxa = $dbcore->buildArray('state', $state, databaseCore::PTSTR);
		$qxa = $dbcore->buildArray('token', $token, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('tokentype', $tokentype, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('issue', $issue, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('expire', $expire, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('refresh', $refresh, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('scope', $scope, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('challenge', $challenge, databaseCore::PTSTR, $qxa);
		return($dbcore->launchUpdateSingle($table, 'userid', $userid, databaseCore::PTINT, $qxa));
	}

	public function updateOAuthProvider($userid, $provider)
	{
		global $dbcore;
		$table = $this->tablebase . '.oauth';
		$qxa = $dbcore->buildArray('provider', $provider, databaseCore::PTINT);
		return($dbcore->launchUpdateSingle($table, 'userid', $userid, databaseCore::PTINT, $qxa));
	}

	public function updateOAuthState($userid, $state)
	{
		global $dbcore;
		$table = $this->tablebase . '.oauth';
		$qxa = $dbcore->buildArray('state', $state, databaseCore::PTSTR);
		return($dbcore->launchUpdateSingle($table, 'userid', $userid, databaseCore::PTINT, $qxa));
	}

	public function updateOAuthChallenge($userid, $challenge)
	{
		global $dbcore;
		$table = $this->tablebase . '.oauth';
		$qxa = $dbcore->buildArray('challenge', $challenge, databaseCore::PTSTR);
		return($dbcore->launchUpdateSingle($table, 'userid', $userid, databaseCore::PTINT, $qxa));
	}

	public function updateOAuthToken($userid, $token, $issue, $expire, $tokentype)
	{
		global $dbcore;
		$table = $this->tablebase . '.oauth';
		$qxa = $dbcore->buildArray('token', $token, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('tokentype', $tokentype, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('issue', $issue, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('expire', $expire, databaseCore::PTINT, $qxa);
		return($dbcore->launchUpdateSingle($table, 'userid', $userid, databaseCore::PTINT, $qxa));
	}

	public function insertOAuth($userid, $state, $provider, $token, $tokentype,
		$issue, $expire, $refresh, $scope, $challenge)
	{
		global $dbcore;
		$table = $this->tablebase . '.oauth';
		$qxa = $dbcore->buildArray('userid', $userid, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('state', $state, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('provider', $provider, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('token', $token, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('tokentype', $tokentype, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('issue', $issue, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('expire', $expire, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('refresh', $refresh, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('scope', $scope, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('challenge', $challenge, databaseCore::PTSTR, $qxa);
		return($dbcore->launchInsert($table, $qxa));
	}

	public function deleteOAuth($userid)
	{
		global $dbcore;
		$table = $this->tablebase . '.oauth';
		return($dbcore->launchDeleteSingle($table, 'userid', $userid, databaseCore::PTINT));
	}



	/* ******** OPENID TABLE ******** */

	/* The OpenID table provides information about the user's OpenID
	   login stauts. */
	
	// Queries a user's OpenID data.
	public function queryOpenId($userid)
	{
		global $dbcore;
		$table = $this->tablebase . '.openid';
		$column = '*';
		$qxa = $dbcore->buildArray('userid', $userid, databaseCore::PTINT);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Queries a user's OpenID data according to state.
	public function queryOpenIdState($state)
	{
		global $dbcore;
		$table = $this->tablebase . '.openid';
		$column = '*';
		$qxa = $dbcore->buildArray('state', $state, databaseCore::PTSTR);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Multi-Query that returns all user IDs which are using the
	// specified provider.
	public function queryOpenIdProvAll($provider)
	{
		$table = $this->tablebase . '.openid';
		$column = 'userid,provider';
		$qxa = $dbcore->buildArray('provider', $provider, databaseCore::PTINT);
		return($dbcore->launchQueryMultiple($table, $column, $qxa));
	}

	// Updates a user's OpenID data.
	public function updateOpenId($userid, $provider, $ident, $handle,
		$invalid, $nonce, $issue, $expire)
	{
		global $dbcore;
		$table = $this->tablebase . '.openid';
		$qxa = $dbcore->buildArray('provider', $provider, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('ident', $ident, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('handle', $handle, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('invalid', $invalid, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('issue', $issue, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('expire', $expire, databaseCore::PTINT, $qxa);
		return($dbcore->launchUpdateSingle($table, 'userid', $userid,
			databaseCore::PTINT, $qxa));
	}

	// Updates just the user's OpenID login status.
	public function updateOpenIdLogin($userid, $handle, $invalid, $nonce,
		$issue, $expire)
	{
		global $dbcore;
		$table = $this->tablebase . '.openid';
		$qxa = $dbcore->buildArray('handle', $handle, databaseCore::PTSTR);
		$qxa = $dbcore->buildArray('invalid', $invalid, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('nonce', $nonce, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('issue', $issue, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('expire', $expire, databaseCore::PTINT, $qxa);
		return($dbcore->launchUpdateSingle($table, 'userid', $userid,
			databaseCore::PTINT, $qxa));
	}

	// Inserts the OpenID data for a user.
	public function insertOpenId($userid, $provider, $ident)
	{
		global $dbcore;
		$table = $this->tablebase . '.openid';
		$qxa = $dbcore->buildArray('userid', $userid, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('provider', $provider, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('ident', $ident, databaseCore::PTSTR, $qxa);
		// The rest of this is default values as they are set when
		// the user logs in via OpenID.
		$qxa = $dbcore->buildArray('handle', '', databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('invalid', '', databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('nonce', '', databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('issue', 0, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('expire', 0, databaseCore::PTINT, $qxa);
		return($dbcore->launchInsert($table, $qxa));
	}

	// Delete's the OpenID data for a user.
	public function deleteOpenId($userid)
	{
		global $dbcore;
		$table = $this->tablebase . '.openid';
		return($dbcore->launchDeleteSingle($table, 'userid', $userid, databaseCore::PTINT));
	}




	/* ******** USERS TABLE ******** */

	/* The users table maps the username to the user ID and profile ID. */

	// Queries a specific username.
	public function queryUsers($username)
	{
		global $dbcore;
		$table = $this->tablebase . '.users';
		$column = '*';
		$qxa = $dbcore->buildArray('username', $username, databaseCore::PTSTR);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Queries a user by their user ident number
	public function queryUsersUserId($userid)
	{
		global $dbcore;
		$table = $this->tablebase . '.users';
		$column = '*';
		$qxa = $dbcore->buildArray('userid', $userid, databaseCore::PTINT);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Queries none, one, or more users by the profile ident number.
	public function queryUsersProfId($profid)
	{
		global $dbcore;
		$table = $this->tablebase . '.users';
		$column = '*';
		$qxa = $dbcore->buildArray('profileid', $profid, databaseCore::PTINT);
		return($dbcore->launchQueryMultiple($table, $column, $qxa));
	}
	
	// Queries all users existing in the table.
	public function queryUsersAll()
	{
		global $dbcore;
		$table = $this->tablebase . '.users';
		$column = '*';
		return($dbcore->launchQueryDumpTable($table, $column));
	}

	// Updates the users table for the specified username.
	public function updateUsers($username, $userid, $profid, $method, $active, $orgid)
	{
		global $dbcore;
		$table = $this->tablebase . '.users';
		$qxa = $dbcore->buildArray('username', $username, databaseCore::PTSTR);
		$qxa = $dbcore->buildArray('profileid', $profid, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('method', $method, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('active', $active, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('orgid', $orgid, databaseCore::PTSTR, $qxa);
		return($dbcore->launchUpdateSingle($table, 'userid', $userid, databaseCore::PTINT, $qxa));
	}	

	// Updates the account's active status.
	public function updateUsersActive($userid, $active)
	{
		global $dbcore;
		$table = $this->tablebase . '.users';
		$qxa = $dbcore->buildArray('active', $active, databaseCore::PTINT);
		return($dbcore->launchUpdateSingle($table, 'userid', $userid, databaseCore::PTINT, $qxa));
	}

	// Inserts a user.
	public function insertUsers($username, $userid, $profid, $method, $active, $orgid)
	{
		global $dbcore;
		$table = $this->tablebase . '.users';
		$qxa = $dbcore->buildArray('userid', $userid, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('username', $username, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('profileid', $profid, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('method', $method, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('active', $active, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('orgid', $orgid, databaseCore::PTSTR, $qxa);
		return($dbcore->launchInsert($table, $qxa));
	}	

	// Deletes a user.
	public function deleteUsers($userid)
	{
		global $dbcore;
		$table = $this->tablebase . '.users';
		return($dbcore->launchDeleteSingle($table, 'userid', $userid, databaseCore::PTINT));
	}
}


// Automatically instantiate the class
$dbuser = new database_user();


?>