<?php
include("../common_functions_v2.php");

$text = isset($_GET["text"]) ? $_GET["text"] : "";
$columns = isset($_GET["columns"]) ? $_GET["columns"] : 2;

echo(html_header($root="../", $page_title="Data viewer", $includehead="", $charset='latin-1'));
echo("<form action=\"\">\n");
echo("<textarea name=\"text\" cols=\"40\" rows=\"5\" ... >" . htmlentities($text) . "</textarea><br>");
echo("Number of columns<br>");
echo("<input type=\"number\" name=\"columns\" min=\"1\" value=$columns></br>");
echo("<input name=\"action\" type=\"submit\" value=\"Submit\">");
echo("</form>\n");
echo("<h1>Generated table</h1><br>");

echo("<p><tt>");
foreach(explode("\n", $text) as $name){
  $name = str_replace( "\n", "", $name);
  echo("|| " . str_pad($name, 20, "   ") . str_repeat(" ||", $columns) . "<br>\n");
}
echo("</tt></p>");

echo(html_footer());

?>