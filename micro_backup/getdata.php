<?php
$query=$_GET["query"];

$connection = mysql_connect("localhost", "root", "CINF123");
mysql_select_db("new_db", $connection);
$sql="SELECT * FROM measurements_bifrost WHERE id = '".$query."'";
$result = mysql_query($sql);

echo "<table border='1'>
<tr>
<th>id</th>
<th>type</th>
<th>timestep</th>
<th>pass</th>
<th>comment</th>
</tr>";

while($row = mysql_fetch_array($result))
  {
  echo "<tr>";
  echo "<td>" . $row['id'] . "</td>";
  echo "<td>" . $row['type'] . "</td>";
  echo "<td>" . $row['timestep'] . "</td>";
  echo "<td>" . $row['pass'] . "</td>";
  echo "<td>" . $row['comment'] . "</td>";
  echo "</tr>";
  }
echo "</table>";

mysql_close($connection);
?>