<?
// Read dateplot is a rather simple status page used only for graphs with dates on the x-axis.
include("../common_functions.php");
include("graphsettings.php");
$db = std_db();
$type = $_GET["type"];

//This is a date-plot, we need the date interval
$xscale = date_xscale($_GET["from"],$_GET["to"]);
$settings = plot_settings($type,$xscale);

$ymax = ($_GET["ymax"] == "") ? 0 : $_GET["ymax"];
$ymin = ($_GET["ymin"] == "") ? 0 : $_GET["ymin"];

$manualscale = ($_GET["manualscale"] == "") ? "" : "checked";
$current_value = single_sql_value($db,$settings["query"] . " limit 1",1);
?>

<?=new_html_header()?>

<div class="data">
<img src="plot.php?type=<?=$type?>&id=<?=$param["id"]?>&from=<?=$xscale["from"]?>&to=<?=$xscale["to"]?>&manualscale=<?=$manualscale?>&ymax=<?=$ymax?>&ymin=<?=$ymin?>">
<form action="read_dateplot.php" method="get">
<input type="hidden" name="type" value="<?=$type?>"><br>

<b>From:</b><input name="from" type="text" value="<?=$xscale["from"]?>" size="13" class="leftspace"><br>
<b>To:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><input name="to" type="text" value="<?=$xscale["to"]?>" size="13" class="leftspace"><br>
<br>
<b>Manual Y-scale:</b> <input type="checkbox" name="manualscale" value="1" <?=$manualscale?>><br>
<b>Max:</b>&nbsp;<input name="ymax" type="text" size="7" value="<?=$ymax?>"><br>
<b>Min:</b>&nbsp;&nbsp;<input name="ymin" type="text" size="7" value="<?=$ymin?>">

<input type="submit" value="Update">
</form>
</div>

<a href="export_date_data.php?type=<?=$type?>&from=<?=$xscale["from"]?>&to=<?=$xscale["to"]?>">Export current data</a>

<br clear="all">

<b>Sql-statement for this graph:</b><br>
<?=$settings["query"]?>

<?=$current_value?>

<?=new_html_footer()?>

