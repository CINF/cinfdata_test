
<?php

function live_header($width){
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
  $header = $header . "        Live Plots\n";
  $header = $header . "        <a href=\"/\"><img class=\"logo\" src=\"../images/cinf_logo_beta_greek.png\" alt=\"CINF data viewer\"></a>\n";
  $header = $header . "          <div class=\"header_utilities\">\n";
  $header = $header . "            <a class=\"header_links\" href=\"https://cinfwiki.fysik.dtu.dk/cinfwiki/Software/DataWebPageUserDocumentation\">Help</a><br>\n";
  $header = $header . "            <a class=\"header_links\" href=\"test_configuration_file.php\">Config</a>\n";
  $header = $header . "          </div>\n";
  $header = $header . "      </div>\n";
  $header = $header . "      <div class=\"liveplotcontainer\">\n";

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

function make_container_divs($layout){
  $containers = array_keys($layout);
  natsort($containers);
  foreach ($containers as $container){
    $width = intval($layout[$container]["width"]) * 12;
    echo("        <div id=\"$container\" style=\"width:{$width}px;float:left\">\n");
    $in_container = array_keys($layout[$container]);
    natsort($in_container);
    foreach($in_container as $item){
      if (substr($item, 0, 6) == "figure"){
	make_figure_container($layout[$container][$item], $item, $width);
      }
    }
    echo("        </div>\n");
  }
}

function make_figure_container($figure, $figurename, $width){
  $height = $figure["height"] * 9;
  echo("          <div id=\"$figurename\" style=\"height:{$height}px;width:{$width}\"></div>\n");
}

date_default_timezone_set("Europe/Copenhagen");

# Get type and settings
$type = $_GET["type"];
include("graphsettings.php");
$settings = plot_settings($type);

# Figure out which sockets are required
$sockets = Array();
$sockets_new = Array();
foreach($settings["figures"] as $figure){
  foreach($figure["plots"] as $plot){
    print_r($plot);
    if (!in_array($plot["socket"], $sockets)){
      array_push($sockets, $plot["socket"]);
    }
    if
  }
}
print_r($sockets);

# Produce header and layout
echo(live_header($settings["page_width"] * 12));
make_container_divs($settings["layout"]);

echo("
<script type=\"text/javascript\">
// Start master java script
console.log(\"hallo\");

function log(msg) {
    setTimeout(function() {
	    throw new Error(msg);
	}, 0);
}");

# Setup the websockets
foreach($sockets as $socket){
  $name = "socket" . $socket;
  $socket_id = $settings["sockets"][$name];
  echo("
console.log(\"Socket: $socket\");
var ws_{$name}_sane_interval = 0;
var ws_{$name}_last_data = new Object();
var ws_{$name}_fields = new Array();

var ws_{$name} = new WebSocket(\"wss://cinf-wsserver.fysik.dtu.dk:9001\");
ws_{$name}.onopen = function() {
    ws_{$name}.send(\"$socket_id;fields\");
    ws_{$name}.send(\"$socket_id;sane_interval\");
};

ws_{$name}.onmessage = function (evt) {
    var parts = evt.data.split(\";\");
    var command = parts[0];
    parts.shift()

    if (command == \"data\"){
        for (var i=0;i<parts.length;i++){
            var splitup = parts[i].split(\":\");
            var p = [parseFloat(splitup[0]), parseFloat(splitup[1])];
            ws_{$name}_last_data[ws_{$name}_fields[i]] = p;
        }
    } else if (command == \"sane_interval\") {
        ws_{$name}_sane_interval = parseInt(parts[0]);
        setInterval(function(){ws_{$name}.send(\"$socket_id;data\")}, ws_{$name}_sane_interval);
    } else if (command == \"fields\") {
        ws_{$name}_fields = parts;
    }
};

");
}  # End foreach socket

# Create sockets list
$socket_names = Array();
foreach ($sockets as $socket){
  array_push($socket_names, "ws_socket" . $socket . "_last_data");
}

echo("var sockets = [" . join($socket_names, ", ") . "];");

$figures = array_keys($settings["figures"]);
natsort($figures);
foreach($figures as $name){
  $plot_names = array_keys($settings["figures"][$name]["plots"]);
  natsort($plot_names);
  $figure_sockets = Array();
  $figure_fields = Array();
  $figure_labels = Array("Time");
  foreach($plot_names as $plot_name){
    $plot = $settings["figures"][$name]["plots"][$plot_name];
    array_push($figure_sockets, $plot["socket"]);
    array_push($figure_fields, $plot["id"]);
    array_push($figure_labels, $plot["label"]);
  }

  #
  $figure_socket_string = join($figure_sockets, ", ");
  $figure_fields_string = "'" . join($figure_fields, "', '") . "'";
  $labels_string = "['" . join("', '", $figure_labels) . "']";
  $initial_data_string = join(", ", array_pad(Array(), count($figure_labels), 1));
  $figure_interval = $settings["figures"][$name]["update_interval"];
  echo("
// $name
var {$name}_data = [[$initial_data_string]];
var $name = new Dygraph(document.getElementById(\"$name\"), {$name}_data,
                          {
                            drawPoints: true,
                            connectSeparatedPoints: true,
                            labels: $labels_string
			  });

var {$name}_sockets = [$figure_socket_string];
var {$name}_fields = [$figure_fields_string];
var {$name}_first_call = true;
var {$name}_last_data = [];
window.setInterval(function(){
    if ({$name}_first_call){
        {$name}_data = [];
        {$name}_first_call = false;
        for (var i=0 ; i<{$name}_sockets.length ; i++){
            {$name}_last_data[i] = [-9999, -9999];
        }
    }

    var {$name}_update = false;
    for (var i=0 ; i<{$name}_sockets.length ; i++){
        if (sockets[{$name}_sockets[i]].hasOwnProperty({$name}_fields[i])){
            var point = sockets[{$name}_sockets[i]][{$name}_fields[i]];
            if (point != {$name}_last_data[i]){
                var new_point = new Array({$name}_sockets.length + 1);
                for (var j=0 ; j<new_point.length ; j++){
                    new_point[j] = null;
                }
                new_point[0] = new Date(point[0]*1000);
                new_point[i+1] = point[1];
                {$name}_data.push(new_point);
                {$name}_update = {$name}_update || true;
            }
        }
    }


    if ({$name}_update){
        {$name}_data = {$name}_data.slice(-100);
        {$name}.updateOptions({ 'file':  {$name}_data});

    }

}, $figure_interval)




");
}
?>

</script>

<?php echo(live_footer()); ?>
