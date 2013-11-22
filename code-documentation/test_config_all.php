<?php
include("graphsettings.php");
include("../common_functions_v2.php");

echo(html_header());

$chambers = Array("bifrost", "stm312", "volvo", "microreactor",
		  "microreactorNG", "omicron", "xrd", "hall", "small_hpc",
		  "tof", "tower", "ps", "oldclustersource",
		  "photo_microreactor", "ups");
?>

<h1>Test configuration files</h1>
<p>If you are unable to check your configuration files from one of
your own pages because the do not load, you can test them from
here. Simply click the link below for your chamber.</p>

<ul>
<?php

  foreach($chambers as $chamber){
    echo("<li><a href=\"../{$chamber}/test_configuration_file.php\">{$chamber}</a></li>");
  }
?>
</ul>

<?php echo(html_footer());?>
