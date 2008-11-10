<?php

/* cscfunc.php - character/spec combo editor functions

	author:	O R Thomas
	date:	05/11/2008
*/


/* Get all character names for a given user
 */
function getCharacterNames($username, &$db) {
	$rslt = mysql_query("SELECT character_id,name FROM pqr_character WHERE player_id = '".$username."'");
	if (!$rslt) die("character sql error: ".mysql_error($db));

	$count = 0;
	while ($row = mysql_fetch_array($rslt)){
		$rsltarray[$count] = $row;
		$count++;
	}

    return $rsltarray;
}

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
		while ($row = mysql_fetch_array($rslt)){
			$csclist[$count] = $row;
			$count++;
		}
		if ($count == 0) {
			$csclist = null;
		}
	}

	return $csclist;	
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

/* Get all access tokens (achievement list)
 */
function getAchievementList(&$db) {
	$rslt = mysql_query('SELECT * FROM pqr_achievements');
	if (!$rslt) die('access token error: '.mysql_error($db));
	
	$count = 0;
	while ($row = mysql_fetch_array($rslt)){
		$accesslist[$count] = $row;
		$count++;
	}
	
	return $accesslist;
}	

/* Get roles
 */
function getRoleList(&$db) {
	$rslt = mysql_query('SELECT * FROM pqr_roles');
	if (!$rslt) die('role sql error: '.mysql_error($db));

	$count = 0;
	while ($row = mysql_fetch_array($rslt)){
		$rolelist[$count] = $row;
		$count++;
	}

	return $rolelist;
}

?>
