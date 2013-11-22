<?
include("../common_functions.php");
include("graph_settings.php");
$db = std_db();

$type = $_GET["type"];

$size = get_size($_GET["xsize"],$_GET["ysize"],$_GET["smallplot"]); // Sets the imagesize
$xscale = date_xscale($_GET["from"],$_GET["to"]);
$yscale = default_yscale($_GET["ymax"],$_GET["ymin"],$_GET["manualscale"],false);

$query = sql_query($type,$xscale);
$data = get_xy_values($query,$db);

$graph = new Graph($size["x"],$size["y"],"auto");

$scaletype = (ylog($type)) ? "datlog" : "datlin";
if ($yscale["manual"]){
	$graph->SetScale($scaletype,$yscale["min"],$yscale["max"]);
}
else{
	$graph->SetScale($scaletype);
}

$graph = std_graph_layout($graph);
$graph = final_formatting($graph,$size["small"],true,titel($type),ylabel($type));

// Create the linear plot
$lineplot=new LinePlot($data["y"],$data["x"]);
$lineplot->SetColor("blue");
$graph->Add($lineplot);

// Display the graph
$graph->Stroke();
?>
