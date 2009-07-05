<?php

include_once "../connect.php";
include_once "../timeFunc.php";

//date("Y-m-d H:i:s",)


	$db = getDb();

	$sql = "SELECT unavail FROM pqr_unavail WHERE 
		unavail >= '2009-03-29' ORDER BY unavail ASC";	

	$rslt = mysql_query($sql);

	if (!$rslt) die("unavail failure: ".mysql_error($db));

	if (mysql_num_rows($rslt) > 0) {
		while ($row = mysql_fetch_array($rslt)){
			//print_r($row);
			$darray = date_parse($row[0]);
			echo $row[0]." - ".$darray['hour']."<br>";

			if ((integer)$darray['hour'] == (integer)23){
				echo "Yarp.<Br>";
				
				// Delete this record
				runquery("DELETE FROM pqr_unavail WHERE unavail = '".$row[0]."'",$db);
			}
		
		}
	}
	
?>
