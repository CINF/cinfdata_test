<?php
include("../common_functions.php");
include("graphsettings.php");

error_reporting (E_ALL ^ E_NOTICE);

# Get settings and initiate argument variables
$settings = plot_settings($_GET['type']);
$boolean_options='';

### Common
# Booleans
foreach (array('left_logscale', 'right_logscale', 'matplotlib', 'small_plot',
	       'diff_left_y', 'linscale_left_y2', 'linscale_left_y1',
	       'linscale_left_y0', 'as_function_of', 'flip_x', 'linscale_x0',
	       'linscale_x1', 'linscale_x2', 'linscale_right_y0',
	       'linscale_right_y1', 'linscale_right_y2',
	       'diff_right_y') as $value){
  $boolean_options .= ',' . $value . ':' . $_GET[$value];
}

# Scales
$left_yscale_bounding = $_GET['left_ymin'] . ',' . $_GET['left_ymax'];
$right_yscale_bounding = $_GET['right_ymin'] . ',' . $_GET['right_ymax'];
$xscale_bounding = $_GET['xmin'] . $_GET['xmax'];
# Strings
$image_format = $_GET['image_format'];
$manual_labels_n_titel = 'title=' . $_GET['title'] . ',xlabel=' . $_GET['xlabel'] .
  ',left_ylabel=' . $_GET['left_ylabel'] . ',right_ylabel=' . $_GET['right_ylabel'];
# Plotlists
$left_plotlist = ''; $right_plotlist = '';
foreach (array('left_plotlist', 'right_plotlist') as $list){
  $$list = '';
  if (count($_GET[$list]) > 0){
    foreach($_GET[$list] as $id){
      $$list .= ',' . $id;
    }
  }
}

### Dateplot specific
$from_to  = $_GET['from'] . ',' . $_GET['to'];


# Call python plot backend
$command = './plot.py --type ' . $_GET['type'] .
  ' --boolean_options "' . $boolean_options . '"' .
  ' --left_plotlist "' . $left_plotlist . '"' .
  ' --right_plotlist "' . $right_plotlist . '"' .
  ' --xscale_bounding "' . $xscale_bounding . '"' .
  ' --left_yscale_bounding "' . $left_yscale_bounding . '"' .
  ' --right_yscale_bounding "' . $right_yscale_bounding . '"' .
  ' --from_to "' . $from_to . '"' .
  ' --image_format "' . $image_format . '"' .
  ' --manual_labels_n_titel "' . $manual_labels_n_titel . '"' .
  ' 2>&1';

# Grab raw output of python plotting command
ob_start();
passthru($command, $return_code);
$content_grabbed=ob_get_contents();
ob_end_clean();

# Python command generated an error, show output as clear text
if ($return_code > 0){
  if ($_GET['matplotlib'] == "checked"){echo("<pre>\n");}
  echo($content_grabbed);
  if ($_GET['matplotlib'] == "checked"){echo("</pre>");}
  exit();
}

# If dygraph output the javascript code
if ($_GET['matplotlib'] != "checked"){
  header('Content-type: text/javascript');
  echo($content_grabbed);
  exit();
}

/* If figure file, determine type and output appropriate header and the content

   File type magic numbers, see:
   http://en.wikipedia.org/wiki/Magic_number_(programming)

   NOTE: eps and ps has same magic numbers, but in version 2 we only provide
   eps, so this will not cause problems
 */
$magic_numbers = array(
		       "png" => array(8, "89504e470d0a1a0a"),
		       "pdf" => array(4, "25504446"),
		       "eps" => array(2, "2521"),
		       );

$content_type = "unknown";
foreach ($magic_numbers as $key => $value){
  $extract = bin2hex(substr($content_grabbed, 0, $value[0]));
  if ( $extract == $value[1]){
    $content_type = $key;
  }
}
# If we have not detected any binary file types, check if it is svg
if ($content_type == "unknown"){
  $xml = @simplexml_load_string($content_grabbed);
  if($xml != FALSE) {
    if (strtolower($xml->getName()) == "svg"){
      $content_type = "svg";
    }
  }
}

if ($_GET['debug'] == 'checked'){
  $content_type = 'debug';
}

switch ($content_type) {
case "png":
  header('Content-type: image/png');
  break;
case "svg":
  header('Content-type: image/svg');
  header('Content-Disposition: attachment;filename="plot.svg"');
  break;
case "eps":
  header('Content-type: application/postscript');
  header('Content-Disposition: attachment;filename="plot.eps"');
  break;
case "pdf":
  header('Content-type: application/pdf');
  header('Content-Disposition: attachment;filename="plot.pdf"');
  break;
case "debug":
  header('Content-type: text/html');
  break;
case "unknown":
  # This case is what happens if we have generated extra output from python
  header('Content-type: text/plain');
  break;
}
echo($content_grabbed);

# ... otherwise, something has produced more output, either intentionally
# with print or error messages, either we want to se it in stead of the
# figure
#header('Content-type: text/plain');
#echo('<pre>');
#for($i=0;$i<count($command_output);$i++){
#  echo($command_output[$i].'</br>');
#}
#cho('</pre>');

?>
