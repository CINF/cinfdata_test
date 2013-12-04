<?
include("../common_functions.php");
include("graphsettings.php");
$db = std_db();

$type = $_GET["type"];
$size = get_size($_GET["xsize"],$_GET["ysize"],$_GET["smallplot"]); // Sets the imagesize
$offset = $_GET["offsetlist"];
$labels = $_GET["labellist"];

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

$max_y_val = 0;
$min_y_val = 0;
$max_x_val = 0;
$min_x_val = 0;
foreach($query as $id_key => $curr_query){
    $data[$id_key] = get_xy_values($curr_query,$db,$offset[$id_key]);
    $max_y_val = (max($data[$id_key]["y"]) > $max_y_val) ? max($data[$id_key]["y"]) : $max_y_val;
    $min_y_val = (min($data[$id_key]["y"]) > $min_y_val) ? min($data[$id_key]["y"]) : $min_y_val;
    $max_x_val = (max($data[$id_key]["x"]) > $max_x_val) ? max($data[$id_key]["x"]) : $max_x_val;
    $min_x_val = (min($data[$id_key]["x"]) > $min_x_val) ? min($data[$id_key]["x"]) : $min_x_val;
}

$yscale = default_yscale($_GET["ymax"],$_GET["ymin"],$_GET["manualscale"],$_GET["logscale"]);
$xscale_xy = xscale($_GET["xmax"],$_GET["xmin"],$_GET["manualxscale"],false);
$graph = new Graph($size["x"],$size["y"],"auto");

if($_GET["logscale"]){
    $settings["default_yscale"] = "log";
}

$scaletype = $settings["default_xscale"] . $settings["default_yscale"];

if ($yscale["min"] !== $yscale["max"]){
    $min_y_val = $yscale["min"];
    $max_y_val = $yscale["max"];
}
if ($xscale_xy["min"] !== $xscale_xy["max"]){
    $min_x_val = $xscale_xy["min"];
    $max_x_val = $xscale_xy["max"];
}

if($settings["default_xscale"] == "dat"){ //If we need a date scale we do not define ranges
    $graph->SetScale($scaletype);
}
else{
    $graph->SetScale($scaletype,$min_y_val,$max_y_val,floor($min_x_val),ceil($max_x_val));
}

if ($max_y_val<0.01){
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
        if ($labels == ""){
      	      $lineplot->SetLegend($id_key);
        } else {
             $lineplot->SetLegend($labels[$id_key]);
        }
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
