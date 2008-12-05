<?php

/*
	scheduler.php - containing the main CSC scheduling code & email notifications
	
	author:	P E Sartain
	date:	30/11/2008

*/

include_once "connect.php";
include_once "calendarFunc.php";
include_once "cscfunc.php";

/* Generate all combinations of $values, of length $level and store as $results
	author: Jason Goodworth
*/
function recurse(&$values, $level, $combination, &$results) {
  if ($level > 1) {
    $newValues = $values;
    for ($idx = 0; $idx < $level - 1 && count($newValues) > $idx + 1; $idx++) {
        $temp = $combination; 
        $temp[] = $newValues[$idx];
        unset($newValues[$idx]);
        $newValues = array_values($newValues);
        recurse($newValues, $level - 1, $temp, $results);
    }
    unset($values[0]);
    $values = array_values($values);
    if (count($values) >= $level)
    {
        recurse($values, $level, $combination, $results);
    }
  }
  else
  {
      foreach ($values as $text)
      {
          $combination[count($combination)] = $text;
          $results[] = $combination;
          unset($combination[count($combination) - 1]);
      }
  }
}

/* Schedule a raid
*/
function scheduleCSC($roles,$raid_id,$loopday) {

	$db = getDb();

/*
	1: Build an exclude list of all unavailable order_ids
	2: Add to it all accessless order_ids
	3: Sum role quantities together for a total number of spaces
	
	{{recursion}}
		4:	List the players from the top of the orderlist (minus excludes)
			of size total_spaces as found from {3}.
		5:	List all the available CSCs from these players (minus excludes)
		6:	Run permutationChecker 
			-> True. These players can make a raid between them.
				{{break}}
			-> False. These players are unable to make a raid, therefore:
				a) Switch the bottom-most player with the player below him
				   (from the orderlist, not the playerlist):

	a - j are player IDs, {1-9} are CSC IDs

		t	1   2	3	4	5
		1	a	a	a	a	a{1,2}
		2	b	b	b	b	b{3}
		3	c	c	c	c	c{4,5,6}
		4	d	d	d	d	d{7}
		5	e	f	g	h	i{8,9}
		6	f	e	f	f	f
		7	g	g	e	g	g
		8	h	h	h	e	h
		9	i	i	i	i	e
		10	j	j	j	j	j
				   
				   
				b) 
				{{recurse to 4}}
*/


	// Full CSC order list
	$csclist = getCSCOrderList($db);

	// Step 1: exclude all unavailable signups
	// Step 2: exclude by access token
	// Both done in the same loop for speed up.

	// Unavailable sign ups
	$unavailable = getDailyUnavail($db,$loopday);

	// Get raid access tokens
	$raidaccess = getAchievementsByRaid($raid_id,$db);

	// Get a proper role list
	$role_list = getRoleList($db);

	foreach($role_list as $role) {
//		print_r($role);
//		echo "<br><br>";
		$rlist[$role['role_id']] = array();
	}


/*	foreach($csclist as $csc) {
		print_r($csc);
		echo "<br><br><br>";
	}*/


	// Exclude loop
	foreach($csclist as $csc) {
	
//		echo $csc['character_name']." from ".$csc['player_id'];
	
		if (isset($unavailable[$csc['player_id']])) {
			// Remove from the CSC list if unavailable
//			echo " is unavailable.";
			unset($csclist[$csc['order_id']]);
		} else {
			// Get the access tokens for the CSC
			$access = getCSCAccessTokens($csc['csc_id'],$db);

			$delme = 0;
			if ($raidaccess != null) {
				foreach($raidaccess as $token) {		
					if (!(isset($access[$token['achievement_id']]))) {

					//echo $csc['character_name']." doesn't have A-".$token['achievement_id']."<br><br><br>";
						$delme = 1;
					}
				}
			}

			if ($delme) {
//				echo " is ineligible.";
				unset($csclist[$csc['order_id']]);
			} else {
				// This CSC is available and eligible
			
				if (isset($playerlist[$csc['player_id']])) {
					// If this player is already in the player list, 
					// add this CSC to the array.
					
					$pcnt = count($playerlist[$csc['player_id']]['csc_list']);
					if (
					($playerlist[$csc['player_id']]['csc_list'][$pcnt-1]) != 
					$csc['csc_id']) {
					
	array_push($playerlist[$csc['player_id']]['csc_list'],$csc['csc_id']);

//	++$playerlist[$csc['player_id']]['role_list'][$csc['role_id']];

	array_push($playerlist[$csc['player_id']]['role_list'],$csc['role_id']);

	//$playerlist[$csc['player_id']]['csc_list'] = array_unique($playerlist[$csc['player_id']]['csc_list']);
					}
				
				} else {
					// Else start a new array 
					
					
//					$op = $rlist;
//					++$op[$csc['role_id']];
					
					$playerlist[$csc['player_id']] = array(
					'player_id' => $csc['player_id'],
					'csc_list' => array($csc['csc_id']),
//					'role_list' => $op
					'role_list' => array($csc['role_id'])
					);
				}
			}
		}
		
//		echo "<br>";

	} //foreach
			
	// Step 3: quick total of the number of spaces available
	$totalslots = array_sum($roles);
	$tcount = $totalslots;
	
	
//	print_r($playerlist);
//	echo "<br><Br>";
	
	
	// Step 4: Get the list of players who are at the top of the list
	for ($x=0;$x<$totalslots;$x++){
		$tary = each($playerlist);
		$plist[$tary[0]] = $tary[1];
	}
	
	// Build an array:
	// role_id[1] = {charA,charB,charC}
	// role_id[2] = {charA,charB,charC}
	// ...
	// role_id[N] = {charA,charB,charC}
	foreach($plist as $player){
	
		if (isset($player['role_list'])) {
			foreach($player['role_list'] as $prole){
				array_push($rlist[$prole],$player['player_id']);
			}
		}
	}
	
	// Step 5: A dumb role check: without permutation computation,
	// are there enough players' CSCs to fill each role total?
	foreach($rlist as $key => $role) {
		if (count($role) < $roles[$key]) {
			echo "Not enough ".$role_list[$key]['name']."<br>";
		}
	}
	
	
//	print_r($rlist);
//	echo "<br><Br>";
	
	
	
	// Step 6: Generate the permutation list of each role
	foreach($rlist as $key => $role){

//		print_r($role);
//		echo "<Br>";
		$comb = array();

		recurse($role,$roles[$key],'',$comb);

//		print_r($comb);
//		echo "<Br>";
	}
	
	
	
	
	
/**	
	
	
	
	// Step 4: Get the list of players who are at the top of the list
	foreach($csclist as $csc) {

		// If we've not included this player yet
		if (!(isset($playerlist[$csc['player_id']]))) {
			// If there are still spaces
			if (($tcount--) > 0) {
				$playerlist[$csc['player_id']] = $csc['order_id'];
			}
		}		
	}

	// Step 5: can these players make a raid between them?
	foreach($playerlist as $player => $order_id){
		$playercsc[$player] = getCSCList($player,$db);
	}

	print_r($playercsc);
	echo "<br><br><br>";


	$plist = $csclist;
	foreach($plist as $csc) {
		
		$match=0;
		foreach($playerlist as $player => $order_id){
			if ($csc['player_id'] == $player){				
				$match = 1;
				--$role_counter[$csc['role_id']];
			}
		}
		
		if (!$match) {
		unset($plist[$csc['order_id']]);
		}
	}
	
	foreach($role_counter as $val) {
		print_r($val);
	echo "<br><br><br>";
	}









	// Step : attempt to build a raid out of what's left	
	foreach($csclist as $csc) {

		// If we've not included this player yet
		if (!(isset($finallist[$csc['player_id']]))) {
		
			// And if there are still some spaces left for that role
			if ($roles[$csc['role_id']] > 0) {
				// Decrement the counter
				--$roles[$csc['role_id']];
				// Add this CSC to the list
				$finallist[$csc['player_id']] = $csc;				
			}
		}
	}

	foreach($finallist as $csc) {
		print_r($csc);
		echo "<br><br><br>";
	}


*/
}

$roles = array(
		1 => 1, // Healer
		2 => 3, // DPS
		3 => 1  // Tanks
		);

scheduleCSC($roles,1,'1228176000');
