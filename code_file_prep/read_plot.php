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

date_default_timezone_set('Europe/Copenhagen');

$db = std_db();
$type = $_GET["type"];

$settings = plot_settings($type); // This will be overridden, we just need to know what type-number to extract from the database

// Get the default image format for export from the graphsettings, default to
// eps
$image_format = no_error_get($settings, "image_format");
if ($image_format == ""){
  $image_format = "eps";
}


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
while (no_error_get($settings, "param" . $n ."_field")!=""){
    $specific_settings[$n]["field"] = $settings["param" . $n ."_field"];
    $specific_settings[$n]["name"] = $settings["param" . $n ."_name"];
    $sql_param_list .= "," . $settings["param" . $n ."_field"];// . ",";
    $n++;
}

//Get the id-number of the newest measurement
$query = "SELECT id FROM " . $settings["measurements_table"] . " where type = " . $settings["type"] . " order by time desc limit 1";
$latest_id = single_sql_value($db,$query,0);

//$plotted_ids is the list of ids that is going to be plotted. This is either the idlist from the url or the latest measurement
$plotted_ids = (no_error_get($_GET, "idlist") == "") ? array($latest_id) : no_error_get($_GET, "idlist");

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
    $query = "SELECT x FROM " . $settings["xyvalues_table"] . " where measurement = " . $curr_id . " order by id limit 2";
    $meta_informations[$curr_id]["stepsize"] = get_xy_stepsize($query,$db);

}

$error_reporting_value = error_reporting(0); # Disable error reporting
$ymax = ($_GET["ymax"] == "") ? 0 : $_GET["ymax"];
$ymin = ($_GET["ymin"] == "") ? 0 : $_GET["ymin"];
$xmax = ($_GET["xmax"] == "") ? 0 : $_GET["xmax"];
$xmin = ($_GET["xmin"] == "") ? 0 : $_GET["xmin"];
$logscale = ($_GET["logscale"] == "") ? "" : "checked";
$flip_x = ($_GET["flip_x"] == "") ? "" : "checked";
error_reporting($error_reporting_value); # Re-enable error reporting
if ( $type == "xps"){
  $shift_be_ke = (no_error_get($_GET, "shift_be_ke") == "") ? "" : "checked";
  $shift_be_ke_string = "&shift_be_ke=" . $shift_be_ke;
} else {
  $shift_be_ke_string = "";
}


echo(new_html_header());
?>
<script type="text/javascript">
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

# Before error hunting on 2011-10-18
#echo("<div class=\"data\"><a href=\"plot.php?type=" . $type . $html_formatted_idlist . "&ymax=" . $ymax . "&ymin=" . $ymin . "&flip_x=" . $flip_x . $shift_be_ke_string . "&logscale=" . $logscale . "&manualxscale=" . $manualxscale . "&xmax=" . $xmax . "&xmin="  . $xmin . $html_formatted_offsetlist . "&image_format=" . $image_format . "\"><img src=\"plot.php?type=" . $type . $html_formatted_idlist . "&ymax=" . $ymax . "&ymin=" . $ymin . "&flip_x=" . $flip_x . $shift_be_ke_string . "&logscale=" . $logscale . "&manualxscale=" . $manualxscale . "&xmax=" . $xmax . "&xmin="  . $xmin . $html_formatted_offsetlist . "\"></a></div>\n\n");
echo("<div class=\"data\"><a href=\"plot.php?type=" . $type . $html_formatted_idlist . "&ymax=" . $ymax . "&ymin=" . $ymin . "&flip_x=" . $flip_x . $shift_be_ke_string . "&logscale=" . $logscale . "&xmax=" . $xmax . "&xmin="  . $xmin . $html_formatted_offsetlist . "&image_format=" . $image_format . "\"><img src=\"plot.php?type=" . $type . $html_formatted_idlist . "&ymax=" . $ymax . "&ymin=" . $ymin . "&flip_x=" . $flip_x . $shift_be_ke_string . "&logscale=" . $logscale . "&xmax=" . $xmax . "&xmin="  . $xmin . $html_formatted_offsetlist . "\"></a></div>\n\n");
echo("<div class=\"next\"></div>");
echo("<form action=\"read_plot.php\" method=\"get\">\n");
echo("<input type=\"hidden\" name=\"type\" value=\"" . $type . "\">");

