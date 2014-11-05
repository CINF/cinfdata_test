
<?php

function live_header($width, $title="Live Plots"){
  $header = "";
  $header = $header . "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n";
  $header = $header . "<html>\n";
  $header = $header . "  <head>\n";
  $header = $header . "    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n";
  $header = $header . "    <title>CINF Live Plots</title>\n";
  $header = $header . "    <link rel=\"StyleSheet\" href=\"../css/live.css\" type=\"text/css\" media=\"screen\">\n";
  $header = $header . "    <script type=\"text/javascript\" src=\"dygraph/dygraph-dev.js\"></script>\n";
  $header = $header . "    <script type=\"text/javascript\" src=\"../js/update.js\"></script> \n";
  $header = $header . "    <script type=\"text/javascript\" src=\"../js/toogle.js\"></script>\n";
  $header = $header . "  </head>\n";
  $header = $header . "  <body>\n";
  $header = $header . "    <div class=\"container\" style=\"width:" . $width . "px\">\n";
  $header = $header . "      <div class=\"caption\">\n";
  $header = $header . "        $title\n";
  $header = $header . "        <a href=\"/\"><img class=\"logo\" src=\"../images/cinf_logo_beta_greek.png\" alt=\"CINF data viewer\"></a>\n";
  $header = $header . "          <div class=\"header_utilities\">\n";
  $header = $header . "            <a class=\"header_links\" href=\"https://cinfwiki.fysik.dtu.dk/cinfwiki/Software/DataWebPageUserDocumentation\">Help</a><br>\n";
  $header = $header . "            <a class=\"header_links\" href=\"test_configuration_file.php\">Config</a>\n";
  $header = $header . "          </div>\n";
  $header = $header . "      </div>\n";
  $header = $header . "      <div class=\"liveplotcontainer\">\n\n";

  return($header);
}

function live_footer(){
  $footer = "";
  $footer = $footer . "      </div>\n";
  $footer = $footer . "      <div class=\"copyright\" style=\"clear:both\">...</div>\n";
  $footer = $footer . "    </div>\n";
  $footer = $footer . "  </body>\n";
  $footer = $footer . "</html>\n";
  return($footer);
}

function make_container_divs($containers){
  $container_names = array_keys($containers);
  natsort($container_names);
  foreach ($container_names as $container_name){
    $container = $containers[$container_name];
    $width = $container["width"];
    $style = "width:{$container['width']}px;height:{$container['height']}px;float:left";
    # If requested add color to the div, for debugging purposes
    if (array_key_exists("bgcolor", $container)){
      $style .= ";background-color:{$container['bgcolor']}";
    }
    if (isset($container["padding"])){
      $style .= ";padding:{$container['padding']}";
    }
    echo("<!-- Container $container_name -->\n");
    echo("<div id=\"$container_name\" style=\"$style\">\n");
    # If it is a data div, create the data table
    if ($container["type"] == "data"){
      echo("<table class=\"datatable\" style=\"font-size:{$container['fontsize']}px\">\n");
      echo("<tr><th>#</th><th>Measurement</th><th>Time</th><th>Value</th>");
      $show_diff = isset($container["show_diff"]) ? $container["show_diff"] : "false";
      if ($show_diff == "true"){
	echo("<th>Diff [ms]</th>");
      }
      echo("</tr>\n");
      $item_names = array_keys($container['data']);
      natsort($item_names);
      foreach($item_names as $item_name){
	$item = $container['data'][$item_name];
	$id = "{$item['socket']}#{$item['id']}";
	if (isset($item["color"])){
	  echo("<tr><td style=background-color:{$item['color']}></td>");
	} else {
	  echo("<tr><td></td>");
	}
	# Write the desired data format into the HTML tag, default to ".2e"
	$format = ".2e";
	if (isset($item["format"])){
	  $format = $item["format"];
	} else {
	  $format = ".2e";
	}

	if (isset($item["unit"])){
	  $unit = $item["unit"];
	} else {
	  $unit = "";
	}
	echo("<td>{$item['label']}</td><td class=\"{$id}_time\">-</td>");
	echo("<td data-format=\"$format\" data-unit=\"$unit\" class=\"$id\">-</td>");
	if ($show_diff == "true"){
	  echo("<td class=\"{$id}_diff\">-</td>");
	}	
	echo("</tr>\n");
      }
      echo("</table>\n");
    }
    echo("</div>\n");
  }
}

