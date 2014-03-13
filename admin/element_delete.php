<?php 

/*
 * Webpage used for editing a layer of the webgis config file
 *
 * (c) Champs Libres COOP <info@champs-libres.coop>
 */

require('data_fct.php');
require('display_fct.php');
$json_array = json_array_get();
$layer_id = $_GET["id"];
display_header("Suppression d'un élément");
$ret = json_array_pop_element($json_array, $layer_id);
$json_array = $ret[0]; 
json_array_save($json_array);
echo "<div class=\"message success\">La couche a bien été supprimée</div>";
display_footer();
?>