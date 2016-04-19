/* This file is the java script part of the live page on CINFDATA */

/* jslint enforceall: true, camelcase: false, maxlen: 90 */

/* globals console, data_channels, subscription_map, send_to_html, figure_defs,
   js_query, Dygraph, document, window, WebSocket, MozWebSocket, location
*/

function log_input() {
    /* Log the script input: data_channels, subscription_map, send_to_html,
       figure_defs

       NOTE. This data is inserted into the javascript environment in a
       separate script part in live.php
    */
    "use strict";

    console.log("### DATA FROM PHP START");

    // Output list of all needed data_channels
    console.log("All required data channels:", data_channels);

    // Make sure subscription_map is an object and log it
    console.log("Subscription map:", subscription_map);

    // Output the list of data channels that should be sent to HTML objects
    console.log("Data channels that should be sent to HTML objects:", send_to_html);

    // Log the figure definition parts:
    console.log("Figure definitions", figure_defs);
}

/* ### Helper FUNCTIONS */
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
    default:
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
    default:
        return String(value);
    }
}

/* ### Figure (pseudo) CLASS definition */
function MyFigure(name, definition) {
    /* Define a MyFigure object */
    "use strict";

    // Define variables
    var key, settings, plot_number, labels = ["Date"], colors = [], i, now,
        query, old_data, new_point, data_channel,
        criteria_names = ["time", "absolute", "relative"];

    console.log("### INSTANTIATE FIGURE START:", name);
    // Setup generel stuff for the figure
    this.name = name;
    this.log("Figure def from graphsetting: %o", definition);

    this.definition = definition;  // Save the definition
    this.type = definition.type;
    this.last_update = new Date();
    // Define how much the figure jumps ahead when it reaches the end
    this.jump_ahead = 0.2;
    if (definition.hasOwnProperty("jump_ahead")) {
        this.log("Change jump ahead from default 0.2 to %s", [definition.jump_ahead]);
        this.jump_ahead = definition.jump_ahead;
    }

    // x-"window" start end end positions
    now = new Date();
    this.x_start = new Date(now.getTime() -
                            (1 - this.jump_ahead) * definition.x_window * 1000);
    this.x_end = new Date(this.x_start.getTime() + definition.x_window * 1000);
    this.log("Initial window interval: %s, %s", [this.x_start, this.x_end]);

    /* Get labels and colors and form initial "default" point and point
       template */
    if (this.type === "date_figure"){
        this.dummy_point = [new Date(0)];
        // Set addPoint
        this.addPoint = this.addDatePoint;
    } else {
        this.dummy_point = [47];
        this.addPoint = this.addXYPoint;
    }
    this.data = [];
    this.data_template = [null];
    // Reduction parameters
    this.last_point_index = [];
    this.new_temporary_point = [];
    this.last_permanent_point_time = [];
    this.last_permanent_point_value = [];
    if (definition.hasOwnProperty("data_reduction")) {
        this.data_reduction = {};
        for (i = 0; i < criteria_names.length; i+=1) {
            if (definition.data_reduction.hasOwnProperty(criteria_names[i])) {
                this.data_reduction[criteria_names[i]] =
		    parseFloat(definition.data_reduction[criteria_names[i]]);
            }
        }
    } else {
        this.data_reduction = null;
    }
    this.log("Data reduction set to: %o", this.data_reduction);

    this.plot_map = {};
    // FIXME Do something here to make it possible to find data slots from codenames
    for (key in definition.figure) {
        if (key.indexOf("plot") === 0) {
            plot_number = parseInt(key.replace("plot", ""), 10);
            // Labels start with the x-axis label, hence the +1
            labels[plot_number + 1] = definition.figure[key].label;
            colors[plot_number] = definition.figure[key].color;
            // Initial "default" point
            this.dummy_point[plot_number + 1] = 1;
            this.data_template[plot_number + 1] = null;
            // Array with last plot index
            this.last_point_index[plot_number] = -1;
            this.last_permanent_point_time[plot_number] = new Date(0);
            this.last_permanent_point_value[plot_number] = 1.0E-100;
            this.new_temporary_point[plot_number] = true;
            /* Add entries into plot_map. plot_map is a mapping of
               data_channels to an array of plot_numbers. E.g:
               {'rasppi4:meas1': [0, 2]}
            */
            data_channel = definition.figure[key].data_channel;
            if (this.plot_map[data_channel] === undefined){
                this.plot_map[data_channel] = [plot_number];
            } else {
                this.plot_map[data_channel].push(plot_number);
            }
        }
    }
    this.log("Plot map: %o", this.plot_map);
    this.log("Labels: %o", labels);
    this.log("Colors: %o", colors);

    // Pre-fill data
    for (key in definition.figure) {
        if (definition.figure[key].hasOwnProperty("old_data_query")) {
            plot_number = parseInt(key.replace("plot", ""), 10);
            query = definition.figure[key].old_data_query;
            query = query.replace("{from}", Math.floor(this.x_start.getTime() / 1000));
            old_data = JSON.parse(js_query(query));

            for (i = 0; i < old_data.length; i+=1) {
                new_point = this.data_template.slice(0);
                new_point[0] = new Date(old_data[i][0] * 1000);
                new_point[plot_number + 1] = old_data[i][1];
                this.data.push(new_point);
            }
            console.log("Added " + old_data.length +
                        " \"old\" points to plot " + plot_number);
        }
    }
    this.sort();  // Sort the data, because there might be several lines

    // Put dummy point in array if there are none, to make dygraphs happy
    if (this.data.length === 0) {
        this.data.push(this.dummy_point);
        this.first_call = true;  // Used to replace the default point on first call
    } else {
        this.first_call = false;
    }

    this.log("Initial data: %o", this.data[0].slice(0));
    this.log("Data template: %o", this.data_template);

    /* Form default settings */
    settings = {
        // Mandatory
        connectSeparatedPoints: true,
        labels: labels,
        colors: colors,
        //dateWindow: [this.x_start, this.x_end],
        //width: parseInt(definition.width),
        //height: parseInt(definition.height),
        // Optional
        drawPoints: true,
        pointSize: 2,
        strokeWidth: 1.5,  // line width
        logscale: false,
        showLabelsOnHighlight: false
    };
    if (this.type === "date_figure"){
        settings.dateWindow = [this.x_start, this.x_end];
    }
    this.log("Default Dygraph settings: %o", settings);

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
    }
    if (definition.hasOwnProperty("xlabel")) {
        settings.xlabel = definition.xlabel;
    }
    if (definition.hasOwnProperty("ylabel")) {
        settings.ylabel = definition.ylabel;
    }

    if (definition.hasOwnProperty("axislabelwidth")) {
        settings.axisLabelWidth = parseInt(definition.axislabelwidth, 10);
    }

    if (definition.hasOwnProperty("format")){
        settings.axes = {
            "y": {
                "axisLabelFormatter":
		function(y) {return format_data(y, definition.format);}
            }
        };
    }

    this.log("Make figure with updated Dygraph settings: %o", settings);
    this.fig = new Dygraph(document.getElementById(name), this.data, settings);
    console.log("### INSTANTIATE FIGURE END:", name, "\n\n");
}

