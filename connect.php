<?php

/*
	connect.php - set up a connection and provide a variable for use elsewhere. 

	author:	P E Sartain
	date:	20/10/2008

*/

function getDb() {

/******* PQDEV
	$uname="pesar2_pqdev";
	$udb="pesar2_pqdev";

/******* PQ & localhost*/
	$uname="pesar2_pq";
	$udb="pesar2_pq";

	$upasswd="ollyship02";


	$db = mysql_connect("localhost",$uname,$upasswd);

	if (!mysql_select_db($udb,$db)) {
		die("Connection failed: ".mysql_error($db)."<br>");
	}

	return $db;
}

function runquery($sql,&$db) {
	$rslt = mysql_query($sql);
	if (!$rslt){
		die("<br /><br />Error: '".mysql_error($db)."' from sql: ".htmlspecialchars($sql));
	}
	return $rslt;
}

?>