function to_javascript($name, $data){
  $json_str = json_encode($data);
  echo("var $name = JSON.parse('$json_str');\n");
}

# Standard setup
date_default_timezone_set("Europe/Copenhagen");

# Get type and settings
$type = $_GET["type"];
include("graphsettings.php");
$settings = plot_settings($type);

# Produce header and layout
if (isset($settings["page_title"])){
  echo(live_header($settings["page_width"], $settings["page_title"]));
} else {
  echo(live_header($settings["page_width"]));
}
make_container_divs($settings["containers"]);

# Get socket defs
/* socket_defs is a list of socket definitions, where the index number is the
   same as used in graphsettings.xml. So: <socket0>rasppi25:8000</socket0>
   turns into: Array("rasppi25:8000") */
$socket_defs = Array();
/* measurement_ids is a list of lists, where the index in the first list is the
   socket def number and the values in the inner list are the ids we need from
   that socket. E.g:
   Array(Array("thetaprobe_pressure_loadlock", "thetaprobe_pressure_uvgun"))
   means that we need those two measurement from socket def 0 */
$measurement_ids = Array();
foreach ($settings['containers'] as $container){
  # Get the either 'figure' or 'data' element
  $plots_or_items = $container[$container['type']];
  # Loop over plots or data items
  foreach($plots_or_items as $plots_or_item){
    $socket_number = (int) $plots_or_item['socket'];
    $id = $plots_or_item['id'];
    # If we have never seen this socket number before
    if (!array_key_exists($socket_number, $socket_defs)){
      # Add it to the list of socket_defs..
      $def = $settings['sockets']['socket' . $plots_or_item['socket']];
      $socket_defs[$socket_number] = $def;
      # .. and add an array containing it to measurement_ids
      $measurement_ids[$socket_number] = Array($id);
    } else {
      # add the id if it is not alread there
      if (!in_array($id, $measurement_ids[$socket_number])){
	$measurement_ids[$socket_number][] = $id;
      }
    }
  }
}
// Sort the array, so that json will not turn them into associative arrays
ksort($measurement_ids);
ksort($socket_defs);


# Form a subscription map, of which figures subscribes to which data sets
/* It will look something like:
     socket_num => id => list_of_subs
   where a sub is an array with "figure_name" and "plot_index"
*/
# Form a list of figure definitions
$fig_data_subs = Array();
$figure_defs = Array();
foreach (array_keys($settings['containers']) as $container_name){
  $container = $settings['containers'][$container_name];
  # Skip if it is not a figure
  if ($container['type'] != "figure"){continue;}

  $figure_defs[$container_name] = $container;

  # Loop over plot names in order
  $plot_names = array_keys($container["figure"]);
  natsort($plot_names);
  foreach($plot_names as $plot_name){
    # Get the plot
    $plot = $container["figure"][$plot_name];
    $plot_index = (int) str_replace("plot", "", $plot_name);
    # Create an entry for this socket, if it does not already exist
    if (!array_key_exists($plot["socket"], $fig_data_subs)){
      $fig_data_subs[$plot["socket"]] = Array();
    }
    # Create an entry for this id, if it does not already exist
    if (!array_key_exists($plot["id"], $fig_data_subs[$plot["socket"]])){
      $fig_data_subs[$plot["socket"]][$plot["id"]] = Array();
    }
    # Form subscription and add it to the subscription index
    $sub = Array("figure_name" => $container_name, "plot_index" => $plot_index);
    $fig_data_subs[$plot["socket"]][$plot["id"]][] = $sub;
  }
}
ksort($fig_data_subs);

# Define input for external javascript
echo("<script>\n");
to_javascript("socket_defs", $socket_defs);
to_javascript("measurement_ids", $measurement_ids);
to_javascript("fig_data_subs", $fig_data_subs);
to_javascript("figure_defs", $figure_defs);
echo("</script>\n");
echo("<script src=../js/live.js></script>");
?>

<?php echo(live_footer()); ?>
