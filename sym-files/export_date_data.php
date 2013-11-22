<?php
  /*
    Copyright (C) 2012 Robert Jensen, Thomas Anderser and Kenneth Nielsen
    
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

include("../common_functions.php");
include("graphsettings.php");
$db = std_db();

$type = $_GET["type"];
$xscale = date_xscale($_GET["from"],$_GET["to"]);
$settings = plot_settings($type,$xscale);

$data = get_xy_values($settings["query"],$db);

$xval = $data["x"];
$yval = $data["y"];

for($i=0;$i<count($xval);$i++){
    echo(date('Y-m-d H:i',$xval[$i]) . "," . $yval[$i] . "<br>");
}

?>
