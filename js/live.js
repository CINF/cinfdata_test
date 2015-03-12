/* This file is the java script part of the live page on CINFDATA */

/* JSLint

   Known variables: console socket_defs socket_ids measurement_ids
   fig_data_subs figure_defs Dygraph document js_query window WebSocket
   MozWebSocket alert
 */

/*jslint continue: true, forin: true, plusplus: true */


/* BLA BLA */
subscription_id_to_socket_id = {};


function log_input() {
    /* Log the script input: socket_defs, measurement_ids, fig_data_subs and
       firgure_defs
       NOTE. This data is inserted into the javascript environment in a
       separate script part in live.php
    */
    "use strict";

    // Define variables
    var key, key_a, i, j, socket_defs_tmp={};

    console.log("### DATA FROM PHP START");
    // Make sure socket_defs is object
    socket_defs = array_to_object(socket_defs);
    console.log("socket_defs:", socket_defs);

    // measurement_ids, make sure measurement_ids is object
    measurement_ids = array_to_object(measurement_ids);
    for (key in measurement_ids) {
        console.log('measurement_ids', key, ':', measurement_ids[key]);
    }

    // fig_data_subs, make sure measurement_ids is object
    fig_data_subs = array_to_object(fig_data_subs);
    for (key_a in fig_data_subs) {
        for (key in fig_data_subs[key_a]) {
            for (j = 0; j < fig_data_subs[key_a][key].length; j++) {
                console.log("Figure data subscription at socket:", key_a, "id:",
                            key, "sub:",
                            JSON.stringify(fig_data_subs[key_a][key][j]));
            }
        }
    }

    // figure_defs
    console.log("FFFF", figure_defs);
    for (key in figure_defs) {
        console.log("Figure definition for:", key, ":",
                    JSON.stringify(figure_defs[key]));
    }
    console.log("### DATA FROM PHP END\n\n");
}

/* ### Helper FUNCTIONS */
function array_to_object(arr){
    /* Returns an object from the array */
    "use strict";

    // Define variables
    var i, tmp={};

    if (Object.prototype.toString.call(arr) === "[object Array]"){
	for (var i = 0; i < arr.length; ++i){
	    tmp[i] = arr[i];
	}
	arr = tmp;
    }
    return arr;
}

function zeropad(number) {
    /* Retuns a padded version of a number less than 10
       NOTE: Return type is string if padded and int otherwise
    */
    "use strict";

    if (number < 10) {
        number = '0' + number;
    }
    return number;
}

function iso_time(date) {
    /* Return a text string time stamp like 08:17:07 from date */
    "use strict";

    // NOTE anything added with a string will be cast as a string, which
    // is why we don't care about the output type of zeropad
    var out = zeropad(date.getHours()) + ":" +
        zeropad(date.getMinutes()) + ":" + zeropad(date.getSeconds());
    return out;
}

function convert_boolean_string(string) {
    /* Convert a boolean string to boolean */
    "use strict";

    switch (string) {
    case "true":
        return true;
    case "false":
        return false;
    }
}

function format_data(value, format) {
    /* Format a float into a string using format strings like .2f and .2e */
    "use strict";

    /* Since we don't support numbers before the decimal point, the number of
       decimals is located at index [1: -1] */
    var decimals = parseInt(format.substring(1, format.length - 1), 10);

    // The format type is just the last character
    switch (format.substring(format.length - 1)) {
    case "f":
        return value.toFixed(decimals);
    case "e":
        return value.toExponential(decimals);
    }
}

