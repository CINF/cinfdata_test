<?
include("../common_functions.php");
include("graphsettings.php");
$db = std_db();
$type = $_GET["type"];

$settings = plot_settings($type); // This will be overridden, we just need to know what type-number to extract from the database

// Get a list of id's that satisfies a search criterion
// These will be shown in a selection list
$query = "SELECT id, unix_timestamp(time), comment, mass_label FROM " .  $settings["measurements_table"] . " where type = " . $settings["type"] . " order by time desc, id limit 800";
$result  = mysql_query($query,$db);  
while ($row = mysql_fetch_array($result)){
    $idlist[] = $row[0];
    $datelist[] = $row[1];
    $commentlist[] = $row[2];
    $masslabellist[] = $row[3];
}


// This block of code will check which settings from the measurements table that should
// be used for this particular type of measurement
$n=0;
$specific_settings="";
$sql_param_list="";
while ($settings["param" . $n ."_field"]!=""){
    $specific_settings[$n]["field"] = $settings["param" . $n ."_field"];
    $specific_settings[$n]["name"] = $settings["param" . $n ."_name"];
    $sql_param_list .= "," . $settings["param" . $n ."_field"];// . ",";
    $n++;
}

//Get the id-number of the newest measurement
$query = "SELECT id FROM " . $settings["measurements_table"] . " where type = " . $settings["type"] . " order by time desc limit 1";
$latest_id = single_sql_value($db,$query,0);

//$plotted_ids is the list of ids that is going to be plotted. This is either the idlist from the url or the latest measurement
$plotted_ids = ($_GET["idlist"] == "") ? array($latest_id) : $_GET["idlist"];

// Get metdata for each plot and produce the id-list for the plot
$html_formatted_idlist = "";
$html_formatted_offsetlist = "";
foreach($plotted_ids as $curr_id){
    $param["id"] = $curr_id;
    $html_formatted_idlist = $html_formatted_idlist . "&idlist[]=" . $curr_id;
    
    $settings = plot_settings($type,$param); 

    $meta_informations[$curr_id]["query"] = $settings["query"];
    $query = "SELECT UNIX_TIMESTAMP(time), timestep, comment " . $sql_param_list . " FROM " .$settings["measurements_table"] . " where id = " . $curr_id;
    $result  = mysql_query($query,$db);  
    $row = mysql_fetch_array($result);
    $meta_informations[$curr_id]["time"] = $row[0];
    $meta_informations[$curr_id]["timestep"] = $row[1];
    $meta_informations[$curr_id]["comment"] = $row[2];
    for ($n=0;$n<count($specific_settings);$n++){
        $meta_informations[$curr_id]["specific_" . $n] = $row[3+$n];
    }
    if($settings["offset_query"] == ""){
        $offsetvalue = 0;
    }
    else {
        $offsetvalue = single_sql_value($db,$settings["offset_query"],0);
        if($offsetvalue < 0){
            $offsetvalue = $offsetvalue*(-1.1);
        }
        else {
            $offsetvalue = 0;
        }
    }
    $meta_informations[$curr_id]["offset"] = $offsetvalue;
    $html_formatted_offsetlist = $html_formatted_offsetlist . "&offsetlist[$curr_id]=" . $offsetvalue;  
    $query = "SELECT x FROM " . $settings["xyvalues_table"] . " where measurement = " . $curr_id . " order by id limit 2";
    $meta_informations[$curr_id]["stepsize"] = get_xy_stepsize($query,$db);

}

$ymax = ($_GET["ymax"] == "") ? 0 : $_GET["ymax"];
$ymin = ($_GET["ymin"] == "") ? 0 : $_GET["ymin"];
$xmax = ($_GET["xmax"] == "") ? 0 : $_GET["xmax"];
$xmin = ($_GET["xmin"] == "") ? 0 : $_GET["xmin"];

$logscale = ($_GET["logscale"] == "") ? "" : "checked";

