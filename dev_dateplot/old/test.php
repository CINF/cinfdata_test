<?php
include("graphsettings.php");

ini_set('display_errors', 1); 
ini_set('log_errors', 1); 
ini_set('error_log', dirname(__FILE__) . '/error_log.txt'); 
error_reporting(E_ALL);

$settings = plot_settings('multidateplot');

# Make a list of the all the graphs in the multiplot definition
$graphs = Array();
foreach($settings as $key => $value){
  # Regular expression matching the dateplotN tag (with minimum one N-digit)
  if (preg_match("/^dateplot[0-9][0-9]*$/", $key)){
    $graphs[$key] = $value;
  }
}

# ROBERT Here we have a list of all the graphs and it can be used to make a
# selection box. The list of selected graphs is probably best passed around
# as a list of tagnames i.e:
# left_graphs  = ['dateplot2', 'dateplot3']
# right_graphs = ['dateplot5']
foreach($graphs as $key => $value){
  echo(substr($key,-1).'</br>');
  echo($value['title'].'</br>');
}

?>
