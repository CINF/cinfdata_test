<?php
header( 'Content-Type: image/jpeg', true );
include("../common_functions_v2.php");
# Create data base connection
$db = std_db();
#mysql_set_charset('utf8', $db);

$id_number = $_GET['id'];

$query = "SELECT data from binary_data WHERE id = " . $id_number;
$result = mysql_query($query, $db);
$row = mysql_fetch_array($result);
echo($row[0]);
?>