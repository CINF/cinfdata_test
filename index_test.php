<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US">
  <head><title>CINF data logging</title>
<!--    <link rel="StyleSheet" href="css/screen.css" type="text/css" media="screen" />-->
    <link rel="StyleSheet" href="css/style.css" type="text/css" media="screen" />

    <script type="text/javascript">
     function toggle(list){ 
     var listElementStyle=document.getElementById(list).style;
      if (listElementStyle.display=="none"){ 
        listElementStyle.display="block"; 
      }
      else{listElementStyle.display="none"; 
      } 
     }
    </script>
  </head>
  <body>
    <div class="container">
      <div class="caption">Data logged at CINF
       <img class="logo" src="images/cinf_logo_beta_greek.png">
       <div class="header_utilities">
         <a class="header_links" href="https://cinfwiki.fysik.dtu.dk/cinfwiki/Software/DataWebPageUserDocumentation">Help</a><br>
       </div>
      </div>
      <div class="table_container">
       <div class="frontpage_table">
	    <a href="javascript:toggle('bifrost')"><h2>Bifrost</h2></a>
            <ul id="bifrost" style="display:none">
	      <li><a href="bifrost/read_dateplot.php?type=pressure">Pressure</a></li>
	      <li><a href="bifrost/read_dateplot.php?type=temperature">Temperature</a></li>
	      <li><a href="bifrost/status_heating.php">Heating status</a></li>
	      <li><a href="bifrost/status_ion_gun.php">Ion gun status</a></li>
	      <li><a href="bifrost/read_plot.php?type=xps">XPS-data</a></li>
	      <li><a href="bifrost/read_plot.php?type=aes">AES-data</a></li>
	      <li><a href="bifrost/read_plot.php?type=massspectrum">Mass spectra</a></li>
	      <li><a href="bifrost/read_plot_group.php?type=masstime">Mass-time</a></li>
	      <li><a href="bifrost/plot.php?type=morning_pressure&from=2009-01-01&to=2012-01-01&manualscale=checked&ymax=1E-5&ymin=1E-11&xsize=1000&ysize=750">Morning status</a></li>
	    </ul>
          </div>
          <div class="frontpage_table">
            <a href="javascript:toggle('stm312')"><h2>STM312</h2></a>
            <ul id="stm312" style="display:none">
	     <li><a href="stm312/dateplot.php?type=multidateplot&from=0&to=0&left_logscale=checked&left_ymin=0&left_ymax=0&left_plotlist[]=1&left_plotlist[]=2&right_ymin=0&right_ymax=0">Pressure</a></li>
	     <li><a href="stm312/dateplot.php?type=multidateplot&from=0&to=0&left_ymin=0&left_ymax=0&left_plotlist[]=3&right_ymin=0&right_ymax=0">Temperature</a></li>
	     <li><a href="stm312/dateplot.php?type=multidateplot&from=0&to=0&left_ymin=0&left_ymax=0&left_plotlist[]=4&right_ymin=0&right_ymax=0&right_plotlist[]=5&right_plotlist[]=6">Roughing pressure</a></li>
	     <li><a href="stm312/dateplot.php?type=multidateplot&from=0&to=0&left_ymin=0&left_ymax=0&left_plotlist[]=7&left_plotlist[]=8&left_plotlist[]=9&right_ymin=0&right_ymax=0">Turbo temperatures</a></li>
             <hr>
             <li><a href="stm312/stm_overview.php">STM Images</a></li>
	     <li><a href="stm312/xyplot.php?type=massspectra&left_logscale=checked">Mass spectra</a></li>

	     <li><a href="stm312/xyplot.php?type=xps&flip_x=checked&matplotlib=checked">XPS-data</a></li>
	     <li><a href="stm312/xyplot.php?type=masstime">Mass-time</a></li>
             <hr>
	     <li><a href="stm312_2/bakeout.php">Bakeout</a></li>
             <li><a href="stm312/modify_comment.php">Modify comments</a></li>
	   </ul>
          </div>
          <div class="frontpage_table">
           <a href="javascript:toggle('volvo')"><h2>Volvo</h2></a>
           <ul id="volvo" style="display:none">
	    <li><a href="volvo/read_dateplot.php?type=pressure">Pressure</a></li>
	    <li><a href="volvo/read_plot.php?type=iv">IV-curves</a></li>
	    <li><a href="volvo/read_plot.php?type=xps">XPS-data</a></li>
	    <li><a href="volvo/read_plot.php?type=iss">ISS-data</a></li>
	    <li><a href="volvo/read_plot.php?type=massspectrum">Mass spectrums</a></li>
	    <li><a href="volvo/read_plot_group.php?type=masstime">Mass-time</a></li>
	    <li><a href="volvo/plot.php?type=morning_pressure&from=2009-01-01&to=2012-01-01&manualscale=checked&ymax=1E-5&ymin=1E-11&xsize=1000&ysize=750">Morning pressure - Beta!</a></li>
	   </ul>
          </div>

          <div class="frontpage_table">
	   <a href="javascript:toggle('microreactor')"><h2>Microreactor</h2>
           <ul id="microreactor" style="display:none">      
	    <li><a href="microreactor/read_dateplot.php?type=pressure">Chamber pressure</a></li>
	    <li><a href="microreactor/read_dateplot.php?type=pirani_buffervolume">Pirani, buffer volume</a></li>
	    <li><a href="microreactor/read_dateplot.php?type=pirani_newturbo">Backing pressure, new turbo</a></li>
	    <li><a href="microreactor/read_dateplot.php?type=temperature_turbos">Turbo temperatures</a></li>
	    <li><a href="microreactor/read_dateplot.php?type=pirani_oldturbo">Backing pressure, old turbo</a></li>
	    <li><a href="microreactor/read_dateplot.php?type=temperature">Temperature, reactor</a></li>
	    <li><a href="microreactor/read_dateplot.php?type=reactor_pressure">Reactor pressure</a></li>
	    <li><a href="microreactor/read_plot.php?type=massspectrum">Mass spectra</a></li>
	    <li><a href="microreactor/read_plot_group.php?type=masstime">Mass-time</a></li>
	    <li><a href="microreactor/plot.php?type=morning_pressure&from=2009-01-01&to=2012-01-01&manualscale=checked&ymax=1E-4&ymin=1E-11&xsize=1000&ysize=750">Morning pressure - Beta!</a></li>
           <li><a href="microreactor/modify_comment.php">Modify comments</a></li>
	   </ul>
          </div>

          <div class="frontpage_table">
           <a href="javascript:toggle('microreactorNG')"><h2>Microreactor NG</h2></a>
           <ul id="microreactorNG" style="display:none">
	    <li><a href="microreactorNG/dateplot.php?type=multidateplot&from=0&to=0&left_ymin=0&left_ymax=0&left_plotlist[]=1&right_ymin=0&right_ymax=0">Chamber pressure</a></li>
	    <li><a href="microreactorNG/dateplot.php?type=multidateplot&from=0&to=0&left_ymin=0&left_ymax=0&left_plotlist[]=2&right_ymin=0&right_ymax=0&right_plotlist[]=3">Buffer and containment volume</a></li>
	    <li><a href="microreactorNG/read_dateplot.php?type=reactor_pressure">Reactor pressure</a></li> 
	    <li><a href="microreactorNG/read_dateplot.php?type=temperature_sample">Sample temperature</a></li>
	    <li><a href="microreactorNG/read_dateplot.php?type=pirani_bufferturbo">Backing pressure, buffer turbo</a></li>
	    <li><a href="microreactor/read_dateplot.php?type=pirani_oldturbo">Backing pressure, chamber turbo</a></li>
	    <li><a href="microreactorNG/read_dateplot.php?type=temperature_turbos">Turbo temperatures</a></li>
	    <li><a href="microreactorNG/xyplot.php?type=massspectrum">Mass spectra</a></li>
	    <li><a href="microreactorNG/xyplot.php?type=masstime">Mass-time</a></li>
	    <li><a href="microreactorNG/plot.php?type=morning_pirani_bufferturbo&from=2009-01-01&to=2012-01-01&manualscale=checked&ymax=1E-1&ymin=1E-5&xsize=1000&ysize=750">Morning pressure</a></li>
           <li><a href="microreactorNG/modify_comment.php">Modify comments</a></li>
	   </ul>
          </div>

          <div class="frontpage_table">
	   <a href="javascript:toggle('omicron')"><h2>Omicron</h2></a>
           <ul id="omicron" style="display:none"> 
	    <li><a href="omicron/read_dateplot.php?type=temperature">Temperature</a></li>
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
          </div>

          <div class="frontpage_table">
	   <a href="javascript:toggle('xrd')"><h2>XRD</h2>
           <ul id="xrd" style="display:none"> 
	    <li><a href="xrd/status.php">Pressure status</a></li>
	    <li><a href="xrd/read_dateplot.php?type=pressure_asg">Pressure asg</a></li>
	    <li><a href="xrd/read_dateplot.php?type=pressure_wrg">Pressure wrg</a></li>
	    <li><a href="xrd/read_dateplot.php?type=pressure_wrgms">Pressure wrgms</a></li>
	   </ul>
          </div>

          <div class="frontpage_table"> 
	   <a href="javascript:toggle('hall')"><h2>Hall</h2></a>
           <ul id="hall" style="display:none"> 
	    <li><a href="hall/read_dateplot.php?type=temperature">Temperature</a></li>
	   </ul>
          </div>

          <div class="frontpage_table"> 
	   <a href="javascript:toggle('smallHPC')"><h2>Small HPC</h2></a>
           <ul id="smallHPC" style="display:none"> 
	    <li><a href="small_hpc/read_dateplot.php?type=temperature">Temperature</a></li>
	    <li><a href="small_hpc/read_plot_group.php?type=masstime">Mass-time</a></li>
	    <li><a href="small_hpc/read_plot.php?type=massspectrum">Mass spectrums</a></li>
            <li><a href="small_hpc/modify_comment.php">Modify comments</a></li>
	   </ul>
          </div>

          <div class="frontpage_table"> 
	   <a href="javascript:toggle('TOF')"><h2>TOF</h2></a>
           <ul id="TOF" style="display:none"> 
	    <li><a href="tof/read_dateplot.php?type=pressure_tof_iongauge">Pressure, ion gauge</a></li>
	    <li><a href="tof/read_dateplot.php?type=pressure_tof_flighttube">Pressure, flight tube</a></li>
	    <li><a href="tof/read_dateplot.php?type=pressure_tof_pirani">Backing pressure, turbo</a></li>
            <li><a href="tof/read_dateplot.php?type=pressure_tof_ionpump">Pressure, small ion pump</a></li>
            <li><a href="tof/read_dateplot.php?type=temperature_tof_turbopump">Temperature</a></li>
	    <li><a href="tof/read_plot.php?type=tofspectrum">TOF spectra</a></li>
	    <li><a href="tof/read_plot.php?type=massspectrum">Mass spectra</a></li>
	    <li><a href="tof/read_plot_group.php?type=masstime">Mass-time</a></li>
            <li><a href="tof/plot.php?type=morning_pressure&from=2009-01-01&to=2012-01-01&manualscale=checked&ymax=1E-5&ymin=1E-11&xsize=1000&ysize=750">Morning pressure</a></li>
	    <li><a href="http://robertj">Flight Time Analysis</a></li>
	    <li><a href="tof/mass_calc.html">Calc. mass deficiency</a></li>
	   </ul>
          </div>

          <div class="frontpage_table"> 
           <a href="javascript:toggle('tower')"><h2>The Tower</h2></a>
           <ul id="tower" style="display:none">
	    <li><a href="tower/read_dateplot.php?type=pressure">Pressure</a></li>
	    <li><a href="tower/read_plot_group.php?type=deposition">Deposition</a></li>
	   </ul>
          </div>

          <div class="frontpage_table"> 
	   <a href="javascript:toggle('ps')"><h2>Parallel Screening</h2>
           <ul id="ps" style="display:none"> 
	    <li><a href="ps/read_dateplot.php?type=pressure">Pressure</a></li>
	    <li><a href="ps/read_dateplot.php?type=temperature">Temperature</a></li>
	    <li><a href="ps/read_dateplot.php?type=temperature_turbos">Temperature, turbos</a></li>
	    <li><a href="ps/read_plot_group.php?type=deposition">Deposition</a></li>
            <li><a href="ps/read_plot.php?type=massspectrum">Mass spectra</a></li>
            <li><a href="ps/read_plot_group.php?type=masstime">Mass-time</a></li>
	    <li><a href="ps/plot.php?type=morning_pressure&from=2009-01-01&to=2015-01-01&manualscale=checked&ymax=1E-5&ymin=1E-11&xsize=1000&ysize=750">Morning pressure</a></li>
           <li><a href="ps/modify_comment.php">Modify comments</a></li>
	   </ul>
          </div>

          <div class="frontpage_table"> 
           <a href="javascript:toggle('shortlinks')"><h2>Shortlinks</h2></a>
            <ul id="shortlinks" style="display:none"> 
	     <li><a href="links/show_links.php">Show previous shortlinks</a></li>
	    </ul>
          </div>

          <div class="frontpage_table"> 
	   <a href="javascript:toggle('dummy')"><h2>Dummy</h2></a>
            <ul id="dummy" style="display:none"> 
	     <li><a href="dummy/read_plot_group.php?type=masstime">Mass-time</a></li>
	     <li><a href="dummy/read_plot.php?type=massspectrum">Mass spectrums</a></li>
	    </ul>
          </div>

          <div class="frontpage_table"> 
	   <a href="javascript:toggle('oldclustersource')"><h2>Old cluster source</h2></a>
            <ul id="oldclustersource" style="display:none"> 
	     <li><a href="oldclustersource/read_plot_group.php?type=clusterdeposition">Cluster deposition</a></li>
             <li><a href="oldclustersource/read_dateplot.php?type=temperatures">Temperatures</a></li>
             <li><a href="oldclustersource/read_dateplot.php?type=backingpressure">Backing pressures</a></li>
	    </ul>
          </div>

          <div class="frontpage_table">
           <a href="javascript:toggle('photomicroreactor')"><h2>Photo-microreactor</h2></a>
           <ul id="photomicroreactor" style="display:none"> 
	     <li><a href="photo_microreactor/read_plot_group.php?type=masstime">Mass-time</a></li>
             <li><a href="photo_microreactor/read_plot.php?type=massspectrum">Mass spectra</a></li>
	   </ul>
          </div>

          <div class="frontpage_table">
           <a href="javascript:toggle('ups')"><h2>UPS</h2></a>
           <ul id="ups" style="display:none"> 
	     <li><a href="ups/read_dateplot.php?type=kVA">VA</a></li>
	     <li><a href="ups/read_dateplot.php?type=power">Power</a></li>
	     <li><a href="ups/read_dateplot.php?type=current">Current</a></li>
	     <li><a href="ups/read_dateplot.php?type=voltage_output">Voltage output</a></li>
	     <li><a href="ups/read_dateplot.php?type=voltage_input">Voltage input</a></li>
	     <li><a href="ups/read_dateplot.php?type=frequency">Input frequency</a></li>
	     <li><a href="ups/read_dateplot.php?type=batt_temperature">Battery temperature</a></li>
	   </ul>
          </div>

          <div class="frontpage_table">
           <a href="javascript:toggle('other')"><h2>Other</h2></a>
           <ul id="other" style="display:none"> 
	     <li><a href="code-documentation/test_config_all.php">Test my configuration file</a></li>
	     <li><a href="code-documentation/statistics.php">Statistics</a></li>
	     <li><a href="sym-files2/dateplot.php?type=multidateplot">Dateplot demo</a></li>
	     <li><a href="sym-files2/xyplot.php?type=masstime">XY plot demo</a></li>
	   </ul>
          </div>
      </div>
      <div class="copyright">...</div>
    </div>
    
  </body>
</html>
