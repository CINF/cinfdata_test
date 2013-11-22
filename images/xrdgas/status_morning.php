<?
// Read dateplot is a rather simple status page used only for graphs with dates on the x-axis.
include("../common_functions.php");
?>
<?=new_html_header()?>
  <div class="graph">
  <h2>Pressure in the sputter chamber at 6 a.m.</h2>
		<img src="plot.php?type=morning_pressure&from=2009-01-01&to=2012-01-01&xsize=700&ysize=400">
  </div>
  <div class="graph">
  <h2>Temperature of crystal in the sputter chamber at 6 a.m.</h2>
		<img src="plot.php?type=morning_temperature&from=2009-01-01&to=2012-01-01&xsize=700&ysize=400">  
  </div>
<?=new_html_footer()?>
