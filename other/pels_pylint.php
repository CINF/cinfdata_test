<?php
#include("graphsettings.php");
include("../common_functions_v2.php");
$dbi = std_dbi();

# Load in pylint error message descriptions
$msgs_raw = file_get_contents("msgs_json");
$msgs = json_decode($msgs_raw, true);

function items_from_query($query, $types){
  /* Returns a types array of data base results from a query */
  global $dbi;
  $result = $dbi->query($query);
  $data = Array();
  while($row = $result->fetch_row()) {
    $typed_row = Array();
    for($i=0; $i < count($row); $i++) {
      switch ($types[$i]){
      case "int":
	$typed_row[$i] = (int) $row[$i];
      default:
	$typed_row[$i] = $row[$i];

      }
    }
    $data[] = $typed_row;
  }
  return $data;
}  


echo(html_header());

# Header section

echo("<h1>PyExpLabSys Pylint statistics</h1>\n");

echo("<p>This page has statistics from the last Pylint run on all the Python
source code in PyExpLabSys. The page has statistics for the number of
errors <u><a style=\"color:blue\" href=\"#files\">per file</a></u> and
per <u><a style=\"color:blue\" href=\"#errors\">error type</a></u>.</p>\n");



# Files section
$query = "select date(time), identifier, value from pylint where " .
  "date(time)=(SELECT max(date(time)) FROM pylint) and isfile=1 " .
  "order by value desc;";
$files = items_from_query($query, Array(null, "int", null));
echo("<h2 id=\"files\">File statistics: " . $files[0][0] . "</h2>\n");

# Make table for files
echo("<table class=\"nicetable\"\n");
echo("<tr><th>File</th><th>Number of errors</th></tr>\n");
foreach($files as $file){
  echo("<tr><td>{$file[1]}</td><td>{$file[2]}</td></tr>\n");
}
echo("</table>\n");
echo("\n");

# Error type section
$query = "select date(time), identifier, value from pylint where " .
  "date(time)=(SELECT max(date(time)) FROM pylint) and isfile=0 " .
  "order by value desc;";
$errors = items_from_query($query, Array(null, "int", null));
echo("<h2 id=\"errors\">Error statistics: " . $errors[0][0] . "</h2>\n");

# Make table for errors
echo("<table class=\"nicetable\"\n");
echo("<tr><th>Error code</th><th>Codename</th><th>Short description (hover for complete description)</th><th>Number of errors</th></tr>\n");
foreach($errors as $error){
  $error_desc = $msgs[$error[1]];
  $short_description = htmlentities($error_desc['short_description']);
  $description = htmlentities($error_desc['description']);
  echo("<tr>\n");
  echo("<td>{$error[1]}</td>\n");
  echo("<td>{$error_desc['codename']}</td>\n");
  echo("<td title=\"$description\">$short_description</td>\n");
  echo("<td>{$error[2]}</td>\n");
  echo("</tr>\n");
}
echo("</table>\n");
echo("\n");

echo(html_footer());
?>