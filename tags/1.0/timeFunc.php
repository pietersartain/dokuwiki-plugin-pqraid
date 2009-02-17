<?php

/*
	timeFunc.php - a function list file for dealing with time
	
	author:	P E Sartain	
	date:	20/10/2008
*/

	// Raid epoch is set to 13/11/2008,
	// the release of Wrath.
	function getRaidEpoch() {
		return mktime(0, 0, 0, 11, 13, 2008);
	}
	
	function diffTime($one,$two) {
		return $one - $two;
	}
	
	function dateToWeek($date) {
		// The week number is relative to the date given compared to the raiding epoch
		
		//$dinfo = getdate($date);
		//$mon = mktime(0,0,0,gmdate("m",$date),gmdate("d",$date)-$dinfo['wday'],gmdate("Y"));
		
		// Get the current day index:
		/*
			0 Sun
			1 Mon
			2 Tues
			3 Weds
			4 Thurs
			5 Fri
			6 Sat
		*/
		$dinfo = date("w",$date);
		
		// Convert it to a 1 - 7 scale
		/*
			1 Mon
			2 Tues
			3 Weds
			4 Thurs
			5 Fri
			6 Sat
			7 Sun
		*/
		if ($dinfo == 0) {
			$dinfo = 7;
		}
		
		// Then decrement it all, to return it to a 0-6 scale
		--$dinfo;

		// Align the raiding epoch with a monday
		$repoch = mktime(0,0,0,date("m",getRaidEpoch()),date("d",getRaidEpoch())-3,date("Y",getRaidEpoch()));
		
		// Get the monday of this week in $date by subtracting the value in 
		// $dinfo from whatever today is
		$mon = mktime(0,0,0,gmdate("m",$date),gmdate("d",$date)-$dinfo,gmdate("Y",$date));
	
	// Debug info
/*		echo $mon."::".$repoch."::".date("m/d/Y",$date)."::";
		// Get the difference between the monday and the epoch
		echo diffTime($mon,$repoch)."::";
		// Count the number of weeks (7-day-blocks) between them
		echo (diffTime($mon,$repoch)/86400/7)."::";
		// Floor the response
		echo floor(diffTime($mon,$repoch)/86400/7);
		echo "<Br>";
*/	
		return floor(diffTime($mon,getRaidEpoch())/86400/7)+1;		
	}
	
	function getToday() {
		//return mktime(0, 0, 0, gmdate("d"), gmdate("m"), gmdate("Y"));
		return mktime(0,0,0,gmdate("m"),gmdate("d"),gmdate("Y"));
	}
	
	function gmnow() {
		return mktime(gmdate("H"),gmdate("i"),gmdate("s"),gmdate("m"),gmdate("d"),gmdate("Y"));
	}
	
	function weekToDate($week) {
	// Returns the Thursday of that week:
	$thurs = ceil((($week-1)*86400*7)+getRaidEpoch());
	return mktime(0,0,0,date("m",$thurs),date("d",$thurs)+4,date("Y",$thurs));
	}
	
?>
