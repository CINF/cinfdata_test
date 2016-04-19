<?php

date_default_timezone_set("Europe/Copenhagen");
header('Content-Type: application/json');

# Include graphsettings
include("sym-files2/graphsettings.php");

# Get the request
$request = isset($_GET["request"]) ? $_GET["request"] : "";

# Generate the appropriate output
switch ($request) {
case "index":
  $return = get_index();
  break;
default:
  $return = "Unkonwn request: " . (string) $request;
}

# And write it to the page
echo(json_encode($return));

/* *** FUNCTIONS *** */

/**
 * Return all relevant information about the available plots
 *
 * This include the information about the available plots from the
 * index and graphsettings object for the available plots.
 */
function get_index(){
  # Read in the index XML file and iterate over setups
  $setups = Array();
  $index_xml = simplexml_load_file('index.xml');
  foreach($index_xml as $setup_xml){
    $setups[] = get_index_single_setup($setup_xml);
  }
  return $setups;
}

/**
 * Return all relevant information for a single setup. See get_index()   
 */
function get_index_single_setup($setup_xml){
  # Initialize setup array
  $setup = Array(
    'codename' => (string) $setup_xml['codename'],
    'title' => (string) $setup_xml->setup_title,
    'links' => Array(),
  );

  # Add links
  foreach ($setup_xml->link as $link_xml){
    $link = get_index_link($link_xml);
    if ($link != null){
      $setup['links'][] = $link;
    } 
  }

  return $setup;
}

/**
 * Return all relevant information for a index link
 */
function get_index_link($link_xml){
  # Initialize link array
  $link = Array('title' => (string) $link_xml->title);
  $ref = (string) $link_xml->ref;

  # Parse the url arguments
  $query = parse_url($ref, PHP_URL_QUERY);
  $query_args = Array();
  parse_str($query, $query_args);
  $link['query_args'] = $query_args;

  # Parse the path part of the url and set pagetype
  $link['path'] = parse_url($ref, PHP_URL_PATH);
  if (strpos($link['path'], 'xyplot.php') !== false){
    $link['pagetype'] = 'xyplot';
  } elseif (strpos($link['path'], 'dateplot.php') !== false){
    $link['pagetype'] = 'dateplot';
    # For dateplots, also include the graphsettings
    $path_parts = explode('/', ltrim($link['path'], '/'), 2);
    $graphsettingsfile = $path_parts[0] . '/graphsettings.xml';
    $link['graphsettings'] = plot_settings($query_args['type'], "", False, $graphsettingsfile);
  } else {
    # If the path is not to xyplot.php or dateplot.php then return null
    return null;
  }

  return $link;
}

?>