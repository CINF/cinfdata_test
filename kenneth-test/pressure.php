<?
include ("../jpgraph/src/jpgraph.php");
include ("../jpgraph/src/jpgraph_log.php");
include ("../jpgraph/src/jpgraph_line.php");
include ("../jpgraph/src/jpgraph_date.php");

$db = mysql_connect("localhost", "root", "CINF123");  
mysql_select_db("new_db",$db);

$to = date('Y-m-d H:i',time() + 60); // Default, 1 minute into the future, to be shure get the whole plot
$from = date('Y-m-d H:i',time() - 60 * 60 * 24); // Default, plot the last 24 hours

$from = ($_GET["from"] == "") ? $from : $_GET["from"]; // If we get an argument, skip the defaults
$to = ($_GET["to"] == "") ? $to : $_GET["to"];


$query = "SELECT unix_timestamp(time), pressure FROM pressure where time between \"" . $from . "\" and \"" . $to ."\" order by id";
$result  = mysql_query($query,$db);  
while ($row = mysql_fetch_array($result)){
      $ydata[] = $row[1];
      $xdata[] = $row[0];
}

// Create the graph. These two calls are always required
$graph = new Graph(850,600,"auto");     
$graph->SetScale("datlog");
$graph->img->SetMargin(70,40,20,100);
//$graph->SetFrame(false);
$graph->SetClipping(true);

$graph->title->SetFont(FF_DV_SANSSERIF,FS_BOLD,18);
$graph->xaxis->SetFont(FF_DV_SANSSERIF,FS_NORMAL,8);
$graph->yaxis->SetFont(FF_DV_SANSSERIF,FS_NORMAL,8);
$graph->yaxis->title->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);


$graph->xaxis->SetLabelAngle(45);
$graph->xaxis->SetLabelFormatString('M-d H:i',true);
$graph->yaxis->title->Set("Pressure / mbar");
$graph->title->Set("Pressure in the Volvo");
$graph->yaxis->title->SetMargin(25);

//$graph->xaxis->SetWeight(1);
//$graph->ygrid->SetFill(true,'#EFEFEF@0.5','#BBCCFF@0.5');
$graph->xgrid->Show(true, true);
$graph->ygrid->Show(true, true);


// Create the linear plot
$lineplot=new LinePlot($ydata,$xdata);
$lineplot->SetColor("blue");


// Add the plot to the graph
$graph->Add($lineplot);

// Display the graph
$graph->Stroke();
?>
