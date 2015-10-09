<?php

include("graphsettings.php");
include("../common_functions_v2.php");
$db = std_db();

echo(html_header());
?>

<script src="sortable-0.5.0/js/sortable.min.js"></script>
<link rel="stylesheet" href="sortable-0.5.0/css/sortable-theme-finder.css" />

<?php

echo("\n\n");

echo("<h1>Dateplot Descriptions</h1>\n");
echo("<table class=\"sortable-theme-finder\" data-sortable>\n");
echo("<thead>\n");
echo("<th>id</th><th>Codename</th><th>Description</th>\n");
echo("</thead>\n\n");
echo("<tbody>\n");

$query = "select id, codename, description from dateplots_descriptions order by id";
$result  = mysql_query($query, $db);
while ($row = mysql_fetch_array($result)){
  echo("<tr>");
  echo('<td>' . $row[0] . '</td>');
  echo('<td>' . $row[1] . '</td>');
  echo('<td>' . $row[2] . '</td>');
  echo("</tr>\n");
}
echo("</tbody></table>");


echo("</pre>");

echo("\n\n");
echo(html_footer());
?>