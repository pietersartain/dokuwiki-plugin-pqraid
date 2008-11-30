<?php

/*
	calendarInterface.php - used for the AJAX parts of the calendar
		
	author:	P E Sartain	
	date:	21/10/2008
*/

if (isset($_GET['func'])) {
	$func = $_GET['func'];
} else {
	$func = null;
}

include_once "calendarFunc.php";
include_once "achievementsfunc.php";
include_once "cscfunc.php";
include_once "timeFunc.php";
include_once "connect.php";
include_once "defines.php";

// This means we can execute arbitrary PHP code through a querystring.
// This is probably bad ...
eval($func.";");

function makeWeekEditBox($week,$current_info,$day) {
	echo '
	<div class="textbox">
	Week '.$week.': <input type="text" value="'.$current_info.'" id="editWeekBox'.$day.'" onblur="saveEditBox(\''.$week.'\',\''.$day.'\')" />
	</div>';
}

function saveWeekEditBox($week,$new_info,$day) {

	include_once "connect.php";
	$db = getDb();

	if (strlen($new_info) == 0) {
		$sql = "DELETE FROM pqr_weeks WHERE week_num=".$week;
	} else {
		$rslt = mysql_query("SELECT info FROM pqr_weeks WHERE week_num=".$week);
		if (mysql_num_rows($rslt) > 0) {
			$sql = "UPDATE pqr_weeks SET info='".$new_info."' WHERE week_num=".$week;
		} else {
			$sql = "INSERT INTO pqr_weeks(week_num,info) VALUES(".$week.",'".$new_info."')";			
		}
	}
	
	$rslt = mysql_query($sql);
	if (!$rslt){
		die("<br /><br />Error: ".mysql_error($db)." from sql: ".htmlspecialchars($sql));
	}
	
	$out='
	<div onclick="makeEditBox(\''.$week.'\', \''.$new_info.'\',\''.$day.'\')">
	Week '.$week.': '.$new_info.'
	</div>';
	
	echo $out;
}

function saveUnavailable() {

	$username = $_POST['uname'];
	$db = getDb();

//	print_r($_POST);
	
	// Get the players existing information
	$unavail = getUnavailable($db,$_POST['start'],$_POST['end'],$username);
	
	// The current week, for sanity we'll use the same var as calendar.
	$week = (int)$_POST['start'] +1;
	
	for ($days=-7;$days<21;$days++) {
		// The loopday is all relative to the raiding epoch
		//	+$days		-- iterates through 28 cells only
		// +($week*7)	-- allows viewing to cycle by week, not day
		// -3			-- a constant to align the epoch to a Monday
		$loopday = mktime(0, 0, 0, gmdate("m",getRaidEpoch()), gmdate("d",getRaidEpoch())+$days+($week*7)-3, gmdate("Y",getRaidEpoch()));
		
		// Convert both the form and unavail information into easier
		// variables, for comparison later on.
		
		// Form information is backwards, because we care about what's NOT set.
		if (isset($_POST[$loopday])) {
			// Available, make no database entries (delete)
			$newtoken = 0;
		} else {
			// Unavailable
			$newtoken = 1;
		}
		
		// Database information contains when people are NOT available
		if (isset($unavail[$loopday])) {
			// Unavailable
			$oldtoken = 1;
		} else {
			$oldtoken = 0;
		}
		
		if ($oldtoken != $newtoken){
			// If the tokens are not the same, some change has been elicited
			if ($newtoken && !$oldtoken) {
				$sql = "INSERT INTO
					pqr_unavail(player_id,unavail) 
					VALUES('".$username."',FROM_UNIXTIME(".$loopday."))";
			} else {
				$sql = "DELETE FROM pqr_unavail WHERE
					unavail=FROM_UNIXTIME(".$loopday.") AND 
					player_id='".$username."'";
			}
//			echo $sql."<br>";
			runquery($sql,$db);
		}

	} // for
} // function

