<?php
include("../common_functions_v2.php");
echo(header_v2());

// === General functions ==

/* Error handling functions. There are two because I couldn't figure
   out how to save the error output. Therefore one function to simply
   supress the error, when we first check if it occours and a nother
   one to print when we provoke it again */
function myErrorHandler($errno, $errstr, $errfile, $errline)
{echo($errstr);return True;}
function myErrorHandlerNoOutput($errno, $errstr, $errfile, $errline)
{return True;}

/* End page functionality */
function end_on_failed_test($tests){
  echo("<p><span style=\"color:red\">At least one of the " . $tests . " have " . 
       "failed. No reason to continue checking. Exiting!</span></p>");
  echo(html_footer_v2());
  exit(1);
}

// Convinience functions returning or printing PASSED or FAILED markup
function pass($print=False){
  $result = "<span style=\"color:green\">PASSED</span>";
  if ($print){echo($result);}
  return $result;
}
function fail($print=False){
  $result = "<span style=\"color:red\">FAILED</span>";
  if ($print){echo($result);}
  return $result;
}

// === Checker utility functions ===

function mandatory($children, $mandatory, $l=3){
  $child_names = Array();
  foreach ($children as $child){
    array_push($child_names, $child->getName());
  }

  foreach ($mandatory as $arg){
    if (!in_array($arg, $child_names)){
      echo("<h{$l}>Mandatory argument test..." . fail() . "</h{$l}>\n");
      echo("<p>Error: The tag \"" . $arg . "\" is missing.</p>\n");
      return False;
    }
  }
  echo("<h{$l}>Mandatory argument test..." . pass() . "</h{$l}>\n");
  echo("<p>All the mandatory arguments: \"" . implode(", ", $mandatory) .
       "\" are present.</p>\n");
  return True;
}

function not_in_mandatory_or_optional($children, $mandatory, $optional, $l=3){
  $child_tags = Array();
  foreach ($children as $child){
    array_push($child_tags, $child->getName());
  }
  $new_optional = Array();
  foreach ($optional as $tag){
    if (strpos($tag, "?") === False){
      array_push($new_optional, $tag);
    } else {
      $split = explode("?", $tag);
      $start = (int)$split[1];
      $prefix = $split[0];
      foreach (range($start, 12) as $n) {
	array_push($new_optional, $prefix . (string)$n);
      }
    }
  }

  $all_tags = array_merge($mandatory, $new_optional);
  $all_tags_out = array_merge($mandatory, $optional);
  foreach ($child_tags as $tag){
    if (!in_array($tag, $all_tags)){
      echo("<h{$l}>Unknown tags test..." . fail() . "</h{$l}>\n");
      echo("<p>Error: The tag \"" . $tag . "\" is not in the total list of " . 
	   "mandatory or optional arguemnts. Only the tags: " . 
	   implode(", ", $all_tags_out) . "are allowed.</p>\n");
      return False;
    }
  }
  echo("<h{$l}>Unknown tags test..." . pass() . "</h{$l}>\n");
  echo("<p>No tags present that were not in the list of mandatory or optional " . 
       "tags. These are: \"" . implode(", ", $all_tags_out) . "\"</p>\n");
  return True;
}

function check_python_parse_float($str, $parameter){
  $str = weed($str);  # Primarily to prevent command injection be removing "
  if (substr($str, 0, 1) . substr($str, -1, 1) == "{}"){
    echo("<p>Value for {$parameter}={$str} appear to be a measurements table " . 
	 "field, assume it is <span style=\"color:green\">OK</span></p>");
    return true;
  }
  $python_check_float = "echo -e \"import sys\ntry:\n float('$str')\nexcept Exception as e:\n print e\"|python";
  $output = shell_exec($python_check_float);
  if (strlen($output) > 0){
    echo("<p>Value for {$parameter}={$str} parse as float <span style=\"color:red\">NOT OK</span></p>");
    echo("<p>" . $output . "</p>");
    return False;
  } else {
    echo("<p>Value for {$parameter}={$str} parse as float <span style=\"color:green\">OK</span></p>");
    return True;
  }  
}

