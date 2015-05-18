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

include("common_functions_v2.php");
echo(html_header($root="", $title="Data logged at CINF", $includehead="",
		 $charset="UTF-8", $width=null, $html5=true));

#$g = simplexml_load_file("index.xml");

class IndexSpec
{
  private $index_xml = null;

  public function __construct(){
    $this->index_xml = simplexml_load_file("index.xml");
  }

  public function generate_table(){
    /* Generate the table with all the setups and linkes */
    echo("\n\n<!-- GENERATED TABLE -->\n");
    echo("<table class=\"frontpage\">\n");

    $setups = $this->index_xml->xpath("//setup");
    $three_setups = Array();
    foreach ($setups as $setup){
      $three_setups[] = $setup;
      if (count($three_setups) == 3){
	$this->generate_group_rows($three_setups);
	$three_setups = Array();
      }
    }
    // Pick up the tail
    if (count($three_setups) > 0){
      $this->generate_group_rows($three_setups);
    }

    echo("</table>\n\n\n");
  }

  private function generate_group_rows($setups){
    /* Generate a the two rows used for one group of up to 3 setups */
    echo("\n<tr>\n");
    # Generate the toggle row
    foreach($setups as $setup){
      $codename = $setup['codename'];
      echo("<td>" .
	   "<a href=\"javascript:toggle('$codename')\"><h2>{$setup->setup_title}</h2></a>" .
	   "</td>\n");
    }
    for($i=0; $i < 3 - count($setups) % 3; $i++){
      echo("<td></td>\n");
    }
    echo("</tr>\n");

    # Generate each setup td
    echo("\n<tr>\n");
    foreach($setups as $setup){
      $this->generate_setup($setup);
    }
    for($i=0; $i < 3 - count($setups) % 3; $i++){
      echo("<td></td>\n");
    }
    echo("</tr>\n");
  }

  private function generate_setup($setup){
    $codename = $setup['codename'];
    echo("<td>\n");
    echo("<ul id=\"$codename\" style=\"display:none\">\n");

    foreach ($setup->xpath("link") as $link){
      $formatted_link = $link->ref;
      $formatted_link = str_replace("&", "&amp;", $formatted_link);
      $formatted_link = str_replace("[", "%5B", $formatted_link);
      $formatted_link = str_replace("]", "%5D", $formatted_link);
      echo("<li><a href=\"{$formatted_link}\" >{$link->title}</a></li>\n");
      }
    
    echo("</ul>\n");
    echo("</td>\n");
  }
}


$index_spec = new IndexSpec();
$index_spec->generate_table();


echo(html_footer("", true));
?>
