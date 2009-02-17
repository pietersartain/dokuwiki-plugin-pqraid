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

define("WIKIPATH","/webspace/pq/wiki");

//define("WIKIROOT","http://".$_SERVER['SERVER_NAME'].WIKIPATH);
define("WIKIROOT","http://".$_SERVER['HTTP_HOST'].WIKIPATH);
define("PQIMG",WIKIROOT."/lib/plugins/pqraid/images");
define("PQDIR",WIKIROOT."/lib/plugins/pqraid");

//define("DOCROOT",$_SERVER['DOCUMENT_ROOT'].WIKIPATH);
define("DOCROOT","/home/pesartain".WIKIPATH); //local only

?>
