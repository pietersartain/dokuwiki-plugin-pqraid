<?php

/*
	authFunc.php - authentication functions
	
	author:	P E Sartain	
	date:	20/10/2008
*/

	function authPlayer($player){
	
		if ($player == 'piete') {
			return array(1,$player);
		} else {
			return array(0,'');
		}
	}
	
?>
