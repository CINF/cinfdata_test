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
$settings = plot_settings($type); // This will be overridden, we just need to know what type-number to extract from the database

//$exported_ids is the list of ids that is going to be plotted. 
$exported_ids = $_GET["idlist"];

// Get the data
foreach($exported_ids as $curr_id){
    $param["id"] = $curr_id;
    $settings = plot_settings($type,$param); 
    $data[$curr_id] = get_xy_values($settings["query"],$db); 
}

//header('Content-type: text/plain');
header('Content-Disposition: attachment; filename="plot.py"');
echo("import numpy as np \n");
echo("import matplotlib.pyplot as plt\n");
echo("plt.ylabel('" . $settings["ylabel"] ."')\n");
echo("plt.xlabel('" . $settings["xlabel"] ."')\n");
echo("plt.title('" . $settings["titel"] ."')\n");

// Run through all the plots and add them to the Python code
$legend = "";
foreach($data as $id_key => $curr_data){
    $legend = $legend . "'" . $id_key . "',";

    $xvalues = "";
    $yvalues = "";
    for($i=0;$i<count($curr_data["x"]);$i++){
        $xvalues = $xvalues . $curr_data["x"][$i] . ",";
        $yvalues = $yvalues . $curr_data["y"][$i] . ",";
    }
    $xvalues  = substr($xvalues, 0, -1);  // Remove the trailing comma
    $yvalues  = substr($yvalues, 0, -1);  // Remove the trailing comma
    echo("x = np.array([". $xvalues ."])\n");
    echo("y = np.array([". $yvalues ."])\n");
    echo("plt.plot(x,y)\n");
}
$legend = substr($legend, 0, -1);  // Remove the trailing comma
if (count($data)>1){  // For some reason Matplotlib craches if only one legend item is present
    echo("plt.legend((" . $legend ."),'upper right', shadow=True)\n");
}
echo("plt.grid(True)\n");
echo("plt.show()\n");
?>
