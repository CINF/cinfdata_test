<?php
include("graphsettings.php");
include("../common_functions_v2.php");
echo(html_header());
$db = std_db();

function file_element($file){
  $bad_names = Array("..", ".", "dygraph", "dygraph_old", "graphsettings.xml", "dygraphs", "xyplot.php.BAK");
  if (in_array($file, $bad_names)){
    return 0;
  }
  $bad_endings = Array("~", "#");
  if (in_array(substr($file, -1), $bad_endings)){
    return 0;
  }
  $types = Array("py"=>"python", "php"=>"php");
  $split = explode(".", $file);
  $type = $types[$split[1]];
  $lines = count(file("../sym-files2/{$file}")) - 1;
  echo("<tr>\n");
  echo "<td><a href=\"../code-documentation/code.php?dir=sym-files2&file=$file&type=$type\">$file</a></td><td>$lines</td>\n";
  echo("</tr>\n");
  return $lines;
}

function byteFormat($bytes, $unit = "", $decimals = 2) {
  # Borrowed from http://www.if-not-true-then-false.com/2009/format-bytes-with-php-b-kb-mb-gb-tb-pb-eb-zb-yb-converter/
  $units = array('B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4, 
		 'PB' => 5, 'EB' => 6, 'ZB' => 7, 'YB' => 8);
 
  $value = 0;
  if ($bytes > 0) {
    // Generate automatic prefix by bytes 
    // If wrong prefix given
    if (!array_key_exists($unit, $units)) {
      $pow = floor(log($bytes)/log(1024));
      $unit = array_search($pow, $units);
    }
 
    // Calculate byte value by prefix
    $value = ($bytes/pow(1024,floor($units[$unit])));
  }
 
  // If decimals is not numeric or decimals is less than 0 
  // then set default value
  if (!is_numeric($decimals) || $decimals < 0) {
    $decimals = 2;
  }
 
  // Format output
  return sprintf('%.' . $decimals . 'f '.$unit, $value);
}

?>
<div class=\"statistics\">
<p>This page contains various statistics for the database and for the
data presentation webpage code</p>

<h1>Database statistics</h1>

<?php
  $max_index = pow(2, 32);

echo("" . 
"<table border=\"1\">\n" .
"  <tr><th>Name</th><th>Table full</th><th>Size</th></tr>\n");
$sum = 0;
$max = 0;
$query = "show table status";
$result  = mysql_query($query, $db);
while ($row = mysql_fetch_array($result)){
  $row_sum = $row["Data_length"] + $row["Index_length"];
  $sum += $row_sum;
  $fraction_of_index = (float)$row['Auto_increment'] / (float)$max_index * 100.0;
  $max = max($max, $fraction_of_index);
  echo("  <tr>\n");
  echo("    <td>{$row['Name']}</td>");
  echo("    <td>" . number_format($fraction_of_index, 2, '.', ',') . "%</td>");
  echo("    <td>" . byteFormat($row_sum) . "</td>");
  echo("  </tr>\n");
}

  echo("  <tr>\n");
  echo("    <td><b>Max</b></td>");
  echo("    <td><b>" . number_format($max, 2, '.', ',') . "%</b></td>");
  echo("    <td></td>");
  echo("  </tr>\n");

  echo("  <tr>\n");
  echo("    <td><b>Total</b></td>");
  echo("    <td></td>");
  echo("    <td><b>" . byteFormat($sum) . "</b></td>");
  echo("  </tr>\n");

echo("</table>\n");

?>

<h1>Code statistics</h1>

<p>The data presentations webpage consist of the following files. Click the files to view the code.</p>


<table border="1" cellpadding="3">
<tr><th align="left">File</th><th align="left">Number of lines</th></tr>
<?php

if ($handle = opendir("../sym-files2")) {
    /* This is the correct way to loop over the directory. */
  $files = Array();
  while (false !== ($entry = readdir($handle))) {
    array_push($files, $entry);
  }
  closedir($handle);
  sort($files);
  $total_lines = 0;
  foreach($files as $file){
    $total_lines += file_element($file);
  }
  echo("<tr>\n<td><b>Total</b></td><td><b>$total_lines</b></td>\n</tr>\n");
}

?>
</table>

</div>
<?php echo(html_footer());?>
