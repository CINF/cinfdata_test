<?php
include("../common_functions.php");
include("graphsettings.php");
$db = std_db();

$type = $_GET["type"];
$settings = plot_settings($type);
$query[] = $settings["queries"]["default"];

// Get the id-number and timestamp of the newest measurement
$query = "SELECT id, time FROM " . $settings["measurements_table"] . " where type = " . $settings["type"] . " order by time desc limit 1";
$latest_id = single_sql_value($db,$query,0);
$latest_time = single_sql_value($db,$query,1);

// $chosen_group is the list of timestamps that is currently active. This is either the timestamplist from the url or the latest measurement
$chosen_group = ($_GET["chosen_group"] == "") ? array($latest_time) : $_GET["chosen_group"];
$sql_times = "";
foreach($chosen_group as $time){
    $sql_times = $sql_times . "\"" . $time . "\",";
}
// Remove the trailing comma
$sql_times  = substr($sql_times, 0, -1);

$query = "SELECT id, time, mass_label FROM " .  $settings["measurements_table"] . " where time in (" . $sql_times . ") order by time desc, id limit 800";
$result  = mysql_query($query,$db);
while ($row = mysql_fetch_array($result)){
    $individ_idlist[] = $row[0];
    $individ_datelist[] = $row[1];
    $individ_labellist[] = $row[2];
}

// If $individ_labellist is empty we will assign "data" to populate the individual id list
if ($individ_labellist[0] == ""){
   $individ_labellist[0] = "data";
}ci

echo("<select name=\"left_plotlist_ids[]\" multiple size=\"8\">\n");
for($i=0;$i<count($individ_idlist);$i++){
    echo("<option value=\"" . $individ_idlist[$i] . "\" " . $selected . ">" . $individ_datelist[$i] . ": " . $individ_labellist[$i] . "</option>\n");
}
print("</select>\n");


?>
