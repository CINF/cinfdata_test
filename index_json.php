<?php

$dirs = Array();
$dir = new DirectoryIterator(dirname(__FILE__));
foreach ($dir as $fileinfo) {
  if ($fileinfo->isDir() && !$fileinfo->isDot()) {
    if (file_exists($fileinfo->getFilename() . '/graphsettings.xml')){
      $xml = simplexml_load_file($fileinfo->getFilename() . '/graphsettings.xml');
      $title = (string) $xml->global_settings->app_title;
      foreach($xml->global_settings->children() as $child){
	if ($child->getName() == "app_title"){
	  $dirs[] = Array("setup" => $fileinfo->getFilename(),
			  "setup_name" => $title);
	}
      }
    }
  }
}

echo(json_encode($dirs));

?>