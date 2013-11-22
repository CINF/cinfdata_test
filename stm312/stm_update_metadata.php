<?php
$debug = $_GET["debug"] == "checked";
include("../common_functions_v2.php");
echo(html_header());

$menu = Array("Overview" => "stm_overview.php",
	      "Search" => "stm_search.php",
	      "Update metadata" => "stm_update_metadata.php");
right_float_menu($menu, 6);

if ($debug){
echo("<pre>\n");
}

passthru('python stm_update_metadata.py 2>&1');

if ($debug){
echo("</pre>\n");
}

echo(html_footer());
?>