/* ### Figure (pseudo) CLASS definition */
function MyFigure(name, definition) {
    /* Define a MyFigure object */
    "use strict";

    // Define variables
    var key, settings, plot_number, labels = ["Date"], colors = [], i, now,
        query, old_data, new_point, dummy_point,
        criteria_names = ["time", "absolute", "relative"];

    console.log("### INSTANTIATE FIGURE START:", name);
    // Setup generel stuff for the figure
    this.name = name;
    this.log("Figure definition from graphsetting:",
                JSON.stringify(definition));

    this.definition = definition;  // Save the definition
    this.last_update = new Date();
    // Define how much the figure jumps ahead when it reaches the end
    this.jump_ahead = 0.2;
    if (definition.hasOwnProperty("jump_ahead")) {
        this.jump_ahead = definition.jump_ahead;
    }

    // x-"window" start end end positions
    now = new Date();
    this.x_start = new Date(now.getTime() - ((1 - this.jump_ahead) * definition.x_window * 1000));
    this.x_end = new Date(this.x_start.getTime() + definition.x_window * 1000);
    console.log("Initial window interval", this.x_start, this.x_end);

    /* Get labels and colors and form initial "default" point and point
       template */
    dummy_point = [new Date(0)];
    this.data = [];
    this.data_template = [null];
    // Reduction parameters
    this.last_point_index = [];
    this.new_temporary_point = [];
    this.last_permanent_point_time = [];
    this.last_permanent_point_value = [];
    if (definition.hasOwnProperty("data_reduction")) {
        this.data_reduction = {};
        for (i = 0; i < criteria_names.length; i++) {
            if (definition.data_reduction.hasOwnProperty(criteria_names[i])) {
                this.data_reduction[criteria_names[i]] = parseFloat(definition.data_reduction[criteria_names[i]]);
            }
        }
    } else {
        this.data_reduction = null;
    }
    for (key in definition.figure) {
        if (key.indexOf("plot") === 0) {
            plot_number = parseInt(key.replace("plot", ""), 10);
            // Labels start with the x-axis label, hence the +1
            labels[plot_number + 1] = definition.figure[key].label;
            colors[plot_number] = definition.figure[key].color;
            // Initial "default" point
            dummy_point[plot_number + 1] = 1;
            this.data_template[plot_number + 1] = null;
            // Array with last plot index
            this.last_point_index[plot_number] = -1;
            this.last_permanent_point_time[plot_number] = new Date(0);
            this.last_permanent_point_value[plot_number] = 1.0E-100;
            this.new_temporary_point[plot_number] = true;
        }
    }

    // Pre-fill data
    for (key in definition.figure) {
        if (definition.figure[key].hasOwnProperty("old_data_query")) {
            plot_number = parseInt(key.replace("plot", ""), 10);
            query = definition.figure[key].old_data_query;
            query = query.replace("{from}", Math.floor(this.x_start.getTime() / 1000));
            old_data = JSON.parse(js_query(query));

            for (i = 0; i < old_data.length; i++) {
                new_point = this.data_template.slice(0);
                new_point[0] = new Date(old_data[i][0] * 1000);
                new_point[plot_number + 1] = old_data[i][1];
                this.data.push(new_point);
            }
            console.log("Added " + old_data.length + " \"old\" points to plot "
                        + plot_number);
        }
    }
    this.sort();  // Sort the data, because there might be several lines

    // Put dummy point in array if there are none, to make dygraphs happy
    if (this.data.length === 0) {
        this.data.push(dummy_point);
        this.first_call = true;  // Used to replace the default point on first call
    } else {
        this.first_call = false;
    }

    console.log("Labels:", labels);
    console.log("Colors:", colors);
    console.log("Initial data:", this.data[0].slice(0));
    console.log("Data template:", this.data_template);

    /* Form default settings */
    settings = {
        // Mandatory
        connectSeparatedPoints: true,
        labels: labels,
        colors: colors,
        dateWindow: [this.x_start, this.x_end],
	//width: parseInt(definition.width),
	//height: parseInt(definition.height),
        // Optional
        drawPoints: true,
        pointSize: 2,
        strokeWidth: 1.5,  // line width
        logscale: false,
        showLabelsOnHighlight: false
    };
    console.log("Default Dygraph settings:", JSON.stringify(settings));

    /* Update optional settings */
    // for points
    if (definition.hasOwnProperty("draw_points")) {
        settings.drawPoints = convert_boolean_string(definition.draw_points);
    }
    if (settings.drawPoints) {
        if (definition.hasOwnProperty("point_size")) {
            settings.pointSize = definition.point_size;
        }
    } else {
        delete settings.pointSize;
    }
    // linewidth
    if (definition.hasOwnProperty("line_width")) {
        settings.strokeWidth = definition.line_width;
    }
    // logscale setting
    if (definition.hasOwnProperty("logscale")) {
        settings.logscale = convert_boolean_string(definition.logscale);
    }

    /* Add optional settings */
    if (definition.hasOwnProperty("title")) {
        settings.title = definition.title;
        /* if (definition.title_height != null) {
            settings.titleHeight = definition.title_height;
        } */
    }
    if (definition.hasOwnProperty("xlabel")) {
        settings.xlabel = definition.xlabel;
        /* if (definition.xlabel_height != null) {
            settings.xLabelHeight = definition.xlabel_height;
        } */
    }
    if (definition.hasOwnProperty("ylabel")) {
        settings.ylabel = definition.ylabel;
        /* if (definition.ylabel_width != null) {
            settings.yLabelWidth = definition.ylabel_width;
        } */
    }

    /* EXPERIMENTAL, use yRangePad to fix bad y-axis ranges with log scale and
       constant values */
    if (definition.hasOwnProperty("yrangepad")) {
        settings.yRangePad = definition.yrangepad;
    }

    if (definition.hasOwnProperty("axislabelwidth")) {
	settings.axisLabelWidth = parseInt(definition.axislabelwidth);
    }

    if (definition.hasOwnProperty("format")){
	settings.axes = {
	    "y": {
		"axisLabelFormatter": function(y) {
		    return format_data(y, definition.format);
		}
	    }
	};
    }

    console.log("Make figure with updated Dygraph settings:",
                JSON.stringify(settings));
    this.fig = new Dygraph(document.getElementById(name), this.data, settings);
    console.log("### INSTANTIATE FIGURE END:", name, "\n\n");
}

