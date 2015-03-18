<?php
  /*
    Copyright (C) 2012 Robert Jensen, Thomas Andersen and Kenneth Nielsen
    
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

include("common_functions_v2.php");
echo(html_header($root="", $title="Data logged at CINF"));
?>
        <table class="frontpage">
          <tr>
           <td>      
	    <a href="javascript:toggle('mwg')"><h2>Palle</h2></a></td><td>
            <a href="javascript:toggle('stm312')"><h2>STM312</h2></a></td><td>
	    <a href="javascript:toggle('volvo')"><h2>Volvo</h2></a></td></tr><tr><td>
            <ul id="mwg" style="display:none">
              <li><a href="mgw/dateplot.php?type=multidateplot&left_plotlist[]=1&right_plotlist[]=2">Pressure</a></li>
              <li><a href="mgw/dateplot.php?type=multidateplot&left_plotlist[]=4&left_plotlist[]=6&left_plotlist[]=7&left_plotlist[]=8&left_plotlist[]=9&left_plotlist[]=10&left_plotlist[]=11&left_plotlist[]=12">Temperatures</a></li>
	    </ul>
           </td>
           <td>
            <ul id="stm312" style="display:none">
	     <li><a href="stm312/dateplot.php?type=multidateplot&left_logscale=checked&left_ymin=0&left_ymax=0&left_plotlist[]=1&left_plotlist[]=2&right_ymin=0&right_ymax=0">Main Chamber</a></li>
	     <li><a href="stm312/dateplot.php?type=multidateplot_tt&left_ymax=0&left_plotlist[]=1&left_plotlist[]=2&left_plotlist[]=3&right_ymin=0&right_ymax=0">Turbo Status</a></li>
             <li><a href="stm312/dateplot.php?type=multidateplot_hp&left_plotlist[]=1">HP Cell Pressure</a></li>
             <hr>
             <li><a href="stm312/stm_overview.php">STM Images</a></li>
	     <li><a href="stm312/xyplot.php?type=massspectra&left_logscale=checked">Mass spectra</a></li>
             <li><a href="stm312/xyplot.php?type=masstime">Mass-time</a></li>
	     <li><a href="stm312/xyplot.php?type=xps&flip_x=checked&matplotlib=checked">XPS-data</a></li>
	     <li><a href="stm312/chiller.php">Chiller control</a></li>
             <hr>
	     <li><a href="stm312/bakeout.php">Bakeout</a></li>
             <li><a href="stm312/modify_comment.php">Modify comments</a></li>

	   </ul>
          </td>
          <td>
           <ul id="volvo" style="display:none">
	     <li><a href="volvo/dateplot.php?type=multidateplot&left_logscale=checked&left_ymin=0&left_ymax=0&left_plotlist[]=1&right_plotlist[]=2&right_ymin=0&right_ymax=0">Pressure and temperature</a></li>
	    <li><a href="volvo/xyplot.php?type=it">IT-curves</a></li>
	    <li><a href="volvo/xyplot.php?type=iv">IV-curves</a></li>
	    <li><a href="volvo/xyplot.php?type=xps">XPS-data</a></li>
	    <li><a href="volvo/xyplot.php?type=iss">ISS-data</a></li>
	    <li><a href="volvo/xyplot.php?type=massspectrum">Mass spectrums</a></li>
	    <li><a href="volvo/xyplot_group.php?type=masstime">Mass-time</a></li>
	    <li><a href="volvo/x.php?type=morning_pressure&from=2009-01-01&to=2012-01-01&manualscale=checked&ymax=1E-5&ymin=1E-11&xsize=1000&ysize=750">Morning pressure - Beta!</a></li
	    <li><a href="volvo/live.php?type=live_values">Live Values</a></li>
	   </ul>
          </td>
         </tr>
         <tr>
          <td>      
           <a href="javascript:toggle('microreactor')"><h2>&micro;-reactor</h2></a></td><td>
           <a href="javascript:toggle('microreactorNG')"><h2>&micro;-reactor NG</h2></a></td><td>
	   <a href="javascript:toggle('omicron')"><h2>Omicron</h2></a>
          </td>
         </tr>
         <tr>
          <td>
           <ul id="microreactor" style="display:none">
	    <li><a href="microreactor/dateplot.php?type=multidateplot&left_ymin=0&left_ymax=0&left_plotlist[]=1&right_ymin=0&right_ymax=0">Chamber pressure</a></li>
	    <li><a href="microreactor/dateplot.php?type=multidateplot&left_ymin=0&left_ymax=0&left_plotlist[]=2&right_ymin=0&right_ymax=0">Pirani buffer volume</a></li>
	    <li><a href="microreactor/dateplot.php?type=multidateplot&left_ymin=0&left_ymax=0&left_plotlist[]=3&right_ymin=0&right_ymax=0">Reactor pressure</a></li>
   <li><a href="microreactor/dateplot.php?type=multidateplot&left_ymin=0&left_ymax=0&left_plotlist[]=4&left_plotlist[]=5&right_ymin=0&right_ymax=0">Backing pressure, turbos</a></li>
	    <li><a href="microreactor/dateplot.php?type=multidateplot&left_ymin=0&left_ymax=0&left_plotlist[]=6&right_ymin=0&right_ymax=0">Sample temperature</a></li>
	    <li><a href="microreactor/dateplot.php?type=multidateplot_turbos&left_ymin=0&left_ymax=0&left_plotlist[]=1&left_plotlist[]=2&right_ymin=0&right_ymax=0">Turbo temperatures</a></li>
	    <li><a href="microreactor/xyplot.php?type=masstime&matplotlib=checked">Mass-time</a></li>
	    <li><a href="microreactor/xyplot.php?type=massspectrum">Massspectra</a></li>
            <li><a href="microreactor/modify_comment.php">Modify comments</a></li>
	    <!--<li><a href="microreactor/manage_measurements.php">Manage measurements</a></li>-->
	    <li><a href="microreactor/live.php?type=live_values">Live Values</a></li>
	   </ul>
          </td>
          <td>
           <ul id="microreactorNG" style="display:none">
	    <li><a href="microreactorNG/dateplot.php?type=multidateplot&left_ymin=0&left_ymax=0&left_plotlist[]=1&right_ymin=0&right_ymax=0">Chamber pressure</a></li>
	    <li><a href="microreactorNG/dateplot.php?type=multidateplot&left_ymin=0&left_ymax=0&left_plotlist[]=2&right_ymin=0&right_ymax=0&right_plotlist[]=3">Buffer and containment volume</a></li>
	    <li><a href="microreactorNG/dateplot.php?type=multidateplot&left_ymin=0&left_ymax=0&left_plotlist[]=5&right_ymin=0&right_ymax=0">Reactor pressure</a></li> 
	    <li><a href="microreactorNG/dateplot.php?type=multidateplot&left_ymin=0&left_ymax=0&left_plotlist[]=8&right_ymin=0&right_ymax=0">Sample temperature</a></li>
	    <li><a href="microreactorNG/dateplot.php?type=multidateplot&left_ymin=0&left_ymax=0&left_plotlist[]=6&right_ymin=0&right_ymax=0">Backing pressure, buffer turbo</a></li>
	    <li><a href="microreactorNG/dateplot.php?type=multidateplot&left_ymin=0&left_ymax=0&left_plotlist[]=7&right_ymin=0&right_ymax=0">Backing pressure, chamber turbo</a></li>
	    <li><a href="microreactorNG/dateplot.php?type=multidateplot&left_ymin=0&left_ymax=0&left_plotlist[]=9&right_ymin=0&right_ymax=0">Turbo temperatures</a></li>
	    <li><a href="microreactorNG/xyplot.php?type=massspectrum">Mass spectra</a></li>
	    <li><a href="microreactorNG/xyplot.php?type=masstime&matplotlib=checked">Mass-time</a></li>
	    <!--<li><a href="microreactorNG/plot.php?type=morning_pirani_bufferturbo&from=2009-01-01&to=2012-01-01&manualscale=checked&ymax=1E-1&ymin=1E-5&xsize=1000&ysize=750">Morning pressure</a></li>-->
           <li><a href="microreactorNG/modify_comment.php">Modify comments</a></li
	    <li><a href="microreactorNG/dateplot.php?type=multidateplot_test&left_ymin=0&left_ymax=0&left_plotlist[]=1&right_ymin=0&right_ymax=0">Test</a></li>
	    <li><a href="microreactorNG/live.php?type=live_values">Live Values</a></li>
	   </ul>
          </td>
          <td>      
           <ul id="omicron" style="display:none"> 
	    <li><a href="omicron/read_dateplot.php?type=temperature">Temperature</a></li>
            <li><a href="omicron/read_dateplot.php?type=temperature_aggregation">Temperature, aggregation</a></li>
	    <li><a href="omicron/read_dateplot.php?type=pressure_ana">Pressure, analytical chamber</a></li>
	    <li><a href="omicron/read_dateplot.php?type=heating_power">Heating Power</a></li>
	    <li><a href="omicron/read_dateplot.php?type=pressure_prep">Pressure, prep chamber</a></li>
	    <li><a href="omicron/read_dateplot.php?type=pressure_nanobeam">Pressure, nanobeam</a></li>
	    <li><a href="omicron/plot.php?type=morning_pressure_ana&from=2009-01-01&to=2012-01-01&manualscale=checked&ymax=1E-8&ymin=1E-12&xsize=1000&ysize=750">Morning pressure, analytical chamber</a></li>
	    <li><a href="omicron/plot.php?type=morning_pressure_prep&from=2009-01-01&to=2012-01-01&manualscale=checked&ymax=1E-8&ymin=1E-12&xsize=1000&ysize=750">Morning pressure, prep chamber</a></li>
	    <li><a href="omicron/read_plot_group.php?type=deposition">Deposition</a></li>
	    <li><a href="omicron/read_plot_group.php?type=masstime">Mass-time</a></li>
	    <li><a href="omicron/read_plot.php?type=massspectrum">Mass spectrums</a></li>
	    <li><a href="omicron/read_plot_group.php?type=cluster_deposition">Cluster deposition</a></li><!-- Should be read_plot_group.php when bug is resolved -->
        <li><a href="omicron/modify_comment.php">Modify comments</a></li>
	   </ul>
          </td>
         </tr>
         <tr>
          <td>      
	   <a href="javascript:toggle('xrd')"><h2>XRD</h2></a></td><td>
           <a href="javascript:toggle('hall')"><h2>Hall</h2></a></td><td>
	   <a href="javascript:toggle('booking')"><h2>Booking</h2></a>
          </td>
         </tr>
         <tr>
          <td>
           <ul id="xrd" style="display:none"> 
	    <li><a href="xrd/status.php">Pressure status</a></li>
	    <li><a href="xrd/read_dateplot.php?type=pressure_asg">Pressure asg</a></li>
	    <li><a href="xrd/read_dateplot.php?type=pressure_wrg">Pressure wrg</a></li>
	    <li><a href="xrd/read_dateplot.php?type=pressure_wrgms">Pressure wrgms</a></li>
	   </ul>
          </td>
          <td>   
           <ul id="hall" style="display:none"> 
	    <li><a href="hall/dateplot.php?type=temperature&matplotlib=checked&left_plotlist%5B%5D=1">Temperature</a></li>
	    <li><a href="hall/live.php?type=live_values">Live Values</a></li>
	   </ul>
          </td>

          <td>
           <ul id="booking" style="display:none"> 
              <li><a href="booking/b307.php">Building 307</a></li>
	   </ul>
          </td>
         </tr>
         <tr>
          <td>
	   <a href="javascript:toggle('TOF')"><h2>TOF</h2></a></td><td>
           <a href="javascript:toggle('tower')"><h2>The Tower</h2></a></td><td>
	   <a href="javascript:toggle('ps')"><h2>Parallel Screening</h2></a>
          </td>
         </tr>
         <tr>
          <td>
           <ul id="TOF" style="display:none"> 
	    <li><a href="tof/dateplot.php?type=multidateplot&matplotlib=checked&left_plotlist%5B%5D=2">Dateplots</a></li>
	    <li><a href="tof/xyplot.php?type=tofspectrum_small&matplotlib=checked">TOF spectra, small</a></li>
	    <li><a href="tof/xyplot.php?type=tofspectrum_wide&matplotlib=checked">TOF spectra, wide</a></li>
	    <li><a href="tof/xyplot.php?type=massspectrum">Mass spectra</a></li>
	    <li><a href="tof/xyplot.php?type=masstime">Mass-time</a></li>
	    <li><a href="http://robertj">Flight Time Analysis</a></li>
	    <li><a href="tof/mass_calc.html">Calc. mass deficiency</a></li>
	    <li><a href="tof/live.php?type=live_values">Live Values</a></li>
	   </ul>
          </td>
          <td>
           <ul id="tower" style="display:none">
	    <li><a href="tower/dateplot.php?type=multidateplot&left_plotlist%5B%5D=1&right_plotlist%5B%5D=2&left_logscale=checked">Pressure and temperature</a></li>
	    <li><a href="tower/xyplot.php?type=deposition">Deposition</a></li>
	   </ul>
          </td>
          <td>
           <ul id="ps" style="display:none"> 
	    <li><a href="ps/dateplot.php?type=multidateplot_chamber&left_plotlist[]=1&left_plotlist[]=2&right_plotlist[]=3">Chamber status</a></li>
            <li><a href="ps/dateplot.php?type=multidateplot_turbo&left_plotlist[]=1&right_plotlist[]=2">Turbo status</a></li>
	    <li><a href="ps/xyplot.php?type=deposition">Deposition</a></li>
            <li><a href="ps/xyplot.php?type=massspectra&left_logscale=checked&plugin_settings[MassSpectraOffset][activate]=checked">Mass spectra</a></li>
	    <li><a href="ps/xyplot.php?type=masstime">Mass-time</a></li>
	    <li><a href="ps/xyplot.php?type=xps&flip_x=checked&matplotlib=checked">XPS</a></li>
	    <li><a href="ps/xyplot.php?type=iss">ISS</a></li>
            <li><a href="ps/modify_comment.php">Modify comments</a></li>

	   </ul>
          </td>
         </tr>
         <tr>
          <td>
           <a href="javascript:toggle('shortlinks')"><h2>Shortlinks</h2></a></td><td>
	   <a href="javascript:toggle('dummy')"><h2>Dummy</h2></a></td><td>
	   <a href="javascript:toggle('oldclustersource')"><h2>Old cluster source</h2></a>
          </td>
         <tr>
          <td>
            <ul id="shortlinks" style="display:none"> 
	     <li><a href="links/show_links.php">Show previous shortlinks</a></li>
	    </ul>
          </td>
          <td>
            <ul id="dummy" style="display:none"> 
  	     <li><a href="dummy/xyplot.php?type=masstime&matplotlib=checked">Mass-time</a></li>
	     <!-- <li><a href="dummy/read_plot_group.php?type=masstime">Mass-time</a></li> -->
	     <li><a href="dummy/xyplot.php?type=massspectrum">Mass spectrums</a></li>
	     <li><a href="dummy/xyplot.php?type=xps">XPS</a></li>
	     <li><a href="dummy/xyplot.php?type=it&matplotlib=checked">Current/Temperature</a></li>
	    </ul>
          </td>
          <td>
            <ul id="oldclustersource" style="display:none"> 
             <li><a href="oldclustersource/read_dateplot.php?type=pressure">Pressure</a></li>
	     <li><a href="oldclustersource/read_plot_group.php?type=clusterdeposition">Cluster deposition</a></li>
             <li><a href="oldclustersource/read_dateplot.php?type=temperatures">Temperatures</a></li>
             <li><a href="oldclustersource/read_dateplot.php?type=backingpressure">Backing pressures</a></li>
	    </ul>
          </td>
         </tr>
         <tr>
          <td>
           <a href="javascript:toggle('photomicroreactor')"><h2>Photo-microreactor</h2></a></td><td>
           <a href="javascript:toggle('ups')"><h2>UPS</h2></a></td><td>
           <a href="javascript:toggle('other')"><h2>Other</h2></a>
          </td>
         </tr>
         <tr>
          <td>
           <ul id="photomicroreactor" style="display:none"> 
	     <li><a href="photo_microreactor/read_plot_group.php?type=masstime">Mass-time</a></li>
             <li><a href="photo_microreactor/read_plot.php?type=massspectrum">Mass spectra</a></li>
	   </ul>
          </td>
          <td>
           <ul id="ups" style="display:none"> 
	     <li><a href="ups/read_dateplot.php?type=kVA">VA</a></li>
	     <li><a href="ups/read_dateplot.php?type=power">Power</a></li>
	     <li><a href="ups/read_dateplot.php?type=current">Current</a></li>
	     <li><a href="ups/read_dateplot.php?type=voltage_output">Voltage output</a></li>
	     <li><a href="ups/read_dateplot.php?type=voltage_input">Voltage input</a></li>
	     <li><a href="ups/read_dateplot.php?type=frequency">Input frequency</a></li>
	     <li><a href="ups/read_dateplot.php?type=batt_temperature">Battery temperature</a></li>
	   </ul>
          </td>
          <td>
           <ul id="other" style="display:none"> 
	     <li><a href="todo.php">Floor Managers To Do list</a></li>
	     <li><a href="https://cinfdata.fysik.dtu.dk/cinf_common/host_checker.php">Host checker</a></li>
	     <li><a href="other/alarms.php">Email alarms</a></li>
	     <li><a href="code-documentation/test_config_all.php">Test my configuration file</a></li>
	     <li><a href="code-documentation/statistics.php">Statistics</a></li>
	     <li><a href="code-documentation/git_status.php">Git status</a></li>
	     <li><a href="other/dateplot.php?type=pylint&matplotlib=checked&left_plotlist%5B%5D=1&right_plotlist%5B%5D=2">PyExpLabSys Stats Graphs</a></li>
	     <li><a href="other/pels_pylint.php">PyExpLabSys Stats Tables</a></li>
	     <li><a href="sym-files2/dateplot.php?type=multidateplot">Dateplot demo</a></li>
	     <li><a href="sym-files2/xyplot.php?type=masstime">XY plot demo</a></li>
	     <li><a href="other/live.php?type=live_values_wss">Web-Socket Status</a></li>
	     <li><a href="other/dateplot.php?type=fridays&left_plotlist[]=1&left_plotlist[]=2&right_plotlist[]=3&right_plotlist[]=4&matplotlib=checked">Fridays Amounts</a></li>
	     <li><a href="other/dateplot.php?type=fridays_items&left_plotlist[]=1&right_plotlist[]=2&matplotlib=checked">Fridays Items</a></li>
	     <li><a href="other/fridays_stats.php">Fridays Pies</a></li>
	   </ul>
          </td>
         </tr>
         <tr>
          <td>
           <a href="javascript:toggle('NH3Synth')"><h2>NH<sub>3</sub> Synthesis</h2></a></td><td>
           <a href="javascript:toggle('sputterchamber')"><h2>Sputter chamber</h2></a></td><td>
           <a href="javascript:toggle('furnaceroom')"><h2>Furnace room</h2></a></td><td>
          </td>
         </tr>
         <tr>
          <td>
           <ul id="NH3Synth" style="display:none"> 
              <li><a href="NH3Synth/dateplot.php?type=multidateplot&matplotlib=checked&left_ymin=0&left_ymax=0&left_plotlist[]=3&right_ymin=0&right_ymax=0">Pressure, iongauge</a></li>
              <li><a href="NH3Synth/dateplot.php?type=multidateplot&matplotlib=checked&left_ymin=0&left_ymax=0&left_plotlist[]=1&right_ymin=0&right_ymax=0">Ammonia Concentration (IR)</a></li>
              <li><a href="NH3Synth/dateplot.php?type=multidateplot&matplotlib=checked&left_ymin=0&left_ymax=0&left_plotlist[]=4&left_plotlist[]=5&left_plotlist[]=6&left_plotlist[]=7&left_plotlist[]=8&right_ymin=0&right_ymax=0">Temperatures</a></li>
              <li><a href="NH3Synth/xyplot.php?type=massspectrum&matplotlib=checked">Mass spectra</a></li>
 	      <li><a href="NH3Synth/xyplot.php?type=masstime&matplotlib=checked">Mass-time</a></li>
	   </ul>
          </td>
          <td>
           <ul id="sputterchamber" style="display:none"> 
 	     <li><a href="sputterchamber/dateplot.php?type=multidateplot&left_ymin=0&left_ymax=0&left_plotlist[]=1&right_ymin=0&right_ymax=0">Pressure</a></li>
 	     <li><a href="sputterchamber/dateplot.php?type=multidateplot&left_ymin=0&left_ymax=0&left_plotlist[]=2&right_ymin=0&right_ymax=0">QCM Frequency</a></li>
 	     <li><a href="sputterchamber/dateplot.php?type=multidateplot&left_ymin=0&left_ymax=0&left_plotlist[]=3&right_ymin=0&right_ymax=0">QCM Thickness</a></li>
 	     <li><a href="sputterchamber/dateplot.php?type=multidateplot&left_ymin=0&left_ymax=0&left_plotlist[]=4&right_ymin=0&right_ymax=0">QCM Life Time</a></li>
 	     <li><a href="sputterchamber/dateplot.php?type=multidateplot&left_ymin=0&left_ymax=0&left_plotlist[]=5&right_ymin=0&right_ymax=0">Chiller Flow</a></li>
	   </ul>
          </td>
          <td>
           <ul id="furnaceroom" style="display:none"> 
               <li><a href="furnaceroom/dateplot.php?type=multidateplot&left_plotlist[]=1">Temperature, furnace 1</a></li>
               <li><a href="furnaceroom/dateplot.php?type=multidateplot&left_plotlist[]=2">Temperature, furnace 2</a></li>
	   </ul>
          </td>
         </tr>
         <tr>
          <td>
           <a href="javascript:toggle('gasmonitor')"><h2>Gasmonitor</h2></a></td><td>
           <a href="javascript:toggle('chillers')"><h2>Chillers</h2></a></td><td>
           <a href="javascript:toggle('thetaprobe')"><h2>Theta probe</h2></a></td><td>
          </td>
         </tr>
         <tr>
          <td>
           <ul id="gasmonitor" style="display:none"> 
              <li><a href="gasmonitor/dateplot.php?type=multidateplot_ch4&matplotlib=checked&left_ymin=0&left_ymax=0&left_plotlist[]=1&left_plotlist[]=2&left_plotlist[]=3&left_plotlist[]=4&left_plotlist[]=5&left_plotlist[]=6&left_plotlist[]=7&right_ymin=0&right_ymax=0">Gas levels - CH<sub>4</sub></a></li>
              <li><a href="gasmonitor/dateplot.php?type=multidateplot_co&matplotlib=checked&left_ymin=0&left_ymax=0&left_plotlist[]=1&left_plotlist[]=2&left_plotlist[]=3&left_plotlist[]=4&left_plotlist[]=5&left_plotlist[]=6&right_ymin=0&right_ymax=0">Gas levels - CO</sub></a></li>
              <li><a href="https://cinfdata.fysik.dtu.dk/gasmonitor/dateplot.php?type=multidateplot&matplotlib=checked&from=2013-09-10+15%3A52&to=2013-09-11+15%3A53&left_ymin=0&left_ymax=0&left_plotlist[]=1&left_plotlist[]=2&left_plotlist[]=3&left_plotlist[]=4&left_plotlist[]=5&left_plotlist[]=6&left_plotlist[]=7&left_plotlist[]=8&left_plotlist[]=9&left_plotlist[]=10&left_plotlist[]=11&left_plotlist[]=12&left_plotlist[]=13&right_ymin=0&right_ymax=0">Gas levels - All</sub></a></li>
	   </ul>
          </td>
          <td>
           <ul id="chillers" style="display:none"> 
              <li><a href="chillers/dateplot.php?type=multidateplot_stm312&left_ymin=0&left_ymax=0&left_plotlist[]=1&left_plotlist[]=2&left_plotlist[]=3&right_plotlist[]=4&right_plotlist[]=5&right_ymin=0&right_ymax=0">Chiller STM312</a></li>
              <li><a href="chillers/dateplot.php?type=multidateplot_sputterchamber&left_ymin=0&left_ymax=0&left_plotlist[]=1&left_plotlist[]=2&left_plotlist[]=3&right_plotlist[]=4&right_plotlist[]=5&right_ymin=0&right_ymax=0">Chiller Sputterchamber</a></li>
              <li><a href="chillers/dateplot.php?type=multidateplot_thetaprobe&left_ymin=0&left_ymax=0&left_plotlist[]=1&left_plotlist[]=2&left_plotlist[]=3&right_plotlist[]=4&right_plotlist[]=5&right_ymin=0&right_ymax=0">Chiller Thetaprobe</a></li>
          </td>
          </td>
          <td>
           <ul id="thetaprobe" style="display:none"> 
              <li><a href="thetaprobe/dateplot.php?type=multidateplot_pressures&matplotlib=checked&left_logscale=checked&right_logscale=checked&left_plotlist%5B%5D=1&right_plotlist%5B%5D=2">Pressure</a></li>
              <li><a href="https://cinfdata.fysik.dtu.dk/thetaprobe/live.php?type=live_pressures">Pressure Live</a></li>
          </td>
         </tr>

         <tr>
          <td>
           <a href="javascript:toggle('vhp_setup')"><h2>VHP Setup</h2></a></td><td>
           <a href="javascript:toggle('chemlab307')"><h2>Chemlab, 307</h2></a></td><td>
           <a href="javascript:toggle('gasalarm307')"><h2>Gas alarm, 307</h2></a></td><td>
          </td>
         </tr>
         <tr>
          <td>
           <ul id="vhp_setup" style="display:none"> 
	     <li><a href="vhp_setup/dateplot.php?type=multidateplot&left_plotlist[]=1">Chamber Pressure</a></li>
	     <li><a href="vhp_setup/xyplot.php?type=massspectra">Mass spectrums</a></li>
	     <li><a href="vhp_setup/xyplot.php?type=masstime">Mass-time</a></li>
	   </ul>
          </td>
          <td>
           <ul id="chemlab307" style="display:none"> 
             <li><a href="chemlab307/dateplot.php?type=multidateplot&left_plotlist[]=1">Temperature, Muffle furnace</a></li>
	   </ul>
          </td>
          <td>
           <ul id="gasalarm307" style="display:none">
             <li><a href="https://cinfdata.fysik.dtu.dk/gasmonitor307/system_status.php">System Status</a></li>
             <li><a href="https://cinfdata.fysik.dtu.dk/gasmonitor307/websockets_simple.php?type=websocket_simple1">Live Values</a></li>
             <li><a href="https://cinfdata.fysik.dtu.dk/gasmonitor307/live.php?type=live_values">Live Plots</a></li>
             <li><a href="https://cinfdata.fysik.dtu.dk/gasmonitor307/dateplot.php?type=multidateplot&matplotlib=checked&left_ymin=0&left_ymax=0&left_plotlist[]=1&left_plotlist[]=3&left_plotlist[]=5&left_plotlist[]=7&left_plotlist[]=9&left_plotlist[]=11&right_ymin=0&right_ymax=0&right_plotlist[]=2&right_plotlist[]=4&right_plotlist[]=6&right_plotlist[]=8&right_plotlist[]=10&right_plotlist[]=12">All detectors</a></li>
             <li><a href="https://cinfdata.fysik.dtu.dk/gasmonitor307/dateplot.php?type=multidateplot_co&matplotlib=checked&left_ymin=0&left_ymax=0&left_plotlist[]=1&left_plotlist[]=2&left_plotlist[]=3&left_plotlist[]=4&left_plotlist[]=5&left_plotlist[]=6&right_ymin=0&right_ymax=0">All CO detectors</a></li>
             <li><a href="https://cinfdata.fysik.dtu.dk/gasmonitor307/dateplot.php?type=multidateplot_h2&matplotlib=checked&left_ymin=0&left_ymax=0&left_plotlist[]=1&left_plotlist[]=2&left_plotlist[]=3&left_plotlist[]=4&left_plotlist[]=5&left_plotlist[]=6&right_ymin=0&right_ymax=0">All H2 detectors</a></li>
             <li><a href="https://cinfdata.fysik.dtu.dk/gasmonitor307/dateplot.php?type=multidateplot&matplotlib=checked&left_ymin=0&left_ymax=0&left_plotlist[]=1&right_ymin=0&right_ymax=0&right_plotlist[]=2">CO and H2 051</a></li>
             <li><a href="https://cinfdata.fysik.dtu.dk/gasmonitor307/dateplot.php?type=multidateplot&matplotlib=checked&left_ymin=0&left_ymax=0&left_plotlist[]=3&right_ymin=0&right_ymax=0&right_plotlist[]=4">CO and H2 055</a></li>
             <li><a href="https://cinfdata.fysik.dtu.dk/gasmonitor307/dateplot.php?type=multidateplot&matplotlib=checked&left_ymin=0&left_ymax=0&left_plotlist[]=5&right_ymin=0&right_ymax=0&right_plotlist[]=6">CO and H2 059</a></li>
             <li><a href="https://cinfdata.fysik.dtu.dk/gasmonitor307/dateplot.php?type=multidateplot&matplotlib=checked&left_ymin=0&left_ymax=0&left_plotlist[]=7&right_ymin=0&right_ymax=0&right_plotlist[]=8">CO and H2 061</a></li>
             <li><a href="https://cinfdata.fysik.dtu.dk/gasmonitor307/dateplot.php?type=multidateplot&matplotlib=checked&left_ymin=0&left_ymax=0&left_plotlist[]=11&right_ymin=0&right_ymax=0&right_plotlist[]=12">CO and H2 932</a></li>
             <li><a href="https://cinfdata.fysik.dtu.dk/gasmonitor307/dateplot.php?type=multidateplot&matplotlib=checked&left_ymin=0&left_ymax=0&left_plotlist[]=9&right_ymin=0&right_ymax=0">CO 42-43</a></li>
             <li><a href="https://cinfdata.fysik.dtu.dk/gasmonitor307/dateplot.php?type=multidateplot&matplotlib=checked&left_ymin=0&left_ymax=0&left_plotlist[]=10&right_ymin=0&right_ymax=0">H2 2.Floor</a></li>
	   </ul>
          </td>
         </tr>

         <tr>
          <td><a href="javascript:toggle('pvd309')"><h2>PVD 309</h2></a></td>
           <td><a href="javascript:toggle('chemlab312')"><h2>Chemlab 312</h2></a></td>
           <td>&nbsp;</td>
         </tr>

         <tr>
          <td>
           <ul id="pvd309" style="display:none"> 
	     <li><a href="pvd_309/dateplot.php?type=multidateplot&left_plotlist[]=1">Chamber Pressure</a></li>
	   </ul>
          </td>
          <td>
           <ul id="chemlab312" style="display:none"> 
	     <li><a href="chemlab312/dateplot.php?type=multidateplot&left_plotlist[]=1">Chamber Pressure</a></li>
	   </ul>

	   </ul>
          </td>
          <td>
  &nbsp;
          </td>
         </tr>

   </table>
<?php
echo(html_footer());
?>
