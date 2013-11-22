<?
include("../common_functions.php");
$db = std_db();

// Get current pressure
$query = "SELECT unix_timestamp(time), pressure FROM pressure order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$pressure_time = $row[0];
$current_pressure = $row[1];

// Get current temperature
$query = "SELECT unix_timestamp(time), temperature FROM temperature order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$temperature_time = $row[0];
$current_temperature = $row[1];

$query = "SELECT id, unix_timestamp(time) FROM measurements where type = 4 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$massscan_id = $row[0];
$massscan_time = $row[1];
?>

<?=html_header()?>

<h1>Status of the good ol' Volvo</h1>

<img src="../cinf_logo_web.png" id="logo">

<img src="volvo.png" id="volvo">

<div style="width:500px;position:absolute;top:70px;left:50px;">
<h2>Pressure history</h2>
<a href="read_pressure.php"><img src="pressure.php?xsize=400&ysize=300&smallplot=1"></a>
</div>

<div style="width:500px;position:absolute;top:70px;left:500px;">
<h2>Temperature history</h2>
<img src="temperature.php?xsize=400&ysize=300&smallplot=1">
</div>

<div style="width:500px;position:absolute;top:400px;left:50px;">
<h2>Current pressure</h2>
<?=science_number($current_pressure)?> mbar recorded @ <?=date("D M j H:i Y",$pressure_time)?>
<!-- odometer: http://hem.bredband.net/aditus/chunkhtml/ch20s02.html#example.odotutex02 -->
</div>

<div style="width:500px;position:absolute;top:400px;left:500px;">
<h2>Current temperature</h2>
<?=round($current_temperature,1)?> recorded @ <?=date("D M j H:i Y",$temperature_time)?>
</div>

<div style="width:500px;position:absolute;top:455px;left:50px;">
<h2>Latest mass scan</h2>
Recorded @ <?=date("D M j H:i Y",$massscan_time)?>
<a href="read_massspectrum.php?idlist[]=<?=$massscan_id?>"><img src="massspectrum.php?xsize=800&ysize=300&smallplot=1&idlist[]=<?=$massscan_id?>"></a>
<!-- Most important masses, pie plot
http://hem.bredband.net/aditus/chunkhtml/ch16.html#example.pieex3 -->
</div>

</body>
</html>
