<?
include("../common_functions.php");
include("graphsettings.php");
$db = std_db();
$type = "masstime";

$settings = plot_settings($type); // This will be overridden, we just need to know what type-number to extract from the database


// Get all available measurements
$query = "SELECT distinct time, comment FROM " .  $settings["measurements_table"] . " where type = " . $settings["type"] . " order by time desc, id limit 800";
$result  = mysql_query($query,$db);
while ($row = mysql_fetch_array($result)){
    $datelist[] = $row[0];
    $commentlist[] = $row[1];
}

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

//Create the list of individual components of the chosen measurements
$query = "SELECT id, time, mass_label FROM " .  $settings["measurements_table"] . " where time in (" . $sql_times . ") order by time desc, id limit 800";
echo($query);
$result  = mysql_query($query,$db);
while ($row = mysql_fetch_array($result)){
    $individ_idlist[] = $row[0];
    $individ_datelist[] = $row[1];
    $individ_labellist[] = $row[2];
}


//$plotted_ids is the list of ids that is going to be plotted. This is either the idlist from the url or the latest measurement
$plotted_ids = ($_GET["plotted_ids"] == "") ? array($latest_id) : $_GET["plotted_ids"];

// Produce the id-list for the plot
$html_formatted_idlist = "";
$measurements = "";
foreach($plotted_ids as $curr_id){
    $param["id"] = $curr_id;
    $html_formatted_idlist = $html_formatted_idlist . "&idlist[]=" . $curr_id;
}

// Get metdata for each seperate measurement
// Need to pair up each id to the correct measurement

// Get metdata for each plot
foreach($plotted_ids as $curr_id){   
    $settings = plot_settings($type,$param); 

    $meta_informations[$curr_id]["query"] = $settings["query"];
    $query = "SELECT UNIX_TIMESTAMP(time), timestep, comment, sem_voltage, preamp_range, mass_label, pre_wait_time FROM " .$settings["measurements_table"] . " where id = " . $curr_id;
    $result  = mysql_query($query,$db);  
    $row = mysql_fetch_array($result);
    $timeamp = $row[0];
    $meta_informations[$curr_id]["time"] = $row[0];
    $meta_informations[$curr_id]["timestep"] = $row[1];
    $meta_informations[$curr_id]["comment"] = $row[2];
    $meta_informations[$curr_id]["sem_voltage"] = $row[3];
    $meta_informations[$curr_id]["preamp_range"] = $row[4];
    $meta_informations[$curr_id]["mass_label"] = $row[5];
    $meta_informations[$curr_id]["pre_wait_time"] = $row[6];
    
    $seperate_measurements[$time]["sem"] = $row[3];
}

$ymax = ($_GET["ymax"] == "") ? 0 : $_GET["ymax"];
$ymin = ($_GET["ymin"] == "") ? 0 : $_GET["ymin"];
$xmax = ($_GET["xmax"] == "") ? 0 : $_GET["xmax"];
$xmin = ($_GET["xmin"] == "") ? 0 : $_GET["xmin"];

$manualscale = ($_GET["manualscale"] == "") ? "" : "checked";
$manualxscale = ($_GET["manualxscale"] == "") ? "" : "checked";
$logscale = ($_GET["logscale"] == "") ? "" : "checked";


echo(html_header());
// IMPLEMENT A MANUAL X-SCALE!!!!

// IMPLEMENT LOG-SCALES

// IMPLEMET SELECTOR BOX TO CHOOSE BETWEEN DIFFERENT SIZES OF THE GRAPH

