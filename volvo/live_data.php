<html>
<head>
<meta http-equiv="refresh" content="5;url=http://cinfdata.fysik.dtu.dk/volvo/live_data.php"> 
</head>
<body>

<?php
$fp = stream_socket_client("udp://rasppi04:9000", $errno, $errstr);
if (!$fp) {
  echo "ERROR: $errno - $errstr<br />\n";
}

  if(isset($_POST['setpoint'])){
    $setpoint_value = $_POST['setpoint'];
    fwrite($fp, "set_setpoint " . $setpoint_value);
    fread($fp, 26);
  }


fwrite($fp, "json_wn");
$json_data = fread($fp, 260);
$json_vars = json_decode($json_data, True);

?>

<h1>Temperature:
<?php
  echo $json_vars['temperature'][1]
?>
 C
</h1>

<h1>Pressure:
<?php
  echo $json_vars['pressure'][1]
?>
 mBar
</h1>

<h1>Setpoint
<?php
    echo("<form method='post'><input type='number' step='1' size='4' name='setpoint' value='");
    fwrite($fp, "read_setpoint");
    echo fread($fp, 26);
    echo("'>C <input type='submit' value='update'>");
    echo("<br>");
?>
</h1>


<h1>Sample Current:
<?php
  fwrite($fp, "read_samplecurrent");
  $val = fread($fp, 26);
echo ((float)$val)*1;
  
?>
 nA
</h1>

<?php
  fclose($fp);
?>
</body>