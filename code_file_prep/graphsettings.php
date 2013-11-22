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

function plot_settings($type,$params=""){
    # Write the type to the associative settings array and hence initiate it
    $settings['type']=$type;

    $gs = fopen('graphsettings.xml', 'r');
    $gs = fread($gs, filesize('graphsettings.xml'));
    $gs_xml = new SimpleXMLElement($gs);

    # Put the graph specific settings in $settings
    foreach ($gs_xml->graph as $g) {
        if ($g['type'] == $type) {
            foreach ($g->children() as $graph_elements){
                $settings[$graph_elements->getName()] = (string) $graph_elements;
            }
        }
    }

    # Put the global settings in $settings
    foreach ($gs_xml->global_settings->children() as $global){
        $settings[$global->getName()] = (string) $global;
    }


    if (gettype($params) == 'array'){
	foreach($settings as $s_key => $s_value){
	  foreach($params as $p_key => $p_value){
	    $settings[$s_key] = str_replace('{'.$p_key.'}', $p_value, $settings[$s_key]);
	  }
	}
    }

    return $settings;
}

?>
