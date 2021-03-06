<?php 

/*
 * Webpage used for editing the order of appearance of the layers of the webgis config file
 *
 * (c) Champs Libres COOP <info@champs-libres.coop>
 */

require('data_fct.php');
require('display_fct.php');
$json_array = json_array_get();
display_header("Mise de l'ordre d'affichage des éléments");

if(isset($_POST["element_id"])) {
   $json_array = json_array_edit_elements_position($json_array, $_POST["element_id"], $_POST["new_id"]);
   json_array_save($json_array);
   echo "<div class=\"message success\">L'odre a été mis à jour.</div>";
}
?>

<h2>Ordre actuel</h2>

<?php    
   for($i = 0, $size = count($json_array["elements"]); $i < $size; ++$i) {
      echo $i . ". " . json_array_get_element_field($json_array, $i, "menu_title", "[sans titre]"). "<br />" . PHP_EOL;
   }
?>

<h2>Mise à jour</h2>

<form action="" method="post">
   Mettre la couche 
   <select name="element_id">
      <?php 
      for($i = 0, $size = count($json_array["elements"]); $i < $size; ++$i) {
         echo "<option value=\"". $i ."\">" . $i . ". " . json_array_get_element_field($json_array, $i, "menu_title", "[sans titre]") . "</option>";
      }
      ?>
   </select>
   à la position
   <select name="new_id">
      <?php 
      for($i = 0, $size = count($json_array["elements"]); $i < $size; ++$i) {
         echo "<option value=\"". $i ."\">" . $i  . "</option>";
      }
      ?>
   </select>
<input type="submit" value="Exécuter">
</form>

<?php
display_footer();
?>