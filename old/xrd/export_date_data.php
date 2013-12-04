<?
include("../common_functions.php");
include("graphsettings.php");
$db = std_db();

$type = $_GET["type"];
$xscale = date_xscale($_GET["from"],$_GET["to"]);
$settings = plot_settings($type,$xscale);

$data = get_xy_values($settings["query"],$db);

$xval = $data["x"];
$yval = $data["y"];

for($i=0;$i<count($xval);$i++){
    echo(date('Y-m-d H:i',$xval[$i]) . "," . $yval[$i] . "<br>");
}

?>
