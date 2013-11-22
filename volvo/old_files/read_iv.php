<?
$db = mysql_connect("localhost", "root", "CINF123");  
mysql_select_db("new_db",$db);

// Get the latest id to plot as default
$query = "SELECT id FROM measurements where type = 1 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$id = $row[0];
$id = ($_GET["id"] == "") ? $id : $_GET["id"];


$ymax = ($_GET["ymax"] == "") ? 0 : $_GET["ymax"];
$ymin = ($_GET["ymin"] == "") ? 0 : $_GET["ymin"];

$manualscale = ($_GET["manualscale"] == "") ? "" : "checked";
?>

<html>
<head>
<title>IV-curves</title>
</head>
<body>
<p><img src="plot.php?type=iv&id=<?=$id?>&manualscale=<?=$manualscale?>&ymax=<?=$ymax?>&ymin=<?=$ymin?>"></p>
<form action="read_iv.php" method="get">
<p><b>Id:</b> <input name="id" type="text" value="<?=$id?>"></p>

<p><b>Manual Y-scale:</b> <input type="checkbox" name="manualscale" value="1" <?=$manualscale?>><br>
<b>Max:</b> <input name="ymax" type="text" size="7" value="<?=$ymax?>"> <b>Min:</b> <input name="ymin" type="text" size="7" value="<?=$ymin?>"></p>

<input type="submit" value="Update">
</form>

</body>
</html>
