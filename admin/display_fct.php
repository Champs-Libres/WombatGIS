<?php

/*
 * This file contains a set of function used for generating the admin html
 *
 * (c) Champs Libres COOP <info@champs-libres.coop>
 */


/**
 * Generate the header of the html page.
 *
 * @param string $tithe The title of the page
 * @param boolean $display_index_link True if display a return link to the index.php page
 */
function display_header($title, $display_index_link=true) {
   ?>
<!DOCTYPE html>
<html>
<head>
   <title>WebGIS - <?php echo $title; ?></title>
   <meta charset="utf-8" />
   <link rel="stylesheet" href="css/main.css" />
</head>
<body>
   <?php
   if($display_index_link) {
      echo "<p><a href=\"index.php\">Retour à l'accueil</a></p>";
   }
?>
<h1><?php echo $title; ?></h1>
<?php
}

/**
 * Generate the footer of the html page.
 *
 * @param boolean $display_index_link True if display a return link to the index.php page
 */
function display_footer($display_index_link=true) {
   if($display_index_link) {
      echo "<p><a href=\"index.php\">Retour à l'accueil</a></p>";
   }
   ?>
</body>
</html>
   <?php
}


/**
 *
 */
function display_element_field($json_array, $element_id, $field_name, $no_field_message) {
   echo json_array_get_element_field($json_array, $element_id, $field_name, $no_field_message);
}

/**
 *
 */
function display_td_element_field($json_array, $element_id, $field_name, $no_field_message) {
   echo "<td>";
   display_element_field($json_array, $element_id, $field_name, $no_field_message);
   echo "</td>" . PHP_EOL;
}
?>