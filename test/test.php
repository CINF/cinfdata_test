<?php

echo(error_reporting());
echo('<br>');

# Here we disable error reporting
$error_reporting_value = error_reporting(0);
echo(error_reporting());
echo('<br>');


echo($_GET['typed']);

error_reporting($error_reporting_value);
echo(error_reporting());
echo('<br>');



#$j = ini_get_all();
#print_r($j['error_reporting']);
#echo($j['error_reporting']['local_value']);
#echo('<br>');
#error_reporting(0);
#$j = ini_get_all();
#print_r($j['error_reporting']);
#echo('<br>');
#echo(error_reporting());









?>