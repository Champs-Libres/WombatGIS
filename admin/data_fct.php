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
 * The order of the layers list ( $data["elements"] ) is very important : it is used for as
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
   $file_saved = fwrite($json_file, json_encode($json_array,JSON_UNESCAPED_UNICODE));
   fclose($json_file);
   if(! $file_saved)
   {
      echo "<div class=\"message error\">Problème durant la sauvegarde du fichier</div>";
      die();
   }
}

/**
 * Edit the global data used for the web GIS :
 * - latitude of the center of the map
 * - longitude of the center of the map
 * - zoom level of the map
 *
 * @param array $json_array The json array
 * @param float $lon The new longitude
 * @param float $lat The new latitude
 * @param int $zoom The new zoom level
 */
function json_array_edit_general($json_array, $lon, $lat, $zoom) {
   if($lon != "") {
      $json_array["map_center_lon"] = $lon;
   }

   if($lon != "") {
      $json_array["map_center_lat"] = $lat;
   }

   if($zoom != "") {
      $json_array["map_zoom_level"] = $zoom;
   }
   return $json_array;
}

/**
 * Edit the data of a given element of the WebGIS. The data can be :
 * - the title of the layer
 * - the path to the geojson
 * - the path to the template (this data is optional)
 *
 * @param array $json_array The json array
 * @param int $element_id The id of the element to edit
 * @param string $field_name The name of the field to edit ("title" for the title,
 * "geojson" for the path to the geojson and "template" for the path to the template )
 * @param string $field_value The new value of the field
 */
function json_array_edit_element_field($json_array, $element_id, $field_name, $field_value) {
   $json_array["elements"][$element_id][$field_name] = $field_value;
   return $json_array;
}

/**
 * Delete a field of the description of an element of the WebGIS.
 *
 * @param array $json_array The json array
 * @param int $element_id The id of the layer to edit
 * @param string $field_name The name of the field to delete ("template" for the path
 * to the template ("title" and "geojson" are not optional))
 */
function json_array_delete_element_field($json_array, $element_id, $field_name) {
   unset($json_array["elements"][$element_id][$field_name]);
   return $json_array;
}

/**
 * Get a field of the description of an element of the WebGIS.
 *
 * @param array $json_array The json array
 * @param int $element_id The id of the layer to edit
 * @param string $field_name The name of the field to get
 * @param string $no_field_response The the respose if the element has not the field.
 */
function json_array_get_element_field($json_array, $element_id, $field_name, $no_field_response) {
   if (array_key_exists($field_name, $json_array["elements"][$element_id]) && $json_array["elements"][$element_id][$field_name] != "") {
      return $json_array["elements"][$element_id][$field_name];
   }
   else {
      return $no_field_response;
   }
}

/**
 * Pop an element of the WebGIS. This order is very important : it
 * used as the display order by the WebGIS.
 */
function json_array_pop_element($json_array, $element_id) {
   $elements = $json_array["elements"];
   $elements_nbr = count($elements);

   $elements_after_id = array_splice($elements, $element_id);
   $popep_element = array_shift($elements_after_id);
   $elements_without_id_row = array_merge($elements, $elements_after_id);

   $json_array["elements"] = $elements_without_id_row;
   return [$json_array, $popep_element];
}

/**
 * Add an element of the WebGIS. This order is very important : it
 * used as the display order by the WebGIS.
 */
function json_array_add_element($json_array) {
   $elements = $json_array["elements"];
   array_push($elements, json_decode("{}"));
   $json_array["elements"] = $elements;
   return $json_array;
}

/**
 * Edit the order of elements of the WebGIS. This order is very important : it
 * used as the display order by the WebGIS (for the layers and the menu).
 *
 * @param array $json_array The json array
 * @param int $element_id The id of the layer that has to be moved
 * @param int $new_element_id The new position of the layer in the layers table
 */
function json_array_edit_elements_position($json_array, $element_id, $new_element_id) {
   $layers = $json_array["elements"];
   $layers_nbr = count($layers);
   if($element_id < 0 || $element_id >= $layers_nbr) {
      echo "Id (" . $element_id . ") not valid : must between 0 and " . ($layers_nbr - 1);
      die();
   }
   if($new_element_id < 0 || $new_element_id >= $layers_nbr) {
      echo "New id (" . $new_element_id . ") not valid : must between 0 and " . ($layers_nbr - 1);
      die();
   }

   $layers_after_row_to_deplace = array_splice($layers, $element_id);
   $row_to_deplace = array_shift($layers_after_row_to_deplace);
   $layers_without_row = array_merge($layers, $layers_after_row_to_deplace);

   $layers_after_row_to_place = array_splice($layers_without_row, $new_element_id);
   array_unshift($layers_after_row_to_place, $row_to_deplace);
   $new_layers = array_merge($layers_without_row, $layers_after_row_to_place);

   $json_array["elements"] = $new_layers;
   return $json_array;
}


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
 * Saves an (post) uploaded file to a certain directory and returns the name of
 * the file
 *
 * @param string $input_name The name used for the file input of the form
 * @param string $destination The directory where to add the file (without / at the end).
 * @return string The file name
 */
function updload($input_name, $destination) {
   if (is_uploaded_file($_FILES[$input_name]['tmp_name'])){
      $file_name = $_FILES[$input_name]['name'];
      $dotdot_destination = $destination . "/" . $file_name;
      if (move_uploaded_file($_FILES[$input_name]['tmp_name'], $dotdot_destination)) {
         return $file_name;
      }
      else {
         echo "<div class=\"message error\">Problème durant l'upload (1)</div>";
   die();
      }
   }
   echo "<div class=\"message error\">Problème durant l'upload (2)</div>";
   die();
}

/**
 * Saves an (post) uploaded file to the data directory and update the json array
 *
 * @param array $json_array The json array
 * @param int $element_id The id of the layer that has to be moved
 * @param string $field The field that will contain the path to the file uploaded
 */
function upload_file_and_json_array_update($json_array,$element_id,$field) {
   $new_file = 'data/' . updload('webgis_file', '../data');
   return json_array_edit_element_field($json_array, $element_id, $field, $new_file);
}

/**
 * Saves an (post) uploaded file to the data directory and update the json array
 *
 * @param array $json_array The json array
 * @param int $element_id The id of the layer that has to be moved
 * @param string $field The field that will contain the path to the file uploaded
 */
function style_to_string($json_array,$element_id) {
   if (array_key_exists("style", $json_array["elements"][$element_id])) {
      return  json_encode($json_array["elements"][$element_id]["style"],JSON_UNESCAPED_UNICODE);
   }
   else {
      return "";
   }
}
?>