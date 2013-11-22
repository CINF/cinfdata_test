<?
include ("../jpgraph/src/jpgraph.php");
include ("../jpgraph/src/jpgraph_log.php");
include ("../jpgraph/src/jpgraph_line.php");
include ("../jpgraph/src/jpgraph_date.php");

$db = mysql_connect("localhost", "root", "CINF123");  

mysql_select_db("new_db",$db);


$query = "SELECT unix_timestamp(time), pressure FROM pressure_stm312 where pressure > 0 and pressure < 3e-6 and time between \"2009-10-09 13:00:00\" and \"2009-10-12 12:00:00\" order by id";
$result  = mysql_query($query,$db);  
while ($row = mysql_fetch_array($result)){
      $ydata[] = $row[1];
      $xdata[] = $row[0];
}

// Create the graph. These two calls are always required
$graph = new Graph(850,600,"auto");     
$graph->SetScale("datlog");
$graph->img->SetMargin(60,40,20,100);
$graph->xaxis->SetFont(FF_DV_SANSSERIF,FS_NORMAL,8);
$graph->xaxis->SetLabelAngle(45);
$graph->xaxis->SetLabelFormatString('M-d H:i',true);
$graph->title->Set("Examples for graph");
$graph->xgrid->Show();

// Create the linear plot
$lineplot=new LinePlot($ydata,$xdata);
$lineplot->SetColor("blue");


// Add the plot to the graph
$graph->Add($lineplot);

// Display the graph
$graph->Stroke();
?>
