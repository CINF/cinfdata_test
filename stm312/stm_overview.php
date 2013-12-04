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

# Settings
#$source_path = "/u/data1/stm312/stm/Images/";
$rows = 5;
$db = std_db();

/* Form an array of dirs and years. */
$dirs = Array();
$years = Array();
$files = Array();
$bad_file_names = Array();

# Get years and dirs from database
$query = "SELECT DISTINCT relative_path from stm312_stmimages";
$result = mysql_query($query, $db);
while ($row = mysql_fetch_array($result)){
  $split = explode("/", $row[0]);
  if (preg_match("/^([0-9]{4,4})_([0-9]{2,2})$/" , $split[0], $matches) and count($split) == 2){
    $dirs[] = $matches[0];
    $years[] = $matches[1];
    if (!array_key_exists($matches[0], $files)){
      $files[$matches[0]] = Array();
    }
    $files[$matches[0]][] = $split[1];
  } else {
  $bad_file_names[] = $row[0];
  }
}

# Clean up years
$years = array_unique($years);
sort($years);
$years = array_reverse($years);

# Clean up dirs
$dirs = array_unique($dirs);
sort($dirs);
$dirs = array_reverse($dirs);


function output_month($dir){
  global $files;
  global $rows;
  $files_in_dir = $files[$dir];
  sort($files_in_dir);
  array_unshift($files_in_dir, $dir);

  echo("      <table class=\"stm_month_table\">\n");
  $month = "";
  foreach($files_in_dir as $index => $value){
    if ($index == 0){                                 # only first start
      $month = $value;
      echo("        <tr>\n");
      echo("          <td class=\"stm_month_name\">$value</td>\n");	
    } else if ($index % $rows == 0 and $index != 0){  # start
      echo("        <tr>\n");
      echo("          <td><a href=\"stm_single.php?file=$value" . '&' . "month=$month\">$value</a></td>\n");
    } else if ($index % $rows == $rows - 1){          # end
      echo("          <td><a href=\"stm_single.php?file=$value" . '&' . "month=$month\">$value</a></td>\n");
      echo("        </tr>\n");
    } else {                                          # all others
      echo("          <td><a href=\"stm_single.php?file=$value" . '&' . "month=$month\">$value</a></td>\n");
    } 
  }
  echo("      </table>\n\n");
}

$menu = Array("Overview" => "stm_overview.php",
	      "Search" => "stm_search.php",
	      "Update metadata" => "stm_update_metadata.php");
right_float_menu($menu, 6);

echo("    <div id=\"stm_overview\">");
foreach($years as $year){
  echo("      <h1>$year</h1>\n");
  
  foreach($dirs as $dir){
    if (preg_match("/^{$year}_[0-9]{2,2}$/" , $dir)){
      output_month($dir);      
    }  
  }
}

if (count($bad_file_names) > 0){
  echo("      <h1>Bad file paths, that have not been included</h1>\n");
  echo("      <table>\n");
  foreach($bad_file_names as $filename){
    echo("        <tr><td>$filename</td></tr>\n");
  }
  echo("      </table>\n\n");
}

echo("    </div>");
?>


<?php echo(html_footer());?>