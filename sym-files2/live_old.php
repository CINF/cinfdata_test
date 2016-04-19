<?php

  /* 
    Copyright (C) 2014 Robert Jensen, Thomas Andersen and Kenneth Nielsen
    
    The CINF Data Presentation Website is free software: you can
    redistribute it and/or modify it under the terms of the GNU
    General Public License as published by the Free Software
    Foundation, either version 3 of the License, or
    (at your option) any later version.
    
    The CINF Data Presentation Website is distributed in the hope
    that it will be useful, but WITHOUT ANY WARRANTY; without even
    the implied warranty of MERCHANTABILITY or FITNESS FOR A
    PARTICULAR PURPOSE.  See the GNU General Public License for more
    details.
    
    You should have received a copy of the GNU General Public License
    along with The CINF Data Presentation Website.  If not, see
    <http://www.gnu.org/licenses/>.
  */


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
      echo("<tr><th>#</th><th>Name</th><th>Time</th><th>Value</th>");
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
  // Some kind of escaping issue
  $json_str = str_replace("\\", "\\\\", $json_str);
  echo("var $name = JSON.parse('$json_str');\n");
}

# Standard setup
date_default_timezone_set("Europe/Copenhagen");

# Get type and settings
include("../common_functions_v2.php");
$type = $_GET["type"];
include("graphsettings.php");
$settings = plot_settings($type);

# Produce header and layout
if (isset($settings["page_title"])){
  $include_head = "    <link rel=\"StyleSheet\" href=\"../css/live.css\" type=\"text/css\" media=\"screen\">\n" . 
    "    <script type=\"text/javascript\" src=\"../js/js_query.js\"></script> ";
  echo(html_header($root="../", $title=$settings["page_title"], $includehead=$include_head, $charset="UTF-8", $width=$settings["page_width"]));
} else {
  echo(html_header($root="../", $title="Live Plots", $includehead=$include_head, $charset="UTF-8", $width=$settings["page_width"]));
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
echo("<script src=../js/live_old.js></script>");
?>

<?php echo(html_footer()); ?>
