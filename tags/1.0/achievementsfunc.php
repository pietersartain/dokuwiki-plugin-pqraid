<?php

/* achievementsfunc.php

	author:	P E Sartain
	date:	21/11/2008
*/

/* Get all access tokens (achievement list)
 */
function getAchievementList(&$db) {
	$rslt = mysql_query('SELECT * FROM pqr_achievements');
	if (!$rslt) die('access token error: '.mysql_error($db));
	
	$count = 0;
	while ($row = mysql_fetch_array($rslt)){
		$accesslist[$count] = $row;
		$count++;
	}
	
	return $accesslist;
}

?>