// Function attached to MyFigure prototype, so something like a virtual method
MyFigure.prototype.addBatch = function (data_batch){
    /* Adds a batch of points to the figure */
    "use strict";

    var value, plots, key;

    for (key in data_batch){
        if (!data_batch.hasOwnProperty(key)){continue;}
        value = data_batch[key];
        if (value === 'RESET'){
            this.data = [this.dummy_point];
            this.first_call = true;
        } else {
            plots = this.plot_map[key];
            if (plots !== undefined){
                for (var i in plots){
                    if (!plots.hasOwnProperty(i)){continue;}
                    this.addPoint(plots[i], value[0], value[1]);
                }
            }
        }
    }

    // Update figure
    this.fig.updateOptions({'file': this.data});
};

MyFigure.prototype.addXYPoint = function (plot_n, x, y){
    /* Adds a point to the figure */
    "use strict";

    // Define variables
    var new_point = this.data_template.slice(0);
    new_point[0] = x;
    new_point[plot_n + 1] = y;

    // If it is the first call to draw, replace the dummy point and redraw
    if (this.first_call) {
        this.data = [];
    }

    // Push point
    this.data.push(new_point);

    // Update on first call
    if (this.first_call) {
        this.fig.updateOptions({'file':  this.data});
        this.first_call = false;
    }
};

