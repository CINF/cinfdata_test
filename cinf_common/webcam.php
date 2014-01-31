<?php
include("../common_functions.php");
include("graphsettings.php");

date_default_timezone_set('Europe/Copenhagen');

$db = std_db();

$xscale = date_xscale(); // Get default x-scale

function currentValue($type){
    $xscale = date_xscale(); // Get default x-scale
    $db = std_db(); // These two are not inherited from global scope....

    $settings = plot_settings($type,$xscale);
    $query = $settings['query'];
    $result = mysql_query($query . " desc limit 1",$db);
    $row = mysql_fetch_array($result);
    $current_value = $row;
    return($current_value);
}

#Channel 01
$query = "SELECT unix_timestamp(time), (6.25*value - 25) FROM gasmonitor_ch01 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ch01_time = $row[0];
$ch01_value = $row[1];

#Channel 02
$query = "SELECT unix_timestamp(time), (6.25*value - 25) +4.2  FROM gasmonitor_ch02 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ch02_time = $row[0];
$ch02_value = $row[1];

#Channel 03
$query = "SELECT unix_timestamp(time), (6.25*value - 25) -5 FROM gasmonitor_ch03 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ch03_time = $row[0];
$ch03_value = $row[1];

#Channel 04
$query = "SELECT unix_timestamp(time), (6.25*value - 25) -9.5 FROM gasmonitor_ch04 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ch04_time = $row[0];
$ch04_value = $row[1];

#Channel 09
$query = "SELECT unix_timestamp(time), (6.25*value - 25) +6 FROM gasmonitor_ch09 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ch09_time = $row[0];
$ch09_value = $row[1];

#Channel 11
$query = "SELECT unix_timestamp(time), (6.25*value - 25) +5 FROM gasmonitor_ch11 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ch11_time = $row[0];
$ch11_value = $row[1];

#Channel 13
$query = "SELECT unix_timestamp(time), (6.25*value - 25) +3 FROM gasmonitor_ch13 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ch13_time = $row[0];
$ch13_value = $row[1];

#Channel 05
$query = "SELECT unix_timestamp(time), 15.625*(value - 4) +0.5 FROM gasmonitor_ch05 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ch05_time = $row[0];
$ch05_value = $row[1];

#Channel 06
$query = "SELECT unix_timestamp(time), 15.625*(value - 4) +1.6 FROM gasmonitor_ch06 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ch06_time = $row[0];
$ch06_value = $row[1];

#Channel 07
$query = "SELECT unix_timestamp(time), 15.625*(value - 4) +0.8 FROM gasmonitor_ch07 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ch07_time = $row[0];
$ch07_value = $row[1];

#Channel 08
$query = "SELECT unix_timestamp(time), 15.625*(value - 4) +1.1 FROM gasmonitor_ch08 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ch08_time = $row[0];
$ch08_value = $row[1];

#Channel 10
$query = "SELECT unix_timestamp(time), 15.625*(value - 4) +0.8 FROM gasmonitor_ch10 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ch10_time = $row[0];
$ch10_value = $row[1];

#Channel 12
$query = "SELECT unix_timestamp(time), 15.625*(value - 4) -1.1 FROM gasmonitor_ch12 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ch12_time = $row[0];
$ch12_value = $row[1];

?>



<html>
<head>
<title>CINF data logging</title>
<style title="css" type="text/css">@import "../style.css";</style>

<style>
.down {
background: #ff0000;
width: 15px;
height: 15px;
  border-radius: 50%;
}
</style>

<style>
.up {
background: #00ff00;
width: 15px;
height: 15px;
  border-radius: 50%;
  }
</style>

</head>
<body>


<h1 id="commonstatus">Gasmonitor, B312</h1>

<img src="../cinf_logo_web.png" id="logo">

<h2 id="commonstatustitle">CH<sub>4</sub></h2>
<table cellpadding="5">
 <tr align="center">
  <td>
   <img src="plot.php?type=gas_monitor_ch4&small_plot=1&ymin=-1&ymax=20">
  </td>
  <td>
   <img src="plot.php?type=gas_monitor_ch4_2&small_plot=1&ymin=-1&ymax=20">
  </td>
  <td>


<!--
  <table border=1 padding=5>
<?php
  for($rows=0;$rows<8;$rows++){
    echo("<tr>");
    for($i=0;$i<13;$i++){
      echo("<td class=\"down\">&nbsp;</td>");
    }
    echo("</tr>");
  }
?>
  </table>
-->
  &nbsp;
  </td>
 </tr>
 <tr>
  <td>
  <font color="0000FF">01, Pumperum: <?php echo round($ch01_value,1)?> %lel <!-- recorded @ <?php echo date("D M j H:i Y",$ch01_time)?>--></font>  <br>
  <font color="FF0000">02, Hal (vest): <?php echo round($ch02_value,1)?> %lel</font><br>
  <font color="00FF00">03, Hal (&oslash;st): <?php echo round($ch03_value,1)?> %lel</font><br>
  <font color="000000">04, Kemilab: <?php echo round($ch04_value,1)?> %lel</font><br>
  </td>
  <td>
  <font color="0000FF">09, Hall 074: Outer wall, down: <?php echo round($ch09_value,1)?> %lel</font><br>
  <font color="FF0000">11, Hall 074: Outer wall, up: <?php echo round($ch11_value,1)?> %lel</font><br>
  <font color="00FF00">13, Hall 074: Ventilation system: <?php echo round($ch13_value,1)?> %lel</font><br>
  </td>
  <td>
  </td>
 </tr>
 <tr>
  <td>
   <h2 id="commonstatustitle">CO</h1>
  </td>
 </tr>
 <tr>
  <td>
   <img src="plot.php?type=gas_monitor_CO&small_plot=1&ymin=-3&ymax=30">
  </td>
  <td>
   <img src="plot.php?type=gas_monitor_CO_2&small_plot=1&ymin=-3&ymax=30">
  </td>
  <td>
  &nbsp;
  </td>
 </tr>
 <tr>
  <td>
  <font color="0000FF">05, Pumperum: <?php echo round($ch05_value,1)?> ppm</font><br>
  <font color="FF0000">06, Hal (vest): <?php echo round($ch06_value,1)?> ppm</font><br>
  <font color="00FF00">07, Hal (&oslash;st): <?php echo round($ch07_value,1)?> ppm</font><br>
  </td>
  <td>
  <font color="0000FF">08, Kemilab: <?php echo round($ch08_value,1)?> ppm</font><br>
  <font color="FF0000">10, Hall 074: Outer wall, down: <?php echo round($ch10_value,1)?> ppm</font><br>
  <font color="00FF00">12, Hall 074: Inner wall down:<?php echo round($ch12_value,1)?> ppm</font><br>

  </td>
 </tr>
</table>
</body>
</html>
