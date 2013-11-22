<?
include("../common_functions.php");
$db = std_db();

$id_array = $_GET["idlist"];

$html_formatted_idlist = ""; //Prepare a html-formatted version of the id-array to transfer the array to the graph
if ($id_array == ""){ // If no id is given, get metadata for the lastest measurement
    $query = "SELECT id, unix_timestamp(time) FROM measurements where type = 5 order by time desc limit 1";
    $result  = mysql_query($query,$db);  
    $row = mysql_fetch_array($result);
    $id_array[] = $row[0];
    $time[] = $row[1];
    $html_formatted_idlist = "&idlist[]=" . $id_array[0];
}
else{ // Walk through the id-list and get metadata for all graphs
    foreach($id_array as $id){
        $query = "SELECT UNIX_TIMESTAMP(time) FROM measurements where id = " . $id;
        $result  = mysql_query($query,$db);  
        $row = mysql_fetch_array($result);
        $time[] = $row[0];
        $html_formatted_idlist = $html_formatted_idlist . "&idlist[]=" . $id;
    }
}


// Get a list of id's that satisfies a search criterion
// These will be shown in a selection list
$query = "SELECT id FROM measurements where type = 5 order by time desc limit 100";
$result  = mysql_query($query,$db);  
while ($row = mysql_fetch_array($result)){
    $idlist[] = $row[0];
}

$ymax = ($_GET["ymax"] == "") ? 0 : $_GET["ymax"];
$ymin = ($_GET["ymin"] == "") ? 0 : $_GET["ymin"];

$manualscale = ($_GET["manualscale"] == "") ? "" : "checked";
$scaletype = ($_GET["scaletype"] == "") ? "" : "checked";
?>

<?=html_header()?>

<img style="float:left" src="massstime.php?scaletype=<?=$scaletype?>&manualscale=<?=$manualscale?>&ymax=<?=$ymax?>&ymin=<?=$ymin?><?=$html_formatted_idlist?>">

<?
$i=0;
foreach($id_array as $id){
    echo("<div style=\"border: 2px solid black; width:200px; float:right\">");
    echo("<p><b>Id:</b> " . $id . "<br>\n");
    echo("<b>Recorded at:</b> " . date("D M j H:i Y",$time[$i]) . "<br>\n");
    echo("</div>\n");
    $i++;
}
?>

<br clear="all">

<form action="read_masstime.php" method="get">
<p><b>Id:</b>
<select name="idlist[]" size="4">
<?
for($i=0;$i<count($idlist);$i++){
    $selected = (in_array($idlist[$i],$id_array)) ? "selected" : "";
    echo("<option " . $selected . ">" . $idlist[$i] . "</option>\n");
}
?>
</select>
</p>

<p><b>Log scale: </b> <input type="checkbox" name="scaletype" value="1" <?=$scaletype?>></p>

<p><b>Manual Y-scale:</b> <input type="checkbox" name="manualscale" value="1" <?=$manualscale?>><br>
<b>Max:</b> <input name="ymax" type="text" size="7" value="<?=$ymax?>"> <b>Min:</b> <input name="ymin" type="text" size="7" value="<?=$ymin?>"></p>

<input type="submit" value="Update">
</form>

</body>
</html>
