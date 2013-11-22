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

// Read dateplot is a rather simple status page used only for graphs with dates on the x-axis.
include("../common_functions.php");
include("graphsettings.php");

date_default_timezone_set('Europe/Copenhagen');

$db = std_db();
$type = $_GET["type"];

//This is a date-plot, we need the date interval
$xscale = date_xscale(no_error_get($_GET, "from"), no_error_get($_GET, "to"));
$settings = plot_settings($type,$xscale);

$ymax = (no_error_get($_GET, "ymax") == "") ? 0 : no_error_get($_GET, "ymax");
$ymin = (no_error_get($_GET, "ymin") == "") ? 0 : no_error_get($_GET, "ymin");

$manualscale = (no_error_get($_GET, "manualscale") == "") ? "" : "checked";
$current_value = single_sql_value($db,$settings["query"] . " desc limit 1",1);
$current_time = single_sql_value($db,$settings["query"] . " desc limit 1",0);

// Get the default image format for export from the graphsettings, default to
// eps
$image_format = no_error_get($settings, "image_format");
if ($image_format == ""){
  $image_format = "eps";
}

?>

<?php echo new_html_header()?>

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

<p class="left"><a href="plot.php?type=<?php echo $type?>&id=<?php echo $param["id"]?>&from=<?php echo $xscale["from"]?>&to=<?php echo $xscale["to"]?>&ymax=<?php echo $ymax?>&ymin=<?php echo $ymin?>&image_format=<?php echo $image_format?>"><img src="plot.php?type=<?php echo $type?>&id=<?php echo $param["id"]?>&from=<?php echo $xscale["from"]?>&to=<?php echo $xscale["to"]?>&ymax=<?php echo $ymax?>&ymin=<?php echo $ymin?>"></a></p>
<form action="read_dateplot.php" method="get">
<input type="hidden" name="type" value="<?php echo $type?>">
<p>
<table>
<COLGROUP>
   <COL>
   <COL>
   <COL>
   <COL>
   <COL width="50%">
   <COL>
<tr>
    <td><b>From:</b></td><td><input name="from" type="text" value="<?php echo $xscale["from"]?>" size="13" class="leftspace"></td>
    <td><b>Y-Min:</b></td><td><input name="ymin" type="text" size="7" value="<?php echo $ymin?>"></td>
  <td></td>
    <td>&nbsp;&nbsp;<input type="submit" value="Update"></td>
</tr>
<tr>
    <td><b>To:</b></td><td><input name="to" type="text" value="<?php echo $xscale["to"]?>" size="13" class="leftspace"></td>
    <td><b>Y-Max:</b></td><td><input name="ymax" type="text" size="7" value="<?php echo $ymax?>"></td>
    <td></td>
    <td></td>
</tr>
</table>
</p>
</form>

<a href="export_date_data.php?type=<?php echo $type?>&from=<?php echo $xscale["from"]?>&to=<?php echo $xscale["to"]?>">Export current data</a>

<br clear="all">

<b>Sql-statement for this graph:</b><br>
<?php echo $settings["query"]?><br>

<b>Latest value:</b><br>
<?php echo $current_value?> @ <?php echo date("Y-m-d H:i:s", $current_time)?>

<?php echo("<div class=\"next\"></div>")?>
<?php echo("<a href=\"javascript:toggle('shortlinks')\"><h2>Make shortlink</h2></a>")?>
<?php echo("<div id=\"shortlinks\" style=\"display:none\">")?>
<?php echo("<form action=\"../links/link.php?url=checked\" method=\"post\">")?>
<?php echo("<b>Comment for short link:</b> <input type=\"text\" name=\"comment\" /><br>")?>
<?php echo("<input type=\"submit\" value=\"Make short link\" />")?>
<?php echo("</form>")?>
<?php echo("</div>")?>


<?php echo new_html_footer()?>
