<?php

/* cscfunc.php - character/spec combo editor functions

	author:	O R Thomas
	date:	05/11/2008
*/

include_once "achievementsfunc.php";

/** DEPRECATED **/
/* Get all character names for a given user
 */
/*function getCharacterNames($username, &$db) {
	$rslt = mysql_query("SELECT character_id,name FROM pqr_character WHERE player_id = '".$username."'");
	if (!$rslt) die("character sql error: ".mysql_error($db));

	$count = 0;
	while ($row = mysql_fetch_array($rslt)){
		$rsltarray[$count] = $row;
		$count++;
	}

    return $rsltarray;
}
*/

/* Get all CSC information for a given user
 */
function getCSCList($username, &$db) {
	$rslt = mysql_query('SELECT pqr_csc.*,pqr_roles.name FROM pqr_csc 
		LEFT JOIN pqr_roles ON pqr_roles.role_id = pqr_csc.role_id 
		WHERE pqr_csc.player_id = "'.$username.'"');

	if (!$rslt) die("csc sql error: ".mysql_error($db));

	$count = 0;
	if (mysql_num_rows($rslt) > 3) {
		die("More than 3 CSCs detected. Problem.");
	} else {
		while ($row = mysql_fetch_array($rslt,MYSQL_ASSOC)){
			$csclist[$count] = $row;
			$count++;
		}
		if ($count == 0) {
			$csclist = null;
		}
	}

	return $csclist;	
}

/**** pqr_cscorder DEPRECATED *******/
/* Get the cscorder list
*/
/*function getCSCOrderList(&$db) {
	$rslt = mysql_query('SELECT pqr_cscorder.*,pqr_csc.*,pqr_roles.*  
		FROM pqr_cscorder 
		LEFT JOIN pqr_csc ON pqr_csc.csc_id = pqr_cscorder.csc_id 
		LEFT JOIN pqr_roles ON pqr_roles.role_id = pqr_csc.role_id 
		ORDER BY cscorder ASC, order_id ASC');
	if (!$rslt) die('csc order list error: '.mysql_error($db));
	
	$cscolist = null;
	while ($row = mysql_fetch_array($rslt,MYSQL_ASSOC)){
		$cscolist[$row['order_id']] = $row;
	}
	
	return $cscolist;
	
}
*/


/* Get the cscorder list for a given player
*/
/*function getCSCOrderListByPlayer($player,&$db) {
	$rslt = mysql_query('SELECT * 
		FROM pqr_cscorder 
		WHERE player_id = "'.$player.'" 
		ORDER BY cscorder ASC');
	if (!$rslt) die('csc order list error: '.mysql_error($db));
	
	$cscolist = null;
	while ($row = mysql_fetch_array($rslt)){
		$cscolist[$row['order_id']] = $row;
	}
	
	return $cscolist;
	
}
*/

/* Get the list of CSCs matching accesstoken list
 */
function getCSCListWhereAccess(&$db,$raidaccess) {
	$rslt = mysql_query('SELECT pqr_csc.*, pqr_roles.*, 
		(pqr_csc.csc_attended/pqr_csc.csc_possible*100) AS csc_percent 
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

		if ($addme) {
			$accesslist[] = $csc;
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
	//			echo " is unavailable.";
				unset($csclist[$csc['csc_id']]);
			}
		}
	}
	
//	return $csclist;
}

/* Get a CSCs by ID
 */
function getCSCById(&$db,$cscid) {
	$rslt = mysql_query('SELECT pqr_csc.*, pqr_roles.*, 
		(pqr_csc.csc_attended/pqr_csc.csc_possible*100) AS csc_percent 
						FROM pqr_csc 
						JOIN pqr_roles ON pqr_csc.role_id = pqr_roles.role_id 
						WHERE csc_id = '.$cscid.' 
						ORDER BY pqr_csc.player_id ASC, 
						csc_percent ASC');
	if (!$rslt) die('all csc accesstoken error: '.mysql_error($db));

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

?>
