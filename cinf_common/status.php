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

# GET BIFROST TEMPERATURE
$query = "SELECT unix_timestamp(time), temperature FROM heating_data_bifrost order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$bifrost_temperature_time = $row[0];
$bifrost_temperature = $row[1];

# GET STM312 TEMPERATURE
$query = "SELECT unix_timestamp(time), temperature FROM temperature_stm312 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$stm312_temperature_time = $row[0];
$stm312_temperature = round($row[1], 2);
if ( $stm312_temperature < -273.15 ){
  $stm312_temperature = 'N/A';
}

# GET MICROREACTOR TEMPERATURE
$query = "SELECT unix_timestamp(time), temperature FROM temperature_microreactor order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$microreactor_temperature_time = $row[0];
$microreactor_temperature = $row[1];

# GET OMICRON PREP TEMPERATURE
$query = "SELECT unix_timestamp(time), temperature FROM temperature_omicron order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$omicron_temperature_time = $row[0];
$omicron_temperature = $row[1];
?>

<html>
<head>
<title>CINF data logging</title>
<style title="css" type="text/css">@import "../style.css";</style>
</head>
<body>


<h1 id="commonstatus">Status of CINF chambers</h1>

<img src="../cinf_logo_web.png" id="logo">

<!--
<div style="width:450px;height:1000px;position:absolute;top:90px;left:20px;">
<h2 id="commonstatustitle">Volvo</h1>
<img src="plot.php?type=volvo_pressure&small_plot=1"><br>
<?php #$res = currentValue("pressure_volvo");?>
<?php #echo science_number($res[1])?> mbar recorded @ <?php #echo date("D M j H:i Y",$res[0])?><br>
&nbsp;
-->

<div style="width:450px;height:1000px;position:absolute;top:90px;left:20px;">
<h2 id="commonstatustitle">Experimental hall</h1>
<img src="plot.php?type=hall_overview&small_plot=1"><br>
<?php $res = currentValue("hall_overview");?>
<?php echo round($res[1],1)?> C recorded @ <?php echo date("D M j H:i Y",$res[0])?><br>
&nbsp;

<h2 id="commonstatustitle">Microreactor</h1>
<img src="plot.php?type=microreactor_overview&small_plot=1"><br>
<?php $res = currentValue("microreactor_overview");?>
<?php echo science_number($res[1])?> mbar recorded @ <?php echo date("D M j H:i Y",$res[0])?><br>
<?php $res = currentValue("microreactor_overview");?>
<?php echo round($microreactor_temperature,2)?> C recorded @ <?php echo date("D M j H:i Y",$microreactor_temperature_time)?>
<!-- <?php #echo round($res[1],1)?> C recorded @ <?php echo date("D M j H:i Y",$res[0])?> -->

<br>&nbsp;<br>
Pressure in blue and temperature in red<br>
NOW powered by matplotlib
</div>

<div style="width:450px;height:1000px;position:absolute;top:90px;left:470px;">
<h2 id="commonstatustitle">STM 312</h1>
<img src="plot.php?type=stm312_overview&small_plot=1"><br>
<?php $res = currentValue("stm312_overview");?>
<?php echo science_number($res[1])?> Torr recorded @ <?php echo date("D M j H:i Y",$res[0])?><br>
<?php $res = currentValue("stm312_overview");?>
<?php echo $stm312_temperature?> C recorded @ <?php echo date("D M j H:i Y",$stm312_temperature_time)?>
<!-- <?php #echo round($res[1],1)?> C recorded @ <?php echo date("D M j H:i Y",$res[0])?> -->


<h2 id="commonstatustitle">Omicron, ana.</h1>
<img src="plot.php?type=omicron_ana_overview&small_plot=1"><br>
<?php $res = currentValue("omicron_ana_overview");?>
<?php echo science_number($res[1])?> Torr recorded @ <?php echo date("D M j H:i Y",$res[0])?><br>
</div>

<div style="width:450px;height:1000px;position:absolute;top:90px;left:920px;">
<h2 id="commonstatustitle">Bifrost</h1><img src="plot.php?type=bifrost_overview&small_plot=1"><br>
<?php $res = currentValue("bifrost_overview");?>
<?php echo science_number($res[1])?> Torr recorded @ <?php echo date("D M j H:i Y",$res[0])?><br>
<?php $res = currentValue("bifrost_overview");?>
<?php echo round($bifrost_temperature,2)?> C recorded @ <?php echo date("D M j H:i Y",$bifrost_temperature_time)?>
<!-- <?php #echo round($res[1],1)?> C recorded @ <?php echo date("D M j H:i Y",$res[0])?> -->

<h2 id="commonstatustitle">Omicron, prep</h1>
<img src="plot.php?type=omicron_prep_overview&small_plot=1"><br>
<?php $res = currentValue("omicron_prep_overview");?>
<?php echo science_number($res[1])?> Torr recorded @ <?php echo date("D M j H:i Y",$res[0])?><br>
<?php $res = currentValue("omicron_prep_overview");?>
<?php echo round($omicron_temperature,2)?> C recorded @ <?php echo date("D M j H:i Y",$omicron_temperature_time)?>
<!-- <?php #echo round($res[1],1)?> C recorded @ <?php echo date("D M j H:i Y",$res[0])?> -->
</div>
</body>
</html>
