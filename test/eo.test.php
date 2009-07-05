<?php
include_once "../calendarInterface.php";

if (!defined("_PRINT_SQL"))	define("_PRINT_SQL",true);
if (!defined("_PRINT_DEBUG"))	define("_PRINT_DEBUG",true);
if (!defined("_RUN_SQL"))	define("_RUN_SQL",true);
if (!defined("_TRIALS"))		define("_TRIALS",50);

function resetDB(&$db) {
	// Reset the db
	$sql = "DELETE FROM `pqr_ranks`";
	runquery($sql,$db);
	$sql = "INSERT INTO `pqr_ranks` (`id`, `player_id`, `rank_id`, `count`, `total`, `last`) VALUES
		(1, 'eritha', 1, 19, 45, 10),
		(2, 'leithania', 2, 8, 45, 0),
		(3, 'apneoa', 2, 22, 45, 8),
		(4, 'kavaan', 2, 16, 45, 2),
		(5, 'py', 2, 14, 45, 0),
		(6, 'morahn', 1, 16, 45, 6),
		(7, 'helene', 2, 20, 45, 4),
		(8, 'salimere', 2, 10, 45, 0),
		(9, 'loralie', 2, 21, 45, 5),
		(10, 'rashir', 2, 6, 45, 1),
		(11, 'lyrea', 2, 17, 45, 2),
		(12, 'kilvanis', 1, 17, 45, 5),
		(13, 'malign', 2, 3, 45, 0),
		(14, 'thol', 2, 0, 45, 0),
		(15, 'allyenfa', 2, 11, 45, 0),
		(16, 'fonkin', 2, 5, 45, 0),
		(17, 'boshie', 2, 1, 45, 0),
		(18, 'thaalig', 2, 8, 45, 0);";
	runquery($sql,$db);
}
function main() {
	
	$db = getDb();

	resetDB($db);

	$csclist = getCSCListWhereAccess($db,null); // Get all cscs


	$raidinvites[] = $csclist[1];	// Eritha	(dps)
	$raidinvites[] = $csclist[9];	// Resiva	(healer)
	$raidinvites[] = $csclist[10];	// Kavaan	(dps)
	$raidinvites[] = $csclist[13];	// Morahn	(tank)
	$raidinvites[] = $csclist[16];	// Tarathel	(dps)
	$raidinvites[] = $csclist[20];	// Feya		(dps)
	$raidinvites[] = $csclist[22];	// Pyronic	(tank)
//	$raidinvites[] = $csclist[25];	// Lyrea	(dps)

	$raidinvites[] = $csclist[28];	// Salimere	(dps)
	$raidinvites[] = $csclist[55];	// Boshie (dps)

	$raidinvites[] = $csclist[31];	// Loralie	(healer)

	// Get *all* the CSCs in the raid invite ...
/*
	foreach ($csclist as $csc) {
		$raidinvites[] = $csc;
	}
*/

	$i=0;
	foreach($raidinvites as $csc) {
		//print_r($csc);
		echo ($i++).': '.$csc['character_name'].'['.$csc['name'].']';
		echo "<br>";
	}
	echo "<br>";


	if (!_PRINT_DEBUG) echo "<table width=50%><tr><th>Raid</th><th>LR</th><th>EO</th></tr>";

	// trial run
	for ($x=0; $x < _TRIALS ; $x++) {
	
		if (!_PRINT_DEBUG) {
			echo "<tr><td>$x</td>";
		} else {
			echo "<h1>RAID $x</h1>";
		}
	
		$sql = "UPDATE pqr_ranks SET total=total+1";
		
		if (_RUN_SQL) runquery($sql,$db);
		if (_PRINT_SQL) {
			echo $sql."<br>";
			echo '<hr>';
		}

		list($lr,$eo) = getRandomLeader($raidinvites,$db);
		
		if (!_PRINT_DEBUG) {
			echo "<td>$lr</td><td>$eo</td></tr>";
		} else {
			echo "LR: ".$lr." // EO: ".$eo."<br>";
		}
	}

	if (!_PRINT_DEBUG) echo "</table>";
	
}

main();

?>
