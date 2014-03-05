/* jslint vars: true */
/*jslint indent: 3 */
/* global L, $, Mustache */
"use strict";

var webgis_layers;

function display_layer(map,id) {
   if (webgis_layers[id].displayed) {
      map.removeLayer(webgis_layers[id].layer);
   }
   else {
      add_geojson_layer(map, webgis_layers[id]);
   }
   webgis_layers[id].displayed = ! webgis_layers[id].displayed;
}

function add_geojson_layer(map, layer_config) {
   $.get(layer_config.geojson, function(geojson_string) {
      var geojson = JSON.parse(geojson_string);
      if ("template" in layer_config) {
         $.get(layer_config.template, function(template_content) {
            var onEachFeature = function (feature, layer) {
               var popupContent =  Mustache.render(template_content, feature.properties);
               layer.bindPopup(popupContent);
            };
            webgis_layers[layer_config.id].layer = L.geoJson(geojson, {onEachFeature:onEachFeature}).addTo(map);
         });
      } else {
         webgis_layers[layer_config.id].layer = L.geoJson(geojson).addTo(map);
      }
      webgis_layers[layer_config.id].displayed = true;
   });
}

$(document).ready(function() {
   $.get("data/config.json", function(config) {
      var map, osm_layer, base_layer, google_layer, i, max_i;

      map = new L.map("map", {
         zoomControl: false,
      }).setView([config.map_center_lon, config.map_center_lat], 10);

      google_layer =  new L.Google('SATELLITE');

      osm_layer = new L.tileLayer("http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
         attribution: "Map data &copy; <a href='http://openstreetmap.org'>OpenStreetMap</a> contributors, <a href='http://creativecommons.org/licenses/by-sa/2.0/'>CC-BY-SA</a>",
         maxZoom: 18,
      });

      map.addLayer(google_layer);
      map.addLayer(osm_layer);

      map.addControl(new L.Control.Layers({'Open Street Map':osm_layer, 'Google Satelite': google_layer},{},{ position: "topleft" }))

      new L.Control.Zoom({ position: "topright" }).addTo(map);

      webgis_layers = config.layers;

      for (i = 0, max_i = webgis_layers.length;  i < max_i; i = i +1) {
         webgis_layers[i].displayed = true;
         webgis_layers[i].id = i;

         (function(i) {
            add_geojson_layer(map, config.layers[i]);
            $("#map_menu").append("<h2 id='title_layer_" + i + "'>" + config.layers[i].title + "</h2>");
            $("#title_layer_" + i).click( function() {
               display_layer(map,i);
            });
         }) (i);
      }      
   });
});