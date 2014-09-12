<?php
  /*
    Copyright (C) 2012-2014 Robert Jensen, Thomas Andersen and Kenneth Nielsen
    
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

include("graphsettings.php");
include("../common_functions_v2.php");
echo(html_header());
$con = std_dbi();

echo("\n\n<h1>System status</h1>\n");

# Generel System Status
echo("\n<h2>Generel</h2>\n");
$query = "select * from status_b307gasalarm where device=254 " .
  "and time >= DATE_ADD(NOW(), INTERVAL -1 DAY) order by time desc";
$result = mysqli_query($con, $query);
echo("<table class=\"nicetable\" border=1>\n");
echo("<tr><th>Time</th><th>Standard checkin</th><th>Status strings</th></tr>\n");
while($row = mysqli_fetch_array($result)) {
  $status_array = json_decode($row['status']);
  foreach ($status_array as $key => $value){
    $status_array[$key] = "\"" . $value . "\"";
  }
  $status_str = implode("\", \"", $status_array);
  if ($status_str == "\"All OK\""){
    $status_str = "<td>" . $status_str . "</td>";
  } else {
    $status_str = "<td class=\"alert\">" . $status_str . "</td>";
  }
  $checkin = $row['check_in'] == 1 ? "<td>True</td>" : "<td class=\"alert\">False</td>";
  
  echo("<tr><td>${row['time']}</td> $checkin $status_str</tr>\n");
}
echo("</table>\n");

# System Power Status
echo("\n<h2>Power status</h2>\n");
$query = "select * from status_b307gasalarm where device=255 " .
  "and time >= DATE_ADD(NOW(), INTERVAL -1 DAY) order by time desc";
$result = mysqli_query($con, $query);
echo("<table class=\"nicetable\" border=1>");
echo("<tr><th>Time</th><th>Standard checkin</th><th>Status</th></tr>");
while($row = mysqli_fetch_array($result)) {
  $status_str = $row['status'];
  if ($status_str == "\"OK\""){
    $status_str = "<td>" . $status_str . "</td>";
  } else {
    $status_str = "<td class=\"alert\">" . $status_str . "</td>";
  }
  $checkin = $row['check_in'] == 1 ?  "<td>True</td>" : "<td class=\"alert\">False</td>";
  echo("<tr><td>${row['time']}</td> $checkin $status_str</tr>\n");
}
echo("</table>\n");

# Detector status
echo("\n<h2>Detector status</h2>\n");
$query = "select * from status_b307gasalarm where device<254 and " .
  "time >= DATE_ADD(NOW(), INTERVAL -1 DAY)" .
  "order by time desc";
$result = mysqli_query($con, $query);
echo("<table class=\"nicetable\" border=1>");
echo("<tr><th>Time</th><th>Detector</th><th>Codename</th><th>Standard checkin</th><th>Inhibit</th><th>Status</th></tr>");
while($row = mysqli_fetch_array($result)) {
  $checkin = $row['check_in'] == '1' ? "<td>True</td>" : "<td class=\"alert\">False</td>";
  $status_array = json_decode($row['status'], $assoc=True);

  $status_string_array = $status_array['status'];
  foreach ($status_string_array as $key => $value){
    $status_string_array[$key] = "\"" . $value . "\"";
  }
  $status_string = implode(", ", $status_string_array);
  if ($status_string == "\"OK\""){
    $status_string = "<td>" . $status_string . "</td>";
  } else {
    $status_string = "<td class=\"alert\">" . $status_string . "</td>";
  }
  
  $inhibit = $status_array['inhibit'] ? "<td class=\"alert\">True</td>" : "<td>False</td>";
  
  echo("<tr><td>${row['time']}</td><td>${row['device']}</td><td>${status_array['codename']}</td> $checkin $inhibit $status_string</tr>\n");
}
echo("</table>\n\n");


echo(html_footer());
?>