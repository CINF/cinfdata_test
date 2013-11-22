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
# Create right floating menu
$menu = Array("Overview" => "stm_overview.php",
	      "Search" => "stm_search.php",
	      "Update metadata" => "stm_update_metadata.php");
right_float_menu($menu, 6);

# Settings start
$image_apache_dir = "../stmimages";
$image_real_dir = "/u/data1/stm312/stm/Images";
$db = std_db();
mysql_set_charset('utf8', $db);
# Settings end

$file = $_GET["file"];
$month = $_GET["month"];

$res_operator     = isset($_GET["res_operator"])    ? $_GET["res_operator"]    : "ge";
$res_value        = isset($_GET["res_value"])       ? $_GET["res_value"]       : 64;
$size_operator    = isset($_GET["size_operator"])   ? $_GET["size_operator"]   : "ge";
$size_value       = isset($_GET["size_value"])      ? $_GET["size_value"]      : 50;
$bias_operator    = isset($_GET["bias_operator"])   ? $_GET["bias_operator"]   : "ge";
$bias             = isset($_GET["bias"])            ? $_GET["bias"]            : "-100.0";
$current_operator = isset($_GET["current_operator"])? $_GET["current_operator"]: "ge";
$current          = isset($_GET["current"])         ? $_GET["current"]         : "-100.0";

$operators1 = Array("ge"=>"≥", "eq"=>"=", "le"=>"≤");
$operators2 = Array("gt"=>">", "eq"=>"=", "lt"=>"<");
$sizes = Array(50, 100, 150, 200, 300, 500, 1000, 1500, 2000);
$resolutions = Array(64, 128, 256, 512);

function form_selectionbox($operators, $operator, $name, $width=0){
  $style = ($width != 0) ? "style=\"width: {$width}px\"" : "";
  $out = "<select width=\"200\" {$style} name=\"{$name}\">";
  foreach ($operators as $key => $value){
    $selected = ($operator == $key) ? "selected=\"selected\"" : "";
    $out .= "<option $selected value=\"$key\">$value</option>";
  }
  $out .= "</select>";
  return $out;
}

$select_res_operator = form_selectionbox($operators1, $res_operator, "res_operator");
$select_size_operator = form_selectionbox($operators1, $size_operator, "size_operator");
$select_resolutions = form_selectionbox($resolutions, $res_value, "res_value", 60);
$select_sizes = form_selectionbox($sizes, $size_value, "size_value", 60);
$select_bias_operator = form_selectionbox($operators2, $bias_operator, "bias_operator");
$select_current_operator = form_selectionbox($operators2, $current_operator, "current_operator");

# Make sure that the thumbnails are created
shell_exec("python stm_parse.py --thumbs --file $image_real_dir/$month/$file 2>&1");

function single_meta($first, $second, $desc=""){
  if ($desc == ""){
    echo("        <tr><td><b>$first:</b></td><td>$second</td></tr>\n");
  } else {
    echo("        <tr title=\"$desc\"><td><b>$first:</b></td><td>$second</td></tr>\n");
  }
}

function print_image($meta){
  echo("      <table>\n");
  echo("        <tr><td colspan=\"2\"><a href=\"{$meta['thumbnail_path']}\"><img width=\"256\" height=\"256\" src=\"{$meta['thumbnail_path']}\"/></a></td></tr>\n");
  single_meta("Image", $meta["nr"]);
  single_meta("Size", "{$meta['xsize']}x{$meta['ysize']} Å");
  single_meta("Pixels", "{$meta['xch']}x{$meta['ych']}");
  single_meta("Z-range", "{$meta['zscale']} V");
  single_meta("I<sub>tunnel</sub>", "{$meta['current']} nA");
  $comment = "This bias is calculated the same way it is done in Gwyddion. The value ".
    "read is an 8 bit signed integer and converted to a float value by the " .
    "formula: -10.0*signed_integer/32768.0. This is not garantied to be " .
    "correct, but the Author of Gwyddion says he has been in contact with " .
    "Specs about it.";
  single_meta("V<sub>gap</sub>", (string) round($meta['bias'], 2) . " V", $comment);
  single_meta("Position", "{$meta['xshift']}, {$meta['yshift']} Å");
  single_meta("Date", "{$meta['time']}");
  echo("      </table>\n");
}

