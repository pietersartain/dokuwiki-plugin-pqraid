<?php

/*
	calendarFunc.php - functions that are used to extract database information

	author:	P E Sartain
	date:	21/10/2008

*/

// Pulled from the mysql site
function mysql_fetch_rowsarr($result, $numass=MYSQL_BOTH) {
  $got=array();
  mysql_data_seek($result, 0);
    while ($row = mysql_fetch_array($result, $numass)) {
        array_push($got, $row);
    }
  return $got;
}

// Return an array of week information
function getWeekInfo($minWeek,$maxWeek,&$db) {
	$rslt = mysql_query("SELECT * FROM pqr_weeks WHERE week_num >= ".$minWeek." AND week_num < ".$maxWeek);
	
	//$rslt = mysql_query("SELECT * FROM pqr_weeks");
	
	if (!$rslt) die("calendar week info failure: ".mysql_error($db));

	for ($x=$minWeek;$x<$maxWeek;$x++) {
		$week_info[$x] = '';
	}

	if (mysql_num_rows($rslt) > 0) {	
		while ($row = mysql_fetch_array($rslt)) {
			$week_info[$row['week_num']] = $row['info'];
		}
	}
	
	return $week_info;
}

// Return the number of players with at least one valid CSC
function getCSCNumber(&$db) {
	$rslt = mysql_query("SELECT DISTINCT(player_id) FROM pqr_csc WHERE (role_id > 0 AND CHAR_LENGTH(character_name) > 0)");
	if (!$rslt) die("csc distinct failure: ".mysql_error($db));
	return mysql_num_rows($rslt);
}

// Return an array of unavailability information for a given player
function getUnavailable(&$db,$start,$end,$player) {

	$sql = "SELECT * FROM pqr_unavail WHERE 
		unavail >= FROM_UNIXTIME(".weektoDate($start).") AND 
		unavail < FROM_UNIXTIME(".weekToDate($end).") AND
		player_id = '".$player."'";	

	$rslt = mysql_query($sql);

	if (!$rslt) die("unavail failure: ".mysql_error($db));

	if (mysql_num_rows($rslt) > 0) {
		while ($row = mysql_fetch_array($rslt)){
			$unavail[strtotime($row['unavail'])] = $row['player_id'];
		}
	} else {
		$unavail = 0;
	}
	
	return $unavail;
}

// Return a list of signups by day
function getSignupsByDay(&$db,$raid_id) {
	$sql = "SELECT * FROM pqr_signups 
		WHERE raid_id=".$raid_id." 
		ORDER BY csc_role DESC, csc_name ASC";
	$rslt = mysql_query($sql);
	if (!$rslt) die(" failure: ".mysql_error($db));

	if (mysql_num_rows($rslt) > 0) {
		while ($row = mysql_fetch_array($rslt)){
			$signups[$row['csc_name']] = $row;
		}
	} else {
		$signups = null;
	}
	
	return $signups;
}

/* Get the access tokens for a given CSC
 */
function getAchievementsByRaid($rid,&$db) {
	$rslt = mysql_query('SELECT * FROM pqr_raidaccess WHERE raid_id ='.$rid);
	if (!$rslt) die('csc access token error: '.mysql_error($db));
	
	$accesslist = null;
	while ($row = mysql_fetch_array($rslt)){
		$accesslist[$row['achievement_id']] = $row;
	}
	
	return $accesslist;
}

// Return the unavailable, but eligible-to-raid (valid CSC), players.
function getDailyUnavail(&$db,$day) {
	$sql = "SELECT DISTINCT(pqr_unavail.player_id) FROM pqr_unavail 
		JOIN pqr_csc ON pqr_csc.player_id = pqr_unavail.player_id 
		WHERE unavail = FROM_UNIXTIME(".$day.") 
		AND (role_id > 0 AND CHAR_LENGTH(character_name) > 0)";
	$rslt = mysql_query($sql);
	if (!$rslt) die("daily unavail failure: ".mysql_error($db));

	if (mysql_num_rows($rslt) > 0) {
		while ($row = mysql_fetch_array($rslt)){
			$signups[$row['player_id']] = $row;
		}
	} else {
		$signups = null;
	}
	
	return $signups;
//	return mysql_num_rows($rslt);
}

// Return an array of raids for a given day
function getRaids(&$db,$day) {

	$sql = "SELECT raid_id,info,name,icon,raid_oclock FROM pqr_raids WHERE DATE(raid_oclock) = '".date("Y-m-d",$day)."'";

	$rslt = mysql_query($sql);
	if (!$rslt) die("get raids failure: ".mysql_error($db));
	
	if (mysql_num_rows($rslt) > 0) {	
		while ($row = mysql_fetch_array($rslt)) {
			$raid_info[$row['raid_id']] = $row;
		}
	}
	
	return $raid_info;
}

?>
