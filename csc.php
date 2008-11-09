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
					onchange='updateCSC()'
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

function mkAccessTokenBoxes($cscid,$accesslist,$achievements,$id) {
	
	foreach ($achievements as $at) {
		$tokenboxes .= '<input 
			type="checkbox" 
			id="'.$id.'achievement'.$at['achievement_id'].'" 
			name="'.$id.'achievement'.$at['achievement_id'].'" 
			'.$lockout.'></input>'.
			$at['short_name'].$at['long_name'].$at['icon'].'<br />';
	}

	return $tokenboxes;
}

function getCSCEditor(&$db) {
	global $INFO;
	$csceditor = "<form id='csceditor' method='POST' action='lib/plugins/pqraid/cscInterface.php?func=saveCSC()'>
					<table class='table'>";
	//Acquire the user's name.
	$username = $INFO['client'];
	
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
	$achievements = getAchivementList($db);
	
	// List all the CSCs, with the appropriately selected boxes
	$x=0;
	if (count($csclist) > 0) {
		foreach ($csclist as $cscinfo) {

		// Get the access tokens for a given CSC
		$accesslist = getCSCAccessTokens($cscinfo['csc_id'],$db);
			
		$csceditor .= "
	<tr><td>
		<input type='hidden' name='cscid".$x."' value='".$cscinfo['csc_id']."'></input>
		<div id=''><input 
			type='text' 
			class='edit' 
			value='".$cscinfo['character_name']."'
			name='character_name".$x."' 
			onchange='updateCSC()' 
			id='character_name".$x."' ></input></div>
		<div id=''>".mkRoleList($roles,$x,$cscinfo['role_id'])."</div>
		</td>
		<td width=300 height=100>
			".mkAccessTokenBoxes($cscinfo['csc_id'],$accesslist,$achievements,$x)."
	</td></tr>
	<tr><td colspan='2'><hr /></td></tr>";

			++$x;
		}
	}

	// Build up the remaining CSC boxes as empty ones.
/*	for ($x=count($csclist);$x<3;++$x) {

$csceditor .= "
	<tr><td>
		<input type='hidden' name='cscid".$x."' value='-1'></input>
		<div id=''><input 
			type='text' 
			class='edit' 
			value=''
			name='character_name".$x."' 
			id='character_name".$x."' ></input></div>
		<div id=''>".mkRoleList($roles,$x,null)."</div>
		</td>
		<td width=300 height=100>
			".mkAccessTokenBoxes(null,null,$achievements,$x)."
	</td></tr>
	<tr><td colspan='2'><hr /></td></tr>";
	}
*/
	$csceditor .= "</table>
	
					<input type='submit' value='Save' class='button'>
					<div id='saveinfo' class='saved'>Saved.</div>
					</form>";
	return $csceditor;
}






?>