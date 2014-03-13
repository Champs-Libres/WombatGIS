/* jslint vars: true */
/*jslint indent: 3 */
/* global L, $, Mustache */
'use strict';

var webgis = function() {
   var map; //the map
   var layers_config_array; //contient all the informations about the layers

   function alternateDisplayLayer(layer_id) {
     /**
      * AlternateDisplay a given layer :
      * if the layer is displayed, this function will undisplay the layer and vice-versa)
      *
      * @param {int} layer_id The id of the layer (regarding to the order in config.json file)
      */
      if (layers_config_array[layer_id].displayed) {
         map.removeLayer(layers_config_array[layer_id].layer);
      }
      else {
         addGeojsonLayer(layer_id);
      }
      layers_config_array[layer_id].displayed = ! layers_config_array[layer_id].displayed;
   }

   function addGeojsonLayerWithTC(layer_id, template_content) {
     /**
      * Add a geolayer on the map knowing the template content
      *
      * @param {int} layer_id The id of the layer (regarding to the order in config.json file)
      * @param {string} template_content The template content (mustache.js). This variable is null
      * if there is no layer
      */
      var leaflet_config = {};
      var layer_config = layers_config_array[layer_id];

      if(template_content) {
         leaflet_config.onEachFeature = function (feature, layer) {
            var popup_content =  Mustache.render(template_content, feature.properties);
            /* click pop-up */
            /* 
            layer.bindPopup(popup_content);
            */

            /* hover pop-up */
            layer.on({
               mouseover: function() {
                  this.bindPopup(popup_content).openPopup();
               }
            });
         };
      }

      if('style' in layer_config) {
         leaflet_config.style = layer_config.style;
      }

      
      if ('icon' in layer_config) {
         var executeWhenImgIsLoaded = function() {
            var icon;
            var img = $('#img_'  + layer_id);
            var img_height = img.height();
            var img_width = img.width();
            var img_center = Math.floor(img.width() / 2) + 1;
            icon = L.icon({
               iconUrl: 'img/marker/' + layer_config.icon,
               iconSize: [img_width, img_height], // size of the icon
               iconAnchor: [img_center, img_height], // point of the icon which will correspond to marker's location 
               popupAnchor: [0, 10 - img_height]
            });

            leaflet_config.pointToLayer = function (feature, latlng) {
               return L.marker(latlng, {icon: icon});
            };

            $.get(layer_config.geojson, function(geojson_string) {
               var geojson = JSON.parse(geojson_string);
               layers_config_array[layer_id].layer = L.geoJson(geojson, leaflet_config).addTo(map);
            });
         };
         
         if($('#img_'  + layer_id).length > 0) { //image deja charge
            executeWhenImgIsLoaded();
         }
         else {
            $('#img_loader_container').append('<img id="img_' + layer_id + '" src="img/marker/' + layer_config.icon + '"></img>');
            $('#img_'+ layer_id).load(executeWhenImgIsLoaded);
         }
      }

      else {
         $.get(layer_config.geojson, function(geojson_string) {
            var geojson = JSON.parse(geojson_string);
            layers_config_array[layer_id].layer = L.geoJson(geojson, leaflet_config).addTo(map);
         });
      }

   }

   function addGeojsonLayer(layer_id) {
     /**
      * Add a geojson layer on a map
      *
      * @param {json} the id of the layer (cf order in the config.json file)
      */

      var layer_config = layers_config_array[layer_id];
      if ('template' in layer_config) { //pop-up qd clique
         $.get(layer_config.template, function(template_content) {
            addGeojsonLayerWithTC(layer_id, template_content);
         });
      } else {
         addGeojsonLayerWithTC(layer_id, null);
      }
   }

   function init() {
     /**
      * Create the WegGIS
      */

      $(document).ready(function() {
         $.get('data/config.json', function(config) {
            var google_layer, osm_layer;
            var i, max_i;

            map = new L.map('map', {
               zoomControl: false,
            }).setView([config.map_center_lon, config.map_center_lat], config.map_zoom_level);

            google_layer =  new L.Google('SATELLITE');

            //http://c.tile.openstreetmap.fr/hot/15/16891/11086.png
            osm_layer = new L.tileLayer('http://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
               attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
               maxZoom: 18,
            });

            map.addLayer(google_layer);
            map.addLayer(osm_layer);

            map.addControl(new L.Control.Layers({'Open Street Map':osm_layer, 'Google Satelite': google_layer},{},{ position: 'topleft' }));

            new L.Control.Zoom({ position: 'topright' }).addTo(map);

            layers_config_array = config.layers_config;

            for (i = 0, max_i = layers_config_array.length;  i < max_i; i = i +1) {
               layers_config_array[i].displayed = true;

               if(layers_config_array[i].geojson) {
                  addGeojsonLayer(i);
                  if(layers_config_array[i].menuTitle) { //couche geojson
                     if('icon' in layers_config_array[i]) {
                        $('#map_menu').append('<div class="layer_title" id="title_layer_' + i + '"><div class="img"><img id="title_layer_' + i + '_img" src="img/marker/' + layers_config_array[i].icon + '" style="margin:auto" /></div><div>' + layers_config_array[i].menuTitle + '</div></div>');
                     }
                     else if ('style' in layers_config_array[i])  {
                        $('#map_menu').append('<div class="layer_title" id="title_layer_' + i + '"><div class="img"><div id="title_layer_' + i + '_img" class="colored_round" style="background-color:' + layers_config_array[i].style.color + ';"></div></div><div>'+ layers_config_array[i].menuTitle + '</div></div>');
                           /*   background-color: #DDDDDD; border: 3px solid #000000; opacity: 0.1; */
                     } else {
                        $('#map_menu').append('<div class="layer_title" id="title_layer_' + i + '"><div class="img"><div id="title_layer_' + i + '_img"></div></div><div>'+ layers_config_array[i].menuTitle + '</div></div>');
                     }
                     (function (i) {
                        $('#title_layer_' + i).click( function() {
                           alternateDisplayLayer(i);
                        });
                     }) (i);
                  }
               } else { // title
                  $('#map_menu').append('<div class="layer_title layer_head">'+ layers_config_array[i].menuTitle + '</div>');
               }
            }
         });
      });
   }

   return {
      init: init
   };
} ();

webgis.init();
