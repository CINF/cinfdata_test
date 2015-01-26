<?php
  /*
    Copyright (C) 2012 Robert Jensen, Thomas Andersen and Kenneth Nielsen
    
    The CINF Data Presentation Website is free software: you can
    redistribute it and/or modify it under the terms of the GNU
    General Public License as published by the Free Software
    Foundation, either version 3 of the License, or
    (at your option) any later version.
    
    The CINF Data Presentation Website is distributed in the hope
    that it will be useful, but WITHOUT ANY WARRANTY; without even
    the implied warranty of MERCHANTABILITY or FITNESS FOR A
    PARTICULAR PURPOSE.  See the GNU General Public License for more
    details.
    
    You should have received a copy of the GNU General Public License
    along with The CINF Data Presentation Website.  If not, see
    <http://www.gnu.org/licenses/>.
  */

include("graphsettings.php");
include("../common_functions_v2.php");
$db = std_db();

# Get the plot type
$type = $_GET["type"];

# Get the default time from an uninitialized version of the settings
$uninit_settings = plot_settings($type);
if (array_key_exists('default_time', $uninit_settings)){
  $default_time = (int) $uninit_settings['default_time'];
} else {
  $default_time = 24;
}
# Get the default to and from to populate fields in the GUI
$xscale = date_xscale(0, 0, $default_time);

# Remember settings after submitting
$from              = isset($_GET["from"])               ? $_GET["from"]           : $xscale["from"];
$to                = isset($_GET["to"])                 ? $_GET["to"]             : $xscale["to"];
$left_ymax         = isset($_GET["left_ymax"])          ? $_GET["left_ymax"]      : 0;
$left_ymin         = isset($_GET["left_ymin"])          ? $_GET["left_ymin"]      : 0;
$right_ymax        = isset($_GET["right_ymax"])         ? $_GET["right_ymax"]     : 0;
$right_ymin        = isset($_GET["right_ymin"])         ? $_GET["right_ymin"]     : 0;
$left_logscale     = isset($_GET["left_logscale"])      ? "checked"               : "";
$right_logscale    = isset($_GET["right_logscale"])     ? "checked"               : "";
$left_plotlist     = isset($_GET["left_plotlist"])      ? $_GET["left_plotlist"]  : array();
$right_plotlist    = isset($_GET["right_plotlist"])     ? $_GET["right_plotlist"] : array();
$matplotlib        = isset($_GET["matplotlib"])         ? "checked"               : "";

# Get the fully initialized version of the settings
$settings = plot_settings($type, Array("from" => $from, "to" => $to));

# Make a list of the all the graphs in the multiplot definition
$graphs = Array();
foreach($settings as $key => $value){
  # Regular expression matching the dateplotN tag (with minimum one N-digit)
  if (preg_match("/^dateplot[0-9][0-9]*$/", $key)){
    $graphs[$key] = $value;
  }
}

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
  $dygraph_settings = isset($settings["dygraph_settings"]) ? $settings["dygraph_settings"] : array();
  $labels_side = isset($dygraph_settings["labels_side"]) ? $dygraph_settings["labels_side"] : "false";
  echo('<div id=warning_div></div>');
  if ($labels_side == "true"){
    echo('<div id="graphdiv" class="dydiv2"></div><div id="labels"></div>');
  } else {
    echo('<div id="graphdiv" class="dydiv"> </div>');
  }
  echo('<script type="text/javascript" src=' . $plot_php_line . '></script>');
}
?>
      </div>
      <!--<span class="clear"></span>-->
      <hr class="clear">
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
	      <b>From:</b><input name="from" type="text" title="Format YYYY-MM-DD HH:MM" value="<?php echo($from);?>" size="13">
	    </td>
	    <td>
	      <b>To:</b><input name="to" type="text" title="Format YYYY-MM-DD HH:MM" value="<?php echo($to);?>" size="13">
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
                   # Determine the max number of digits in the dateplotnum
                   $num_digits = 0;
                   foreach(array_keys($graphs) as $key){
		        preg_match("/[0-9]+/", $key, $matches);
		        $num_digits = max($num_digits, strlen($matches[0]));
		   }
		   # Creation of plotlist for left axis
                   echo("<!-- List of options -->\n");
		   foreach($graphs as $key => $value){
		        preg_match("/[0-9]+/", $key, $matches);
		        $dateplotnum = $matches[0];
			$selected = (in_array($dateplotnum, $left_plotlist)) ? "selected" : "";
			echo(str_repeat(" ", 16) . "<option value=\"" . $dateplotnum . "\" " . $selected .">" . str_pad($dateplotnum, $num_digits, "0", STR_PAD_LEFT) . ":" . $value['title'] . "</option>\n");
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
                   echo("<!-- List of options -->\n");
		   foreach($graphs as $key => $value){
		   preg_match("/[0-9]+/", $key, $matches);
		   $dateplotnum = $matches[0];
		   $selected = (in_array($dateplotnum, $right_plotlist)) ? "selected" : "";
		   echo(str_repeat(" ", 16) . "<option value=\"" . $dateplotnum . "\" " . $selected . ">" . str_pad($dateplotnum, $num_digits, "0", STR_PAD_LEFT) . ":" . $value['title'] . "</option>\n");
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
	  <h3>Sql-statements for this graph, left side:</h3>
	  <?php
	     foreach ($left_plotlist as $index){
	     $latest = latest_sql_row($db, $settings["dateplot" . $index]["query"]);
	     echo("<p><b>Dateplot {$index}:</b><br>");
	     echo("<b>Query: </b>" . $settings["dateplot" . $index]["query"] . "<br>");
	     echo("<b>Latest value: </b>{$latest[1]}@" . date("Y-m-d H:i:s", $latest[0]) . "</p>");
	     }
	     ?>
	  <h3>Sql-statements for this graph, right side:</h3>
	  <?php
	     foreach ($right_plotlist as $index){
	     $latest = latest_sql_row($db, $settings["dateplot" . $index]["query"]);
	     echo("<p><b>Dateplot {$index}:</b><br>");
	     echo("<b>Query: </b>" . $settings["dateplot" . $index]["query"] . "<br>");
	     echo("<b>Latest value: </b>{$latest[1]}@" . date("Y-m-d H:i:s", $latest[0]) . "</p>");
	     }
	     ?>
	</div>
	
	<h2><a href="javascript:toggle('shortlinks')">Make shortlink</a></h2>
	<div id="shortlinks" style="display:none">
	  <form action="../links/link.php?url=checked" method="post">
	    <b>Comment for shortlink:</b> <input type="text" name="comment"><br><input type="submit" value="Make short link">
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
