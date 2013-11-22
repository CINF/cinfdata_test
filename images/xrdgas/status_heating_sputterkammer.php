<?
include("../common_functions.php");
$db = std_db();

// Get current rmsvoltage
$query = "SELECT unix_timestamp(time), rmsvoltage FROM heating_data_sputterkammer order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$rmsvoltage_time = $row[0];
$current_rmsvoltage = $row[1];

// Get current rmscurrent
$query = "SELECT unix_timestamp(time), rmscurrent FROM heating_data_sputterkammer order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$rmscurrent_time = $row[0];
$current_rmscurrent = $row[1];

// Get current power
$query = "SELECT unix_timestamp(time), power FROM heating_data_sputterkammer order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$power_time = $row[0];
$current_power = $row[1];

// Get current temperature
$query = "SELECT unix_timestamp(time), temperature FROM heating_data_sputterkammer order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$temperature_time = $row[0];
$current_temperature = $row[1];

// Get current resistance
$query = "SELECT unix_timestamp(time), rmsvoltage/rmscurrent FROM heating_data_sputterkammer order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$resistance_time = $row[0];
$current_resistance = $row[1];

// Get current pressure
$query = "SELECT unix_timestamp(time), pressure FROM pressure_sputterkammer order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$pressure_time = $row[0];
$current_pressure = $row[1];
?>

<html>
<head>
<title>CINF data logging</title>
<style title="css" type="text/css">@import "../style.css";</style>
</head>
<body>

<h1>Heating status in the sputter chamber</h1>

<a href="/"><img src="../cinf_logo_web.png" id="logo"></a>

<div style="width:500px;position:absolute;top:70px;left:50px;">
<h2>Voltage history</h2>
<a href="read_dateplot.php?type=heating_voltage"><img src="plot.php?type=heating_voltage&xsize=400&ysize=300&smallplot=1"></a>
</div>

<div style="width:500px;position:absolute;top:70px;left:500px;">
<h2>Current history</h2>
<a href="read_dateplot.php?type=heating_current"><img src="plot.php?type=heating_current&xsize=400&ysize=300&smallplot=1"></a>
</div>

<div style="width:500px;position:absolute;top:400px;left:50px;">
<h2>Last recorded voltage</h2>
<?=round($current_rmsvoltage,2)?> V recorded @ <?=date("D M j H:i Y",$rmsvoltage_time)?>
</div>

<div style="width:500px;position:absolute;top:400px;left:500px;">
<h2>Last recorded current</h2>
<?=round($current_rmscurrent,2)?> A recorded @ <?=date("D M j H:i Y",$rmscurrent_time)?>
</div>

<div style="width:500px;position:absolute;top:470px;left:50px;">
<h2>Power history</h2>
<a href="read_dateplot.php?type=heating_power"><img src="plot.php?type=heating_power&xsize=400&ysize=300&smallplot=1"></a>
</div>

<div style="width:500px;position:absolute;top:470px;left:500px;">
<h2>Temperature history</h2>
<a href="read_dateplot.php?type=temperature"><img src="plot.php?type=temperature&xsize=400&ysize=300&smallplot=1"></a>
</div>

<div style="width:500px;position:absolute;top:800px;left:50px;">
<h2>Last recorded power</h2>
<?=round($current_power,2)?> W recorded @ <?=date("D M j H:i Y",$power_time)?>
</div>

<div style="width:500px;position:absolute;top:800px;left:500px;">
<h2>Last recorded temperature</h2>
<?=round($current_temperature,1)?> C recorded @ <?=date("D M j H:i Y",$temperature_time)?>
</div>

<div style="width:500px;position:absolute;top:870px;left:50px;">
<h2>Resistance history</h2>
<a href="read_dateplot.php?type=heating_resistance"><img src="plot.php?type=heating_resistance&xsize=400&ysize=300&smallplot=1"></a>
</div>

<div style="width:500px;position:absolute;top:1200px;left:50px;">
<h2>Last recorded resistance</h2>
<?=round($current_resistance,1)?> &#937; recorded @ <?=date("D M j H:i Y",$resistance_time)?>
</div>

<div style="width:500px;position:absolute;top:870px;left:500px;">
<h2>Pressure history</h2>
<a href="read_dateplot.php?type=pressure"><img src="plot.php?type=pressure&xsize=400&ysize=300&smallplot=1"></a>
</div>

<div style="width:500px;position:absolute;top:1200px;left:500px;">
<h2>Last recorded pressure</h2>
<?=science_number($current_pressure)?> Torr recorded @ <?=date("D M j H:i Y",$pressure_time)?>
</div>

</body>
</html>
