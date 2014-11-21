<?php

  /* 
    Copyright (C) 2014 Robert Jensen, Thomas Andersen and Kenneth Nielsen
    
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

# Standard setup
date_default_timezone_set("Europe/Copenhagen");

# Get database object
include("../common_functions_v2.php");
$dbi = std_dbi();

$query = $_GET["query"];


$result = $dbi->query($query);



$out = Array();
while($row = $result->fetch_row()) {
  $row[0] = (int) $row[0];
  $row[1] = (float) $row[1];
  $out[] = $row;
}

echo(json_encode($out));
?>