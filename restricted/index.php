<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US">
  <head><title>CINF data logging</title>
    <link rel="StyleSheet" href="css/screen.css" type="text/css" media="screen" />
    <link rel="StyleSheet" href="css/handheld.css" type="text/css" media="handheld" />
  </head>
  <body>
    <div class="container">
      <div class="caption">Data logged at CINF
	<!--<img class="logo" src="images/cinf_logo_beta.png">-->
       <img class="logo" src="images/cinf_logo_beta_greek.png">
	<!--<img class="logo" src="images/cinf_logo_beta.png"/><img class="logo" src="images/underconstruction.gif"/>-->
      </div>
      
      <div class="element">
	<h2>Bifrost</h2>
	<ul>
	  <li><a href="bifrost/read_dateplot.php?type=pressure">Pressure</a></li>
	  <li><a href="bifrost/read_dateplot.php?type=temperature">Temperature</a></li>
	  <li><a href="bifrost/status_heating.php">Heating status</a></li>
	  <li><a href="bifrost/status_ion_gun.php">Ion gun status</a></li>
	  <li><a href="bifrost/read_plot.php?type=xps">XPS-data</a></li>
	  <li><a href="bifrost/read_plot.php?type=aes">AES-data</a></li>
	  <li><a href="bifrost/read_plot.php?type=massspectrum">Mass spectrums</a></li>
	  <li><a href="bifrost/read_plot_group.php?type=masstime">Mass-time</a></li>
	  <li><a href="bifrost/plot.php?type=morning_pressure&from=2009-01-01&to=2012-01-01&manualscale=checked&ymax=1E-5&ymin=1E-11&xsize=1000&ysize=750">Morning status</a></li>
	  <!-- <li><a href="bifrost/manage_measurements.php">Manage measurements</a></li>-->
	</ul>
      </div>
      
      <div class="element">
	<h2>STM 312</h2>
	<ul>
	  <li><a href="stm312/read_dateplot.php?type=pressure">Pressure</a></li>
	  <li><a href="stm312/read_dateplot.php?type=temperature">Temperature</a></li>
	  <li><a href="stm312/read_plot.php?type=massspectrum">Mass spectra</a></li>
	  <li><a href="stm312/read_plot.php?type=xps">XPS-data</a></li>
	  <li><a href="stm312/read_plot_group.php">Mass-time</a></li>
	  <li><a href="stm312/bakeout.php">Bakeout</a></li>
	  <li><a href="stm312_test/plot.php?type=morning_pressure&from=2009-01-01&to=2012-01-01&manualscale=checked&ymax=1E-5&ymin=1E-11&xsize=1000&ysize=750">Morning pressure - Beta!</a></li>
	</ul>
      </div>
      
      <div class="element">
	<h2>Volvo</h2>
	<ul>
	  <!--<li><a href="volvo/current_status.php">Current status</a></li>-->
	  <li><a href="volvo/read_dateplot.php?type=pressure">Pressure</a></li>
	  <!--<li><a href="volvo/read_dateplot.php?type=temperature">Temperature</a></li>-->
	  <li><a href="volvo/read_plot.php?type=iv">IV-curves</a></li>
	  <li><a href="volvo/read_plot.php?type=xps">XPS-data</a></li>
	  <li><a href="volvo/read_plot.php?type=iss">ISS-data</a></li>
	  <li><a href="volvo/read_plot.php?type=massspectrum">Mass spectrums</a></li>
	  <li><a href="volvo/plot.php?type=morning_pressure&from=2009-01-01&to=2012-01-01&manualscale=checked&ymax=1E-5&ymin=1E-11&xsize=1000&ysize=750">Morning pressure - Beta!</a></li>
	</ul>
      </div>
      
      <div class="element">
	<h2>Microreactor</h2>
	<ul>
	  <li><a href="microreactor/read_dateplot.php?type=pressure">Chamber pressure</a></li>
	  <li><a href="microreactor/read_dateplot.php?type=pirani_buffervolume">Pirani, buffer volume</a></li>
	  <li><a href="microreactor/read_dateplot.php?type=pirani_newturbo">Backing	pressure, new turbo</a></li>
	  <li><a href="microreactor/read_dateplot.php?type=pirani_oldturbo">Backing	pressure, old turbo</a></li>
	  <li><a href="microreactor/read_dateplot.php?type=temperature">Temperature</a></li>
	  <li><a href="microreactor/read_dateplot.php?type=reactor_pressure">Reactor pressure</a></li>
	  <li><a href="microreactor/read_plot.php?type=massspectrum">Mass spectrums</a></li>
	  <li><a href="microreactor/read_plot_group.php?type=masstime">Mass-time</a></li>
	  <li><a href="microreactor/plot.php?type=morning_pressure&from=2009-01-01&to=2012-01-01&manualscale=checked&ymax=1E-4&ymin=1E-11&xsize=1000&ysize=750">Morning pressure - Beta!</a></li>
	  <li><a href="microreactor/plot.php?type=masstime_massspectrum&from=%202010-08-01%2021:56&to=%202010-08-09%2012:57">Mass-time from mass-scans - Alfa!!!</a></li>
	  <li><a href="microreactor/manage_measurements.php">Manage measurements</a></li>
	</ul>
      </div>
      
      <div class="element">
	<h2>Omicron</h2>
	<ul>
	  <li><a href="omicron/read_dateplot.php?type=temperature">Temperature</a></li>
	  <li><a href="omicron/read_dateplot.php?type=pressure_ana">Pressure, analytical chamber</a></li>
	  <li><a href="omicron/read_dateplot.php?type=pressure_prep">Pressure, prep chamber</a></li>
	  <li><a href="omicron/plot.php?type=morning_pressure_ana&from=2009-01-01&to=2012-01-01&manualscale=checked&ymax=1E-7&ymin=1E-11&xsize=1000&ysize=750">Morning pressure, analytical chamber- Beta!</a></li>
	  <li><a href="omicron/plot.php?type=morning_pressure_prep&from=2009-01-01&to=2012-01-01&manualscale=checked&ymax=1E-7&ymin=1E-11&xsize=1000&ysize=750">Morning pressure, prep chamber - Beta!</a></li>
	  <li><a href="omicron/read_plot_group.php?type=masstime">Mass-time</a></li>
	  <li><a href="omicron/read_plot.php?type=massspectrum">Mass spectrums</a></li>
	</ul>
      </div>
      
      <div class="element">
	<h2>In-situ XRD</h2>
	<ul>
	  <li><a href="xrd/status.php">Pressure status</a></li>
	</ul>
      </div>
      
      <div class="element">
	<h2>Experimental hall</h2>
	<ul>
	  <li><a href="hall/read_dateplot.php?type=temperature">Temperature</a></li>
	</ul>
      </div>
      
      <div class="element">
	<h2>Small HPC</h2>
	<ul>
	  <li><a href="small_hpc/read_dateplot.php?type=temperature">Temperature</a></li>
	  <li><a href="small_hpc/read_plot_group.php?type=masstime">Mass-time</a></li>
	  <li><a href="small_hpc/read_plot.php?type=massspectrum">Mass spectrums</a></li>
	</ul>
      </div>
      
      <div class="element">
	<h2>Web page testing</h2>
	<ul>
	  <li><a href="stm312_test/read_dateplot.php?type=pressure">Pressure</a></li>
	  <li><a href="stm312_test/read_dateplot.php?type=temperature">Temperature</a></li>
	  <li><a href="stm312_test/read_plot.php?type=massspectrum">Mass spectra</a></li>
	  <li><a href="stm312_test/read_plot.php?type=xps&flip_x=checked">XPS-data</a></li>
	  <li><a href="stm312_test/read_plot_group.php?type=masstime">Mass-time</a></li>
	  <li><a href="stm312_test/bakeout.php">Bakeout</a></li>
	  <li><a href="stm312_test/plot.php?type=morning_pressure&from=2009-01-01&to=2012-01-01&manualscale=checked&ymax=1E-5&ymin=1E-11&xsize=1000&ysize=750">Morning pressure - Beta!</a></li>
	</ul>
      </div>
      
      <div class="element">
	<h2>Dummy</h2>
	<ul>
	  <li><a href="dummy/read_plot_group.php?type=masstime">Mass-time</a></li>
	  <li><a href="dummy/read_plot.php?type=massspectrum">Mass spectrums</a></li>
	</ul>
      </div>
      
      
      
      <div class="next"></div>
      <div class="copyright"><a>...</a></div>
    </div>
    
  </body>
</html>
