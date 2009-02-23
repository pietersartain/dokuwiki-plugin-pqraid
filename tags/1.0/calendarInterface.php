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
//eval($func.";");

// This is a bit more secure
switch("ui$func") {
	case "uishowRaid":
		showRaid($_GET['arg1']);
		break;
	case "uishowMakeRaid":
		showMakeRaid($_GET['arg1']);
		break;
	case "uiwriteCSCList":
		writeCSCList($_GET['arg1']);
		break;
/*	case "uimakeWeekEditBox":
		makeWeekEditBox($_GET['arg1'],$_GET['arg2'],$_GET['arg2']);
		break;
*/	case "uisaveWeekEditBox":
		saveWeekEditBox();
		break;
	case "uisaveUnavailable":
		saveUnavailable();
		break;
	case "uisaveRaid":
		saveRaid();
		break;
	default:
		echo "$func not found in ".$_SERVER['PHP_SELF'];
		break;
}

/* DEPRECATED */
/*
function makeWeekEditBox($week,$current_info,$day) {
	echo '
	<div class="textbox">
	Week '.$week.': <input type="text" value="'.$current_info.'" id="editWeekBox'.$day.'" onblur="saveEditBox(\''.$week.'\',\''.$day.'\')" />
	</div>';
}
*/

function saveWeekEditBox() {

	print_r($_GET);
	$week = $_GET['arg2'];
	$new_info = $_GET['arg1'];

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

/*	
	$out='
	<div onclick="makeEditBox(\''.$week.'\', \''.$new_info.'\',\''.$day.'\')">
	Week '.$week.': '.$new_info.'
	</div>';
	
	echo $out;
*/
}

function saveUnavailable() {

	$username = htmlspecialchars($_POST['uname'],ENT_QUOTES);
	$db = getDb();

	print_r($_POST);
	
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
			echo $sql."<br>";
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
//				$checked = 'checked';


			$achievements .= '
			<div id="atip'.$at['achievement_id'].'" class="tooltip">
			'.$at['long_name'].'
			</div>

			<img src="'.PQIMG.'/'.$at['icon'].'" 
				title="'.$at['long_name'].'" class="achievementcheck"
				onmouseover="showtip(\'atip'.$at['achievement_id'].'\',-220,-60)" 
				onmouseout="hidetip(\'atip'.$at['achievement_id'].'\')"
				height=31 
				width=29 
				></img>';

/*
				<input 
				type="checkbox" 
				id="achievement'.$at['achievement_id'].'" 
				name="achievement'.$at['achievement_id'].'" 
				class="achievementcheck"
onmouseover="showtip(\'atip'.$at['achievement_id'].'\',-220,-60)" 
				onmouseout="hidetip(\'atip'.$at['achievement_id'].'\')"				
				'.$checked.' 
				></input>';


			} else {
				$checked = '';
*/			}


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
		<div id="csclist">'.$cscs.'</div>';
		
		
	
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
		$roleboxes.=" <input type='text' size='1' name='rolecount' id='rolecount".$token['role_id']."'
		style='background: #".$token['colour']."' value='0'>";
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
			height=31 
			width=29 
			></img>
		
			<input 
			type="checkbox" 
			id="achievement'.$at['achievement_id'].'" 
			name="achievement[]" 
			value="'.$at['achievement_id'].'" 
			onmouseover="showtip(\'atip'.$at['achievement_id'].'\',-220,-60)" 
			onmouseout="hidetip(\'atip'.$at['achievement_id'].'\')" 
			onchange="updateRaidAchievement(this.form,\''.$datestring.'\')" 
			class="achievementcheck"></input>';

		if($count==8) { 
			$achievements .= '<br></br>'; 
			$count = 0;
		}
		
		$count++;
	}

	$mraid="

	<div id='closeX'><a href='#' onclick='boxit()'>X</a></div>
	<div id='lefthead'>".date('F jS, Y',$datestring)."</div>

	<form id='newraid' method='POST' name='newraid' 
	action='".PQDIR."/calendarInterface.php?func=saveRaid'>
	
	<div id='addnewraid'>
	
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
		
		<tr>
			<td>Scheduling:</td>
			<td>
			
			<div id='sendglobaltip' class='tooltip'>
			<b>Open Invitation / No Raid Time</b><br>
			If this box is checked, all eligible 
			members will be sent an invitation.
			Any boxes checked in the current CSC 
			list will be ignored and no raid time
			will be creditted.
			</div>
			
			<input type='checkbox' name='sendglobal'
			onmouseover='showtip(\"sendglobaltip\",-220,-60)'
			onmouseout='hidetip(\"sendglobaltip\")'>
			
			<div id='noincrementtip' class='tooltip'>
			<b>Closed Invitation / No Raid Time</b><br>
			If this box is checked, only members selected
			from the current CSC list will be invited,
			however no raid time will be creditted.
			</div>
			
			<input type='checkbox' name='noincrement'
			onmouseover='showtip(\"noincrementtip\",-220,-60)'
			onmouseout='hidetip(\"noincrementtip\")'>			
			
			</td>
		</tr>
		
		<tr><td colspan='2'>Other info</td><tr>
		<tr><td colspan='2'>
			<textarea name='raid_note' id='raid_note' rows='4' cols='50'></textarea>
		</td><tr>
		<tr><td colspan='2'>Requirements</td><tr>
		<tr><td colspan='2'>".$achievements."</td><tr>
	</table>
	<input type='submit' value='Create'>
	</div>";
	
	

	echo $mraid;
	echo "
		<div id='cscstatus'>Current CSC list</div><br />
		<div id='csclist'>";
		writeCSCList($datestring);
	echo "
	</div>
	
	</form>";
}