function check_python_reg_exp($str){
  $str = weed($str);  # Primarily to prevent command injection be removing "
  $python_check_reg_exp = "echo -e \"import re\ntry:\n re.compile('$str')\nexcept Exception as e:\n print e\"|python";
  $output = shell_exec($python_check_reg_exp);
  if (strlen($output) > 0){
    echo("<p>String \"{$str}\" parsed as reg exp <span style=\"color:red\">NOT OK</span></p>");
    echo("<p>" . $output . "</p>");
    return False;
  } else {
    echo("<p>String \"{$str}\" parsed as reg exp <span style=\"color:green\">OK</span></p>");
    return True;
  }  
}

// === XML checker tests ===

/* Test to check if the XML file can be parsed by PHP. This function
   is greatly complicated either by the absolutely insain error and
   exceptions handling system in PHP, or more likely, by my complete
   inability to use it correctly. Please correct it if you can confirm
   the latter. \KN */
$xml;
function phpXmlParseTest(){
  $gs = fopen('graphsettings.xml', 'r');
  $gs = fread($gs, filesize('graphsettings.xml'));
  
  // Ugly stuff to get the error message
  ini_set('display_errors', 1); 
  error_reporting(E_ALL);   
  set_error_handler("myErrorHandlerNoOutput");
  
  global $xml;
  try {
    $xml = new SimpleXMLElement($gs); # The actual parsing
    echo("<h3>PHP XML Parser Test..." . pass() . "</h3>\n");
    restore_error_handler();
    return True;
  } catch (Exception $e) {
    echo("<h3>PHP XML Parser Test..." . fail() . "</h3>\n");
    echo("<p>Errors:</p><p>");
    set_error_handler("myErrorHandler");
    try {$xml = new SimpleXMLElement($gs);} catch(Exception $e) {}
    echo("</p>\n");
    restore_error_handler();
    return False;
  }
}

// Test to check of the XML file can be parsed in Python
function pythonXmlParseTest(){
   $python_parse_test = "echo -e \"import sys\nimport xml.etree.ElementTree\n" . 
     "gs = xml.etree.ElementTree.ElementTree()\ntry:\n" . 
     " gs.parse('graphsettings.xml')\nexcept Exception as e:\n" . 
     " print e\"|python";
   $output = shell_exec($python_parse_test);
   if (strlen($output) > 0){
    echo("<h3>Python XML Parser Test..." . fail() . "</h3>\n");
    echo("<p>Errors:</p><p>" . $output . "</p>\n");
    return False;
   } else {
    echo("<h3>Python XML Parser Test..." . pass() . "</h3>\n");
    return True;
   }  
}

// Check first level tag names
function first_level_tag_test(){
  global $xml;
  if($xml->getName()=="graphs"){
    echo("<h3>Top level tag name \"graphs\"..." . pass() . "</h3>");
  }else{
    echo("<h3>Top level tag name \"graphs\"..." . fail() . "</h3>");
  }
}

// Check second level tag names
function second_level_tag_test(){
  global $xml;
  $mandatory = Array("global_settings");
  $optional = Array("graph");
  $children = $xml->children();
  $res1 = mandatory($children, $mandatory);
  $res2 = not_in_mandatory_or_optional($children, $mandatory, $optional);
  return ($res1 and $res2);
}

#$figure_defaults;
function global_settings_tag_test(){
  #global $figure_defaults;
  $mandatory = Array("measurements_table", "xyvalues_table", "sql_username",
		     "label_column", "mandatory_export_fields", "image_format",
		     "folder_name");
  $optional = Array("matplotlib_settings", "dygraph_settings");
  global $xml;
  $children = $xml->global_settings->children();
  $pass = mandatory($children, $mandatory);
  $pass = ($pass and not_in_mandatory_or_optional($children, $mandatory, $optional));
  # Check mandatory export fields
  $pass = ($pass and check_mandatory_export_fields($xml->global_settings->mandatory_export_fields));
  foreach ($children as $child){
    if ($child->getName() == "matplotlib_settings"){
      $pass = ($pass and check_matplotlib($child));
    } else if ($child->getName() == "dygraph_settings"){
      $pass = ($pass and check_dygraph($child));
    }
  }
  return $pass;
  
}

