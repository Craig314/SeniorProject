<?php

/*

PHP Web Application Configuration Database Driver

*/

require_once 'database.php';

interface database_config_interface
{
	// Table: config
	public function queryConfig($setting);
	public function queryConfigAllProf($profid);
	public function queryConfigAll();
	public function updateConfigValue($setting, $value);
	public function updateConfigProfId($setting, $profid);
	public function insertConfig($setting, $type, $name, $dispname, $value, $desc, $profid, $vendor, $admin);
	public function deleteConfig($setting);

	// Table: flagdesc_core
	public function queryFlagdescCore($flag);
	public function queryFlagdescCoreAll();
	public function updateFlagdescCore($flag, $name, $desc);
	public function insertFlagdescCore($flag, $name, $desc);
	public function deleteFlagdescCore($flag);

	//Table: flagdesc_app
	public function queryFlagdescApp($flag);
	public function queryFlagdescAppAll();
	public function updateFlagdescApp($flag, $name, $desc);
	public function insertFlagdescApp($flag, $name, $desc);
	public function deleteFlagdescApp($flag);

	// Table: module
	public function queryModule($modid);
	public function updateModuleInfo($modid, $name, $file, $icon);
	public function updateModuleActivation($modid, $active);
	public function updateModule($modid, $name, $file, $icon, $active);
	public function insertModule($modid, $name, $file, $icon, $active, $allusers, $system);
	public function deleteModule($modid);
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

	// Query all parameters for the given profile ID.
	public function queryConfigAllProf($profid)
	{
		global $dbcore;
		$table = $this->tablebase . '.config';
		$column = '*';
		$qxa = $dbcore->buildArray('profileid', $profid, databaseCore::PTINT);
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

	// Updates a configuration parameter profile ID.
	public function updateConfigProfId($setting, $profid)
	{
		global $dbcore;
		$table = $this->tablebase . '.config';
		$column = '*';
		$qxa = $dbcore->buildArray('profileid', $profid, databaseCore::PTINT);
		return($dbcore->launchUpdateSingle($table, 'setting', $setting, databaseCore::PTINT, $qxa));
	}

	// Inserts a configuration parameter.
	public function insertConfig($setting, $type, $name, $dispname, $value, $desc, $profid, $vendor, $admin)
	{
		global $dbcore;
		$table = $this->tablebase . '.config';
		$qxa = $dbcore->buildArray('setting', $setting, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('type', $type, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('dispname', $dispname, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('value', $value, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('desc', $desc, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('profileid', $profid, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('vendor', $vendor, databaseCore::PTINT, $qxa);
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
		$qxa = $dbcore->buildArray('desc', $desc, databaseCore::PTSTR, $qxa);
		return($dbcore->launchUpdateSingle($table, 'flag', $flag, databaseCore::PTINT, $qxa));
	}

	// Inserts data for a specific flag.
	public function insertFlagdescApp($flag, $name, $desc)
	{
		global $dbcore;
		$table = $this->tablebase . '.flagdesc_app';
		$qxa = $dbcore->buildArray('flag', $flag, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('desc', $desc, databaseCore::PTSTR, $qxa);
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
		$qxa = $dbcore->buildArray('desc', $desc, databaseCore::PTSTR, $qxa);
		return($dbcore->launchUpdateSingle($table, 'flag', $flag, databaseCore::PTINT, $qxa));
	}

	// Inserts data for a specific flag.
	public function insertFlagdescCore($flag, $name, $desc)
	{
		global $dbcore;
		$table = $this->tablebase . '.flagdesc_core';
		$qxa = $dbcore->buildArray('flag', $flag, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('desc', $desc, databaseCore::PTSTR, $qxa);
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

	// Updates the general information about a module.
	public function updateModuleInfo($modid, $name, $file, $icon)
	{
		global $dbcore;
		$table = $this->tablebase . '.module';
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR);
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
	public function updateModule($modid, $name, $file, $icon, $active)
	{
		global $dbcore;
		$table = $this->tablebase . '.module';
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR);
		$qxa = $dbcore->buildArray('filename', $file, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('iconname', $icon, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('active', $active, databaseCore::PTINT, $qxa);
		return($dbcore->launchUpdateSingle($table, 'moduleid', $modid, databaseCore::PTINT, $qxa));
	}

	// Inserts a module into the database.
	public function insertModule($modid, $name, $file, $icon, $active, $allusers, $system)
	{
		global $dbcore;
		$table = $this->tablebase . '.module';
		$qxa = $dbcore->buildArray('moduleid', $modid, databaseCore::PTINT);
		$qxa = $dbcore->buildArray('name', $name, databaseCore::PTSTR);
		$qxa = $dbcore->buildArray('filename', $file, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('iconname', $icon, databaseCore::PTSTR, $qxa);
		$qxa = $dbcore->buildArray('active', $active, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('allusers', $rtu, databaseCore::PTINT, $qxa);
		$qxa = $dbcore->buildArray('system', $rtukey, databaseCore::PTINT, $qxa);
		return($dbcore->launchInsert($table, $qxa));
	}

	// Removes a module from the database.
	public function deleteModule($modid)
	{
		global $dbcore;
		$table = $this->tablebase . '.module';
		return($dbcore->launchDeleteSingle($table, 'moduleid', $modid, databaseCore::PTINT));
	}

}

// Autoinstantiate the class
$dbconf = new database_config();

?>