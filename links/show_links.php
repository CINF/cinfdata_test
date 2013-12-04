<?php
include("../common_functions.php");
include("graphsettings.php");
$db = std_db();

echo(new_html_header());

$query = "SELECT id,url,comment FROM short_links"; 
$result = mysql_query($query,$db);
echo("<table border='1' class=\"links\">"); 
echo("<tr><td><b>Id</b></td><td><b>Comment</b></td><td><b>Link</b></td></tr>"); 
while($row = mysql_fetch_array($result)) { 
  echo("<tr><td>"); 
  echo($row['id']); 
  echo("</td><td>"); 
  echo($row['comment']);
  echo("</td><td>");
  echo("<a href=\"http://www.cinfdata.fysik.dtu.dk/links/link.php?id=" . $row['id'] . "\">cinfdata.fysik.dtu.dk/links/link.php?id=" . $row['id'] . "</a>");
  echo("</td></tr>");
} 
echo("</table>");

echo(new_html_footer());
?>
