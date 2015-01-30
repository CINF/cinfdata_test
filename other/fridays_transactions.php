<?php
#include("graphsettings.php");
include("../common_functions_v2.php");
$dbi = std_dbi();

echo(html_header());

# Transactions section
echo("<h1>Transactions</h1>\n\n");

# Start table
echo("<table class=\"nicetable\">\n");

# Headers
$headers = Array("ID", "User Barcode", "Time", "Amount", "Item Barcode", "User ID");
echo("<tr>\n");
foreach($headers as $header){
  echo("<th>" . $header . "</th>");
}
echo("<tr>\n");

# Rows
$query = "select * from fridays_transactions order by time desc limit 100";
$result = $dbi->query($query);
while($row = $result->fetch_row()){
  echo("<tr>\n");

  foreach($row as $item){
    echo("<td>" . htmlentities($item) . "</td>");
  }

  echo("</tr>\n");
}

# End table
echo("</table>\n");

echo(html_footer());
?>