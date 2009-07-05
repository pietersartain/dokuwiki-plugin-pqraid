<?php

$testList = array (0 => 'Frank', 1 => 'Bob', 2=> 'Ann', 3 => 'Jane', 4 => 'Steve');

$results = array();

recurse($testList, 3, "", $results);

print_r($results);

foreach ($results as $array)
{
  echo "[";  
  foreach ($array as $text)
  {
      echo $text . ",";
  }
  echo "]";
}

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

?>
