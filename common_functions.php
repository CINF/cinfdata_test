<?php
  /* Removed by Robert -- will do a real cleanup later
include ("../jpgraph/src/jpgraph.php");
include ("../jpgraph/src/jpgraph_log.php");
include ("../jpgraph/src/jpgraph_line.php");
include ("../jpgraph/src/jpgraph_date.php");
include ("../jpgraph/src/jpgraph_scatter.php");
include ("../jpgraph/src/jpgraph_bar.php");
  */

/** Returns a handle to the standard database
    @return object 
  */
function std_db($user = "cinf_reader"){
    //$db = mysql_connect("localhost", "root", "CINF123");  
    //$db = mysql_connect("localhost", "cinf_reader", "cinf_reader");
    $db = mysql_connect("localhost", $user, $user);    
    mysql_select_db("cinfdata",$db);
    return($db);
}

function std_dbi($user = "cinf_reader"){
  $mysqli = new mysqli("localhost", $user, $user, "cinfdata");
  return $mysqli;
}

function single_sql_value($db,$query,$column){
    $result  = mysql_query($query,$db);  
    $row = mysql_fetch_array($result);
    $value = $row[$column];
    return($value);
}

/** Returns x and y values for a plot if these can be found as a simple sql-query 
    @param string $query sql-query to used to find the x and y values. First returned value pr. row used for x, second value pr. row for y.
    @param object $db Handle to a database object to be used for the query
    @return array
  */
function get_xy_values($query,$db,$offset=0){
    $result  = mysql_query($query,$db);
    while ($row = mysql_fetch_array($result)){
        $data["y"][] = $row[1] + $offset;
        $data["x"][] = $row[0];
    }
    return($data);
}


/** Formats a given number as a scientific number (exponentials of 10 notation) in HTML 
    @param real $number The number to be html-formatted
    @return string
  */
function science_number($number){
    if($number == 0){
        $result = 0;
    }
    else {
        $exponent = floor(log10($number));
        $digits = $number * pow(10,-1*$exponent);
        $digits = round($digits,2);
        $result = $digits . " &times " . "10<sup>" . $exponent . "</sup>";
    }
    return($result);
}

function remove_outliers($data){
    for($i=1;$i<count($data["x"])-1;$i++){ // There HAS to be a better way to do this
	$old = $data["y"][$i-1];
	$current = $data["y"][$i];
	$next = $data["y"][$i];
	if((($old/$current)<2) && (($old/$current)>0.5) && (($next/$current)<2) && (($next/$current)>0.5)){
	    $new_data["x"][] = $data["x"][$i];
	    $new_data["y"][] = $data["y"][$i];
	}
    }
    $new_data["x"][] = $data["x"][$i];
    $new_data["y"][] = $data["y"][$i];

    return($new_data);
}

/** Returns the distance between the values of a field in two different rows of a table 
    - maybe it should be expanded to do an array of querys
    @param string $query sql-query to used to find the step-size. Only the first value of the first two rows is used.
    @param object $db Handle to a database object to be used for the query
    @return real
 */
function get_xy_stepsize($query,$db){
	$result  = mysql_query($query,$db);  
	$row = mysql_fetch_array($result);
	$x1 = $row[0];
	$row = mysql_fetch_array($result);
	$x2 = $row[0];
	$stepsize = $x2-$x1;
    return($stepsize);
}

/** Returns the default values for a date-xscale or an array of the manually selected scale
    If non-default values are provided, these will simply be returned
    The return value is formatted as a string suitable for injection into an sql-query
    @param string $from The from-value to be returned. If an emptry string is given, the default value will be returned
    @param string $to The to-value to be returned. If an emptry string is given, the default value will be returned
    @param string $defaulthours The default amount of hours to go back in time if defaults are to be returned
    @return array
  */
function date_xscale($from="",$to="",$defaulthours=24){
    $xscale["to"] = date('Y-m-d H:i',time() + 60); // Default, 1 minute into the future, to be shure get the whole plot
    $xscale["from"] = date('Y-m-d H:i',time() - 60 * 60 * $defaulthours);
    $xscale["from"] = ($from == "") ? $xscale["from"] : $from; // If we get an argument, skip the defaults
    $xscale["to"] = ($to == "") ? $xscale["to"] : $to;
    return($xscale);
}


