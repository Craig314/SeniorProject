<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application 


*/


require_once '../libs/dbaseconf.php';


interface moduleLoadInterface
{
	function loadModule();
	public function redirect($filename);
}

class moduleLoadClass implements moduleLoadInterface
{

	// Loads the selected module.
	function loadModule()
	{
		global $baseUrl;
		global $dbconf;
		global $herr;
		global $vendor;
		global $admin;

		// Check input.
		if (!isset($_POST['MODULE']))
		{
			$ajax->SendCode(ajaxClass::CODE_BADREQ, 'Missing module identifier');
			exit(1);
		}
		else
		{
			if (!is_numeric($_POST['MODULE']))
			{
				$ajax->SendCode(ajaxClass::CODE_BADREQ, 'Malformed module identifier');
				exit(1);
			}
			else
			{
				$modId = $_POST['MODULE'];
			}
		}

		// Load module data.
		$rxa = $dbconf->queryModule($modId);
		if ($rxa == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage());
			else
				handleError('Database Error: Unable to get module information.');
		}

		// Perform security checks.
		if ($vendor != 0) $this->redirect($rxa['filename']);
		if ($rxa['vendor'] != 0) handleError('You do not have access to the requested module.');
		if ($admin != 0) $this->redirect($rxa['filename']);
		if ($rxa['allusers'] != 0) $this->redirect($rxa['filename']);
		$rxm = $dbconf->queryModaccess($_SESSION['profileId'], $modId);
		if ($rxm == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage());
			else
				handleError('You do not have access to the requested module.');
		}

	// Redirect.
	$this->redirect($rxa['filename']);
	}

	// Redirect
	public function redirect($filename)
	{
		global $ajax;
		global $CONFIGVAR;

		$docRoot = $CONFIGVAR['server_document_root']['value'];
		$result = file_exists($docRoot . '/modules/' . $filename);
		if ($result) $ajax->redirect('/modules/' . $filename);
		else
		{
			$result = file_exists($docRoot . '/application/' . $filename);
			if ($result) $ajax->redirect('/application/' . $filename);
			else
				handleError('Configured module/application file is missing' .
					'<br>Contact your administrator.');
		}
		exit(0);
	}

}

$moduleLoad = new moduleLoadClass();

?>