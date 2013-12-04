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
$type = $_GET["type"];
$settings = plot_settings($type);

// Get the id-number and timestamp of the newest measurement
$query = "SELECT id, " . $settings["grouping_column"] . " FROM " . $settings["measurements_table"] . " where type = " . $settings["type"] . " order by time desc limit 1";
$latest_id = single_sql_value($db,$query,0);
$latest_time = single_sql_value($db,$query,1);

// If graphsettings.xml do not have a specific grouping column setting we will default to "time"
if(in_array("grouping_column",array_keys($settings)) != "1"){
 $settings["grouping_column"] = "time";
}

// Remember settings after submitting and initialize values
$chosen_group      = isset($_GET["chosen_group"])       ? $_GET["chosen_group"]        : array($latest_time);
$xmin              = isset($_GET["xmin"])               ? $_GET["xmin"]                : 0;
$xmax              = isset($_GET["xmax"])               ? $_GET["xmax"]                : 0;
$left_ymax         = isset($_GET["left_ymax"])          ? $_GET["left_ymax"]           : 0;
$left_ymin         = isset($_GET["left_ymin"])          ? $_GET["left_ymin"]           : 0;
$right_ymax        = isset($_GET["right_ymax"])         ? $_GET["right_ymax"]          : 0;
$right_ymin        = isset($_GET["right_ymin"])         ? $_GET["right_ymin"]          : 0;
$left_logscale     = isset($_GET["left_logscale"])      ? "checked"                    : "";
$right_logscale    = isset($_GET["right_logscale"])     ? "checked"                    : "";
$left_plotlist     = isset($_GET["left_plotlist"])      ? $_GET["left_plotlist"]       : array($latest_id);
$right_plotlist    = isset($_GET["right_plotlist"])     ? $_GET["right_plotlist"]      : array();
$matplotlib        = isset($_GET["matplotlib"])         ? "checked"                    : "";
$plot_options      = isset($_GET["plot_options"])       ? "checked"                    : "";
$flip_x            = isset($_GET["flip_x"])             ? "checked"                    : "";
$as_function_of    = isset($_GET["as_function_of"])     ? "checked"                    : "";
$diff_left_y       = isset($_GET["diff_left_y"])        ? "checked"                    : "";
$diff_right_y      = isset($_GET["diff_right_y"])       ? "checked"                    : "";
$linscale_x0       = isset($_GET["linscale_x0"])        ? "checked"                    : "";
$linscale_x1       = isset($_GET["linscale_x1"])        ? "checked"                    : "";
$linscale_x2       = isset($_GET["linscale_x2"])        ? "checked"                    : "";
$linscale_left_y0  = isset($_GET["linscale_left_y0"])   ? "checked"                    : "";
$linscale_left_y1  = isset($_GET["linscale_left_y1"])   ? "checked"                    : "";
$linscale_left_y2  = isset($_GET["linscale_left_y2"])   ? "checked"                    : "";
$linscale_right_y0 = isset($_GET["linscale_right_y0"])  ? "checked"                    : "";
$linscale_right_y1 = isset($_GET["linscale_right_y1"])  ? "checked"                    : "";
$linscale_right_y2 = isset($_GET["linscale_right_y2"])  ? "checked"                    : "";
$title             = isset($_GET["title"])              ? weed($_GET["title"])         : "";
$xlabel            = isset($_GET["xlabel"])             ? weed($_GET["xlabel"])        : "";
$left_ylabel       = isset($_GET["left_ylabel"])        ? weed($_GET["left_ylabel"])   : "";
$right_ylabel      = isset($_GET["right_ylabel"])       ? weed($_GET["right_ylabel"])  : "";

// Simple function to replicate PHP 5 behaviour
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

// Get all available measurements to populate selection boxes
$query = "SELECT distinct " . $settings["grouping_column"] . ", comment FROM " .  $settings["measurements_table"] . " where type = " . $settings["type"] . " order by time desc, id limit 25000";
$result  = mysql_query($query,$db);
  while ($row = mysql_fetch_array($result)){
    $datelist[] = $row[0];
    $commentlist[] = $row[1];
  }

// $chosen_group is the list of timestamps that is currently active. This is either the timestamplist from the url or the latest measurement
$sql_times = "";
foreach($chosen_group as $time){
    $sql_times = $sql_times . "\"" . $time . "\",";
}
$sql_times  = substr($sql_times, 0, -1);  // Remove the trailing comma

