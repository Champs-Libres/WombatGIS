<?php 

/*
 * Webpage used for editing a layer of the webgis config file
 *
 * (c) Champs Libres COOP <info@champs-libres.coop>
 */

require('data_fct.php');
require('display_fct.php');
$json_array = json_array_get();
$element_id = $_GET["id"];
display_header("Mise à jour d'un élement");

if(isset($_POST["form_id"])) {
   if($_POST["form_id"] == "menuTitle") {
      $json_array = json_array_edit_element_field($json_array, $element_id, "menuTitle", $_POST["menuTitle"]);
      $message = "Titre mis à jour";
   } elseif($_POST["form_id"] == "icon") {
      if($_POST["icon"] == "") {
         $json_array = json_array_delete_element_field($json_array, $element_id, "icon");
         $message = "Icône supprimée";
      }
      else {
         $json_array = json_array_edit_element_field($json_array, $element_id, "icon", $_POST["icon"]);
         $message = "Icône mise à jour";
      }
   } elseif($_POST["form_id"] == "style") {
      $new_style = json_decode($_POST["style"],true);
      $json_array = json_array_edit_element_field($json_array, $element_id, "style", $new_style);
      $message = "Style mise à jour";
   } elseif($_POST["form_id"] == "icon_upload") {
      $icon_name = updload("icon_file", "../img/marker");
      $json_array = json_array_edit_element_field($json_array, $element_id, "icon", $icon_name);
      $message = "Icône downloadée et ajoutée";
   } elseif ($_POST["form_id"] == "tempate_delete") {
      $json_array = json_array_delete_element_field($json_array, $element_id, "template");
      $message = "Template supprimé";
   } elseif ($_POST["form_id"] == "tempate_update") {
      $json_array = upload_file_and_json_array_update($json_array,$element_id,"template");
      $message = "Template mis à jour";
   } elseif ($_POST["form_id"] == "geojson_update") {
      $json_array = upload_file_and_json_array_update($json_array,$element_id,"geojson");
      $message = "GeoJSON mis à jour";
   }
   json_array_save($json_array);
   echo "<div class=\"message success\">" . $message . "</div>";
}
   
$element = $json_array["elements"][$element_id];
?>
<h2>Edition du titre</h2> 

<p>
   Titre actuel:  
   <?php display_element_field($json_array, $element_id, "menuTitle", "pas de titre (l'élément n'est pas affiché dans le menu)"); ?>
</p>

<form method="post">
   <input type="hidden" name="form_id" value="menuTitle">
   <input type="input" name="menuTitle" value="<?php echo json_array_get_element_field($json_array,$element_id,"menuTitle","");  ?>">
   <input type="submit" value="Modifier">
</form>

<h2>Edition du GeoJSON</h2> 

<p>
   GeoJSON actucel:
   <?php display_element_field($json_array, $element_id, "geojson", "pas de geojson"); ?>
</p>

   <form enctype="multipart/form-data" method="post">
      <input type="hidden" name="form_id" value="geojson_update">
      <input type="file" name="webgis_file">
      <input type="submit" value="Mettre à jour le geojson">
   </form>

<h2>Choix de l'icône des marqueurs (pour les points)</h2>

<p>
   Icône actuelle :
   <?php display_element_field($json_array, $element_id, "icon", "pas d'icône"); ?>
</p>

<p>
<form method="post">
   <input type="hidden" name="form_id" value="icon">
   <select name="icon">
      <option value="">Pas d'icône</option>
      <?php
         $dir = "../img/marker";
         $files = scandir($dir);
         foreach ($files as $file) {
            if ($file != '..' and $file != '.') {
               if($element["icon"] == $file) {
                  echo "<option value=\"" . $file . "\" selected>" . $file . "</option>";
               }
               else {
                  echo "<option value=\"" . $file . "\">" . $file . "</option>";
               }
            }
         }
      ?>
   </select>
   <input type="submit" value="Choisir cette icône"> ( <a href="marker_icons_list.php" target="blank">liste des icônes</a> )
</form>
</p>

<form enctype="multipart/form-data" method="post">
   <input type="hidden" name="form_id" value="icon_upload">
   <input type="file" name="icon_file">
   <input type="submit" value="Ajouter cette icône">
</form>

<h2>Choix du style (pour les polygones et lignes)</h2>

<p>
   Style actuel:
   <?php if(array_key_exists("style", $element)) {
      echo style_to_string($json_array,$element_id);
   } else {
      echo "pas de style choisi";
   } ?>
</p>

<form method="post">
   <input type="hidden" name="form_id" value="style">
   <input type="input" name="style" value='<?php if(array_key_exists("style", $element)) {
      echo style_to_string($json_array,$element_id);
   } ?>'>
   <input type="submit" value="Modifier">
</form>

<h2>Edition du template</h2> 
<?php 
if(array_key_exists("template", $element)) {
   ?>

   <form method="post" style="margin-bottom:10px">
      Template actuel: <?php echo $element["template"]; ?>
   
      <input type="hidden" name="form_id" value="tempate_delete">
      <input type="submit" value="Supprimer le template">
   </form>

   <form enctype="multipart/form-data" method="post">
      <input type="hidden" name="form_id" value="tempate_update">
      <input type="file" name="webgis_file">
      <input type="submit" value="Mettre à jour le template">
   </form>
   <?php
} else {
   ?>
   <p>
      Il n'y a pas de template.
   </p>

   <form enctype="multipart/form-data" method="post">
      <input type="hidden" name="form_id" value="tempate_update">
      <input type="file" name="webgis_file">
      <input type="submit" value="Ajouter ce template">
   </form>
   <?php
}

display_footer();
?>