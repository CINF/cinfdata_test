<?php

function plot_settings($type,$params=""){
    # Write the type to the associative settings array and hence initiate it
    $settings['type']=$type;

    $gs = fopen('graphsettings.xml', 'r');
    $gs = fread($gs, filesize('graphsettings.xml'));
    $gs_xml = new SimpleXMLElement($gs);

    # Put the graph specific settings in $settings
    foreach ($gs_xml->graph as $g) {
        if ($g['type'] == $type) {
            foreach ($g->children() as $graph_elements){
                $settings[$graph_elements->getName()] = (string) $graph_elements;
            }
        }
    }

    # Put the global settings in $settings
    foreach ($gs_xml->global_settings->children() as $global){
        $settings[$global->getName()] = (string) $global;
    }


    if (gettype($params) == 'array'){
	foreach($settings as $s_key => $s_value){
	  foreach($params as $p_key => $p_value){
	    $settings[$s_key] = str_replace('{'.$p_key.'}', $p_value, $settings[$s_key]);
	  }
	}
    }

    return $settings;
}

?>
