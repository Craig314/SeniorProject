<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application Panel Library

This library holds the routines that generate
the links which are shown in the link panel of
the allplication as well as the status information
in the status panel of the library.


*/


const LIBSDIR = '../libs/';
require_once LIBSDIR . 'confload.php';
require_once LIBSDIR . 'timedate.php';
require_once LIBSDIR . 'account.php';
require_once LIBSDIR . 'dbaseconf.php';
require_once LIBSDIR . 'dbaseuser.php';
require_once 'dbaseapp.php';
require_once LIBSDIR . 'error.php';
// require_once LIBSDIR . '.php';


interface linkPanelInterface
{
	public function getLinks();
	public function getStatus();
}


class linkPanel implements linkPanelInterface
{
	// This is the function that writes the HTML to the client.
	private function writeModuleIcon($url, $id, $iname, $dname, $desc)
	{
		$tooltip = 'data-toggle="tooltip" data-html="true" title="' . $desc . '"';
		$content = "	<div class=\"linkitem iconfont\" onclick=\"loadModule($id)\" $tooltip>$dname</div>";
		return $content;
	}

	// Generates and returns the HTML for the links panel.
	public function getLinks()
	{
		global $baseUrl;
		global $account;
		global $dbconf;
		global $herr;
		global $admin;
		global $vendor;
		global $special;
		global $moduleId;

		// Get the current user's profile Id.
		$profId = $_SESSION['profileId'];

		// Load the modaccess table.
		$rxa = $dbconf->queryModaccessProfile($profId);
		if ($rxa == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage());
		}

