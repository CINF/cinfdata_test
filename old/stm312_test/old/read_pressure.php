<?
include("../common_functions.php");

$to = date('Y-m-d H:i',time() + 60); // Default, 1 minute into the future, to be shure get the whole plot
$from = date('Y-m-d H:i',time() - 60 * 60 * 24); // Default, plot the last 24 hours

$from = ($_GET["from"] == "") ? $from : $_GET["from"]; // If we get an argument, skip the defaults
$to = ($_GET["to"] == "") ? $to : $_GET["to"];

$ymax = ($_GET["ymax"] == "") ? 0 : $_GET["ymax"];
$ymin = ($_GET["ymin"] == "") ? 0 : $_GET["ymin"];

$manualscale = ($_GET["manualscale"] == "") ? "" : "checked";
?>

<?=html_header()?>

<p><img src="pressure.php?from=<?=$from?>&to=<?=$to?>&manualscale=<?=$manualscale?>&ymax=<?=$ymax?>&ymin=<?=$ymin?>"></p>
<form action="read_pressure.php" method="get">
<p><b>From:</b> <input name="from" type="text" value="<?=$from?>"> <b>To:</b><input name="to" type="text" value="<?=$to;?>"></p>

<p><b>Manual Y-scale:</b> <input type="checkbox" name="manualscale" value="1" <?=$manualscale?>><br>
<b>Max:</b> <input name="ymax" type="text" size="7" value="<?=$ymax?>"> <b>Min:</b> <input name="ymin" type="text" size="7" value="<?=$ymin?>"></p>


<input type="submit" value="Update">
</form>

</body>
</html>
