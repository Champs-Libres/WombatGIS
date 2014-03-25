/* jslint vars: true */
/*jslint indent: 3 */
/* global L, $, Mustache */
'use strict';

var webgis = function() {
   var map; //the map
   var elements; //contient all the informations about the layers (+ title configuration)

   function alternateDisplayLayer(element_id) {
     /**
      * AlternateDisplay a given layer :
      * if the layer is displayed, this function will undisplay the layer and vice-versa)
      *
      * @param {int} element_id The id of the element (regarding to the order in config.json file)
      */
      if (elements[element_id].displayed) {
         map.removeLayer(elements[element_id].layer);
         $("#title_layer_" + element_id).removeClass("layer_title_selected");
      }
      else {
         addGeojsonLayer(element_id);
         $("#title_layer_" + element_id).addClass("layer_title_selected");
      }
      elements[element_id].displayed = ! elements[element_id].displayed;
   }

   function marker_mouseout_fct(e) {      
      var popup = this.layer._popup;
      var target = e.originalEvent.fromElement || e.originalEvent.relatedTarget;
      //var target = e.toElement || e.relatedTarget;
      if($(target).parent().hasClass('leaflet-popup') 
         || $(target).parent().hasClass('leaflet-popup-tip-container') ) {
         L.DomEvent.addListener(popup._container, 'mouseout', popup_mouseout_fct, {'popup': popup, 'layer': this.layer});
         L.DomEvent.removeListener(this.layer, 'mouseout', marker_mouseout_fct);
      }
      else {
         console.log($(target).parent().attr('class'));
         this.layer.closePopup();
         L.DomEvent.removeListener(this.layer, 'mouseout', marker_mouseout_fct);
         console.log(close);
      }
   }

   function popup_mouseout_fct (e) {
      var target = e.toElement || e.relatedTarget;
      console.log($(target).parent().attr('class'));
      if($(target).parent().hasClass('leaflet-overlay-pane'))
      {
         console.log("popup_mouseout_fct");
         L.DomEvent.removeListener(this.popup._container, 'mouseout', popup_mouseout_fct);
         this.layer.closePopup();
      }
   }



   function addGeojsonLayerWithTC(element_id, template_content) {
     /**
      * Add a geolayer on the map knowing the template content
      *
      * @param {int} element_id The id of the layer (regarding to the order in config.json file)
      * @param {string} template_content The template content (mustache.js). This variable is null
      * if there is no layer
      */
      var leaflet_config = {};
      var element = elements[element_id];

      if(template_content) {
         leaflet_config.onEachFeature = function (feature, layer) {
            var popup_content = Mustache.render(template_content, feature.properties);
            /* click pop-up */
            //layer.bindPopup(popup_content);

            /* hover pop-up */
            layer.on({
               mouseover: function() {
                  this.bindPopup(popup_content).openPopup();
                  L.DomEvent.addListener(this, 'mouseout', marker_mouseout_fct, {'layer': this});
               }
            });
         };
      }

      if('style' in element) {
         leaflet_config.style = element.style;
      }

      
      if ('icon' in element) {
         var executeWhenImgIsLoaded = function() {
            var icon;
            var img = $('#img_'  + element_id);
            var img_height = img.height();
            var img_width = img.width();
            var img_center = Math.floor(img.width() / 2) + 1;
            icon = L.icon({
               iconUrl: 'img/marker/' + element.icon,
               iconSize: [img_width, img_height], // size of the icon
               iconAnchor: [img_center, img_height], // point of the icon which will correspond to marker's location 
               popupAnchor: [0, 10 - img_height]
            });

            leaflet_config.pointToLayer = function (feature, latlng) {
               return L.marker(latlng, {icon: icon});
            };

            $.get(element.geojson, function(geojson_string) {
               var geojson = JSON.parse(geojson_string);
               elements[element_id].layer = L.geoJson(geojson, leaflet_config).addTo(map);
            });
         };
         
         if($('#img_'  + element_id).length > 0) { //image deja charge
            executeWhenImgIsLoaded();
         }
         else {
            $('#img_loader_container').append('<img id="img_' + element_id + '" src="img/marker/' + element.icon + '"></img>');
            $('#img_'+ element_id).load(executeWhenImgIsLoaded);
         }
      }

      else {
         $.get(element.geojson, function(geojson_string) {
            var geojson = JSON.parse(geojson_string);
            elements[element_id].layer = L.geoJson(geojson, leaflet_config).addTo(map);
         });
      }

   }

   function addGeojsonLayer(element_id) {
     /**
      * Add a geojson layer on a map
      *
      * @param {json} the id of the layer (cf order in the config.json file)
      */

      var element = elements[element_id];
      if ('template' in element) { //pop-up qd clique
         $.get(element.template, function(template_content) {
            addGeojsonLayerWithTC(element_id, template_content);
         });
      } else {
         addGeojsonLayerWithTC(element_id, null);
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

            var hillshade_layer = new L.tileLayer('./tiles/hillshade/{z}/{x}/{y}.png', {
               minZoom: 8,
               maxZoom: 14,
               tms: true
            });

            map.addLayer(google_layer);
            map.addLayer(osm_layer);
            map.addLayer(hillshade_layer);

            map.addControl(
               new L.Control.Layers({
                  'Open Street Map':osm_layer,
                  'Google Satelite': google_layer
               },{'Relief' : hillshade_layer },{ position: 'topleft' }));

            new L.Control.Zoom({ position: 'topright' }).addTo(map);

            elements = config.elements;

            for (i = 0, max_i = elements.length;  i < max_i; i = i +1) {
               elements[i].displayed = true;

               if(elements[i].geojson) {
                  addGeojsonLayer(i);
                  if(elements[i].menu_title) { //couche geojson
                     if('icon' in elements[i]) {
                        $('#map_menu').append('<div class="layer_title layer_title_selected" id="title_layer_' + i + '"><div class="layer_icon"><img id="title_layer_' + i + '_icon" src="img/marker/' + elements[i].icon + '" style="margin:auto" /></div><div>' + elements[i].menu_title + '</div></div>');
                     }
                     else if ('style' in elements[i])  {
                        $('#map_menu').append('<div class="layer_title layer_title_selected" id="title_layer_' + i + '"><div class="layer_icon"><div id="title_layer_' + i + '_icon" class="colored_line" style="background-color:' + elements[i].style.color + ';"></div></div><div>'+ elements[i].menu_title + '</div></div>');
                           /*   background-color: #DDDDDD; border: 3px solid #000000; opacity: 0.1; */
                     } else {
                        $('#map_menu').append('<div class="layer_title layer_title_selected" id="title_layer_' + i + '"><div class="layer_icon"><div id="title_layer_' + i + '_icon"></div></div><div>'+ elements[i].menu_title + '</div></div>');
                     }
                     (function (i) {
                        $('#title_layer_' + i).click( function() {
                           alternateDisplayLayer(i);
                        });
                     }) (i);
                  }
               } else if(elements[i].menu_title) { // title
                  $('#map_menu').append('<div class="layer_title layer_head">'+ elements[i].menu_title + '</div>');
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
