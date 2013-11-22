<html>
<head>
</head>
<body>

<?php
$fp = stream_socket_client("udp://130.225.87.230:9999", $errno, $errstr);
if (!$fp) {
  echo "ERROR: $errno - $errstr<br />\n";
}
?>

<h1>Temperature 1:
<?php
  fwrite($fp, "read_temperature_1");
  echo fread($fp, 26);
?>
 C
</h1>

<h1>Temperature 2:
<?php
  fwrite($fp, "read_temperature_2");
  echo fread($fp, 26);
?>
 C
</h1>

<h1>Temperature outside:
<?php
  fwrite($fp, "read_temperature_outside");
  echo fread($fp, 26);
  
?>
 C
</h1>

<?php
  fclose($fp);
?>
</body>