function all_graph_tag_test(){
  global $xml;
  $pass = True;
  foreach($xml->children() as $child){
    if ($child->getName() == 'graph'){
      echo("<div class=\"config_check_l1\">\n");
      $pass = ($pass and single_graph_tag_test($child));
      echo("</div>\n");
    }
  }
  return $pass;
}

function single_graph_tag_test($xml){
  
  echo("\n<!-- Single graph test-->\n" .
       "<h3 class=\"check_config_h3_highlight\">Testing graph <span style=\"color:blue\">" . 
       $xml["type"] . "</span></h3>\n");
  $child_names = Array();
  foreach ($xml->children() as $child){
    array_push($child_names, $child->getName());
  }
  
  if (!in_array('default_xscale', $child_names)){
    $error = "All graphs must contain the \"default_xscale\" tag";
    echo("<span style=\"color:red\">" . $error . "</span>\n");
    return False;
  }

  switch ($xml->default_xscale){
  case "dat";
    echo("<p>Deafult xscale is: " . $xml->default_xscale . "</p>\n");
    return date_graph_tag_test($xml);
  case "lin";
  case "log";
    echo("<p>Deafult xscale is: " . $xml->default_xscale . "</p>\n");
    return xy_graph_tag_test($xml);
  default;
  $error = "Value of \"default_xscale\" must be dat, lin or log";
  echo("<span style=\"color:red\">" . $error . "</span>\n");
  return False;  
  }
}

function date_graph_tag_test($xml){
  $mandatory = Array("default_xscale", "right_legend_suffix", "dateplot1");
  $optional = Array("xlabel", "title", "ylabel", "image_format", "dateplot?2",
		    "matplotlib_settings", "dygraph_settings", "default_time");
  $children = $xml->children();
  $pass = mandatory($children, $mandatory, 4);
  $pass = ($pass and not_in_mandatory_or_optional($children, $mandatory, $optional, 4));

  $child_names = Array();
  foreach ($xml->children() as $child){
    array_push($child_names, $child->getName());
  }
  foreach ($child_names as $name){
    if ($name == "dygraph_settings"){
      $pass = ($pass and check_dygraph($xml->$name));
    } else if ($name == "matplotlib_settings"){
      $pass = ($pass and check_matplotlib($xml->$name));
    }
  }
  
  $mandatory = Array("legend", "query", "color");
  $optional = Array("title", "ylabel");  
  foreach($children as $child){
    if (strpos($child->getName(), 'dateplot') !== false){
      echo("\n<h4 class=\"config_check_graph\">Testing <span style=\"color:blue\">" . $child->getName() . "</span></h4>\n");
      $pass = ($pass and mandatory($child->children(), $mandatory, 4));
      $pass = ($pass and not_in_mandatory_or_optional($child->children(),
						      $mandatory,
						      $optional, 4));
      if ((int) $child->color > 0){
	echo("<p>Value of color={$child->color} is valid</p>\n");
	$pass = ($pass and True);
      } else {
	echo("<p><span style=\"color:red\">Value of color={$child->color} " . 
	     "is not valid. It must be larger than 0.</p>");
	$pass = ($pass and False);
      }
    }
  }
  return $pass;
}

