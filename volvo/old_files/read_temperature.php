<?
include("../common_functions.php");

$xscale = date_xscale($_GET["from"],$_GET["to"]);
$yscale = default_yscale($_GET["ymax"],$_GET["ymin"],$_GET["manualscale"],false);
$manualscale = ($yscale["manual"]) ? "checked" : ""; // Bug in the common_functions API!!! This is not how
					            // it is supposed to be done!!!
?>

<?=html_header()?>

<p><img src="dateplot.php?type=temperature&from=<?=$xscale["from"]?>&to=<?=$xscale["to"]?>&manualscale=<?=$manualscale?>&ymax=<?=$yscale["max"]?>&ymin=<?=$yscale["min"]?>"></p>
<form action="read_temperature.php" method="get">
<p><b>From:</b> <input name="from" type="text" value="<?=$xscale["from"]?>"> <b>To:</b><input name="to" type="text" value="<?=$xscale["to"];?>"></p>

<p><b>Manual Y-scale:</b> <input type="checkbox" name="manualscale" value="checked" <?=$manualscale?>><br>
<b>Max:</b> <input name="ymax" type="text" size="7" value="<?=$yscale["max"]?>"> <b>Min:</b> <input name="ymin" type="text" size="7" value="<?=$yscale["min"]?>"></p>


<input type="submit" value="Update">
</form>

</body>
</html>
