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

include("../common_functions.php");
include("graphsettings.php");
$db = std_db();

$type = $_GET["type"];
$settings = plot_settings($type); // This will be overridden, we just need to know what type of x-scale is in this plot
//$query[] = $settings["query1"];
//print_r($query[]);
// Get the id-number and timestamp of the newest measurement
$query = "SELECT id, " . $settings["grouping_column"] . " FROM " . $settings["measurements_table"] . " where type = " . $settings["type"] . " order by time desc limit 1";
$latest_id = single_sql_value($db,$query,0);
$latest_time = single_sql_value($db,$query,1);

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


//Get the id-number and timestamp of the newest measurement
$query = "SELECT id, time FROM " . $settings["measurements_table"] . " where type = " . $settings["type"] . " order by time desc limit 1";
$latest_id = single_sql_value($db,$query,0);
$latest_time = single_sql_value($db,$query,1);

// $chosen_group is the list of timestamps that is currently active. This is either the timestamplist from the url or the latest measurement
$sql_times = "";
foreach($chosen_group as $time){
    $sql_times = $sql_times . "\"" . $time . "\",";
}
$sql_times  = substr($sql_times, 0, -1);  // Remove the trailing comma

//print($sql_times);

$query = "SELECT id, time, mass_label FROM " .  $settings["measurements_table"] . " where time in (" . $sql_times . ") order by time desc, id limit 800";
$result  = mysql_query($query,$db);
while ($row = mysql_fetch_array($result)){
    $individ_idlist[] = $row[0];
    $individ_datelist[] = $row[1];
    $individ_labellist[] = $row[2];
}

print("<div id=\"left_y\">\n");
print("<!--LEFT Y-->\n");
print("<b>Log-scale</b><input type=\"checkbox\" name=\"left_logscale\" value=\"checked\"" . $left_logscale . "><br>\n");
print("<b>Y-Min:</b><input name=\"left_ymin\" type=\"text\" size=\"7\" value=" . $left_ymin . "><br>\n");
print("<b>Y-Max:</b><input name=\"left_ymax\" type=\"text\" size=\"7\" value=" . $left_ymax . "><br>\n");
print("<b>Select component (left y-axis):</b><br>\n");
print("<select multiple size=\"8\" name=\"left_plotlist[]\">\n");
             // Creation of plotlist for left axis
		     for($i=0;$i<count($individ_idlist);$i++){
				$selected = (in_array($individ_idlist[$i],$left_plotlist)) ? "selected" : "";
                echo("<option value=\"" . $individ_idlist[$i] . "\" " . $selected . ">" . $individ_datelist[$i] . ": " . $individ_labellist[$i] . "</option>\n");
             }
print("</select>\n");
print("</div>\n");

print("<div id=\"right_y\">\n");
print("<!--RIGHT Y-->\n");
print("<b>Log-scale</b><input type=\"checkbox\" name=\"right_logscale\" value=\"checked\"" . $right_logscale . "><br>\n");
print("<b>Y-Min:</b><input name=\"right_ymin\" type=\"text\" size=\"7\" value=" . $right_ymin . "><br>\n");
print("<b>Y-Max:</b><input name=\"right_ymax\" type=\"text\" size=\"7\" value=" . $right_ymax . "><br>\n");
print("<b>Select component (right y-axis):</b><br>\n");
print("<select multiple size=\"8\" name=\"right_plotlist[]\">\n");
             // Creation of plotlist for right axis
		     for($i=0;$i<count($individ_idlist);$i++){
                $selected = (in_array($individ_idlist[$i],$right_plotlist)) ? "selected" : "";
				echo("<option value=\"" . $individ_idlist[$i] . "\" " . $selected . ">" . $individ_datelist[$i] . ": " . $individ_labellist[$i] . "</option>\n");
             }

print("</select>\n");
print("</div>\n");