/** Returns an array of the yscale, default values of 1 and 2 are provides if no values are given
  * @param string $max The max value. The value is type-casted into a nummeric value
  * @param string $min The min value. The value is type-casted into a nummeric value
  * @param string $log indicates if the plot is on a log scale. "checked"=true, !"checked"=false
  * @return array
  */
function default_yscale($max,$min,$manual,$log){
    if ($log){
        $max = log10($max);
        $min = log10($min);
    }
    $yscale["max"] = ($max == "") ? 2 : $max; //Default values if no values is given: 2 and 1
    $yscale["min"] = ($min == "") ? 1 : $min;
    $yscale["manual"] = $manual==="checked"; // If a manual scale is chosen the variable
                                             // $manual will have the value "checked"
    return($yscale);
}

function xscale($max,$min,$manual,$log){
    if ($log){
        $max = log10($max);
        $min = log10($min);
    }
    $xscale["max"] = ($max == "") ? 2 : $max; //Default values if no values is given: 2 and 1
    $xscale["min"] = ($min == "") ? 1 : $min;
    $xscale["manual"] = $manual==="checked"; // If a manual scale is chosen the variable
                                             // $manual will have the value "checked"
    return($xscale);
}

/** Returns the default size (in pixels) of a graph or an array of the manually selected size
  * @param integer $x Size in the x-directions
  * @param integer $y Size in the y-directions
  * @param integer $small Indicates a small plot, 1=true !1=false
  * @return array
  */
function get_size($x,$y,$small){
    // Default size: 900x600, but we also accept other sizes
    $size["x"] = ($x == "") ? 900 : $x;
    $size["y"] = ($y == "") ? 600 : $y;
    $size["small"] = ($small==1) ? true : false; // print less fancy stuff of on the graph if this is true
    return($size);
}

/** Returns a string with a standard html-header
 *  @return string
 */
function html_header(){
    $header = "";
    $header = $header . "<html>\n";
    $header = $header . "<head>\n";
    $header = $header . "<title>CINF data logging</title>\n";
    $header = $header . "<style title=\"css\" type=\"text/css\">";
    $header = $header . "@import \"../style.css\";";
    $header = $header . "</style>\n";
    $header = $header . "</head>\n";
    $header = $header . "<body>\n";
    $header = $header . "<a href=\"/\" id=\"frontpagelink\">Front page</a>\n";
    return($header);
}


function new_html_header(){
    $header = "";
    $header = $header . "<head><title>CINF data logging</title>\n";
    $header = $header . "<link rel=\"StyleSheet\" href=\"../css/screen.css\" type=\"text/css\" media=\"screen\">\n";
    $header = $header . "</head>\n";
    $header = $header . "<body>\n";
    $header = $header . "<div class=\"container\">\n";
    $header = $header . "<div class=\"caption\">Data viewer\n";
#    $header = $header . "<a href=\"/\"><img class=\"logo\" src=\"../images/cinf_logo_beta.png\"></a>\n";
#    $header = $header . "<a href=\"/\"><img class=\"logo\" src=\"../images/cinf_logo_beta.png\"></a><img class=\"logo\" src=\"../images/underconstruction.gif\">\n";
    $header = $header . "<a href=\"/\"><img class=\"logo\" src=\"../images/cinf_logo_beta_greek.png\"></a>\n";

    $header = $header . "</div>\n";
    return($header);
}

function header_v2(){
  $header = "";
  $header = $header . "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";
  $header = $header . "<html>\n";
  $header = $header . "  <head>\n";
  $header = $header . "    <title>CINF data logging</title>\n";
  $header = $header . "    <link rel=\"StyleSheet\" href=\"../css/screen.css\" type=\"text/css\" media=\"screen\">\n";
  $header = $header . "  </head>\n";
  $header = $header . "  <body>\n";
  $header = $header . "    <div class=\"container\">\n";
  $header = $header . "      <div class=\"caption\">Data viewer\n";
  $header = $header . "        <a href=\"/\"><img class=\"logo\" src=\"../images/cinf_logo_beta_greek.png\"></a>\n";
  $header = $header . "      </div>\n";

  return($header);
}

