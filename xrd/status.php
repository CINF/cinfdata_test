<?php
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
$pressure_wrg_time = $row[0];
$current_pressure_wrg = $row[1];

// Get current pressure
$query = "SELECT unix_timestamp(time), pressure FROM pressure_xrdgas_wrgms order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$pressure_wrgms_time = $row[0];
$current_pressure_wrgms = $row[1];


?>
<?php echo(new_html_header())?>
  <div class="graph">
  <h2>Pressure history asg</h2>
  <a href="dateplot.php?type=multidateplot&matplotlib=checked&left_plotlist[]=1"><img src="plot.php?type=multidateplot&left_logscale=checked&matplotlib=checked&left_plotlist[]=1&image_format=png"></a>y
  </div>

  <div class="graph">
  <h3>Last recorded pressure</h3>
  <?php echo(science_number($current_pressure_asg))?> mbar recorded @ <?php echo(date("D M j H:i Y",$pressure_asg_time))?>
  </div>

  <div class="graph">
  <h2>Pressure history wrg</h2>
  <a href="dateplot.php?type=multidateplot&matplotlib=checked&left_plotlist[]=2"><img src="plot.php?type=multidateplot&left_logscale=checked&matplotlib=checked&left_plotlist[]=2&image_format=png"></a>
  </div>

  <div class="graph">
  <h3>Last recorded pressure</h3>
  <?php echo(science_number($current_pressure_wrg))?> mbar recorded @ <?php echo(date("D M j H:i Y",$pressure_wrg_time))?>
  </div>

  <div class="graph">
  <h2>Pressure history wrgms</h2>
  <a href="dateplot.php?type=multidateplot&matplotlib=checked&left_plotlist[]=3"><img src="plot.php?type=multidateplot&left_logscale=checked&matplotlib=checked&left_plotlist[]=3&image_format=png"></a>
  </div>

  <div class="graph">
  <h3>Last recorded pressure</h3>
  <?php echo(science_number($current_pressure_wrgms))?> Torr recorded @ <?php echo(date("D M j H:i Y",$pressure_wrgms_time))?>
  </div>

  <?php echo(new_html_footer())?>

</body>
</html>