// Method to update selection boxes dynamically when choosing measurement
function showData(str, type){
    // Get the select measurement form
    var formLength = document.forms[0].elements.length-1;
    // Get the list of options from that
    var options = document.forms[0].elements[formLength].options;
    var i;
    // Some sort of a hack, that means that the type defaults to mass time
    var newstr = typeof type !== 'undefined' ? type : "masstime";

    for (i=0; i<options.length; i++){
        if (document.forms[0].elements[formLength].options[i].selected){
            newstr = newstr +  "&chosen_group[]=" + document.forms[0].elements[formLength].options[i].value;
        }
    }
    // No idea why this is, maybe something historically, look into how this
    // is called different places
    if (str==""){
        // No output if str is empty
        document.getElementById("list_components").innerHTML=""; 
        return;
    }

    // Make a new http request (what ever that means)
    if (window.XMLHttpRequest){
        xmlhttp = new XMLHttpRequest();
    }

    // Attach a call back that updates the lists, when the new list of
    // measurements are ready
    xmlhttp.onreadystatechange = function(){
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
            // readyState ok
            document.getElementById("list_components").innerHTML = xmlhttp.responseText;
        }
    }

    xmlhttp.open("GET", "get_components.php?type=" + newstr, true); //retrieve data from MySQL using PHP
    xmlhttp.send();
}
