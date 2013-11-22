<?php
include("graphsettings.php");
include("../common_functions_v2.php");

$type = $_GET["type"];
$settings = plot_settings($type);

# Make a list of the all the graphs in the multiplot definition
$graphs = Array();
foreach($settings as $key => $value){
  # Regular expression matching the dateplotN tag (with minimum one N-digit)
  if (preg_match("/^dateplot[0-9][0-9]*$/", $key)){
    $graphs[$key] = $value;
  }
}

# Remember settings after submitting
$from              = isset($_GET["from"])               ? $_GET["from"]           : 0;
$to                = isset($_GET["to"])                 ? $_GET["to"]             : 0;
$left_ymax         = isset($_GET["left_ymax"])          ? $_GET["left_ymax"]      : 0;
$left_ymin         = isset($_GET["left_ymin"])          ? $_GET["left_ymin"]      : 0;
$right_ymax        = isset($_GET["right_ymax"])         ? $_GET["right_ymax"]     : 0;
$right_ymin        = isset($_GET["right_ymin"])         ? $_GET["right_ymin"]     : 0;
$left_logscale     = isset($_GET["left_logscale"])      ? "checked"               : "";
$right_logscale    = isset($_GET["right_logscale"])     ? "checked"               : "";
$left_plotlist     = isset($_GET["left_plotlist"])      ? $_GET["left_plotlist"]  : array();
$right_plotlist    = isset($_GET["right_plotlist"])     ? $_GET["right_plotlist"] : array();
$matplotlib        = isset($_GET["matplotlib"])         ? "checked"               : "";

# Generate URL for figure ...
$options_line = '';
$options = array('from', 'to',
		 'left_ymax', 'left_ymin',
		 'right_ymax', 'right_ymin', 
		 'left_logscale', 'right_logscale',
		 'matplotlib');
# ... add values ...
foreach($options as $value){
  $options_line .= '&' . $value . '=' . str_replace(' ', '+', $$value);
}
# ... and lists ...
foreach(array('left_plotlist', 'right_plotlist') as $value){
  foreach($$value as $id){
    $options_line .= '&' . $value . '[]=' . $id;
  }
}
# ... and append imageformat to them
$plot_php_line = 'plot.php?type=' . $type . $options_line . '&image_format=' . 'png';
$plot_php_line_graph =  'plot.php?type=' . $type . $options_line . '&image_format=' . $settings['image_format'];
$export_php_line = 'export_data.php?type=' . $type . $options_line . '&image_format=' . 'png';

echo(html_header());
 
