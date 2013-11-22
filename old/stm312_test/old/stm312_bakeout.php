<?
$db = mysql_connect("localhost", "root", "CINF123");  
mysql_select_db("new_db",$db);

$query = "SELECT temperature FROM temperature_stm312 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$temperature = $row[0];

$query = "SELECT time FROM temperature_stm312 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$temperature_time = $row[0];

$query = "SELECT pressure FROM pressure_stm312 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$pressure = $row[0];

$query = "SELECT time FROM pressure_stm312 order by time desc limit 1";
$result  = mysql_query($query,$db);  
$row = mysql_fetch_array($result);
$pressure_time = $row[0];
?>

<html>
<head>
<title>Bakeout status page STM 312</title>
</head>
<body>
 <table border="0">
    <tr>
      <td><img src="oven.jpg"></td>
      <td><h1>Overview of the bakeout status STM 312</h1></td>
      <td><img src="setup.jpg"></td>
    </tr>
  </table>
<h2>Current temperature: <?=round($temperature,2)-273.15?> &deg;C, at <?=$temperature_time?></h2>
<h2>Current pressure: <?=$pressure?> torr, at <?=$pressure_time?></h2>
<img src="pressuresmall.png"><img src="temperaturesmall.png"><br>
<h1>Full size graphs</h1>
<img src="pressure.png"><br><img src="temperature.png">
</body>
</html>
