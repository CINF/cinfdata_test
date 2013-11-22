<?php
include("../common_functions.php");
include("graphsettings.php");

# Disable error from asking for value in the associative array that does not exist
$error_reporting_value = error_reporting(0);

# Parse the parameters
$type = $_GET['type'];
$from = $_GET['from'];
$to = $_GET['to'];
$xmin = $_GET['xmin'];
$xmax = $_GET['xmax'];
$ymin = $_GET['ymin'];
$ymax = $_GET['ymax'];
$as_function_of_t = $_GET['as_function_of_t'];
$logscale = $_GET['logscale'];
$shift_temp_unit = $_GET['shift_temp_unit'];
$flip_x = $_GET['flip_x'];
$shift_be_ke = $_GET['shift_be_ke'];
$image_format = $_GET['image_format'];
$small_plot = $_GET['small_plot'];
# Ids
$exported_ids = $_GET['idlist'];
$exported_offsets = $_GET['offsetlist'];

# Re-enable error reporting
error_reporting($error_reporting_value);

$settings = plot_settings($type);

# Build easily parsable list of id's
$idlist = '';
if (count($exported_ids) > 0){
    foreach($exported_ids as $id){
        $idlist .= ','.$id;
    }
}

# Build easily parsable key,value pair of offsets
$offsetlist = '';
if (count($exported_offsets) > 0){
  foreach($exported_offsets as $key=>$value){
    $offsetlist .= ','.$key.':'.$value;
  }
}

// Call python plot backend
$command = './plot.py --type ' . $type .
  ' --idlist "' . $idlist . '"'.
  ' --from_d "' . (string)$from . '"'.
  ' --to_d="' . $to . '"'.
  ' --xmin "' . $xmin . '"'.
  ' --xmax "' . $xmax . '"'.
  ' --ymin "' . $ymin . '"'.
  ' --ymax "' . $ymax . '"'.
  ' --offset "' . $offsetlist . '"'.
  ' --as_function_of_t "' . $as_function_of_t . '"'.
  ' --logscale "' . $logscale . '"'.
  ' --flip_x "' . $flip_x . '"'.
  ' --shift_be_ke "' . $shift_be_ke . '"'.
  ' --shift_temp_unit "' . $shift_temp_unit . '"'.
  ' --image_format "' . $image_format . '"'.
  ' --small_plot "' . $small_plot . '"'.
  ' 2>&1';

if ($image_format == "default"){
  $image_format = $settings["image_format"];
}

exec($command, $command_output);

# If there is only one line of output, that is the filename of the figure we
# want to show ...
if (count($command_output) == 1){
  $handle = fopen($command_output[0], "rb");
  $data = fread($handle, filesize($command_output[0]));
  switch ($image_format) {
  case "":
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
  case "ps":
    header('Content-type: application/postscript');
    header('Content-Disposition: attachment;filename="plot.ps"');
    break;
  case "pdf":
    header('Content-type: application/pdf');
    header('Content-Disposition: attachment;filename="plot.pdf"');
    break;
  default:
    header('Content-type: image/png');
    break;
  } 
  echo($data);
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
