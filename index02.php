<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Wilaya Map </title>
  <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <style>
    /* Map container */
    #map {
      width: 100%;
      height: 80vh;

    }



    /* Style ONLY villes (now clickable, so no pointer-events:none) */
    .ville-shape {
      z-index: 1000;
      /* cursor: pointer; */
    }

    .obj-card {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      /* makes it circular */
      background-color: #ff7800;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      cursor: pointer;
      transition: transform 0.2s, background-color 0.2s;
    }

    .obj-card:hover {
      background-color: #e56e00;
      transform: scale(1.1);
    }

    /* Marker hover glow */
    .leaflet-interactive.custom-marker:hover {
      stroke: #fff;
      stroke-width: 3px;
      filter: drop-shadow(0 0 5px rgba(255, 255, 255, 0.8));
    }

    /* Popup style */
    .popup-container {
      font-family: Arial, sans-serif;
      padding: 5px 8px;
      min-width: 150px;
    }

    .popup-container h4 {
      margin: 0 0 5px 0;
      font-size: 14px;
      color: #333;
      border-bottom: 1px solid #ddd;
      padding-bottom: 3px;
    }

    .popup-container p {
      margin: 0;
      font-size: 13px;
      color: #555;
    }

    .popup-container {
  font-family: Arial, sans-serif;
  padding: 6px 8px;
  min-width: 160px;
}

.popup-title {
  margin: 0 0 6px 0;
  font-size: 14px;
  color: #2c3e50;
  border-bottom: 1px solid #ccc;
  padding-bottom: 3px;
}

.popup-container p {
  margin: 2px 0;
  font-size: 13px;
  color: #555;
}

  </style>
</head>

