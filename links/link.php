<?php

include("../common_functions_v2.php");
$db = std_db();

$URL = $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
$REFERER = $_SERVER["HTTP_REFERER"];
$comment = $_POST["comment"];

echo(html_header());

if($_GET["url"] == "checked") {
  $query = "INSERT INTO short_links (url,comment) VALUES (\"" . $REFERER . "\",\"" . $comment . "\")";
  $result  = mysql_query($query, $db);
  $query = "SELECT max(id) from short_links";
  $max_id = single_sql_value($db, $query, 0);
  echo("<br><b>Your full URL:</b> " . $REFERER . " <br> <b>Can now be reached at:</b> <a href=\"http://www.cinfdata.fysik.dtu.dk/links/link.php?id=" . $max_id . "\">http://www.cinfdata.fysik.dtu.dk/links/link.php?id=" . $max_id . "</a><br>");
  echo("<input type=\"button\" value=\"Back to Previous Page\" onClick=\"javascript: history.go(-1)\">");
} else {
  $id = $_GET["id"];
  $query = "SELECT url from short_links WHERE id=" . $id . "";
  $url = single_sql_value($db, $query, 0);
  header("Location: $url");
  exit;
}

echo(html_footer());

?>
