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
		
		$dinfo = date("w",$date);
		if ($dinfo == 0) {
			$dinfo = 7;
		} else {
			--$dinfo;
		}
		$mon = mktime(0,0,0,gmdate("m",$date),gmdate("d",$date)-$dinfo,gmdate("Y"));
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
