<?php
include("../common_functions.php");
include("graphsettings.php");
$db = std_db();
echo(new_html_header());
date_default_timezone_set("Europe/Copenhagen");
?>

<?php

echo exec("python flighttime.py --html");

echo("<div class=\"data\"><a href=\"plot.php?type=" . $type . $html_formatted_idlist . "&ymax=" . $ymax . "&ymin=" . $ymin . "&flip_x=" . $flip_x . $shift_be_ke_string . "&logscale=" . $logscale . "&manualxscale=" . $manualxscale . "&xmax=" . $xmax . "&xmin="  . $xmin . $html_formatted_offsetlist . "&image_format=" . $image_format . "\"><img src=\"plot.php?type=" . $type . $html_formatted_idlist . "&ymax=" . $ymax . "&ymin=" . $ymin . "&flip_x=" . $flip_x . $shift_be_ke_string . "&logscale=" . $logscale . "&manualxscale=" . $manualxscale . "&xmax=" . $xmax . "&xmin="  . $xmin . $html_formatted_offsetlist . "\"></a></div>\n\n");
echo("<div class=\"next\"></div>");
echo("<form action=\"read_plot.php\" method=\"get\">\n");
echo("<input type=\"hidden\" name=\"type\" value=\"" . $type . "\">");

echo("<table>");
echo("<tr>");
echo("<td align=\"right\"><b>X-Max:</b></td>");
echo("<td><input name=\"xmax\" type=\"text\" size=\"7\" value=\"" . $xmax . "\"></td>");
echo("<td align=\"right\"><b>Y-Max:</b></td>");
echo("<td><input name=\"ymax\" type=\"text\" size=\"7\" value=\"" . $ymax . "\"></td>");
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
echo("<td align=\"right\"><b>X-Min:</b></td>");
echo("<td><input name=\"xmin\" type=\"text\" size=\"7\" value=\"" . $xmin . "\"></td>");
echo("<td align=\"right\"><b>Y-Min:</b></td>");
echo("<td><input name=\"ymin\" type=\"text\" size=\"7\" value=\"" . $ymin . "\"></td>");
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
echo($offset);

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
