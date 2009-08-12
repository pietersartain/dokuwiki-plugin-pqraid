<?php

/*
	defines.php - a collection of defines for locations
	
	author:	P E Sartain	
	date:	07/11/2008
*/

/*
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
*/
//define("THISPAGE","http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);

/********* localhost *
define("WIKIPATH","/webspace/pq/wiki");
define("DOCROOT","/home/pesartain".WIKIPATH);

/********* PQDEV *
define("WIKIPATH","/pqdev");
define("DOCROOT",$_SERVER['DOCUMENT_ROOT'].WIKIPATH);



*/
$addr = explode(".",$_SERVER['SERVER_NAME']);

/********* PQ */
if ($addr[1] == 'pesartain') {
	define("WIKIPATH","");
	define("DOCROOT",$_SERVER['DOCUMENT_ROOT'].WIKIPATH);
} else {
	define("WIKIPATH","/webspace/dokufarm");
	define("DOCROOT","/home/pesartain".WIKIPATH);
}

/********* Common */

//define("WIKIROOT","http://".$_SERVER['SERVER_NAME'].WIKIPATH);
define("WIKIROOT","http://".$_SERVER['HTTP_HOST'].WIKIPATH);
define("PQIMG",WIKIROOT."/lib/plugins/pqraid/images");
define("PQDIR",WIKIROOT."/lib/plugins/pqraid");


?>
