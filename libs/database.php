<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application Core Database Driver

This is tooled for use with multiple databases with just adding in the code to
create a connection to that specific type of database at the switch statement
around line 66.

*/


require_once 'confbase.php';
require_once 'error.php';
require_once 'utility.php';


interface databaseCoreInterface
{
	const PTINT = PDO::PARAM_INT;
	const PTSTR = PDO::PARAM_STR;
	const PTBOOL = PDO::PARAM_BOOL;
	const PTNULL = PDO::PARAM_NULL;
	const PTLOB = PDO::PARAM_LOB;

	// Query
	public function launchQuerySingle($tab, $col, $qxa);
	public function launchQueryMultiple($tab, $col, $qxa);
	public function launchQueryMultipleRange($tab, $col, $qxa, $field, $type, $start, $end);
	public function launchQueryDumpTable($tab, $col);
	public function launchQueryMultipleSpec($request);

	// Update
	public function launchUpdateSingle($tab, $kfld, $kval, $ktyp, $dxa);
	public function launchUpdateMultiple($tab, $dxk, $dxa);

	// Insert
	public function launchInsert($tab, $qxa);

	// Delete
	public function launchDeleteSingle($tab, $kfld, $kval, $ktyp);
	public function launchDeleteMultiple($tab, $qxa);
	public function launchDeleteTable($tab);

	// Special
	public function launchRequest($request);

	// Transactions
	public function transOpen();
	public function transCommit();
	public function transRollback();

	// Utility
	public function buildArray($field, $value, $type, $qxa = array());
}


class databaseCore implements databaseCoreInterface
{

	private $sqlconn;

	// Object Constructor
	function __construct($dbinfo = '')
	{
		// Perform setup for each specific database type
		switch (APP_DATABASE_TYPE)
		{
			case 'mysql':
				$dsn = APP_DATABASE_TYPE . ':';
				if (APP_DATABASE_CONNECT == 'inet')
				{
					$dsn .= 'host=' . APP_DATABASE_HOST;
					if (APP_DATABASE_PORT != NULL) $dsn .= ';port=' . APP_DATABASE_PORT;
						else printErrorImmediate(
							'Inet connection type requires both hostname or IP address, and a port.');
				}
				elseif (APP_DATABASE_CONNECT == 'sock')
				{
					if (!empty(APP_DATABASE_SOCKET)) $dsn .= 'unix_socket=' . APP_DATABASE_SOCKET;
						else printErrorImmediate(
							'Socket connection type requires a socket filename.');
				}
				elseif (APP_DATABASE_CONNECT == 'default')
				{
					if (!empty(APP_DATABASE_HOST)) $dsn .= 'hostname=' . APP_DATABASE_HOST;
						else printErrorImmediate(
							'Default connection type requires a hostname');
				}
				else printErrorImmediate('Unknown database connection type.');
				if (!empty($dbinfo)) $dsn .= ';dbname=' . $dbinfo;
				if (APP_DATABASE_CHARSET != NULL || APP_DATABASE_CHARSET != 'default')
					$dsn .= ';charset=' . APP_DATABASE_CHARSET;
				break;
			default:
				printErrorImmediate('Database type must be defined and valid.');
				break;
		}

		// Attempt to open a connection to the database server.
		try
		{
			$this->sqlconn = new PDO($dsn, APP_DATABASE_USER, APP_DATABASE_PASSWORD);
			if (APP_DEBUG_STATUS)
				$this->sqlconn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (PDOException $e)
		{
			printErrorImmediate('Failed to connect to database: '
				. $e->getMessage());
		}
	}

	// Puts error messages into the error message buffer and returns false.
	private function handleError($mixvar)
	{
		global $herr;

		if (is_object($mixvar))
		{
			$error = $mixvar->errorInfo();
			if ($error[1] != 0)
			{
				$str = 'SQL Failue: (' . $error[1] . ':' . $error[0] . ') ' . $error[2];
				if (defined(COMMAND_LINE_PROGRAM))
				{
					printErrorContinue($str);
				}
				else
				{
					$herr->errorPutMessage(handleErrors::ETDBASE, $str, handleErrors::ESFAIL);
				}
				if (is_a($mixvar, 'PDOStatement', false))
				{
					$mixvar->closeCursor;
				}
			}
		}
		else if (is_string($mixvar))
		{
			if (defined(COMMAND_LINE_PROGRAM))
			{
				printErrorContinue($mixvar);
			}
			else
			{
				$herr->puterrmsg(handleErrors::ETDBASE, $mixvar, handleErrors::ESFAIL);
			}
		}

		// Always return false
		return false;
	}


	// Collates results into a 3D associative array.
	// This only makes sense for queries which result in
	// multiple rows.  Not to be called directly.
	private function multiResult($stmt)
	{
		$rxa = array();
		$flag = false;
		while (true)
		{
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row == false)
			{
				if ($flag == false) return($this->handleError($stmt));
				else break;
			}
			array_push($rxa, $row);
			$flag = true;
		}
		return $rxa;
	}


