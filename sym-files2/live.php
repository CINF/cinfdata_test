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
  # Sort container names naturally
  $container_names = array_keys($containers);
  natsort($container_names);

  # Generate the containers
  foreach ($container_names as $container_name){
    $container = $containers[$container_name];

    # Setup the style
    $style = "width:{$container['width']}px;height:{$container['height']}px;float:left";
    # If requested add color to the div, for debugging purposes
    if (array_key_exists("bgcolor", $container)){
      $style .= ";background-color:{$container['bgcolor']}";
    }
    if (isset($container["padding"])){
      $style .= ";padding:{$container['padding']}";
    }

    # Generate html for the container
    echo("<!-- Container $container_name -->\n");
    echo("<div id=\"$container_name\" style=\"$style\">\n");

    # If it is a data div (table), create the data table
    if ($container["type"] == "table"){
      echo("<table class=\"datatable\" style=\"font-size:{$container['fontsize']}px\">\n");
      echo("<tr><th>#</th><th>Name</th><th>Time/x</th><th>Value</th>");
      $show_diff = isset($container["show_diff"]) ? $container["show_diff"] : "false";
      if ($show_diff == "true"){
	echo("<th>Diff [ms]</th>");
      }
      echo("</tr>\n");

      # Get the item names and sort the naturally
      $item_names = array_keys($container['data']);
      natsort($item_names);

      # Generate a row for each item
      foreach($item_names as $item_name){
	$item = $container['data'][$item_name];
	$id = str_replace(":", "_", $item['data_channel']);
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

        if (isset($item["xformat"])){
          $xformat = $item["xformat"];
        } else {
          $xformat = "";
        }
	echo("<td>{$item['label']}</td><td class=\"{$id}_time\" xformat=\"$xformat\">-</td>");
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

# Form a list of all the data channels that we want to subscribe to
# We use just the keys of an associative array as a set, since php does not have
# a distinct data type for this
$data_channels = Array();
$subscription_map = Array();
$send_to_html = Array();
foreach ($settings['containers'] as $container_name => $container){
  # Get the either data or figure element
  if ($container['type'] == 'table'){
    $items = $container['data'];
  } else {
    $items = $container['figure'];
  }

  # Loop over elements in the table or figure
  foreach($items as $item){
    $data_channel = $item['data_channel'];
    # If it is not already there, add this data_channel to the list of all
    # data_channels
    if (!array_key_exists($data_channel, $data_channels)){
      $data_channels[$data_channel] = null;
    }

    if ($container['type'] == 'table'){
      if (!array_key_exists($data_channel, $send_to_html)){
	$send_to_html[$data_channel] = null;
      }
      continue;
    }

    # If the subscription map already knows about this data_channel
    if (array_key_exists($data_channel, $subscription_map)){
      # If this container was already added to the subscription mad for this
      # data channel
      if (!array_key_exists($container_name, $subscription_map[$data_channel])){
	$subscription_map[$data_channel][$container_name] = null;
      }
    } else {
      $subscription_map[$data_channel] = Array($container_name => null);
    }
  }
}
foreach($subscription_map as $data_channel => $containers){
  $subscription_map[$data_channel] = array_keys($containers);
}

# Form a list of figure definitions
$figure_defs = Array();
foreach (array_keys($settings['containers']) as $container_name){
  $container = $settings['containers'][$container_name];
  # Skip if it is not a figure
  if ($container['type'] == "table"){continue;}

  $figure_defs[$container_name] = $container;

  # Loop over plot names in order
  #$plot_names = array_keys($container["figure"]);
  #natsort($plot_names);
  #foreach($plot_names as $plot_name){
    # Get the plot
    #$plot = $container["figure"][$plot_name];
    #$plot_index = (int) str_replace("plot", "", $plot_name);
    # Form subscription and add it to the subscription index
    #$sub = Array("figure_name" => $container_name, "plot_index" => $plot_index);
    #$fig_data_subs[$plot["socket"]][$plot["id"]][] = $sub;
  #}
}

# Define input for external javascript
echo("<script>\n");

to_javascript("data_channels", array_keys($data_channels));
to_javascript("subscription_map", $subscription_map);
to_javascript("send_to_html", array_keys($send_to_html));
to_javascript("figure_defs", $figure_defs);

echo("</script>\n");
echo("<script src=../js/live.js></script>");
?>

<?php echo(html_footer()); ?>