// Write the raid information into a window for ajax display on the calendar
function showRaid($raid_id) {
	$db = getDb();
	$sql = "SELECT * FROM pqr_raids WHERE raid_id = ".$raid_id;
	$rslt = mysql_query($sql);
	if (mysql_num_rows($rslt) > 0) {
		$row = mysql_fetch_array($rslt);
	
		$signups = getSignupsByDay($db,$raid_id);
		
		$cscs='';
		if ($signups != null) {
			foreach($signups as $csc) {	
				$cscs.=
					"<div style='background: #".$csc['csc_role_colour']."'>".
					$csc['csc_name']."</div>";
			}
		}

		// Achievement information
		$alist = getAchievementList($db);
		$accesslist = getAchievementsByRaid($raid_id,$db);
		$count = 1;
		$achievements='';
		foreach($alist as $at) {
			if (isset($accesslist[$at['achievement_id']])) {
				$checked = 'checked';
			} else {
				$checked = '';
			}

			$achievements .= '
			<div id="atip'.$at['achievement_id'].'" class="tooltip">
			'.$at['long_name'].'
			</div>

			<img src="'.PQIMG.'/'.$at['icon'].'" 
				title="'.$at['long_name'].'" class="achievementcheck"
				onmouseover="showtip(\'atip'.$at['achievement_id'].'\',-220,-60)" 
				onmouseout="hidetip(\'atip'.$at['achievement_id'].'\')"
				></img>

				<input 
				type="checkbox" 
				id="achievement'.$at['achievement_id'].'" 
				name="achievement'.$at['achievement_id'].'" 
				class="achievementcheck"
				'.$checked.' 
				></input>';

			if($count==8) { 
				$achievements .= '<br></br>'; 
				$count = 0;
			}

			$count++;
		}		
	
		$raid_oclock = strtotime($row['raid_oclock']);
	
		$str ='
		<div id="closeX"><a href="#" onclick="boxit()">X</a></div>
		<div id="lefthead"><img src="'.PQIMG.'/'.$row['icon'].'"></img>
		'.$row['name'].' - '.date("d/m/Y h:i",$raid_oclock).'</div>
		
		<div id="addnewraid">
		'.$row['info'].'<br>
		'.$achievements.'
		</div>
		<div id="cscorderlist">'.$cscs.'</div>';
		
		
	
		echo $str;	
	} else {
		echo "Fudged.";
	}
}


function showMakeRaid($datestring) {
	$db = getDb();

	// Role information
	$rlist = getRoleList($db);	
	$rolenames='';
	$roleboxes='';
	foreach ($rlist as $token) {
		$rolenames.=$token['name']."/";
		$roleboxes.=" <input type='text' size='1' name='role".$token['role_id']."' style='background: #".$token['colour']."'>";
	}
	$rolenames=rtrim($rolenames,"/");

	// Achievement information
	$alist = getAchievementList($db);
	$count = 1;
	$achievements='';
	foreach($alist as $at) {
	
		$achievements .= '
		<div id="atip'.$at['achievement_id'].'" class="tooltip">
		'.$at['long_name'].'
		</div>
		
		<img src="'.PQIMG.'/'.$at['icon'].'" 
			title="'.$at['long_name'].'" class="achievementcheck"
			onmouseover="showtip(\'atip'.$at['achievement_id'].'\',-220,-60)" 
			onmouseout="hidetip(\'atip'.$at['achievement_id'].'\')"
			></img>
		
			<input 
			type="checkbox" 
			id="achievement'.$at['achievement_id'].'" 
			name="achievement'.$at['achievement_id'].'" 
			class="achievementcheck"></input>';

		if($count==8) { 
			$achievements .= '<br></br>'; 
			$count = 0;
		}
		
		$count++;
	}

	$cscolist = getCSCOrderList($db);
	
	$cscs='';
	if ($cscolist != null) {
		foreach($cscolist as $csc) {	
			$cscs.=
				"<div style='background: #".$csc['colour']."'>".
				$csc['character_name']."</div>";
		}
	}

	$mraid="
	<div id='closeX'><a href='#' onclick='boxit()'>X</a></div>
	<div id='lefthead'>".date('F jS, Y',$datestring)."</div>
	
	<div id='addnewraid'>
	<form id='newraid' method='POST'
	action='".PQDIR."/calendarInterface.php?func=saveRaid()'>
	
	<table>
		<tr>
			<td>Title:	</td>
			<td><input type='text' name='raidname'><td>
		</tr>
		<tr>
			<td>Icon:	</td>
			<td>".getIconList(-1)."</td>
		</tr>
		<tr>
			<td>".$rolenames."</td>
			<td>".$roleboxes."</td>
		</tr>
		<tr>
			<td>Time 
			(<a href='http://wwp.greenwichmeantime.com/time-zone/europe/uk/time/'>UK</a>)</td>
			<td>".getTimes($datestring,-1)."</td>
		</tr>
		<tr><td colspan='2'>Other info</td><tr>
		<tr><td colspan='2'>
			<textarea name='raid_note' id='raid_note' rows='4' cols='50'></textarea>
		</td><tr>
		<tr><td colspan='2'>Requirements</td><tr>
		<tr><td colspan='2'>".$achievements."</td><tr>
	</table>
	<input type='submit' value='Create'>
	
	</form></div>
	
	Current CSC Order list<br />
	<div id='cscorderlist'>".$cscs."</div>";

	echo $mraid;
}

