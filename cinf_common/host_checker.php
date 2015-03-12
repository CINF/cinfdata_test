<html>
<head>

<script src="sortable-0.5.0/js/sortable.min.js"></script>
<link rel="stylesheet" href="sortable-0.5.0/css/sortable-theme-finder.css" />

<style>
.down {
background: #ff0000;
width: 15px;
height: 15px;
border-radius: 50%;
}
</style>

<style>
.up {
background: #00ff00;
width: 15px;
height: 15px;
border-radius: 50%;
}​
</style>

</head>

<body>
<?php
#exec('python snapshot.py',$output);
#exec('python host_status.py',$output);
#passthru('python host_status.py');
$file = file_get_contents('http://robertj/hosts.php');
$output = explode("\n",$file);

#echo("<table border=1 padding=5>");
#echo("<tr><th>&nbsp;</th><th>Hostname</th><th>Uptime</th><th>Load</th><th>Setup</th><th>Description</th><th>OS</th></tr>");

echo("<table class=\"sortable-theme-finder\" data-sortable>\n");
echo("<thead>");
echo("<th>&nbsp;</th><th>Hostname</th><th>Uptime</th><th>Load</th><th>Setup</th><th>Description</th><th>OS</th><th>Git</th>");
echo("</thead><tbody>");

for ($i=0;$i<sizeof($output)-2;$i++){
  $row = explode(';',$output[$i]);
  echo("<tr>");
  $color = ($row[1] == 0) ? "\"#FF0000\"" : "\"#00FF00\"";
  $value = ($row[1] == 0) ? "0" : "1";

  #echo("<td class=" . $color . ">&nbsp;</td>");
  echo("<td data-value=\"" . $value . "\"><font color=" . $color . ">&#8226;</font></td>");
  echo("<td>" . $row[0] . "</td>");
  if ($row[1] == 0){
    echo("<td colspan=2><b>Host is down</b></td>");
  }else{
       echo("<td>" . $row[2] . "</td><td>" . $row[3] . "</td>");
  }
  echo("<td>" . $row[4] . "</td>");
  echo("<td>" . $row[5] . "</td>");
  echo("<td>" . $row[6] . "</td>");
  echo("<td>" . $row[7] . "</td>");
  echo("</tr>\n");
}
echo("</tbody></table>");
?>
</body>
</html>
