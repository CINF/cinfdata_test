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
//echo($query);
$result  = mysql_query($query,$db);
while ($row = mysql_fetch_array($result)){
    $individ_idlist[] = $row[0];
    $individ_datelist[] = $row[1];
    $individ_labellist[] = $row[2];
}


//$plotted_ids is the list of ids that is going to be plotted. This is either the idlist from the url or the latest measurement
$plotted_ids = ($_GET["plotted_ids"] == "") ? array($latest_id) : $_GET["plotted_ids"];

// Get metdata for each seperate measurement
$min_offset = 0;
foreach($plotted_ids as $curr_id){   
    $param["id"] = $curr_id;
    $settings = plot_settings($type,$param); 

   if($settings["offset_query"] == ""){
        $offsetvalue[$curr_id] = 0;
    }
    else{
        $offsetvalue = single_sql_value($db,$settings["offset_query"],0);
        $min_offset = ($offsetvalue<$min_offset) ? $offsetvalue : $min_offset;
    }

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
}

if($min_offset<0){
    $min_offset = ($min_offset * -1.05) + 1e-13;
}

// Produce the id-list for the plot
$html_formatted_idlist = "";
$html_formatted_labellist = "";
$html_formatted_offsetlist = "";
for($i=0;$i<count($plotted_ids);$i++){
    $html_formatted_idlist = $html_formatted_idlist . "&idlist[]=" . $plotted_ids[$i];
    $html_formatted_labellist = $html_formatted_labellist . "&labellist[" . $plotted_ids[$i] . "]=" . $meta_informations[$plotted_ids[$i]]["mass_label"];
    $html_formatted_offsetlist .= "&offsetlist[" . $plotted_ids[$i] . "]=" . $min_offset;
}


$ymax = ($_GET["ymax"] == "") ? 0 : $_GET["ymax"];
$ymin = ($_GET["ymin"] == "") ? 0 : $_GET["ymin"];
$xmax = ($_GET["xmax"] == "") ? 0 : $_GET["xmax"];
$xmin = ($_GET["xmin"] == "") ? 0 : $_GET["xmin"];

$logscale = ($_GET["logscale"] == "") ? "" : "checked";


echo(new_html_header());

echo("<div class=\"data\"><img src=\"plot.php?type=" . $type . $html_formatted_idlist . "&ymax=" . $ymax . "&ymin=" . $ymin . "&logscale=" . $logscale . "&xmax=" . $xmax . "&xmin="  . $xmin . $html_formatted_labellist . $html_formatted_offsetlist . "\"></div>\n\n");

echo("<form action=\"read_massspec.php\" method=\"get\">\n");
print("<div class=\"selectcontainer\"><b>Select measurement:</b><br>\n");
print("<select class=\"select\" name=\"chosen_times[]\" multiple size=\"8\">\n");
for($i=0;$i<count($datelist);$i++){
    $selected = (in_array($datelist[$i],$chosen_times)) ? "selected" : "";
    echo("<option value=\"" . $datelist[$i] . "\" " . $selected . ">" . $datelist[$i] . ": " . $commentlist[$i] . "</option>\n");
}
print("</select>\n");
print("</div>\n\n");
print("<div class=\"selectcontainer\">\n");
print("<b>Select individual components:</b><br>\n");
print("<select class=\"select\" name=\"plotted_ids[]\" multiple size=\"8\">\n");
for($i=0;$i<count($individ_idlist);$i++){
    $selected = (in_array($individ_idlist[$i],$plotted_ids)) ? "selected" : "";
    echo("<option value=\"" . $individ_idlist[$i] . "\" " . $selected . ">" . $individ_datelist[$i] . ": " . $individ_labellist[$i] . "</option>\n");
}
print("</select>\n");
print("</div>\n\n");

echo("<div class=\"next\"></div>\n");

echo("<div class=\"scaleboxes\">");
echo("<b>Y-Min:&nbsp;</b> <input name=\"ymin\" type=\"text\" size=\"7\" value=\"" . $ymin . "\">\n");
echo("<b>Y-Max:</b> <input name=\"ymax\" type=\"text\" size=\"7\" value=\"" . $ymax . "\"><br>\n\n");

echo("<b>X-Min:&nbsp;</b> <input name=\"xmin\" type=\"text\" size=\"7\" value=\"" . $xmin . "\">\n");
echo("<b>X-Max:</b> <input name=\"xmax\" type=\"text\" size=\"7\" value=\"" . $xmax . "\"><br>\n\n");

echo("<b>Log scale:</b><input type=\"checkbox\" name=\"logscale\" value=\"1\" " . $logscale . "><br>\n");

echo("<a href=\"export_data.php?type=" . $type . $html_formatted_idlist . "\">Export data</a></br>");
echo("<a href=\"plot_matplotlib.php?type=" . $type . $html_formatted_idlist . $html_formatted_labellist . "\">Matplotlib</a></br>");
echo("<input type=\"submit\" value=\"Update\">");
echo("</form>");
echo("</div>\n");

echo("<div class=\"next\"></div>");

//echo("<div style=\"width:305px;float:right\">");
foreach($meta_informations as $id => $meta){
    echo("<div class=\"infobox\">");
    echo("<b>Id:</b> " . $id . "<br>\n");
    echo("<b>Recorded at:</b> " . date("D M j H:i Y",$meta["time"]) . "<br>\n");
    echo("<b>Measuement time</b> " . $meta["timestep"] . "s<br>\n");
    echo("<b>Delay:</b> " . $meta["pre_wait_time"] . "s<br>\n");
    echo("<b>SEM-voltage:</b> " . $meta["sem_voltage"] . "s<br>\n");
    echo("<b>Mass-label:</b> " . $meta["mass_label"] . "<br>\n");
    echo("<b>Comment:</b> " . $meta["comment"] . "<br>\n");
    echo("<b>query:</b> " . $meta["query"] . "<br>\n");
    echo("<b>offset:</b>" . science_number($min_offset) . "<br>\n");
    echo("</div>\n");
}
//echo("</div>\n");

//echo("<div class=\"next\"></div>");
//echo("MySQL query: " . $query);

echo(new_html_footer());
?>
