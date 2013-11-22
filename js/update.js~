// Method to update selection boxes dynamically when choosing measurement
     function showDataLeft(str){
      var formLength = document.forms[0].elements.length-1;
      var options = document.forms[0].elements[formLength].options;
      var i;
      var newstr = "";
       for (i=0;i<options.length;i++){
        if (document.forms[0].elements[formLength].options[i].selected){
            newstr = newstr +  "&chosen_group[]=" + document.forms[0].elements[formLength].options[i].value;
        }
      }
      if (str==""){
        // No output if str is empty
        document.getElementById("measurements_left").innerHTML=""; 
        return;
      }
 
      if (window.XMLHttpRequest){
        xmlhttp = new XMLHttpRequest();
      }

      xmlhttp.onreadystatechange = function(){
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
            // readyState ok
            document.getElementById("measurements_left").innerHTML = xmlhttp.responseText;
        }
      }
      xmlhttp.open("GET","get_components.php?side=left&type=masstime"+newstr,true); //retrieve data from MySQL using PHP
      xmlhttp.send();    
     }



     function showDataRight(str){
      var formLength = document.forms[0].elements.length-1;
      var options = document.forms[0].elements[formLength].options;
      var i;
      var newstr = "";
       for (i=0;i<options.length;i++){
        if (document.forms[0].elements[formLength].options[i].selected){
            newstr = newstr +  "&chosen_group[]=" + document.forms[0].elements[formLength].options[i].value;
        }
      }
      if (str==""){
        // No output if str is empty
        document.getElementById("measurements_right").innerHTML="";
        return;
      }
 
      if (window.XMLHttpRequest){
        xmlhttp = new XMLHttpRequest();
      }

      xmlhttp.onreadystatechange = function(){
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
            // readyState ok
            document.getElementById("measurements_right").innerHTML = xmlhttp.responseText;
        }
      }
      xmlhttp.open("GET","get_components.php?side=right&type=masstime"+newstr,true); //retrieve data from MySQL using PHP
      xmlhttp.send();    
     }
