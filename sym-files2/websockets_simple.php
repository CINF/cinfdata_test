<?php
  /*
    Copyright (C) 2012 Robert Jensen, Thomas Andersen and Kenneth Nielsen
    
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

include("graphsettings.php");
include("../common_functions_v2.php");

$type = $_GET["type"];
$settings = plot_settings($type);

$register = Array();
$socket_defs = Array();
foreach(array_keys($settings["sockets"]) as $socket_name){
  $socket_defs[] = $settings["sockets"][$socket_name];
  $socket_name = str_replace("socket", "", $socket_name);
  if (sizeof($register) != (int) $socket_name){
    trigger_error("Sockets definitions must have consecutive numbers starting from 0", E_USER_ERROR);
  }
  $register[] = Array();
}
foreach($settings["fields"] as $field){
  $register[$field["socket"]][] = $field["codename"];
}
$register_json = json_encode($register);
$socket_defs_json = json_encode($socket_defs);

$head_script = <<<EOD
<script type="text/javascript">

window.onload = function() {

    // Convert php vars to java script
    var register = JSON.parse('$register_json');
    var socket_defs = JSON.parse('$socket_defs_json');

    // Setup websocket
    var wsuri = "wss://kenni:9001";
    console.log("ws: URI: " + wsuri);

    // Work around Mozilla naming the websockets differently *GRR*
    if ("WebSocket" in window) {
	webSocket = new WebSocket(wsuri);
    }
    else {
	webSocket = new MozWebSocket(wsuri);
    }
    
    webSocket.onopen = function() {
	console.log("ws: Connected!");
	for (var n in register){
	    msg = "register#".concat(socket_defs[n], ";", register[n].join(','));
	    console.log("ws: Register connection: ".concat(msg));
	    webSocket.send(msg);
	}
    }
    
    webSocket.onclose = function(e) {
	console.log("ws: Closed (wasClean = " + e.wasClean + ", code = " + e.code + ", reason = '" + e.reason + "')");
    }

    Number.prototype.padLeft = function(base, chr){
	var len = (String(base || 10).length - String(this).length)+1;
	return len > 0? new Array(len).join(chr || '0')+this : this;
    }

    function zeropadd(string, width){
	while (string.length < width){
	    string = '0' + string;
	}
	return string
    }

    function iso_time(date){
	var out = zeropadd(String(date.getHours()), 2) + ":" + 
	    zeropadd(String(date.getMinutes()), 2) + ":" + 
	    zeropadd(String(date.getSeconds()), 2);
	return out
    }

    function parse_register(string) {
	// data is register#rasppi25:8000;codename0,codename1#0#1
	// where last 0 and 1 are register and saneinterval respectively
	var split = data.split("#");
	window.setInterval(
	    function(){
		webSocket.send(split[2])
	    }, parseFloat(split[3]) * 1000
	);
    }

    function parse_data(data){
	var socket = data[0];
	for (var n in register[socket]){
	    var id = String(socket).concat("#", register[socket][n]);
	    var date = new Date(data[1][n][0] * 1000);
	    var out = '<b>Time: </b>' + iso_time(date) +
		' <b>Value: </b>' + data[1][n][1];
	    document.getElementById(id).innerHTML = out
	}

    }
    
    webSocket.onmessage = function(e) {
	// Produces a lot of output
	// console.log("message received: " + e.data);
	data = JSON.parse(e.data)
	document.getElementById("raw").innerHTML = data
	if (typeof(data) == "string"){
	    if (data.indexOf("register") == 0){
		parse_register(data);
	    }
	} else {
	    parse_data(data);
	}
    }
    
    webSocket.onerror = function(e) {
	console.log(e);
    }
}

</script>
EOD;

echo(html_header($root="../", $title="Data viewer", $includehead=$head_script));
?>

<h1>Simple websocket page</h1>
<?php

foreach($settings["fields"] as $field){
  $location = $socket_defs[(int) $field["socket"]];
  $id = $field["socket"] . "#" . $field["codename"];
  echo("<p><b>$location ${field["name"]}</b></p><p id=\"\"></p>");
  echo("<p id=\"$id\"></p>");
}

?>

<h1>Debug</h1>
<h2>Raw return values</h2>
<p id="raw"></p>

<p><a href="https://kenni:9001">Click me the first time</a>, to install the TLS certificate for the WebSockets endpoint.</p>


<?php echo(html_footer());?>