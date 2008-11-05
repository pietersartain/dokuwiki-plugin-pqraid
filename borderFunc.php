<?php

/*
	borderFunc.php - a function list file, used for getting HTML header/footer
	information.
	
	author:	P E Sartain	
	date:	20/10/2008
	
Changelog:
	20081021 - added strict doctype
*/

include_once "authFunc.php";

	function getHeader(){

//		<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
//		"http://www.w3.org/TR/html4/strict.dtd">

	
		$header='
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
			<head>
				<title>pq raid calendar</title>
				<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1"></meta>
				<link rel="stylesheet" href="style.css" type="text/css" media="screen"></link>
				<script type="text/javascript" src="script.js"></script>
			</head>
			<body>
			<div id="maincontent">
		';
		return $header;
	}
	
	function getFooter(){
		$footer='
			</div>
			</body>
		</html>';
		
		return $footer;
	}

	function getLinkHeader(){

		$playerAuth = authPlayer('piete');
	
		$linkheader='
			<div id="linkheader">
			
			<a href="index.php">Calendar</a>&nbsp;&nbsp;
			<a href="character.php">Character</a>&nbsp;&nbsp;
			<a href=""></a>&nbsp;&nbsp;
			<a href=""></a>&nbsp;&nbsp;
			
			</div>';
	
		return $linkheader;
	}
	
?>
