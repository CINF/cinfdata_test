// ### Set window callbacks
window.onload = function () {
    /* Start everything up on load */
    "use strict";

    // Define variables, wsuri is websocket uri
    var i, key, msg, webSocket,
        wsuri = "wss://kenni.fysik.dtu.dk:9002";


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
        console.log("... WebSocket Connected!");
	webSocket.send(JSON.stringify({'action': 'subscribe', 'subscriptions': ['rasppi71:sine1', 'rasppi71:sine1', 'rasppi71:status', 'rasppi71:cosine1', 'rasppi71:cosine1']}));
    };

    webSocket.onclose = function (e) {
        /* on ws close show information on the console */
        console.log("ws: Closed (wasClean = " + e.wasClean + ", code = " +
                    e.code + ", reason = '" + e.reason + "')");
    };


    webSocket.onmessage = function (e) {
	/* console.log(e.data); */
	var data = JSON.parse(e.data);
	if (data.data.status === undefined){
	    if (data.data["sine1"] === undefined){
		document.getElementById('value1').innerHTML = e.data;    
	    } else {
		document.getElementById('value2').innerHTML = e.data;    
	    }
	} else {
	    document.getElementById('value3').innerHTML = e.data;    
	}

    };

    webSocket.onerror = function (e) {
        /* on ws error log to console */
        console.log("Websocket error:", e);
    };
};
