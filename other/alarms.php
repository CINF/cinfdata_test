<?php


include("../common_functions_v2.php");
$dbi = std_dbi("alarm");

# Holds the data on existing alarms
$alarm_data = Array();
$message_out = "";

/* --- Functions --- */

function msg($string, $alarm=false){
  if ($alarm){
    return "<p style=\"color:red\">" . htmlentities($string) . "</p>";
  } else {
    return "<p>" . htmlentities($string) . "</p>";
  }
}

function existing_alarms(){
  /* Prints out a table of exiting alarms */
  global $dbi;
  global $alarm_data;

  # Get the alarms
  $query = "SELECT * FROM alarm WHERE visible=1";
  $result = $dbi->query($query);

  # Start the table
  echo("<form action=\"alarms.php#edit_alarm\">\n");
  echo("<table border=\"1\" class=\"nicetable\">\n");
  echo("<tr>\n");
  echo("<th>ID<br><br>Description</th>" .
       "<th>Quiries</th>" .
       "<th>Parameters<br><br>Check</th>" .
       "<th style=\"width:100px\">No repeat interval<br><br>Active</th>" .
       "<th>Message</th>" .
       "<th>Subject<br><br>Recipients</th>" .
       "<th>Action</th>\n");
  echo("  </tr>\n");

  # Loop over alarms
  while($row = $result->fetch_row()) {
    # Save the alarm data for use in the edit table
    $alarm_data[$row[0]] = $row;
    # Generate the single alarm row
    single_alarm_row($row);
  }

  # End the table
  echo("</table>\n");
  echo("</form>\n");
}

function single_alarm_row($row){
  /* Generates a single alarm row

     The $row input is an array of the following items:
       [id, quiries_json, parameters_json, check, no_repeat_interval, message,
        recipients_json]

  */
  # Format the quiries for a table cell
  $quiries = JSON_decode($row[1]);
  foreach($quiries as $key => $query){
    $quiries[$key] = htmlentities($query);
  }
  $quiries = implode("<br><br>", $quiries);

  # Format the recipients, the message and the check for a table cell
  $recipients = implode("<br>", JSON_decode($row[6]));
  $message = str_replace("\n", "<br>", $row[5]);

  $escaped_row = Array();
  foreach($row as $key=>$value){
    $escaped_row[$key] = htmlentities($value);
  }
  $check = htmlentities($row[3]);

  $active = $row[11] == "1" ? "True"  : "False";

  # Output the table row
  echo("<tr>\n");
  echo("<td style=\"width:150px\">{$escaped_row[0]}</td>" .
       "<td rowspan=2 style=\"width:230px\">$quiries</td>" .
       "<td>{$escaped_row[2]}</td>" .
       "<td>{$escaped_row[4]}</td>" .
       "<td rowspan=2>$message</td>" .
       "<td>{$escaped_row[10]}</td>");
  # Only show edit button on unlocked items
  if ($row[7] == 0){
    echo("<td rowspan=2><input name=\"action\" type=\"submit\" value=\"edit {$row[0]}\"><br><br>\n");
    echo("<input name=\"action\" type=\"submit\" value=\"delete {$row[0]}\"></td>\n");
  } else {
    echo("<td rowspan=2>N/A</td>\n");
  }
  echo("</tr>\n");
  echo("</tr>");
  echo("<td>{$escaped_row[9]}</td>" .
       "<td>{$escaped_row[3]}</td>" .
       "<td>{$active}</td>" .
       "<td>$recipients</td>");
  echo("</tr>\n");
  echo("<tr style=\"background-color: #000000\"><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>");
}

function from_json_to_array($json){
  $array = JSON_decode($json);
  for ($n = 0; $n <= 10; $n++) {
    if (isset($array[$n])){
      $array[$n] = htmlentities($array[$n]);
    } else {
      $array[$n] = "";
    }
  }
  return $array;
}

