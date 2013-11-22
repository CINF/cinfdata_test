<?php
  /*
    Copyright (C) 2012 Robert Jensen, Thomas Andersen and Kenneth Nielsen
    
    The CINF Data Presentation Website is free software: you can
    redistribute it and/or modify it under the terms of the GNU
    General Public License as published by the Free Software
    Foundation, either version 3 of the License, or
    (at your option) any later version.
    
    The CINF Data Presentation Website is distributed in the hope
    that it will be useful, but WITHOUT ANY WARRANTY; without even
    the implied warranty of MERCHANTABILITY or FITNESS FOR A
    PARTICULAR PURPOSE.  See the GNU General Public License for more
    details.
    
    You should have received a copy of the GNU General Public License
    along with The CINF Data Presentation Website.  If not, see
    <http://www.gnu.org/licenses/>.
  */

include("../common_functions_v2.php");
echo(html_header());

# Settings start
$image_apache_dir = "../stmimages";
$image_real_dir = "/u/data1/stm312/stm/Images";
# Settings end

$file = $_GET["file"];
$month = $_GET["month"];
$output = shell_exec("python stm_parse.py --file $image_real_dir/$month/$file 2>&1");
#echo($output);
$metadata = json_decode($output);

function single_meta($first, $second, $desc=""){
  if ($desc == ""){
    echo("        <tr><td><b>$first:</b></td><td>$second</td></tr>\n");
  } else {
    echo("        <tr title=\"$desc\"><td><b>$first:</b></td><td>$second</td></tr>\n");
  }
}

function print_image($meta){
  echo("      <table>\n");
  echo("        <tr><td colspan=\"2\"><a href=\"{$meta->label->thumbnail_path}\"><img src=\"{$meta->label->thumbnail_path}\"/></a></td></tr>\n");
  single_meta("Image", "{$meta->index[0]}");
  single_meta("Size", "{$meta->label->xsize}x{$meta->label->ysize} Å");
  single_meta("Pixels", "{$meta->label->xch}x{$meta->label->ych}");
  single_meta("Z-range", "{$meta->label->zscale} V");
  single_meta("I<sub>tunnel</sub>", "{$meta->label->current} nA");
  #-10.0*label->bias/32768.0
  $comment = "This bias is calculated the same way it is done in Gwyddion. The value ".
    "read is an 8 bit signed integer and converted to a float value by the " .
    "formula: -10.0*signed_integer/32768.0. This is not garantied to be " .
    "correct, but the Author of Gwyddion says he has been in contact with " .
    "Specs about it.";
  single_meta("V<sub>gap</sub>", (string) round($meta->label->bias, 2) . " V", $comment);
  single_meta("Position", "{$meta->label->xshift}, {$meta->label->yshift} Å");
  single_meta("Date", sprintf("%04d-%02d-%02d %02d:%02d", $meta->label->year, $meta->label->month, $meta->label->day, $meta->label->hour, $meta->label->minute));
  echo("      </table>\n");
}

echo("    <table>\n");
echo("      <tr><td><b>File:</b></td><td><a href=\"$image_apache_dir/$month/$file\">$month/$file</a></td></tr>\n");
echo("      <tr><td><b>Sample:</b></td><td>{$metadata[0]->label->sample}</td></tr>\n");
echo("      <tr><td><b>Title:</b></td><td>{$metadata[0]->label->title}</td></tr>\n");
echo("    </table>\n");
echo("    <div class=\"clear\"></div>\n");

foreach($metadata as $meta){
  echo("    <div class=\"stm_image\">\n");
  print_image($meta);
  echo("    </div>\n");
}

echo("    <div class=\"clear\"></div>\n");

?>
<?php echo(html_footer());?>