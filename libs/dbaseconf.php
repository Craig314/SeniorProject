<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application Configuration Database Driver

*/


require_once 'database.php';


interface database_config_interface
{
	// Table: config
	public function queryConfig($setting);
	public function queryConfigAllAdmin();
	public function queryConfigAll();
	public function updateConfigValue($setting, $value);
	public function updateConfigAll($settng, $type, $name, $dispname, $value, $desc, $admin);
	public function insertConfig($setting, $type, $name, $dispname, $value, $desc, $admin);
	public function deleteConfig($setting);

	//Table: flagdesc_app
	public function queryFlagdescApp($flag);
	public function queryFlagdescAppAll();
	public function updateFlagdescApp($flag, $name, $desc);
	public function insertFlagdescApp($flag, $name, $desc);
	public function deleteFlagdescApp($flag);

	// Table: flagdesc_core
	public function queryFlagdescCore($flag);
	public function queryFlagdescCoreAll();
	public function updateFlagdescCore($flag, $name, $desc);
	public function insertFlagdescCore($flag, $name, $desc);
	public function deleteFlagdescCore($flag);

	// Table: module
	public function queryModule($modid);
	public function queryModuleAll();
	public function updateModuleInfo($modid, $name, $desc, $file, $icon);
	public function updateModuleActivation($modid, $active);
	public function updateModule($modid, $name, $desc, $file, $icon, $active, $allusers, $system, $vendor);
	public function insertModule($modid, $name, $desc, $file, $icon, $active, $allusers, $system, $vendor);
	public function deleteModule($modid);

	// Table: modaccess
	public function queryModaccessProfile($profid);
	public function queryModaccess($profid, $modid);
	public function insertModaccess($profid, $modid);
	public function deleteModaccess($profid, $modid);

	// Table: oauth
	public function queryOAuth($provider);
	public function queryOAuthAll();
	public function updateOAuth($provider, $module, $expire, $clid,
		$clsecret, $scope, $authtype, $authurl, $redirecturl, $resurl1,
		$resurl2, $resurl3, $resurl4);
	public function insertOAuth($provider, $module, $expire, $clid,
		$clsecret, $scope, $authtype, $authurl, $redirecturl, $resurl1,
		$resurl2, $resurl3, $resurl4);
	public function deleteOAuth($provider);

	// Table: profile
	public function queryProfile($profid);
	public function queryProfileAll();
	public function updateProfile($profid, $name, $desc, $portal, $bmc, $bma);
	public function insertProfile($profid, $name, $desc, $portal, $bmc, $bma);
	public function deleteProfile($profid);

}


class database_config implements database_config_interface
{

	private $tablebase = 'configuration';



	/* ******** CONFIGURATION TABLE ******** */

	/* The configuration table stores all the configuration settings for the
	   application.  However, some settings are stored in the file confbase.php
	   as constants.  The settings in this file are primarilly used to setup
	   access to the database as well as some debugging functions. */


