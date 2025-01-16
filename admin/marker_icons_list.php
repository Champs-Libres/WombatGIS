<?php

/*
 * Webpage used for editing a layer of the webgis config file
 *
 * (c) Champs Libres COOP <info@champs-libres.coop>
 */

require('display_fct.php');
display_header("Liste des icônes", false);

$dir = "../img/marker";
$files = scandir($dir);
   foreach ($files as $file) {
      if ($file[0] != '.' and $file != 'README.md') {
         echo "<div style=\"margin:30px; float:left; text-align: center;\"><img src=\"../img/marker/" . $file . "\" height=\"30px\" alt=\" . $file . \"><br />". $file ."</div>";
            }
         }
display_footer(false);
?>