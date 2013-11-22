<?
include("../common_functions.php");
$db = std_db();

/*
0. Find den nyeste måling
1. Find step-størrelsen
2. Find mindste heltal større end startværdien
3. Beregn antal steps mellem hvert heltal (også selvom der ikke er målt ved heltallige værdier)
4. Gå frem i hop af dette antal steps og rund evt. ned, hvis der ikke er målt ved heltallige værdier
5. Nu haves en liste over samtlige værdier -> find de N mest forekommende

?>
