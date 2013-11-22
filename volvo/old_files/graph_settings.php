<?
function sql_query($type,$params=""){
    $query = "";
    switch ($type){
    case "pressure":
        $query = "SELECT unix_timestamp(time), pressure FROM pressure where time between \"" . $params["from"] . "\" and \"" . $params["to"] ."\" order by time";
        break;
    case "temperature":
        $query = "SELECT unix_timestamp(time), temperature FROM temperature where temperature between 0 and 500 and time between \"" . $params["from"] . "\" and \"" . $params["to"] ."\" order by time";
    }
    return($query);
}

function xlabel($type){
    $label = "";
    switch($type){
    case "pressure":
        $label = "";
    }
    return($label);
}

function ylabel($type){
    $label = "";
    switch($type){
    case "pressure":
        $label = "Pressure / mbar";
        break;
    case "temperature":
         $label = "Temperature / C";
    }
    return($label);
}

function titel($type){
    $titel = "";
    switch($type){
    case "pressure":
        $titel = "Pressure in the Volvo";
        break;
    case "temperature":
         $titel = "Temperature in the Volvo";
    }
    return($titel);
}

function ylog($type){
    $value = false;
    switch($type){
    case "pressure":
        $value = true;
        break;
    }
    return($value);
}
?>
