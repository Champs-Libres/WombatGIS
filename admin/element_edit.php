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
   if($_POST["form_id"] == "menu_title") {
      $json_array = json_array_edit_element_field($json_array, $element_id, "menu_title", $_POST["menu_title"]);
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
   elseif($_POST["form_id"] == "icon_shadow") {
      if($_POST["icon_shadow"] == "") {
         $json_array = json_array_delete_element_field($json_array, $element_id, "icon_shadow");
         $message = "Ombre supprimée";
      }
      else {
         $json_array = json_array_edit_element_field($json_array, $element_id, "icon_shadow", $_POST["icon_shadow"]);
         $message = "Ombre mise à jour";
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
   } elseif ($_POST["form_id"] == "geojson_delete") {
      $json_array = json_array_delete_element_field($json_array, $element_id, "geojson");
      $message = "GeoJSON supprimé";
   }
   json_array_save($json_array);
   echo "<div class=\"message success\">" . $message . "</div>";
}
   
$element = $json_array["elements"][$element_id];
?>
<h2>Edition du titre</h2> 

<p>
   Titre actuel:  
   <?php display_element_field($json_array, $element_id, "menu_title", "pas de titre (l'élément n'est pas affiché dans le menu)"); ?>
</p>

<form method="post">
   <input type="hidden" name="form_id" value="menu_title">
   <input type="input" name="menu_title" value="<?php echo json_array_get_element_field($json_array,$element_id,"menu_title","");  ?>">
   <input type="submit" value="Modifier"> (pour supprimer laisser ce champ vide)
</form>

<h2>Edition du GeoJSON</h2> 

<?php 
if(array_key_exists("geojson", $element)) {
   ?>

   <form method="post" style="margin-bottom:10px">
      GeoJSON actuel: <?php echo $element["geojson"]; ?>
   
      <input type="hidden" name="form_id" value="geojson_delete">
      <input type="submit" value="Supprimer le GeoJSON">
   </form>

   <form enctype="multipart/form-data" method="post">
      <input type="hidden" name="form_id" value="geojson_update">
      <input type="file" name="webgis_file">
      <input type="submit" value="Mettre à jour le GeoJSON">
   </form>
   <?php
} else {
   ?>
   <p>
      Il n'y a pas de GeoJSON.
   </p>

   <form enctype="multipart/form-data" method="post">
      <input type="hidden" name="form_id" value="geojson_update">
      <input type="file" name="webgis_file">
      <input type="submit" value="Ajouter ce GeoJSON">
   </form>
   <?php
} ?>

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
            if ($file != '..' and $file != '.' and $file != 'README.md') {
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

<p>
<form method="post">
   <input type="hidden" name="form_id" value="icon_shadow">
   <select name="icon_shadow">
      <option value="">Pas d'ombre</option>
      <?php
         $dir = "../img/marker";
         $files = scandir($dir);
         foreach ($files as $file) {
            if ($file != '..' and $file != '.' and $file != 'README.md') {
               echo $element["icon_shadow"];
               if($element["icon_shadow"] == $file) {
                  echo "<option value=\"" . $file . "\" selected>" . $file . "</option>";
               }
               else {
                  echo "<option value=\"" . $file . "\">" . $file . "</option>";
               }
            }
         }
      ?>
   </select>
   <input type="submit" value="Choisir cette ombre"> ( <a href="marker_icons_list.php" target="blank">liste des ombres</a> )
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