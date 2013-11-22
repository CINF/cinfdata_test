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
echo(html_header() . "\n");

# Settings
$image_real_dir = "/u/data1/stm312/stm/Images";

# Create data base connection
$db = std_db();
mysql_set_charset('utf8', $db);

# Create right floating menu
$menu = Array("Overview" => "stm_overview.php",
	      "Search" => "stm_search.php",
	      "Update metadata" => "stm_update_metadata.php");
right_float_menu($menu, 6);

# Define address line parameters and their defaults ...
$url_params = Array("res_operator" => "ge", "res_value" => 0,
		    "size_operator" => "ge", "size_value" => 0,
		    "bias_operator" => "ge", "bias" => "-100.0",
		    "current_operator" => "ge", "current" => "-100.0",
		    # Deafult for the time filter is newer than 30 days
		    "date_operator" => "gt", "date" => date("Y-m-d", time() - 30 * 24 * 60 * 60),
		    "title" => "", "sample" => "");
# ... and read them in if set
foreach($url_params as $key => $value){
  $url_params[$key] = isset($_GET[$key]) ? $_GET[$key] : $value;
}
$preview = isset($_GET["preview"]) ? "checked" : "";

# Functions
function form_selectionbox($operators, $name, $width=0){
  /* This function forms a input html selection box with the items in
     $operators, saves the result to $name upon submission and optionally
     sets the width of the selection box */
  global $url_params;
  $style = ($width != 0) ? "style=\"width: {$width}px\"" : "";
  $out = "<select {$style} name=\"{$name}\">";
  foreach ($operators as $key => $value){
    $selected = ($url_params[$name] == $key) ? "selected=\"selected\"" : "";
    $escaped_value = htmlspecialchars($value);
    $out .= "<option $selected value=\"$key\">$escaped_value</option>";
  }
  $out .= "</select>";
  return $out;
}

function output_item($item=null, $even=true){
  /* Output a single row of a html table, with a set of namegiven items in
     $item. $col_spec spcifies whether to create <td> or <th> elements */
  if ($item == null){
    # Header
    echo("" .
	 "        <tr>\n" .
	 "          <th rowspan=\"2\" class=\"row_both\">Image</th>\n" .
	 "          <th rowspan=\"2\" class=\"row_both\">Id</th>\n" .
	 "          <th class=\"row_top\">Size [Å]</th>\n" .
	 "          <th class=\"row_top\">Current [nA]</th>\n" .
	 "          <th class=\"row_top\">Datetime</th>\n" .
	 "          <th rowspan=\"2\" class=\"row_both\">Sample</th>\n" .
	 "          <th rowspan=\"2\" class=\"row_both\">Title</th>\n" .
	 "        </tr>\n" .
	 "        <tr>\n" .
	 "          <th class=\"row_bottom\">Resolution</th>\n" .
	 "          <th class=\"row_bottom\">Bias [V]</th>\n" .
	 "          <th class=\"row_bottom\">File</th>\n" .
	 "        </tr>\n");
  } else {
    $img = "<a href=\"{$item['thumbnail_path']}\"><img width=\"64\" height=\"64\" src=\"{$item['thumbnail_path']}\"/></a>";
    $bias = (string) round($item['bias'], 2);
    $resolution = "{$item['xch']}x{$item['ych']}";
    $size = "{$item['xsize']}x{$item['ysize']}";
    $folder_filename = explode('/', $item['relative_path']);
    $file = "<a href=\"stm_single.php?file={$folder_filename[1]}&month={$folder_filename[0]}\">{$item['relative_path']}</a>";
    $rowstyle = $even ? "class=\"even\"" : "class=\"odd\"";
    echo("" .
	 "        <tr {$rowstyle}>\n" .
	 "          <td rowspan=\"2\" class=\"image_cell\">$img</td>\n" .
	 "          <td rowspan=\"2\" class=\"row_both\">{$item['id']}</td>\n" .
	 "          <td class=\"row_top\">$size</td>\n" .
	 "          <td class=\"row_top\">{$item['current']}</td>\n" .
	 "          <td class=\"row_top\">{$item['time']}</td>\n" .
	 "          <td rowspan=\"2\" class=\"row_both\">{$item['sample']}</td>\n" .
	 "          <td rowspan=\"2\" class=\"row_both\">{$item['title']}</td>\n" .
	 "        </tr>\n" .
	 "        <tr {$rowstyle}>\n" .
	 "          <td class=\"row_bottom\">$resolution</td>\n" .
	 "          <td class=\"row_bottom\">$bias</td>\n" .
	 "          <td class=\"row_bottom\">$file</td>\n" .
	 "        </tr>\n");
  }

}

# Define static lists
$operators1 = Array("ge"=>"≥", "eq"=>"=", "le"=>"≤");
$operators1_mysql = Array("ge"=>">=", "eq"=>"=", "le"=>"<=");
$operators2 = Array("gt"=>">", "eq"=>"=", "lt"=>"<");
$sizes = Array(50, 100, 150, 200, 300, 500, 1000, 1500, 2000);
$resolutions = Array(64, 128, 256, 512);
# Form selection box strinsg
$select_res_operator = form_selectionbox($operators1, "res_operator");
$select_size_operator = form_selectionbox($operators1, "size_operator");
$select_resolutions = form_selectionbox($resolutions, "res_value", 60);
$select_sizes = form_selectionbox($sizes, "size_value", 60);
$select_bias_operator = form_selectionbox($operators2, "bias_operator");
$select_current_operator = form_selectionbox($operators2, "current_operator");
$select_date_operator = form_selectionbox($operators2, "date_operator");

