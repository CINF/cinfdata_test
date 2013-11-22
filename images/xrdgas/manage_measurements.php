<?
include("../common_functions.php");
include("graphsettings.php");

echo(new_html_header());


$settings = plot_settings(0); //Just need to get names of tables

$db = std_db();

$timestamp = ($_GET["timestamp"] == "") ? "" : $_GET["timestamp"];
$timestamp_sel = ($_GET["timestamp_sel"] == "") ? "" : $_GET["timestamp_sel"];

if($timestamp_sel!=""){
   echo("<p>");
   $query = "select * from " . $settings["xyvalues_table"] . " where measurement in (select id from " . $settings["measurements_table"] . " where time = \"" . $timestamp_sel . "\")";
   $result  = mysql_query($query,$db);  
   echo("Affected rows in xy-values: " . mysql_affected_rows() . "<br>");
   $query = "select * from " . $settings["measurements_table"] . " where time = \"" . $timestamp_sel . "\"";
   $result  = mysql_query($query,$db);  
   echo("Affected rows in measurements table: " . mysql_affected_rows() . "</p>");
}

if($timestamp!=""){
   echo("<p>");
   $query = "delete from " . $settings["xyvalues_table"] . " where measurement in (select id from " . $settings["measurements_table"] . " where time = \"" . $timestamp . "\")";
   $result  = mysql_query($query,$db);  
   echo("Deleted rows in xy-values: " . mysql_affected_rows() . "<br>");
   $query = "delete from " . $settings["measurements_table"] . " where time = \"" . $timestamp . "\"";
   $result  = mysql_query($query,$db);  
   echo("Deleted rows in measurements table: " . mysql_affected_rows() . "</p>");
}


$query = "select distinct time, comment from " . $settings["measurements_table"] . " 	order by time desc";
$result  = mysql_query($query,$db);  
while ($row = mysql_fetch_array($result)){
    $timelist[] = $row[0];
    $commentlist[] = $row[1];
}

echo("<div class=\"graph\">");
//echo("<h2>Manage measurements</h2>");
echo("<form action=\"manage_measurements.php\" method=\"get\">\n");

echo("<b>Timestamp to delete</b></br>");
echo("<input name=\"timestamp\" type=\"text\" size=\"20\"><br>\n");

echo("<b>Timestamp to count</b></br>");
echo("<input name=\"timestamp_sel\" type=\"text\" size=\"20\" value=\"" . $timestamp_sel . "\"><br>\n");
echo("<input type=\"submit\" value=\"Update\"></p>");
echo("</form>");


for($i=0;$i<count($timelist);$i++){
    echo($timelist[$i] . " " . $commentlist[$i] . "<br>");
}

echo("</div>");
echo(new_html_footer());
?>
