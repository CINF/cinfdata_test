<?php
include("../common_functions.php");
include("graphsettings.php");

#exec('python snapshot.py',$output);                                                                                                                                                     
#exec('python host_status.py',$output);                                                                                                                                                  
#passthru('python host_status.py');                                                                                                                                                      
$file = file_get_contents('http://robertj/hosts.php');
$output = explode("\n",$file);

?>

<html>
<head>
<title>CINF data logging</title>
<style title="css" type="text/css">@import "../style.css";</style>
<style>
.down {
background: #ff0000;
width: 15px;
height: 10px;
border-radius: 50%;
}

.up {
background: #00ff00;
width: 15px;
height: 10px;
border-radius: 50%;
}​
</style>
</head>
<body>
	
<h1 id="commonstatus">Status of CINF servers</h1>

<img src="../cinf_logo_web.png" id="logo">

<h2 id="commonstatustitle">Servers</h2>


<table border=1 padding=5>
  <tr>
   <th>&nbsp;</th>
   <th>Hostname</th>
   <th>Uptime</th>
   <th>Description</th>
   <th>OS</th>
  </tr>

<?php
for ($i=0;$i<sizeof($output)-2;$i++){
  $row = explode(';',$output[$i]);
  echo("<tr>");
  $color = ($row[1] == 0) ? "\"down\"" : "\"up\"";
  echo("<td class=" . $color . ">&nbsp;</td>");
  echo("<td>" . $row[0] . "</td>");
  $uptime = ($row[1] == 0) ? "<b>Host is down</b>" : $row[2];
  echo("<td>" . $uptime . "</td>");
  echo("<td>" . $row[3] . "</td>");
  echo("<td>" . $row[4] . "</td>");
  echo("</tr>\n");
}

?>
</table>

</body>
</html>
