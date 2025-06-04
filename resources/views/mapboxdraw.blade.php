<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Draw a polygon and calculate its area</title>
    <meta name="viewport" content="initial-scale=1,maximum-scale=1,user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.12.0/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.12.0/mapbox-gl.js"></script>


    <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
    <script src="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-draw/v1.5.0/mapbox-gl-draw.js"></script>
    <link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-draw/v1.5.0/mapbox-gl-draw.css"
        type="text/css">

    <script src=" https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js "></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            margin: 0;
            padding: 0;
        }

        #map {
            width: 100%;
            height: 95vh;
        }
    </style>
</head>

<body>
    {{-- <div id='map' style='width: 400px; height: 300px;'></div> --}}
    <div id='map'></div>

    <button id="add-marker-btn"
        style="position: absolute; top: 10px; left: 10px; z-index: 1; padding: 10px; background: white; border: none; cursor: pointer;">
        ➕ Add Marker
    </button>

    <button id="add-marker-btn"
        style="position: absolute; top: 50px; left: 10px; z-index: 1; padding: 10px; background: white; border: none; cursor: pointer;">
        Save Polygon
    </button>

    <div class="calculation-box">
        <p>Click the map to draw a polygon.</p>
        <div id="calculated-area"></div>
    </div>

    <script>
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        mapboxgl.accessToken = 'pk.eyJ1IjoiZGtlMzYwIiwiYSI6ImNtYjBmdmZubTBqNmwybXNhMW84bjBveTcifQ.YArIG5KcPE1unjo1Tp41BA';
        const center = [125.822101, 7.398548];
        const map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/satellite-streets-v12',
            center: center,
            zoom: 14
        });



        map.on('load', () => {

            let isAddingMarker = false;

            map.addSource('maine', {
                'type': 'geojson',
                'data': {
                    'type': 'Feature',
                    'geometry': {
                        'type': 'Polygon',
                        'coordinates': [
                            [
                                [125.821812, 7.397899],
                                [125.822349, 7.397917],
                                [125.822300, 7.399815],
                                [125.821848, 7.398718],
                                [125.821812, 7.397899]
                            ]
                        ]
                    },
                    'properties': {
                        height: 15,
                        color: '#3a86ff'
                    }
                }
            });

            // 2. Add the 3D extrusion layer
            map.addLayer({
                'id': 'custom-building',
                'type': 'fill-extrusion',
                'source': 'maine',
                'paint': {
                    'fill-extrusion-color': ['get', 'color'],
                    'fill-extrusion-height': ['get', 'height'],
                    'fill-extrusion-base': 0,
                    'fill-extrusion-opacity': 0.6
                }
            });

            map.addLayer({
                'id': 'maine',
                'type': 'fill',
                'source': 'maine', // reference the data source
                'layout': {},
                'paint': {
                    'fill-color': '#0080ff', // blue color fill
                    'fill-opacity': 0.5
                }
            });
            // Add a black outline around the polygon.
            map.addLayer({
                'id': 'outline',
                'type': 'line',
                'source': 'maine',
                'layout': {},
                'paint': {
                    'line-color': '#000',
                    'line-width': 3
                }
            });


            const offset = 0.0001;
            const polygon = {
                type: 'Feature',
                geometry: {
                    type: 'Polygon',
                    coordinates: [
                        [
                            [125.821812, 7.397880], // bottom-left
                            [125.822349, 7.397917], // bottom-right
                            [125.822332, 7.399784], // top-right
                            [125.821794, 7.399809], // top-left
                            [125.821812, 7.397899] // close the polygon (same as first)

                        ]
                    ]
                }
            };

            //WOBLY PART

            map.addSource('mapbox-dem', {
                type: 'raster-dem',
                url: 'mapbox://mapbox.terrain-rgb',
                tileSize: 512,
                maxzoom: 14
            });
            map.setTerrain({
                source: 'mapbox-dem',
                exaggeration: 2.0
            });





            map.setPitch(60); // tilt camera
            map.setBearing(-20);


            const iconUrl = 'https://docs.mapbox.com/mapbox-gl-js/assets/custom_marker.png';


            const el = document.createElement('div');
            el.className = 'unclipped-marker';
            el.style.backgroundImage = `url(${iconUrl})`;
            el.style.width = '50px';
            el.style.height = '50px';
            el.style.backgroundSize = '30px 30px';
            el.style.backgroundPosition = 'center 10px';
            el.style.backgroundRepeat = 'no-repeat';

            new mapboxgl.Marker({
                    element: el,
                    // offset: [0, -20],

                })
                .setPopup(new mapboxgl.Popup().setHTML('<h3>Main Office</h3>'))
                .setLngLat(center)
                .addTo(map);



        });







        let currentPolygonCoords = [];
        const draw = new MapboxDraw({
            displayControlsDefault: false,
            // Select which mapbox-gl-draw control buttons to add to the map.
            controls: {
                polygon: true,
                trash: true
            },
            // Set mapbox-gl-draw to draw by default.
            // The user does not have to click the polygon control button first.
            defaultMode: 'draw_polygon'
        });
        map.addControl(draw);

        map.on('draw.create', updateArea);
        map.on('draw.delete', updateArea);
        map.on('draw.update', updateArea);



        function updateArea(e) {
            const data = draw.getAll();
            const answer = document.getElementById('calculated-area');
            if (data.features.length > 0) {
                const area = turf.area(data);
                // Restrict the area to 2 decimal points.
                const rounded_area = Math.round(area * 100) / 100;
                answer.innerHTML = `<p><strong>${rounded_area}</strong></p><p>square meters</p>`;
            } else {
                answer.innerHTML = '';
                if (e.type !== 'draw.delete')
                    alert('Click the map to draw a polygon.');
            }
        }

        map.on('draw.create', function(e) {
            const feature = e.features[0];
            const polygon = feature.geometry;

            const area = turf.area(feature);
            const center = turf.center(feature);
            const centerCoords = center.geometry.coordinates;

            if (confirm("Save this area?")) {
                const savedArea = {
                    coordinates: polygon.coordinates,
                    area: area,
                    center: {
                        lng: centerCoords[0],
                        lat: centerCoords[1]
                    }
                };
                // Send to backend via AJAX
                $.ajax({
                    url: '/areas',
                    type: 'POST',
                    dataType: 'json',
                    contentType: 'application/json',
                    data: JSON.stringify(savedArea),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content'
                        ) // Add this if CSRF is required
                    },
                    success: function(response) {
                        alert('Area saved successfully!');
                        console.log(response);
                        console.log(
                            `Center:\nLongitude: ${centerCoords[0].toFixed(6)}\nLatitude: ${centerCoords[1].toFixed(6)}`
                        );
                    },
                    error: function(xhr, status, error) {
                        alert('Error saving area!');
                        console.error(xhr.responseText || error);
                    }
                });
            }
        });
    </script>
</body>

</html>
