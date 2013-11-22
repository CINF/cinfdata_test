<?php

ini_set('display_errors', 1); 
ini_set('log_errors', 1); 
ini_set('error_log', dirname(__FILE__) . '/error_log.txt'); 
error_reporting(E_ALL);

function xml_tree_to_assiciative_arrays($xml){
  $ret = Array();
  foreach ($xml->children() as $child){
    if ($child->count() > 0){
      $ret[$child->getName()] = xml_tree_to_assiciative_arrays($child);
	}
    else{
      $ret[$child->getName()] = (string) $child;
	}
  }
  
  return $ret;
}

function plot_settings($type,$params=""){
  # Write the type to the associative settings array and hence initiate it
  $gs = fopen('graphsettings.xml', 'r');
  $gs = fread($gs, filesize('graphsettings.xml'));
  $gs_xml = new SimpleXMLElement($gs);
  
  # Put the graph specific settings in $settings
  foreach ($gs_xml->graph as $g) {
    if ($g['type'] == $type) {
      $settings = xml_tree_to_assiciative_arrays($g);
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
