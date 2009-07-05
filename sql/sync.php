<?php 
/*
	sync.php - a sql file installer. Requires the
	author: P E Sartain
	date: 28/02/2009
	
*/


$on = 0;

if ($on) die("Currently disabled.");

$uname="pesar2_pqdev";
$upasswd="ollyship02";
$udb="pesar2_pqdev";

$db = mysql_connect("localhost",$uname,$upasswd);

// Converting the datetimes from US (server) to GMT0 (local):
// US-28800=GMT0



?>
