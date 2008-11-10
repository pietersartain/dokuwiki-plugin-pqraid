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

	print_r($_POST);
	$username = $_POST['uname'];
	$db = getDb();
	
	$achievements = getAchievementList($db);
	

	for($x=0; $x<3;$x++) {
	
		// First update pqr_csc table with changes to name/role
		$cscid = $_POST['cscid'.$x];
		$sql = 'UPDATE pqr_csc SET 
				character_name = "'.$_POST['character_name'.$cscid].'", 
				role_id = "'.$_POST['rolelist'.$cscid].'" 
				WHERE csc_id='.$cscid.' 
				AND player_id="'.$username.'"';

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
						'.$username.',
						FROM_UNIXTIME("'.gmnow().'"))';
				} else {
					$sql = "DELETE FROM pqr_accesstokens WHERE 
						achievement_id='".$token['achievement_id'].
						"' AND csc_id='".$cscid."'";
				}
				echo $sql."<br />";
				//runquery($sql,$db);
			}
		} // foreach
	} // for
} // function

function saveCharacter($charid,$cscid) {
	include_once "connect.php";
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
/*
function makeWeekEditBox($week,$current_info,$day) {
	echo '
	<div>
	Week '.$week.': <input type="text" value="'.$current_info.'" id="editWeekBox'.$day.'" onblur="saveEditBox(\''.$week.'\',\''.$day.'\')" />
	</div>';
}

function saveWeekEditBox($week,$new_info,$day) {

	include_once "connect.php";
	$db = getDb();

	if (strlen($new_info) == 0) {
		$sql = "DELETE FROM pqr_weeks WHERE week_num=".$week;
	} else {
		$rslt = mysql_query("SELECT info FROM pqr_weeks WHERE week_num=".$week);
		if (mysql_num_rows($rslt) > 0) {
			$sql = "UPDATE pqr_weeks SET info='".$new_info."' WHERE week_num=".$week;
		} else {
			$sql = "INSERT INTO pqr_weeks(week_num,info) VALUES(".$week.",'".$new_info."')";			
		}
	}
	
	$rslt = mysql_query($sql);
	if (!$rslt){
		die("<br /><br />Error: ".mysql_error($db)." from sql: ".htmlspecialchars($sql));
	}
	
	$out='
	<div onclick="makeEditBox(\''.$week.'\', \''.$new_info.'\',\''.$day.'\')">
	Week '.$week.': '.$new_info.'
	</div>';
	
	echo $out;
}

function showString($string) {
	echo $string;
}
*/
?>