echo("<table>");
echo("<tr>");
echo("<td align=\"right\"><b>X-Min:</b></td>");
echo("<td><input name=\"xmin\" type=\"text\" size=\"7\" value=\"" . $xmin . "\"></td>");
echo("<td align=\"right\"><b>Y-Min:</b></td>");
echo("<td><input name=\"ymin\" type=\"text\" size=\"7\" value=\"" . $ymin . "\"></td>");
echo("<td align=\"right\">&nbsp;&nbsp;&nbsp;<b>Log scale:</b></td>");
echo("<td><input type=\"checkbox\" name=\"logscale\" value=\"1\" " . $logscale . "></td>");
if($type == "xps"){
echo("<td align=\"right\"><b>Shift between KE and BE:</b></td>");
echo("<td><input type=\"checkbox\" name=\"shift_be_ke\" value=\"1\" " . $shift_be_ke . "></td>");
} else {
echo("<td></td>");
echo("<td></td>");
}
echo("<td>&nbsp;&nbsp;&nbsp;<a href=\"export_data.php?type=" . $type . $shift_be_ke_string . $html_formatted_idlist . $html_formatted_offsetlist . "\">Export data</a></td>");
echo("<td>&nbsp;&nbsp;&nbsp;<a href=\"help.php\"><b>Help</b></a></td>");
echo("</tr>");
echo("<tr>");
echo("<td align=\"right\"><b>X-Max:</b></td>");
echo("<td><input name=\"xmax\" type=\"text\" size=\"7\" value=\"" . $xmax . "\"></td>");
echo("<td align=\"right\"><b>Y-Max:</b></td>");
echo("<td><input name=\"ymax\" type=\"text\" size=\"7\" value=\"" . $ymax . "\"></td>");
echo("<td align=\"right\">&nbsp;&nbsp;&nbsp;<b>Flip x:</b></td>");
echo("<td><input type=\"checkbox\" name=\"flip_x\" value=\"1\" " . $flip_x . "></td>");
echo("<td></td>");
echo("<td></td>");
echo("<td>&nbsp;&nbsp;&nbsp;<a href=\"plot_matplotlib.php?type=" . $type . $html_formatted_idlist . "\">Matplotlib</a></td>");
echo("<td>&nbsp;&nbsp;&nbsp;<input type=\"submit\" value=\"Update\"></td>");
echo("</tr>");
echo("</table>");
echo("<hr>");

print("<div class=\"selectcontainer\"><b>Select measurement:</b><br>\n");
print("<select class=\"select\" name=\"idlist[]\" multiple size=\"10\">\n");

for($i=0;$i<count($idlist);$i++){
    $selected = (in_array($idlist[$i],$plotted_ids)) ? "selected" : "";
    echo("<option value=\"" . $idlist[$i] . "\" " . $selected . ">" . $idlist[$i] . ": " . date('Y-m-d H:i',$datelist[$i]) . ": " . $masslabellist[$i] . " " . $commentlist[$i] . "</option>\n");
}
print("</select><br>\n");

echo("</form>");
# Comment out 2011-10-18, as far as I can tell does nothing
#echo($offset);

print("</div>\n\n");

echo("<div class=\"next\"></div>");

//echo("</div>");

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
echo("<a href=\"javascript:toggle('gasanalysis')\"><h2>Show gas analysis</h2></a>");
echo("<div id=\"gasanalysis\" style=\"display:none\">");
echo("<div class=\"gasanalysis\">");
    if($type == "massspectrum"){
       echo("<pre>");
        echo(shell_exec("./gas_analysis.py " . $param["id"]));
    	echo("</pre>");
    }
echo("</div>");
echo("</div>");

echo("<div class=\"next\"></div>");
echo("<a href=\"javascript:toggle('flighttime')\"><h2>Show flight time estimate</h2></a>");
echo("<div id=\"flighttime\" style=\"display:none\">");
echo("<div class=\"flighttime\">");
    if($type == "tofspectrum"){
       echo("<pre>");
        echo(shell_exec("./flighttime.py " . $param["id"]));
    	echo("</pre>");
    }
echo("</div>");
echo("</div>");

echo("<div class=\"next\"></div>");
echo("<a href=\"javascript:toggle('shortlinks')\"><h2>Make shortlink</h2></a>");
echo("<div id=\"shortlinks\" style=\"display:none\">");
echo("<form action=\"../links/link.php?url=checked\" method=\"post\">");
echo("<b>Comment for short link:</b> <input type=\"text\" name=\"comment\" /><br>");
echo("<input type=\"submit\" value=\"Make short link\" />");
echo("</form>");
echo("</div>");
//echo("</div>");

echo(new_html_footer());
?>
