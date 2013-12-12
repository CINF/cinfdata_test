<?php
/**
 * linear regression function
 * @param $x array x-coords
 * @param $y array y-coords
 * @returns array() m=>slope, b=>intercept
 */
function linear_regression($x, $y) {

  // calculate number points
  $n = count($x);

  // ensure both arrays of points are the same size
  if ($n != count($y)) {
    trigger_error("linear_regression(): Number of elements in coordinate arrays do not match.", E_USER_ERROR);
  }

  // calculate sums
  $x_sum = array_sum($x);
  $y_sum = array_sum($y);

  $xx_sum = 0;
  $xy_sum = 0;

  for($i = 0; $i < $n; $i++) {
    $xy_sum+=($x[$i]*$y[$i]);
    $xx_sum+=($x[$i]*$x[$i]);
  }

  // calculate slope
  $m = (($n * $xy_sum) - ($x_sum * $y_sum)) / (($n * $xx_sum) - ($x_sum * $x_sum));
  // calculate intercept
  $b = ($y_sum - ($m * $x_sum)) / $n;
  // return result
  return array("m"=>$m, "b"=>$b);
}

$db = mysql_connect("localhost", "cinf_reader", "cinf_reader");  
mysql_select_db("cinfdata",$db);
include("../common_functions_v2.php");

# Get the time that the last bakeout was started
$query = "SELECT time FROM bakeout_312 ORDER BY time DESC LIMIT 1";
$result  = mysql_query($query, $db);
$row = mysql_fetch_array($result);
$start_time = date('Y-m-d+H:i', strtotime($row[0]));
$now = date('Y-m-d+H:i');

# Get the latest temperature point
$query = "SELECT time, temperature FROM temperature_stm312 ORDER BY TIME DESC LIMIT 1";
$result  = mysql_query($query,$db);
$temperature = mysql_fetch_array($result);
$temperature['temperature'] = round($temperature['temperature'], 2);
# Format the time of last point as the "to" part of the url but add 1 minut,
# to allow of re-use of figures
$temperature['time_url'] = date('Y-m-d+H:i', strtotime($temperature['time']) + 60);

# Get latest pressure from the main chamber
$query = "SELECT time, pressure FROM pressure_stm312 ORDER BY TIME DESC LIMIT 1";
$result  = mysql_query($query,$db);  
$pressure = mysql_fetch_array($result);

# Get latest pressure from the prep chamber (called elbow!)
$query = "SELECT time, pressure FROM pressure_stm312_elbow ORDER BY TIME DESC LIMIT 1";
$result  = mysql_query($query,$db);  
$pressure_elbow = mysql_fetch_array($result);

# Form the "to" part for the pressure graph urls, as the lates pressure point +60s
if (strtotime($pressure['time']) > strtotime($pressure_elbow['time'])){
  $pressure_url_to = date('Y-m-d+H:i', strtotime($pressure['time']) + 60);
} else {
  $pressure_url_to = date('Y-m-d+H:i', strtotime($pressure_elbow['time']) + 60);
}

$query = "SELECT UNIX_TIMESTAMP(time), temperature FROM temperature_stm312 order by time desc limit 5";
$result  = mysql_query($query,$db);
while ($row = mysql_fetch_array($result)){
        $ydata[] = $row[1];
        $xdata[] = $row[0];
}

$regression_results = linear_regression($xdata, $ydata);
$temperature_slope = $regression_results['m']*60;
$temperature_slope = round($temperature_slope, 2);

echo(html_header("../", "Bakeout status"));
echo("
<!-- Last values -->
<img src=\"oven.jpg\" style=\"float:right\">
<img src=\"setup.jpg\" style=\"float:right\">
<h2>Current pressure: {$pressure['pressure']} torr, at {$pressure['time']}</h2>
<h2>Current pressure prep: {$pressure_elbow['pressure']} torr, at {$pressure_elbow['time']}</h2>
<h2>Current temperature: {$temperature['temperature']}&deg;C, at {$temperature['time']}</h2>
<h2>Temperature slope: $temperature_slope&deg;C/min, last 5 points</h2>

<!-- Small plots -->
<img style=\"width:430px\" src=\"plot.php?type=multidateplot&from={$start_time}&to={$pressure_url_to}&left_logscale=checked&matplotlib=checked&left_plotlist[]=1&left_plotlist[]=2&image_format=png\">
<img style=\"width:430px\" src=\"plot.php?type=multidateplot&from={$start_time}&to={$temperature['time_url']}&matplotlib=checked&left_plotlist[]=3&image_format=png&left_ymin=0&left_ymax=200\"><br>

<!-- Full size plots -->
<h1>Full size graphs</h1>
<img src=\"plot.php?type=multidateplot&from={$start_time}&to={$pressure_url_to}&left_logscale=checked&matplotlib=checked&left_plotlist[]=1&left_plotlist[]=2&image_format=png\">
<img src=\"plot.php?type=multidateplot&from={$start_time}&to={$temperature['time_url']}&matplotlib=checked&left_plotlist[]=3&image_format=png&left_ymin=0&left_ymax=200\"><br>
");
echo(html_footer());
?>