<?php
include("../common_functions_v2.php");

# Create data base connection
$db = std_db();
mysql_set_charset('utf8', $db);
echo(html_header());

$setup = $_GET['setup'];

#This will be the query to use when real data is inserted
$query = 'select time, user, pictureid, login from picture_logbooks where setup = "' . $setup . '" order by time desc';
$result = mysql_query($query, $db);

echo('<table border=0> ' . PHP_EOL);
while ($row = mysql_fetch_array($result)){
  if ($row[3] == 0){
    echo('<tr><td valign="top">' . PHP_EOL);
    echo('<b>Logout</b><br>' . PHP_EOL);
  }
  else{
    echo('</td>' . PHP_EOL . '<td valign="top">' . PHP_EOL);
    echo('<b>Login</b><br>' . PHP_EOL);
  }
  echo('<p><b>User:</b><br> ' . $row[1] . '</p>' . PHP_EOL);
  echo('<b>Time:</b><br> ' . $row[0] . '<br>' . PHP_EOL);
  echo('</td>' . PHP_EOL . '<td>');
  echo('<img src=picture.php?id=' . $row[2] . '>' . PHP_EOL);
  if ($row[3] == 0){
    echo('</td><td>');
  }
  else{
    echo('</td></tr>' . PHP_EOL . PHP_EOL);
  }
}
echo('</table>');
echo(html_footer());
?>