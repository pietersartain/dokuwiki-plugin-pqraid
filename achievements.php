<?php

/*
	achievements.php - used to add/edit raids & achievements
		
	author:	P E Sartain	
	date:	21/11/2008
*/

include_once "achievementsfunc.php";

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

?>