echo(new_html_header());

echo("<div class=\"data\"><img src=\"plot.php?type=" . $type . $html_formatted_idlist . "&ymax=" . $ymax . "&ymin=" . $ymin . "&logscale=" . $logscale . "&manualxscale=" . $manualxscale . "&xmax=" . $xmax . "&xmin="  . $xmin . $html_formatted_offsetlist . "\"></div>\n\n");
echo("<div class=\"next\"></div>");
echo("<form action=\"read_plot.php\" method=\"get\">\n");
echo("<input type=\"hidden\" name=\"type\" value=\"" . $type . "\">");
print("<div class=\"selectcontainer\"><b>Select measurement:</b><br>\n");
print("<select class=\"select\" name=\"idlist[]\" multiple size=\"10\">\n");
for($i=0;$i<count($idlist);$i++){
    $selected = (in_array($idlist[$i],$plotted_ids)) ? "selected" : "";
    echo("<option value=\"" . $idlist[$i] . "\" " . $selected . ">" . $idlist[$i] . ": " . date('Y-m-d H:i',$datelist[$i]) . ": " . $masslabellist[$i] . " " . $commentlist[$i] . "</option>\n");
}
print("</select><br>\n");

echo("<b>Y-Min:</b> <input name=\"ymin\" type=\"text\" size=\"7\" value=\"" . $ymin . "\">\n");
echo("<b>Y-Max:</b> <input name=\"ymax\" type=\"text\" size=\"7\" value=\"" . $ymax . "\"><br>\n\n");
echo("<b>X-Min:</b> <input name=\"xmin\" type=\"text\" size=\"7\" value=\"" . $xmin . "\">\n");
echo("<b>X-Max:</b> <input name=\"xmax\" type=\"text\" size=\"7\" value=\"" . $xmax . "\"><br>\n\n");

echo("<b>Log scale:</b><input type=\"checkbox\" name=\"logscale\" value=\"1\" " . $logscale . "><br>\n");

echo("<a href=\"export_data.php?type=" . $type . $html_formatted_idlist . "\">Export data</a></br>");
echo("<a href=\"plot_matplotlib.php?type=" . $type . $html_formatted_idlist . "\">Matplotlib</a></br>");
echo("<input type=\"submit\" value=\"Update\">");
echo("</form>");
echo($offset);

print("</div>\n\n");

echo("<div class=\"next\"></div>");

//echo("</div>");

echo("<div class=\"next\"></div>");

foreach($meta_informations as $id => $meta){
    echo("<div class=\"infobox\">");
    echo("<b>Id:</b> " . $id . "<br>\n");
    echo("<b>Recorded at:</b> " . date("D M j H:i Y",$meta["time"]) . "<br>\n");
    echo("<b>Step size:</b> " . $meta["stepsize"] . "<br>\n");
    echo("<b>Time pr. step:</b> " . $meta["timestep"] . "s<br>\n");
    if($meta["stepsize"]!=0){
        echo("<b>Time pr. x-unit:</b> " . $meta["timestep"]/$meta["stepsize"] . "s<br>\n");
    }
    echo("<b>Comment:</b> " . $meta["comment"] . "<br>\n");
    for ($n=0;$n<count($specific_settings);$n++){
        echo("<b>" . $specific_settings[$n]["name"] . ":</b> " . $meta["specific_" .$n] . "<br>\n");
    }
    echo("<b>Offset:</b> " .science_number($meta["offset"]) . "<br>\n");

    echo("<b>query:</b> " . $meta["query"] . "<br>\n");
echo("</div>\n");
}

echo("<div class=\"next\"></div>");
echo("<div class=\"gasanalysis\">");
    if($type == "massspectrum"){
       echo("<pre>");
        echo(shell_exec("python /var/www/microreactor/gas_analysis.py " . $param["id"]));
    	echo("</pre>");
    }
echo("</div>");

echo(new_html_footer());
?>