		// Load the module table.
		$rxm = $dbconf->queryModuleAll();
		if ($rxm == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage());
			else
				handleError('There are currently no modules to access.');
		}

		// Now we run the module list and check permissions and
		// active status on each one. 
		$access = false;
		$navContent = '';
		foreach($rxm as $kxa => $vxa)
		{
			// Check if the module is active.
			if ($vxa['active'] == 0) continue;

			// Get what we need from the database array.
			$modId = $vxa['moduleid'];
			$modName = $vxa['name'];
			$modDesc = $vxa['description'];
			$modIcon = $vxa['iconname'] . '.png';

			// Check to see if the current module ID is the same as the
			// executing module ID.  If it is, then skip.
			if ($moduleId == $modId) continue;

			// Don't change the order of these checks.
			// Order Matters.

			// The vendor account always has access.
			if ($vendor != 0)
			{
				$access = true;
				$navContent .= $this->writeModuleIcon($baseUrl, $modId, $modIcon, $modName, $modDesc);
				continue;
			}

			// Some modules are only for the vendor.
			if ($vxa['vendor'] != 0) continue;

			// The admin account has access to everything that is not vendor
			// only.  So we need to check if the user account is the admin
			// account.
			if ($admin != 0)
			{
				$access = true;
				$navContent .= $this->writeModuleIcon($baseUrl, $modId, $modIcon, $modName, $modDesc);
				continue;
			}

			// Modules with allusers set can be accessed by anyone.
			if ($vxa['allusers'] != 0)
			{
				$access = true;
				$navContent .= $this->writeModuleIcon($baseUrl, $modId, $modIcon, $modName, $modDesc);
				continue;
			}

			// Now we have to bash the module id against the modaccess list.
			// If the module id is not on the modaccess list, then that module
			// is not displayed.
			if (!empty($rxa))
			{
				foreach($rxa as $kxb => $vxb)
				{
					if ($modId == $vxb['moduleid'] && $profId == $vxb['profileid'])
					{
						$access = true;
						$navContent .= $this->writeModuleIcon($baseUrl, $modId, $modIcon, $modName, $modDesc);
						break;
					}
				}
			}
		}
		return $navContent;
	}

	// Generates and returns the HTML for the status panel.
	public function getStatus()
	{
		global $CONFIGVAR;
		global $dbapp;
		global $herr;


		// Courses
		$rxa = $dbapp->queryStudentclassStudentAll($_SESSION['userId']);
		if ($rxa == false)
		{
			if ($herr->checkState())
				handleError($herr->errorGetMessage());
			$rxb = $dbapp->queryCourseInstructAll($_SESSION['userId']);
			if ($rxb == false)
			{
				if ($herr->checkState())
					handleError($herr->errorGetMessage());
				else
					return 'No course data found.';
			}
		}
		else
		{
			$rxb = array();
			foreach($rxa as $kx => $vx)
			{
				$rxc = $dbapp->queryCourse($vx['courseid']);
				if ($rxc == false)
				{
					if ($herr->checkState())
						handleError($herr->errorGetMessage());
					return 'No course data found.';
				}
				else array_push($rxb, $rxc);
			}
		}

		// Past Due
		$pastDue = '<h4><b>Assignments<br>Past Due<b></h4><br>';
		$pastFlag = false;
		$max = time();
		$min = $max - $CONFIGVAR['assign_past_due_time']['value'];
		if ($min < 0) $min = 0;
		foreach($rxb as $kx => $vx)
		{
			$rxa = $dbapp->queryAssignmentRangeDue($vx['courseid'], $min, $max);
			if ($rxa == false)
			{
				if ($herr->checkState())
					handleError($herr->errorGetMessage());
				continue;
			}
			$pastFlag = true;
			foreach($rxa as $kxa => $vxa)
			{
				if ($vxa['lockdate'] > 0)
				{
					if ($vxa['lockdate'] < $max) continue;
				}
				$rxc = $dbapp->queryTurninStudentAssignAll($_SESSION['userId'],
					$vxa['assignment']);
				if ($rxc == false)
				{
					if ($herr->checkState())
						handleError($herr->errorGetMessage());
				}
				else continue;
				$pastDue .= '<span class="cursor-pointer" onclick="loadAssignment('
					. $vxa['assignment'] . ')">';
				$pastDue .= '<b>' . $vx['class'] . ': ' . $vx['name'] . '</b></br>';
				$pastDue .= '<i>' . $vxa['name'] . '</i></br>';
				$pastDue .= '<span class="bgcolor-black color-red">' .
					timedate::unix2canonical($vxa['duedate']) . '</span></br><br>';
				$pastDue .= '</span>';
			}
		}
		if ($pastFlag == true)	$pastDue .= '<br>';
		else $pastDue = 'No assignments past due.<br><br>';

		// Upcoming
		$upcoming = '<h4><b>Assignments<br>Upcoming <b></h4><br>';
		$upcomingFlag = false;
		$min = time();
		$max = $min + $CONFIGVAR['assign_duedate_lookahead']['value'];
		foreach($rxb as $kx => $vx)
		{
			$rxa = $dbapp->queryAssignmentRangeDue($vx['courseid'], $min, $max);
			if ($rxa == false)
			{
				if ($herr->checkState())
					handleError($herr->errorGetMessage());
				continue;
			}
			$upcomingFlag = true;
			foreach($rxa as $kxa => $vxa)
			{
				$upcoming .= '<span class="cursor-pointer" onclick="loadAssignment('
					. $vxa['assignment'] . ')">';
				$upcoming .= '<b>' . $vx['class'] . ': ' . $vx['name'] . '</b></br>';
				$upcoming .= '<i>' . $vxa['name'] . '</i></br>';
				$priorityHigh = $CONFIGVAR['assign_priority_high']['value'] + $min;
				$priorityMed = $CONFIGVAR['assign_priority_medium']['value'] + $min;
				$priorityLow = $CONFIGVAR['assign_priority_low']['value'] + $min;
				if ($vxa['duedate'] < $priorityHigh)
					$upcoming .= '<span class="color-red">';
				else if ($vxa['duedate'] < $priorityMed)
					$upcoming .= '<span class="color-orange">';
				else if ($vxa['duedate'] < $priorityLow)
					$upcoming .= '<span class="color-green">';
				else $upcoming .= '<span class="color-blue">';
				$upcoming .= timedate::unix2canonical($vxa['duedate']) .
					'</span></br>';
				$upcoming .= '</span>';
			}
		}
		if ($upcomingFlag == true)	$upcoming .= '<br>';
		else $upcoming = 'No assignments upcoming.';

		return $pastDue . $upcoming;
	}

}

$panels = new linkPanel();

?>
