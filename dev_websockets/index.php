<!DOCTYPE html>
<html>
  <head>
    <title>Websockets Test</title>
  </head>
  <body>

<?php
include("../common_functions_v2.php");
echo(html_header());
?>

    <h1>WebSocket Test</h1>
    <p>This page will tell you, live streamed from a rasppi18, sine to the epoch time and epoc time + pi. If you feel brave try to open a <a href="https://cinfdata.fysik.dtu.dk/dev_websockets/">second tab</a> ;)</p>
    <h2>Sines</h2>	
    <p id="sine1">Dummy1!</p>
    <p id="sine2">Dummy2!</p>
    <p id="time_iterations">Dummy3!</p>
    <div id="div_g" style="width:600px; height:300px;"></div>
    <script>
      function log(msg) {
      setTimeout(function() {
      throw new Error(msg);
      }, 0);
      }
      					
      var t = new Date();
      var data = [[t.getTime()/1000, 0, 0]];
      var g = new Dygraph(document.getElementById("div_g"), data,
                          {
                            drawPoints: true,
                            labels: ['d', 'u', 'p']
			  });

      var t0 = t.getTime();

      var ws = new WebSocket("ws://130.225.86.27:8888/websocket");
      ws.onopen = function() {
	  setInterval(function(){ws.send("130.225.86.187:9000;data")}, 100);
      };

      var first_call = true;
      function update_data(x0, y0, y1){
      if (first_call){
      data = [[x0, y0, y1]];
      first_call = false;
      } else {
      data.push([x0, y0, y1])
      data = data.slice(-100);
      }
      }

      ws.onmessage = function (evt) {
	  var splitup = evt.data.split(";");
	  document.getElementById("sine1").innerHTML = splitup[0];
	  item_split = splitup[0].split(":");
	  var x0 = parseFloat(item_split[0]);
	  var y0 = parseFloat(item_split[1]);
          var xd0 = new Date(x0*1000);
	  item_split = splitup[1].split(":");
	  var y1 = parseFloat(item_split[1]);
	  update_data(xd0,y0,y1);
          g.updateOptions( { 'file': data } );
	  document.getElementById("sine2").innerHTML = splitup[1];
          var t = new Date();
          var t1 = t.getTime();
          var diff = t1-t0;
          document.getElementById("time_iterations").innerHTML = (t1-t0).toString();
          t0 = t1;
      };




    </script>
  </body>
</html>