function edit_table($row){
  /* Output edit table

     The $row input is an array of the following items:
       [id, quiries_json, parameters_json, check, no_repeat_interval, message,
        recipients_json]
  */
  # On continue edit or new, get the already filled in data from the url
  if ($row == "continue new" or $row == "continue edit"){
    $alarm_id = isset($_GET["alarm_id"]) ? $_GET["alarm_id"] : null; 
    $check = isset($_GET["check"]) ? $_GET["check"] : "";
    $no_repeat_interval = isset($_GET["no_repeat_interval"]) ? $_GET["no_repeat_interval"] : 3600;
    $quiries = isset($_GET["quiries"]) ? $_GET["quiries"] : array_fill(0, 10, "");
    $recipients = isset($_GET["recipients"]) ? $_GET["recipients"] : array_fill(0, 10, "");
    $parameters = isset($_GET["parameters"]) ? $_GET["parameters"] : array_fill(0, 10, "");
    $message = isset($_GET["message"]) ? $_GET["message"] : "";
    $description = isset($_GET["description"]) ? $_GET["description"] : "";
    $subject = isset($_GET["subject"]) ? $_GET["subject"] : "";
    $active = isset($_GET["active"]) ? $_GET["active"] : "";
  } elseif ($row === null){  # Add new alarm
    $alarm_id = null;
    $check = "";
    $no_repeat_interval = 3600;
    $quiries = array_fill(0, 10, "");
    $recipients = array_fill(0, 10, "");
    $parameters = array_fill(0, 10, "");
    $message = "";
    $description = "";
    $subject = "";
    $active = "checked";
  } else {  # Edit existing
    $alarm_id = $row[0];
    $check = $row[3];
    $no_repeat_interval = $row[4];
    $quiries = from_json_to_array($row[1]);
    $recipients = from_json_to_array($row[6]);
    $parameters = from_json_to_array($row[2]);
    $message = $row[5];
    $description = $row[9];
    $subject = $row[10];
    $active = $row[11] == "1" ? "checked" : "";
  }

  echo("<p><u>Hover over input fields for help</u></p>");
  echo("<form action=\"alarms.php\">\n");
  echo("<input name=\"alarm_id\" type=\"hidden\" value=\"$alarm_id\">\n");

  # Output check, no_repeat_interval and message inputs
  echo("<table>\n");
  $help = htmlentities("A short description used only to identify the alarm. " .
		       "Please start with the setup name, to make it easier " .
		       "to scan the alarms. Example: \n\n" .
		       "Thethaprobe load lock pump down");
  echo("<tr>" .
       "<td>Description</td>" .
       "<td><input title=\"$help\" style=\"width:100%\" type=\"text\" name=\"description\" value=\"$description\"></td>" .
       "</tr>\n");
  $help = "This is the expression that is evaluated to determine when " .
    "an alarm should be sent. In the check the follow entities can be used: " .
    "\"<\", \">\", \"and\", \"or\", \"q#\", \"p#\" and \"dqdt#\", where the ".
    "# is used to number placeholders for the query, parameter or slope of " .
    "a query respectively. Spaces are ignored.\n\n" .
    "Examples:\n" .
    "q0 > p0 and q1 < p1\n" .
    "dqdt0 > p0";
  $help = htmlentities($help);
  echo("<tr>" .
       "<td>Check</td>" .
       "<td><input title=\"$help\" style=\"width:100%\" type=\"text\" name=\"check\" value=\"$check\"></td>" .
       "</tr>\n");
  $help = htmlentities("If the alarm condition continues to be true, do not " .
		       "send a new alarm email until after this amount of " .
		       "seconds has passed. The check for alarms is run once " .
		       "per minute. Is used to prevent flooding of your inbox."
		       );
  echo("<tr>" .
       "<td>No repeat interval [s]</td>" .
       "<td><input title=\"$help\" style=\"width:100%\" type=\"number\" name=\"no_repeat_interval\" value=\"$no_repeat_interval\"></td>" .
       "</tr>\n");
  $help = htmlentities("The email body. To this body will be appended the " .
		       "check that evaluated to true.");
  echo("<tr>" .
       "<td>Email body</td>" .
       "<td><textarea title=\"$help\" name=\"message\" rows=\"8\" cols=\"80\">" . htmlentities($message) . "</textarea></td>" .
       "</tr>\n");
  $help = htmlentities("The subject of the alarm email");
  echo("<tr>" .
       "<td>Subject</td>" .
       "<td><input title=\"$help\" style=\"width:100%\" type=\"text\" name=\"subject\" value=\"$subject\"></td>" .
       "</tr>\n");
  $help = htmlentities("Whether this alarm is being checked or not");
  echo("<tr>" .
       "<td>Active</td>" .
       "<td><input title=\"$help\" style=\"width:100%\" type=\"checkbox\" name=\"active\" value=\"checked\" $active></td>" .
       "</tr>\n");
  echo("</table>\n");

  # Output parameters, quiries and recipients table
  $parameter_help = "A numeric parameter used in the check. Exponential " .
    "notation can be used.";
  $parameter_help = htmlentities($parameter_help);
  $recipient_help = htmlentities("An email adress for a recipient");
  $query_help = "A query to use in the check. The queries must return rows on " .
    "the form (unix timestamp, value). The queries must return exactly 1 row " .
    "EXCEPT if used for dqdt.";
  $query_help = htmlentities($query_help);
  echo("<br>\n");
  echo("<table class=\"nicetable\">\n");
  echo("<col width=\"3%\">" .
       "<col width=\"7%\">" .
       "<col width=\"20%\">" .
       "<col width=\"70%\">\n");
  echo("<tr><th>#</th><th>Parameter</th><th>Recipient</th><th>Query</th></tr>\n");
  for ($n = 0; $n <= 10; $n++) {
    echo("<tr><td>$n</td>" .
	 "<td><input title=\"$parameter_help\" style=\"width:100%\" type=\"number\" name=\"parameters[]\" step=\"any\" value=\"{$parameters[$n]}\"></td>" . 
	 "<td><input title=\"$recipient_help\" style=\"width:100%\" type=\"email\" name=\"recipients[]\" value=\"{$recipients[$n]}\"></td>" .
	 "<td><input title=\"$query_help\" style=\"width:100%\" type=\"text\" name=\"quiries[]\" value=\"{$quiries[$n]}\"></td>" .
	 "</tr>\n");
  } 
  echo("</table>\n");

  # Output submit buttons
  if ($row === null or $row == "continue new"){
    echo("<input name=\"action\" type=\"submit\" value=\"Submit New\">\n");
  } else {
    echo("<input name=\"action\" type=\"submit\" value=\"Submit Edit\">\n");
  }
  echo("</form>\n");

  echo("<form action=\"alarms.php\">\n");
  echo("<input name=\"action\" type=\"submit\" value=\"Cancel\"\n>");
  echo("</form>\n");

}

