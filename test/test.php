<?php

function getCombinations($obj,$csize,$pos,&$str) {
	foreach($obj as $item) {
		if ($pos < $csize){
		//echo $item;
		$str.=$item;
		getCombinations($obj,$csize,$pos+1,$str);
		//echo "<br>";
		//$str.='|';
		} else {
		echo $str."<br>";
		$str='';
		}
	}
	return $str;
}

// Things we know:

// The objects
$obj = array('a','b','c','d');

// The size of the combinations:
$csize = 2;

// The number of combinations:  4! / 2!(4-2)! = 6

// An array of combinations:
/*
	$combs = array (
		[0] => array(a,b),
		[1] => array(a,c),
		[2] => array(a,d),
		[3] => array(b,c),
		[4] => array(b,d),
		[5] => array(c,d)
		);
*/

$combs = array();

print_r($obj);
echo "<br>";

$str = '';

$clist = getCombinations($obj,$csize,0,$str);

print_r($clist);

?>