// Function attached to MyFigure prototype, so something like a virtual method
MyFigure.prototype.addPoint = function (plot_n, date, value) {
    /* Adds a point to the figure */
    "use strict";

    // Define variables
    var i, cut, cutpoint, new_point = this.data_template.slice(0), last_index,
        now = new Date();
    new_point[0] = date;
    new_point[plot_n + 1] = value;

    // If it is the first call to draw, replace the dummy point and redraw
    if (this.first_call) {
        this.data = [];
    }

    if (this.data_reduction === null) {  // No data reduction
        this.data.push(new_point);
    } else {  // Data reduction        
        if (this.new_temporary_point[plot_n]) {
            this.data.push(new_point);
            this.last_point_index[plot_n] = this.data.length - 1;
            this.new_temporary_point[plot_n] = false;
        }

        last_index = this.last_point_index[plot_n];
        this.data[last_index] = new_point;
        if (this.time_to_add(plot_n, date, value)) {
            this.last_permanent_point_time[plot_n] = date;
            this.last_permanent_point_value[plot_n] = value;
            this.new_temporary_point[plot_n] = true;
        }
    }

    if (this.first_call) {
        this.fig.updateOptions({'file':  this.data});
        this.first_call = false;
    }
    // this.log("Number of points: " + this.data.length);

    // If the new points is outside the plot window
    if (date.getTime() > this.x_end) {
        cut = 0;

        /* Loop over the data to find the cutpoint. The cut point defines from
           where newer data should be saved, because it is still used after
           changing the plot window. The cut criteria is defined as:
               new_point_time - x_window * (1 - jump_ahead_fraction)
        */
        // Sort the data if we use data reduction, because that introduces
	// slight disorder
        if (this.data_reduction !== null) {
            this.sort();
            this.log("Sort in data reduction");
        }

        // Calculate the point from which older data is discarded and save it
        // as this.x_start
        this.x_start = new Date(date.getTime() - this.definition.x_window *
                                1000 * (1 - this.jump_ahead));
        this.x_end = new Date(this.x_start.getTime() +
                              this.definition.x_window * 1000);
        for (i = 0; i < this.data.length; i++) {
            if (this.data[i][0] > this.x_start) {
                cut = i;
                break;
            }
        }

        // Cut the data and update plot
        this.data = this.data.slice(cut);

        // Always force an update if the window has changed
        this.fig.updateOptions({dateWindow: [this.x_start, this.x_end]});
        this.last_update = date;
        // Make sure to add a new temporary point
        for (i = 0; i < this.new_temporary_point.length; i++) {
            this.new_temporary_point[i] = true;
        }
    }

    // Determine if it is time to update
    if (date - this.last_update > this.definition.update_interval * 1000) {
        this.last_update = date;
        this.fig.updateOptions({'file': this.data});
    }
};

