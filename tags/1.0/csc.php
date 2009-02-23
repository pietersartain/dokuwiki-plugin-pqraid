<?php

/* csc.php - character/spec combo editor

	author:	O R Thomas
	date:	05/11/2008
*/

include_once "cscfunc.php";

/* Build a drop box from $roles, with the id rolelist.$id and optionally
 * set the value to $selected (where $roles[..]['role_id'] == $selected)
 */
function mkRoleList($roles,$id,$selected) {
	$rolelist = "<select name='rolelist".$id."' 
					id='rolelist".$id."' 
					class='rolelist' 
					onchange='updateCSC(this.form)'
					>";
	
	foreach($roles as $rinfo) {
		if ($rinfo['role_id'] == $selected) {
			$cscset = "selected='selected'";
		} else {
			$cscset = "";
		}
		
		$rolelist .= "<option value='".$rinfo['role_id']."' ".$cscset.">".$rinfo['name']."</option>";
	}
	
	if ($selected == "-1") {
		$noneset = "selected='selected'";
	}
	
	$rolelist .= "
			<option value='-1' ".$noneset.">None</option>
		</select>";
	
	return $rolelist;
}

function mkAccessTokenBoxes($accesslist,$achievements,$id,$width) {
	
	$count = 1;
	foreach ($achievements as $at) {
		if (isset($accesslist[$at['achievement_id']])) {
			$checked = 'checked';
		} else {
			$checked = '';
		}
		
		$tokenboxes .= '
		
		<div id="tip'.$at['achievement_id'].'" class="tooltip">
		'.$at['long_name'].'
		</div>
		
		<img src="lib/plugins/pqraid/images/'.$at['icon'].'" 
			title="'.$at['long_name'].'" class="achievementcheck"
			onmouseover="showtip(\'tip'.$at['achievement_id'].'\',3,3)" 
			onmouseout="hidetip(\'tip'.$at['achievement_id'].'\')"
			width=29
			height=31
			></img>
		
			<input 
			type="checkbox" 
			id="'.$id.'achievement'.$at['achievement_id'].'" 
			name="'.$id.'achievement'.$at['achievement_id'].'" 
			class="achievementcheck"
			onmouseover="showtip(\'tip'.$at['achievement_id'].'\',3,3)" 
			onmouseout="hidetip(\'tip'.$at['achievement_id'].'\')"
			'.$checked.' 
			onchange="updateCSC(this.form)"></input>';

		if($count==$width) { 
			$tokenboxes .= '<br></br>'; 
			$count = 0;
		}
		
		$count++;
	}

	return $tokenboxes;
}

function getCSCEditor(&$db) {
	global $INFO;

	//Acquire the user's name.
	$username = $INFO['client'];

	$csceditor = "<form id='csceditor' method='POST' action='lib/plugins/pqraid/cscInterface.php?func=saveCSC()'>
	<input type='hidden' name='uname' value='".$username."'></input>

					<table class='table'>";
	
	//Get the CSC list (if any)
	$csclist = getCSCList($username, $db);
	
	if ($csclist == null) {
		// No CSCs detected, so initialise the CSC list in the DB before we go on.
		$sql = 'INSERT INTO pqr_csc(role_id,player_id) 
				VALUES("-1","'.$username.'")';
		for ($x=0;$x<3;$x++) {
			mysql_query($sql);
		}
		// Get the CSC list again, post initilialisation:
		$csclist = getCSCList($username,$db);		
	}

	//Get the role list
	$roles = getRoleList($db);
	
	//Get the achievement list
	$achievements = getAchievementList($db);
	
	// List all the CSCs, with the appropriately selected boxes
	$x=0;
	if (count($csclist) > 0) {
		foreach ($csclist as $cscinfo) {
		$cscid = $cscinfo['csc_id'];

		// Get the access tokens for a given CSC
		$accesslist = getCSCAccessTokens($cscinfo['csc_id'],$db);
			
		$csceditor .= "
	<tr>
		<td>
		<input type='hidden' name='cscid".$x."' value='".$cscid."'></input>
		<div id=''><input 
			type='text' 
			class='edit' 
			value='".$cscinfo['character_name']."'
			name='character_name".$cscid."' 
			onchange='updateCSC(this.form)' 
			id='character_name".$cscid."' ></input>
			".mkRoleList($roles,$cscid,$cscinfo['role_id'])."</div>
		</td>
	</tr>
	<tr>
		<td>
			".mkAccessTokenBoxes($accesslist,$achievements,$cscid,8)."
		</td>
	</tr>
	<tr><td colspan='2'><hr /></td></tr>";

			++$x;
		}
	}

	$csceditor .= "</table>
	
					<!--//<input type='submit' value='Save' class='button'>//-->
					<div id='saveinfo' class='saved'>Saved.</div>
					</form>";
	return $csceditor;
}






?>