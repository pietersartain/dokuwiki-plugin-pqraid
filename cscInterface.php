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
include_once "connect.php";

// This means we can execute arbitrary PHP code through a querystring.
// This is probably bad ...
eval($func.";");

function saveCSC() {
/*
//	global $INFO;
//	$username = $INFO['client'];
	$username = $_POST['uname'];
	$db = getDb();
	
	$csclist = getCSCList($username,$db);
	
	if (count($csclist) > 3) {
		die("More than 3 CSCs detected for ".$username.". This is a problem.");
	} else {

	$updated = 0;
		for($x=0; $x<3;$x++) {
			
			// For each potential CSC,
			// first check to see if it's one of the ones we grabbed earlier:
			foreach($csclist as $cscinfo) {
				if ($cscinfo['csc_id'] == $_POST['cscid'.$x]) {
					// Match found, it's unlikely anyone's been really screwing with the
					// signup system, so carry on like it's meant to be ...
					$sql = 'UPDATE pqr_csc SET 
							character_name="'.$_POST['character_name'.$x].'" 
							role_id='.$_POST['rolelist'.$x].' 
							WHERE csc_id="'.$csc['csc_id'].'" 
							AND player_id="'.$username.'"';
			$rslt = mysql_query($sql);
			if (!$rslt){
				die("<br /><br />Error: ".mysql_error($db)." from sql: ".htmlspecialchars($sql));
			} else {
				++$updated;
			}
				}// end match
			}// end foreach
			
			if ($updated > $x) {
				die("We seem to have updated more than we should ... ");
			} else {
				// If we've got this far, the CSC hasn't been initialised yet, so
				// we insert instead of updating:
				$sql = 'INSERT INTO pqr_csc(character_name,role_id,player_id) 
					VALUES("'.$_POST['character_name'.$x].'","'.$_POST['rolelist'.$x].'","'.$username.'")';
				$rslt = mysql_query($sql);
				if (!$rslt){
					die("<br /><br />Error: ".mysql_error($db)." from sql: ".htmlspecialchars($sql));
				} else {
					++$updated;
				}
			}
			
		}// end csc for loop
	}
	header("Location:http://".$_SERVER['SERVER_NAME']."/pqdev/csc?purge=true"); */
}

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
	
	$rslt = mysql_query($sql);
	if (!$rslt){
		die("<br /><br />Error: ".mysql_error($db)." from sql: ".htmlspecialchars($sql));
	}
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
