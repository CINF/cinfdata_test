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

# GET UPS kVA Ph1
$query = "SELECT unix_timestamp(time), value FROM ups_kVAPh1 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ups_kVA_Ph1_time = $row[0];
$ups_kVA_Ph1 = $row[1];

# GET UPS kVA Ph2
$query = "SELECT unix_timestamp(time), value FROM ups_kVAPh2 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ups_kVA_Ph2_time = $row[0];
$ups_kVA_Ph2 = $row[1];

# GET UPS kVA Ph3
$query = "SELECT unix_timestamp(time), value FROM ups_kVAPh3 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ups_kVA_Ph3_time = $row[0];
$ups_kVA_Ph3 = $row[1];

# GET UPS POWER Ph1
$query = "SELECT unix_timestamp(time), value FROM ups_WPh1 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ups_power_Ph1_time = $row[0];
$ups_power_Ph1 = $row[1];

# GET UPS POWER Ph2
$query = "SELECT unix_timestamp(time), value FROM ups_WPh2 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ups_power_Ph2_time = $row[0];
$ups_power_Ph2 = $row[1];

# GET UPS POWER Ph3
$query = "SELECT unix_timestamp(time), value FROM ups_WPh3 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ups_power_Ph3_time = $row[0];
$ups_power_Ph3 = $row[1];

# GET UPS VOLTAGE Ph1
$query = "SELECT unix_timestamp(time), value FROM ups_voltagePh1 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ups_voltage_Ph1_time = $row[0];
$ups_voltage_Ph1 = $row[1];

# GET UPS VOLTAGE Ph2
$query = "SELECT unix_timestamp(time), value FROM ups_voltagePh2 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ups_voltage_Ph2_time = $row[0];
$ups_voltage_Ph2 = $row[1];

# GET UPS VOLTAGE Ph3
$query = "SELECT unix_timestamp(time), value FROM ups_voltagePh3 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ups_voltage_Ph3_time = $row[0];
$ups_voltage_Ph3 = $row[1];

# GET UPS CURRENT Ph1
$query = "SELECT unix_timestamp(time), value FROM ups_currentPh1 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ups_current_Ph1_time = $row[0];
$ups_current_Ph1 = $row[1];

# GET UPS CURRENT Ph2
$query = "SELECT unix_timestamp(time), value FROM ups_currentPh2 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ups_current_Ph2_time = $row[0];
$ups_current_Ph2 = $row[1];

# GET UPS CURRENT Ph3
$query = "SELECT unix_timestamp(time), value FROM ups_currentPh3 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ups_current_Ph3_time = $row[0];
$ups_current_Ph3 = $row[1];

# GET UPS INPUT VOLTAGE Ph1
$query = "SELECT unix_timestamp(time), value FROM ups_voltage1NormalInput order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ups_voltage_input_Ph1_time = $row[0];
$ups_voltage_input_Ph1 = $row[1];

# GET UPS INPUT VOLTAGE Ph2
$query = "SELECT unix_timestamp(time), value FROM ups_voltage2NormalInput order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ups_voltage_input_Ph2_time = $row[0];
$ups_voltage_input_Ph2 = $row[1];

# GET UPS INPUT VOLTAGE Ph3
$query = "SELECT unix_timestamp(time), value FROM ups_voltage3NormalInput order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ups_voltage_input_Ph3_time = $row[0];
$ups_voltage_input_Ph3 = $row[1];

# GET UPS INPUT FREQUENCY
$query = "SELECT unix_timestamp(time), value FROM ups_frequencyNormalInput order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ups_frequency_time = $row[0];
$ups_frequency = $row[1];

# GET UPS INPUT FREQUENCY
$query = "SELECT unix_timestamp(time), value FROM ups_batteryTemperatureLevel order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$ups_battery_temp_time = $row[0];
$ups_battery_temp = $row[1];

?>



<html>
<head>
<title>CINF data logging</title>
<style title="css" type="text/css">@import "../style.css";</style>
</head>
<body>


<h1 id="commonstatus">Status of the CINF UPS</h1>

<img src="../cinf_logo_web.png" id="logo">

