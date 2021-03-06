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

/* Dokuwiki includes for the raid notes links.
 * Hardcoded things like this are bad, but writing a whole new app is worse.
 */
global $conf;
$conf['sepchar'] = "_";
$conf['useslash'] = 1;
include_once DOCROOT."/inc/utf8.php";
include_once DOCROOT."/inc/pageutils.php";

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
	case "uisaveWeekEditBox":
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

function saveWeekEditBox() {

//	print_r($_GET);
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

	// Get the players existing information
	$unavail = getUnavailable($db,$_POST['start'],$_POST['end'],$username);

	/***********
		print_r($_POST);
		echo "<br><br>";
		print_r($unavail);
		echo "<br><br>";
	/***********/
	
	// The current week, for sanity we'll use the same var as calendar.
	$week = (int)$_POST['start'] +1;
	
	for ($days=-7;$days<21;$days++) {
		// The loopday is all relative to the raiding epoch
		//	+$days		-- iterates through 28 cells only
		// +($week*7)	-- allows viewing to cycle by week, not day
		// -3			-- a constant to align the epoch to a Monday
		$loopday = mktime(0, 0, 0, date("n",getRaidEpoch()), date("j",getRaidEpoch())+$days+($week*7)-3, date("Y",getRaidEpoch()));
		
		/***********
		$dbg = "1: ".$loopday." | ".date("m/d/Y H:i",$loopday)."<br>";
		echo $dbg;
		/***********/
		
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
/*
				$sql = "INSERT INTO
					pqr_unavail(player_id,unavail) 
					VALUES('".$username."','".date("Y-m-d H:i:s",$loopday)."')";
*/				$sql = "INSERT INTO
					pqr_unavail(player_id,unavail) 
					VALUES('".$username."','".date("Y-m-d 00:00:00",$loopday)."')";
			} else {
/*				$sql = "DELETE FROM pqr_unavail WHERE
					unavail='".date("Y-m-d H:i:s",$loopday)."' AND 
					player_id='".$username."'";
*/				$sql = "DELETE FROM pqr_unavail WHERE
					unavail='".date("Y-m-d 00:00:00",$loopday)."' AND 
					player_id='".$username."'";
			}
			//echo $sql."<br>";
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

		$raid_oclock = strtotime($row['raid_oclock']);

		$str ='		
		<div id="closeX"><a href="#" onclick="boxit()">X</a></div>
		<div id="lefthead"><img src="'.PQIMG.'/'.$row['icon'].'"></img>
		'.$row['name'].' - '.date("d/m/Y H:i",$raid_oclock).'
		</div>';
		echo $str;

		$raidlink = str_replace(array('&','amp:'),'',cleanID($row['name']));

		echo '[[<a href="raid/'.date('Y',$raid_oclock).'/'.date('md',$raid_oclock).'_'.$raidlink.'">Raid notes</a>]]<br /><br />';

		// Get all CSCs
		$csclist = getCSCInfoByPlayerID($db);

		// Unavailable sign ups
		$unavailable = getDailyUnavail($db,$raid_oclock);

		$mraid = "
		<div>
		<table class='playerlist'>
			<tr><th>Player</th><th>CSC 1</th><th>CSC 2</th><th>CSC 3</th><th></th></tr>";

		// Get a user list to convert from user_id to a real name
		$users = _loadUserData();

		$playercount = 0;
		foreach($csclist as $key=>$player) {

			if (isset($unavailable[$key])) {
				$disabled = 'class="disabled"';
			} else {
				$disabled = '';
			}

			$rank = getRank($db,$key);

			$mraid .= "<tr ".$disabled."><td>
				<img src='".PQIMG."/ranks/".$rank[$key]['rank_id'].".gif' style='height: 16px; width: 16px;' />
				".$users[$key]['name']."</td>";

			$rows=0;

			foreach($player as $cscid=>$cscinfo) {

				// Ensure the character has a name
				if ( ($cscinfo['character_name'] != '') && ($cscinfo['character_name'] != null) ) {

					$checked = "";
					$eo = "";
					$rl = "";
					// If a CSC with this name is in the signup list
					if (isset($signups[$cscinfo['character_name']])) {
						// Check if it's the same role in both
						if (($signups[$cscinfo['character_name']]['csc_role']) == $cscinfo['name']) {
							$checked = "checked";

							// Since only one character of the same name can be in the same raid
							// it's guarenteed that if this player is an EO or LR, it'll be with
							// this CSC.
	$eo = ($signups[$cscinfo['character_name']]['static_raid_organiser'] == $cscinfo['character_name']) ? 1 : '';
	$rl = ($signups[$cscinfo['character_name']]['static_lead_raider'] == $cscinfo['character_name']) ? 1 : '';
						}
					}

					$mraid .= "<td style='background: #".$cscinfo['colour'].";'>";
					$mraid .= $access."
						<input type='radio' name='playercsc".$key."' value='".$cscid."' disabled 
							onclick=\"updateRoleCount('".$playercount."','".$cscinfo['role_id']."');\"
							".$checked."
						/>
						<img src='".PQIMG."/classes/16".strtolower($cscinfo['csc_class']).".png' style='' ";
						$mraid .= ' />';
						$mraid .= '<div class="csc_name"


						>'.$cscinfo['character_name']."</div> - 		
						<span>".$cscinfo['csc_percent']."% / ".$cscinfo['csc_totalpercent']."%</span>";

						if ($eo) $mraid .= "<img src='".PQIMG."/ranks/2.gif' style='height: 16px; width: 16px;' />";
						if ($rl) $mraid .= "<img src='".PQIMG."/ranks/1.gif' style='height: 16px; width: 16px;' />";

					$mraid .= "</td>";
					++$rows;
				}
			
			}

			for ($x = $rows; $x < 3; $x++){
				$mraid .= "<td></td>";
			}

			$mraid .= "<td width='20px'>
				<input type='radio' name='playercsc".$key."' value='0' disabled 
					onclick=\"updateRoleCount('".$playercount."','0');\"
				/>
			</td>";

			$mraid .= "</tr>";
			++$playercount;
		}

		$mraid .= "</table></div>";

		echo $mraid;
		
		
		$cscs='';
		if ($signups != null) {
			foreach($signups as $csc) {	
				$cscs.=
					"<div style='background: #".$csc['csc_role_colour']."'>".
					$csc['csc_name']."</div>";
			}
		}
		
		$str = '
		
		<div id="leftfloat">'.$cscs.'</div>
		<div id="rightfloat">
		'.$row['info'].'</div>';
		
		
	
		echo $str;
	
	} else {
		echo "Fudged.";
	}
}

