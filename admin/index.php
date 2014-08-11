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

<h2>Elements et ordre d'affichage</h2>
<center>
<table width="100%">
   <tr>
      <th>#</th>
      <th>Titre 
         <br />(vide : pas dans le menu)</th>
      <th>GeoJson
         <br />(vide : titre dans le menu)</th>
      <th>Affichage au chargement<br />
         (0 : affiché, 1 : pas affiché)</th>
      <th>Template</th>
      <th></th>
      <th></th>
   </tr>
<?php
for($i = 0, $size = count($json_array["elements"]); $i < $size; ++$i) {
   echo "<tr>" . PHP_EOL;
   echo "<td>" . $i . "</td>" . PHP_EOL; 
   display_td_element_field($json_array, $i, "menu_title", "[vide]");
   display_td_element_field($json_array, $i, "geojson", "[vide]");
   display_td_element_field($json_array, $i, "at_start_not_displayed", "0 [affiché]");
   display_td_element_field($json_array, $i, "template", "[pas de template]");
   echo "<td><a href=\"element_edit.php?id={$i}\">Modifier</a></td>" . PHP_EOL;
   echo "<td><a href=\"element_delete.php?id={$i}\">Supprimer</a></td>" . PHP_EOL;
   echo "</tr>" . PHP_EOL;
}
?>
</table>
</center>

<p>
   <a href="element_add.php">Ajouter un élément</a>
</p>

<p>
   <a href="order_edit.php">Changer l'ordre d'affichage</a>
</p>

<?php
display_footer(false);
?>