// Function attached to MyFigure prototype, so something like a virtual method
MyFigure.prototype.time_to_add = function (plot_n, date, value) {
    /* Determines if it is time to add, if using data reduction */
    "use strict";
    var last_time, last_value, ratio;

    // Check time
    if (this.data_reduction.hasOwnProperty("time")) {
        last_time = this.last_permanent_point_time[plot_n];
        //this.log("Check time: " + (date.getTime() - last_time.getTime()));
        if (date.getTime() - last_time.getTime() >
                this.data_reduction.time * 1000) {
            //this.log("Check time true");
            return true;
        }
    }

    // Check absolute
    if (this.data_reduction.hasOwnProperty("absolute")) {
        last_value = this.last_permanent_point_value[plot_n];
        //this.log("Check absolute: " + Math.abs(value - last_value));
        if (Math.abs(value - last_value) > this.data_reduction.absolute) {
            //this.log("Check absolute true");
            return true;
        }
    }

    // Check relative
    if (this.data_reduction.hasOwnProperty("relative")) {
        last_value = this.last_permanent_point_value[plot_n];
        /* In Javascript you are allowed to divide by 0 !!!! Becomes Infinity
           which can be used for numerical comparisons */
        ratio = last_value / value;
        //this.log("Check relative: " + ratio);
        if (ratio < 1 - this.data_reduction.relative
                ||
                1 + this.data_reduction.relative < ratio) {
            //this.log("Check relative true");
            return true;
        }
    }

    return false;
};

// Function attached to MyFigure prototype, so something like a virtual method
MyFigure.prototype.log = function (string) {
    /* Logs to console with figure name prefixed */
    "use strict";
    console.log(this.name + " says: " + string);
};

// Function attached to MyFigure prototype, so something like a virtual method
MyFigure.prototype.sort = function () {
    /* Sort the data array according to date at index 0 in the rows */
    "use strict";
    this.data.sort(function (a, b) {
        // Compare the 2 dates
        if (a[0].getTime() < b[0].getTime()) {return -1; }
        if (a[0].getTime() > b[0].getTime()) {return 1; }
        return 0;
    });
};

// ### Functions used by the websocket callbacks
function parse_data(data) {
    /* Parse a data return string on the form:
         [registration_number, [[t1, v1], [t2, v2]]]
       where t, v are time, value sets for a point
     */
    "use strict";

    // Variable definitions
    var n, id_name, id, date, value, time, diff, el,
        value_elements, time_elements, diff_elements,
        fig_sub_index, sub, format, unit,
        now = new Date(),
        socket = subscription_id_to_socket_id[data[0]];

    // Loop over the number of codenames in subscriptions[socket]
    for (n in measurement_ids[socket]) {
        // Form the HTML id on the form 0#codename0
        id_name = measurement_ids[socket][n];
        id = String(socket).concat("#", id_name);
        // Get a date object from unixtime
        date = new Date(data[1][n][0] * 1000);
        value = data[1][n][1];
        time = iso_time(date);
        diff = (now - date);

        // Set all corresponding text elements, values, times and diffs
        value_elements = document.getElementsByClassName(id);
        for (el = 0; el < value_elements.length; el++) {
            format = value_elements[el].attributes["data-format"].value;
            unit = value_elements[el].attributes["data-unit"].value;
            // Add small space before unit, if it is not degC
            if (unit !== "&deg;C") {
                unit = "&thinsp;" + unit;
            }
            value_elements[el].innerHTML = format_data(value, format) + unit;
        }
        // Time elements
        time_elements = document.getElementsByClassName(id + "_time");
        for (el = 0; el < time_elements.length; el++) {
            time_elements[el].innerHTML = time;
        }
        // Diff elements
        diff_elements = document.getElementsByClassName(id + "_diff");
        for (el = 0; el < diff_elements.length; el++) {
            diff_elements[el].innerHTML = diff;
        }

        // loop continue of there are no subscriptions for this: socket, id
        if (!fig_data_subs.hasOwnProperty(socket)) {continue; }
        if (!fig_data_subs[socket].hasOwnProperty(id_name)) {continue; }

        // Send point(s) to all subscribed figures
        for (fig_sub_index in fig_data_subs[socket][id_name]) {
            sub = fig_data_subs[socket][id_name][fig_sub_index];
            window.figures[sub.figure_name].addPoint(sub.plot_index, date, value);
        }
    }
}


