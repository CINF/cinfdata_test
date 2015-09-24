<?php
include("../common_functions_v2.php");
echo(html_header());
?>

<h1>Overview of the booking calendars for building 312</h1>

<div style="float:left;width:550px;padding:10px 10px 0px 10px">
  <b>You have to be looged into the Google account that you use to make reservations with, to see the content of the calendars</b>
  <ul>
    <li><a href="#thetaprobe">Thetaprobe</a></li>
  </ul>
</div>

<div style="float:left;width:280px;background-color:#99CC00;padding:0px 10px 0px 10px">
  <p>To make a reservation click the "+Google Calendar" botton at the bottom right of the calendar. Before you can make reservations in the calendars you will have to:</p>
    <ul>
      <li>Set up a Google account (if you do not already have one)</li>
      <li>Contact <a href="mailto:k.nielsen81@gmail.com">Kenneth Nielsen</a> (in the case of the Thetaprobe) or <a href="mailto:christian.damsgaard@cen.dtu.dk">Christian Damsgaard</a> (in the case of the XRD) and give him you Google email and tell him which calendars you need access to</li>
    </ul>
</div>

<iframe id="thetaprobe" src="https://www.google.com/calendar/embed?src=dmq8ag678l31f0dec0r1s29rmk%40group.calendar.google.com&ctz=Europe/Copenhagen" style="border: 0" width="800" height="600" frameborder="0" scrolling="no"></iframe>

<?php
include("../common_functions_v2.php");
echo(html_footer());
?>
