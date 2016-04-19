<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script> 

<script type="text/javascript" src="https://cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/plug-ins/1.10.11/sorting/natural.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.11/css/jquery.dataTables.min.css">

<script type="text/javascript">
   var table = $(document).ready( function () {
       $('#table_id').DataTable({
	 "columnDefs": [
			{ "type": "natural", targets: 1 },
			{ "type": "natural", targets: 2 }
                       ]
	   })
     } );
</script>

</head>

<body>
<?php
$file = file_get_contents('http://robertj/hosts.php');
$output = explode("\n",$file);

echo("<table id=\"table_id\" class=\"display compact\" data-paging='false' data-searching='false'>\n");
echo("<thead>");
echo("<th>&nbsp;</th><th style='font-size:12px'>Hostname</th><th style='font-size:12px'>Uptime</th><th style='font-size:12px'>Load</th><th style='font-size:12px'>Setup</th><th style='font-size:12px'>Description</th><th style='font-size:12px'>OS</th><th style='font-size:12px'>Git</th><th style='font-size:12px'>Temp.</th><th style='font-size:12px'>Python</th><th style='font-size:12px'>Model</th>");
echo("</thead>\n\n<tbody>");

for ($i=0;$i<sizeof($output)-2;$i++){
  $row = explode('|',$output[$i]);
  echo("<tr id=" . $i . ">");
  $color = ($row[1] == 0) ? "\"#FF0000\"" : "\"#00FF00\"";
  echo("<td><font color=" . $color . ">&#8226;</font></td>");
  echo("<td  style='font-size:10px'>" . $row[0] . "</td>");
  if ($row[1] == 0){
    #echo("<td colspan=2><b>Host is down</b></td>");
    echo("<td  style='font-size:10px'><b>Host is down</b></td><td style='font-size:10px'><b>Host is down</b></td>");
  }else{
       echo("<td  style='font-size:10px'>" . $row[2] . "</td><td style='font-size:10px'>" . $row[3] . "</td>");
  }
  echo("<td style='font-size:10px'>" . $row[4] . "</td>");
  echo("<td style='font-size:10px'>" . $row[5] . "</td>");
  echo("<td style='font-size:10px'>" . $row[6] . "</td>");
  echo("<td style='font-size:10px'>" . $row[7] . "</td>");
  echo("<td style='font-size:10px'>" . $row[8] . "</td>");
  echo("<td style='font-size:10px'>" . $row[10] . "</td>");
  if (strpos($row[9], '(') == True){
    echo("<td  style='font-size:10px'>" . substr($row[9],0, strpos($row[9], '(')) . "</td>");
    }
    else{
  echo("<td  style='font-size:10px'>" . $row[9] . "</td>");
    }
  echo("</tr>\n");
}
echo("</tbody></table>");
?>
</body>
</html>
