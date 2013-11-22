<?
include("../common_functions.php");
include("sql-statements.php");
$db = std_db();

$size = get_size($_GET["xsize"],$_GET["ysize"],$_GET["smallplot"]); // Sets the imagesize
$xscale = date_xscale($_GET["from"],$_GET["to"]);
$yscale = default_yscale($_GET["ymax"],$_GET["ymin"],$_GET["manualscale"],false);

$query = sql_query("temperature",$xscale);
$data = get_xy_values($query,$db);
//$data = remove_outliers($data);

$graph = new Graph($size["x"],$size["y"],"auto");

if ($yscale["manual"]){
	$graph->SetScale("datlin",$yscale["min"],$yscale["max"]);
}
else{
	$graph->SetScale("datlin");
}

$graph = std_graph_layout($graph);
$graph = final_formatting($graph,$size["small"],true,"Temperature in the Volvo","Temperature / C");

// Create the linear plot
$lineplot=new LinePlot($data["y"],$data["x"]);
$lineplot->SetColor("blue");
$graph->Add($lineplot);

// Display the graph
$graph->Stroke();
?>