function prepare_db_data(){
  /* Prepares the data in the URL for insertion in the MySQL db */
  $output = Array("check" => $_GET["check"],
		  "id" =>  $_GET["alarm_id"],
		  "message" => str_replace("\r\n", "\n", $_GET["message"]),
		  "no_repeat_interval" => (int) $_GET["no_repeat_interval"],
		  "description" => $_GET["description"],
		  "subject" => $_GET["subject"]);

  # Get the boolean active
  $output["active"] = isset($_GET["active"]) ? "1" : "0";

  # Cut the arrays to non-empty values and jsonify
  foreach(Array("parameters", "recipients", "quiries") as $key){
    $array = Array();
    foreach($_GET[$key] as $value){
      if ($value != ""){
	if ($key == "parameters"){
	  $value = (float) $value;
	}
	$array[] = $value;	
      } else {
	break;
      }
    }
    $output[$key . "_json"] = JSON_encode($array);
  }

  # Checks for bad data
  if (!is_string($output["check"]) or $output["check"] == ""){
    return msg("A non-empty string must be given for the check", $alarm=true);
  }

  if (!is_string($output["message"])){
    return msg("A non-empty string must be given for the message", $alarm=true);
  }

  if (count(JSON_decode($output["recipients_json"])) < 1){
    return msg("At least 1 recipient must be set", $alarm=true);
  }

  return $output;
}

function p($object){
  echo("<pre>");
  echo(gettype($object) . " ");
  echo(htmlentities(print_r($object, $return=true)));
  echo("</pre>");
}

function insert_new(){
  global $dbi;
  global $message_out;

  # If the data is insufficient, set the screen message and return
  $data = prepare_db_data();
  if (is_string($data)){
    $message_out = $data;
    return false;
  }

  $query = "INSERT INTO alarm (" .
    "quiries_json, " .
    "parameters_json, " .
    "`check`, " .
    "no_repeat_interval, " .
    "message, " .
    "recipients_json, " .
    "locked, " .
    "description, " .
    "subject, " .
    "active" .
    ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
  $statement = $dbi->prepare($query);
  # bind parameters for markers, where (s = string, i = integer, d = double,
  #                                     b = blob)

  $locked = 0;
  $statement->bind_param('sssississi',
			 $data["quiries_json"],
			 $data["parameters_json"],
			 $data["check"],
			 $data["no_repeat_interval"],
			 $data["message"],
			 $data["recipients_json"],
			 $locked,
			 $data["description"],
			 $data["subject"],
			 $data["active"]
			 );

  if($statement->execute()){
    $message_out = msg("The new alarm was successfully added and given ID " .
		       "number: " . $statement->insert_id);
  } else {
    $message_out = msg("The following error occurred while " .
		       "trying to insert the new alarm: (" . $mysqli->errno .
		       ") " . $mysqli->error,
		       $alarm=true);
  }
  $statement->close();

  return true;
}

