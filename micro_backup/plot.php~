<?
include("../common_functions.php");
include("graphsettings.php");
$db = std_db();

$type = $_GET["type"];
$size = get_size($_GET["xsize"],$_GET["ysize"],$_GET["smallplot"]); // Sets the imagesize

$settings = plot_settings($type); // This will be overridden, we just need to know what type of x-scale is in this plot
if ($settings["default_xscale"] == "dat"){ // This is a date-plot, we need the date interval
    $xscale = date_xscale($_GET["from"],$_GET["to"]);
    $settings = plot_settings($type,$xscale);
    $query[] = $settings["query"];
}
else{ // This is properly a set of xy-data, we need some ids.
    $plotted_ids = $_GET["idlist"];
    foreach($plotted_ids as $id){
        $param["id"] = $id;
        $settings = plot_settings($type,$param);
        $query[$id] = $settings["query"];
    }
}

$max_val = 0; // Find maximum plotted value to write the y-axis nicer if the numbers are small
foreach($query as $id_key => $curr_query){
    $data[$id_key] = get_xy_values($curr_query,$db);
    $max_val = (max($data[$id_key]["y"]) > $max_val) ? max($data[$id_key]["y"]) : $max_val;
}

$yscale = default_yscale($_GET["ymax"],$_GET["ymin"],$_GET["manualscale"],false);
$graph = new Graph($size["x"],$size["y"],"auto");
$scaletype = $settings["default_xscale"] . $settings["default_yscale"];

if ($yscale["manual"]){
	$graph->SetScale($scaletype,$yscale["min"],$yscale["max"]);
} else{
	$graph->SetScale($scaletype);
}
if ($max_val<0.01){
    $graph->yaxis->SetLabelFormat("%1.1e"); // Apparantly this only works for the linear scale
}

$graph = std_graph_layout($graph);
$graph = final_formatting($graph,$size["small"],($settings["default_xscale"]=="dat"),$settings["titel"],$settings["ylabel"],$settings["xlabel"]);

// Create the linear plot
if ($settings["default_style"]!="barplot"){ // So far we have only one exception from default - more will follow and then we need a more fancy way of handling this
    $i=0;
    foreach($data as $id_key => $curr_data){
        $lineplot=new LinePlot($curr_data["y"],$curr_data["x"]);
        $lineplot->SetColor(JP_colors($i++));
      	$lineplot->SetLegend($id_key);
        $graph->Add($lineplot);
    }
} else{
    $barplot=new BarPlot($data[0]["y"],$data[0]["x"]);
    $barplot->SetWidth(10);
    $graph->Add($barplot);
}

// Display the graph
$graph->Stroke();
?>
