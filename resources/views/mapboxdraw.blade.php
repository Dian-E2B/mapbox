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
	<link type="text/css" href="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-draw/v1.5.0/mapbox-gl-draw.css"
		rel="stylesheet">

	<script src=" https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js "></script>

	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet"
		integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"
		integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous">
	</script>

	@vite(['resources/css/app.css', 'resources/js/app.js'])
	<style>
		body {
			margin: 0;
			padding: 0;
			font-family: "Open Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", Helvetica, Arial, sans-serif !important;
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

	<div class="form-group" style="position: absolute; top: 50px; left:
        10px; z-index: 1; padding: 10px; background: white; border: none; cursor: pointer;">
		<div class="custom-control custom-switch">
			<input class="custom-control-input" id="toggleIconsBtn" type="checkbox" onclick="selectPolygon()">
			<label class="custom-control-label" for="toggleIconsBtn">Toggle this to add icons</label>
		</div>
	</div>

	<div class="calculation-box">
		<p>Click the map to draw a polygon.</p>
		<div id="calculated-area"></div>
	</div>

	<script>
		Swal.bindClickHandler();
	const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	mapboxgl.accessToken = 'pk.eyJ1IjoiZGtlMzYwIiwiYSI6ImNtYjBmdmZubTBqNmwybXNhMW84bjBveTcifQ.YArIG5KcPE1unjo1Tp41BA';
	const center = [125.822101, 7.398548];
	const map = new mapboxgl.Map({
		container: 'map',
		style: 'mapbox://styles/mapbox/satellite-streets-v12',
		center: center,
		zoom: 14
	});

	let mapClicker = {
		enabled: false
	};

        function selectPolygon() {
            mapClicker.enabled = !mapClicker.enabled;
            const canvas = map.getCanvas();

            if (mapClicker.enabled) {
                // Disable Mapbox's default cursor handlers
                map.dragPan.disable();
                map.boxZoom.disable();

                // Apply custom cursor
                map.getCanvas().style.cursor = 'url("{{ asset('icons/flag.png') }}") 16 16, cell';
                console.log("🟢 Custom cursor FORCED");
            } else {
                // Re-enable default behaviors
                map.dragPan.enable();
                map.boxZoom.enable();
                canvas.style.cursor = '';
                console.log("🔴 Default cursor restored");
            }
        }

        map.on('load', () => {
            Get();
            GetIcons();
            let isAddingMarker = false;

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

            map.setPitch(60);
            map.setBearing(-20);
        });

        let currentMarkers = [];

        function GetIcons() {

            if (currentMarkers && currentMarkers.length > 0) {
                currentMarkers.forEach(obj => obj.marker.remove());
                currentMarkers = []; //reset lang
            }

            $.get('/centers', function(markers) {
                markers.forEach(marker => {
                    if (marker.isSprinkled === 1) {
                        const el = document.createElement('div');
                        el.className = 'custom-marker';

                        const img = document.createElement('img');
                        img.src = 'icons/sprinkler1.gif';
                        img.className = 'custom-marker-img';

                        el.appendChild(img);

                        const markerInstance = new mapboxgl.Marker({
                                element: el,
                                anchor: 'center'
                            })
                            .setLngLat(marker.coords)
                            .setPopup(new mapboxgl.Popup().setHTML(
                                `<h3>${marker.label}</h3><p>${marker.coords}</p>`))
                            .addTo(map);

                        currentMarkers.push({
                            marker: markerInstance,
                            el: img
                        });
                    }
					if (marker.plantState === 1) {
						const el = document.createElement('div');
						el.className = 'custom-marker';

						const img = document.createElement('img');
						img.src = 'icons/growing-seed.png';
						img.className = 'custom-marker-growing-img';

						el.appendChild(img);
						let offsetLat = 0.0;
						let offsetLng = 0.0;
						
						if (marker.isSprinkled === 1) {
							offsetLat = 0.0000; // tweak this value as needed
							offsetLng = 0.0015; // tweak this value as needed
						}
						
						const offsetCoords = [
							marker.coords[0] + offsetLng, // lng
							marker.coords[1] + offsetLat  // lat
						];

						const markerInstance = new mapboxgl.Marker({
								element: el,
								anchor: 'center'
							})
							.setLngLat(offsetCoords)
							.setPopup(new mapboxgl.Popup().setHTML(
								`<h3>${marker.label}</h3><p>${marker.coords}</p>`))
							.addTo(map);

						currentMarkers.push({
							marker: markerInstance,
							el: img
						});
					}
                });
            });
        }

        map.on('zoom', () => {
            const zoom = map.getZoom();
            currentMarkers.forEach(({
                el
            }) => {
                let scale = Math.max(0, Math.min(1, (zoom - 10) / 5));
                el.style.transform = `scale(${scale})`;
                el.style.opacity = scale <= 0.5 ? '0' : '1';
            });
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


                        // if (draw) {
                        //     draw.add({
                        //         type: 'Feature',
                        //         geometry: {
                        //             type: 'Polygon',
                        //             coordinates: coordinates
                        //         },
                        //         properties: {}
                        //     });
                        // }

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
                                        polygon_code: area.polygon_code, // from DB
                                        color: '#0080ff',
                                        height: 15,
                                    },
                                }
                            });
                        }

                        map.on('click', layerId, function(e) {

                            const code = e.features[0].properties.polygon_code;

                            if (mapClicker.enabled === true) {
                                $.ajax({
                                    url: '/check-polygon',
                                    type: 'POST',
                                    contentType: 'application/json',
                                    data: JSON.stringify({
                                        polygon_code: code
                                    }),
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                                            .attr('content'),
                                    },
                                    success: function(res) {
                                        const area = res.area;
                                        const info = `Polygon Info:
ID: ${area.id}
Code: ${area.polygon_code}
Area: ${area.area.toFixed(2)} sqm
Center: (${area.center_lat}, ${area.center_lng})
Created at: ${area.created_at}`;

                                        console.log(info);
                                        Swal.fire({
                                            title: 'Choose an icon',
                                            input: 'radio',
                                            inputOptions: {
                                                sprinkled: 'Sprinkled',
                                                growing: 'Growing',
                                                delete: 'Delete'
                                            },
                                            inputValidator: (value) => {
                                                if (!value) {
                                                    return 'You need to choose one!';
                                                }
                                            },
                                            confirmButtonText: 'Submit',
                                            showCancelButton: true
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                console.log('You selected:',
                                                result.value);
                                                if (result.value ==='sprinkled') {
                                                    console.log( 'Sprinkol!');
                                                    $.ajax({
                                                        url: '/setterIcons/' + area.id + '/setSprinkol_add',
                                                        type: 'POST',
                                                        dataType: 'json',
                                                        headers: {
                                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                                        },
                                                        success: function(response) {
															if (response.updated) {
																GetIcons();
																Swal.fire({
																	title: 'Success!',
																	text: 'Sprinkler was set!',
																	icon: 'success',
																	toast: true,
																	timer: 2000,
																	showConfirmButton: false
																});
															} else {
																Swal.fire({
																	title: 'Oops!',
																	text: response.message || 'Already set!',
																	icon: 'info',
																	toast: true,
																	timer: 2000,
																	showConfirmButton: false
																});
															}
                                                            // console.log("setterSprinkol",response);
                                                        }
                                                    });
                                                } else if (result.value ==='delete') {

                                                    // $.ajax({
                                                    //     url: '/setterIcons/' + area.id + '/setSprinkol_remove',
                                                    //     type: 'POST',
                                                    //     dataType: 'json',
                                                    //     headers: {
                                                    //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                                    //     },
                                                    //     success: function(response) {
													// 		if (response.updated) {
													// 			GetIcons();
													// 			Swal.fire({
													// 				title: 'Success!',
													// 				text: 'Sprinkler removed!',
													// 				icon: 'success',
													// 				toast: true,
													// 				timer: 2000,
													// 				showConfirmButton: false
													// 			});
													// 		} else {
													// 			Swal.fire({
													// 				title: 'Oops!',
													// 				text: response.message || 'Nothing to remove!',
													// 				icon: 'info',
													// 				toast: true,
													// 				timer: 2000,
													// 				showConfirmButton: false
													// 			});
													// 		}
                                                    //         GetIcons();
                                                    //         console.log("setterIcons",response);
                                                    //     }
                                                    // });
                                                }
												else if (result.value ==='growing') {
                                                    $.ajax({
                                                        url: '/setterIcons/' + area.id + '/setGrown_1',
                                                        type: 'POST',
                                                        dataType: 'json',
                                                        headers: {
                                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                                        },
                                                        success: function(response) {
															if (response.updated) {
																GetIcons();
																Swal.fire({
																	title: 'Success!',
																	text: 'Plant state updated!',
																	icon: 'success',
																	toast: true,
																	timer: 2000,
																	showConfirmButton: false
																});
															} else {
																Swal.fire({
																	title: 'Oops!',
																	text: response.message || 'Nothing to remove!',
																	icon: 'info',
																	toast: true,
																	timer: 2000,
																	showConfirmButton: false
																});
															}
                                                           
                                                            console.log("setterIcons",response);
                                                        }
                                                    });
                                                }
                                            }
                                        });
                                        //     console.log("User wants to proceed!");
                                        // } else {
                                        //     console.log("User canceled.");
                                        // }
                                    }
                                });
                            }

                        });



                        if (!map.getLayer(layerId)) {
                            map.addLayer({
                                id: layerId,
                                type: 'fill',
                                source: sourceId,
                                layout: {},
                                paint: {
                                    'fill-color': '#0080ff',
                                    'fill-opacity': 0.1,
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
        });

        map.addControl(draw);
        map.on('draw.create', onDrawCreate);
        map.on('draw.delete', updateArea);
        map.on('draw.update', updateArea);

        function updateArea(e) {
            mapClicker = false;
            document.getElementById('toggleIconsBtn').checked = false;
            const data = draw.getAll();
            const answer = document.getElementById('calculated-area');
            if (data.features.length > 0) {
                const area = turf.area(data);
                const rounded_area = Math.round(area * 100) / 100;
                answer.innerHTML = `<p><strong>${rounded_area}</strong></p>
                <p>square meters</p>`;
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
            const center = turf.centroid(feature);
            const centerCoords = center.geometry.coordinates;
            const polygonCode = crypto.randomUUID()

            if (confirm("Save this area?")) {
                const savedArea = {
                    polygon_code: polygonCode,
                    coordinates: polygon.coordinates,
                    area: area,
                    center: {
                        lng: centerCoords[0],
                        lat: centerCoords[1]
                    }
                };
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
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    draw.deleteAll();

                    // alert('Area saved successfully!');
                    Swal.fire({
                        title: "Added!",
                        text: "Saved successfully!",
                        icon: "success",
                        toast: true
                    });
                    console.log(response);
                    console.log(
                        `Center:\nLongitude: ${centerCoords[0].toFixed(6)}\nLatitude: ${centerCoords[1].toFixed(6)}`
                    );
                    setTimeout(() => {
                        Get();
                        GetIcons();
                    }, 100);
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