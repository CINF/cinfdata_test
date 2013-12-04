<?
include("../common_functions.php");
include("graphsettings.php");
$db = std_db();
$type = $_GET["type"];

$settings = plot_settings($type); // This will be overridden, we just need to know what type-number to extract from the database

//$exported_ids is the list of ids that is going to be plotted. This is either the idlist from the url or the latest measurement
$exported_ids = $_GET["idlist"];

// Get metdata for each plot and produce the id-list for the plot
$html_formatted_idlist = "";
foreach($exported_ids as $curr_id){
    $param["id"] = $curr_id;
    $html_formatted_idlist = $html_formatted_idlist . "&idlist[]=" . $curr_id;
    
    $settings = plot_settings($type,$param); 

    $meta_informations[$curr_id]["query"] = $settings["query"];
    $query = "SELECT UNIX_TIMESTAMP(time), timestep, comment, pass FROM " .$settings["measurements_table"] . " where id = " . $curr_id;
    $result  = mysql_query($query,$db);  
    $row = mysql_fetch_array($result);
    $meta_informations[$curr_id]["time"] = $row[0];
    $meta_informations[$curr_id]["timestep"] = $row[1];
    $meta_informations[$curr_id]["comment"] = $row[2];
    $meta_informations[$curr_id]["pass"] = $row[3];
    
   	$query = "SELECT x FROM " . $settings["xyvalues_table"] . " where measurement = " . $curr_id . " order by id limit 2";
	$meta_informations[$curr_id]["stepsize"] = get_xy_stepsize($query,$db);
}


$idline          = "";
$recordedline    = "";
$stepline        = "";
$timeprstepline  = "";
$commentline     = "";
$passline        = "";
$queryline       = "";
foreach($meta_informations as $id_key => $meta){
    $idline          = $idline          . "\"Id\"\t" . "\"" . $id_key . "\"\t";
    $recordedline    = $recordedline    . "\"Recorded\"\t" . "\"" . date("D M j H:i Y",$meta["time"]) . "\"\t";
    $stepline        = $stepline        . "\"Stepsize\"\t" . "\"" . abs($meta["stepsize"]) . "\"\t";
    $timeprstepline  = $timeprstepline  . "\"Time pr. step\"\t" . "\"" . $meta["timestep"] . "\"\t";
    $commentline     = $commentline     . "\"Comment\"\t" . "\"" . $meta["comment"] . "\"\t";
    $passline        = $passline        . "\"Pass energy\"\t" . "\"" . $meta["pass"]  . "\"\t";
    $queryline       = $queryline       . "\"Query\"\t" . "\"" . $meta["query"] . "\"\t";

    $data[$id_key] = get_xy_values($meta["query"],$db);
}
header('Content-type: text/plain');
// Definition: The exported files contains 20 lines of meta-information. In this way the file
// will not change syntax even if more meta-information is added later
echo($idline          . "\n"); //1
echo($recordedline    . "\n");
echo($stepline        . "\n");
echo($timeprstepline  . "\n");
echo($commentline     . "\n");
echo($passline        . "\n");
echo($queryline       . "\n");
echo("\n");
echo("\n"); //10
echo("\n");
echo("\n");
echo("\n");
echo("\n");
echo("\n"); //15
echo("\n");
echo("\n");
echo("\n");
echo("\n");
echo("\n"); //20


foreach($data as $id_key => $curr_data){
    $finaldata[0] = $finaldata[0] . "\"" . $id_key . "-x\"\t" . "\"" . $id_key . "-y\"\t";

    for($i=0;$i<count($curr_data["x"]);$i++){
        $finaldata[$i+1] = $finaldata[$i+1] . "\"" . $curr_data["x"][$i] . "\"\t\"" . $curr_data["y"][$i] . "\"\t";
    }
}
for($i=0;$i<count($finaldata);$i++){
    echo($finaldata[$i] . "\n");
}
?>
