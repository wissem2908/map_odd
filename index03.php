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
    </style>
</head>

<body>

    <div id="map"></div>
    <div id="objectives-container" style="display: flex; flex-wrap: wrap; gap: 10px;"></div>

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
                color: '#a58f73',
                fillOpacity: 0.6,
                fillColor: '#ffebbe',
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
                        }).bindPopup(feature.properties.Ville || "No name");

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
            <div class="obj-card" data-id="${obj.idObjectif}">
              ${obj.idObjectif}
            </div>
          `);

                            // Add click event
                            card.on("click", function() {
                                let id = $(this).data("id");
                                // console.log("Clicked ID:", id);
                                // You can call another function here using this id
                                /*************************** get objectif ville resultat *************************** */

                                $.ajax({
                                    url: 'assets/php/get_obj_ville.php',
                                    method: 'POST',
                                    data: {
                                        id_obj: id
                                    },
                                    success: function(response) {
                                        // console.log("get_obj_ville:", response);

                                        var data = JSON.parse(response);
                                        console.log("Parsed Data:", data);

                                        for (i = 0; i < data.length; i++) {

                                            var resultat = data[i].resultat;


                                            // You can handle the data as needed, e.g., display it in a modal or alert
                                        }
                                        // Handle the response as needed
                                    },
                                })

                                /************************** get indicateur ******************************** */



                                // $.ajax({
                                //   url: 'assets/php/get_indicateur.php',
                                //   method: 'POST',
                                //   data: {
                                //     id_obj: id
                                //   },
                                //   success: function(response) {
                                //     // console.log("Response:", response);
                                //     // Handle the response as needed
                                //   },
                                //   error: function(xhr, status, error) {
                                //     console.error("Error fetching indicator:", error);
                                //   }
                                // })
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
    </script>

</body>

</html>