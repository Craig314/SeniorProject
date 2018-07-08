<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

Accounts Library


*/


require_once "confload.php";


interface accountInterface
{
	public function checkSpecialAccount();
	public function checkSpecialProfile();
	public function checkAccountVendor();
	public function checkAccountAdmin();
	public function checkAccountSpecial();
	public function checkCredentials();
}


class account implements accountInterface
{

	// Checks to see if the current user is on a special account.
	// Returns true if the account is special.  False if not.
	function checkSpecialAccount()
	{
		global $CONFIGVAR;

		$userId = $_SESSION['userId'];
		if ($userId == $CONFIGVAR['account_id_vendor']['value']) return true;
		if ($userId == $CONFIGVAR['account_id_admin']['value']) return true;
		return false;
	}


	// Checks to see if the profile id of the current user
	// corresponds to a special account.  Returns true if
	// the profile id is special, false if not.
	function checkSpecialProfile()
	{
		global $CONFIGVAR;

		$profId = $_SESSION['profileId'];
		if ($profId == $CONFIGVAR['profile_id_vendor']['value']) return true;
		if ($profId == $CONFIGVAR['profile_id_admin']['value']) return true;
		return false;
	}

	// Checks to see if the current user is the vendor account.
	// Returns true if the account is the vendor account.
	function checkAccountVendor()
	{
		global $CONFIGVAR;

		$userId = $_SESSION['userId'];
		$profId = $_SESSION['profileId'];
		if ($userId == $CONFIGVAR['account_id_vendor']['value']
			&& $profId == $CONFIGVAR['profile_id_vendor']['value'])
			return true;
		return false;
	}

	// Checks to see if the current user is the admin account.
	// Returns true if the account is the admin account, false
	// if not.
	function checkAccountAdmin()
	{
		global $CONFIGVAR;

		$userId = $_SESSION['userId'];
		$profId = $_SESSION['profileId'];
		if ($userId == $CONFIGVAR['account_id_admin']['value']
			&& $profId == $CONFIGVAR['profile_id_admin']['value'])
			return true;
		return false;
	}

	// Checks to see if the current user is on any kind of special
	// account.  Note that the user ID and the profile ID must
	// match up.
	public function checkAccountSpecial($session = true, $userid = 0, $profid = 0)
	{
		global $CONFIGVAR;

		if ($session)
		{
			if ($this->checkAccountVendor()) return true;
			if ($this->checkAccountAdmin()) return true;
		}
		else
		{
			if ($userid == $CONFIGVAR['account_id_admin']['value'] &&
				$profid == $CONFIGVAR['profile_id_admin']['value'])
				return true;
			if ($userid == $CONFIGVAR['account_id_vendor']['value'] &&
				$profid == $CONFIGVAR['profile_id_vendor']['value'])
				return true;
		}
		return false;
	}

	// Checks the credentials from the supergloabal variable
	// $_SESSION.  Returns true if everything is ok, false if
	// there is a problem.
	public function checkCredentials()
	{
		if (isset($_SESSION))
		{
			// Check Login Status
			if (isset($_SESSION['login']))
			{
				if ($_SESSION['login'] != true) return false;
			}
			else return false;
			// Check for userName
			if (isset($_SESSION['nameUser']))
			{
				if ($_SESSION['nameUser'] === false) return false;
			}
			else return false;

			// Check for userId
			if (isset($_SESSION['userId']))
			{
				if ($_SESSION['userId'] === false) return false;
			}
			else return false;

			// Check for profileId
			if (isset($_SESSION['profileId']))
			{
				if ($_SESSION['profileId'] === false) return false;
			}
			else return false;
		}
		else return false;
		return true;
	}

}


// Automatically instantiate the class
$account = new account();


?>