// List raid icons, mark $sel as selected
function getIconList($sel) {
	$retval = '<select name="icon">';

	$d = dir($_SERVER['DOCUMENT_ROOT']."pq/wiki/lib/plugins/pqraid/images");
	while (false !== ($entry = $d->read())) {
		if ($entry!="." && $entry!=".."){
				$selected = '';
				if ($sel == $entry) $selected='selected="selected"';
				$retval = $retval.'<option value='.$entry.' '.$selected.'>'.$entry.'</option>';
			}
		}
	$d->close();

	$retval = $retval.'</select>';
	
	return $retval;
}

// Produce a drop box of 30min increments, select $sel or 20:00.
function getTimes($tday,$sel) {

	$retval = '<select name="time">';

	for ($x=0;$x<24;$x=$x+1) {
		for ($y=0;$y<60;$y=$y+30) {
		
		$tstamp = mktime(0+$x, 0+$y, 0, gmdate("m",$tday), gmdate("d",$tday), gmdate("Y",$tday));

		$selected="";
		if ($sel == -1) {
			if (($x==20) && ($y==0)) $selected="selected='selected'";
		} else {
			if ($sel == $tstamp) $selected="selected='selected'";
		}
		
		//echo ("Sel: ".$sel." :: tstamp ".$tstamp."<br />".date('F jS, Y - H:i',$sel)." :: ".date('F jS, Y - H:i',$tstamp)."<br />");
	
		$retval = $retval.'<option value="'.$tstamp.'" '.$selected.'>'.date('H:i',$tstamp).'</option>';
		}
	}
	
	$retval = $retval.'</select>';
	return $retval;
}


// Make new raid
function saveRaid() {
	$db = getDB();
	
	$sql="INSERT INTO pqr_raids(name,info,icon,raid_oclock) VALUES(
			'".$_POST['raidname']."',
			'".$_POST['raid_note']."',
			'".$_POST['icon']."',
			FROM_UNIXTIME('".$_POST['time']."'))";
	runquery($sql,$db);

	// Find what was just inserted:
	$sql="SELECT MAX(raid_id) as rid FROM pqr_raids";
	$rslt = mysql_query($sql);
	if (mysql_num_rows($rslt) > 0) {
		$row = mysql_fetch_array($rslt);
	}	

	// All achievements
	$achievements = getAchievementList($db);

	// Then update the achievement lists	
	foreach($achievements as $token) {
		$achievestr = 'achievement'.$token['achievement_id'];

		if (isset($_POST[$achievestr])) {
			$sql = 'INSERT INTO 
				pqr_raidaccess(achievement_id,raid_id) 
				VALUES(
				'.$token['achievement_id'].',
				'.$row['rid'].')';
			runquery($sql,$db);
		}
	} // achievement foreach

	// Role information
	$rlist = getRoleList($db);	
	foreach ($rlist as $token) {
			$sql = 'INSERT INTO 
				pqr_raidroles(raid_id,role_id,quantity) 
				VALUES(
				'.$row['rid'].',
				'.$token['role_id'].',
				'.$_POST['role'.$token['role_id']].')';
			runquery($sql,$db);
	}

	header("location: ".WIKIROOT."/doku.php?id=raid");

}


// Get all CSCs in the cscorder table
/*
function getCSCOrder(&$db) {
	$rslt = mysql_query('SELECT pqr_csc.*,pqr_roles.name FROM pqr_csc 
		LEFT JOIN pqr_roles ON pqr_roles.role_id = pqr_csc.role_id');

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
*/

// This was used to try and rewrite all the availability information after a
// local ajax update. It didn't work well. Deprecated.
/*
function updateUnavailDisplay($day) {
	$db = getDb();
	echo getDailyUnavail($db,$day);
}
*/

?>
