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
<center>
<table width="100%">
<?php
for($i = 0, $size = count($json_array["elements"]); $i < $size; ++$i) {
   echo "<tr>" . PHP_EOL;
   echo "<td>" . $i . "</td>" . PHP_EOL; 
   display_td_element_field($json_array, $i, "menuTitle", "[ne se trouve pas dans le menu (pas de titre)]");
   display_td_element_field($json_array, $i, "geojson", "[titre du menu (pas de geojson)]");
   display_td_element_field($json_array, $i, "template", "[pas de template]");
   echo "<td><a href=\"layer_edit.php?id={$i}\">Modifier la couche</a></td>" . PHP_EOL;
   echo "</tr>" . PHP_EOL;
}
?>
</table>
</center>

<p>
   <a href="order_edit.php">Changer l'ordre d'affichage</a>
</p>

<?php
display_footer(false);
?>