<body>

  <div id="map"></div>

  <div id="objectives-container" style="display: flex; flex-wrap: wrap; gap: 10px;"></div>
  <div>
    <ul id="indicateurs">

    </ul>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <script>
    var map = L.map('map').setView([28, 3], 5); // Centered on Algiers, Algeria

    // Esri World Imagery basemap
    L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
      attribution: 'Tiles &copy; Esri &mdash; Source: Esri, Maxar, Earthstar Geographics',
      // maxZoom: 19
    }).addTo(map);


    /****************************************** style ********************************************** */

    let wilayaBaseFill = '#ffebbe';

    function styleWilaya(feature) {
      return {
        className: 'wilaya-shape',
        opacity: 0.4,
        color: '#a58f73',
        fillOpacity: 0.6,
        fillColor: wilayaBaseFill,
      };
    }

    function setBaseFillColor(color) {
      wilayaBaseFill = color;
      // Apply immediately to the layer
      if (wilayaLayer) {
        wilayaLayer.eachLayer(layer => layer.setStyle({
          fillColor: wilayaBaseFill
        }));
      }
    }

    function styleVille(feature) {
      return {
        className: 'ville-shape',
        fillColor: '#936700ff',
        weight: 1,
        opacity: 1,
        color: '#080808ff',
        fillOpacity: 0.4,
      };
    }

    /******************************************* geojson ********************************************* */
    // Load wilayas
    function loadWilaya() {
      return $.getJSON('geojson/wilaya.json').then(function(data) {
        wilayaLayer = L.geoJson(data, {
          style: styleWilaya
        }).addTo(map);
        return wilayaLayer;
      });
    }

    function loadVilles() {
      return $.getJSON('geojson/limite_villes.json').then(function(data) {
        villeLayer = L.geoJson(data, {
          style: styleVille
        }).addTo(map);
        villeLayer.bringToFront();
        return villeLayer;
      });
    }

    function loadPerformancesVilles() {
      return $.getJSON('geojson/performances_villes.json').then(function(pointData) {
        let markers = [];

        let ajaxCalls = pointData.features.map(function(feature) {
          return $.ajax({
            url: 'assets/php/indice_global.php',
            method: 'POST',
            data: {
              ville: feature.properties.Ville
            },
            dataType: 'json'
          }).then(function(response) {
            let val = response ? parseFloat(response) : null;

            let color;
            if (val === null) color = 'gray';
            else if (val < 25) color = 'red';
            else if (val < 50) color = 'orange';
            else if (val < 75) color = 'yellow';
            else color = 'green';

            let latlng = L.latLng(
              feature.geometry.coordinates[1], // lat
              feature.geometry.coordinates[0] // lng
            );

            let marker = L.circleMarker(latlng, {
              radius: 8,
              fillColor: color,
              color: '#000',
              weight: 1,
              opacity: 1,
              fillOpacity: 0.8
            }).bindPopup(`
  <div class="popup-container">
    <h4>${feature.properties.Ville || "Ville inconnue"}</h4>
    <p><b>Indice Global:</b> ${val !== undefined ? val : 'Non défini'}</p>
  </div>
`);

            markers.push(marker);
          });
        });

        return $.when.apply($, ajaxCalls).then(function() {
          let performanceLayer = L.layerGroup(markers).addTo(map);
          performanceLayer.bringToFront();
          return performanceLayer;
        });
      });
    }

    // Main loader
    function initMapData() {
      let wilayaLayer, villeLayer, performanceLayer;

      loadWilaya().then(function(wl) {
        wilayaLayer = wl;
        return loadVilles();
      }).then(function(vl) {
        villeLayer = vl;
        return loadPerformancesVilles();
      }).then(function(pl) {
        performanceLayer = pl;

        // Fit map bounds to all layers
        let allBounds = wilayaLayer.getBounds()
          .extend(villeLayer.getBounds())
          .extend(performanceLayer.getBounds());
        map.fitBounds(allBounds);
      });
    }

    // Call it
    initMapData();



    /**************************** get objectifs********************************* */

    function getObj() {
      $.ajax({
        url: 'assets/php/get_obj.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
          if (data && data.length > 0) {
            let container = $("#objectives-container");
            container.empty();

            data.forEach(function(obj) {
              // Create a clickable card
              let card = $(`
            <div class="obj-card" data-id="${obj.idObjectif}" data-nom="${obj.intitule}">
              ${obj.idObjectif}
            </div>
          `);

              card.on("click", function() {
                let id = $(this).data("id");
                let intitule = $(this).data("nom");

                if (wilayaLayer) {
                  map.removeLayer(wilayaLayer);
                }

                $.getJSON('geojson/wilaya.json').then(function(data) {
                  wilayaLayer = L.geoJson(data, {
                    style: {
                      className: 'wilaya-shape',
                      opacity: 0.4,
                      color: '#602e52ff',
                      fillOpacity: 1,
                      fillColor: "#f7bcf9ff",
                    }
                  }).addTo(map);
                  wilayaLayer.bringToBack(); // put it under all other layers
                });
                // Get all results for the selected objectif
                $.ajax({
                  url: 'assets/php/get_obj_ville.php',
                  method: 'POST',
                  data: {
                    id_obj: id
                  },
                  dataType: 'json',
                  success: function(data) {


                    // Load the geojson file
                    $.getJSON('geojson/performances_villes.json').then(function(pointData) {
                      let markers = [];

                      pointData.features.forEach(function(feature) {

                        // Find matching city by idVille
                        let villeInfo = data.find(v => v.nomVille === feature.properties.Ville);

                        console.log("Ville Info:", villeInfo);
                        if (villeInfo) {
                          console.log("ville info:", villeInfo);
                          let val = parseFloat(villeInfo.resultat);

                          // Determine marker color
                          let color;
                          if (val === null) color = 'gray';
                          else if (val < 25) color = 'red';
                          else if (val < 50) color = 'orange';
                          else if (val < 75) color = 'yellow';
                          else color = 'green';

                          let latlng = L.latLng(
                            feature.geometry.coordinates[1], // lat
                            feature.geometry.coordinates[0] // lng
                          );

                          let marker = L.circleMarker(latlng, {
                            radius: 8,
                            fillColor: color,
                            color: '#000',
                            weight: 1,
                            opacity: 1,
                            fillOpacity: 0.8
                          }).bindPopup(`
  <div class="popup-container">
    <h4 class="popup-title">${feature.properties.Ville || "Ville inconnue"}</h4>
    <p><b>Valeur:</b> ${val}</p>
    <p><b>ODD:</b> ${intitule}</p>
  </div>
`);

                          markers.push(marker);
                        }
                      });

                      // Add all markers to map
                      if (window.performanceLayer) map.removeLayer(window.performanceLayer);
                      window.performanceLayer = L.layerGroup(markers).addTo(map);
                      window.performanceLayer.bringToFront();

                    });
                  }
                });

                /************************** get indicateur ******************************** */



                $.ajax({
                  url: 'assets/php/get_indicateur.php',
                  method: 'POST',
                  data: {
                    id_obj: id
                  },
                  success: function(response) {
                    //   console.log("Response:", response);
                    var data = JSON.parse(response);

                    var indicateurs = "";

                    for (i = 0; i < data.length; i++) {
                      indicateurs += "<li><a data-indc='"+data[i].intitule+"' id='indic' href='#' data='" + data[i].idIndicateur + "'>" + data[i].intitule + "</a></li>";
                    }
                    $("#indicateurs").html(indicateurs);
                    // Handle the response as needed
                  },
                  error: function(xhr, status, error) {
                    console.error("Error fetching indicator:", error);
                  }
                })
              });

              container.append(card);
            });
          } else {
            console.log("No objectives found.");
          }
        },
        error: function(xhr, status, error) {
          console.error("Error fetching objectives:", error);
        }
      });
    }

    getObj();



    /**************************************** indicateur click ********************************************/


    $(document).on('click', '#indic', function(e) {
      e.preventDefault();
      var id = $(this).attr('data');
      var intitule = $(this).data('indc');
      console.log("Clicked indicator:", intitule);
      console.log("Clicked indicator ID:", id);

      $.ajax({
        url: 'assets/php/get_indicateur_details.php',
        method: 'POST',
        data: {
          id_ind: id
        },
        success: function(response) {
          console.log(response);
          var data = JSON.parse(response);

          // Remove old performance layer if exists
          if (window.performanceLayer) {
            map.removeLayer(window.performanceLayer);
          }

          // Optional: reload wilaya background each time indicator is clicked
          if (wilayaLayer) {
            map.removeLayer(wilayaLayer);
          }
          $.getJSON('geojson/wilaya.json').then(function(geoData) {
            wilayaLayer = L.geoJson(geoData, {
              style: {
                className: 'wilaya-shape',
                opacity: 0.4,
                color: '#602e52ff',
                fillOpacity: 1,
                fillColor: "#bcf9d3ff"
              }
            }).addTo(map);
            wilayaLayer.bringToBack();
          });

          // Load villes points and recolor based on indicator results
          $.getJSON('geojson/performances_villes.json').then(function(pointData) {
            let markers = [];

            pointData.features.forEach(function(feature) {
              // Find matching city in indicator data
              let villeInfo = data.find(v => v.nomVille === feature.properties.Ville);

              if (villeInfo) {
                let val = parseFloat(villeInfo.valeurNormaliseCor);
                let uniteMesure = villeInfo.uniteMesure; // ✅ directly from matched city
                console.log("Valeur Normalisée Cor:", val, "Ville:", villeInfo.nomVille, uniteMesure);

                // Determine marker color
                let color;
                if (isNaN(val)) color = 'gray';
                else if (val < 25) color = 'red';
                else if (val < 50) color = 'orange';
                else if (val < 75) color = 'yellow';
                else color = 'green';

                let latlng = L.latLng(
                  feature.geometry.coordinates[1], // lat
                  feature.geometry.coordinates[0] // lng
                );

                let marker = L.circleMarker(latlng, {
                  radius: 8,
                  fillColor: color,
                  color: '#000',
                  weight: 1,
                  opacity: 1,
                  fillOpacity: 0.8
                }).bindPopup(`
  <div class="popup-container">
    <h4 class="popup-title">${feature.properties.Ville || "Ville inconnue"}</h4>
    <p><b>Valeur:</b> ${val} <span class="unit">(${uniteMesure})</span></p>
    <p><b>Indicateur:</b> ${intitule}</p>
  </div>
`);

                markers.push(marker);
              }
            });

            // Add the new markers to the map
            window.performanceLayer = L.layerGroup(markers).addTo(map);
            window.performanceLayer.bringToFront();
          });
        }
      });
    });
  </script>

</body>

</html>