/* Show the create-new-raid window
 */
function showMakeRaid($datestring) {
	$db = getDb();


	$mraid="
	
		<table>
			<tr>
				<td>M</td>
				<td>T</td>
				<td>W</td>
				<td>T</td>
				<td>F</td>
				<td>S</td>
				<td>S</td>
			</tr>
		</table>

	<div id='closeX'><a href='#' onclick='boxit()'>X</a></div>
	<div id='lefthead'>".date('F jS, Y',$datestring)."</div>

	<form id='newraid' method='POST' name='newraid' 
	action='".PQDIR."/calendarInterface.php?func=saveRaid'>";
	
	echo $mraid;

	// Get all CSCs
	$csclist = getCSCInfoByPlayerID($db);

	// Unavailable sign ups
	$unavailable = getDailyUnavail($db,$datestring);

	$mraid = "
	<div>
	<table class='playerlist'>
		<tr><th>Player</th><th>CSC 1</th><th>CSC 2</th><th>CSC 3</th><th></th></tr>";

	// Get a user list to convert from user_id to a real name
	$users = _loadUserData();

	$playercount = 0;
	foreach($csclist as $key=>$player) {

		if (isset($unavailable[$key])) {
			$disabled = 'class="disabled"';
			$disabledbutton = 'disabled';
		} else {
			$disabled = '';
			$disabledbutton = '';
		}
		
		$rank = getRank($db,$key);

		$mraid .= "<tr ".$disabled."><td>
			<img src='".PQIMG."/ranks/".$rank[$key]['rank_id'].".gif' style='height: 16px; width: 16px;' />
			".$users[$key]['name']."</td>";

		$rows=0;
		foreach($player as $cscid=>$cscinfo) {

			// Ensure the character has a name
			if ( ($cscinfo['character_name'] != '') && ($cscinfo['character_name'] != null) ) {
				$mraid .= "<td style='background: #".$cscinfo['colour'].";'>".$access."

					<input type='radio' name='playercsc".$key."' value='".$cscid."' ".$disabledbutton." 
						onclick=\"updateRoleCount('".$playercount."','".$cscinfo['role_id']."');\"
					/>
					<img src='".PQIMG."/classes/16".strtolower($cscinfo['csc_class']).".png' style='' ";
				$mraid .='/>';
				$mraid .= '<div class="csc_name">'.$cscinfo['character_name']."</div> - 	
					<span>".$cscinfo['csc_percent']."% / ".$cscinfo['csc_totalpercent']."%</span>

				</td>";
				++$rows;
			}
		}

		for ($x = $rows; $x < 3; $x++){
			$mraid .= "<td></td>";
		}
		
		$mraid .= "<td width='20px'>
			<input type='radio' name='playercsc".$key."' value='0' ".$disabledbutton." checked 
				onclick=\"updateRoleCount('".$playercount."','0');\"
			/>
		</td>";

		$mraid .= "</tr>";
		++$playercount;
	}

	$mraid .= "</table></div>";

	echo $mraid;

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

	$mraid = "	
	<div id='leftfloat'>
	
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
	</table>
	<input type='submit' value='Create'>
	</div>
	
	</form>";
	
	echo $mraid;
}

