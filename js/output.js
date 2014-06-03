// Method to update selection boxes dynamically when choosing measurement
function fetchOutput(id){
    //document.getElementById("Factor_output").innerHTML = "Man";
    
    console.log('NOW!');
    if (window.XMLHttpRequest){
        xmlhttp = new XMLHttpRequest();
    }

    xmlhttp.onreadystatechange = function(){
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
            // readyState ok
	    var output = xmlhttp.responseText;
	    if (output != 'none'){
		var output_array = JSON.parse(output);
		for(var index in output_array) {
		    if (output_array[index] != ''){
			document.getElementById(index.concat("_output")).innerHTML = output_array[index];
		    } else {
			document.getElementById(index.concat("_output")).innerHTML = 'No output';
		    }
		}
	    }
        }
    }
    xmlhttp.open("GET", "get_output.php?output_id=".concat(id), true); //retrieve data from MySQL using PHP
    xmlhttp.send();
    
}