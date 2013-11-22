<?
function plot_settings($type,$params=""){
    $settings = "";
    switch($type){
    case "pressure_asg":
        $settings["query"] = "SELECT unix_timestamp(time), pressure FROM pressure_xrdgas_asg where time between \"" . $params["from"] . "\" and \"" . $params["to"] ."\" order by time desc";
        $settings["ylabel"] = "Pressure / mbar";
        $settings["titel"] = "Pressure in XRD";
        $settings["default_yscale"] = "log";
        $settings["default_xscale"] = "dat";
        break;
    case "pressure_wrg":
        $settings["query"] = "SELECT unix_timestamp(time), pressure FROM pressure_xrdgas_wrg where time between \"" . $params["from"] . "\" and \"" . $params["to"] ."\" order by time desc";
        $settings["ylabel"] = "Pressure / mbar";
        $settings["titel"] = "Pressure in XRD";
        $settings["default_yscale"] = "log";
        $settings["default_xscale"] = "dat";
        break;
    case "pressure_wrgms":
        $settings["query"] = "SELECT unix_timestamp(time), pressure FROM pressure_xrdgas_wrgms where time between \"" . $params["from"] . "\" and \"" . $params["to"] ."\" order by time desc";
        $settings["ylabel"] = "Pressure / mbar";
        $settings["titel"] = "Pressure in XRD";
        $settings["default_yscale"] = "log";
        $settings["default_xscale"] = "dat";
        break;
    case "morning_pressure":
        $settings["query"] = "select unix_timestamp(date(time)), avg(pressure) from pressure_sputterkammer where hour(time) = 6 and minute(time) between 00 and 20 and time between \"" . $params["from"] . "\" and \"" . $params["to"] ."\" group by date(time) order by time desc limit 30";
        $settings["ylabel"] = "Pressure / Torr";
        $settings["default_yscale"] = "log";
        $settings["default_xscale"] = "dat";
        $settings["default_style"] = "barplot";
        break;
    case "morning_temperature":
        $settings["query"] = "select unix_timestamp(date(time)), avg(temperature) from heating_data_sputterkammer where hour(time) = 6 and minute(time) between 00 and 20 and time between \"" . $params["from"] . "\" and \"" . $params["to"] ."\" group by date(time) order by time desc limit 30";
        $settings["ylabel"] = "Temperature / C";
        $settings["default_yscale"] = "lin";
        $settings["default_xscale"] = "dat";
        $settings["default_style"] = "barplot";
        break;
    case "massspectrum":
        $settings["query"] = "SELECT x,y  FROM xy_values_bifrost where measurement = " . $params["id"] . " order by id";
        $settings["offset_query"] = "SELECT min(y) FROM xy_values_bifrost where measurement = " . $params["id"];
        $settings["type"] = 4; // The type as defined in the types table
        $settings["xlabel"] = "Mass / amu";
        $settings["ylabel"] = "SEM current / A";
        $settings["titel"] = "Mass Spectrum";
        $settings["default_xscale"] = "lin";
        $settings["default_yscale"] = "lin";
        $settings["param0_field"] = "sem_voltage";
        $settings["param0_name"] = "SEM Voltage / V";
        $settings["param1_field"] = "preamp_range";
        $settings["param1_name"] = "Range";
        $settings["param2_field"] = "mass_spec_id";
        $settings["param2_name"] = "Massspec ID";       
	 break;
    case "masstime":
        $settings["query"] = "SELECT x/1000,y  FROM xy_values_bifrost where measurement = " . $params["id"] . " order by id";
        $settings["offset_query"] = "SELECT min(y) FROM xy_values_bifrost where measurement = " . $params["id"] . " order by id";
        $settings["type"] = 5; // The type as defined in the types table
        $settings["xlabel"] = "Time / s";
        $settings["ylabel"] = "SEM current / A";
        $settings["titel"] = "Mass-time";
        $settings["default_xscale"] = "lin";
        $settings["default_yscale"] = "lin";
        $settings["param0_field"] = "sem_voltage";
        $settings["param0_name"] = "SEM Voltage / V";
        $settings["param1_field"] = "preamp_range";
        $settings["param1_name"] = "Range";
        $settings["param2_field"] = "mass_label";
        $settings["param2_name"] = "Mass Label";
        break;
    }
    $settings["measurements_table"] = "measurements_xrdgas";
    $settings["xyvalues_table"] = "xy_values_xrdgas";
    return($settings);
}
?>
