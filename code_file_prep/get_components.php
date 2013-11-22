<?php
  /*
    Copyright (C) 2012 Robert Jensen, Thomas Anderser and Kenneth Nielsen
    
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
$query[] = $settings["query"];


//Get the id-number and timestamp of the newest measurement
$query = "SELECT id, time FROM " . $settings["measurements_table"] . " where type = " . $settings["type"] . " order by time desc limit 1";
$latest_id = single_sql_value($db,$query,0);
$latest_time = single_sql_value($db,$query,1);

//$chosen_times is the list of timestamps that is currently active. This is either the timestamplist from the url or the latest measurement
$chosen_times = ($_GET["chosen_times"] == "") ? array($latest_time) : $_GET["chosen_times"];
$sql_times = "";
foreach($chosen_times as $time){
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

print("<b>Select individual components:</b><br>\n");
print("<select class=\"select\" name=\"plotted_ids[]\" multiple size=\"8\">\n");
for($i=0;$i<count($individ_idlist);$i++){
    echo("<option value=\"" . $individ_idlist[$i] . "\" " . $selected . ">" . $individ_datelist[$i] . ": " . $individ_labellist[$i] . "</option>\n");
    //echo("document.mainform.elements[1].options[" . $i . "] = new Option(\"" . $individ_datelist[$i] . ": " . $individ_labellist[$i] . "\"," . $individ_idlist[$i] . " , false, false);\n");
}
print("</select>\n");


?>
