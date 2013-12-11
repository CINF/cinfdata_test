<?php
include("graphsettings.php");
include("../common_functions_v2.php");

$ansi2html_dict = array('[33m' => '<span style="color:brown">',
			'[31m' => '<span style="color:red">',
			'[m'   => '</span>' ,
			);
function ansi2html($string){
  global $ansi2html_dict;
  return str_replace(array_keys($ansi2html_dict), $ansi2html_dict, $string);
}

echo(html_header());
echo("\n\n");

echo("<h1>Git information for cinfdata</h1>\n");

echo("<h2>Git status</h2>\n");
echo("<pre>\n");
$gitstatus = shell_exec("cd .. && git --git-dir=/var/www/cinfdata/.git --work-tree=/var/www/cinfdata/ status");
#$gitstatus = ansi2html($gitstatus);
echo($gitstatus);
echo("</pre>\n");

echo("<h2>Short log</h2>\n");
echo("<pre>\n");
$gitlog = shell_exec("git log --color --graph --decorate --pretty=oneline --abbrev-commit");

$gitlog = ansi2html($gitlog);
echo($gitlog);
echo("</pre>\n");

echo("<h2>Detailed log</h2>\n");
echo("<pre>\n");
$gitlog = shell_exec("git log --color --graph --decorate --pretty=medium --abbrev-commit");
$gitlog = ansi2html($gitlog);
echo($gitlog);
echo("</pre>\n");
echo("\n\n");
echo(html_footer());
