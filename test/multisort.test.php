<?php

include "../calendarInterface.php";
echo "<br><br>";

function test($csclist) {
	foreach($csclist as $csc) {
	
		$rankinfo = getRank($db,$csc['player_id']);
		// You can't use list() with non-numeric array indices.
		$rankinfo = $rankinfo[$csc['player_id']];

		$count = $rankinfo['count'];
		$last = $rankinfo['last'];
		$total = $rankinfo['total'];
		
		if ($count == 0) {
			$order = 0;
		} else {
			$order = ($last/$count);
		}
		$last_inc = 1;

		$eo[] = array($order,$csc['csc_id'],$csc['character_name'],$csc['player_id']);		
	}
	
	$i=0;
	echo "<table>";
	foreach($eo as $c) {
		echo "<tr>
			<td>".($i++)."</td>
			<td>".$c[0]."</td>
			<td>".$c[2]."</td>
			<td>".$c[3]."</td>
			</tr>";
	}

	multi2dSortAsc($eo,0);
	$eo_id = array_slice($eo,0,1);
	$eo_id = $eo_id[0];
	$organiser = $eo_id[2];

	echo "<tr><td colspan=4><hr></tr>";

	foreach($eo as $c) {
		echo "<tr>
			<td>".($i++)."</td>
			<td>".$c[0]."</td>
			<td>".$c[2]."</td>
			<td>".$c[3]."</td>
			</tr>";
	}
	
	echo "</table>";
}

function main() {
	$db = getDb();
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

	test($raidinvites);
}

main();

?>

