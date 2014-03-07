/* jslint vars: true */
/*jslint indent: 3 */
/* global L, $, Mustache */
"use strict";

var webgis_layers;
var map, osm_layer, google_layer;

function display_layer(map,id) {
   if (webgis_layers[id].displayed) {
      map.removeLayer(webgis_layers[id].layer);
      $("#title_layer_" + id + "_img").hide();
   }
   else {
      add_geojson_layer(map, webgis_layers[id]);
      $("#title_layer_" + id + "_img").show();
   }
   webgis_layers[id].displayed = ! webgis_layers[id].displayed;
}

function add_geojson_layer(map, layer_config) {
   $.get(layer_config.geojson, function(geojson_string) {
      var geojson = JSON.parse(geojson_string);
      if ("icon" in layer_config) {
         var execute_when_img_load = function() {
            console.log("dsklksdk");
            var icon;
            var img = $("#img_"  + layer_config.id);
            var img_height = img.height();
            var img_width = img.width();
            var img_center = Math.floor(img.width() / 2) + 1;
            icon = L.icon({
               iconUrl: "img/marker/" + layer_config.icon,
               iconSize: [img_width, img_height], // size of the icon
               iconAnchor: [img_center, img_height], // point of the icon which will correspond to marker's location 
               popupAnchor: [0, 10 - img_height]
            });
            $.get(layer_config.template, function(template_content) {
               var pointToLayer = function (feature, latlng) {
                  return L.marker(latlng, {icon: icon});
               };
               var onEachFeature = function (feature, layer) {
                  var popupContent =  Mustache.render(template_content, feature.properties);
                  layer.bindPopup(popupContent);
               };
               webgis_layers[layer_config.id].layer = L.geoJson(geojson, { pointToLayer:pointToLayer,onEachFeature:onEachFeature}).addTo(map);
            });
         };
         console.log("-- 1");
         if($("#img_"  + layer_config.id).length > 0) { //image deja charge
            console.log("-- 2");
            execute_when_img_load(); 
         } 
         else {
            console.log("-- 3");
            console.log("blop");
            $("#img_loader_container").append("<img id=\"img_" + layer_config.id + "\" src=\"img/marker/" + layer_config.icon + "\"></img>");
            $("#img_"+ layer_config.id).load(execute_when_img_load);

         }
      } else {
         var style = function () {
            return {color: layer_config.color};
         };
         webgis_layers[layer_config.id].layer = L.geoJson(geojson, {style: style}).addTo(map);
      }
      webgis_layers[layer_config.id].displayed = true;
   });
}

$(document).ready(function() {
   $.get("data/config.json", function(config) {
      var i, max_i;

      map = new L.map("map", {
         zoomControl: false,
      }).setView([config.map_center_lon, config.map_center_lat], config.map_zoom_level);

      google_layer =  new L.Google("SATELLITE");

      //http://c.tile.openstreetmap.fr/hot/15/16891/11086.png
      osm_layer = new L.tileLayer("http://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png", {
         attribution: "Map data &copy; <a href='http://openstreetmap.org'>OpenStreetMap</a> contributors, <a href='http://creativecommons.org/licenses/by-sa/2.0/'>CC-BY-SA</a>",
         maxZoom: 18,
      });

      map.addLayer(google_layer);
      map.addLayer(osm_layer);

      map.addControl(new L.Control.Layers({"Open Street Map":osm_layer, "Google Satelite": google_layer},{},{ position: "topleft" }));

      new L.Control.Zoom({ position: "topright" }).addTo(map);

      webgis_layers = config.layers;

      for (i = 0, max_i = webgis_layers.length;  i < max_i; i = i +1) {
         webgis_layers[i].displayed = true;
         webgis_layers[i].id = i;

         (function(i) {
            add_geojson_layer(map, config.layers[i]);
               if("icon" in config.layers[i]) {
                  $("#map_menu").append("<div class=\"layer_title\" id='title_layer_" + i + "'><div class=\"img\"><img id='title_layer_" + i + "_img' src=\"img/marker/" + config.layers[i].icon + "\" style=\"margin:auto\" /></div><div>" + config.layers[i].title + "</div></div>");
               }
               else {
                  $("#map_menu").append("<div class=\"layer_title\" id='title_layer_" + i + "'><div class='img'><div id='title_layer_" + i + "_img' class=\"colored_round\" style='background-color:" + config.layers[i].color + ";'></div></div><div>"+ config.layers[i].title + "</div></div>");
               }
            $("#title_layer_" + i).click( function() {
               display_layer(map,i);
            });
         }) (i);
      }
   });
});