// ### Set window callbacks
window.onload = function () {
    /* Start everything up on load */
    "use strict";

    // Define variables, wsuri is websocket uri
    var i, key, msg, webSocket,
        wsuri = "wss://cinf-wsserver.fysik.dtu.dk:9001";

    // Log the variables input from php
    log_input();

    // Setup figures, windows.figures creates a global variable
    window.figures = {};
    for (key in figure_defs) {
        window.figures[key] = new MyFigure(key, figure_defs[key]);
    }

    // Work around Mozilla naming the websockets differently *GRR*
    console.log("### WebSocket Setup");
    if (window.hasOwnProperty("WebSocket")) {
        webSocket = new WebSocket(wsuri);
        console.log("Connect to websocket:", wsuri, "using WebSocket");
    } else {
        webSocket = new MozWebSocket(wsuri);
        console.log("Connect to websocket:", wsuri, "using MozWebSocket");
    }

    webSocket.onopen = function () {
        /* On ws open subscribe for data from machines (hostname:port) that are
        required for this page with the ws socket server.
        
        The ws server will respond with an echo of the request and the number
        of the subscription (one per hostname:port) and the sane interval. I.e.
        a request looks like:
          subscribe#hostname:port;codename0,codename1
        and the response looks like:
          subscribe#hostname:port;codename0,codename1#0#0.2
        */

        console.log("... WebSocket Connected!");
	console.log(socket_defs);
	for (key in socket_defs) {
            msg = "subscribe#".concat(socket_defs[key], ";", measurement_ids[key].join(','));
            console.log("Subscribe on machine: ".concat(msg));
            webSocket.send(msg);
	}
        /*for (i = 0; i < socket_defs.length; i++) {
            msg = "subscribe#".concat(socket_defs[i], ";", measurement_ids[i].join(','));
            console.log("Subscribe on machine: ".concat(msg));
            webSocket.send(msg);
	    }*/
    };

    webSocket.onclose = function (e) {
        /* on ws close show information on the console */
        console.log("ws: Closed (wasClean = " + e.wasClean + ", code = " +
                    e.code + ", reason = '" + e.reason + "')");
    };

    function parse_subscription(string) {
        /* Parse a subscription return string on the form:
             subscribe#rasppi25:8000;codename0,codename1#0#0.2
           where the last 0 and 0.2 are the subscription number and sane
           interval respectively
        */
        var socket_def, split = string.split("#");

        console.log("## Parse subscription reply START:", string);
        console.log("Subscription id:", split[2], "Sane interval", split[3]);

	// Add an entry in the object that translates subscription ids to
	// socket id numbers
	socket_def = split[1].split(";")[0];
	for (key in socket_defs){
	    if (socket_def == socket_defs[key]){
		subscription_id_to_socket_id[split[2]] = key;
	    }
	}
	console.log("id_to_socket_id:", subscription_id_to_socket_id);

        // Check for bad sane interval
        if (isNaN(parseFloat(split[3]) * 1000)) {
            alert("Bad sane interval on \"" + string +
                  "\". The data from this socket will not be available.");
            throw new Error("Bad sane interval on subscription" + string);
        }

        // Schedule a ws send of the subscription number once every sane
        // interval
        window.setInterval(
            function () {webSocket.send(split[2]); },
            parseFloat(split[3]) * 1000
        );
        console.log("## Parse subscription reply END:");
    }

    webSocket.onmessage = function (e) {
        /* ws onmessage: parse the message from JSON and act on it */
	//console.log(e);
        var message, data = JSON.parse(e.data);

        if (typeof data === "string") {
            if (data.indexOf("subscribe") === 0) {
                // The string is a subscription response
                parse_subscription(data);
            } else {
                // The strings is unknown, most likely an error
                message = "Recieved an unknown string: '" + data +
                    "' on the websocket. Shutting down!";
                throw new Error(message);
            }
        } else {
            // Assume that if type is not string, then it is data (array)
            parse_data(data);
        }
    };

    webSocket.onerror = function (e) {
        /* on ws error log to console */
        console.log("Websocket error:", e);
    };
};
