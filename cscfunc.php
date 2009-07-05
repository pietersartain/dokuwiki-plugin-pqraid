<?php

/* cscfunc.php - character/spec combo editor functions

	author:	O R Thomas
	date:	05/11/2008
*/

include_once "achievementsfunc.php";

/* Get all CSC information for a given user
 */
function getCSCList($username, &$db) {
	$rslt = mysql_query('SELECT pqr_csc.*,pqr_roles.name FROM pqr_csc 
		LEFT JOIN pqr_roles ON pqr_roles.role_id = pqr_csc.role_id 
		WHERE pqr_csc.player_id = "'.$username.'"');

	if (!$rslt) die("csc sql error: ".mysql_error($db));

//	$count = 0;
	if (mysql_num_rows($rslt) > 3) {
		die("More than 3 CSCs detected. Problem.");
	} else {
		$csclist = null;
		while ($row = mysql_fetch_array($rslt,MYSQL_ASSOC)){
//			$csclist[$count] = $row;
//			$count++;
			// Ensure the character has a name
			if ( ($row['character_name'] != '') && ($row['character_name'] != null) ) {
				$csclist[] = $row;
			}
		}
//		if ($count == 0) {
//			$csclist = null;
//		}
	}

	return $csclist;	
}

/* Get all CSCs
 */
/*
function getCSCFullList(&$db) {
	$rslt = mysql_query('SELECT pqr_csc.*,pqr_roles.name FROM pqr_csc 
		LEFT JOIN pqr_roles ON pqr_roles.role_id = pqr_csc.role_id');

	if (!$rslt) die("get all csc sql error: ".mysql_error($db));
	$csclist = null;
	while ($row = mysql_fetch_array($rslt,MYSQL_ASSOC)){
		$csclist[] = $row;
	}

	return $csclist;	
}
*/

/* Get a list of CSCs grouped by player. Include all relevant CSC info except achievements */
function getCSCInfoByPlayerID(&$db) {
	$rslt = mysql_query('SELECT pqr_csc.*, pqr_roles.*, 
		ROUND((pqr_csc.csc_attended/pqr_csc.csc_possible*100),0) AS csc_percent, 
		ROUND((pqr_csc.csc_attended/(SELECT COUNT(raid_id) FROM pqr_raids)*100),0) AS csc_totalpercent 
						FROM pqr_csc 
						JOIN pqr_roles ON pqr_csc.role_id = pqr_roles.role_id 
						ORDER BY pqr_csc.player_id ASC, 
						csc_id ASC');
	if (!$rslt) die('all csc accesstoken error: '.mysql_error($db));

	$accesslist = null;
	while ($csc = mysql_fetch_array($rslt,MYSQL_ASSOC)){
	
//		print_r($csc);
//		echo "<br />";
	
		// Make sure the character has a name
		if ( ($csc['character_name'] != '') && ($csc['character_name'] != null) ) {
				$accesslist[$csc['player_id']][$csc['csc_id']] = $csc;
		}
		//$accesslist[$csc['player_id']][$csc['csc_id']] = $csc;
	}
	
	return $accesslist;
}

/* Get the list of CSCs matching accesstoken list
 */
function getCSCListWhereAccess(&$db,$raidaccess=null) {
	$rslt = mysql_query('SELECT pqr_csc.*, pqr_roles.*, 
		ROUND((pqr_csc.csc_attended/pqr_csc.csc_possible*100),0) AS csc_percent, 
		ROUND((pqr_csc.csc_attended/(SELECT COUNT(raid_id) FROM pqr_raids)*100),0) AS csc_totalpercent 
						FROM pqr_csc 
						JOIN pqr_roles ON pqr_csc.role_id = pqr_roles.role_id 
						ORDER BY pqr_csc.player_id ASC, 
						csc_percent ASC');
	if (!$rslt) die('all csc accesstoken error: '.mysql_error($db));

	$accesslist = null;
	while ($csc = mysql_fetch_array($rslt,MYSQL_ASSOC)){
		// Get the access tokens for the CSC
		$cscaccess = getCSCAccessTokens($csc['csc_id'],$db);

		$addme = 1;
		if ($raidaccess != null) {
			foreach($raidaccess as $token) {		
				if (!(isset($cscaccess[$token['achievement_id']]))) {

				//echo $csc['character_name']." doesn't have A-".$token['achievement_id']."<br><br><br>";
					$addme = 0;
				}
			}
		}
		
		// Don't do anything to a character with no name
		if ( ($csc['character_name'] == '') || ($csc['character_name'] == null) ) {
			$addme = 0;
		}

		if ($addme) {
			$accesslist[$csc['csc_id']] = $csc;
//			echo " is eligible.";
		}
	}
	
	return $accesslist;
}

function stripCSCListByAvailability(&$db,$loopday,&$csclist) {
	// Unavailable sign ups
	$unavailable = getDailyUnavail($db,$loopday);

	if (count($csclist) > 0) {
		foreach($csclist as $csc) {
			if (isset($unavailable[$csc['player_id']])) {
				// Remove from the CSC list if unavailable
//				print_r($unavailable[$csc['player_id']]);
//				echo " is unavailable.";
				unset($csclist[$csc['csc_id']]);
			}
		}
	}
	
//	print_r($csclist);
//	return $csclist;
}

/* Get a CSCs by ID
 */
function getCSCById(&$db,$cscid) {
	$rslt = mysql_query('SELECT pqr_csc.*, pqr_roles.*, 
		ROUND((pqr_csc.csc_attended/pqr_csc.csc_possible*100),1) AS csc_percent, 
		ROUND((pqr_csc.csc_attended/(SELECT COUNT(raid_id) FROM pqr_raids)*100),1) AS csc_totalpercent 
						FROM pqr_csc 
						JOIN pqr_roles ON pqr_csc.role_id = pqr_roles.role_id 
						WHERE csc_id = '.$cscid.' 
						ORDER BY pqr_csc.player_id ASC, 
						csc_percent ASC');
	if (!$rslt) die('get csc by id error: '.mysql_error($db));

	$csc = null;
	while ($cscinfo = mysql_fetch_array($rslt,MYSQL_ASSOC)){
		$csc[] = $cscinfo;
	}
	
	return $csc;
}


/* Get the access tokens for a given CSC
 */
function getCSCAccessTokens($csc,&$db) {
	$rslt = mysql_query('SELECT * FROM pqr_accesstokens WHERE csc_id ='.$csc);
	if (!$rslt) die('csc access token error: '.mysql_error($db));
	
//	$count = 0;
	$accesslist = null;
	while ($row = mysql_fetch_array($rslt)){
		$accesslist[$row['achievement_id']] = $row['csc_id'];
//		$count++;
	}
	
	return $accesslist;
}

/* Get roles
 */
function getRoleList(&$db) {
	$rslt = mysql_query('SELECT * FROM pqr_roles');
	if (!$rslt) die('role sql error: '.mysql_error($db));

	while ($row = mysql_fetch_array($rslt,MYSQL_ASSOC)){
		$rolelist[$row['role_id']] = $row;
	}

	return $rolelist;
}

/* Get ranks
 */
function getRankList(&$db) {
	$rslt = mysql_query('SELECT * FROM pqr_rank_list');
	if (!$rslt) die('rank sql error: '.mysql_error($db));

	$rolelist = null;

	while ($row = mysql_fetch_array($rslt,MYSQL_ASSOC)){
		$rolelist[$row['rank_id']] = $row;
	}

	return $rolelist;
}

/* Get rank of a player
 */
function getRank(&$db,$id) {
	$rslt = mysql_query('SELECT * FROM pqr_ranks WHERE player_id = "'.$id.'"');
	if (!$rslt) die('rank sql error: '.mysql_error($db));

	$rolelist = null;
	while ($row = mysql_fetch_array($rslt,MYSQL_ASSOC)){
		$rolelist[$row['player_id']] = $row;
	}

	return $rolelist;
}

?>
