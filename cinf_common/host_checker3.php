<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/t/dt/jq-2.2.0,dt-1.10.11,b-1.1.2,b-colvis-1.1.2,b-print-1.1.2,cr-1.3.1,fc-3.2.1,fh-3.1.1,kt-2.1.1,r-2.0.2,rr-1.1.1,sc-1.4.1,se-1.1.2/datatables.min.css"/>

<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.0.min.js"></script>
 
<script type="text/javascript" src="https://cdn.datatables.net/t/dt/jq-2.2.0,dt-1.10.11,b-1.1.2,b-colvis-1.1.2,b-print-1.1.2,cr-1.3.1,fc-3.2.1,fh-3.1.1,kt-2.1.1,r-2.0.2,rr-1.1.1,sc-1.4.1,se-1.1.2/datatables.min.js"></script>
<link rel="stylesheet" type="text/css" href="style.css">



<script type="text/javascript">
   $(document).ready( function () {
       $('#table_id').DataTable();
            "order": [[ 3, "desc" ]]

     } );
</script>
</head>

<body>
<?php
$file = file_get_contents('http://robertj/hosts.php');
$output = explode("\n",$file);

echo("<table id=\"table_id\" class=\"display compact\">\n");
echo("<thead>");
echo("<th>&nbsp;</th><th>Hostname</th><th>Uptime</th><th>Load</th><th>Setup</th><th>Description</th><th>OS</th><th>Git</th><th>Temp.</th><th>Python</th><th>Model</th>");
echo("</thead>\n\n<tbody>");

for ($i=0;$i<sizeof($output)-2;$i++){
  $row = explode('|',$output[$i]);
  echo("<tr>");
  $color = ($row[1] == 0) ? "\"#FF0000\"" : "\"#00FF00\"";
  $value = ($row[1] == 0) ? "0" : "1";

  echo("<td data-value=\"" . $value . "\"><font color=" . $color . ">&#8226;</font></td>");
  if (substr($row[0], 0, 6) == 'rasppi'){
    $sortval = (substr($row[0], 6));
  }
  else{
    $sortval = 1000;
  }
  echo("<td data-value=\"" . $sortval . "\">" . $row[0] . "</td>");
  if ($row[1] == 0){
    echo("<td colspan=2><b>Host is down</b></td>");
  }else{
       echo("<td>" . $row[2] . "</td><td>" . $row[3] . "</td>");
  }
  echo("<td>" . $row[4] . "</td>");
  echo("<td>" . $row[5] . "</td>");
  echo("<td>" . $row[6] . "</td>");
  echo("<td>" . $row[7] . "</td>");
  echo("<td>" . $row[8] . "</td>");
  echo("<td>" . $row[10] . "</td>");
  if (strpos($row[9], '(') == True){
    echo("<td>" . substr($row[9],0, strpos($row[9], '(')) . "</td>");
    }
    else{
  echo("<td>" . $row[9] . "</td>");
    }
  echo("</tr>\n");
}
echo("</tbody></table>");
?>
</body>
</html>