/* Generate a CSC list for a given:
 	$loopday
	$raidaccess = which achievements were checked on submit
	$checkedcsc = which CSCs were checked on submit
*/
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
					$csc['character_name']." - ".$csc['csc_percent']."% / ".$csc['csc_totalpercent']."%</div>";
		
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
	
	// Get a CSC list, limit by access for this raid
	$csclist = getCSCListWhereAccess($db,null); // Turned off trimming by access for now

	// Update the total raids, for everyone
	$sql = "UPDATE pqr_ranks SET total=total+1";
	runquery($sql,$db);

	// Trim that down by day
	stripCSCListByAvailability($db,$loopday,$csclist);


	// If "open invite" is **NOT** set
	if (!isset($_POST['sendglobal'])) {
		$updatepossible = 1;


		// For each potential CSC
		foreach($csclist as $csc) {
			// For each CSC ticked
			if ($_POST['playercsc'.$csc['player_id']] == $csc['csc_id']) {
				$raidinvites[] = $csc;

				// Save the invitees to the signup table
				$sql = "INSERT INTO pqr_signups(raid_id, player_id, csc_name, csc_role, csc_role_colour) VALUES('".$row['rid']."','".$csc['player_id']."','".$csc['character_name']."','".$csc['name']."','".$csc['colour']."')";
				runquery($sql,$db);

				// If "closed invitation / no raid time" is **NOT** checked
				if (!isset($_POST['noincrement'])) {
					// Update the raid times
					$sql = "UPDATE pqr_csc SET csc_attended=csc_attended+1 
						WHERE csc_id=".$csc['csc_id'];
					runquery($sql,$db);
				} else {
					$updatepossible = 0;
				}
			}
		}
		
		// Randomise the raid leader
		list($leader,$eo) = getRandomLeader($raidinvites,$db);
		
		// Save the leader & EO to the signup table
		$sql = "UPDATE pqr_signups
			SET static_raid_organiser='".$eo."', 
				static_lead_raider='".$leader."' 
			WHERE raid_id = '".$row['rid']."'";
		runquery($sql,$db);
		
		foreach($raidinvites as $csc){
			// Mail these people.
			$to = $users[$csc['player_id']]['mail'];
		sendRaidMessage($_POST['raidname'],
			$_POST['time'],
			$leader,
			$eo,
			htmlspecialchars($_POST['raid_note'],ENT_QUOTES),
			$to,
			array($csc['character_name'],$csc['name']),
			$raidinvites);
		}
	
	} else {
		$updatepossible = 0;
		
		// Randomise the raid leader
		/*
		foreach($csclist as $csc) {
			$leader_list[] = $csc['csc_id'];
		}
		
		$leader = getRandomLeader($leader_list,$db);
		*/
		$leader['character_name'] = 'whomever put this up';

		// Send everyone a mail
		foreach($csclist as $csc) {
			//echo $key." | ".$user['mail']."<br>";

	$to = $users[$csc['player_id']];
	sendRaidMessage($_POST['raidname'],
		$_POST['time'],
		$leader,
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

function deleteRaid() {
	$db = getDB();
}

/*********** HELPER FUNCTIONS, NOT AJAX LINKED ****************/

/* This is hardcoded to return a leader and an EO.
 * 
 */
function getRandomLeader(&$csclist,&$db) {

if (!defined("_PRINT_SQL"))		define("_PRINT_SQL",true);
if (!defined("_PRINT_DEBUG"))	define("_PRINT_DEBUG",true);
if (!defined("_RUN_SQL"))		define("_RUN_SQL",true);

// This mostly returned Luke
/*
	$loffset = rand(0,(count($csclist)-1));
	list($leader_id) = array_slice($csclist,$loffset,1);
	list($leader) = getCSCById($db,$leader_id);
*/

	if (_PRINT_DEBUG) { 
		$i=0; 
		echo "<h2>CSC List </h2>"; 
		echo '<table>
		<tr>
			<th></th>
			<th>Name</th>
			<th>Role</th>
			<th>% of attended</th>
			<th>% of raids as EO/LR</th>
			<th>% of EO/LR of attended</th>
			<th></th>
		</tr>
		<tr>
			<th></th>
			<th></th>
			<th>Rank</th>
			<th>Count</th>
			<th>Last</th>
			<th>Total</th>
			<th>DIFF</th>
		</tr>';
	}

	foreach($csclist as $csc) {
		$rankinfo = getRank($db,$csc['player_id']);
		// You can't use list() with non-numeric array indices.
		$rankinfo = $rankinfo[$csc['player_id']];

		// We use these variables a lot
		$count = $rankinfo['count'];
		$last = $rankinfo['last'];
		$total = $rankinfo['total'];

		// This is the algorithm to determine the order in which people are chosen for roles
		//$order = ($rankinfo['count'] - $rankinfo['last']);
		$order = ($last/$count);
		$last_inc = 1;
		
		/* 0	% of raids attended
		 * 1	% of raids EO/LR
		 * 2	% of EO/LR of attended
		 * 3	??
		 */
		
		// What does this do?!
		if ($last == $count)
		
		$inf_array = array(
						($count/$total*100),
						($last/$total*100),
						($last/$count*100),
						
					);

		if (_PRINT_DEBUG) {
					
//			echo ($i++).': '.$csc['character_name'].' ['.$csc['name'].']';
			echo "<tr>
					<td>".($i++)."</td>
					<td>".$csc['character_name']."</td>
					<td>".$csc['name']."</td>
					<td>".$inf_array[0]."</td>
					<td>".$inf_array[1]."</td>
					<td>".$inf_array[2]."</td>
					<td>".$inf_array[3]."</td>
				   </tr>";


			echo '<tr>
					<td></td>
					<td></td>
					<td>'.$rankinfo['rank_id'].'</td>
					<td>'.$rankinfo['count'].'</td>
					<td>'.$rankinfo['last'].'</td>
					<td>'.$rankinfo['total'].'</td>
					<td>'.$order.'</td>
				  </tr>';

		}



		if ((int)($rankinfo['rank_id']) == 2) {
			// Event organiser list
			$eo[] = array($order,$csc['csc_id'],$csc['character_name'],$csc['player_id']);
		} else {
			// Lead raider list
			$lr[] = array($order,$csc['csc_id'],$csc['character_name'],$csc['player_id']);
		}

		$sql = "UPDATE pqr_ranks SET count=count+1 
				WHERE player_id='".$csc['player_id']."'";
		
		if (_RUN_SQL) runquery($sql,$db);
		if (_PRINT_SQL) {
			echo $sql."<br>";
			echo '<hr>';
		}
	}

	if (_PRINT_DEBUG) {
		echo "</table>";
		echo "<h2>EO List</h2>";
		$i=0;
		foreach($eo as $csc) {
			echo ($i++).': '.$csc[2].' [Diff: '.$csc[0].']<br>';
		}
		echo "<br>";
	}

	if (_PRINT_DEBUG) {
		echo "<h1>LR List</h1>";
		$i=0;
		foreach($lr as $csc) {
			echo ($i++).': '.$csc[2].' [Diff: '.$csc[0].']<br>';
		}
		echo "<br>";
	}	

	if (isset($eo)) {
		
		multi2dSortAsc($eo,0);
		$eo_id = array_slice($eo,0,1);
		$eo_id = $eo_id[0];
		
		$sql = "UPDATE pqr_ranks SET last=last+".$last_inc." 
				WHERE player_id='".$eo_id[3]."'";
		if (_RUN_SQL) runquery($sql,$db);
		if (_PRINT_SQL) {
			echo $sql."<br>";
			echo '<hr>';
		}

		
		$organiser = $eo_id[2];
		
	} else {
		if (_PRINT_DEBUG) echo "EO: Fallback.";
		$loffset = rand(0,(count($csclist)-1));
		list($eo_id) = array_slice($csclist,$loffset,1);
		list($organiser) = getCSCById($db,$eo_id['csc_id']);
		$organiser = $organiser['character_name'];		
	}

	if (isset($lr)) {
		multi2dSortAsc($lr,0);
		$lr_id = array_slice($lr,0,1);
		$lr_id = $lr_id[0];

		$sql = "UPDATE pqr_ranks SET last=last+".$last_inc." 
				WHERE player_id='".$lr_id[3]."'";
		if (_RUN_SQL) runquery($sql,$db);
		if (_PRINT_SQL) {
			echo $sql."<br>";
			echo '<hr>';
		}

		
		$leader = $lr_id[2];

	} else {
		if (_PRINT_DEBUG) echo "LR: Fallback.";
		$loffset = rand(0,(count($csclist)-1));
		list($lr_id) = array_slice($csclist,$loffset,1);
		list($leader) = getCSCById($db,$lr_id['csc_id']);
		$leader = $leader['character_name'];		
	}

	if (_PRINT_DEBUG) {
		echo "<h2>Sorted EO List</h2>";
		$i=0;
		foreach($eo as $csc) {
			echo ($i++).': '.$csc[2].' [Diff: '.$csc[0].']<br>';
		}
		echo "<br>";
	}

	if (_PRINT_DEBUG) {
		echo "<h2>Sorted LR List</h2>";
		$i=0;
		foreach($lr as $csc) {
			echo ($i++).': '.$csc[2].' [Diff: '.$csc[0].']<br>';
		}
		echo "<br>";
	}

	// These should just be text values.
	return array($leader,$organiser);
}

/* 2d multi sorting */

/* from:
 * http://www.informit.com/articles/article.aspx?p=341245&seqNum=7
 */
function compare($x, $y) {
 if ( $x[0] == $y[0] )
  return 0;
 else if ( $x[0] < $y[0] )
  return -1;
 else
  return 1;
}

function multi2dSortAsc(&$arr, $key){ 
/* from:
 * http://www.prodevtips.com/2008/01/06/sorting-2d-arrays-in-php-anectodes-and-reflections/
 * failed to work on the server for reasons unknown.
 */
/*
  $sort_col = array(); 
  foreach ($arr as $sub) $sort_col[] = $sub[$key]; 
  array_multisort($sort_col, $arr); 
*/
	usort($arr, 'compare');
}

// List raid icons, mark $sel as selected
function getIconList($sel) {
	$retval = '<select name="icon">';

	$d = dir(DOCROOT."/lib/plugins/pqraid/images");
	while (false !== ($entry = $d->read())) {
	
		if (is_dir($entry)) {
			//echo "Yerbugger.";
		} else {

		if ($entry!="." && $entry!=".."){
				$selected = '';
				if ($sel == $entry) $selected='selected="selected"';
				$retval = $retval.'<option value='.$entry.' '.$selected.'>'.$entry.'</option>';
			}
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
		
		$tstamp = mktime(0+$x, 0+$y, 0, date("m",$tday), date("d",$tday), date("Y",$tday));

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

function sendRaidMessage($raidname,$time,$leader,$eo,$notes,$to,$yourcsc,$yourraid) {
	
	$raidlink = str_replace(array('&','amp:'),'',cleanID($raidname));
	
	$rnotes = 'http://pq.pesartain.com/raid/'.date('Y',$time).'/'.date('md',$time).'_'.$raidlink;
	
	$subj = '[PQ Raid] '.$raidname.' ('.date('l d/m/Y H:i',$time).')';
	$message = 'Peace and Quiet cordially invite '.$yourcsc[0].' ('.$yourcsc[1].') to '.$raidname.' on '.date('l d/m/Y H:i',$time).".<br>\r\n<br>\r\n".' 
				Your event organiser is '.$eo.', to whom scheduling related problems should be addressed.'."<br>\r\n";
	$message .= 'Your lead raider is '.$leader.', to whom all other issues should be addressed.'."<br>\r\n<br>\r\n";
	$message .= 'The latest raid notes for this raid are located at:'."<br>\r\n";
	$message .= ''.$rnotes."<br>\r\n";
	$message .= 'These will be updated by the Lead Raider shortly.'."<br>\r\n<br>\r\n";	
	$message .= 'Please remember it is your responsibility to locate your own replacement, to inform the EO that you are doing so and update the raid notes to reflect that.'."<br>\r\n<br>\r\n";
	$message .= 'Good luck in there; enjoy yourselves and remember, if you\'re not having fun, you\'re not doing it right!'."<br>\r\n<br>\r\n";
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
