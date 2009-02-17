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
//eval($func.";");

// This is a bit more secure.
switch("ui$func") {
	case "uisaveCSC":
		saveCSC();
		break;
	default:
		echo "$func not found in ".$_SERVER['PHP_SELF'];
		break;
}

function saveCSC() {

//	print_r($_POST);
	$username = htmlspecialchars($_POST['uname'],ENT_QUOTES);
	$db = getDb();
	
	// All achievements
	$achievements = getAchievementList($db);

	for($x=0; $x<3;$x++) {
	
		// First update pqr_csc table with changes to name/role
		$cscid = $_POST['cscid'.$x];
		$sql = 'UPDATE pqr_csc SET 
				character_name = "'.htmlspecialchars($_POST['character_name'.$cscid],ENT_QUOTES).'", 
				role_id = "'.$_POST['rolelist'.$cscid].'", 
				csc_class = "'.$_POST['classlist'.$cscid].'" 
				WHERE csc_id='.$cscid.' 
				AND player_id="'.$username.'"';

		//echo $sql."<br />";
		runquery($sql,$db);

		// Now update pqr_ranks table with changes to rank
		$sql = 'UPDATE pqr_ranks SET
					rank_id = "'.$_POST['ranklist'].'" 
					WHERE player_id = "'.$username.'"';
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
		


	} // CSC for

	
} // function

?>
