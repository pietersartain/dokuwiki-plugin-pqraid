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
//eval($func.";");

// This is a bit more secure.
switch("ui$func") {
	case "uisaveAchievements":
		saveAchievements();
		break;
	case "uiaddAchievement":
		addAchievement();
		break;
	default:
		echo "$func not found in ".$_SERVER['PHP_SELF'];
		break;
}

function saveAchievements() {

	$username = htmlspecialchars($_POST['uname'],ENT_QUOTES);
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
						long_name = "'.htmlspecialchars($_POST['long'.$aid],ENT_QUOTES).'",
						short_name = "'.htmlspecialchars($_POST['short'.$aid],ENT_QUOTES).'" 
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
	
	$sql = "INSERT INTO pqr_achievements(short_name,long_name,icon) VALUES('".htmlspecialchars($_POST['newshort0'])."','".htmlspecialchars($_POST['newlong0'],ENT_QUOTES)."','".htmlspecialchars($_POST['newicon0'],ENT_QUOTES)."')";
	runquery($sql,$db);
	
	echo buildAchieveTable($db);
}
