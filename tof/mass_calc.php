<?php

// Remember settings after submitting and initialize values
$carbon_atoms_1              = isset($_GET["carbon_atoms_1"])   ? $_GET["carbon_atoms_1"]   : 0;
$oxygen_atoms_1              = isset($_GET["oxygen_atoms_1"])   ? $_GET["oxygen_atoms_1"]   : 0;
$hydrogen_atoms_1            = isset($_GET["hydrogen_atoms_1"]) ? $_GET["hydrogen_atoms_1"] : 0;
$nitrogen_atoms_1            = isset($_GET["nitrogen_atoms_1"]) ? $_GET["nitrogen_atoms_1"] : 0;

$carbon_atoms_2              = isset($_GET["carbon_atoms_2"])   ? $_GET["carbon_atoms_2"]   : 0;
$oxygen_atoms_2              = isset($_GET["oxygen_atoms_2"])   ? $_GET["oxygen_atoms_2"]   : 0;
$hydrogen_atoms_2            = isset($_GET["hydrogen_atoms_2"]) ? $_GET["hydrogen_atoms_2"] : 0;
$nitrogen_atoms_2            = isset($_GET["nitrogen_atoms_2"]) ? $_GET["nitrogen_atoms_2"] : 0;

$total_mass_1 = 12.0107*$carbon_atoms_1+15.9994*$oxygen_atoms_1+1.00794*$hydrogen_atoms_1+14.00674*$nitrogen_atoms_1;
$total_mass_2 = 12.0107*$carbon_atoms_2+15.9994*$oxygen_atoms_2+1.00794*$hydrogen_atoms_2+14.00674*$nitrogen_atoms_2;

$mass_difference = abs($total_mass_2-$total_mass_1)*1000;

?>

<html>
 <head>
  <title>Calculate mass</title>
    <link rel="StyleSheet" href="../css/screen.css" type="text/css" media="screen">
 </head>
 <div class="container">
 <div class="caption">Data viewer
  <a href="/"><img class="logo" src="../images/cinf_logo_beta_greek.png"></a>
 </div>
 </head>
 <body>
   <table>
     <tr>
       <td>
         <b>Total mass molecule 1:</b> <?php echo($total_mass_1);?> amu
       </td>
     </tr>
     <tr>
       <td>
         <b>Total mass molecule 2:</b> <?php echo($total_mass_2);?> amu
       </td>
     </tr>
     <tr>
       <td>
         <b>Mass difference:</b> <?php echo($mass_difference);?> milli-amu
       </td>
     </tr>
   </table>
  <p><a href="javascript:history.back()">Previous page</a></p>
 <div class="copyright">...</div>
 </body>
</html>
