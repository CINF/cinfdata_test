<?php
$handle = fopen("test.png", "rb");
$data = fread($handle, filesize("test.png"));
#header('Content-type: image/png');
echo($data);
?>