	/* ******** PUBLIC FUNCTIONS ******** */


	/* **** QUERY **** */


	// Launches a single query for a single table.
	// Returns a single record in associative array format.
	// $qxa is a nested array.  There is one inside array for
	// each query parameter.  The keys are field, type, and value.
	public function launchQuerySingle($tab, $col, $qxa)
	{
		// Check Input
		if (!is_array($qxa)) return($this->handleError('Query data not an array.'));

		// Setup
		$request = "SELECT $col FROM $tab WHERE ";
		$flag = false;

		// Build Query
		foreach($qxa as $fx => $vx)
		{
			if ($flag) $request .= ' AND ';
			$request .= '`' . $vx['field'] . '` = ?';
			$flag = true;
		}
		$request .= ' LIMIT 1';  

		// Prepare Statements
		$stmt = $this->sqlconn->prepare($request);		
		if ($stmt == false) return($this->handleError($this->sqlconn));
		
		// Associate Prepared Statement
		$count = 1;
		foreach($qxa as $fx => $vx)
		{
			$result = $stmt->bindParam($count, $vx['value'], $vx['type']);
			if ($result == false) return($this->handleError($stmt));
			$count++;
		}

		// Launch
		$result = $stmt->execute();
		if ($result == false) return($this->handleError($stmt));

		// Process Results
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($row == false) return($this->handleError($stmt));

		// Return
		$stmt->closeCursor();
		return $row;
	}


	// Same as launchQuerySingle above, but returns data in a 3D
	// associative array.
	public function launchQueryMultiple($tab, $col, $qxa)
	{
		if (!is_array($qxa)) return($this->handleError('Query data not an array.'));
		$request = "SELECT $col FROM $tab WHERE ";
		$flag = false;
		foreach($qxa as $fx => $vx)
		{
			if ($flag) $request .= ' AND ';
			$request .= '`' . $vx['field'] . '` = ?';
			$flag = true;
		}
		$stmt = $this->sqlconn->prepare($request);
		
		if ($stmt == false) return($this->handleError($this->sqlconn));
		$count = 1;
		foreach($qxa as $fx => $vx)
		{
			$result = $stmt->bindParam($count, $vx['value'], $vx['type']);
			if ($result == false) return($this->handleError($stmt));
			$count++;
		}
		$result = $stmt->execute();
		if ($result == false) return($this->handleError($stmt));
		$rxa = $this->multiResult($stmt);
		$stmt->closeCursor();
		return $rxa;
	}

