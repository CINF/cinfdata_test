<?php
include("../common_functions.php");
include("graphsettings.php");

date_default_timezone_set('Europe/Copenhagen');

$db = std_db();
$type = $_GET["type"];

/**
 * Simple function to replicate PHP 5 behaviour
 */
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

$settings = plot_settings($type); // This will be overridden, we just need to know what type-number to extract from the database

// Get the default image format for export from the graphsettings, default to
// eps

$image_format = no_error_get($settings, "image_format");
if ($image_format == ""){
  $image_format = "eps";
}

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
#$error_reporting_value = error_reporting(0); # Disable error reporting
$chosen_times = (no_error_get($_GET, "chosen_times") == "") ? array($latest_time) : $_GET["chosen_times"];
#error_reporting($error_reporting_value); # Re-enable error reporting
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
$plotted_ids = (no_error_get($_GET, "plotted_ids") == "") ? array($latest_id) : $_GET["plotted_ids"];
//$x_transform =  ($_GET["x_transform"] == "") ? "time" : $_GET["x_transform"];

// Get metadata for each seperate measurement

// This block of code will check which settings from the measurements table that should
// be used for this particular type of measurement
$n=0;
$specific_settings="";
$sql_param_list="";
while (no_error_get($settings, "param" . $n ."_field")!=""){
    $specific_settings[$n]["field"] = $settings["param" . $n ."_field"];
    $specific_settings[$n]["name"] = $settings["param" . $n ."_name"];
    $sql_param_list .= "," . $settings["param" . $n ."_field"];// . ",";
    $n++;
}

$min_offset = 0;
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
    if(no_error_get($settings, "offset_query") == ""){
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
//  $query = "SELECT x FROM " . $settings["xyvalues_table"] . " where measurement = " . $curr_id . " order by id limit 2"; Performance issues!
    $query = "SELECT x FROM " . $settings["xyvalues_table"] . " where measurement = " . $curr_id . " limit 2";
    $meta_informations[$curr_id]["stepsize"] = get_xy_stepsize($query,$db);
}

if($min_offset<0){
    $min_offset = ($min_offset * -1.05) + 1e-13;
}

# Disable error from asking for value in the associative array that does not exist
$error_reporting_value = error_reporting(0);

$ymax = ($_GET["ymax"] == "") ? 0 : $_GET["ymax"];
$ymin = ($_GET["ymin"] == "") ? 0 : $_GET["ymin"];
$xmax = ($_GET["xmax"] == "") ? 0 : $_GET["xmax"];
$xmin = ($_GET["xmin"] == "") ? 0 : $_GET["xmin"];

$logscale = ($_GET["logscale"] == "") ? "" : "checked";
$as_function_of_t = ($_GET["as_function_of_t"] == "") ? "" : "checked";
$shift_temp_unit = ($_GET["shift_temp_unit"] == "") ? "" : "checked";
$flip_x = ($_GET["flip_x"] == "") ? "" : "checked";

# Re-enable error reporting
error_reporting($error_reporting_value);

echo(new_html_header());
?>

<script type="text/javascript">
function showData(str){
    var options = document.forms[0].elements[10].options;
    var i;
    var newstr = "";
    for (i=0;i<options.length;i++){
        if (document.forms[0].elements[10].options[i].selected){
            newstr = newstr +  "&chosen_times[]=" + document.forms[0].elements[10].options[i].value;
        }
    }
    //alert(document.forms[0].elements[9].options[2].selected);
    //alert(document.forms[0].elements[9].options[2].value);
    if (str==""){
        document.getElementById("measurements").innerHTML=""; //don't output if you don't have anything to say
        return;
    }
 
    if (window.XMLHttpRequest){
        xmlhttp=new XMLHttpRequest(); //Create object we will check upon
    }

    xmlhttp.onreadystatechange=function(){
        if (xmlhttp.readyState==4 && xmlhttp.status==200){ // readyState indicates all data have been received and status is 'OK' = go ahead!
            document.getElementById("measurements").innerHTML=xmlhttp.responseText; //Where should we put the answer? Let's go for output!
        }
    }
    //xmlhttp.open("GET","get_components.php?type=masstime&chosen_times[]="+str,true); //retrieve data from MySQL using PHP
    xmlhttp.open("GET","get_components.php?type=masstime"+newstr,true); //retrieve data from MySQL using PHP
    xmlhttp.send(); // Show it to me!
}

function toggle(list){ 
  var listElementStyle=document.getElementById(list).style;
    if (listElementStyle.display=="none"){ 
        listElementStyle.display="block"; 
    }
    else{listElementStyle.display="none"; 
    } 
}
</script>
<?php

echo("<div class=\"data\"><a href=\"plot.php?type=" . $type . $html_formatted_idlist . "&ymax=" . $ymax . "&ymin=" . $ymin . "&flip_x=" . $flip_x . "&logscale=" . $logscale . "&xmax=" . $xmax . "&xmin="  . $xmin . $html_formatted_offsetlist . "&as_function_of_t=" . $as_function_of_t . "&shift_temp_unit=" . $shift_temp_unit . "&image_format=" . $image_format . "\"><img src=\"plot.php?type=" . $type . $html_formatted_idlist . "&ymax=" . $ymax . "&ymin=" . $ymin . "&flip_x=" . $flip_x . "&logscale=" . $logscale . "&xmax=" . $xmax . "&xmin="  . $xmin . $html_formatted_offsetlist . "&as_function_of_t=" . $as_function_of_t . "&shift_temp_unit=" . $shift_temp_unit . "\" ></a></div>\n\n");

