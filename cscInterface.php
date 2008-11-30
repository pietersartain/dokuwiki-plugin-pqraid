<?php

/*
	cscInterface.php - used for the AJAX parts of the CSC editor
		
	author:	P E Sartain	
	date:	07/11/2008
*/

if (isset($_GET['func'])) {
	$func = $_GET['func'];
} else {
	$func = null;
}

include_once "cscfunc.php";
include_once "timeFunc.php";
include_once "connect.php";

// This means we can execute arbitrary PHP code through a querystring.
// This is probably bad ...
eval($func.";");

function saveCSC() {

	//print_r($_POST);
	$username = $_POST['uname'];
	$db = getDb();
	
	// All achievements
	$achievements = getAchievementList($db);
	
	// Full CSC order list
	$cscolist = getCSCOrderList($db);

	// Player specific CSC order list
	$mycscolist = getCSCOrderListByPlayer($username,$db);

	if ($mycscolist == null) {
		// There are no preallocated CSC order blocks, do that now.
		$sql = 'INSERT INTO pqr_cscorder(player_id,csc_id,cscorder) 
				VALUES("'.$username.'",-1,0)';
		for ($x=0;$x<6;$x++) {
			runquery($sql,$db);
		}
		// Get the CSC list again, post initilialisation:
		$mycscolist = getCSCOrderListByPlayer($username,$db);		
	}

	// Keep count of the number of new valid CSCs
	$validCSC = 0;

	for($x=0; $x<3;$x++) {
	
		// First update pqr_csc table with changes to name/role
		$cscid = $_POST['cscid'.$x];
		$sql = 'UPDATE pqr_csc SET 
				character_name = "'.$_POST['character_name'.$cscid].'", 
				role_id = "'.$_POST['rolelist'.$cscid].'" 
				WHERE csc_id='.$cscid.' 
				AND player_id="'.$username.'"';

		//echo $sql."<br />";
		runquery($sql,$db);

		// Get the existing CSC access list
		$accesslist = getCSCAccessTokens($cscid,$db);

		// Then update the achievement lists	
		foreach($achievements as $token) {
			$achievestr = $cscid.'achievement'.$token['achievement_id'];
			
			
			if (isset($_POST[$achievestr])) {
				$newtoken = 1;
			} else {
				$newtoken = 0;
			}
			
			if (isset($accesslist[$token['achievement_id']])) {
				$oldtoken = 1;
			} else {
				$oldtoken = 0;
			}
			
			if ($oldtoken != $newtoken){
				if ($newtoken && !$oldtoken) {
					$sql = 'INSERT INTO 
						pqr_accesstokens(achievement_id,csc_id,set_by,set_when) 
						VALUES(
						'.$token['achievement_id'].',
						'.$cscid.',
						"'.$username.'",
						FROM_UNIXTIME("'.gmnow().'"))';
				} else {
					$sql = "DELETE FROM pqr_accesstokens WHERE 
						achievement_id='".$token['achievement_id'].
						"' AND csc_id='".$cscid."'";
				}
				//echo $sql."<br />";
				runquery($sql,$db);
			}
		} // achievement foreach
		

		// Once the CSC information has been updated, ensure the cscorder info
		// is update and correct:
		if ((count($_POST['character_name'.$cscid]) > 0) && ($_POST['rolelist'.$cscid] > 0)) {
		// This character is a valid CSC, so needs to be in the CSC order list
		$clist[$validCSC++] = $cscid;
		}
		
	} // CSC for
	
	// There should be $validCSC CSCs in the csc order list
	// So each CSC gets 6/$validCSC spaces:
	if ($validCSC > 0) {
	
//		echo $validCSC."<br><br>";

		// The index for the CSC list, max of validCSC
		$cscidx = 0;
		// Our current positional pointer in the cscorder table
		$csccount = 0;

		foreach ($mycscolist as $order) {
//			echo $clist[$cscidx]." : ".$cscidx."<br>";
			runquery("UPDATE pqr_cscorder SET 
				csc_id=".$clist[$cscidx]." 
				WHERE order_id=".$order['order_id'],$db);

			if (++$csccount == (6/$validCSC)) {
				++$cscidx;
				$csccount=0;
			}
		}
	}
	
} // function

// Pretty sure this has been deprecated. Commented out until something breaks.
/*
function saveCharacter($charid,$cscid) {
	$db = getDb();

	if ($cscid == 'null') {
		$sql = "INSERT INTO pqr_csc(character_id) VALUES('".$charid."')";
	} else {
		$rslt = mysql_query("SELECT * FROM pqr_csc WHERE csc_id=".$cscid);

		if (mysql_num_rows($rslt) > 0) {
			$sql = "UPDATE pqr_csc SET character_id = '".$charid."' WHERE csc_id =".$cscid;
		}
	}
	
	runquery($sql);
}
*/

?>
