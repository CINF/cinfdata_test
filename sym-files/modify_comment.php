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

// Read dateplot is a rather simple status page used only for graphs with dates on the x-axis.
include("../common_functions_v2.php");
include("graphsettings.php");
$type = "";
$settings = plot_settings($type);
$db = std_db($settings["sql_username"]);
?>

<?php echo html_header()?>

<?php
if (!empty($_GET["time"])){
    $timestamp = $_GET["time"];
    $comment = $_GET["comment"];
    $query = "select id from " . $settings["measurements_table"] . " where time = \"" . $timestamp . "\"";
    $result  = mysql_query($query,$db);  
    $i = 0;
    while ($row = mysql_fetch_array($result)){
        $query = "update ". $settings["measurements_table"] . " set comment = \"" . $comment . "\", time = time where id = " . $row[0];
        mysql_query($query,$db);  
        $valid = $i++;
    }
    if ($i==0){
        echo("<b>Not a valid timestamp</b>");
    }
    else{
        echo("<b>Updated " . $i . " measurement rows</b>");
    }
}
?>


<form action="modify_comment.php" method="get">
Select timestamp to modify<br>
<input name="time" type="text" size="13"><br>
New comment:<br>
<input name="comment" type="text" size="100"><br>
<input type="submit" value="Engage"><br>

<?php
$query = "select distinct time, comment from " . $settings["measurements_table"] . " order by time desc";
print $query;
$result  = mysql_query($query,$db);  
while ($row = mysql_fetch_array($result)){
    print($row[0] . " - " . $row[1] .  "<br>");
}
?>



<?php echo html_footer()?>
