<?
include ("/usr/share/jpgraph/jpgraph.php");
include ("/usr/share/jpgraph/jpgraph_log.php");
include ("/usr/share/jpgraph/jpgraph_line.php");

$db = mysql_connect("localhost", "root", "CINF123");  

mysql_select_db("new_db",$db);

$id = $_GET["id"];

$query = "SELECT x,y  FROM xy_values where measurement = " . $id . " order by id";

$result  = mysql_query($query,$db);  
while ($row = mysql_fetch_array($result)){
      $ydata[] = $row[1];
      $xdata[] = $row[0];
}

// Create the graph. These two calls are always required
$graph = new Graph(650,600,"auto");     
$graph->SetScale("linlin");
$graph->img->SetMargin(30,90,40,50);
$graph->xaxis->SetFont(FF_FONT1,FS_BOLD);
$graph->title->Set("Examples for graph");

// Create the linear plot
$lineplot=new LinePlot($ydata,$xdata);
$lineplot->SetLegend("Test 1");
$lineplot->SetColor("blue");


// Add the plot to the graph
$graph->Add($lineplot);

// Display the graph
$graph->Stroke();
?>