//
function writeCSCList($loopday,$raidaccess=null,$checkedcsc=null) {
	$db = getDb();
	
	$loopday = mktime(0,0,0,date("m",$loopday),date("d",$loopday),date("Y",$loopday));

	if (isset($_POST['achievement'])) {
		// Build a raidaccess array
		foreach($_POST['achievement'] as $achievement) {
			$raidaccess[$achievement] = array('achievement_id'=>$achievement);
		}
	}

	if (isset($_POST['cscid'])) {
		// Build a checkedcsc array
		foreach($_POST['cscid'] as $csc) {
			$checkedcsc[$csc] = $csc;
		}
	}

	// Get a CSC list, limit by access where provided
	$csclist = getCSCListWhereAccess($db,$raidaccess);
	
	// Trim that down by day
	stripCSCListByAvailability($db,$loopday,$csclist);
	
//	print_r($csclist);
	
	$cscs = '';
	$player = '';
	$vlist = array();

	// Get a user list to convert from user_id to a real name
	$users = _loadUserData();

	if (count($csclist) > 0) {
		foreach($csclist as $csc) {
			if ($csc['player_id'] != $player) {
				$cscs.="<div class='cscplayer'>".$users[$csc['player_id']]['name']."</div>";
				$player = $csc['player_id'];
			}

			if (isset($checkedcsc[$csc['csc_id']])) {
				$checked="checked";

				if (isset($vlist[$csc['role_id']])) {
					$vlist[$csc['role_id']]++;
				} else {
					$vlist[$csc['role_id']]=1;
				}

			} else {
				$checked="";
			}

			$cscs.="<div style='background: #".$csc['colour']."' class='csc'>
			<input type='checkbox' name='cscid[]' 
				value='".$csc['csc_id']."' $checked
				onchange='updateCSCCountSolo(".$csc['role_id'].",this);'
				id='".$csc['role_id']."|".$csc['csc_id']."'
				>&nbsp;&nbsp;".
					$csc['character_name']." - ".$csc['csc_percent']."%</div>";
		
		}
	}

	echo $cscs;

}

