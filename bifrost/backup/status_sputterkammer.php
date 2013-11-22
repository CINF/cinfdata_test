<?
include("../common_functions.php");
$db = std_db();

// Get current rmsvoltage
$query = "SELECT unix_timestamp(time), pressure FROM pressure_sputterkammer order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$pressure_time = $row[0];
$current_pressure = $row[1];

// Get current temperature
$query = "SELECT unix_timestamp(time), temperature FROM heating_data_sputterkammer order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$temperature_time = $row[0];
$current_temperature = $row[1];
?>
<?=new_html_header()?>

  <div class="graph">
  <h2>Pressure history</h2>
		<a href="read_dateplot.php?type=pressure"><img src="plot.php?type=pressure&xsize=700&ysize=300&smallplot=1"></a>
  </div>

  <div class="graph">
  <h3>Last recorded pressure</h3>
		<?=science_number($current_pressure)?> Torr recorded @ <?=date("D M j H:i Y",$pressure_time)?>
  </div>

  <div class="graph">
  <h2>Temperature history</h2>
		<a href="read_dateplot.php?type=temperature"><img src="plot.php?type=temperature&xsize=700&ysize=300&smallplot=1"></a>
  </div>

  <div class="graph">
  <h3>Last recorded temperature of crystal</h3>
		<?=round($current_temperature,1)?> C recorded @ <?=date("D M j H:i Y",$temperature_time)?>
  </div>

<?=new_html_footer()?>

</body>
</html>