	// Query a specific parameter.
	public function queryConfig($setting)
	{
		global $dbcore;
		$table = $this->tablebase . '.config';
		$column = '*';
		$qxa = $dbcore->buildArray('setting', $setting, databaseCore::PTINT);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Query all parameters for the admin user.
	public function queryConfigAllAdmin()
	{
		global $dbcore;
		$table = $this->tablebase . '.config';
		$column = '*';
		$qxa = $dbcore->buildArray('admin', 1, databaseCore::PTINT);
		return($dbcore->launchQueryMultiple($table, $column, $qxa));
	}

	// Query all parameters.
	public function queryConfigAll()
	{
		global $dbcore;
		$table = $this->tablebase . '.config';
		$column = '*';
		return($dbcore->launchQueryDumpTable($table, $column));
	}

	// Updates a configuration parameter value.
	public function updateConfigValue($setting, $value)
	{
		global $dbcore;
		$table = $this->tablebase . '.config';
		$column = '*';
		$qxa = $dbcore->buildArray('value', $value, databaseCore::PTSTR);
		return($dbcore->launchUpdateSingle($table, 'setting', $setting, databaseCore::PTINT, $qxa));
	}

	public function updateConfigAll($settng, $type, $name, $dispname, $value, $desc, $admin)
	{
		global $dbcore;
		$table = $this->tablebase . '.config';
		$qxa = $dbcore->buildArray('type', $type, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('dispname', $dispname, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('value', $value, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('description', $desc, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('admin', $admin, databaseCore::PTINT, $qxa);
		return($dbcore->launchUpdate($table, 'setting', $setting, databaseCore::PTINT, $qxa));
	}

	// Inserts a configuration parameter.
	public function insertConfig($setting, $type, $name, $dispname, $value, $desc, $admin)
	{
		global $dbcore;
		$table = $this->tablebase . '.config';
		$qxa = $dbcore->buildArray('setting', $setting, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('type', $type, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('dispname', $dispname, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('value', $value, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('description', $desc, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('admin', $admin, databaseCore::PTINT, $qxa);
		return($dbcore->launchInsert($table, $qxa));
	}

	// Deletes a configuration parameter.
	public function deleteConfig($setting)
	{
		global $dbcore;
		$table = $this->tablebase . '.config';
		return($dbcore->launchDeleteSingle($table, 'setting', $setting, databaseCore::PTINT));
	}



	/* ******** FLAGDESC_APP TABLE ******** */

	/* The flagdesc_app table in the database is used to provide a description of
	   various permission flags within the user's profile which are used in the
	   application.  The flag must be defined here for it to show up in the
	   interface. */


	// Queries information about a specific flag.
	public function queryFlagdescApp($flag)
	{
		global $dbcore;
		$table = $this->tablebase . '.flagdesc_app';
		$column = '*';
		$qxa = $dbcore->buildArray('flag', $flag, databaseCore::PTINT);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Queries data on all flags.
	public function queryFlagdescAppAll()
	{
		global $dbcore;
		$table = $this->tablebase . '.flagdesc_app';
		$column = '*';
		return($dbcore->launchQueryDumpTable($table, $column));
	}

	// Updates information about a specific flag.
	public function updateFlagdescApp($flag, $name, $desc)
	{
		global $dbcore;
		$table = $this->tablebase . '.flagdesc_app';
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR);
		$qxa = $dbcore->buildArray('description', $desc, databaseCore::PTSTR, $qxa);
		return($dbcore->launchUpdateSingle($table, 'flag', $flag, databaseCore::PTINT, $qxa));
	}

	// Inserts data for a specific flag.
	public function insertFlagdescApp($flag, $name, $desc)
	{
		global $dbcore;
		$table = $this->tablebase . '.flagdesc_app';
		$qxa = $dbcore->buildArray('flag', $flag, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('description', $desc, databaseCore::PTSTR, $qxa);
		return($dbcore->launchInsert($table, $qxa));
	}

	// Deletes the specified flag from the database.
	public function deleteFlagdescApp($flag)
	{
		global $dbcore;
		$table = $this->tablebase . '.flagdesc_app';
		return($dbcore->launchDeleteSingle($table, 'flag', $flag, databaseCore::PTINT));
	}


	/* ******** FLAGDESC_CORE TABLE ******** */

	/* The flagdesc_core table in the database is used to provide a description of
	   various permission flags within the user's profile which are used in the
	   system core.  The flag must be defined here for it to show up in the
	   interface. */


	// Queries information about a specific flag.
	public function queryFlagdescCore($flag)
	{
		global $dbcore;
		$table = $this->tablebase . '.flagdesc_core';
		$column = '*';
		$qxa = $dbcore->buildArray('flag', $flag, databaseCore::PTINT);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Queries data on all flags.
	public function queryFlagdescCoreAll()
	{
		global $dbcore;
		$table = $this->tablebase . '.flagdesc_core';
		$column = '*';
		return($dbcore->launchQueryDumpTable($table, $column));
	}

	// Updates information about a specific flag.
	public function updateFlagdescCore($flag, $name, $desc)
	{
		global $dbcore;
		$table = $this->tablebase . '.flagdesc_core';
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR);
		$qxa = $dbcore->buildArray('description', $desc, databaseCore::PTSTR, $qxa);
		return($dbcore->launchUpdateSingle($table, 'flag', $flag, databaseCore::PTINT, $qxa));
	}

	// Inserts data for a specific flag.
	public function insertFlagdescCore($flag, $name, $desc)
	{
		global $dbcore;
		$table = $this->tablebase . '.flagdesc_core';
		$qxa = $dbcore->buildArray('flag', $flag, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('description', $desc, databaseCore::PTSTR, $qxa);
		return($dbcore->launchInsert($table, $qxa));
	}



	// Deletes the specified flag from the database.
	public function deleteFlagdescCore($flag)
	{
		global $dbcore;
		$table = $this->tablebase . '.flagdesc_core';
		return($dbcore->launchDeleteSingle($table, 'flag', $flag, databaseCore::PTINT));
	}


	/* ******** MODULE TABLE ******** */

	/* The module table defines all the modules that are available in the
	   application.  The module access list is in the userdata database.
	   The module must be defined in this table, and the user must be given
	   access to it in order for it to show up on the user's screen. */


	// Queries information about the specific module.
	public function queryModule($modid)
	{
		global $dbcore;
		$table = $this->tablebase . '.module';
		$column = '*';
		$qxa = $dbcore->buildArray('moduleid', $modid, databaseCore::PTINT);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Returns all modules in the database.
	public function queryModuleAll()
	{
		global $dbcore;
		$table = $this->tablebase . '.module';
		$column = '*';
		return($dbcore->launchQueryDumpTable($table, $column));
	}

	// Updates the general information about a module.
	public function updateModuleInfo($modid, $name, $desc, $file, $icon)
	{
		global $dbcore;
		$table = $this->tablebase . '.module';
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR);
		$qxa = $dbcore->buildArray('description', $file, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('filename', $file, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('iconname', $icon, databaseCore::PTSTR, $qxa);
		return($dbcore->launchUpdateSingle($table, 'moduleid', $modid, databaseCore::PTINT, $qxa));
	}

	// Updates the activation data for a module.
	public function updateModuleActivation($modid, $active)
	{
		global $dbcore;
		$table = $this->tablebase . '.module';
		$qxa = $dbcore->buildArray('active', $active, databaseCore::PTSTR);
		return($dbcore->launchUpdateSingle($table, 'moduleid', $modid, databaseCore::PTINT, $qxa));
	}

	// Updates all information about a module.
	public function updateModule($modid, $name, $desc, $file, $icon, $active, $allusers, $system, $vendor)
	{
		global $dbcore;
		$table = $this->tablebase . '.module';
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR);
		$qxa = $dbcore->buildArray('description', $desc, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('filename', $file, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('iconname', $icon, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('active', $active, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('allusers', $allusers, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('system', $system, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('vendor', $vendor, databaseCore::PTINT, $qxa);
		return($dbcore->launchUpdateSingle($table, 'moduleid', $modid, databaseCore::PTINT, $qxa));
	}

	// Inserts a module into the database.
	public function insertModule($modid, $name, $desc, $file, $icon, $active, $allusers, $system, $vendor)
	{
		global $dbcore;
		$table = $this->tablebase . '.module';
		$qxa = $dbcore->buildArray('moduleid', $modid, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('description', $desc, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('filename', $file, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('iconname', $icon, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('active', $active, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('allusers', $allusers, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('system', $system, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('vendor', $vendor, databaseCore::PTINT, $qxa);
		return($dbcore->launchInsert($table, $qxa));
	}

	// Removes a module from the database.
	public function deleteModule($modid)
	{
		global $dbcore;
		$table = $this->tablebase . '.module';
		return($dbcore->launchDeleteSingle($table, 'moduleid', $modid, databaseCore::PTINT));
	}



	/* ******** MODACCESS TABLE ******** */

	/* The modaccess table maps which user profiles have access to
		which modules.  If an entry exists, then the access is allowed.
		If the entry is missing, then access is denied unless the
		user is on a special account, or the allusers flag is marked
		for the module. */

	// Query all modules for a specific profile ID.
	public function queryModaccessProfile($profid)
	{
		global $dbcore;
		$table = $this->tablebase . '.modaccess';
		$column = '*';
		$qxa = $dbcore->buildArray('profileid', $profid, databaseCore::PTINT);
		return($dbcore->launchQueryMultiple($table, $column, $qxa));
	}

	// Query if a specific module ID and profile ID combination exists
	// in the database.
	public function queryModaccess($profid, $modid)
	{
		global $dbcore;
		$table = $this->tablebase . '.modaccess';
		$column = '*';
		$qxa = $dbcore->buildArray('moduleid', $modid, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('profileid', $profid, databaseCore::PTINT, $qxa);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Inserts a module/profile ID pair into the database.
	public function insertModaccess($profid, $modid)
	{
		global $dbcore;
		$table = $this->tablebase . '.modaccess';
		$qxa = $dbcore->buildArray('moduleid', $modid, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('profileid', $profid, databaseCore::PTINT, $qxa);
		return($dbcore->launchInsert($table, $qxa));
	}

	// Removes a module/profile ID pair from the database.
	public function deleteModaccess($profid, $modid)
	{
		global $dbcore;
		$table = $this->tablebase . '.modaccess';
		$qxa = $dbcore->buildArray('moduleid', $modid, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('profileid', $profid, databaseCore::PTINT, $qxa);
		return($dbcore->launchDeleteMultiple($table, $qxa));
	}



	/* ******** OAUTH TABLE ******** */

	/* The OAuth table provides information about OAuth providers and
	   how to communicate with them.  */

	// Queries a specific provider.
	public function queryOAuth($provider)
	{
		global $dbcore;
		$table = $this->tablebase . '.oauth';
		$column = '*';
		$qxa = $dbcore->buildArray('provider', $provider, databaseCore::PTSTR);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Queries all providers.
	public function queryOAuthAll()
	{
		global $dbcore;
		$table = $this->tablebase . '.oauth';
		$column = 'provider,module,expire,clientid,authtype';
		return($dbcore->launchQueryDumpTable($table, $column));
	}

	// Updates the data associated with the specified OAuth provider.
	public function updateOAuth($provider, $module, $expire, $clid,
		$clsecret, $scope, $authtype, $authurl, $redirecturl, $resurl1,
		$resurl2, $resurl3, $resurl4)
	{
		global $dbcore;
		$table = $this->tablebase . '.oauth';
		$qxa = $dbcore->buildArray('module', $module, databaseCore::PTSTR);
		$qxa = $dbcore->buildArray('expire', $expire, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('clientid', $clid, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('clientsecret', $clsecret, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('scope', $scope, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('authtype', $authtype, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('authurl', $authurl, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('redirecturl', $redirecturl, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('resourceurl1', $resurl1, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('resourceurl2', $resurl2, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('resourceurl3', $resurl3, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('resourceurl4', $resurl4, databaseCore::PTSTR, $qxa);
		return($dbcore->launchUpdateSingle($table, 'provider', $provier, databaseCore::PTSTR, $qxa));
	}

	// Inserts a new OAuth provider in the database.
	public function insertOAuth($provider, $module, $expire, $clid,
		$clsecret, $scope, $authtype, $authurl, $redirecturl, $resurl1,
		$resurl2, $resurl3, $resurl4)
	{
		global $dbcore;
		$table = $this->tablebase . '.oauth';
		$qxa = $dbcore->buildArray('provider', $provider, databaseCore::PTSTR);
		$qxa = $dbcore->buildArray('module', $module, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('expire', $expire, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('clientid', $clid, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('clientsecret', $clsecret, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('scope', $scope, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('authtype', $authtype, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('authurl', $authurl, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('redirecturl', $redirecturl, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('resourceurl1', $resurl1, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('resourceurl2', $resurl2, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('resourceurl3', $resurl3, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('resourceurl4', $resurl4, databaseCore::PTSTR, $qxa);
		return($dbcore->launchInsert($table, $rxa));
	}

	// Removes an OAuth provider from the database.
	public function deleteOAuth($provider)
	{
		global $dbcore;
		$table = $this->tablebase . '.oauth';
		return($dbcore->lauchDeleteSingle($table, 'provider', $provider, databaseCore::PTSTR));
	}



	/* ******** PROFILE TABLE ******** */

	/* The profile table determines what access rights a user has on the
	   system.  Every user must have a profile associated with their
	   account. */

	// Queries a specific profile.
	public function queryProfile($profid)
	{
		global $dbcore;
		$table = $this->tablebase . '.profile';
		$column = '*';
		$qxa = $dbcore->buildarray('profileid', $profid, databaseCore::PTINT);
		return($dbcore->launchQuerySingle($table, $column, $qxa));
	}

	// Returns all profiles in the database.
	public function queryProfileAll()
	{
		global $dbcore;
		$table = $this->tablebase . '.profile';
		$column = 'profileid, name, description, portal';
		return($dbcore->launchQueryDumpTable($table, $column));
	}

	// Updates a profile.
	public function updateProfile($profid, $name, $desc, $portal, $bmc, $bma)
	{
		global $dbcore;
		$table = $this->tablebase . '.profile';
		$qxa = $dbcore->buildarray('name', $name, databaseCore::PTSTR);
		$qxa = $dbcore->buildarray('description', $desc, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildarray('portal', $portal, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildarray('bitmap_core', $bmc, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildarray('bitmap_app', $bma, databaseCore::PTSTR, $qxa);
		return($dbcore->launchUpdateSingle($table, 'profileid', $profid, databaseCore::PTINT, $qxa));
	}

	// Inserts a new profile in the database.
	public function insertProfile($profid, $name, $desc, $portal, $bmc, $bma)
	{
		global $dbcore;
		$table = $this->tablebase . '.profile';
		$qxa = $dbcore->buildarray('profileid', $profid, databaseCore::PTINT);
		$qxa = $dbcore->buildarray('name', $name, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildarray('description', $desc, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildarray('portal', $portal, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildarray('bitmap_core', $bmc, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildarray('bitmap_app', $bma, databaseCore::PTSTR, $qxa);
		return($dbcore->launchInsert($table, $qxa));
	}

	// Deletes a profile from the database.
	public function deleteProfile($profid)
	{
		global $dbcore;
		$table = $this->tablebase . '.profile';
		return($dbcore->launchDeleteSingle($table, 'profileid', $profid, databaseCore::PTINT));
	}
}


// Autoinstantiate the class
$dbconf = new database_config();


?>