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

# GET MICROREACTOR TEMPERATURE
$query = "SELECT unix_timestamp(time), temperature FROM temperature_microreactor order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$microreactor_temperature_time = $row[0];
$microreactor_temperature = $row[1];

# GET MICROREACTORNG TEMPERATURE
$query = "SELECT unix_timestamp(time), temperature FROM temperature_microreactorNG order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$microreactorNG_temperature_time = $row[0];
$microreactorNG_temperature = $row[1];

# GET MICROREACTORNG CONTAINMENT PRESSURE
$query = "SELECT unix_timestamp(time), pressure FROM pressure_microreactorNG_pirani_samplecontainer order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$microreactorNG_pressure_time = $row[0];
$microreactorNG_pressure = $row[1];

# GET MICROREACTORNG MASSSPECTRUM ID
$query = "SELECT id FROM measurements_microreactorNG where type = 4 order by time desc limit 1";
$microreactorNG_id = single_sql_value($db,$query,0);

# GET MICROREACTOR MASSSPECTRUM ID
$query = "SELECT id FROM measurements_microreactor where type = 4 order by time desc limit 1";
$microreactor_id = single_sql_value($db,$query,0);
?>



<html>
<head>
<title>CINF data logging</title>
<style title="css" type="text/css">@import "../style.css";</style>
</head>
<body>


<h1 id="commonstatus">Status of CINF microreactors</h1>

<img src="../cinf_logo_web.png" id="logo">

<h2 id="commonstatustitle">Microreactor</h2>
<table cellpadding="5">
 <tr align="center">
  <td>
   <img src="plot.php?type=microreactor_overview&small_plot=1">
  </td>
  <td>
   <img src="plot.php?type=microreactor_pressure_overview&small_plot=1">
  </td>
  <td>
   <img src="https://cinfdata.fysik.dtu.dk/microreactor/plot.php?type=massspectrum&idlist[]=<?php echo($microreactor_id)?>&offsetlist[<?php echo($microreactor_id)?>]=0" width="325px" height="200px">
  </td>
 </tr>
 <tr>
  <td>
   <?php $res = currentValue("microreactor_overview");?>
   <font color="0000FF"><?php echo science_number($res[1])?> mbar recorded @ <?php echo date("D M j H:i Y",$res[0])?></font><br>
   <font color="FF0000"><?php echo round($microreactor_temperature,2)?> C recorded @ <?php echo date("D M j H:i Y",$microreactor_temperature_time)?></font>
  </td>
  <td>
   <?php $res = currentValue("microreactor_pressure_overview");?>
   <font color="0000FF"><?php echo science_number($res[1])?> mbar recorded @ <?php echo date("D M j H:i Y",$res[0])?></font>
  </td>
  <td>
  </td>
 </tr>
 <tr>
  <td>
   <h2 id="commonstatustitle">MicroreactorNG</h1>
  </td>
 </tr>
 <tr>
  <td>
   <img src="plot.php?type=microreactorNG_overview&small_plot=1">
  </td>
  <td>
   <img src="plot.php?type=microreactorNG_pressure_overview&small_plot=1">
  </td>
  <td>
   <img src="https://cinfdata.fysik.dtu.dk/microreactorNG/plot.php?type=massspectrum&idlist[]=<?php echo($microreactorNG_id)?>&offsetlist[<?php echo($microreactorNG_id)?>]=0" width="325px" height="200px">
  </td>
 </tr>
 <tr>
  <td>
   <?php $res = currentValue("microreactorNG_overview");?>
   <font color="0000FF"><?php echo science_number($res[1])?> mbar recorded @ <?php echo date("D M j H:i Y",$res[0])?></font><br>
   <font color="FF0000"><?php echo round($microreactorNG_temperature,2)?> C recorded @ <?php echo date("D M j H:i Y",$microreactorNG_temperature_time)?></font>
  </td>
  <td>
   <?php $res = currentValue("microreactorNG_pressure_overview");?>
   <font color="0000FF"><?php echo science_number($res[1])?> mbar recorded @ <?php echo date("D M j H:i Y",$res[0])?></font><br>
   <font color="FF0000"><?php echo science_number($microreactorNG_pressure)?> mbar recorded @ <?php echo date("D M j H:i Y",$microreactorNG_pressure_time)?></font>
  </td>
  <td>
  </td>
 </tr>
</table>
</body>
</html>
