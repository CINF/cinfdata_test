<?php
include "libchart/libchart/classes/libchart.php";

header("Content-type: image/png");


function pprint($arr){
  foreach($arr as $point){
    print_r($point);
    print_r('<br>');
  }
}


# Get input from address line
$decimals = isset($_GET['decimals']) ? (int) $_GET['decimals'] : 0;
$data = JSON_decode($_GET['data'], true);
$simplify_level = isset($_GET['simplify_level']) ? $_GET['simplify_level'] : 2;

# Calculate sum of all items
$sum = 0;
foreach($data["data"] as $point){
  $sum += $point[1];
}

# Make array for group data
$cumulative_data = Array();
for ($i = 0; $i < $simplify_level; $i++){
  if ($i === 0){
    $cumulative_data[$i] = Array("[GROUP] <1%", 0);
  } else {
    $description = "[GROUP] between ${i}% and " . (string) ($i + 1) . "%";
    $cumulative_data[$i] = Array($description, 0);
  }
}

# Filter data between single items and cumulative
$filtered_data = Array();
foreach($data["data"] as $point){
  $percentage = $point[1] * 100.0 / $sum;
  if ($percentage < $simplify_level){
    for ($i = 0; $i < $simplify_level; $i++){
      if ($i < $percentage and $percentage < ($i + 1)){
	$cumulative_data[$i][1] += $point[1];
      }
    }
  } else {
    $filtered_data[] = $point;
  }
}

# Add groups to single points data
foreach($cumulative_data as $point){
  $filtered_data[] = $point;
}

$chart = new PieChart(800, 500);
$dataSet = new XYDataSet();

foreach($filtered_data as $point){
  if ($decimals == 0){
    $num_str = (string) $point[1];
  } else {
    $num_str = number_format($point[1], $decimals);
  }
  $dataSet->addPoint(new Point($point[0] . " (" . $num_str  . ")", $point[1]));  

}

$chart->setDataSet($dataSet);

$chart->setTitle($data["title"]);
$chart->render();
?>