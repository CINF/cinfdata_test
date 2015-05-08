<?php

$gs = fopen('graphsettings.xml', 'r');
$gs = fread($gs, filesize('graphsettings.xml'));
$gs_xml = new SimpleXMLElement($gs);



$dateplots = Array();
foreach ($gs_xml->graph as $graph_) {
  if (isset($graph_->app)){
    $element = Array();
    if ($graph_->default_xscale == "dat"){
      $element["type"] = "date";
    }
    $element["plot"] = (string) $graph_["type"];
    $element["title"] = (string) $graph_->app->title;
    $dateplots[] = $element;

  }
}

echo(json_encode($dateplots));

?>