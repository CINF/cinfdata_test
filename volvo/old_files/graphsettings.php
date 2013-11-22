<?
function plot_settings($type,$params=""){
    $settings = "";
    switch($type){
    case "pressure":
        $settings["query"] = "SELECT unix_timestamp(time), pressure FROM pressure_volvo where pressure > 1e-12 and time between \"" . $params["from"] . "\" and \"" . $params["to"] ."\" order by time desc";
        $settings["ylabel"] = "Pressure / mbar";
        $settings["titel"] = "Pressure in the Volvo";
        $settings["default_yscale"] = "log";
        $settings["default_xscale"] = "dat";
        break;
    case "morning_pressure":
        $settings["query"] = "select unix_timestamp(date(time)), avg(pressure) from pressure_volvo where hour(time) = 6 and minute(time) between 00 and 20 and time between \"" . $params["from"] . "\" and \"" . $params["to"] ."\" group by date(time) order by time desc limit 30";
        $settings["ylabel"] = "Pressure / mbar";
        $settings["titel"] = "Pressure at 6 am in the Volvo";
        $settings["default_yscale"] = "log";
        $settings["default_xscale"] = "dat";
        $settings["default_style"] = "barplot";
        break;
    case "temperature":
        $settings["query"] = "SELECT unix_timestamp(time), temperature FROM temperature_volvo where temperature between -1 and 1300 and time between \"" . $params["from"] . "\" and \"" . $params["to"] ."\" order by time";
        $settings["ylabel"] = "Temperature / C";
        $settings["titel"] = "Temperature in the Volvo";
        $settings["default_yscale"] = "lin";
        $settings["default_xscale"] = "dat";
        break;
    case "iv":
        $settings["query"] = "SELECT x,y  FROM xy_values_volvo where measurement = " . $params["id"] . " order by id";
        $settings["type"] = 1; // The type as defined in the types table
        $settings["xlabel"] = "Voltage / V";
        $settings["ylabel"] = "Current / A";
        $settings["titel"] = "IV-curves";
        $settings["default_xscale"] = "lin";
        $settings["default_yscale"] = "log";
        break;
    case "xps":
        $settings["query"] = "SELECT x,y  FROM xy_values_volvo where measurement = " . $params["id"] . " order by id";
        $settings["type"] = 2; // The type as defined in the types table
        $settings["xlabel"] = "Binding energy / eV";
        $settings["ylabel"] = "Counts / s";
        $settings["titel"] = "XPS data";
        $settings["default_xscale"] = "lin";
        $settings["default_yscale"] = "lin";
        $settings["param0_field"] = "pass_energy";
        $settings["param0_name"] = "Pass Energy / eV";
        break;
    case "iss":
        $settings["query"] = "SELECT x,y  FROM xy_values_volvo where measurement = " . $params["id"] . " order by id";
        $settings["type"] = 3; // The type as defined in the types table
        $settings["xlabel"] = "Energy / eV";
        $settings["ylabel"] = "Counts / s";
        $settings["titel"] = "ISS data";
        $settings["default_xscale"] = "lin";
        $settings["default_yscale"] = "lin";
        $settings["param0_field"] = "pass_energy";
        $settings["param0_name"] = "Pass Energy / eV";
        break;
    case "massspectrum":
        $settings["query"] = "SELECT x,y  FROM xy_values_volvo where measurement = " . $params["id"] . " order by id";
        $settings["offset_query"] = "SELECT min(y) FROM xy_values where measurement = " . $params["id"];
        $settings["type"] = 4; // The type as defined in the types table
        $settings["xlabel"] = "Mass / amu";
        $settings["ylabel"] = "SEM current / A";
        $settings["titel"] = "Mass Spectrum";
        $settings["default_xscale"] = "lin";
        $settings["default_yscale"] = "log";
        $settings["param0_field"] = "sem_voltage";
        $settings["param0_name"] = "SEM Voltage / V";
        $settings["param1_field"] = "preamp_range";
        $settings["param1_name"] = "Range";
        break;
    case "masstime":
        $settings["query"] = "SELECT x/1000,y  FROM xy_values_volvo where measurement = " . $params["id"] . " order by id";
        $settings["type"] = 5; // The type as defined in the types table
        $settings["xlabel"] = "Time / s";
        $settings["ylabel"] = "SEM current / A";
        $settings["titel"] = "Mass Spectrum";
        $settings["default_xscale"] = "lin";
        $settings["default_yscale"] = "lin";
        $settings["param0_field"] = "sem_voltage";
        $settings["param0_name"] = "SEM Voltage / V";
        $settings["param1_field"] = "preamp_range";
        $settings["param1_name"] = "Range";
        break;
    }
    $settings["measurements_table"] = "measurements_volvo";
    $settings["xyvalues_table"] = "xy_values_volvo";
    return($settings);
}
?>
