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

// Return an array of unavailability information
function getUnavailable(&$db,$start,$end) {

	$sql = "SELECT * FROM pqr_unavail WHERE 
		unavail > FROM_UNIXTIME(".weektoDate($start).") AND 
		unavail < FROM_UNIXTIME(".weekToDate($end).") AND
		player_id = '".$player."'";	

	$rslt = mysql_query($sql);

	if (!$rslt) die("unavail failure: ".mysql_error($db));
	return mysql_num_rows($rslt);
}



?>
