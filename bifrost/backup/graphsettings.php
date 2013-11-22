<?
function plot_settings($type,$params=""){
    $settings = "";
    switch($type){
    case "pressure":
        $settings["query"] = "SELECT unix_timestamp(time), pressure FROM pressure_sputterkammer where time between \"" . $params["from"] . "\" and \"" . $params["to"] ."\" order by time desc";
        $settings["ylabel"] = "Pressure / Torr";
        $settings["titel"] = "Pressure in the Sputter chamber";
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
    case "temperature":
        $settings["query"] = "SELECT unix_timestamp(time), temperature FROM heating_data_sputterkammer where time between \"" . $params["from"] . "\" and \"" . $params["to"] ."\" order by time";
        $settings["ylabel"] = "Temperature / C";
        $settings["titel"] = "Temperature of the crystal";
        $settings["default_yscale"] = "lin";
        $settings["default_xscale"] = "dat";
        break;
    case "aes":
        $settings["query"] = "SELECT x,y  FROM xy_values_bifrost where measurement = " . $params["id"] . " order by id";
        $settings["type"] = 6; // The type as defined in the types table
        $settings["xlabel"] = "Kinetic energy / eV";
        $settings["ylabel"] = "Counts / 1/s";
        $settings["titel"] = "AES data";
        $settings["default_xscale"] = "lin";
        $settings["default_yscale"] = "lin";
        $settings["param0_field"] = "pass";
        $settings["param0_name"] = "Pass energy / eV";
        break;
    case "xps":
        $settings["query"] = "SELECT x,y  FROM xy_values_bifrost where measurement = " . $params["id"] . " order by id";
        $settings["type"] = 2; // The type as defined in the types table
        $settings["xlabel"] = "Binding energy / eV";
        $settings["ylabel"] = "Counts / 1/s";
        $settings["titel"] = "XPS data";
        $settings["default_xscale"] = "lin";
        $settings["default_yscale"] = "lin";
        $settings["param0_field"] = "pass";
        $settings["param0_name"] = "Pass energy / eV";
        break;
    case "heating_voltage":
        $settings["query"] = "SELECT unix_timestamp(time), rmsvoltage FROM heating_data_sputterkammer where time between \"" . $params["from"] . "\" and \"" . $params["to"] ."\" order by time";
        $settings["ylabel"] = "Voltage / V";
        $settings["titel"] = "Voltage across crystal";
        $settings["default_yscale"] = "lin";
        $settings["default_xscale"] = "dat";
        break;
    case "heating_current":
        $settings["query"] = "SELECT unix_timestamp(time), rmscurrent FROM heating_data_sputterkammer where time between \"" . $params["from"] . "\" and \"" . $params["to"] ."\" order by time";
        $settings["ylabel"] = "Current / A";
        $settings["titel"] = "Current through crystal";
        $settings["default_yscale"] = "lin";
        $settings["default_xscale"] = "dat";
        break;
    case "heating_power":
        $settings["query"] = "SELECT unix_timestamp(time), power FROM heating_data_sputterkammer where time between \"" . $params["from"] . "\" and \"" . $params["to"] ."\" order by time";
        $settings["ylabel"] = "Power / W";
        $settings["titel"] = "Power deposited in the crystal";
        $settings["default_yscale"] = "lin";
        $settings["default_xscale"] = "dat";
        break;
    case "heating_resistance":
        $settings["query"] = "SELECT unix_timestamp(time), rmsvoltage/rmscurrent FROM heating_data_sputterkammer where time between \"" . $params["from"] . "\" and \"" . $params["to"] ."\" order by time";
        $settings["ylabel"] = "Resistance / Ohm";
        $settings["titel"] = "Resistance of the crystal";
        $settings["default_yscale"] = "lin";
        $settings["default_xscale"] = "dat";
        break;
    case "acceleration_voltage":
        $settings["query"] = "SELECT unix_timestamp(time), accvoltage FROM ion_gun_data_sputterkammer where time between \"" . $params["from"] . "\" and \"" . $params["to"] ."\" order by time";
        $settings["ylabel"] = "Voltage / V";
        $settings["titel"] = "Acceleration voltage";
        $settings["default_yscale"] = "lin";
        $settings["default_xscale"] = "dat";
        break;
    case "emission_current":
        $settings["query"] = "SELECT unix_timestamp(time), emissioncurrent FROM ion_gun_data_sputterkammer where time between \"" . $params["from"] . "\" and \"" . $params["to"] ."\" order by time";
        $settings["ylabel"] = "Power / W";
        $settings["titel"] = "Power deposited in the crystal";
        $settings["default_yscale"] = "lin";
        $settings["default_xscale"] = "dat";
        break;
    case "sputter_current":
        $settings["query"] = "SELECT unix_timestamp(time), energycurrent FROM ion_gun_data_sputterkammer where time between \"" . $params["from"] . "\" and \"" . $params["to"] ."\" order by time";
        $settings["ylabel"] = "Power / W";
        $settings["titel"] = "Power deposited in the crystal";
        $settings["default_yscale"] = "lin";
        $settings["default_xscale"] = "dat";
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
    $settings["measurements_table"] = "measurements_bifrost";
    $settings["xyvalues_table"] = "xy_values_bifrost";
    return($settings);
}
?>
