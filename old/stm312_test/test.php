<?php
#include("../common_functions.php");

echo('test');

exec('./gas_analysis.py 1128', $command_output);

#header('Content-type: text/plain');
echo('<pre>');
for($i=0 ; $i<count($command_output) ; $i++){
  echo($command_output[$i].'</br>');
}
echo('</pre>');

?>