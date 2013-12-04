<?php
include("graphsettings.php");
include("../common_functions.php");

#$type = $_GET["type"];
$settings = plot_settings('multidateplot');

# Make a list of the all the graphs in the multiplot definition
$graphs = Array();
foreach($settings as $key => $value){
  # Regular expression matching the dateplotN tag (with minimum one N-digit)
  if (preg_match("/^dateplot[0-9][0-9]*$/", $key)){
    $graphs[$key] = $value;
  }
}

# Disable error reporting
$error_reporting_value = error_reporting(0); 

#Remember settings after submitting
$from            = ($_GET["from"]           == "") ? 0 : $_GET["from"];
$to              = ($_GET["to"]             == "") ? 0 : $_GET["to"];
$left_ymax       = ($_GET["left_ymax"]      == "") ? 0 : $_GET["left_ymax"];
$left_ymin       = ($_GET["left_ymin"]      == "") ? 0 : $_GET["left_ymin"];
$right_ymax      = ($_GET["right_ymax"]     == "") ? 0 : $_GET["right_ymax"];
$right_ymin      = ($_GET["right_ymin"]     == "") ? 0 : $_GET["right_ymin"];
$left_logscale   = ($_GET["left_logscale"]  == "") ? "" : "checked";
$right_logscale  = ($_GET["right_logscale"] == "") ? "" : "checked";
$left_plotlist   = ($_GET["left_plotlist"]  == "") ? "" : $_GET["left_plotlist"];
$right_plotlist  = ($_GET["right_plotlist"] == "") ? "" : $_GET["right_plotlist"];
$selected = "";
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
 <title>CINF data logging</title>
 <link rel="StyleSheet" href="style.css" type="text/css" media="screen">
