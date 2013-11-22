<?
include("../common_functions.php");
$db = std_db();

$id = $_GET["id"];

$query = "SELECT x,y  FROM xy_values where measurement = " . $id . " order by id";
$data = get_xy_values($query,$db);

$size = get_size($_GET["xsize"],$_GET["ysize"],$_GET["smallplot"]);  // Sets the imagesize
$graph = new Graph($size["x"],$size["y"],"auto");

$graph->SetScale("linlog");
$graph->img->SetMargin(70,40,20,30);
$graph = std_graph_layout($graph);

$graph->yaxis->title->Set("Current / A");
$graph->xaxis->title->Set("Voltage / V");
$graph->xaxis->SetLabelAngle(0);
$graph->title->Set("IV-curves");

// Create the linear plot
$lineplot=new LinePlot($data["y"],$data["x"]);
$lineplot->SetColor("blue");
$graph->Add($lineplot);

// Display the graph
$graph->Stroke();
?>
