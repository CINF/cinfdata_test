
  /* 
    Copyright (C) 2014 Robert Jensen, Thomas Andersen and Kenneth Nielsen
    
    The CINF Data Presentation Website is free software: you can
    redistribute it and/or modify it under the terms of the GNU
    General Public License as published by the Free Software
    Foundation, either version 3 of the License, or
    (at your option) any later version.
    
    The CINF Data Presentation Website is distributed in the hope
    that it will be useful, but WITHOUT ANY WARRANTY; without even
    the implied warranty of MERCHANTABILITY or FITNESS FOR A
    PARTICULAR PURPOSE.  See the GNU General Public License for more
    details.
    
    You should have received a copy of the GNU General Public License
    along with The CINF Data Presentation Website.  If not, see
    <http://www.gnu.org/licenses/>.
  */

function js_query(query){
    /* Make a generic MySQL query from java script */

    // Make a new http request
    if (window.XMLHttpRequest){
        xmlhttp = new XMLHttpRequest();
    }

    //retrieve data from MySQL using PHP
    xmlhttp.open("GET", "js_query.php?query=" + encodeURIComponent(query), false);
    xmlhttp.send();
    return xmlhttp.responseText; 
}
