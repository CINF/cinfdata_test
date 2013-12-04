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

# GET OMICRON PREP TEMPERATURE
$query = "SELECT unix_timestamp(time), temperature FROM temperature_omicron order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$omicron_temperature_time = $row[0];
$omicron_temperature = $row[1];

# GET STM312 TEMPERATURE
$query = "SELECT unix_timestamp(time), temperature FROM temperature_stm312 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$stm312_temperature_time = $row[0];
$stm312_temperature = round($row[1], 2);
if ( $stm312_temperature < -273.15 ){
  $stm312_temperature = 'N/A';
}

# GET OMICRON NANOBEAM PRESSURE
$query = "SELECT unix_timestamp(time), pressure FROM pressure_omicron_nanobeam order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$omicron_nanobeam_pressure_time = $row[0];
$omicron_nanobeam_pressure = $row[1];

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


<h1 id="commonstatus">Status of Omicron and STM312</h1>

<img src="../cinf_logo_web.png" id="logo">

<h2 id="commonstatustitle">Omicron</h2>
<table cellpadding="5">
 <tr align="center">
  <td>
   <img src="plot.php?type=omicron_ana_overview&small_plot=1">
  </td>
  <td>
   <img src="plot.php?type=omicron_prep_overview&small_plot=1">
  </td>
  <td>
  </td>
 </tr>
 <tr>
  <td>
   <?php $res = currentValue("omicron_ana_overview");?>
   <font color="0000FF"><?php echo science_number($res[1])?> mbar recorded @ <?php echo date("D M j H:i Y",$res[0])?></font><br>
   <font color="FF0000"><?php echo science_number($omicron_nanobeam_pressure)?> mbar recorded @ <?php echo date("D M j H:i Y",$omicron_nanobeam_pressure_time)?></font>
  </td>
  <td>
   <?php $res = currentValue("omicron_prep_overview");?>
   <font color="0000FF"><?php echo science_number($res[1])?> mbar recorded @ <?php echo date("D M j H:i Y",$res[0])?></font><br>
   <font color="FF0000"><?php echo round($omicron_temperature,2)?> K recorded @ <?php echo date("D M j H:i Y",$omicron_temperature_time)?></font>
  </td>
  <td>
  </td>
 </tr>
 <tr>
  <td>
   <h2 id="commonstatustitle">STM312</h1>
  </td>
 </tr>
 <tr>
  <td>
   <img src="plot.php?type=stm312_overview&small_plot=1">
  </td>
  <td>
   <!--<img src="plot.php?type=microreactorNG_pressure_overview&small_plot=1">-->
  </td>
  <td>
  </td>
 </tr>
 <tr>
  <td>
   <?php $res = currentValue("stm312_overview");?>
   <font color="0000FF"><?php echo science_number($res[1])?> mbar recorded @ <?php echo date("D M j H:i Y",$res[0])?></font><br>
   <font color="FF0000"><?php echo round($stm312_temperature,2)?> C recorded @ <?php echo date("D M j H:i Y",$stm312_temperature_time)?></font>
  </td>
  <td>
   <!--<?php $res = currentValue("microreactorNG_pressure_overview");?>
   <font color="0000FF"><?php echo science_number($res[1])?> mbar recorded @ <?php echo date("D M j H:i Y",$res[0])?></font><br>
   <font color="FF0000"><?php echo round($microreactorNG_pressure,2)?> mbar recorded @ <?php echo date("D M j H:i Y",$microreactorNG_pressure_time)?></font>-->
  </td>
  <td>
  </td>
 </tr>
</table>
</body>
</html>
