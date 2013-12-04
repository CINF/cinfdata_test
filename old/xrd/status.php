<?
include("../common_functions.php");
$db = std_db();

// Get current pressure
$query = "SELECT unix_timestamp(time), pressure FROM pressure_xrdgas_asg order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$pressure_asg_time = $row[0];
$current_pressure_asg = $row[1];

// Get current pressure
$query = "SELECT unix_timestamp(time), pressure FROM pressure_xrdgas_wrg order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$pressure_wsg_time = $row[0];
$current_pressure_wrg = $row[1];

// Get current pressure
$query = "SELECT unix_timestamp(time), pressure FROM pressure_xrdgas_wrgms order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$pressure_wsgms_time = $row[0];
$current_pressure_wrgms = $row[1];


?>
<?=new_html_header()?>

  <div class="graph">
  <h2>Pressure history</h2>
		<a href="read_dateplot.php?type=pressure_asg"><img src="plot.php?type=pressure_asg&xsize=700&ysize=300&smallplot=1"></a>
  </div>

  <div class="graph">
  <h3>Last recorded pressure</h3>
		<?=science_number($current_pressure_asg)?> Torr recorded @ <?=date("D M j H:i Y",$pressure_asg_time)?>
  </div>

  <div class="graph">
  <h2>Pressure history</h2>
		<a href="read_dateplot.php?type=pressure_wrg"><img src="plot.php?type=pressure_wrg&xsize=700&ysize=300&smallplot=1"></a>
  </div>

  <div class="graph">
  <h3>Last recorded pressure</h3>
		<?=science_number($current_pressure_wrg)?> Torr recorded @ <?=date("D M j H:i Y",$pressure_wrg_time)?>
  </div>

  <div class="graph">
  <h2>Pressure history</h2>
		<a href="read_dateplot.php?type=pressure_wrgms"><img src="plot.php?type=pressure_wrgms&xsize=700&ysize=300&smallplot=1"></a>
  </div>

  <div class="graph">
  <h3>Last recorded pressure</h3>
		<?=science_number($current_pressure_wrgms)?> Torr recorded @ <?=date("D M j H:i Y",$pressure_wrgms_time)?>
  </div>

<?=new_html_footer()?>

</body>
</html>