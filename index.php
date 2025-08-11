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
        stroke 0.3s ease,
        filter 0.3s ease;
      cursor: pointer;
    }

    /* Hover effect for wilayas (keeps image) */
    .wilaya-shape.hovered {
      filter:
        brightness(1.15)
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
<script src="https://unpkg.com/@turf/turf/turf.min.js"></script>

<script>
  $(function () {
    var map = L.map('map', {
      center: [28, 3],
      zoom: 5,
      zoomControl: false,
        attributionControl: false // disables the attribution control entirely
    });

    // Create an SVG defs section for patterns
    var svgDefs = document.createElementNS("http://www.w3.org/2000/svg", "defs");
    map.getRenderer(map)._container.appendChild(svgDefs);

function createPattern(feature) {
  var patternId = "pattern-" + feature.properties.name.replace(/\s+/g, '-');

  var pattern = document.createElementNS("http://www.w3.org/2000/svg", "pattern");
  pattern.setAttribute("id", patternId);
  pattern.setAttribute("patternUnits", "objectBoundingBox");
  pattern.setAttribute("patternContentUnits", "objectBoundingBox");
  pattern.setAttribute("width", 1);
  pattern.setAttribute("height", 1);

  $.ajax({
    url: 'assets/php/get_image.php',
    type: 'POST',
    data: { name: feature.properties.name },
    dataType: 'json',
    success: function(response) {
      if (response && response.ImageProfil) {
        // If image exists, use it
        var image = document.createElementNS("http://www.w3.org/2000/svg", "image");
        image.setAttribute("href", response.ImageProfil);
        image.setAttribute("width", 1);
        image.setAttribute("height", 1);
        image.setAttribute("preserveAspectRatio", "xMidYMid slice");
        pattern.appendChild(image);
      } else {
        // No image → fill with background color
        var rect = document.createElementNS("http://www.w3.org/2000/svg", "rect");
        rect.setAttribute("width", 1);
        rect.setAttribute("height", 1);
        rect.setAttribute("fill", "#cccccc"); // your default background color
        pattern.appendChild(rect);
      }
    }
  });

  svgDefs.appendChild(pattern);
  return "url(#" + patternId + ")";
}



    function styleWilaya(feature) {
      return {
        className: 'wilaya-shape',
        weight: 1,
        opacity: 1,
        color: 'white',
        fillOpacity: 1
      };
    }

    function styleVille(feature) {
      return {
        className: 'ville-shape',
        fillColor: '#5a5858ff',
        weight: 1,
        opacity: 1,
        color: '#080808ff',
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

      // Set the fill to the pattern
      layer.on('add', function () {
        var path = layer.getElement();
        if (path) {
          path.setAttribute("fill", createPattern(feature));
        }
      });
    }

    // Load wilayas
    $.getJSON('geojson/wilaya.json', function (data) {
      var wilayaLayer = L.geoJson(data, {
        style: styleWilaya,
        onEachFeature: onEachWilaya,
      }).addTo(map);

      // Load villes
      // $.getJSON('geojson/limite_villes.json', function (data2) {
      //   var villeLayer = L.geoJson(data2, {
      //     style: styleVille
      //   }).addTo(map);

      //   villeLayer.bringToFront();

      //   var allBounds = wilayaLayer.getBounds().extend(villeLayer.getBounds());
      //   map.fitBounds(allBounds);
      // });
    });
  });
</script>

</body>
</html>