	// Same as lauchQueryMultiple above, but returns records that have
	// the specified field within the specified range.  $start and $end
	// are included in the returned range.
	public function launchQueryMultipleRange($tab, $col, $qxa, $field, $type, $start, $end)
	{
		if (!is_array($qxa)) return($this->handleError('Query data not an array.'));
		$request = "SELECT $col FROM $tab WHERE `$field` >= ? AND `$field` <= ? AND ";
		$flag = false;
		if (is_array($qxa))
		{
			foreach($qxa as $fx => $vx)
			{
				if ($flag) $request .= ' AND ';
				$request .= '`' . $vx['field'] . '` = ?';
				$flag = true;
			}
		}
		$stmt = $this->sqlconn->prepare($request);
		if ($stmt == false) return($this->handleError($this->sqlconn));
		$result = $stmt->bindParam(1, $start, $type);
		$result = $stmt->bindParam(2, $end, $type);
		if (is_array($qxa))
		{
			$count = 3;
			foreach($qxa as $fx => $vx)
			{
				$result = $stmt->bindParam($count, $vx['value'], $vx['type']);
				if ($result == false) return($this->handleError($stmt));
				$count++;
			}
		}
		$result = $stmt->execute();
		if ($result == false) return($this->handleError($stmt));
		$rxa = $this->multiResult($stmt);
		$stmt->closeCursor();
		return $rxa;
	}

	// This dumps the entire table and returns the data in a 3D
	// associative array.
	public function launchQueryDumpTable($tab, $col)
	{
		$request = "SELECT $col FROM $tab";
		$stmt = $this->sqlconn->query($request);
		if ($stmt == false) return($this->handleError($this->sqlconn));
		$rxa = $this->multiResult($stmt);
		$stmt->closeCursor();
		return $rxa;
	}


	// Special queries that expect multiple results.
	public function launchQueryMultipleSpec($request)
	{
		$stmt = $this->sqlconn->query($request);
		if ($stmt == false) return($this->handleError($this->sqlconn));
		$rxa = $this->multiResult($stmt);
		return $rxa;
	}


	/* **** UPDATE **** */


	// Updates a database record. This is multi-field enabled with single key.
	public function launchUpdateSingle($tab, $kfld, $kval, $ktyp, $dxa)
	{
		// Check Input
		if (!is_array($dxa))
			return($this->handleError('Program error, 5th param is not array.'));

		$str = '';
		$flag = false;
		foreach($dxa as $fx => $vx)
		{
			if ($flag) $str .= ',';
			$str .= '`' . $vx['field'] . '`=?';
			$flag = true;
		}
		$request = "UPDATE $tab SET $str WHERE `$kfld` = ? LIMIT 1";
		$stmt = $this->sqlconn->prepare($request);
		if ($stmt == false) return($this->handleError($this->sqlconn));
		$count = 1;
		foreach($dxa as $fx => $vx)
		{
			$result = $stmt->bindParam($count, $vx['value'], $vx['type']);
			if ($result == false) return($this->handleError($stmt));
			$count++;
		}
		$result = $stmt->bindParam($count, $kval, $ktyp);
		if ($result == false) return($this->handleError($stmt));
		$result = $stmt->execute();
		if ($result == false) return($this->handleError($stmt));
		return true;
	}


	// Updates a database record. This is multi-field enabled with multiple keys.
	public function launchUpdateMultiple($tab, $dxk, $dxa)
	{
		if (!is_array($dxk))
			return($this->handleError('Program error, 2nd param is not array.'));
		if (!is_array($dxa))
			return($this->handleError('Program error, 3th param is not array.'));

		$strdat = '';
		$strkey = '';
		$flag = false;
		foreach($dxa as $fx => $vx)
		{
			if ($flag) $strdat .= ',';
			$strdat .= '`' . $vx['field'] . '`=?';
			$flag = true;
		}

		$flag = false;
		foreach($dxk as $fx => $vx)
		{
			if ($flag) $strkey .= ' AND ';
			$strkey .= '`' . $vx['field'] . '`=?';
			$flag = true;
		}

		$request = "UPDATE $tab SET $strdat WHERE $strkey LIMIT 1";
		$stmt = $this->sqlconn->prepare($request);
		if ($stmt == false) return($this->handleError($this->sqlconn));
		$count = 1;
		foreach($dxa as $fx => $vx)
		{
			$result = $stmt->bindParam($count, $vx['value'], $vx['type']);
			if ($result == false) return($this->handleError($stmt));
			$count++;
		}
		foreach($dxk as $fx => $vx)
		{
			$result = $stmt->bindParam($count, $vx['value'], $vx['type']);
			if ($result == false) return($this->handleError($stmt));
			$count++;
		}
		$result = $stmt->execute();
		if ($result == false) return($this->handleError($stmt));
		return true;
	}