// Create the list of individual components of the chosen measurements
$query = "SELECT id, time, " . $settings["label_column"] . " FROM " .  $settings["measurements_table"] . " where " . $settings["grouping_column"] . " in (" . $sql_times . ") order by time desc, id limit 800";

// replace \ with \\ in comments (Some setups have bad habits...)
$query = str_replace("\\","\\\\",$query);

$result  = mysql_query($query,$db);
while ($row = mysql_fetch_array($result)){
    $individ_idlist[] = $row[0];
    $individ_datelist[] = $row[1];
    $individ_labellist[] = $row[2];
}

// If $individ_labellist is empty we will assign "data" to populate the individual id list
if ($individ_labellist[0] == ""){
   $individ_labellist[0] = "data";
}

// Generate URL for figure ...
$options_line = "";
$options = array('xmin', 'xmax',
		 'left_ymax', 'left_ymin',
		 'right_ymax', 'right_ymin', 
		 'left_logscale', 'right_logscale',
		 'matplotlib',
		 'flip_x',
		 'as_function_of',
		 'diff_left_y',
		 'diff_right_y',
		 'linscale_x0',
		 'linscale_x1',
		 'linscale_x2',
		 'linscale_left_y0',
		 'linscale_left_y1',
		 'linscale_left_y2',
		 'linscale_right_y0',
		 'linscale_right_y1',
		 'linscale_right_y2',
		 'title',
		 'xlabel',
		 'left_ylabel',
		 'right_ylabel'
		 );

// ... add values ...
foreach($options as $value){
  $options_line .= '&' . $value . '=' . str_replace(' ', '+', $$value);
}

// ... and lists ...
foreach(array('left_plotlist', 'right_plotlist') as $value){
  foreach($$value as $id){
  $options_line .= '&' . $value . '[]=' . $id;
  }
}

// ... and append imageformat to them
$plot_php_line = 'plot.php?type=' . $type . $options_line . '&image_format=' . 'png';
$plot_php_line_graph = 'plot.php?type=' . $type . $options_line . '&image_format=' . $settings['image_format'];
$export_php_line = 'export_data.php?type=' . $type . $options_line . '&image_format=' . 'png';

echo(html_header());