function html_footer_v2(){
  $footer = "";
  $footer = $footer . "      <div class=\"copyright\">...</div>\n";
  $footer = $footer . "    </div>\n";
  $footer = $footer . "  </body>\n";
  $footer = $footer . "</html>\n";
  return($footer);
}


function html_code_header($file){
    $header = "";
    $header = $header . "<head><title>CINF data logging</title>\n";
    $header = $header . "<link rel=\"StyleSheet\" href=\"../css/screen.css\" type=\"text/css\" media=\"screen\">\n";
    $header = $header . "</head>\n";
    $header = $header . "<body>\n";
    $header = $header . "<div class=\"container\">\n";
    $header = $header . "<div class=\"caption\">Code viewer: ".$file."\n";
    $header = $header . "<a href=\"/\"><img class=\"logo\" src=\"../images/cinf_logo_beta_greek.png\"></a>\n";
    $header = $header . "</div>\n";
    return($header);
}

function new_html_footer(){
    $footer = "";
    $footer = $footer . "<div class=\"next\"></div>\n";
    $footer = $footer . "<div class=\"copyright\">...</div>\n";
    $footer = $footer . "</div>\n";
    $footer = $footer . "</body>\n";
    $footer = $footer . "</html>\n";
    return($footer);
}

/** Modifies a graph to fit the standard layout 
 *  @param object $graph Handle to the graph being modified
 *  @return object
 */
function std_graph_layout($graph){
    //$graph->img->SetAntiAliasing(); // Not supported in this version of GD
    //$graph->SetFrame(false);
    $graph->SetClipping(true);
    $graph->title->SetFont(FF_DV_SANSSERIF,FS_BOLD,18);
    $graph->xaxis->SetFont(FF_DV_SANSSERIF,FS_NORMAL,8);
    $graph->yaxis->SetFont(FF_DV_SANSSERIF,FS_NORMAL,8);
    $graph->yaxis->title->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);
    $graph->xaxis->title->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);
    $graph->xgrid->Show(true, true);
    $graph->ygrid->Show(true, true);
    $graph->yaxis->title->SetMargin(25);
    return($graph);
}

/** Makes modfications to the layout of the graph depending on
 *  if the graph is running in compact mode
 *  @param object $graph Handle to the graph being modified
 *  @param boolean $small Indicates whether this is a less flashy small-plot
 *  @param boolean $datescale Indicates that the x-axis is a date
 *  @param string $title Title of the graph - not plotted in a small-plopt
 *  @param string $ytitle Title of the y-axis
 *  @param string $xtitle Title of the x-axis
 *  @return object
 */
function final_formatting($graph,$small,$datescale,$title,$ytitle,$xtitle=""){
    if (!$small){
        $graph->title->Set($title);
        $graph->img->SetMargin(70,40,20,100);
        $graph->yaxis->title->Set($ytitle);
        if($datescale){
            $graph->xaxis->SetLabelFormatString('M-d H:i',true);
            $graph->xaxis->SetLabelAngle(45);
        }
        if ($xtitle!=""){
            $graph->xaxis->title->Set($xtitle);
        }
    }
    else{
        if($datescale){
            $graph->xaxis->SetLabelFormatString('H:i',true);
            $graph->xaxis->SetLabelAngle(45);
        }
        $graph->img->SetMargin(50,40,20,40);
    }
    return($graph);
}

/** Returns a string with the name of JP Graphs predefines colors
 *  @param integer $index A number between 0 and ?? corresponding to a given color
 */
function JP_colors($index){
  $jpcolors = array("blue","darkmagenta","black","chocolate4","brown2","darkorange","darkgreen","chocolate","chartreuse4","cornsilk","khaki","darkmagenta","darksalmon","burlywood1","deeppink","gold","firebrick1","forestgreen","green","khaki1","lawngreen","magenta","lime","mediumpurple","navy");
  return($jpcolors[$index]);
}

/** Returns $arr[$key] but without any warning of the array does not
 *  contain the key
 */
function no_error_get ($arr, $key){
  $error_reporting_value = error_reporting(0); # Disable error reporting
  $value = $arr[$key];
  error_reporting($error_reporting_value); # Re-enable error reporting
  return $value;
}

?>
