<?php

  /* This file is used to document the code on the webpage */

include("../common_functions.php");

if (!(array_key_exists('file', $_GET) and array_key_exists('type', $_GET))){
  exit('Both parameters "file" and "type" are mandatory, "dir" is optional');
}

$basedir = '/var/www/cinfdata';
$dir = array_key_exists('dir', $_GET) ? $_GET['dir'] : 'sym-files';
$path = $basedir.'/'.$dir.'/'.$_GET['file'];
$type = $_GET['type'];

# To prevent command injection
if (!file_exists($path)){
  exit("This file \"$path\" does not exit");
}

# For php file we use the builtin highlight_string command
if ($type == 'php'){
  echo(html_code_header($dir.'/'.$_GET['file']));
  $lines = file($path);
  $code = implode('', $lines);
  highlight_string($code);
  echo(new_html_footer());
    }
# For python or xml we use the pygmentize command
elseif ($type == 'python' || $type == 'xml') {
  $command = "pygmentize -f html -O full -l " . $type . " " . $path;
  system($command, $command_output);
  echo(implode('', $command_output));
}
else {
  exit('Unknown type: ' . $type . '</br>You can use: php, python or '. 'xml');
}
?>