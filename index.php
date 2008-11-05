<?php

/*
	index.php - default directory page
	
	author:	P E Sartain
	date:	20/10/2008
*/

	include_once "connect.php";
	include_once "timeFunc.php";

	include "borderFunc.php";
	include "calendar.php";
	
	if (isset($_GET['week'])) {
		// Set the raid week to the desired, or ...
		$week = $_GET['week'];
	} else {
		// Set it to the current week, relative to the raiding epoch
		$week = dateToWeek(time());
	}
	
	echo getHeader();
	echo getLinkHeader();
	echo getCalendar($week,getDb());
	echo getFooter();

?>
