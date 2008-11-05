<?php

/*
	connect.php - set up a connection and provide a variable for use elsewhere. 

	author:	P E Sartain
	date:	20/10/2008

*/

function getDb() {
	$uname="pesar2_pq";
	$upasswd="ollyship02";
	$udb="pesar2_pq";

	$db = mysql_connect("localhost",$uname,$upasswd);

	if (!mysql_select_db($udb,$db)) {
		die("Connection failed: ".mysql_error($db)."<br>");
	}

	return $db;
}

?>
