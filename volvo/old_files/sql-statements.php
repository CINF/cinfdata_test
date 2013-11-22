<?
function sql_query($querytype,$params=""){
    $query = "";
    switch ($querytype){
    case "pressure":
        $query = "SELECT unix_timestamp(time), pressure FROM pressure where time between \"" . $params["from"] . "\" and \"" . $params["to"] ."\" order by time";
        break;
    case "temperature":
        $query = "SELECT unix_timestamp(time), temperature FROM temperature where temperature between 0 and 500 and time between \"" . $params["from"] . "\" and \"" . $params["to"] ."\" order by time";
    }
    return($query);
}
?>
