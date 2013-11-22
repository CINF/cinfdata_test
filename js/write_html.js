// Method to write HTML between <div> tags
	function replace() {
		var txt=document.getElementById("selection_boxes")
		txt.innerHTML="Some Content";
	}


	  <tr>
	    <td><!--LEFT Y-->
	      <b>Log-scale</b><input type="checkbox" name="left_logscale" value="checked" <?php echo($left_logscale);?>><br>
	      <b>Y-Min:</b><input name="left_ymin" type="text" size="7" value="<?php echo($left_ymin);?>"><br>
	      <b>Y-Max:</b><input name="left_ymax" type="text" size="7" value="<?php echo($left_ymax);?>"><br>
              <div id="measurements_left" class="component_select">
	      <b>Select component (left y-axis):</b><br>
	        <select multiple size="8" name="left_plotlist[]">
                  <?php
                     // Creation of plotlist for left axis
		     for($i=0;$i<count($individ_idlist);$i++){
                        $selected = (in_array($individ_idlist[$i],$left_plotlist)) ? "selected" : "";
                        echo("<option value=\"" . $individ_idlist[$i] . "\" " . $selected . ">" . $individ_datelist[$i] . ": " . $individ_labellist[$i] . "</option>\n");
                     }
		  ?>
	        </select>
              </div>
	    </td>
	    <!--
	       <td align="center">
		 center column - currently empty
	       </td>
	    -->
	    <td align="right"><!--RIGHT Y-->
	      <b>Log-scale</b><input type="checkbox" name="right_logscale" value="checked" <?php echo($right_logscale);?>><br>
	      <b>Y-Min:</b><input name="right_ymin" type="text" size="7" value="<?php echo($right_ymin);?>"><br>
	      <b>Y-Max:</b><input name="right_ymax" type="text" size="7" value="<?php echo($right_ymax);?>"><br>
              <div id="measurements_right" class="component_select">
	      <b>Select component (right y-axis):</b><br>
	        <select multiple size="8" name="right_plotlist[]" id="right_plotlist">
		  <!--<option value="0">None <?php echo($selected); ?></option>-->
                  <?php
                     // Creation of plotlist for right axis
		     for($i=0;$i<count($individ_idlist);$i++){
                        $selected = (in_array($individ_idlist[$i],$right_plotlist)) ? "selected" : "";
                        echo("<option value=\"" . $individ_idlist[$i] . "\" " . $selected . ">" . $individ_datelist[$i] . ": " . $individ_labellist[$i] . "</option>\n");
                     }
		  ?>
	        </select>
              </div>
	    </td>
	  </tr>
