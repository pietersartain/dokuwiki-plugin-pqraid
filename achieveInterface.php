<?php

/*
	achieveInterface.php - used for the AJAX parts of the achievement editor
		
	author:	P E Sartain	
	date:	26/11/2008
*/

if (isset($_GET['func'])) {
	$func = $_GET['func'];
} else {
	$func = null;
}

include_once "achievementsfunc.php";
include_once "connect.php";
include_once "achievements.php";

// This means we can execute arbitrary PHP code through a querystring.
// This is probably bad ...
eval($func.";");

function saveAchievements() {

	$username = $_POST['uname'];
	$db = getDb();

//	print_r($_POST);
	
	// Get the achievements
	$alist = getAchievementList($db);
	
	foreach($alist as $token) {
		$aid = $token['achievement_id'];
		$sql = '';
		
		if (isset($_POST['del'.$aid]) && $_POST['del'.$aid] == '1') {
			// delete this achievement
			$sql = "DELETE FROM pqr_accesstokens WHERE achievement_id=".$aid;
			runquery($sql,$db);
			$sql = "DELETE FROM pqr_raidaccess WHERE achievement_id=".$aid;
			runquery($sql,$db);
			$sql = "DELETE FROM pqr_achievements WHERE achievement_id=".$aid;
			runquery($sql,$db);
		} else {
		
		if (
			($token['icon'] != $_POST['icon'.$aid]) || 
			($token['long_name'] != $_POST['long'.$aid]) ||	
			($token['short_name'] != $_POST['short'.$aid])) {
			$sql = 'UPDATE pqr_achievements SET
						icon = "'.$_POST['icon'.$aid].'",
						long_name = "'.$_POST['long'.$aid].'",
						short_name = "'.$_POST['short'.$aid].'"
						WHERE achievement_id = '.$aid;
			runquery($sql,$db);
			}
		}
		
//		echo $sql."<br>";
		
	} // foreach

	echo buildAchieveTable($db);

} // function

function addAchievement(){
	$db = getDb();
	
	$sql = "INSERT INTO pqr_achievements(short_name,long_name,icon) VALUES('".$_POST['newshort0']."','".$_POST['newlong0']."','".$_POST['newicon0']."')";
	runquery($sql,$db);
	
	echo buildAchieveTable($db);
}
