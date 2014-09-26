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

/*
  After parsing "register" will contain an array of
  machine/registration numbers and the codenames that will be used
  from that machine, e.g:
    [0, [codename0, codename1], 2, [codename2, codename3]]
  And "socket_defs" will contain a list of hostname:ip e:g:
    ['hostname1:8000', 'hostname1:8000']
 */

$head_script = <<<EOD
<script type="text/javascript">

window.onload = function() {

    // Convert php vars to java script
    var register = JSON.parse('$register_json');
    var socket_defs = JSON.parse('$socket_defs_json');

    // Setup websocket ...
    var wsuri = "wss://cinf-wsserver.fysik.dtu.dk:9001";
    console.log("ws: URI: " + wsuri);

    // ... and work around Mozilla naming the websockets differently *GRR*
    if ("WebSocket" in window) {
	webSocket = new WebSocket(wsuri);
    }
    else {
	webSocket = new MozWebSocket(wsuri);
    }

    webSocket.onopen = function() {
	/* On ws open register the machines (hostname:ip) that are
	required for this page with the ws socket server.
        
        The ws server will respond with an echo of the request and the number
        of the registration (one per hostname:ip) and the sane interval. I.e.
        a request looks like:
          register#hostname:ip;codename0,codename1
        and the response looks like:
          register#hostname:ip;codename0,codename1#0#0.2
        */
	console.log("ws: Connected!");
	for (var n in register){
	    msg = "register#".concat(socket_defs[n], ";", register[n].join(','));
	    console.log("ws: Register connection: ".concat(msg));
	    webSocket.send(msg);
	}
    }
    
    webSocket.onclose = function(e) {
	/* on ws close show information on the console */
	console.log("ws: Closed (wasClean = " + e.wasClean + ", code = " + e.code + ", reason = '" + e.reason + "')");
    }

    function zeropadd(number){
	/* Retuns a padded version of a number less than 10
           NOTE: Return type is string if padded and int otherwise
        */
	if (number < 10){
	    number = '0' + number;
	}
	return number
    }

    function iso_time(date){
	/* Return a text string time stamp like 08:17:07 from date */

	// NOTE anything added with a string will be cast as a string, which
	// is why we don't care about the output type of zeropad
	var out = zeropadd(date.getHours()) + ":" +
	    zeropadd(date.getMinutes()) + ":" + zeropadd(date.getSeconds());
	return out
    }

    function parse_register(string) {
	/* Parse a register return string on the form:
             register#rasppi25:8000;codename0,codename1#0#0.2
           where the last 0 and 1 are the registration number and sane
           interval respectively
        */
	var split = data.split("#");
	// Schedule a ws send of the registration number once every sane
	// interval
	window.setInterval(
	    function(){
		webSocket.send(split[2])
	    }, parseFloat(split[3]) * 1000
	);
    }

    function parse_data(data){
	/* Parse a data return string on the form:
             [registration_number, [[t1, v1], [t2, v2]]]
           where t, v are time, value sets for a point
         */
	var socket = data[0];
	// Loop over the number of codenames in register[socket]
	for (var n in register[socket]){
	    // Form the HTML id on the form 0#codename0
	    var id = String(socket).concat("#", register[socket][n]);
	    // Get a date object from unixtime
	    var date = new Date(data[1][n][0] * 1000);
	    var out = '<b>Time: </b>' + iso_time(date) +
		' <b>Value: </b>' + data[1][n][1];
	    var now = new Date()
	    var diff = 'Estimated time diff (milliseconds): ' + (now - date);
	    document.getElementById(id).innerHTML = out;
	    document.getElementById(id + 'diff').innerHTML = diff;
	}

    }
    
    webSocket.onmessage = function(e) {
	/* ws onmessage: parse the message from JSON and act on it */
	document.getElementById("raw").innerHTML = e.data
	//console.log("message received: " + e.data);  // LOTS of debug output
	data = JSON.parse(e.data)
	if (typeof(data) == "string"){
	    if (data.indexOf("register") == 0){
		// The string is an register response
		parse_register(data);
	    } else {
		// The strings is unknown, most likely an error
		var msg = "Recieved an unknown string: '" + data +
		    "' on the websocket. Shutting down!"
		document.getElementById("raw").innerHTML = msg
		throw new Error(msg)
	    }
	} else {
	    // Assume that if type is not string, then it is data (array)
	    parse_data(data);
	}
    }
    
    webSocket.onerror = function(e) {
	/* on ws error log to console */
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
  echo("<p><b>$location ${field["name"]}</b></p>");
  echo("<p id=\"$id\"></p>");
}

?>

<h1>Debug</h1>
<h2>Raw return values</h2>
<p id="raw"></p>
<h2>Time delays</h2>
<?php
foreach($settings["fields"] as $field){
  $location = $socket_defs[(int) $field["socket"]];
  $id = $field["socket"] . "#" . $field["codename"] . "diff";
  echo("<p><b>$location ${field["name"]}</b></p>");
  echo("<p id=\"$id\"></p>");
}
?>

<p><a href="https://kenni:9001">Click me the first time</a>, to install the TLS certificate for the WebSockets endpoint.</p>


<?php echo(html_footer());?>