// Make new raid
function saveRaid() {
	$db = getDB();

	$users = _loadUserData();

	$sql="INSERT INTO pqr_raids(name,info,icon,raid_oclock) VALUES(
			'".htmlspecialchars($_POST['raidname'],ENT_QUOTES)."',
			'".htmlspecialchars($_POST['raid_note'],ENT_QUOTES)."',
			'".htmlspecialchars($_POST['icon'],ENT_QUOTES)."',
			FROM_UNIXTIME('".$_POST['time']."'))";
	runquery($sql,$db);

	// Find what was just inserted:
	$sql="SELECT MAX(raid_id) as rid FROM pqr_raids";
	$rslt = mysql_query($sql);
	if (mysql_num_rows($rslt) > 0) {
		$row = mysql_fetch_array($rslt);
	}	

	if (isset($_POST['achievement'])) {
		// Process the raidaccesses
		foreach($_POST['achievement'] as $achievement) {
		
		$sql = 'INSERT INTO 
				pqr_raidaccess(achievement_id,raid_id) 
				VALUES(
				'.$achievement.',
				'.$row['rid'].')';
			runquery($sql,$db);

		$raidaccess[$achievement] = array('achievement_id'=>$achievement);
		}
	} else {
		$raidaccess = null;
	}

	// Perform raid scheduling
	
	$loopday = $_POST['time'];
	$loopday = mktime(0,0,0,date("m",$loopday),date("d",$loopday),date("Y",$loopday));
	
//	scheduleCSC($row['rid'],$scheduledRoles,$loopday);


	// Get a CSC list, limit by access for this raid
	$csclist = getCSCListWhereAccess($db,$raidaccess);
	
	// Trim that down by day
	stripCSCListByAvailability($db,$loopday,$csclist);

	// Sort the CSC list by role, for use later on.
	//msort($csclist,"role_id");

	// If "open invite" is **NOT** set
	if (!isset($_POST['sendglobal'])) {
		$updatepossible = 1;

		// For each CSC ticked
		if (isset($_POST['cscid'])) {

		// Randomise the raid leader
		$leader = getRandomLeader($_POST['cscid'],$db);

			foreach($_POST['cscid'] as $cscid) {
			// Mail & save all the people who were invited

				// Get the CSC information
				list($csc) = getCSCById($db,$cscid);
				$raidinvites[] = $csc;

				// Save the invitees to the signup table
				$sql = "INSERT INTO pqr_signups(raid_id, player_id, csc_name, csc_role, csc_role_colour) VALUES('".$row['rid']."','".$csc['player_id']."','".$csc['character_name']."','".$csc['name']."','".$csc['colour']."')";
				runquery($sql,$db);

				// If "closed invitation / no raid time" is **NOT** checked
				if (!isset($_POST['noincrement'])) {
					// Update the raid times
					$sql = "UPDATE pqr_csc SET csc_attended=csc_attended+1 
						WHERE csc_id=".$cscid;
					runquery($sql,$db);
				} else {
					$updatepossible = 0;
				}
			}
			
		foreach($raidinvites as $csc){
			
		// Mail these people.
		$to = $users[$csc['player_id']]['mail'];
	sendRaidMessage($_POST['raidname'],
		$_POST['time'],
		$leader,
		htmlspecialchars($_POST['raid_note'],ENT_QUOTES),
		$to,
		array($csc['character_name'],$csc['name']),
		$raidinvites);
		
		}
			
		}
	
	} else {
		$updatepossible = 0;
		
		// Randomise the raid leader
		foreach($csclist as $csc) {
			$leader_list[] = $csc['csc_id'];
		}
		
		$leader = getRandomLeader($leader_list,$db);
		
		// Send everyone a mail
		foreach($csclist as $csc) {
			//echo $key." | ".$user['mail']."<br>";

	$to = $users[$csc['player_id']];
	sendRaidMessage($_POST['raidname'],
		$_POST['time'],
		$leader,
		htmlspecialchars($_POST['raid_note'],ENT_QUOTES),
		$to,
		array($csc['character_name'],$csc['name']),
		$csclist);

		}
	}
	
	if ($updatepossible) {
		// Update possible raids
		if (count($csclist) > 0) {
			foreach($csclist as $csc) {
				$sql = "UPDATE pqr_csc SET csc_possible=csc_possible+1 
					WHERE csc_id=".$csc['csc_id'];
				runquery($sql,$db);
			}
		}
	}

	header("location: ".WIKIROOT."/doku.php?id=raid");

}

