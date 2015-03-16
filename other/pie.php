<?php
include "libchart/libchart/classes/libchart.php";

header("Content-type: image/png");
#error_reporting(E_ALL);
#ini_set("display_errors", 1);


function pprint($arr){
  foreach($arr as $point){
    print_r($point);
    print_r('<br>');
  }
}


# Get input from address line
$decimals = isset($_GET['decimals']) ? (int) $_GET['decimals'] : 0;
$data = JSON_decode($_GET['data'], true);
$simplify_level = isset($_GET['element_count']) ? $_GET['element_count'] : 2;

# Calculate sum of all items
$sum = 0;
foreach($data["data"] as $point){
  $sum += $point[1];
}

# Make reduced array that only contains top $simplify_levels and a cumulative
# one for the rest
$reduced_array = Array(Array("[The rest]", 0));
foreach($data["data"] as $key => $point){
  if ((int) $key < $simplify_level){
    $reduced_array[] = $point;
  } else {
    $reduced_array[0][1] += $point[1];
  }
}


# Make the chart
$chart = new PieChart(800, 500);
$dataSet = new XYDataSet();

foreach($reduced_array as $point){
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