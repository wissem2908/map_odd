<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Wilaya Map - Hover Scale + Black Shadow + Bring to Front</title>
  <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
  />
  <style>
    /* Map container */
    #map {
      width: 100%;
      height: 600px;
      background: transparent; /* no background at all */
    }

    /* Default polygon style */
    .leaflet-interactive {
      fill: #ccc;
      fill-opacity: 0.6;
      stroke: white;
      stroke-width: 1;
      transition:
        transform 0.3s ease,
        stroke-width 0.3s ease,
        stroke 0.3s ease;
      transform-origin: center center;
      cursor: pointer;
    }

    /* Hovered polygon style: bigger + black shadow */
    .leaflet-interactive.hovered {
      /* stroke: #fff !important; */
      /* stroke-width: 4 !important; */
      filter:
        drop-shadow(0 0 6px #000)
        drop-shadow(0 0 12px #000);
    }
  </style>
</head>
<body>

<div id="map"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script
  src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
></script>

<script>
  $(function () {
    // Initialize map centered on Algeria
    var map = L.map('map', {
      center: [28, 3],
      zoom: 6,
      zoomControl: false,
    });

    // No tile layer = transparent background

    function style(feature) {
      return {
        fillColor: '#3498db',
        weight: 1,
        opacity: 1,
        color: 'white',
        fillOpacity: 0.6,
      };
    }

    function onEachFeature(feature, layer) {
      layer.on({
        mouseover: function (e) {
          var path = e.target.getElement();
          if (path) {
            path.classList.add('hovered');
          }
          e.target.bringToFront(); // Bring hovered polygon to top
        },
        mouseout: function (e) {
          var path = e.target.getElement();
          if (path) {
            path.classList.remove('hovered');
          }
          e.target.bringToBack(); // Restore original stacking order
        },
      });
    }

    $.getJSON('geojson/wilaya.geojson', function (data) {
      var geojsonLayer = L.geoJson(data, {
        style: style,
        onEachFeature: onEachFeature,
      }).addTo(map);

      map.fitBounds(geojsonLayer.getBounds());
    });
  });
</script>

</body>
</html>
