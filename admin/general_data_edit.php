<?php 

/*
 * Webpage used for editing general data of the webgis config file
 *
 * (c) Champs Libres COOP <info@champs-libres.coop>
 */

require('data_fct.php');
require('display_fct.php');
$json_array = json_array_get();
display_header("Mise à jour des données générales");
?>

<?php
if(isset($_POST["lon"])) {
   $json_array = json_array_edit_general($json_array, $_POST["lon"], $_POST["lat"]);
   json_array_save($json_array);
   echo "<div class=\"message success\">Les données ont été mises à jour.</div>";
}
?>

<form action="" method="post">
Longitude : <input type="input" name="lon" value="<?php echo $json_array["map_center_lon"];  ?>"><br>
Latitude : <input type="input" name="lat" value="<?php echo $json_array["map_center_lat"];  ?>"><br>
<input type="submit" value="Editer">
</form>

<?php
display_footer();
?>
