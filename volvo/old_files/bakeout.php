<?
include("../common_functions.php");
$db = std_db();

$size = get_size($_GET["xsize"],$_GET["ysize"],$_GET["smallplot"]); // Sets the imagesize
$xscale = date_xscale($_GET["from"],$_GET["to"]);
//$yscale = default_yscale($_GET["ymax"],$_GET["ymin"],$_GET["manualscale"],false);

$query = "SELECT unix_timestamp(time), temperature FROM temperature where temperature between 0 and 600 and time between \"" . $xscale["from"] . "\" and \"" . $xscale["to"] ."\" order by time";
$data_temp = get_xy_values($query,$db);

$query = "SELECT unix_timestamp(time), pressure FROM pressure where time between \"" . $xscale["from"] . "\" and \"" . $xscale["to"] ."\" order by time";
$data_pressure = get_xy_values($query,$db);

$graph = new Graph($size["x"],$size["y"],"auto");
$graph->SetScale("datlin");

$graph->SetYScale(0,'log');
$graph->SetYScale(1,'lin');


$graph = std_graph_layout($graph);
$graph = final_formatting($graph,$size["small"],true,"Baking the Volvo","Temperature / C");

$lineplot_temp=new LinePlot($data_temp["y"],$data_temp["x"]);
$lineplot_temp->SetColor("blue");
$graph->Add($lineplot_temp);

$lineplot_pressure=new LinePlot($data_pressure["y"],$data_pressure["x"]);
$lineplot_pressure->SetColor("red");


$graph->AddY(0,$lineplot_pressure);
$graph->AddY(1,$lineplot_temp);


// Display the graph
$graph->Stroke();
?>