function print_single_stmfile_top($meta, $number_of_images){
  global $image_apache_dir, $select_res_operator, $select_size_operator;
  global $file, $month, $select_resolutions, $select_sizes, $bias, $current;
  global $select_bias_operator, $select_current_operator;
  $title = "\"Use dot as the decimal point\"";

  echo("    <table>\n");
  echo("      <form action=\"stm_single.php\" method=\"get\">\n");
  echo("        <input type=\"hidden\" name=\"file\" value=\"{$file}\">");
  echo("        <input type=\"hidden\" name=\"month\" value=\"{$month}\">");
  echo("        <tr>\n");
  echo("          <td width=\"70\"><b>File:</b></td>\n");
  echo("          <td width=\"200\"><a href=\"{$image_apache_dir}/{$meta['relative_path']}\">{$meta['relative_path']}</a> ($number_of_images)</td>\n");
  echo("          <td>Resolution:</td>\n");
  echo("          <td>{$select_res_operator}</td>\n");
  echo("          <td>{$select_resolutions}</td>\n");
  echo("          <td style=\"padding:0px 0px 0px 20px\">I<sub>tunnel</sub>:</td>\n");
  echo("          <td>{$select_current_operator}</td>\n");
  echo("          <td><input title=$title name=\"current\" type=\"text\"  value=\"{$current}\"/></td>\n");
  echo("        </tr>\n");
  echo("        <tr>\n");
  echo("          <td><b>Sample:</b></td>\n");
  echo("          <td>{$meta['sample']}</td>\n");
  echo("          <td>Size:</td>\n");
  echo("          <td>{$select_size_operator}</td>\n");
  echo("          <td>{$select_sizes}</td>\n");
  echo("          <td style=\"padding:0px 0px 0px 20px\">V<sub>gap</sub>:</td>\n");
  echo("          <td>{$select_bias_operator}</td>\n");
  echo("          <td><input title=$title name=\"bias\" type=\"text\" value=\"{$bias}\"/></td>\n");
  echo("        </tr>\n");
  echo("        <tr>\n");
  echo("          <td><b>Title:</b></td>\n");
  echo("          <td>{$meta['title']}</td>\n");
  echo("          <td colspan=5></td>\n");
  echo("          <td><input id=\"submit_button\" type=\"submit\" value=\"Update\"></td>\n");
  echo("        </tr>\n");
  echo("      </form>\n");
  echo("    </table>\n");
  echo("    <div class=\"clear\"></div>\n");
}

function myeval($operator, $val1, $val2){
  switch($operator){
  case "gt": return ($val1 > $val2);
  case "ge": return ($val1 >= $val2);
  case "lt": return ($val1 < $val2);
  case "le": return ($val1 <= $val2);
  case "eq": return ($val1 == $val2);
  }
}

$query = "SELECT * from stm312_stmimages WHERE relative_path=\"$month/$file\" ORDER BY nr ASC";
$result = mysql_query($query, $db);

$number_of_images = mysql_num_rows($result);

while ($row = mysql_fetch_array($result)){
  if ($row['nr'] == 1){
    print_single_stmfile_top($row, $number_of_images);
  }

  $show_image =\
    myeval($res_operator, $row["xch"], $resolutions[$res_value]) && \
    myeval($size_operator, $row["xsize"], $sizes[$size_value]) && \
    myeval($current_operator, $row["current"], (float) $current) &&	\
    myeval($bias_operator, $row["bias"], (float) $bias);
  if ($show_image){
    echo("    <div class=\"stm_image\">\n");
    print_image($row);
    echo("    </div>\n");
  }

}

echo("    <div class=\"clear\"></div>\n");

?>
<?php echo(html_footer());?>