<?php

/*
 * This file contains a set of function used editing the data
 * of the WebGis ( ../data/config.json & upload file into ../data/ )
 *
 * (c) Champs Libres COOP <info@champs-libres.coop>
 */


/**
 * Return the data from the file ../data/config.json in an array
 *
 * The order of the layers list ( $data["layers"] ) is very important : it is used for as 
 * id for the edition.
 */
function json_array_get() {
   $json_file = fopen("../data/config.json", "r");
   $json_data = fread($json_file, filesize("../data/config.json"));
   fclose($json_file);
   $data = json_decode($json_data,true);
   return $data;
}

/**
 * Save the array that represent the json into the file ../data/config.json
 *
 * @param array $json_array The json array.
 */
function json_array_save($json_array) {
   $json_file = fopen("../data/config.json", "w");
   fwrite($json_file, json_encode($json_array,JSON_UNESCAPED_UNICODE));
   fclose($json_file);
}

/**
 * Edit the global data used for the web GIS : 
 * - latitude of the center of the map
 * - longitude of the center of the map
 *
 * @param array $json_array The json array
 * @param float $lon The new longitude
 * @param float $lat The new latitude
 */
function json_array_edit_general($json_array, $lon, $lat) {
   if($lon != "") {
      $json_array["map_center_lon"] = $lon;
   }

   if($lon != "") {
      $json_array["map_center_lat"] = $lat;
   }
   return $json_array;
}

/**
 * Edit the data of a given geojson layer displayed in the web GIS. The data can be :
 * - the title of the layer
 * - the path to the geojson
 * - the path to the template (this data is optional)
 *
 * @param array $json_array The json array
 * @param int $layer_id The id of the layer to edit
 * @param string $field_name The name of the field to edit ("title" for the title,
 * "geojson" for the path to the geojson and "template" for the path to the template )
 * @param string $field_value The new value of the field
 */
function json_array_edit_layer_field($json_array, $layer_id, $field_name, $field_value) {
   $json_array["layers"][$layer_id][$field_name] = $field_value;
   return $json_array;
}

/**
 * Delete a field of the description of a given geojson layer displayed in the web GIS.
 *
 * @param array $json_array The json array
 * @param int $layer_id The id of the layer to edit
 * @param string $field_name The name of the field to delete ("template" for the path 
 * to the template ("title" and "geojson" are not optional))
 */
function json_array_delete_layer_field($json_array, $layer_id, $field_name) {
   unset($json_array["layers"][$layer_id][$field_name]);
   return $json_array;
}

/**
 * Edit the order of the layers table of the web GIS. This order is very important : it
 * used as the display order by the WebGIS.
 *
 * @param array $json_array The json array
 * @param int $layer_id The id of the layer that has to be moved
 * @param int $new_layer_id The new position of the layer in the layers table
 */
function json_array_edit_layer_position($json_array, $layer_id, $new_layer_id) {
   $layers = $json_array["layers"];
   $layers_nbr = count($layers);
   if($layer_id < 0 || $layer_id >= $layers_nbr) {
      echo "Id (" . $layer_id . ") not valid : must between 0 and " . ($layers_nbr - 1);
      die();
   }
   if($new_layer_id < 0 || $new_layer_id >= $layers_nbr) {
      echo "New id (" . $new_layer_id . ") not valid : must between 0 and " . ($layers_nbr - 1);
      die();
   }

   $layers_after_row_to_deplace = array_splice($layers, $layer_id);
   $row_to_deplace = array_shift($layers_after_row_to_deplace);
   $layers_without_row = array_merge($layers, $layers_after_row_to_deplace);

   $layers_after_row_to_place = array_splice($layers_without_row, $new_layer_id);
   array_unshift($layers_after_row_to_place, $row_to_deplace);
   $new_layers = array_merge($layers_without_row, $layers_after_row_to_place);

   $json_array["layers"] = $new_layers;
   return $json_array;
}

/**
 * Saves an (post) uploaded file to the data directory and returns the path (used by
 * the javascipt part) to access to the file.
 *
 * The form used to upload the file may has the file input called "webgis_file"
 *  
 * @return string The path (used by the javascipt part) to access to the file.
 */
function upload() {
   if (is_uploaded_file($_FILES['webgis_file']['tmp_name'])){
      $file_name = $_FILES['webgis_file']['name'];
      $destination = "../data/" . $_FILES['webgis_file']['name'];
      if (move_uploaded_file($_FILES['webgis_file']['tmp_name'], $destination)) {
         return "data/" . $file_name;
      }
   }
   echo "<div class=\"message error\">Problème durant l'upload</div>";
   die();
}

/**
 * Saves an (post) uploaded file to the data directory and update the json array
 *
 * @param array $json_array The json array
 * @param int $layer_id The id of the layer that has to be moved
 * @param string $field The field that will contain the path to the file uploaded
 */
function upload_file_and_json_array_update($json_array,$layer_id,$field) {
   $new_file = upload();
   return json_array_edit_layer_field($json_array, $layer_id, $field, $new_file);
}
?>