echo("<div class=\"next\"></div>\n");
echo("<form action=\"read_plot_group.php\" method=\"get\">\n");

echo("<input type=\"hidden\" name=\"type\" value=\"" . $type . "\">\n");

# SELECTIONS START
echo("<div class=\"scaleboxes\">");
echo("<table>\n");
# First row
echo(" <tr>\n");
echo("  <td align=\"right\"><b>X-Min:</b></td>\n");
echo("  <td><input name=\"xmin\" type=\"text\" size=\"7\" value=\"" . $xmin . "\"></td>\n");
echo("  <td align=\"right\"><b>Y-Min:</b></td>\n");
echo("  <td><input name=\"ymin\" type=\"text\" size=\"7\" value=\"" . $ymin . "\"></td>\n");
echo("  <td align=\"right\"><b>As function of temperature:</b></td>\n");
echo("  <td><input type=\"checkbox\" name=\"as_function_of_t\" value=\"1\" " . $as_function_of_t . "></td>\n");
echo("  <td align=\"right\"><b>Log scale:</b></td>\n");
echo("  <td><input type=\"checkbox\" name=\"logscale\" value=\"1\" " . $logscale . ">&nbsp;&nbsp;</td>\n");
echo("  <td><a href=\"export_data.php?type=" . $type . $html_formatted_idlist . "&as_function_of_t=" . $as_function_of_t . "&shift_temp_unit=" . $shift_temp_unit . "\">Export data</a></td>\n");
echo("  <td></td>\n");

echo(" </tr>\n");
echo(" <tr>\n");

echo("  <td align=\"right\"><b>X-Max:</b></td>\n");
echo("  <td><input name=\"xmax\" type=\"text\" size=\"7\" value=\"" . $xmax . "\"></td>\n");
echo("  <td align=\"right\"><b>Y-Max:</b></td>\n");
echo("  <td><input name=\"ymax\" type=\"text\" size=\"7\" value=\"" . $ymax . "\"></td>\n");
echo("  <td align=\"right\"><b>Shift temperature unit:</b></td>\n");
echo("  <td><input type=\"checkbox\" name=\"shift_temp_unit\" value=\"1\" " . $shift_temp_unit . "></td>\n");
echo("  <td align=\"right\"><b>Flip x-axis:</b></td>\n");
echo("  <td><input type=\"checkbox\" name=\"flip_x\" value=\"1\" " . $flip_x . "></td>\n");
echo("  <td><a href=\"plot_matplotlib.php?type=" . $type . $html_formatted_idlist . "\">Matplotlib</a></td>\n");
echo("  <td>&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"submit\" value=\"Update\"></td>\n");
echo(" </tr>\n");
echo("</table>\n");
echo("</div>\n");

echo("<div class=\"next\"></div>\n");
echo("<hr>");

print("<div class=\"selectcontainer\"><b>Select measurement:</b><br>\n");
print("<select class=\"select\" name=\"chosen_times[]\" multiple size=\"8\" onChange=\"showData(this.value)\">\n");
for($i=0;$i<count($datelist);$i++){
    $selected = (in_array($datelist[$i],$chosen_times)) ? "selected" : "";
    echo("<option value=\"" . $datelist[$i] . "\" " . $selected . ">" . $datelist[$i] . ": " . $commentlist[$i] . "</option>\n");
}
print("</select>\n");
print("</div>\n\n");
print("<div class=\"selectcontainer\" id=\"measurements\">\n");
print("<b>Select individual components:</b><br>\n");
print("<select class=\"select\" name=\"plotted_ids[]\" multiple size=\"8\">\n");
for($i=0;$i<count($individ_idlist);$i++){
    $selected = (in_array($individ_idlist[$i],$plotted_ids)) ? "selected" : "";
    echo("<option value=\"" . $individ_idlist[$i] . "\" " . $selected . ">" . $individ_datelist[$i] . ": " . $individ_labellist[$i] . "</option>\n");
}
print("</select>\n");
print("</div>\n\n");

/*print("<div class=\"selectcontainer\">\n");
print("<b>Select x-axis:</b><br>\n");
print("<select class=\"select\" name=\"x_transform\" size=\"8\">\n");
echo("<option value=\"time\">Time</option>\n");
for($i=0;$i<count($individ_idlist);$i++){
    $selected = ($x_transform==$individ_idlist[$i]) ? "selected" : "";
    echo("<option value=\"" . $individ_idlist[$i] . "\" " . $selected . ">" . $individ_datelist[$i] . ": " . $individ_labellist[$i] . "</option>\n");
}
print("</select>\n");
print("</div>\n\n");*/

echo("</form>\n\n");

echo("<div class=\"next\"></div>");

//echo("<div style=\"width:305px;float:right\">");
echo("<div class=\"next\"></div>");
echo("<a href=\"javascript:toggle('infobox')\"><h2>Show infobox(es)</h2></a>");
echo("<div id=\"infobox\" style=\"display:none\">");
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
echo("</div>");

echo("<div class=\"next\"></div>");
echo("<a href=\"javascript:toggle('shortlinks')\"><h2>Make shortlink</h2></a>");
echo("<div id=\"shortlinks\" style=\"display:none\">");
 echo("<form action=\"../links/link.php?url=checked\" method=\"post\">");
  echo("<b>Comment for short link:</b> <input type=\"text\" name=\"comment\" /><br>");
 echo("<input type=\"submit\" value=\"Make short link\" />");
 echo("</form>");
echo("</div>");

//echo("MySQL query: " . $query);

echo(new_html_footer());
echo("<div class=\"next\"></div>");
?>