/*********** HELPER FUNCTIONS, NOT AJAX LINKED ****************/

function getRandomLeader(&$csclist,&$db) {
	$loffset = rand(0,(count($csclist)-1));
	list($leader_id) = array_slice($csclist,$loffset,1);
	list($leader) = getCSCById($db,$leader_id);
	
	return $leader;
}

// List raid icons, mark $sel as selected
function getIconList($sel) {
	$retval = '<select name="icon">';

	$d = dir(DOCROOT."/lib/plugins/pqraid/images");
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

/**
 * Load all user data
 *
 * loads the user file into a datastructure
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @hacked	Pieter E Sartain
 */
function _loadUserData(){
  $users = array();

$AUTH_USERFILE = DOCROOT."/conf/users.auth.php";

  if(!@file_exists($AUTH_USERFILE)) return;

  $lines = file($AUTH_USERFILE);
  foreach($lines as $line){
    $line = preg_replace('/#.*$/','',$line); //ignore comments
    $line = trim($line);
    if(empty($line)) continue;

    $row    = split(":",$line,5);
 
 	// Hardwire the removal of any account titled "admin"
	if (urldecode($row[0]) != "admin") {
 
        $users[$row[0]]['name'] = urldecode($row[2]);
        $users[$row[0]]['mail'] = $row[3];
    }
  }
  return $users;
}

function sendRaidMessage($raidname,$time,$leader,$notes,$to,$yourcsc,$yourraid) {
	$subj = '[PQ Raid] '.$raidname.' ('.date('l d/m/Y H:i',$time).')';
	$message = 'Peace and Quiet cordially invite '.$yourcsc[0].' ('.$yourcsc[1].') to '.$raidname.' on '.date('l d/m/Y H:i',$time).'. Your raid leader will be '.$leader['character_name'].'. Please ensure you arrive up on time and prepared; '.$leader['character_name'].' will thank you for it!'."<br>\r\n<br>\r\n";
	$message .= 'If you are the raid leader, invitations should be sent at a convenient time and the first pull should be close to the start of the raid time to get as much out of the session as possible.'."<br>\r\n<br>\r\n";
	$message .= 'The raid notes:'."<br>\r\n<br>\r\n".$notes."<br>\r\n<br>\r\n";
	$message .= 'Enjoy yourselves and remember, if you\'re not having fun, you\'re not doing it right!'."<br>\r\n<br>\r\n";
	$message .= ' - Peace and Quiet.'."<br>\r\n<br>\r\n";
	$message .= 'Your fellow raiders will be:'."<br>\r\n";

	foreach($yourraid as $csc) {
		$message .= $csc['character_name'].' ('.$csc['name'].')'."<br>\r\n";
	}

	$message = wordwrap($message,70,"<br>\r\n",true);

	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'From: Peace and Quiet <nobody@example.com>' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
//	$headers .= 'Bcc: '.$users[$csc['player_id']]."\r\n";

//	echo $message;

	mail($to,$subj,$message,$headers);

//	return array($subj,$message);
}

// Stolen from http://www.php.net/manual/en/function.sort.php#76547
// Called with: msort($array,$some_key_val,$reverse)
function msort($array, $id="id", $sort_ascending=true) {
        $temp_array = array();
        while(count($array)>0) {
            $lowest_id = 0;
            $index=0;
            foreach ($array as $item) {
                if (isset($item[$id])) {
                                    if ($array[$lowest_id][$id]) {
                    if ($item[$id]<$array[$lowest_id][$id]) {
                        $lowest_id = $index;
                    }
                    }
                                }
                $index++;
            }
            $temp_array[] = $array[$lowest_id];
            $array = array_merge(array_slice($array, 0,$lowest_id), array_slice($array, $lowest_id+1));
        }
                if ($sort_ascending) {
            return $temp_array;
                } else {
                    return array_reverse($temp_array);
                }
    }

?>