function xy_graph_tag_test($xml){
  $mandatory = Array("default_xscale", "type", "grouping_column", "queries",
		     "legend", "right_legend_suffix");
  # Optional options in groups
  $gui_only = Array("flip_x", "diff_left_y", "diff_right_y");
  $linscale = Array("linscale_x0", "linscale_x1", "linscale_x2",
		    "linscale_left_y0", "linscale_left_y1", "linscale_left_y2", 
		    "linscale_right_y0", "linscale_right_y1", "linscale_right_y2");
  $rest = Array("title", "xlabel", "ylabel", "image_format", "parameters",
		"as_function_of", "mandatory_export_fields", "label_column",
		"matplotlib_settings", "dygraph_settings", "flight_time_estimate",
		"gas_analysis");
  # Add them up
  $optional = array_merge($gui_only, $linscale, $rest);
  $children = $xml->children();

  $child_names = Array();
  foreach ($xml->children() as $child){
    array_push($child_names, $child->getName());
  }

  $pass = mandatory($children, $mandatory, 4);
  $pass = ($pass and not_in_mandatory_or_optional($children, $mandatory, $optional, 4));

  sort($child_names);

  foreach ($child_names as $name){
    if (in_array($name, $gui_only)){
      $pass = ($pass and check_only_gui($xml->$name));
    } else if  (in_array($name, $linscale)){
      $pass = ($pass and check_linscale($xml->$name)); 
    } else if ($name == "as_function_of"){
      echo("<h3>Testing option: <span style=\"color:blue\">" . $name . "</span></h3>\n");
      $mandatory = Array("gui", "column", "reg_match", "xlabel");
      $pass = ($pass and mandatory($xml->$name->children(), $mandatory, 4));
      $pass = ($pass and check_python_reg_exp($xml->$name->reg_match));
    } else if (in_array($name, Array("queries", "ylabel", "legend"))){
      $pass = ($pass and check_with_matches($xml->$name));
    } else if ($name == "mandatory_export_fields"){
      $pass = ($pass and check_mandatory_export_fields($xml->$name));
    } else if ($name == "matplotlib_settings"){
      $pass = ($pass and check_matplotlib($xml->$name));
    } else if ($name == "dygraph_settings"){
      $pass = ($pass and check_dygraph($xml->$name));
    }
  }
  
  return $pass;
}

function check_only_gui($xml){
  echo("<h3>Testing option: <span style=\"color:blue\">" . $xml->getName() . "</span></h3>\n");
  if ($xml->getName() == "flip_x"){
    $mandatory = Array("gui");
      } else {
    $mandatory = Array("gui", "ylabel_addition");
  }
  return mandatory($xml->children(), $mandatory, 4);
}

function check_linscale($xml){
  echo("<h3>Testing option: <span style=\"color:blue\">" . $xml->getName() . "</span></h3>\n");
  $mandatory = Array("gui", "a", "b");
  if (substr($xml->getName(), 9, 1) == "x"){
    array_push($mandatory, "xlabel_addition");
  } else {
    array_push($mandatory, "ylabel_addition");
  }
  if (mandatory($xml->children(), $mandatory, 4)){
    $pass = True;
  } else {
    return False;
  }
  $pass = ($pass and check_python_parse_float($xml->a, "a"));
  $pass = ($pass and check_python_parse_float($xml->b, "b"));
  return $pass;
}

function check_with_matches($xml){
  echo("<h3>Testing option: <span style=\"color:blue\">" . $xml->getName() . "</span></h3>\n");
  $mandatory = Array("default");
  if ($xml->getName() == "queries"){
    $tagname = "extra";
  } else {
    $tagname = "pattern";
  }
  $optional = Array("column", $tagname . "?0");
  $pass = mandatory($xml->children(), $mandatory, 4);
  $pass = ($pass and not_in_mandatory_or_optional($xml->children(), $mandatory,
						  $optional, 4));

  $options = Array();
  echo("<div class=\"suboption\">\n");
  foreach (range(0,12) as $i){
    $option = $tagname . (string) $i;
    if ($xml->$option->count() > 0){
      echo("<h3>Testing sub option: <span style=\"color:blue\">" . $xml->$option->getName() . "</span></h3>\n");
      switch ($xml->getName()){
      case "queries";
      $pass = ($pass and mandatory($xml->$option->children(), Array("match", "query"), 4));
      break;
      case "legend";
      $pass = ($pass and mandatory($xml->$option->children(), Array("reg_match", "legend"), 4));
      $pass = ($pass and check_python_reg_exp($xml->$option->reg_match));
      break;
      case "ylabel";
      $pass = ($pass and mandatory($xml->$option->children(), Array("reg_match", "ylabel"), 4));
      $pass = ($pass and check_python_reg_exp($xml->$option->reg_match));
      break;
      }
    }
  }
  echo("</div>\n");
  return $pass;
}

