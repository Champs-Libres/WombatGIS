<?php

/*
 * Index webpage for editing the webgis config file
 *
 * (c) Champs Libres COOP <info@champs-libres.coop>
 */

require('data_fct.php');
require('display_fct.php');
$json_array = json_array_get();
display_header("Administration",false);
?>

<h2>Données générales</h2>

<p>
   <b>Centre de la carte:</b><br />
   Longitude : <?php echo $json_array["map_center_lon"]; ?><br />
   Latitude : <?php echo $json_array["map_center_lat"]; ?><br />
   Niveau de zoom : <?php echo $json_array["map_zoom_level"]; ?><br />
</p>

<p>
   <a href="general_data_edit.php">Modifier les données générales</a>
</p>

<h2>Couches et ordre d'affichage</h2>
<table width="100%">
   <tr>
      <th>#</th>
      <th>Titre
         <br />(vide : pas dans le menu)</th>
      <th>Geojson
         <br />(vide : titre dans le menu)</th>
      <th>Affichage au chargement</th>
      <th>Template popup</th>
      <th></th>
      <th></th>
   </tr>
<?php
for($i = 0, $size = count($json_array["elements"]); $i < $size; ++$i) {
   echo "<tr>" . PHP_EOL;
   echo "<td>" . $i . "</td>" . PHP_EOL;
   display_td_element_field($json_array, $i, "menu_title", "[vide]");
   display_td_element_field($json_array, $i, "geojson", "[vide]");
   display_td_boolean_field($json_array, $i, "displayed_at_start", "pas affiché");
   display_td_element_field($json_array, $i, "template", "[pas de template]");
   echo "<td><a href=\"element_edit.php?id={$i}\">Modifier</a></td>" . PHP_EOL;
   echo "<td><a href=\"element_delete.php?id={$i}\">Supprimer</a></td>" . PHP_EOL;
   echo "</tr>" . PHP_EOL;
}
?>
</table>

<p>
   <?php
   if(isset($_POST["add"])) { //TODO other condition?
      $json_array = json_array_get();
      $json_array = json_array_add_element($json_array);
      json_array_save($json_array);
      header("Refresh:0");
   }
   ?>
   <form action="" method="post">
     <button name="add">Ajouter un élément</button>
   </form>
</p>

<p>
   <a href="order_edit.php">Changer l'ordre d'affichage</a>
</p>

<?php
display_footer(false);
?>