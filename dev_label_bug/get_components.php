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
date_default_timezone_set("Europe/Copenhagen");
include("graphsettings.php");
$dbi = std_dbi();

// Get settings
$type = $_GET["type"];
$settings = plot_settings($type);

// Get the id-number and timestamp of the newest measurement
$query = "SELECT id, " . $settings["grouping_column"] . " FROM " . $settings["measurements_table"] . " where type = " . $settings["type"] . " order by time desc limit 1";
$row = $dbi->query($query)->fetch_array();
list($latest_id, $latest_group) = $row;

// Remember settings after submitting and initialize values
$chosen_group      = isset($_GET["chosen_group"])       ? $_GET["chosen_group"]        : array($latest_group);
$left_plotlist     = isset($_GET["left_plotlist"])      ? $_GET["left_plotlist"]       : array($latest_id);
$right_plotlist    = isset($_GET["right_plotlist"])     ? $_GET["right_plotlist"]      : array();
$left_ylabel       = isset($_GET["left_ylabel"])        ? weed($_GET["left_ylabel"])   : "";

// $chosen_group is the list of timestamps that is currently active. This is either the timestamplist from the url or the latest measurement
$sql_groups = "";
foreach($chosen_group as $group){
    $sql_groups .= "\"" . $group . "\",";
}
$sql_groups = trim($sql_groups, ",");  // Remove the trailing comma

// Create the list of individual components of the chosen measurements
$query = "SELECT id, time," . $settings["label_column"] .  " FROM " .  $settings["measurements_table"] . " where " . $settings["grouping_column"] . " in (" . $sql_groups . ") order by time desc, id limit 800";
$result  = $dbi->query($query);
while ($row = $result->fetch_array()) {
  # Unpack $row and append its elements to the id, date and label lists
  list($individ_idlist[], $individ_datelist[], $individ_labellist[]) = $row;
}

// Make the left_y component list
print("<div id=\"left_y\">\n");
print("<!--LEFT Y COMPONENTS-->\n");
print("<b>Select component (left y-axis):</b><br>\n");
print("<select multiple size=\"8\" name=\"left_plotlist[]\">\n");
// Creation of plotlist for left axis
for($i=0; $i < count($individ_idlist); $i++){
  $selected = (in_array($individ_idlist[$i],$left_plotlist)) ? "selected" : "";
  echo("<option value=\"" . $individ_idlist[$i] . "\" " . $selected . ">" . $individ_datelist[$i] . ": " . $individ_labellist[$i] . "</option>\n");
}
print("</select>\n");
print("</div>\n");

// Make the right_y component list
print("<div id=\"right_y\">\n");
print("<!--RIGHT Y COMPONENTS-->\n");
print("<b>Select component (right y-axis):</b><br>\n");
print("<select multiple size=\"8\" name=\"right_plotlist[]\">\n");
// Creation of plotlist for right axis
for($i=0; $i < count($individ_idlist); $i++){
  $selected = (in_array($individ_idlist[$i],$right_plotlist)) ? "selected" : "";
  echo("<option value=\"" . $individ_idlist[$i] . "\" " . $selected . ">" . $individ_datelist[$i] . ": " . $individ_labellist[$i] . "</option>\n");
}
print("</select>\n");
print("</div>\n");