// Function attached to MyFigure prototype, so something like a virtual method
MyFigure.prototype.addDatePoint = function (plot_n, date, value) {
    /* Adds a point to the figure */
    "use strict";

    date = new Date(date * 1000);

    // Define variables
    var i, cut, new_point = this.data_template.slice(0), last_index;
    new_point[0] = date;
    new_point[plot_n + 1] = value;

    // If it is the first call to draw, replace the dummy point and redraw
    if (this.first_call) {
        this.data = [];
    }

    if (this.data_reduction === null) {  // No data reduction
        this.data.push(new_point);
    } else {
        // Data reduction
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
        for (i = 0; i < this.data.length; i+=1) {
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
        for (i = 0; i < this.new_temporary_point.length; i+=1) {
            this.new_temporary_point[i] = true;
        }
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
        if (ratio < 1 - this.data_reduction.relative ||
            1 + this.data_reduction.relative < ratio) {
            //this.log("Check relative true");
            return true;
        }
    }

    return false;
};

// Function attached to MyFigure prototype, so something like a virtual method
MyFigure.prototype.log = function (string, args) {
    /* Logs to console with figure name prefixed */
    "use strict";
    console.log(this.name + ": " + string, args);
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
function handle_batch_data(data) {
    /* Handle a data batch. The data batch is on the form:
       {'host': 'rasppi71', data:
        {'sine1': [123.4, 567.8], 'sine2': [567.8, 123.4]}}
    */
    "use strict";

    // Variable definitions UPDATE
    var containers, figure, property, element;

    // Make new associative array and prepend the host to the codename
    var data_with_hostname = {};
    var host = data.host;
    for (property in data.data) {
        if (!data.data.hasOwnProperty(property)){continue;}
        data_with_hostname[host + ':' + property] = data.data[property];
    }

    // Find figures to send this batch to
    var send_to_containers = {};
    for (property in data_with_hostname) {
        if (data_with_hostname.hasOwnProperty(property)) {
            containers = subscription_map[property];
            if (containers !== undefined){
                for (var item in containers){
                    if (!containers.hasOwnProperty(item)){continue;}
                    send_to_containers[containers[item]] = true;
                }
            }
        }
    }

    // Send the data batch to all subscribed figures
    for (var container in send_to_containers){
        if (send_to_containers.hasOwnProperty(container)){
            figure = window.figures[container].addBatch(data_with_hostname);
        }
    }

    // Send the data points to tables
    for (var id in data_with_hostname){
        if (!data_with_hostname.hasOwnProperty(id)){continue;}

        var html_id = id.replace(":", "_");
        var point_data = data_with_hostname[id];

        // Reset events should not be handled in tables
        if (point_data === "RESET"){continue;}

        var now = new Date();
        var date = new Date(point_data[0] * 1000);
        var value = point_data[1];
        var time = iso_time(date);
        var diff = now - date;

        // Set all corresponding text elements, values, times and diffs
        var value_elements = document.getElementsByClassName(html_id);
        for (var el = 0; el < value_elements.length; el+=1) {
            var format = value_elements[el].attributes["data-format"].value;
            var unit = value_elements[el].attributes["data-unit"].value;
            // Add small space before unit, if it is not degC
            if (unit !== "&deg;C") {
                unit = "&thinsp;" + unit;
            }
            if (format === 'text'){
                value_elements[el].innerHTML = value + unit;
            } else {
                value_elements[el].innerHTML = format_data(value, format) + unit;
            }
        }
        // Time elements
        var time_elements = document.getElementsByClassName(html_id + "_time");
        for (element = 0; element < time_elements.length; element+=1) {
            var xformat = time_elements[element].attributes.xformat.value;
            if (xformat !== ""){
                time_elements[element].innerHTML = format_data(point_data[0], xformat);
            } else {
                time_elements[element].innerHTML = time;
            }
        }
        // Diff elements
        var diff_elements = document.getElementsByClassName(html_id + "_diff");
        for (element = 0; element < diff_elements.length; element+=1) {
            diff_elements[element].innerHTML = diff;
        }
    }
}


// ### Set window callbacks
window.onload = function () {
    /* Start everything up on load */
    "use strict";

    // Define variables, wsuri is websocket uri
    var key, webSocket,
        wsuri = "wss://cinf-wsserver.fysik.dtu.dk:9002";

    // Log the variables input from php
    log_input();

    // Setup figures, windows.figures creates a global variable
    window.figures = {};
    for (key in figure_defs) {
        if (!figure_defs.hasOwnProperty(key)){continue;}
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
        console.log("Subscribe to data channels: %o", data_channels);
        webSocket.send(JSON.stringify(
	    {'action': 'subscribe', 'subscriptions': data_channels}
	));
    };

    webSocket.onclose = function (e) {
        /* on ws close remove all graphs and inform the user of impending reload */
        console.log("WebSocket closed: %o", e);

        // Remove all plot and table divs and change a few properties
        var div = document.getElementsByClassName("plotcontainer")[0];
        while(div.firstChild){div.removeChild(div.firstChild);}
        div.style["background-color"] = "red";
        div.style.padding = "100px";


        // Set the countdown time
        var countdowntime = 3;
        var countdown = Date.now() + countdowntime * 1000;

        function done(){
            /* Callback function that reloads */
            console.log("Reloading");
            location.reload();
        }

        // Add a countdown headline to div and fetch bach out the element that
        // contains the time
        var headline = document.createElement("H1");
        headline.innerHTML = "Lost connection to proxy server<br>Reload in " +
            "<span id=\"countdownelement\">" + countdowntime + "</span> seconds";
        div.appendChild(headline);
        var countdownelement = document.getElementById("countdownelement");

        // Add a reload button to div
        var btn = document.createElement("BUTTON");
        btn.onclick = function(){done();};
        btn.innerHTML = "Reload now";
        div.appendChild(btn);

        function update(){
            /* Update the countdown and call done if countdown is done */
            var now = Date.now();
            if (now > countdown){
                countdownelement.innerHTML = '0';
                window.setTimeout(done, 0);
            } else {
                countdownelement.innerHTML = ((countdown - now) / 1000).toFixed(0);
                window.setTimeout(update, 100);
            }
        }

        window.setTimeout(update, 0);
    };

    webSocket.onmessage = function (e) {
        /* ws onmessage: parse the message from JSON and act on it */
        var data = JSON.parse(e.data);
        handle_batch_data(data);
    };

    webSocket.onerror = function (e) {
        /* on ws error log to console */
        console.log("WebSocket error: %o", e);
    };
};
