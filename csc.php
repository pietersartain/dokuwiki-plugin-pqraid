<?php

/* csc.php - character/spec combo editor

	author:	O R Thomas
	date:	05/11/2008
*/

include_once "cscfunc.php";
include_once "calendarFunc.php";

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

/* Build a radio group from $ranks
 */
function mkRankList($roles,$id,$selected) {
	$rolelist = "";
	
//	if ($selected==null)
//		$selected = 2;
		
	foreach($roles as $rinfo) {

//		echo $rinfo['rank_id']." :: ".$selected."<br>";

		if ($rinfo['rank_id'] == $selected) {
			$cscset = "checked";
		} else {
			$cscset = "";
		}
		
		$rolelist .= "
		
			<div id='ranktip".$rinfo['rank_id']."' class='tooltip'>
				".$rinfo['rank_desc']."
			</div>
		
			<input 
				type='radio' 
				name='ranklist' 
				value='".$rinfo['rank_id']."' 
				onmouseover='showtip(\"ranktip".$rinfo['rank_id']."\",3,3)' 
				onmouseout='hidetip(\"ranktip".$rinfo['rank_id']."\")'
				onchange='updateCSC(this.form)'
				".$cscset.">".$rinfo['rank_name']."&nbsp;&nbsp;";
	}
	
	return $rolelist;
}

/* Build a tickable list of accesstokens */
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

/* Build a drop list of classes.
 * $id : csc ID
 * $selected : current saved class
 */
function mkClassList($id,$selected=''){
	$classlist = "<select name='classlist".$id."' 
					id='classlist".$id."' 
					class='classlist' 
					onchange='updateCSC(this.form)'
					>";
	
	// For colouring the classes
/*	$colours['DRUID']="#FF7D0A";
	$colours['HUNTER']="#ABD473";
	$colours['MAGE']="#69CCF0";
	$colours['PALADIN']="#F58CBA";
	$colours['PRIEST']="#000000";
	$colours['ROGUE']="#FFF569";
	$colours['SHAMAN']="#2459FF";
	$colours['WARLOCK']="#9482CA";
	$colours['WARRIOR']="#C79C6E";
	$colours['DEATHKNIGHT']="#C41F3B";
*/

	$class['DEATHKNIGHT']="Death Knight";
	$class['DRUID']="Druid";
	$class['HUNTER']="Hunter";
	$class['MAGE']="Mage";
	$class['PALADIN']="Paladin";
	$class['PRIEST']="Priest";
	$class['ROGUE']="Rogue";
	$class['SHAMAN']="Shaman";
	$class['WARLOCK']="Warlock";
	$class['WARRIOR']="Warrior";
	
	foreach($class as $key=>$cname) {
		if ($key == $selected) {
			$cscset = "selected='selected'";
		} else {
			$cscset = "";
		}
		
		$classlist .= "<option value='".$key."' ".$cscset.">".$cname."</option>";
	}
	
	if ($selected == '') {
		$noneset = "selected='selected'";
	}
	
	$classlist .= "
			<option value='' ".$noneset.">None</option>
		</select>";
	
	return $classlist;
}

function getCSCEditor(&$db) {
	global $INFO;

	//Acquire the user's name.
	$username = $INFO['client'];

	// Get the rank
	$rank = getRank($db,$username);

	// Run initialisation on ranks
	if ($rank == null) {
	echo "rank init.";
		$sql = 'INSERT INTO pqr_ranks(player_id,rank_id,count,total,last)
				VALUES("'.$username.'","2","0","'.getNumRaids($db).'","0")';
		mysql_query($sql);
		// Rerun to init the ranks
		$rank = getRank($db,$username);
	}
	
//	print_r($rank);
//	echo getNumRaids($db);

	$csceditor = "<form id='csceditor' method='POST' action='lib/plugins/pqraid/cscInterface.php?func=saveCSC()'>
	<input type='hidden' name='uname' value='".$username."'></input>

		<table class='table'>
		<tr><td></td><td></td><td><td></tr>
		<tr><td colspan='3'>

		Rank: ".mkRankList(getRankList($db),$username,$rank[$username]['rank_id'])."		
		
		</td></tr>
		<tr><td colspan='3'><hr /></td></tr>";
	
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
			".mkRoleList($roles,$cscid,$cscinfo['role_id'])."
			".mkClassList($cscid,$cscinfo['csc_class'])." 
		</div>
		</td>
	</tr>
	<tr>
		<td>
			".mkAccessTokenBoxes($accesslist,$achievements,$cscid,8)."
		</td>
	</tr>
	<tr><td colspan='3'><hr /></td></tr>";

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
