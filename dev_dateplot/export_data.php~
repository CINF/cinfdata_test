<?php
include("../common_functions.php");

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
$shift_be_ke = $_GET['shift_be_ke'];
# Ids
$exported_ids = $_GET['idlist'];
$exported_offsets = $_GET['offsetlist'];

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

// Call python export backend
$command = './export_data.py --type ' . $type .
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
  ' --shift_temp_unit "' . $shift_temp_unit . '"'.
  ' --shift_be_ke "' . $shift_be_ke . '"'.
  ' 2>&1';

exec($command, $command_output);

header("Content-type: text/plain");
#echo('<pre>');
for($i=0;$i<count($command_output);$i++){
  echo($command_output[$i]."\n");
}
#echo('</pre>');

?>