</head>
<body>
 <div class="container">
  <div class="caption">
   Data viewer<a href="/"><img class="logo" src="cinf_logo_beta_greek.png" alt="CINF data viewer"></a>
  </div>

  <script type="text/javascript">
   function toggle(list){ 
    var listElementStyle=document.getElementById(list).style;
     if (listElementStyle.display=="none"){ 
          listElementStyle.display="block"; 
     }
     else{listElementStyle.display="none"; 
     } 
   }

   function getCurrentDate(){
    var currentTime = new Date();
    var year = currentTime.getFullYear();
    var month = currentTime.getMonth()+1;
    var day = currentTime.getDate();
    var hours = currentTime.getHours();
    var minutes = currentTime.getMinutes();

    if (minutes < 10){
     minutes = "0" + minutes;
    }

    if (hours < 10){
     hours = "0" + hours;
    }

    document.write(year + "-" + month + "-" + day + " " + hours + ":" + minutes);
   }

   function goBackInTime(days){
    var currentTime = new Date();
    var currentUnixTime = Math.floor(currentTime.getTime());
    var oldUnixTime = currentUnixTime-(days*24*60*60*1000); //UNIX is in milliseconds in JavaScript for some reason
    var oldDate = new Date(oldUnixTime)
    var year = oldDate.getFullYear();
    var month = oldDate.getMonth()+1;
    var day = oldDate.getDate();
    var hours = oldDate.getHours();
    var minutes = oldDate.getMinutes();

    if (minutes < 10){
     minutes = "0" + minutes;
    }

    if (hours < 10){
     hours = "0" + hours;
    }

    document.write(year + "-" + month + "-" + day + " " + hours + ":" + minutes);

   }

   function getSelectValue(){
    var select = document.getElementById("daysback");
    //document.write("Value: " + select.options[select.selectedIndex].value);
    goBackInTime(select.options[select.selectedIndex].value);
   }

  </script>

  <div class="plotcontainer">
   <p><img src="../microreactorNG/plot.php?type=pirani_buffervolume&id=&from=2011-12-12 18:08&to=2011-12-13 18:09&ymax=0&ymin=0"></p>
  </div>
  <hr>
  <form action="dateplot.php" method="get">
  <!--<input type="hidden" name="type" value="<?php echo($type);?>">-->
   <table class="generaloptions">
    <tr>
     <td>
      <select name="dateselection">
       <option value="1">1 day back</option>
       <option value="2">2 days back</option>
       <option value="3">3 days back</option>
      </select>
     </td>
     <td>
      <b>From:</b><input name="from" type="text" value="<?php echo($from);?>" size="13">
     </td>
     <td>
      <b>To:</b><input name="to" type="text" value="onload:getCurrentDate()" size="13">
     </td>
     <td>
      <b>Matplotlib</b><input type="checkbox" name="matplotlib" onClick="javascript:toggle('matplotlib')" value="checked">
     </td>
     <td>
      <a href="#">Export current data</a>
     </td>
     <td>
      <input type="submit" value="Update">
     </td>
    </tr>
   </table>
   <hr>
   <table class="selection">
    <tr>
     <td><!--LEFT Y-->
      <b>Log-scale</b><input type="checkbox" name="left_logscale" value="checked" <?php echo($left_logscale);?>><br>
      <b>Y-Min:</b><input name="left_ymin" type="text" size="7" value="<?php echo($left_ymin);?>"><br>
      <b>Y-Max:</b><input name="left_ymax" type="text" size="7" value="<?php echo($left_ymax);?>"><br>
      <b>Select measurement:</b><br>
      <select class="select" multiple size="8" name="left_plotlist[]">
      <?php
      # Creation of plotlist for left axis
       foreach($graphs as $key => $value){
        $selected = (in_array(substr($key,-1),$left_plotlist)) ? "selected" : "";
        echo('<option value="'.substr($key,-1).'" ' . $selected .'>' . substr($key,-1) . ':' . $value['title'] . '</option>');
       }
      ?>
      </select>
     </td>
     <!--
     <td align="center">
      midterkolonnen - her er spas og løjer...
     </td>
     -->
     <td align="right"><!--RIGHT Y-->
      <b>Log-scale</b><input type="checkbox" name="right_logscale" value="checked" <?php echo($right_logscale);?>><br>
      <b>Y-Min:</b><input name="right_ymin" type="text" size="7" value="<?php echo($right_ymin);?>"><br>
      <b>Y-Max:</b><input name="right_ymax" type="text" size="7" value="<?php echo($right_ymax);?>"><br>
      <b>Select measurement:</b><br>
      <select class="select" multiple size="8" name="right_plotlist[]">
       <option value="0">None</option>
      <?php
      # Creation of plotlist for right axis
       foreach($graphs as $key => $value){
        $selected = (in_array(substr($key,-1),$left_plotlist)) ? "selected" : "";
        echo('<option value="'.substr($key,-1).'" ' . $selected .'>' . substr($key,-1) . ':' . $value['title'] . '</option>');
       }
      ?>
      </select>
     </td>
    </tr>
   </table>
  </form>
  
  <hr>

  <div class="additionalinfo">
   <h2><a href="javascript:toggle('sqlinfo')">SQL info</a></h2>
   <div id="sqlinfo" style="display:none">
    <b>Sql-statement for this graph:</b><br>
    SELECT unix_timestamp(time), pressure FROM pressure_microreactorNG where time between "2011-12-12 16:42" and "2011-12-13 16:43" order by time<br>
    <b>Latest value:</b><br>
    1.896e-08 @ 2011-12-13 16:33:53
   </div>

   <h2><a href="javascript:toggle('shortlinks')">Make shortlink</a></h2>
   <div id="shortlinks" style="display:none">
    <form action="../links/link.php?url=checked" method="post">
     <b>Comment for short link:</b> <input type="text" name="comment"><br><input type="submit" value="Make short link">
    </form>
   </div>

  <div id="matplotlib" style="display:none">
   <h2><a href="javascript:toggle('matplotlib')">Matplotlib options</a></h2>
   <table>
    <tr>
     <td>
      <b>Title:</b>
     </td>
     <td>
      <input name="title" type="text" size="15">
     </td>
    </tr>
    <tr>
     <td>
      <b>x-label:</b>
     </td>
     <td>
      <input name="xlabel" type="text" size="15">
     </td>
    </tr>
    <tr>
     <td>
      <b>Left y-label</b>
     </td>
     <td>
      <input name="left_ylabel" type="text" size="15">
     </td>
    </tr>
    <tr>
     <td>
      <b>Right y-label:</b>
     </td>
     <td>
      <input name="right_ylabel" type="text" size="15">
     </td>
    </tr>
   </table>
  </div>
  </div>
  <hr>

  <div class="copyright">
   <a href="#"><img src="http://www.w3.org/Icons/valid-html401" alt="Valid HTML 4.01 Transitional" height="31" width="88"></a>
  </div>
 </div>
</body>
</html>