	/* **** INSERT **** */


	// Inserts a record into the database.  Multifield enabled.
	public function launchInsert($tab, $qxa)
	{
		$str1 = '';
		$str2 = '';
		$flag = false;
		foreach($qxa as $fx => $vx)
		{
			if ($flag)
			{
				$str1 .= ',';
				$str2 .= ',';
			}
			$str1 .= '`' . $vx['field'] . '`';
			$str2 .= '?';
			$flag = true;
		}
		$request = "INSERT INTO $tab ($str1) VALUES ($str2)";
		$stmt = $this->sqlconn->prepare($request);
		if ($stmt == false) return($this->handleError($this->sqlconn));
		$count = 1;
		foreach($qxa as $fx => $vx)
		{
			$result = $stmt->bindParam($count, $vx['value'], $vx['type']);
			if ($result == false) return($this->handleError($stmt));
			$count++;
		}
		$result = $stmt->execute();
		if ($result == false) return($this->handleError($stmt));
		return true;
	}


	/* **** DELETE **** */


	// Deletes a record from the database.
	public function launchDeleteSingle($tab, $kfld, $kval, $ktyp)
	{
		$request = "DELETE FROM $tab WHERE `$kfld`=?";
		$stmt = $this->sqlconn->prepare($request);
		if ($stmt == false) return($this->handleError($this->sqlconn));
		$result = $stmt->bindParam(1, $kval, $ktyp);
		if ($result == false) return($this->handleError($stmt));
		$result = $stmt->execute();
		if ($result == false) return($this->handleError($stmt));
		return true;
	}

	// Deletes a record with multiple keys from the database.
	public function launchDeleteMultiple($tab, $qxa)
	{
		$flag = false;
		$str = "";
		foreach($qxa as $fx => $vx)
		{
			if ($flag) $str .= ' AND ';
			else $flag = true;
			$str .= '`' . $vx['field'] . '`=?';
		}
		$request = "DELETE FROM $tab WHERE $str";
		$stmt = $this->sqlconn->prepare($request);
		if ($stmt == false) return($this->handleError($this->sqlconn));
		$count = 1;
		foreach($qxa as $fx => $vx)
		{
			$result = $stmt->bindParam($count, $vx['value'], $vx['type']);
			if ($result == false) return($this->handleError($stmt));
			$count++;
		}
		$result = $stmt->execute();
		if ($result == false) return($this->handleError($stmt));
		return true;
	}

	// Deletes all records within a table
	public function launchDeleteTable($tab)
	{
		$request = "DELETE FROM $tab";
		$stmt = $this->sqlconn->query($request);
		if ($stmt == false) return($this->handleError($this->sqlconn));
		return true;
	}


	/* **** SPECIAL **** */


	// Sends a preformatted command to the database.  Returns false
	// on error, true on success.  Do not use for queries.
	public function launchRequest($request)
	{
		$stmt = $this->sqlconn->query($request);
		if ($stmt == false) return($this->handleError($this->sqlconn));
		return true;
	}


	/* **** TRANSACTION PROCESSING **** */


	// Turns off autocommit mode and begins a transaction.
	// Returns true if successful, false on error.
	public function transOpen()
	{
		return($this->sqlconn->beginTransaction());
	}

	// Commits an open transaction and turns autocommit mode on.
	// Returns true if successful, false on error.
	public function transCommit()
	{
		return($this->sqlconn->commit());
	}

	// Cancels a transaction and turns autocommit mode on.
	// Returns true if successful, false on error.
	public function transRollback()
	{
		return($this->sqlconn->rollBack());
	}


	/* **** UTILITY **** */


	// Builds a request array from given data.
	public function buildArray($field, $value, $type, $qxa = array())
	{
		if (!is_array($qxa)) $qxa = array();
		array_push($qxa, array('field' => $field, 'value' => $value, 'type' => $type));
		return $qxa;
	}

}


// Auto instantiate the class.
$dbcore = new databaseCore();


?>