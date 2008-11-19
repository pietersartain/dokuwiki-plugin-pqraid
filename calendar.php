<?php

/* calendar.php - calendar display functions

	author:	P E Sartain	
	date:	20/10/2008
*/

include_once "timeFunc.php";
include_once "authFunc.php";
include_once "calendarFunc.php";

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

function getCalendar($week,$permission,&$db) {
	global $INFO;
	
// Parse the authentication function passed to $permission.
    
	if($permission == 255){
		$isadmin = true;	
	} else {
		$isadmin = false;	
	}
	
	if($permission > 2){
		$islogged = true;
	} else {
		$islogged = false;
	}

	$username = $INFO['client'];

// Get week information:
$week_info = getWeekInfo($week-1,$week+3,$db);

$fqn[0] = add_querystring_var(THISPAGE,"week","0");
$fqn[1] = add_querystring_var(THISPAGE,"week",($week-1));
$fqn[2] = add_querystring_var(THISPAGE,"week",($week+1));
$fqn[3] = add_querystring_var(THISPAGE,"week","52");

// How many players do we have with at least one valid (name + role) CSC
$totalplayers = getCSCNumber($db);
// Return an array of unavailability information for the logged in player.
// This will determine what checkboxes are unticked for their display.
$unavailable = getUnavailable($db,$week-1,$week+3);

	// Calendar header
	$calendar='
		
		<form id="calendarform" method="POST" 
		action="lib/plugins/praid/calendarInterface.php?func=saveUnavailable()">
		<input type="hidden" name="uname" value="'.$username.'"></input>

		<table id="calendar">
			<tr class="weekscroller">
				<td><a href="'.$fqn[0].'">|&lt;</a></td>
				<td><a href="'.$fqn[1].'">&lt;&lt;</a></td>
				<td colspan="3">Weeks</td>
				<td><a href="'.$fqn[2].'">&gt;&gt;</a></td>
				<td><a href="'.$fqn[3].'">&gt;|</a></td>
			</tr>
			<tr>
				<th>Mo</th>
				<th>Tu</th>
				<th>We</th>
				<th>Th</th>
				<th>Fr</th>
				<th>Sa</th>
				<th>Su</th>
			';
// Debug info
//	echo "Const: ".$week." : ".getToday()." : ".getRaidEpoch()."<br>";

	for ($days=-7;$days<21;$days++) {

		// The loopday is all relative to the raiding epoch
		//	+$days		-- iterates through 28 cells only
		// +($week*7)	-- allows viewing to cycle by week, not day
		// -3			-- a constant to align the epoch to a Monday
		$loopday = mktime(0, 0, 0, gmdate("m",getRaidEpoch()), gmdate("d",getRaidEpoch())+$days+($week*7)-3, gmdate("Y",getRaidEpoch()));


// More debug info
/*		$dbg = $loopday." | ".date("w",$loopday)." | ".($days+($week*7)-3)." : ".dateToWeek($loopday)."<br />";
		if ($loopday == getToday()) {
			echo "<b>".$dbg."</b>";
		} else {
			echo $dbg;
		}
*/

		if (($days % 7) == 0) {
			
			$calendar.='</tr>';
			
			$week_num = dateToWeek($loopday);

			// And yet more debug
			/*
			$dbg = $week_num." : ".date("m:d:Y",weekToDate($week_num))." : ".dateToWeek(weekToDate($week_num))."<br>";
			echo $dbg; */
			
			// Week information
			if ($isadmin){
				$calendar.='
					<tr>
						<td colspan="7">
							<div 
								id="weekinfo'.($days+7).'" 
								class="weekinfo" 
								onmouseover="mover(this,\'over\');" 
								onmouseout="mover(this,\'weekinfo\');"
								>
						<div onclick="makeEditBox(\''.$week_num.'\', \''.$week_info[$week_num].'\',\'weekinfo'.($days+7).'\')">
							Week '.$week_num.': '.$week_info[$week_num].'
						</div>
							</div>
						</td>
					</tr>';

			} else {
				$calendar.='
					<tr>
						<td colspan="7">
							<div
								id="weekinfo'.($days+7).'"
								class="weekinfo"
							>
								Week '.$week_num.': '.$week_info[$week_num].'
							</div>
						</td>
					</tr>';
			}
			
			$calendar.='<tr>';
	}
		// Calendar days/cells
		
		// Cell header
		$calendar.='<td class="cell';
		if ($loopday == getToday()) {
			$calendar.=' today';
		}
		$calendar.='">';
		
		// Cell content divs:
		
		// date
		$calendar.='<div class="datecell">'.date('M j',$loopday).'</div>';

		if ($isadmin) {
			// admin
			$calendar.='
			<div class="admincell">
				
			</div>';
		}
		
		if ($islogged) {
			// User
			
			if (isset($unavailable[$loopday])) {
				$checked = '';
			} else {
				$checked = 'checked';
			}

			$calendar.='
			<div class="availcell">
				<input 
				type="checkbox" 
				id="'.$loopday.'" 
				name="'.$loopday.'" 
				class="calendarcheck" 
				'.$checked.' 
				onchange="updateUnavail(this.form)"></input> '.
				($totalplayers-$unavailable).'/'.$totalplayers.'
			</div>';
		}

		// raids
		$calendar.='<div class="raidcell" onclick="boxit(\'in\')">Raids</div>';


		// Cell footer
		$calendar.='</td>';
		
		if ($days == 21) {
			$calendar.='</tr>';
		}

	}
	
	// Calendar footer
	$calendar.='</tr></table></form>
	<br />
	<div id="saveinfo" class="saved">Saved.</div>
	';
	
	// Lightbox 
	$calendar.='
	<div id="fade" class="black_overlay">&nbsp;</div>
	<div id="light" class="white_content">Loading ...</div>';
	 
	return $calendar;
};

?>