function delete_alarm($alarm_number){
  global $dbi;
  global $message_out;

  # If the data is insufficient, set the screen message and return
  $query = "UPDATE alarm SET visible=0 WHERE `id`=?";
  $statement = $dbi->prepare($query);
  $statement->bind_param('i', $alarm_number);
  if($statement->execute()){
    $message_out = "<p>Alarm " . $data["id"] . " successfully deleted</p>";
  } else {
    $message_out = msg("The following error occurred while trying to delete " .
		       "the alarm: (" . $mysqli->errno . ") " . $mysqli->error,
		       $alarm=true);
  }
  $statement->close();

}

function update_existing(){
  global $dbi;
  global $message_out;

  # If the data is insufficient, set the screen message and return
  $data = prepare_db_data();
  if (is_string($data)){
    $message_out = $data;
    return false;
  }

  $query = "UPDATE alarm SET " .
    "quiries_json=?, " .
    "parameters_json=?, " .
    "`check`=?, " .
    "no_repeat_interval=?, " .
    "message=?, " .
    "recipients_json=?, " .
    "description=?, " .
    "subject=?, " .
    "active=? " .
    "WHERE `id`=?";

  $statement = $dbi->prepare($query);

  # bind parameters for markers, where (s = string, i = integer, d = double,
  #                                     b = blob)
  $data["id"] = (int) $data["id"];
  $data["active"] = (int) $data["active"];
  $statement->bind_param('sssissssii',
			 $data["quiries_json"],
			 $data["parameters_json"],
			 $data["check"],
			 $data["no_repeat_interval"],
			 $data["message"],
			 $data["recipients_json"],
			 $data["description"],
			 $data["subject"],
			 $data["active"],
			 $data["id"]);
  if($statement->execute()){
    $message_out = "<p>Alarm " . $data["id"] . " was successfully updated</p>";
  } else {
    $message_out = msg("The following error occurred while trying to update " .
		       "the alarm: (" . $mysqli->errno . ") " . $mysqli->error,
		       $alarm=true);
  }
  $statement->close();

  return true;
}

/* --- Main Script --- */

# Parse action
$action = isset($_GET["action"]) ? $_GET["action"] : "new" ;
if (substr($action, 0, 4) === "edit"){
  $alarm_number = (int) substr($action, 5);
  $action = "edit";
} elseif ($action === "Submit New"){
  $db_result = insert_new();
  if (!$db_result){
    $action = "continue new";
  }
} elseif ($action === "Submit Edit"){
  $db_result = update_existing();
  if (!$db_result){
    $action = "continue edit";
  }
} elseif (substr($action, 0, 6) === "delete") {
  $alarm_number = (int) substr($action, 7);
  delete_alarm($alarm_number);
  $action = "new";
}

/* --- Main page output --- */

echo(html_header());
echo($message_out);


echo("<div style=\"float:right\">\n");
echo("<form action=\"alarms.php\">\n");
echo("<input name=\"action\" type=\"submit\" value=\"Cancel\"\n>");
echo("</form>\n");
echo("</div>\n");
echo("<h1><a id=\"existing\"></a>Existing alarms</h1>\n");

existing_alarms();

if ($action == "new"){
  echo("<h1>Enter new alarm</h1>\n");
  edit_table(null);
} elseif ($action == "continue new"){
  echo("<h1>Enter new alarm</h1>\n");
  edit_table($action);
} elseif ($action == "continue edit"){
  echo("<h1><a id=\"edit_alarm\"></a>Edit alarm number $alarm_number</h1>\n");
  edit_table($action);
} else {
  echo("<h1><a id=\"edit_alarm\"></a>Edit alarm number $alarm_number</h1>\n");
  edit_table($alarm_data[$alarm_number]);
}

echo(html_footer());

?>