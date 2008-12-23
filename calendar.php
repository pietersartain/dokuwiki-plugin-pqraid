<?php

/* calendar.php - calendar display functions

	author:	P E Sartain	
	date:	20/10/2008
*/

include_once "timeFunc.php";
//include_once "authFunc.php"; // deprecated.
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
$unavailable = getUnavailable($db,$week-1,$week+3,$username);

// Start and end dates, used for ajax display updates:
$firstday = mktime(0, 0, 0, gmdate("m",getRaidEpoch()), gmdate("d",getRaidEpoch())-7+($week*7)-3, gmdate("Y",getRaidEpoch()));

$lastday = mktime(0, 0, 0, gmdate("m",getRaidEpoch()), gmdate("d",getRaidEpoch())+21+($week*7)-3, gmdate("Y",getRaidEpoch()));

	// Calendar header
	$calendar='
		
		<form id="calendarform" method="POST">
		<input type="hidden" name="uname" value="'.$username.'"></input>
		<input type="hidden" name="start" value="'.($week-1).'"></input>
		<input type="hidden" name="end" value="'.($week+3).'"></input>

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


/*	for ($days=-7;$days<21;$days++) {
		$loopday = mktime(0, 0, 0, gmdate("m",getRaidEpoch()), gmdate("d",getRaidEpoch())+$days+(6*7)-3, gmdate("Y",getRaidEpoch()));
		echo $loopday."::".date("d/m/Y",$loopday)."<br>";
	}
*/
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
			$calendar.='<div class="admincell">';
			if ($loopday >= getToday()) {
				$calendar.='<a href="#" onclick="showMakeRaid(\''.$loopday.'\')">[+]</a>';
			} else {
				$calendar.='[+]';
			}			
			$calendar.='</div>';
		}

		if ($islogged) {
			// User

			if (isset($unavailable[$loopday])) {
				$checked = '';
			} else {
				$checked = 'checked';
			}
			
			// How many people are unavailable?
			$unavailable_num = count(getDailyUnavail($db,$loopday));
			
			// How many are available?
			$available = $totalplayers - $unavailable_num;
			
			// Now hard code some limits. The display should show at most
			// 10 people, regardless of their available/unavailable status
			// $av and $unav are % values for the size of a div
			
			$display_size = 10;
			
			if ($available > $display_size) {
				$av = 100;
				$unav = 0;
			} else {
				$av = $available/$display_size*100;
				
				if ($totalplayers <= 10) {
					$unav = $unavailable_num*10;
				} else {
					$unav = 100-$av;
				}
			}
			
//			echo "$av : $available // $unavailable_num : $unav<br>";
			
			$calendar.='
			<div class="availcell">

				<div class="totalplayers">
					<div class="availplayers" style="height: '.$av.'%;">
					&nbsp;</div>
					<div class="unavailplayers" style="height: '.$unav.'%;">
					&nbsp;</div>
				</div>

				<input 
				type="checkbox" 
				id="'.$loopday.'" 
				name="'.$loopday.'" 
				class="calendarcheck" 
				'.$checked.' 
				onchange="updateUnavail(this.form);"';
				
				if ($loopday < getToday()) {
					$calendar.='disabled';
				}
				
				$calendar.='></input>
			</div>
			';
		}

		// raids
		$raid_info = getRaids($db,$loopday);
		if (count($raid_info) > 0) {
		
			$calendar.='<div class="raidcell">';

			foreach($raid_info as $raid) {
			
				$calendar.='<div class="tooltip" id="tip'.$raid['raid_id'].'">
				'.$raid['name'].'<br>
				'.$raid['info'].'<br>
				</div>';
			
				$calendar.='
				<div class="img"><img 
					src="lib/plugins/pqraid/images/'.$raid['icon'].'" 
					onmouseover="showtip(\'tip'.$raid['raid_id'].'\',5,5);" 
					onmouseout="hidetip(\'tip'.$raid['raid_id'].'\');"
					onclick="showRaid(\''.$raid['raid_id'].'\')" 
					alt="'.$raid['name'].'" 
					title="'.$raid['name'].'" 
				></img>
				</div>';
			}

			$calendar.='</div>';
			
		}

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