# Produce the header that includes all the filters
$decimal_point_comment = "\"Use dot as the decimal separator\"";
$date_format_comment = "Use the YYYY-MM-DD format";
$search_comment = ("Use '%' as wildcard for several characters, '_' for " .
		   "exactly one. A % is added to both the beginning and end. " . 
		   "Wildcards can be excaped with '\\'.");
echo("" .
"      <!-- HEADER AND FILTERS -->\n" .
"      <table>\n" .
"        <form action=\"stm_search.php\" method=\"get\">\n" .
"          <tr>\n" .
"            <td>Title:</td>\n" .
"            <td><input title=\"$search_comment\" style=\"width:170px\" name=\"title\" type=\"text\"  value=\"{$url_params['title']}\" /></td>\n" .
"            <td>Resolution:</td>\n" .
"            <td>{$select_res_operator}</td>\n" .
"            <td>{$select_resolutions}</td>\n" .
"            <td style=\"padding:0px 0px 0px 20px\">I<sub>tunnel</sub>:</td>\n" .
"            <td>{$select_current_operator}</td>\n" .
"            <td><input style=\"width:120px\" title=$decimal_point_comment name=\"current\" type=\"text\"  value=\"{$url_params['current']}\" /></td>\n" .
"          </tr>\n" .
"          <tr>\n" .
"            <td>Sample:</td>\n" .
"            <td><input title=\"$search_comment\" style=\"width:170px\" name=\"sample\" type=\"text\"  value=\"{$url_params['sample']}\" /></td>\n" .
"            <td>Size:</td>\n" .
"            <td>{$select_size_operator}</td>\n" .
"            <td>{$select_sizes}</td>\n" .
"            <td style=\"padding:0px 0px 0px 20px\">V<sub>gap</sub>:</td>\n" .
"            <td>{$select_bias_operator}</td>\n" .
"            <td><input style=\"width:120px\" title=$decimal_point_comment name=\"bias\" type=\"text\" value=\"{$url_params['bias']}\" /></td>\n" .
"          </tr>\n" .
"          <tr>\n" .
"            <td colspan=\"2\">Generate thumbnails <input type=\"checkbox\" name=\"preview\" value=\"{$preview}\" $preview/></td>" .
"            <td></td>" .
"            <td colspan=\"2\"></td>\n" .
"            <td style=\"padding:0px 0px 0px 20px\">Date</td>\n" .
"            <td>{$select_date_operator}</td>\n" .
"            <td><input style=\"width:120px\" title=$date_format_comment name=\"date\" type=\"text\"  value=\"{$url_params['date']}\" /></td>\n" .
"            <td><td>\n" .
"            <td><input id=\"submit_button\" type=\"submit\" value=\"Search\" /></td>\n" .
"          </tr>\n" .
"        </form>\n" .
"      </table>\n" .
"      <div class=\"clear\"></div>\n");

# Search and results
$criteria = Array("xch {$operators1_mysql[$url_params['res_operator']]} {$resolutions[$url_params['res_value']]}",
		  "xsize {$operators1_mysql[$url_params['size_operator']]} {$sizes[$url_params['size_value']]}",
		  "current {$operators2[$url_params['current_operator']]} {$url_params['current']}",
		  "bias {$operators2[$url_params['bias_operator']]} {$url_params['bias']}",
		  $url_params['date_operator'] == "eq" ? "time LIKE \"{$url_params['date']}%\"" : "time {$operators2[$url_params['date_operator']]} \"{$url_params['date']}\"",
		  "title LIKE \"%{$url_params['title']}%\"",
		  "sample LIKE \"%{$url_params['sample']}%\"");
$criteria_string = implode(" AND ", $criteria);
# If requested, make sure there are thumbnails
if ($preview){
  $query = "SELECT relative_path, thumbnail_path FROM stm312_stmimages WHERE $criteria_string GROUP BY relative_path";
  $result = mysql_query($query, $db);
  while ($row = mysql_fetch_array($result)){
    if(!file_exists($row[1])){
      shell_exec("python stm_parse.py --thumbs --file $image_real_dir/{$row[0]} 2>&1");
    }
  }
}
# And output the results
$headers = Array("id" => "Id", "current" => "Current [nA]",
		 "time" => "Date time", "sample" => "Sample", "title" => "Title");
$query = "SELECT * FROM stm312_stmimages WHERE {$criteria_string} ORDER BY time DESC";
$result = mysql_query($query, $db);
echo("\n      <!-- RESULTS -->\n" .
     "      <div class=\"stm_table\">\n" .
     "      <table>\n");
$count = 0;
while ($row = mysql_fetch_array($result)){
  if ($count % 10 == 0){
    output_item(null);
  }
  $even = $count % 2 == 0;
  output_item($row, $even);
  $count ++;
}
echo("      </table>\n");
echo("      </div>\n");
echo("\n      <p>" . htmlspecialchars($query) . "</p>\n");
echo(html_footer());
?>