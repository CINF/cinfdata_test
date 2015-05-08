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
if (empty($settings)){
  echo("Type \"$type\" not found in graphsettings.xml. Bailing out!");
  exit(42);
}

$subscriptions = Array();
$socket_defs = Array();

foreach(array_keys($settings["sockets"]) as $socket_name){
  $socket_defs[] = $settings["sockets"][$socket_name];
  $socket_name = str_replace("socket", "", $socket_name);
  if (sizeof($subscriptions) != (int) $socket_name){
    trigger_error("Sockets definitions must have consecutive numbers starting from 0", E_USER_ERROR);
  }
  $subscriptions[] = Array();
}
foreach($settings["fields"] as $field){
  $subscriptions[$field["socket"]][] = $field["codename"];
}
$subscriptions_json = json_encode($subscriptions);
$socket_defs_json = json_encode($socket_defs);

/*
  After parsing "subscriptions" will contain an array of
  machine/subscription numbers and the codenames that will be used
  from that machine, e.g:
    [0, [codename0, codename1], 2, [codename2, codename3]]
  And "socket_defs" will contain a list of hostname:port e:g:
    ['hostname1:8000', 'hostname1:8000']
 */

$head_script = <<<EOD
<script type="text/javascript">

window.onload = function() {

    // Convert php vars to java script
    var subscriptions = JSON.parse('$subscriptions_json');
    var socket_defs = JSON.parse('$socket_defs_json');

    // Setup websocket URI ...
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
	/* On ws open subscribe for data from machines (hostname:port) that are
	required for this page with the ws socket server.
        
        The ws server will respond with an echo of the request and the number
        of the subscription (one per hostname:port) and the sane interval. I.e.
        a request looks like:
          subscribe#hostname:port;codename0,codename1
        and the response looks like:
          subscribe#hostname:port;codename0,codename1#0#0.2
        */
	console.log("ws: Connected!");
	for (var n in subscriptions){
	    msg = "subscribe#".concat(socket_defs[n], ";", subscriptions[n].join(','));
	    console.log("ws: Subscribe on machine: ".concat(msg));
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

    function parse_subscription(string) {
	/* Parse a subscription return string on the form:
             subscribe#rasppi25:8000;codename0,codename1#0#0.2
           where the last 0 and 0.2 are the subscription number and sane
           interval respectively
        */
	var split = data.split("#");
	// Schedule a ws send of the subscription number once every sane
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
	// Loop over the number of codenames in subscriptions[socket]
	for (var n in subscriptions[socket]){
	    // Form the HTML id on the form 0#codename0
	    var id = String(socket).concat("#", subscriptions[socket][n]);
	    // Get a date object from unixtime
	    var date = new Date(data[1][n][0] * 1000);
	    var value = data[1][n][1];
	    var time = iso_time(date);
	    var now = new Date()
	    var diff = (now - date);
	    document.getElementById(id).innerHTML = value;
	    document.getElementById(id + 'diff').innerHTML = diff;
	    document.getElementById(id + 'time').innerHTML = time;
	}

    }
    
    webSocket.onmessage = function(e) {
	/* ws onmessage: parse the message from JSON and act on it */
	document.getElementById("raw").innerHTML = e.data
	//console.log("message received: " + e.data);  // LOTS of output
	data = JSON.parse(e.data)
	if (typeof(data) == "string"){
	    if (data.indexOf("subscribe") == 0){
		// The string is a subscription response
		parse_subscription(data);
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

echo(html_header($root="../", $title="Live Values", $includehead=$head_script));
?>
<br>
<table class="nicetable">
<tr><th>Name</th><th>Time</th><th>Value</th><th>Delay [ms]</th><th>Measurement host</th></tr>

<?php
foreach($settings["fields"] as $field){
  $location = $socket_defs[(int) $field["socket"]];
  $id = $field["socket"] . "#" . $field["codename"];
  $diff_id = $field["socket"] . "#" . $field["codename"] . "diff";
  $time_id = $field["socket"] . "#" . $field["codename"] . "time";
  echo("<tr>");
  echo("<td>${field["name"]}</td>");
  echo("<td id=\"$time_id\"></td>");
  echo("<td class=\"nobr\"><b id=\"$id\"></b> <b>${field["unit"]}</b></td>");
  echo("<td id=\"$diff_id\"></td>");
  echo("<td>($location)</td>");
  echo("</tr>");
}
?>

</table>

<a href="javascript:toggle('raw')"><h2>Raw return values</h2></a></td><td>
<p id="raw" style="display:none"></p>
<?php echo(html_footer());?>