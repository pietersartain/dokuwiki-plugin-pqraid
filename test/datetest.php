<?php

include "timeFunc.php";

function test($tz) {
	if ($tz != null) putenv("TZ=".$tz);
	echo "Running with timezone: $tz<br>";
	echo "Time now: ".date("m/d/Y H:i")."<br>";
	
	echo "gmdate: ".gmdate("m/d/Y H:i")."<br>";
	
	// mktime and gmmktime should be MONTH,DAY,YEAR not d,m,y
	$deftime = mktime(date("H"),date("i"),date("s"),date("m"),date("j"),date("Y"));
	$gmtime = gmmktime(date("H"),date("i"),date("s"),date("m"),date("j"),date("Y"));
	$gmtime1 = mktime(gmdate("H"),gmdate("i"),gmdate("s"),gmdate("m"),date("j"),gmdate("Y"));
	echo "<pre>";
	echo "mktime:       ".$deftime."<br>";
	echo "gmmktime:     ".$gmtime."<br>";
	echo "mktime gmdate:".$gmtime1."<br>";
	echo "getrepoch:    ".getRaidEpoch()."<br>";
	echo "</pre>";
	
	echo "<br>";
}

//putenv("TZ=America/Los_Angeles");
//phpinfo();

test("America/Los_Angeles");
test("GMT");
test("Europe/London");



?>
