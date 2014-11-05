/* This file is the java script part of the live page on CINFDATA */

function log_input() {
    /* Log the script input: socket_defs, measurement_ids, fig_data_subs and
       firgure_defs
       NOTE. This data is inserted into the javascript environment in a
       separate script part in live.php
    */
    "use strict";

    // Define variables
    var key, i, j;

    console.log("### DATA FROM PHP START");
    console.log("socket_defs:", socket_defs);

    // measurement_ids
    for (key in measurement_ids) {
        console.log('measurement_ids', key, ':', measurement_ids[key]);
    }

    // fig_data_subs
    for (i = 0; i < fig_data_subs.length; i++) {
        for (key in fig_data_subs[i]) {
            for (j = 0; j < fig_data_subs[i][key].length; j++) {
                console.log("Figure data subscription at socket:", i, "id:",
                            key, "sub:",
                            JSON.stringify(fig_data_subs[i][key][j]));
            }
        }
    }

    // figure_defs
    for (key in figure_defs) {
        console.log("Figure definition for:", key, ":",
                    JSON.stringify(figure_defs[key]));
    }
    console.log("### DATA FROM PHP END\n\n");
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
    }
}

function format_data(value, format) {
    /* Format a float into a string using format strings like .2f and .2e */
    "use strict";

    /* Since we don't support numbers before the decimal point, the number of
       decimals is located at index [1: -1] */
    var decimals = parseInt(format.substring(1, format.length - 1));

    // The format type is just the last character
    switch (format.substring(format.length - 1)) {
    case "f":
	return value.toFixed(decimals) ;
    case "e":
	return value.toExponential(decimals) ;
    }
}

/* ### Figure (pseudo) CLASS definition */
function MyFigure(name, definition) {
    /* Define a MyFigure object */
    "use strict";

    // Define variables
    var key, settings, plot_number, labels = ["Date"], colors = [];

    console.log("### INSTANTIATE FIGURE START:", name);
    console.log("Figure definition from graphsetting:",
                JSON.stringify(definition));

    // Setup generel stuff for the figure
    this.name = name;
    this.first_call = true;  // Used to replace the default point on first call
    this.definition = definition;  // Save the definition
    this.last_update = new Date();
    // x-"window" start end end positions
    this.x_start = new Date();
    this.x_end = new Date(this.x_start.getTime() +
                          definition.x_window * 1000);
    // Define how much the figure jumps ahead when it reaches the end
    this.jump_ahead = 0.2;
    if (definition.hasOwnProperty("jump_ahead")) {
        this.jump_ahead = definition.jump_ahead;
    }
    console.log("Initial window interval", this.x_start, this.x_end);

    /* Get labels and colors and form initial "default" point and point
       template */
    this.data = [[new Date(0)]];
    this.data_template = [null];
    for (key in definition.figure) {
        if (key.indexOf("plot") === 0) {
            plot_number = parseInt(key.replace("plot", ""), 10);
            // Labels start with the x-axis label, hence the +1
            labels[plot_number + 1] = definition.figure[key].label;
            colors[plot_number] = definition.figure[key].color;
            // Initial "default" point
            this.data[0][plot_number + 1] = 1;
            this.data_template[plot_number + 1] = null;
        }
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
        // Optional
        drawPoints: true,
        pointSize: 2,
        strokeWidth: 1.5,  // line width
        logscale: false
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

    console.log("Make figure with updated Dygraph settings:",
                JSON.stringify(settings));
    this.fig = new Dygraph(document.getElementById(name), this.data, settings);
    console.log("### INSTANTIATE FIGURE END:", name, "\n\n");
}

MyFigure.prototype.addPoint = function (plot_n, date, value) {
    /* Add point to figure. Function attached to MyFigure prototype, so
       something like a virtual method
    */
    "use strict";

    // Define variables
    var i, cut, cutpoint, new_point = this.data_template.slice(0);
    new_point[0] = date;
    new_point[plot_n + 1] = value;

    // If it is the first call to draw, replace the dummy point and redraw
    if (this.first_call) {
        this.first_call = false;
        this.data[0] = new_point;
        this.fig.updateOptions({ 'file':  this.data});
    } else {
        this.data.push(new_point);
    }

    // If the new points is outside the plot window
    if (date.getTime() > this.x_end) {
        cut = 0;

        /* Loop over the data to find the cutpoint. The cut point defines from
           where newer data should be saved, because it is still used after
           changing the plot window. The cut criteria is defined as:
               new_point_time - x_window * (1 - jump_ahead_fraction)
        */
        cutpoint = date.getTime() - this.definition.x_window * 1000 * (1 - this.jump_ahead);
        for (i = 0; i < this.data.length; i++) {
            if (this.data[i][0].getTime() > cutpoint) {
                cut = i;
                break;
            }
        }

        // Cut the data and update plot
        this.data = this.data.slice(cut);
        this.x_start = this.data[0][0];
        this.x_end = new Date(this.x_start.getTime() +
                              this.definition.x_window * 1000);
        // Always force an update if the window has changed
        this.fig.updateOptions({dateWindow: [this.x_start, this.x_end]});
        this.last_update = date;
    }

    // Determine if it is time to update
    if (date - this.last_update > this.definition.update_interval * 1000) {
        this.last_update = date;
        this.fig.updateOptions({'file':  this.data});
    }
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
        socket = data[0];

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
            value_elements[el].innerHTML = format_data(value, format) +
		"&thinsp;" + unit;
        }
        time_elements = document.getElementsByClassName(id + "_time");
        for (el = 0; el < time_elements.length; el++) {
            time_elements[el].innerHTML = time;
        }
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
        for (i = 0; i < socket_defs.length; i++) {
            msg = "subscribe#".concat(socket_defs[i], ";", measurement_ids[i].join(','));
            console.log("Subscribe on machine: ".concat(msg));
            webSocket.send(msg);
        }
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
        var split = string.split("#");

        console.log("## Parse subscription reply START:", string);
        // Schedule a ws send of the subscription number once every sane
        // interval
        console.log("Subscription id:", split[2], "Sane interval", split[3]);
        // Send the subscription number periodically every sane_interval
        window.setInterval(
            function () {webSocket.send(split[2]); },
            parseFloat(split[3]) * 1000
        );
        console.log("## Parse subscription reply END:");
    }

    webSocket.onmessage = function (e) {
        /* ws onmessage: parse the message from JSON and act on it */
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
