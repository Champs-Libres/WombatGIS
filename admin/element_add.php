<?php 

/*
 * Webpage used for adding a layer of the webgis config file
 *
 * (c) Champs Libres COOP <info@champs-libres.coop>
 */
require('data_fct.php');
require('display_fct.php');
$json_array = json_array_get();
$json_array = json_array_add_element($json_array);
json_array_save($json_array);
display_header("Ajout d'un élément");
echo "<div class=\"message success\">Un nouvel élément a été ajouté</div>";
display_footer();
?>