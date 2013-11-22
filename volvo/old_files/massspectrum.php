<?
ini_set('memory_limit', '100M');
include("../common_functions.php");
$db = std_db();

$colors = array("red","blue","green","chocolate");

$size = get_size($_GET["xsize"],$_GET["ysize"],$_GET["smallplot"]);

if ($_GET["scaletype"]=="checked"){ // This means we want a log-scale
	$ymax = ($_GET["ymax"] == "") ? 1 : log10($_GET["ymax"]);
	$ymin = ($_GET["ymin"] == "") ? 1e-20 : log10($_GET["ymin"]);
	$scale="linlog";
}
else{
	$ymax = ($_GET["ymax"] == "") ? 1 : ($_GET["ymax"]);
	$ymin = ($_GET["ymin"] == "") ? 0 : ($_GET["ymin"]);
	$scale="linlin";	
}
$manual = ($_GET["manualscale"]=="checked");

// Create the graph.
$graph = new Graph($size["x"],$size["y"],"auto");
if ($manual){
	$graph->SetScale($scale,$ymin,$ymax);
}
else{
	$graph->SetScale($scale);
}

$graph = std_graph_layout($graph);
$graph = final_formatting($graph,$size["small"],false,"Mass spectrum","Current / A","Mass / amu");
$graph->yaxis->SetLabelFormat("%1.1e"); // Apparantly this only works for the linear scale

// Get the data and put it into the plot
$id_array = $_GET["idlist"];
$i = 0;
foreach($id_array as $id){
	unset($ydata);
	unset($xdata);
	$query = "SELECT x,y  FROM xy_values where measurement = " . $id . " and y>5e-15 order by id";
	$result  = mysql_query($query,$db);  
	while ($row = mysql_fetch_array($result)){
	      $ydata[] = $row[1];
	      $xdata[] = $row[0];
	}
	$lineplot=new LinePlot($ydata,$xdata);
	$lineplot->SetLegend($id);
	$lineplot->SetColor($colors[$i++]); //Increment the color for each plot
	// Add the plot to the graph
	$graph->Add($lineplot);
}


// Display the graph
$graph->Stroke();
?>
