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
  echo("  <tr>\n");
  echo("    <th>ID</th><th>Quiries</th><th>Parameters</th><th>Check</th>" .
       "<th>No repeat interval</th><th>Message</th><th>Recipients</th>" .
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
  $check = htmlentities($row[3]);

  # Output the table row
  echo("  <tr>\n");
  echo("    <td>{$row[0]}</td><td>$quiries</td><td>{$row[2]}</td>" .
       "<td>$check</td><td>{$row[4]}</td><td>$message</td>" .
       "<td>$recipients</td>");
  # Only show edit button on unlocked items
  if ($row[7] == 0){
    echo("<td><input name=\"action\" type=\"submit\" value=\"edit {$row[0]}\"><br><br>\n");
    echo("<input name=\"action\" type=\"submit\" value=\"delete {$row[0]}\"></td>\n");
  } else {
    echo("<td></td>\n");
  }
  echo("  </tr>\n");
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
  } elseif ($row === null){  # Add new alarm
    $alarm_id = null;
    $check = "";
    $no_repeat_interval = 3600;
    $quiries = array_fill(0, 10, "");
    $recipients = array_fill(0, 10, "");
    $parameters = array_fill(0, 10, "");
    $message = "";
  } else {  # Edit existing
    $alarm_id = $row[0];
    $check = $row[3];
    $no_repeat_interval = $row[4];
    $quiries = from_json_to_array($row[1]);
    $recipients = from_json_to_array($row[6]);
    $parameters = from_json_to_array($row[2]);
    $message = $row[5];
  }

  echo("<form action=\"alarms.php\">\n");
  echo("<input name=\"alarm_id\" type=\"hidden\" value=\"$alarm_id\">\n");

  # Output check, no_repeat_interval and message inputs
  echo("<table>\n");
  echo("<tr>" .
       "<td>Check</td>" .
       "<td><input style=\"width:100%\" type=\"text\" name=\"check\" value=\"$check\"></td>" .
       "</tr>\n");
  echo("<tr>" .
       "<td>No repeat interval [s]</td>" .
       "<td><input style=\"width:100%\" type=\"number\" name=\"no_repeat_interval\" value=\"$no_repeat_interval\"></td>" .
       "</tr>\n");
  echo("<tr>" .
       "<td>Email body</td>" .
       "<td><textarea name=\"message\" rows=\"8\" cols=\"80\">" . htmlentities($message) . "</textarea></td>" .
       "</tr>\n");
  echo("</table>\n");

  # Output parameters, quiries and recipients table
  echo("<br>\n");
  echo("<table class=\"nicetable\">\n");
  echo("<col width=\"3%\">\n");
  echo("<col width=\"7%\">\n");
  echo("<col width=\"20%\">\n");
  echo("<col width=\"70%\">\n");
  echo("<tr><th>#</th><th>Parameter</th><th>Recipient</th><th>Query</th></tr>\n");
  for ($n = 0; $n <= 10; $n++) {
    echo("<tr><td>$n</td>" .
	 "<td><input style=\"width:100%\" type=\"number\" name=\"parameters[]\" step=\"any\" value=\"{$parameters[$n]}\"></td>" . 
	 "<td><input style=\"width:100%\" type=\"email\" name=\"recipients[]\" value=\"{$recipients[$n]}\"></td>" .
	 "<td><input style=\"width:100%\" type=\"text\" name=\"quiries[]\" value=\"{$quiries[$n]}\"></td>" .
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
		  "no_repeat_interval" => (int) $_GET["no_repeat_interval"]);

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

  $query = "INSERT INTO alarm (quiries_json, parameters_json, `check`, " .
    "no_repeat_interval, message, recipients_json, locked) VALUES (?, ?, ?, ?, ?, ?, ?)";
  $statement = $dbi->prepare($query);
  # bind parameters for markers, where (s = string, i = integer, d = double,
  #                                     b = blob)

  $locked = 0;
  $statement->bind_param('sssissi',
			 $data["quiries_json"],
			 $data["parameters_json"],
			 $data["check"],
			 $data["no_repeat_interval"],
			 $data["message"],
			 $data["recipients_json"],
			 $locked);
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

  # If the data is insufficient, set the screen message and return  $query = "UPDATE alarm SET visible=0 WHERE `id`=?";
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

  $query = "UPDATE alarm SET quiries_json=?, parameters_json=?, `check`=?, " .
    "no_repeat_interval=?, message=?, recipients_json=? WHERE `id`=?";
  $statement = $dbi->prepare($query);
  # bind parameters for markers, where (s = string, i = integer, d = double,
  #                                     b = blob)
  $data["id"] = (int) $data["id"];
  $statement->bind_param('sssissi', $data["quiries_json"], $data["parameters_json"], $data["check"], $data["no_repeat_interval"], $data["message"], $data["recipients_json"], $data["id"]);
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

echo("<h1><a id=\"existing\"></a>Existing alarms</h1>\n");
existing_alarms();

if ($action == "new"){
  echo("<h1>Enter new alarm</h1>");
  edit_table(null);
} elseif ($action == "continue new"){
  echo("<h1>Enter new alarm</h1>");
  edit_table($action);
} elseif ($action == "continue edit"){
  echo("<h1><a id=\"edit_alarm\"></a>Edit alarm number $alarm_number</h1>");
  edit_table($action);
} else {
  echo("<h1><a id=\"edit_alarm\"></a>Edit alarm number $alarm_number</h1>");
  edit_table($alarm_data[$alarm_number]);
}

echo(html_footer());

?>