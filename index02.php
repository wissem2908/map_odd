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
      height: 100vh;

    }



    /* Style ONLY villes (now clickable, so no pointer-events:none) */
    .ville-shape {
      z-index: 1000;
      /* cursor: pointer; */
    }
  </style>
</head>

<body>

  <div id="map"></div>

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

    function styleWilaya(feature) {
      return {
        className: 'wilaya-shape',
        // weight: 1,
        opacity: 0.4,
        color: 'white',
        fillOpacity: 0.6,
        fillColor: '#043cbeff',
      };
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
    $.getJSON('geojson/wilaya.json', function(data) {
      wilayaLayer = L.geoJson(data, {
        style: styleWilaya,
      }).addTo(map);

      // Load villes
      $.getJSON('geojson/limite_villes.json', function(data2) {
        villeLayer = L.geoJson(data2, {
          style: styleVille,
        }).addTo(map);

        villeLayer.bringToFront();

        // Load performances_villes
        $.getJSON('geojson/performances_villes.json', function(pointData) {
          performanceLayer = L.geoJson(pointData, {
            pointToLayer: function(feature, latlng) {
              return L.circleMarker(latlng, {
                radius: 8,
                fillColor: '#ff7800',
                color: '#000',
                weight: 1,
                opacity: 1,
                fillOpacity: 0.8
              }).bindPopup(feature.properties.name || "No name");
            }
          }).addTo(map);

          performanceLayer.bringToFront();

          // Fit map to all layers combined bounds
          var allBounds = wilayaLayer.getBounds()
            .extend(villeLayer.getBounds())
            .extend(performanceLayer.getBounds());
          map.fitBounds(allBounds);
        });
      });
    });
  </script>

</body>

</html>