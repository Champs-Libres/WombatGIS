/* jslint vars: true */
/*jslint indent: 2 */
/* global L, $, Mustache */
"use strict";

var webgis = (function () {
  var map; //the map
  var elements; //holds all the informations about the layers (+ title configuration)

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
    } else {
      addGeojsonLayer(element_id);
      $("#title_layer_" + element_id).addClass("layer_title_selected");
    }
    elements[element_id].displayed = !elements[element_id].displayed;
  }

  function addGeojsonLayerWithTC(element_id, template_content) {
    /**
     * Add a layer on the map knowing the template content
     *
     * @param {int} element_id The id of the layer (regarding to the order in config.json file)
     * @param {string} template_content The template content (mustache.js). This variable is null
     * if there is no layer
     */
    var leaflet_config = {};
    var element = elements[element_id];

    if (template_content) {
      leaflet_config.onEachFeature = function (feature, layer) {
        var popup_content = Mustache.render(
          template_content,
          feature.properties
        );
        $("#popup_content_loader").html(popup_content);
        layer.bindPopup(popup_content);
      };
    }

    if ("style" in element) {
      leaflet_config.style = element.style;
    }

    if ("icon" in element) {
      var executeWhenImgIsLoaded = function () {
        var icon;
        var img = $("#img_" + element_id);
        var img_height = img.height();
        var img_width = img.width();
        var img_center = Math.floor(img.width() / 2) + 1;
        var icon_config = {
          iconUrl: "img/marker/" + element.icon,
          iconSize: [img_width, img_height], // size of the icon
          iconAnchor: [img_center, img_height], // point of the icon which will correspond to marker's location
          popupAnchor: [0, 10 - img_height],
        };

        if ("icon_shadow" in element) {
          icon_config.shadowUrl = "img/marker/" + element.icon_shadow;
        }

        icon = L.icon(icon_config);

        leaflet_config.pointToLayer = function (_feature, latlng) {
          return L.marker(latlng, { icon: icon });
        };

        $.get(element.geojson, function (geojson) {
          elements[element_id].layer = L.geoJson(geojson, leaflet_config).addTo(
            map
          );
        });
      };

      if ($("#img_" + element_id).length > 0) {
        executeWhenImgIsLoaded();
      } else {
        $("#img_loader_container").append(
          '<img id="img_' +
            element_id +
            '" src="img/marker/' +
            element.icon +
            '"></img>'
        );
        $("#img_" + element_id).one("load", executeWhenImgIsLoaded);
      }
    } else {
      $.get(element.geojson, function (geojson) {
        elements[element_id].layer = L.geoJson(geojson, leaflet_config).addTo(
          map
        );
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
    if ("template" in element) {
      $.get(element.template, function (template_content) {
        addGeojsonLayerWithTC(element_id, template_content);
      });
    } else {
      addGeojsonLayerWithTC(element_id, null);
    }
  }

  function layerMenuHeader(css_class, i) {
    return `<div class="${css_class}" id="title_layer_${i}"><div class="layer_icon"><div id="title_layer_${i}_icon"`;
  }

  function init() {
    /**
     * Create the WegGIS
     */

    $(document).ready(function () {
      $.get(
        "data/config.json",
        function (config) {
          var base_layers = {};
          var overlays = {};
          var i, max_i;
          var layer;

          if (document && config.page_title) {
            document.title = config.page_title;
          }

          map = new L.map("map", {
            zoomControl: false,
          }).setView(
            [config.map_center_lat, config.map_center_lon],
            config.map_zoom_level
          );

          for (i = 0, max_i = config.base_layers.length;  i < max_i; i = i +1) {
            layer = null;
            if (config.base_layers[i] === 'SPW photos aériennes') {
               layer = new L.tileLayer.wms('https://geoservices.wallonie.be/arcgis/services/IMAGERIE/ORTHO_LAST/MapServer/WMSServer?', {
                  attribution: '&copy; SPW',
                  layers: '0',
                  format: 'image/png',
                  transparent: true,
               })
            } else if (config.base_layers[i] === 'Basé sur OpenStreetMap') {
               layer = new L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                  attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
                  maxZoom: 18,
               });
            }
            if(layer) {
               map.addLayer(layer);
               base_layers[config.base_layers[i]] = layer;
            }
          }

          if (config.overlays) {
            for (i = 0, max_i = config.overlays.length; i < max_i; i = i + 1) {
              layer = new L.tileLayer(
                config.overlays[i].url,
                config.overlays[i].options
              );
              map.addLayer(layer);
              overlays[config.overlays[i].title] = layer;
            }
          }

          map.addControl(
            new L.Control.Layers(base_layers, overlays, {
              position: "topright",
            })
          );

          new L.Control.Zoom({ position: "topright" }).addTo(map);

          elements = config.elements;

          for (i = 0, max_i = elements.length; i < max_i; i = i + 1) {
            if (elements[i].geojson) {
              var css_class = "layer_title";
              if (elements[i].displayed_at_start) {
                elements[i].displayed = true;
                css_class += " layer_title_selected";
                addGeojsonLayer(i);
              } else {
                elements[i].displayed = false;
              }
              if (elements[i].menu_title) {
                if ("icon" in elements[i]) {
                  $("#panelContent").append(
                    layerMenuHeader(css_class, i) +
                      ' src="img/marker/' +
                      elements[i].icon +
                      '" style="margin:auto" /></div><div>' +
                      elements[i].menu_title +
                      "</div></div>"
                  );
                } else if ("style" in elements[i]) {
                  if (elements[i].style) {
                    if ("fillColor" in elements[i].style) {
                      // Polygon
                      $("#panelContent").append(
                        layerMenuHeader(css_class, i) +
                          ' class="colored_box" style="background-color:' +
                          elements[i].style?.fillColor +
                          ';"></div></div><div>' +
                          elements[i].menu_title +
                          "</div></div>"
                      );
                    } else {
                      // Line
                      $("#panelContent").append(
                        layerMenuHeader(css_class, i) +
                          ' class="colored_line" style="background-color:' +
                          elements[i].style?.color +
                          ';"></div></div><div>' +
                          elements[i].menu_title +
                          "</div></div>"
                      );
                    }
                  } else {
                    // No style
                    $("#panelContent").append(
                      layerMenuHeader(css_class, i) +
                        ' class="colored_box" style="background-color:#3388ff;"></div></div><div>' +
                        elements[i].menu_title +
                        "</div></div>"
                    );
                  }
                  /*   background-color: #DDDDDD;  */
                } else {
                  $("#panelContent").append(
                    '<div class="' +
                      css_class +
                      '" id="title_layer_' +
                      i +
                      '"><div class="layer_icon"><div id="title_layer_' +
                      i +
                      '_icon"></div></div><div>' +
                      elements[i].menu_title +
                      "</div></div>"
                  );
                }
                (function (i) {
                  $("#title_layer_" + i).click(function () {
                    alternateDisplayLayer(i);
                  });
                })(i);
              }
            } else if (elements[i].menu_title) {
              $("#panelContent").append(
                '<div class="layer_title layer_head">' +
                  elements[i].menu_title +
                  "</div>"
              );
            }
          }
        },
        "json"
      );
    });
  }

  return {
    init: init,
  };
})();

webgis.init();

// Hide/show panel function for desktop view. The panel is shown by default.
var showPanel = true;
var collapsePanel = function () {
  if (showPanel === true) {
    $("div#map_menu").css("width", "50px");
    $("div#panelContent").css("opacity", "0");
    $("div#collapseBtn button").text(">");
    showPanel = !showPanel;
  } else {
    $("div#map_menu").css("width", "300px");
    $("div#panelContent").css("opacity", "1");
    $("div#collapseBtn button").text("<");
    showPanel = !showPanel;
  }
};

// Hide/show panel function for mobile view. The panel is not shown by default.
var showPanelXs = false;
var collapsePanelXs = function () {
  if (showPanelXs === true) {
    $("div#map_menu").css("width", "0px");
    $("div#panelContent").css("opacity", "0");
    showPanelXs = !showPanelXs;
  } else {
    $("div#map_menu").css("width", "calc(100% - 45px)");
    $("div#panelContent").css("opacity", "1");
    $("div#navbar").removeClass("in");
    showPanelXs = !showPanelXs;
  }
};
