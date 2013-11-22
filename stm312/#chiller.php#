<html>
<head>
   <meta http-equiv="refresh" content="15;url=http://cinfdata.fysik.dtu.dk/stm312/chiller.php">
   <title>STM312 chiller</title>
   <link rel="StyleSheet" href="../css/style.css" type="text/css" media="screen" />
</head>
<body>

<?php
  include("../common_functions_v2.php");
  echo(html_header());

//  echo("<h2>STM312/Omicron XPS chiller</h2>");

  $from = date("Y-m-d H:i:s", time() - 24*60*60); 
  $to = date("Y-m-d H:i:s", time()); 

  echo("<div style='float:right'><img width='650' src='https://cinfdata.fysik.dtu.dk/chillers/plot.php?type=multidateplot_stm312&from=" . $from . "&to=" . $to . "&left_ymax=0&left_ymin=0&right_ymax=0&right_ymin=0&left_logscale=&right_logscale=&matplotlib=checked&left_plotlist[]=1&left_plotlist[]=2&left_plotlist[]=3&right_plotlist[]=4&right_plotlist[]=5&image_format=png'></div>");

  $fp = stream_socket_client("udp://rasppi20:9759", $errno, $errstr);
  if (!$fp) {
    echo "ERROR: $errno - $errstr<br />\n";
  }

  if(isset($_POST['onoff'])){
    $turn_onoff = $_POST['onoff'];
    fwrite($fp, $turn_onoff);
    fread($fp, 26);
  }

  echo("<div style='float:left'>");
  echo("<b>Status: ");
  fwrite($fp, "read_status");
  $status = fread($fp, 26);
  echo($status);
  echo("</b><br>");


  if(isset($_POST['setpoint'])){
    $setpoint_value = $_POST['setpoint'];
    fwrite($fp, "set_setpoint" . $setpoint_value);
    fread($fp, 26);
  }

  if($status == 'On'){
    echo("<form method='post'>Setpoint<input type='number' step='1' size='4' name='setpoint' value='");
    fwrite($fp, "read_setpoint");
    echo fread($fp, 26);
    echo("'>C <input type='submit' value='update'>");
    echo("<br>");

    echo("Temperature: ");
    fwrite($fp, "read_temperature");
    echo fread($fp, 26);
    echo(" C<br>");

    echo("Flow rate: ");
    fwrite($fp, "read_flow_rate");
    echo fread($fp, 26);
    echo(" LPM<br>");

    echo("Pressure: ");
    fwrite($fp, "read_pressure");
    echo fread($fp, 26);
    echo(" bar<br>");

    echo("Ambient temperature: ");
    fwrite($fp, "read_ambient_temperature");
    echo fread($fp, 26);
    echo(" C<br>");
  }

  echo("</div>");
  echo("<br clear='all'>");
  echo("<hr>");

  echo("<div style='float:left'>");
  echo("<form method='post'>");
  echo("<input type='radio' name='onoff' value='turn_on'>On");
  echo("<input type='radio' name='onoff' value='turn_off'>Off<br>");
  echo("<input type='submit' value='Do something!'>");
  echo("</form>");
  echo("</div>");
  echo("<br clear='all'>");

  echo("<hr>");

  fclose($fp);
  echo(html_footer());
?>