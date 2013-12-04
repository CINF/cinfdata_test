<?php
include("../common_functions.php");
include("graphsettings.php");

// Call python plot backend
$command = './fig_test.py 2>&1';

/* File type magic numbers, see:
   http://en.wikipedia.org/wiki/Magic_number_(programming)

   NOTE: eps and ps has same magic numbers, but in version 2 we only provide
   eps, so this will not cause problems
 */

#$magic_numbers = array(
#		       "png" => array(8, "89504e470d0a1a0a"),
#		       "pdf" => array(4, "25504446"),
#		       "eps" => array(2, "2521"),
#		       )
#echo('j');
if (1 == 1){
  #header('Content-type: image/png');

  ob_start();
  passthru($command, $command_output);
  $content_grabbed=ob_get_contents();
  ob_end_clean();

  echo($command_output . "#");

  $h = substr($content_grabbed, 0, 8);
  #echo(strlen($h));
  #echo("\n");

  #for ( $i = 0; $i < 8; $i++){
  #  echo(' ' . bin2hex(substr($h, $i, 1)));
  #}

  echo(bin2hex($h));
  exit();



  #89504e470d0a1a0a

  if ( substr($content_grabbed[0], 0, 8) == "\211PNG\r\n\032\n" ){
      echo("succes");
    } else {
      echo("fail");
    }

  #$lines = count($command_output);

  #echo(gettype($command_output));

  # PNG Type check; see http://en.wikipedia.org/wiki/Magic_number_(programming)
  #if ($command_output[0] . $command_output[1] == "\211PNG\032"){
  #  echo ("succes");
  #} else {
  #  echo('Fail');
  #}

  #echo('exit');
  exit();

  for ( $n = 0 ; $n < $lines ; $n += 1 ){
    if ( $n == 0 ){
      # First line needs a carrige return
      echo($command_output[$n] . "\r\n");
    } else if ( $n == $lines -1) {
      # Last line needs no line shift
      print($command_output[$n]);
    } else {
      # Everything else needs an ordinary newline
      echo($command_output[$n] . "\n");
    }
  }
}
# ... otherwise, something has produced more output, either intentionally
# with print or error messages, either we want to se it in stead of the
# figure
else
{
    #header('Content-type: text/plain');
    echo('<pre>');
    for($i=0;$i<count($command_output);$i++){
	    echo($command_output[$i].'</br>');
    }
    echo('</pre>');
}

?>