if ($matplotlib == 'checked'){
   echo('<a href="' . $plot_php_line_graph . '">');
   echo('<img src="' . $plot_php_line . '"/>');
   echo('</a>');
   } else {
   echo('<div id="graphdiv" class="dydiv"> </div>');
   echo('<script type="text/javascript" src=' . $plot_php_line . '></script>');
}
?>
      </div>
      <hr>
      <form action="dateplot.php" method="get">
	<input type="hidden" name="type" value="<?php echo($type);?>">
	<table class="generaloptions">
	  <tr>
	    <!-- NOT IMPLEMENTED
            <td>
	      <select name="dateselection">
		<option value="1">1 day back</option>
		<option value="2">2 days back</option>
		<option value="3">3 days back</option>
	      </select>
	    </td>-->
	    <td>
	      <b>From:</b><input name="from" type="text" value="<?php echo($from);?>" size="13">
	    </td>
	    <td>
	      <b>To:</b><input name="to" type="text" value="<?php echo($to);?>" size="13">
	    </td>
	    <td>
	      <b>Matplotlib</b><input type="checkbox" name="matplotlib" onClick="javascript:toggle('matplotlib')" value="checked" <?php echo($matplotlib);?>>
	    </td>
	    <td>
	      <a href="<?php echo($export_php_line) ?>">Export current data</a>
	    </td>
	    <td>
	      <input type="submit" value="Update">
	    </td>
	  </tr>
	</table>
	<hr>
	<table class="selection">
	  <tr>
	    <td><!--LEFT Y-->
	      <b>Log-scale</b><input type="checkbox" name="left_logscale" value="checked" <?php echo($left_logscale);?>><br>
	      <b>Y-Min:</b><input name="left_ymin" type="text" size="7" value="<?php echo($left_ymin);?>"><br>
	      <b>Y-Max:</b><input name="left_ymax" type="text" size="7" value="<?php echo($left_ymax);?>"><br>
	      <b>Select measurement:</b><br>
	      <select class="select" multiple size="8" name="left_plotlist[]">
		<?php
		   # Creation of plotlist for left axis
		   foreach($graphs as $key => $value){
			$selected = (in_array(substr($key,-1),$left_plotlist)) ? "selected" : "";
			echo("<option value=\"" .substr($key,-1). "\" " . $selected .">" . substr($key,-1) . ":" . $value['title'] . "</option>\n");
		   }
		?>
	      </select>
	    </td>
	    <!--
	       <td align="center">
		 midterkolonnen - her er spas og løjer...
	       </td>
	       -->
	    <td align="right"><!--RIGHT Y-->
	      <b>Log-scale</b><input type="checkbox" name="right_logscale" value="checked" <?php echo($right_logscale);?>><br>
	      <b>Y-Min:</b><input name="right_ymin" type="text" size="7" value="<?php echo($right_ymin);?>"><br>
	      <b>Y-Max:</b><input name="right_ymax" type="text" size="7" value="<?php echo($right_ymax);?>"><br>
	      <b>Select measurement:</b><br>
	      <select class="select" multiple size="8" name="right_plotlist[]">
		<?php
		   # Creation of plotlist for right axis
		   foreach($graphs as $key => $value){
		$selected = (in_array(substr($key,-1),$right_plotlist)) ? "selected" : "";
		echo("<option value=\"" .substr($key,-1). "\" " . $selected . ">" . substr($key,-1) . ":" . $value['title'] . "</option>\n");
		}
		?>
	      </select>
	    </td>
	  </tr>
	</table>
      </form>
      
      <hr>

      <div class="additionalinfo">
	<h2><a href="javascript:toggle('sqlinfo')">SQL info</a></h2>
	<div id="sqlinfo" style="display:none">
	  <b>Sql-statement for this graph:</b><br>
	  SELECT unix_timestamp(time), pressure FROM pressure_microreactorNG where time between "2011-12-12 16:42" and "2011-12-13 16:43" order by time<br>
	  <b>Latest value:</b><br>
	  1.896e-08 @ 2011-12-13 16:33:53
	</div>
	
	<h2><a href="javascript:toggle('shortlinks')">Make shortlink</a></h2>
	<div id="shortlinks" style="display:none">
	  <form action="../links/link.php?url=checked" method="post">
	    <b>Comment for short link:</b> <input type="text" name="comment"><br><input type="submit" value="Make short link">
	  </form>
	</div>
	
	<div id="matplotlib" style="display:none">
	  <h2><a href="javascript:toggle('matplotlib')">Matplotlib options</a></h2>
	  <table>
	    <tr>
	      <td>
		<b>Title:</b>
	      </td>
	      <td>
		<input name="title" type="text" size="15">
	      </td>
	    </tr>
	    <tr>
	      <td>
		<b>x-label:</b>
	      </td>
	      <td>
		<input name="xlabel" type="text" size="15">
	      </td>
	    </tr>
	    <tr>
	      <td>
		<b>Left y-label</b>
	      </td>
	      <td>
		<input name="left_ylabel" type="text" size="15">
	      </td>
	    </tr>
	    <tr>
	      <td>
		<b>Right y-label:</b>
	      </td>
	      <td>
		<input name="right_ylabel" type="text" size="15">
	      </td>
	    </tr>
	  </table>
	</div>
<?php echo(html_footer());?>

