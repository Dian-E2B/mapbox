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

    {{-- <button id="add-marker-btn"
        style="position: absolute; top: 10px; left: 10px; z-index: 1; padding: 10px; background: white; border: none; cursor: pointer;">
        ➕ Add Marker
    </button> --}}

    <button id="add-marker-btn" onclick="DrawPolygon('draw_polygon')"
        style="position: absolute; top: 50px; left: 10px; z-index: 1; padding: 10px; background: white; border: none; cursor: pointer;">
        Add Polygon
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
            Get();
            let isAddingMarker = false;

            // map.addSource('maine', {
            //     'type': 'geojson',
            //     'data': {
            //         'type': 'Feature',
            //         'geometry': {
            //             'type': 'Polygon',
            //             'coordinates': [
            //                 [
            //                     [125.821812, 7.397899],
            //                     [125.822349, 7.397917],
            //                     [125.822300, 7.399815],
            //                     [125.821848, 7.398718],
            //                     [125.821812, 7.397899]
            //                 ]
            //             ]
            //         },
            //         'properties': {
            //             height: 15,
            //             color: '#0000ff'
            //         }
            //     }
            // });








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



        function Get() {
            $.ajax({
                url: '/areas',
                type: 'GET',
                dataType: 'json',
                success: function(areas) {
                    areas.forEach((area, index) => {
                        const sourceId = `polygon-${index}`;
                        const layerId = `polygon-layer-${index}`;

                        const coordinates = typeof area.coordinates === 'string' ?
                            JSON.parse(area.coordinates) :
                            area.coordinates;

                        if (!map.getSource(sourceId)) {
                            map.addSource(sourceId, {
                                type: 'geojson',
                                data: {
                                    type: 'Feature',
                                    geometry: {
                                        type: 'Polygon',
                                        coordinates: coordinates
                                    },
                                    properties: {
                                        color: '#3a86ff',
                                        height: 15
                                    }
                                }
                            });
                        }



                        if (!map.getLayer(layerId)) {
                            map.addLayer({
                                id: layerId,
                                type: 'fill',
                                source: sourceId,
                                layout: {},
                                paint: {
                                    'fill-color': '#0080ff',
                                    'fill-opacity': 0.2,
                                    'fill-outline-color': 'black'
                                }
                            });

                            const outlineLayerId = layerId + '-outline';
                            if (!map.getLayer(outlineLayerId)) {
                                map.addLayer({
                                    id: outlineLayerId,
                                    type: 'line',
                                    source: sourceId,
                                    layout: {},
                                    paint: {
                                        'line-color': 'black',
                                        'line-width': 3
                                    }
                                });
                            }
                        }



                    });
                },
                error: function(xhr, status, error) {
                    console.error('Failed to load polygons:', error);
                }
            });
        }



        const draw = new MapboxDraw({
            displayControlsDefault: false,
            controls: {
                polygon: true,
                trash: true
            },
            defaultMode: 'simple_select' // start with select mode, no drawing
        });

        map.addControl(draw);
        map.on('draw.create', onDrawCreate);
        map.on('draw.delete', updateArea);
        map.on('draw.update', updateArea);

        function updateArea(e) {
            const data = draw.getAll();
            const answer = document.getElementById('calculated-area');
            if (data.features.length > 0) {
                const area = turf.area(data);
                const rounded_area = Math.round(area * 100) / 100;
                answer.innerHTML = `<p><strong>${rounded_area}</strong></p><p>square meters</p>`;
            } else {
                answer.innerHTML = '';
                if (e.type !== 'draw.delete')
                    alert('Click the map to draw a polygon.');
            }
        }

        function onDrawCreate(e) {
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
                Save(savedArea, centerCoords);
            }
        }

        function DrawPolygon() {
            draw.changeMode('draw_polygon');
        }



        function Save(savedArea, centerCoords) {
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

                    draw.deleteAll(); // Clear the drawn polygon after saving
                    Get();
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
    </script>
</body>

</html>
