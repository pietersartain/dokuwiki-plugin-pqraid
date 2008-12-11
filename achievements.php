<?php

/*
	achievements.php - used to add/edit raids & achievements
		
	author:	P E Sartain	
	date:	21/11/2008
*/

include_once "achievementsfunc.php";
include_once "cscfunc.php";

define("THISPAGE","http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);

function add_querystring_var($url, $key, $value) {
	$url = preg_replace('/(.*)(\?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
	$url = substr($url, 0, -1);
	
	if (strpos($url, '?') === false) {
		return ($url . '?' . $key . '=' . $value);
	} else {
		return ($url . '&' . $key . '=' . $value);
	}
}

function getAchievements($db) {
	$astr='<div id="saveinfo" class="saved">Saved.</div><br /><br />
		<form id="achievementform" name="achievementform" method="POST">
		<input type="hidden" name="uname" value="'.$username.'"></input>
		<div id="achievements">'.buildAchieveTable($db).'</div>
		</form>';
return $astr;
}

function buildAchieveTable($db) {

	$astr='<table id="aeditor">
			<tr>
				<th>Icon</th>
				<th>Icon path</th>
				<th>Short name</th>
				<th>Long name</th>
				<th></th>
			</tr>';

	$achievements = getAchievementList($db);
	foreach ($achievements as $token) {
		$astr.='
		
<tr>
	<td class="img">
		<img 
			src="lib/plugins/pqraid/images/'.$token['icon'].'" 
			id="img'.$token['achievement_id'].'" 
			name="img'.$token['achievement_id'].'" 
			>
		</img>
	</td>
	<td><input type="text" 
			size="8"
			id="icon'.$token['achievement_id'].'" 
			name="icon'.$token['achievement_id'].'" 
			onchange="updateAchievements(\''.$token['achievement_id'].'\',this.form)" 
			value="'.$token['icon'].'">
		</input>
	</td>
	<td><input type="text" 
			size="8"
			id="short'.$token['achievement_id'].'" 
			name="short'.$token['achievement_id'].'" 
			onchange="updateAchievements(\''.$token['achievement_id'].'\',this.form)"
			value="'.$token['short_name'].'">
		</input>
	</td>
	<td><input type="text" 
			size="12"
			id="long'.$token['achievement_id'].'" 
			name="long'.$token['achievement_id'].'" 
			onchange="updateAchievements(\''.$token['achievement_id'].'\',this.form)"
			value="'.$token['long_name'].'">
		</input>
	</td>
	<td>
		<input type="checkbox" 
			id="del'.$token['achievement_id'].'" 
			name="del'.$token['achievement_id'].'" 
			value="1" 	onchange="updateAchievements(\''.$token['achievement_id'].'\',this.form)"
			></input>
	</td>
</tr>
			';
	}

// Used for having multiple new achievement boxes.
/*
add_querystring_var(THISPAGE,"add","0");
for ($x=0;$x<$addnew;$x++) {
*/

$x = 0;

	$astr.='

<tr>
	<td class="img"><img src="lib/plugins/pqraid/images/mystery.png"
				id="newimg'.$x.'" 
				name="newimg'.$x.'" 
				></img></td>
	<td><input type="text" 
			size="8"
			id="newicon'.$x.'" 
			name="newicon'.$x.'" 
			onchange="unsavedAchieve('.$x.')" 
			value="">
		</input>
	</td>
	<td><input type="text" 
			size="8"
			id="newshort'.$x.'" 
			name="newshort'.$x.'" 
			onchange="unsavedAchieve('.$x.')" 
			value="">
		</input>
	</td>
	<td><input type="text" 
			size="12"
			id="newlong'.$x.'" 
			name="newlong'.$x.'" 
			onchange="unsavedAchieve('.$x.')" 
			value="">
		</input>
	</td>
	<td>
		<a href="#" onclick="addAchievement(forms.achievementform);">[+]</a>
	</td>
</tr>
	</table>';
			
return $astr;

}

function showAchievements(&$db,$character){

	$astr = '';
	$achievements = getAchievementList($db);


	if ($character == null) {
		// Show all in a grid	
//		$achievements = getAchievementList($db);
		$csclist = getCSCListWhereAccess($db);

		$astr .= "<table><tr><th></th>";

		foreach($achievements as $token) {
			$astr .= '<th><img 
			src="lib/plugins/pqraid/images/'.$token['icon'].'" 
			id="img'.$token['achievement_id'].'" 
			name="img'.$token['achievement_id'].'" 
			></img></th>';
		}

		$astr .= "</tr>";

		foreach($csclist as $csc) {
			$access = getCSCAccessTokens($csc['csc_id'],$db);
			
			$astr .= '<tr><td>'.$csc['character_name'].'</td>';
			
			foreach($achievements as $token) {
				$astr .= "<td>";
				if (isset($access[$token['achievement_id']])) {
					$astr .= 'X';
				}
				$astr .= "</td>";
			}
			
			$astr .= '</tr>';
		}

		$astr .= "</table>";

	} else {
		// Show just the one
		//$csclist = getCSCList($character,$db);
		
		// Because I don't have a function (or any other need) to 
		// call by name, let's inline it.
		$rslt = mysql_query('SELECT * 
				FROM pqr_accesstokens 
				JOIN pqr_csc ON pqr_accesstokens.csc_id = pqr_csc.csc_id 
				JOIN pqr_achievements ON pqr_accesstokens.achievement_id = pqr_achievements.achievement_id 
				WHERE pqr_csc.character_name ="'.$character.'"');
		if (!$rslt) die('csc access token by name error: '.mysql_error($db));

		$accesslist = null;
		while ($row = mysql_fetch_array($rslt)){
			$accesslist[$row['achievement_id']] = $row['icon'];
		}

//		print_r($accesslist);

		if (count($accesslist) > 0) {
			foreach($accesslist as $csc) {
				$astr .= '<img 
			src="lib/plugins/pqraid/images/'.$csc.'" style="margin: 1px;"></img>';
			}
		}
	}
	
	
	

return $astr;
}

?>