<!--<h2 id="commonstatustitle">UPS</h2>-->
<table cellpadding="5">
 <tr align="center">
  <td>
   <img src="plot.php?type=UPS_voltage_kVA_overview&small_plot=1&ymin=0&ymax=10000">
  </td>
  <td>
   <img src="plot.php?type=UPS_power_overview&small_plot=1&ymin=0&ymax=10000">
  </td>
  <td>
   <img src="plot.php?type=UPS_voltage_overview&small_plot=1">
  </td>
 </tr>
 <tr>
  <td>
   <font color="0000FF"><?php echo round($ups_kVA_Ph1,2)?> VA recorded on Ph1 @ <?php echo date("D M j H:i Y",$ups_kVA_Ph1_time)?></font><br>
   <font color="FF0000"><?php echo round($ups_kVA_Ph2,2)?> VA recorded on Ph2 @ <?php echo date("D M j H:i Y",$ups_kVA_Ph2_time)?></font><br>
   <font color="green"><?php echo round($ups_kVA_Ph3,2)?> VA recorded on Ph3 @ <?php echo date("D M j H:i Y",$ups_kVA_Ph3_time)?></font>
  </td>
  <td>
   <font color="0000FF"><?php echo round($ups_power_Ph1,2)?> W recorded on Ph1 @ <?php echo date("D M j H:i Y",$ups_power_Ph1_time)?></font><br>
   <font color="FF0000"><?php echo round($ups_power_Ph2,2)?> W recorded on Ph2 @ <?php echo date("D M j H:i Y",$ups_power_Ph2_time)?></font><br>
   <font color="green"><?php echo round($ups_power_Ph3,2)?> W recorded on Ph3 @ <?php echo date("D M j H:i Y",$ups_power_Ph3_time)?></font>
  </td>
  <td>
   <font color="0000FF"><?php echo round($ups_voltage_Ph1)?> V recorded on Ph1 @ <?php echo date("D M j H:i Y",$ups_voltage_Ph1_time)?></font><br>
   <font color="FF0000"><?php echo round($ups_voltage_Ph2)?> V recorded on Ph2 @ <?php echo date("D M j H:i Y",$ups_voltage_Ph2_time)?></font><br>
   <font color="green"><?php echo round($ups_voltage_Ph3)?> V recorded on Ph3 @ <?php echo date("D M j H:i Y",$ups_voltage_Ph3_time)?></font><br>
  </td>
 </tr>
 <tr>
  <td>
   <!--<h2 id="commonstatustitle">MicroreactorNG</h1>-->
  </td>
 </tr>
 <tr>
  <td>
   <img src="plot.php?type=UPS_current_overview&small_plot=1&ymin=0&ymax=45">
  </td>
  <td>
   <img src="plot.php?type=UPS_voltage_input_overview&small_plot=1">
  </td>
  <td>
   <img src="plot.php?type=UPS_freq_batt_temp_overview&small_plot=1">
  </td>
 </tr>
 <tr>
  <td>
   <font color="0000FF"><?php echo round($ups_current_Ph1)?> A recorded on Ph1 @ <?php echo date("D M j H:i Y",$ups_current_Ph1_time)?></font><br>
   <font color="FF0000"><?php echo round($ups_current_Ph2)?> A recorded on Ph2 @ <?php echo date("D M j H:i Y",$ups_current_Ph2_time)?></font><br>
   <font color="green"><?php echo round($ups_current_Ph3)?> A recorded on Ph3 @ <?php echo date("D M j H:i Y",$ups_current_Ph3_time)?></font><br>
  </td>
  <td>
   <font color="0000FF"><?php echo round($ups_voltage_input_Ph1,4)?> V recorded on Ph1 @ <?php echo date("D M j H:i Y",$ups_voltage_input_Ph1_time)?></font><br>
   <font color="FF0000"><?php echo round($ups_voltage_input_Ph2,2)?> V recorded on Ph2 @ <?php echo date("D M j H:i Y",$ups_voltage_input_Ph2_time)?></font><br>
   <font color="green"><?php echo round($ups_voltage_input_Ph3,2)?> V recorded on Ph3 @ <?php echo date("D M j H:i Y",$ups_voltage_input_Ph3_time)?></font><br>
  </td>
  <td>
   <font color="0000FF"><?php echo round($ups_frequency,4)?> Hz recorded @ <?php echo date("D M j H:i Y",$ups_frequency_time)?></font><br>
   <font color="FF0000"><?php echo round($ups_battery_temp)?> C recorded @ <?php echo date("D M j H:i Y",$ups_battery_temp_time)?></font>
  </td>
 </tr>
</table>
</body>
</html>
