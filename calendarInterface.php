<?php

/*
	calendarInterface.php - used for the AJAX parts of the calendar
		
	author:	P E Sartain	
	date:	21/10/2008
*/

if (isset($_GET['func'])) {
	$func = $_GET['func'];
} else {
	$func = null;
}

include_once "calendarFunc.php";
include_once "timeFunc.php";
include_once "connect.php";

// This means we can execute arbitrary PHP code through a querystring.
// This is probably bad ...
eval($func.";");

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

function saveUnavailable() {

	$username = $_POST['uname'];
	$db = getDb();

//	print_r($_POST);
	
	// Get the players existing information
	$unavail = getUnavailable($db,$_POST['start'],$_POST['end'],$username);
	
	// The current week, for sanity we'll use the same var as calendar.
	$week = (int)$_POST['start'] +1;
	
	for ($days=-7;$days<21;$days++) {
		// The loopday is all relative to the raiding epoch
		//	+$days		-- iterates through 28 cells only
		// +($week*7)	-- allows viewing to cycle by week, not day
		// -3			-- a constant to align the epoch to a Monday
		$loopday = mktime(0, 0, 0, gmdate("m",getRaidEpoch()), gmdate("d",getRaidEpoch())+$days+($week*7)-3, gmdate("Y",getRaidEpoch()));
		
		// Convert both the form and unavail information into easier
		// variables, for comparison later on.
		
		// Form information is backwards, because we care about what's NOT set.
		if (isset($_POST[$loopday])) {
			// Available, make no database entries (delete)
			$newtoken = 0;
		} else {
			// Unavailable
			$newtoken = 1;
		}
		
		// Database information contains when people are NOT available
		if (isset($unavail[$loopday])) {
			// Unavailable
			$oldtoken = 1;
		} else {
			$oldtoken = 0;
		}
		
		if ($oldtoken != $newtoken){
			// If the tokens are not the same, some change has been elicited
			if ($newtoken && !$oldtoken) {
				$sql = "INSERT INTO
					pqr_unavail(player_id,unavail) 
					VALUES('".$username."',FROM_UNIXTIME(".$loopday."))";
			} else {
				$sql = "DELETE FROM pqr_unavail WHERE
					unavail=FROM_UNIXTIME(".$loopday.") AND 
					player_id='".$username."'";
			}
//			echo $sql."<br>";
			runquery($sql,$db);
		}

	} // for
} // function

// Write the raid information into a window for ajax display on the calendar
function showRaid($raid_id) {
	$db = getDb();
	$sql = "SELECT * FROM pqr_raids WHERE raid_id = ".$raid_id;
	$rslt = mysql_query($sql);
	if ((mysql_num_rows($rslt) > 0) && (mysql_num_rows($rslt) <= 1)) {
		$row = mysql_fetch_array($rslt);
	
		$raid_oclock = strtotime($row['raid_oclock']);
	
		$str ='<img src="lib/plugins/pqraid/images/'.$row['icon'].'"></img>';
		$str.='<a href="" onclick="boxit()">X</a>';
		$str.='<h1>'.$row['name'].'</h1>';
		$str.='<h2>'.date("d/m/Y H:i",$raid_oclock).'</h2>';
		$str.=$row['info'];
	
		echo $str;	
	} else {
		echo "Fudged.";
	}
}

// This was used to try and rewrite all the availability information after a
// local ajax update. It didn't work well. Deprecated.
/*
function updateUnavailDisplay($day) {
	$db = getDb();
	echo getDailyUnavail($db,$day);
}
*/

?>