echo("<div style=\"width:305px;float:right\">");
foreach($meta_informations as $id => $meta){
    echo("<div style=\"border-top: 2px solid black; padding-bottom:2px;\">");
    echo("<b>Id:</b> " . $id . "<br>\n");
    echo("<b>Recorded at:</b> " . date("D M j H:i Y",$meta["time"]) . "<br>\n");
    echo("<b>Measuement time</b> " . $meta["timestep"] . "s<br>\n");
    echo("<b>Delay:</b> " . $meta["pre_wait_time"] . "s<br>\n");
    echo("<b>SEM-voltage:</b> " . $meta["sem_voltage"] . "s<br>\n");
    echo("<b>Mass-label:</b> " . $meta["mass_label"] . "<br>\n");
    echo("<b>Comment:</b> " . $meta["comment"] . "<br>\n");
    echo("<b>query:</b> " . $meta["query"] . "<br>\n");
    echo("</div>\n");
}
echo("</div>\n");


echo("<div style=\"width:830px;float:left;padding-bottom:15px;\"><img src=\"plot.php?type=" . $type . $html_formatted_idlist . "&from= " . $xscale["from"] . "&to= " . $xscale["to"] . "&manualscale=" . $manualscale . "&ymax=" . $ymax . "&ymin=" . $ymin . "\"></div>\n\n");

echo("<div style=\"width:830px;float:left;\">\n");
echo("<form action=\"read_massspec.php\" method=\"get\">\n");

print("<p style=\"float:left;padding-right:50px;\"><b>Select measurement:</b><br>\n");
print("<select name=\"chosen_times[]\" multiple size=\"20\">\n");
for($i=0;$i<count($datelist);$i++){
    $selected = (in_array($datelist[$i],$chosen_times)) ? "selected" : "";
    echo("<option value=\"" . $datelist[$i] . "\" " . $selected . ">" . $datelist[$i] . ": " . $commentlist[$i] . "</option>\n");
}
print("</select>\n");
print("</p>\n\n");

print("<p style=\"float:left;padding-right:50px;\"><b>Select individual components:</b><br>\n");
print("<select name=\"plotted_ids[]\" multiple size=\"10\">\n");
for($i=0;$i<count($individ_idlist);$i++){
    $selected = (in_array($individ_idlist[$i],$plotted_ids)) ? "selected" : "";
    echo("<option value=\"" . $individ_idlist[$i] . "\" " . $selected . ">" . $individ_datelist[$i] . ": " . $individ_labellist[$i] . "</option>\n");
}
print("</select>\n");
print("</p>\n\n");


echo("<p style=\"float:left;padding-right:50px;\"><b>Manual Y-scale:</b><input type=\"checkbox\" name=\"manualscale\" value=\"1\" " . $manualscale . "><br>\n");
echo("<b>Max:</b> <input name=\"ymax\" type=\"text\" size=\"7\" value=\"" . $ymax . "\"><br>\n");
echo("<b>Min:&nbsp;</b> <input name=\"ymin\" type=\"text\" size=\"7\" value=\"" . $ymin . "\"></p>\n\n");

echo("<p style=\"float:left;padding-right:50px;\"><b>Manual X-scale:</b><input type=\"checkbox\" name=\"manualxscale\" value=\"1\" " . $manualxscale . "><br>\n");
echo("(not implemented)<br>\n");
echo("<b>Max:</b> <input name=\"xmax\" type=\"text\" size=\"7\" value=\"" . $xmax . "\"><br>\n");
echo("<b>Min:&nbsp;</b> <input name=\"xmin\" type=\"text\" size=\"7\" value=\"" . $xmin . "\"></p>\n\n");

echo("<p style=\"float:left;padding-right:50px;\"><b>Log scale:</b><input type=\"checkbox\" name=\"logscale\" value=\"1\" " . $logscale . "><br>\n");
echo("(not implemented)</p>\n");

echo("<p style=\"float:left;padding-right:50px;\">");
echo("<a href=\"export_data.php?type=" . $type . $html_formatted_idlist . "\">Export data</a><br>");
echo("<a href=\"plot_matplotlib.php?type=" . $type . $html_formatted_idlist . "\">Matplotlib</a><br>");
echo("<input type=\"submit\" value=\"Update\"></p>");
echo("</form>");
echo("</div");

echo("</body>");
echo("</html>");
?>
