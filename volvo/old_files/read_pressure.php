<?
include("../common_functions.php");

$xscale = date_xscale($_GET["from"],$_GET["to"]);

$ymax = ($_GET["ymax"] == "") ? 0 : $_GET["ymax"];
$ymin = ($_GET["ymin"] == "") ? 0 : $_GET["ymin"];

$manualscale = ($_GET["manualscale"] == "") ? "" : "checked";
?>

<?=html_header()?>

<p><img src="dateplot.php?type=pressure&from=<?=$xscale["from"]?>&to=<?=$xscale["to"]?>&manualscale=<?=$manualscale?>&ymax=<?=$ymax?>&ymin=<?=$ymin?>"></p>
<form action="read_pressure.php" method="get">
<p><b>From:</b> <input name="from" type="text" value="<?=$xscale["from"]?>"> <b>To:</b><input name="to" type="text" value="<?=$xscale["to"];?>"></p>

<p><b>Manual Y-scale:</b> <input type="checkbox" name="manualscale" value="1" <?=$manualscale?>><br>
<b>Max:</b> <input name="ymax" type="text" size="7" value="<?=$ymax?>"> <b>Min:</b> <input name="ymin" type="text" size="7" value="<?=$ymin?>"></p>


<input type="submit" value="Update">
</form>

</body>
</html>
