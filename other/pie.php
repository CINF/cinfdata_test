<?php
include "libchart/libchart/classes/libchart.php";

header("Content-type: image/png");


$decimals = isset($_GET['decimals']) ? (int) $_GET['decimals'] : 0;
$data = JSON_decode($_GET['data'], true);
#$type = JSON_decode($_GET['type'], true);

$chart = new PieChart(800, 500);
$dataSet = new XYDataSet();

foreach($data["data"] as $point){
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