function check_mandatory_export_fields($xml, $l=3){
  # Mandatory export fields
  $children = $xml->children();
  $mandatory = Array("field0");
  $optional = Array("field?1");
  echo("<h{$l}>Checking the <span style=\"color:blue\">mandatory export fields</span><h{$l}>");
  $pass = mandatory($children, $mandatory, $l+1);
  $pass = ($pass and not_in_mandatory_or_optional($children, $mandatory, $optional, $l+1));
  echo("<div class=\"suboption\">");
  foreach($children as $child){
    echo("<h{$l}>Checking <span style=\"color:blue\">" . $child->getName() . "</span><h{$l}>");
    $pass = ($pass and mandatory($child->children(), Array("field", "name"), $l+1));
  }
  echo("</div>");
  return $pass;
}

function check_matplotlib($xml, $l=3){
  # Mandatory export fields
  $children = $xml->children();
  $mandatory = Array();
  $optional = Array("width", "height", "title_size", "xtick_labelsize",
		    "ytick_labelsize", "legend_fontsize", "label_fontsize",
		    "linewidth", "grid");
  echo("<h{$l}>Checking the <span style=\"color:blue\">matplotlib settings</span><h{$l}>");
  $pass = not_in_mandatory_or_optional($children, $mandatory, $optional, $l+1);
  return $pass;
}

function check_dygraph($xml, $l=3){
  # Mandatory export fields
  $children = $xml->children();
  $mandatory = Array();
  $optional = Array("roll_period", "xgrid", "ygrid", "labels_side", "series_highlight",
		    "labels_newline");
  echo("<h{$l}>Checking the <span style=\"color:blue\">dygraph settings</span><h{$l}>");
  $pass = not_in_mandatory_or_optional($children, $mandatory, $optional, $l+1);
  return $pass;
}


?>

<?php
################################################################################
# Here starts the page layout and test order part                              #
################################################################################
?>

<div class="config_check">
<p>This page will test if your configuration file can be parsed by the
  xml parsers and if it contains all the correct settings a specified of
  this page.</p>

<!-- XML PARSER TESTS -->
<h2>XML Parser Tests</h2>

<!-- PHP XML PARSER TESTS -->
<?php $php_parse_passed = phpXmlParseTest(); ?>

<!-- Python XML PARSER TESTS -->
<?php $python_parse_passed = pythonXmlParseTest(); ?>
<?php
if (!$php_parse_passed or !$python_parse_passed){
  end_on_failed_test("parse tests");
}
?>

<!-- Test of the first level tag -->
<h2>First level tags</h2>
<?php first_level_tag_test(); ?>


<!-- Test of the second level tags -->
<h2>Second level tags</h2>
<?php
if (!second_level_tag_test()){
  end_on_failed_test("second level tag tests");
}
?>

<!-- Test of the global settings -->
<h2>Global settings</h2>
<?php
if (!global_settings_tag_test()){
  end_on_failed_test("global settings tag tests");
}
?>

<!-- Test of the graph settings -->
<h2>Graph settings</h2>
<?php
if (!all_graph_tag_test()){
  end_on_failed_test("graph_tag_test");
}
?>

<!-- TODO FIXME FOR DATA TREATMENT OPTIONS DON'T ALLOW AS_FUNCTION_OF AND DIFF FOR THE SAME GRAPH -->
</div>  
<?php echo(html_footer_v2()); ?>
  