if ($matplotlib == 'checked'){
   echo('<a href="' . $plot_php_line_graph . '">');
   echo('<img class="matplotlib" src="' . $plot_php_line . '"/>');
   echo('</a>');
   } else {
  $dygraph_settings = isset($settings["dygraph_settings"]) ? $settings["dygraph_settings"] : array();
  $labels_side = isset($dygraph_settings["labels_side"]) ? $dygraph_settings["labels_side"] : "false";
  if ($labels_side == "true"){
    echo('<div id="graphdiv" class="dydiv2"></div><div id="labels"></div>');
  } else {
    echo('<div id="graphdiv" class="dydiv"> </div>');
  }
  echo('<script type="text/javascript" src=' . $plot_php_line . '></script>');
#echo('<div id="graphdiv" class="dydiv"> </div>');
#echo('<script type="text/javascript" src=' . $plot_php_line . '></script>');

}
?>
     </div>
      <hr class="clear">
      
      <form action="xyplot.php" method="get">
	  <div id="container_measurements">
	  <input type="hidden" name="type" value="<?php echo($type);?>">
	    <!-- NOT IMPLEMENTED
	    <select name="dateselection"> 
		  <option value="1">1 day back</option>
		  <option value="2">2 days back</option>
		  <option value="3">3 days back</option>
	    </select> -->
        <div class="permanent_options">  
	       <!--X-axis zoom is implemented in the data selection, not in the graph, and therefore it can only be given in the units and scale of the original untreated x-axis data"-->
	      <b>x-min:</b><input name="xmin" type="text" value="<?php echo($xmin);?>" size="13"><br>
	       <!--X-axis zoom is implemented in the data selection, not in the graph, and therefore it can only be given in the units and scale of the original untreated x-axis data-->
	      <b>x-max:</b><input name="xmax" type="text" value="<?php echo($xmax);?>" size="13"><br>
	      <input id="submit_button" type="submit" value="Update">
        </div>
        <b>Plot options</b><input type="checkbox" name="plot_options" onClick="javascript:toggle('plot_options')" value="checked" <?php echo($plot_options);?>><br>
          <?php

              // Remember to show matplotlib fields after a submit
              if($plot_options == "checked"){
                $show_plot_options = "display:block";
              } else {
                $show_plot_options = "display:none";
              }
		    ?>
		    <span id="plot_options" style="<?php echo($show_plot_options);?>">
		    <hr>
		  <?php
          // Check for specific settings defined in graphsettings.xml and print if defined
            if(in_array("flip_x",array_keys($settings)) == "1"){
               echo($settings["flip_x"]["gui"] . "<input type=\"checkbox\" name=\"flip_x\" value=\"checked\"" . $flip_x . "><br>");
            }
            if(in_array("as_function_of",array_keys($settings)) == "1"){
               echo($settings["as_function_of"]["gui"] . "<input type=\"checkbox\" name=\"as_function_of\" value=\"checked\"" . $as_function_of . "><br>");
            }
            if(in_array("diff_left_y",array_keys($settings)) == "1"){
               echo($settings["diff_left_y"]["gui"] . "<input type=\"checkbox\" title=\"Assumes equidistant x-spacing!\" name=\"diff_left_y\" value=\"checked\"" . $diff_left_y . "><br>");
            }
            if(in_array("diff_right_y",array_keys($settings)) == "1"){
               echo($settings["diff_right_y"]["gui"] . "<input type=\"checkbox\" name=\"diff_right_y\" title=\"Assumes equidistant x-spacing!\" value=\"checked\"" . $diff_right_y . "><br>");
            }
            if(in_array("linscale_x0",array_keys($settings)) == "1"){
               echo($settings["linscale_x0"]["gui"] . "<input type=\"checkbox\" name=\"linscale_x0\" value=\"checked\"" . $linscale_x0 . "><br>");
            }
            if(in_array("linscale_x1",array_keys($settings)) == "1"){
               echo($settings["linscale_x1"]["gui"] . "<input type=\"checkbox\" name=\"linscale_x1\" value=\"checked\"" . $linscale_x1 . "><br>");
            }
            if(in_array("linscale_x2",array_keys($settings)) == "1"){
               echo($settings["linscale_x2"]["gui"] . "<input type=\"checkbox\" name=\"linscale_x2\" value=\"checked\"" . $linscale_x2 . "><br>");
            }
            if(in_array("linscale_left_y0",array_keys($settings)) == "1"){
               echo($settings["linscale_left_y0"]["gui"] . "<input type=\"checkbox\" name=\"linscale_left_y0\" value=\"checked\"" . $linscale_left_y0 . "><br>");
            }
            if(in_array("linscale_left_y1",array_keys($settings)) == "1"){
               echo($settings["linscale_left_y1"]["gui"] . "<input type=\"checkbox\" name=\"linscale_left_y1\" value=\"checked\"" . $linscale_left_y1 . "><br>");
            }
            if(in_array("linscale_left_y2",array_keys($settings)) == "1"){
               echo($settings["linscale_left_y2"]["gui"] . "<input type=\"checkbox\" name=\"linscale_left_y2\" value=\"checked\"" . $linscale_left_y2 . "><br>");
            }
            if(in_array("linscale_right_y0",array_keys($settings)) == "1"){
               echo($settings["linscale_right_y0"]["gui"] . "<input type=\"checkbox\" name=\"linscale_right_y0\" value=\"checked\"" . $linscale_right_y0 . "><br>");
            }
            if(in_array("linscale_right_y1",array_keys($settings)) == "1"){
               echo($settings["linscale_right_y1"]["gui"] . "<input type=\"checkbox\" name=\"linscale_right_y1\" value=\"checked\"" . $linscale_right_y1 . "><br>");
            }
            if(in_array("linscale_right_y2",array_keys($settings)) == "1"){
               echo($settings["linscale_right_y2"]["gui"] . "<input type=\"checkbox\" name=\"linscale_right_y2\" value=\"checked\"" . $linscale_right_y2 . "><br>");
            }
          ?>
          <a href="<?php echo($export_php_line)?>">Export current data</a>
          <hr>
          </span>
	      <b>Matplotlib</b><input type="checkbox" name="matplotlib" onClick="javascript:toggle('matplotlib')" value="checked" <?php echo($matplotlib);?>>
            <?php
              // Remember to show matplotlib fields after a submit
              if($matplotlib == "checked"){
                $show_matplotlib = "display:block";
              } else {
                $show_matplotlib = "display:none";
              }
            ?>
              <span id="matplotlib" style="<?php echo($show_matplotlib);?>">
                Title:<input name="title" type="text" size="15" value="<?php echo($title);?>"><br>
                x-label:<input name="xlabel" type="text" size="15" value="<?php echo($xlabel);?>"><br>
                Left y-label: <input name="left_ylabel" type="text" size="15" value="<?php echo($left_ylabel);?>"><br>
                Right y-label: <input name="right_ylabel" type="text" size="15" value="<?php echo($right_ylabel);?>">
              </span>
	<hr>

	 <div id="list_components">
		  <div id="left_y">
          <!--LEFT Y-->
	      <b>Log-scale</b><input type="checkbox" name="left_logscale" value="checked" <?php echo($left_logscale);?>><br>
	      <b>Y-Min:</b><input name="left_ymin" type="text" size="7" value="<?php echo($left_ymin);?>"><br>
	      <b>Y-Max:</b><input name="left_ymax" type="text" size="7" value="<?php echo($left_ymax);?>"><br>
	      <b>Select component (left y-axis):</b><br>
			<select multiple size="8" name="left_plotlist[]">
				<?php
                     // Creation of plotlist for left axis
		             for($i=0;$i<count($individ_idlist);$i++){
                        $selected = (in_array($individ_idlist[$i],$left_plotlist)) ? "selected" : "";
                        echo("<option value=\"" . $individ_idlist[$i] . "\" " . $selected . ">" . $individ_datelist[$i] . ": " . $individ_labellist[$i] . "</option>\n");
                     }
		          ?>
	         </select>
          </div>
		  <div id="right_y">
	          <!--RIGHT Y-->
              <b>Log-scale</b><input type="checkbox" name="right_logscale" value="checked" <?php echo($right_logscale);?>><br>
	          <b>Y-Min:</b><input name="right_ymin" type="text" size="7" value="<?php echo($right_ymin);?>"><br>
	          <b>Y-Max:</b><input name="right_ymax" type="text" size="7" value="<?php echo($right_ymax);?>"><br>
	          <b>Select component (right y-axis):</b><br>
			  <select multiple size="8" name="right_plotlist[]" id="right_plotlist">
				<?php
                     // Creation of plotlist for right axis
		             for($i=0;$i<count($individ_idlist);$i++){
                        $selected = (in_array($individ_idlist[$i],$right_plotlist)) ? "selected" : "";
                        echo("<option value=\"" . $individ_idlist[$i] . "\" " . $selected . ">" . $individ_datelist[$i] . ": " . $individ_labellist[$i] . "</option>\n");
                     }
		        ?>
	          </select>
          </div>   
       </div>
          <div id="list_measurements">
           <!--LIST OF MEASUREMENTS-->
	       <b>Select measurement:</b><br>
             <select id="measurement_select" name="chosen_group[]" multiple size="8" onChange="showData(this.value, <?php echo("'" . $type . "'"); ?>)">
              <?php
                // Creation of measurement list
                for($i=0;$i<count($datelist);$i++){
                    $selected = (in_array($datelist[$i],$chosen_group)) ? "selected" : "";
                    echo("<option value=\"" . $datelist[$i] . "\" " . $selected . ">" . $datelist[$i] . ": " . $commentlist[$i] . "</option>\n");
                }
              ?>
             </select>
           </div>
      </div>
      </form>
      
      <div style="clear: both;"></div>
      <hr>

      <div class="additionalinfo">
	<h2><a href="javascript:toggle('sqlinfo')">SQL info</a></h2>
	<div id="sqlinfo" style="display:none">
	  <b>Sql-statement for this graph:</b><br>
	  <?php echo($query);?><br>
	  <b>Latest value:</b><br>
	  1.896e-08 @ 2011-12-13 16:33:53
	</div>
	
	<h2><a href="javascript:toggle('shortlinks')">Make shortlink</a></h2>
	<div id="shortlinks" style="display:none">
	  <form action="../links/link.php?url=checked" method="post">
	    <b>Comment for short link:</b> <input type="text" name="comment"><input type="submit" value="Make short link">
	  </form>
	</div>

<?php echo(html_footer());?>
