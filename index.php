<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Wilaya + Ville Map</title>
  <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
  />
  <style>
    #map {
      width: 100%;
      height: 600px;
      background: transparent;
    }

    /* Style ONLY wilayas */
    .wilaya-shape {
      transition:
        transform 0.3s ease,
        stroke-width 0.3s ease,
        stroke 0.3s ease;
      cursor: pointer;
    }

    /* Hover effect for wilayas */
    .wilaya-shape.hovered {
      fill: #fff !important;
      filter:
        drop-shadow(0 0 6px #ffffffff)
        drop-shadow(0 0 12px #000);
    }

    /* Style ONLY villes (no hover, no click) */
    .ville-shape {
      pointer-events: none; /* disables hover/click */
    }
  </style>
</head>
<body>

<div id="map"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
  $(function () {
    var map = L.map('map', {
      center: [28, 3],
      zoom: 6,
      zoomControl: false,
    });

    function styleWilaya(feature) {
      return {
        className: 'wilaya-shape', // unique class for CSS
        fillColor: '#cccccc',
        weight: 1,
        opacity: 1,
        color: 'white',
        fillOpacity: 0.6,
      };
    }

    function styleVille(feature) {
      return {
        className: 'ville-shape', // unique class for CSS
        fillColor: '#5a5858ff', // red fill for city limits
        weight: 1,
        opacity: 1,
        color: '#080808ff', // dark red border
        fillOpacity: 0.4,
      };
    }

    function onEachWilaya(feature, layer) {
      layer.on({
        mouseover: function (e) {
          var path = e.target.getElement();
          if (path) path.classList.add('hovered');
          e.target.bringToFront();
        },
        mouseout: function (e) {
          var path = e.target.getElement();
          if (path) path.classList.remove('hovered');
          e.target.bringToBack();
        },
      });
    }

    // Load wilayas
    $.getJSON('geojson/wilaya.json', function (data) {
      var wilayaLayer = L.geoJson(data, {
        style: styleWilaya,
        onEachFeature: onEachWilaya,
      }).addTo(map);

      // Load villes (always on top)
      $.getJSON('geojson/limite_villes.json', function (data2) {
        var villeLayer = L.geoJson(data2, {
          style: styleVille
        }).addTo(map);

        // Ensure villes are above wilayas
        villeLayer.bringToFront();

        // Fit map to both
        var allBounds = wilayaLayer.getBounds().extend(villeLayer.getBounds());
        map.fitBounds(allBounds);
      });
    });
  });
</script>

</body>
</html>
