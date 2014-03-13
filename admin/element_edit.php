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
display_header("Mise à jour d'une couche");

if(isset($_POST["form_id"])) {
   if($_POST["form_id"] == "title") {
      $json_array = json_array_edit_layer_field($json_array, $layer_id, "title", $_POST["title"]);
      $message = "Title mis à jour";
   } elseif($_POST["form_id"] == "icon") {
      if($_POST["icon"] == "") {
         $json_array = json_array_delete_layer_field($json_array, $layer_id, "icon");
         $message = "Icône supprimée";
      }
      else {
         $json_array = json_array_edit_layer_field($json_array, $layer_id, "icon", $_POST["icon"]);
         $message = "Icône mise à jour";
      }
   } elseif($_POST["form_id"] == "color") {
      $json_array = json_array_edit_layer_field($json_array, $layer_id, "color", $_POST["color"]);
      $message = "Couleur mise à jour";
   } elseif($_POST["form_id"] == "icon_upload") {
      $icon_name = updload("icon_file", "../img/marker");
      $json_array = json_array_edit_layer_field($json_array, $layer_id, "icon", $icon_name);
      $message = "Icône downloadée et ajoutée";
   } elseif ($_POST["form_id"] == "tempate_delete") {
      $json_array = json_array_delete_layer_field($json_array, $layer_id, "template");
      $message = "Template supprimé";
   } elseif ($_POST["form_id"] == "tempate_update") {
      $json_array = upload_file_and_json_array_update($json_array,$layer_id,"template");
      $message = "Template mis à jour";
   } elseif ($_POST["form_id"] == "geojson_update") {
      $json_array = upload_file_and_json_array_update($json_array,$layer_id,"geojson");
      $message = "GeoJSON mis à jour";
   }
   json_array_save($json_array);
   echo "<div class=\"message success\">" . $message . "</div>";
}
   
$layer = $json_array["layers"][$layer_id];
?>
<h2>Edition du titre</h2> 

<p>
   Titre actuel: <?php echo $layer["title"];  ?>
</p>

<form method="post">
   <input type="hidden" name="form_id" value="title">
   <input type="input" name="title" value="<?php echo $layer["title"];  ?>">
   <input type="submit" value="Modifier">
</form>

<h2>Edition du GeoJSON</h2> 

<p>
   GeoJSON actucel: <?php echo $layer["geojson"]; ?>
</p>

   <form enctype="multipart/form-data" method="post">
      <input type="hidden" name="form_id" value="geojson_update">
      <input type="file" name="webgis_file">
      <input type="submit" value="Mettre à jour le geojson">
   </form>

<h2>Choix de l'icône des marqueurs (pour les points)</h2>

<p>
   Icône actuelle :
   <?php if(array_key_exists("icon", $layer)) {
      echo $layer["icon"]; 
   } else {
      echo "pas d'icone";
   } ?>
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
               if($layer["icon"] == $file) {
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

<h2>Choix de la couleur de fond (pour les polygones)</h2>

<p>
   Couleur actuelle:
   <?php if(array_key_exists("color", $layer) and $layer["color"] != "") {
      echo $layer["color"]; 
   } else {
      echo "pas de couleur choisie";
   } ?>
</p>

<form method="post">
   <input type="hidden" name="form_id" value="color">
   <input type="input" name="color" value="<?php if(array_key_exists("color", $layer)) { echo $layer["color"]; }  ?>">
   <input type="submit" value="Modifier">
</form>

<h2>Edition du template</h2> 
<?php 
if(array_key_exists("template", $layer)) {
   ?>

   <form method="post" style="margin-bottom:10px">
      Template actuel: <?php echo $layer["template"]; ?>
   
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