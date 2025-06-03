<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Mapbox Shaded Area Test</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
 <script src='https://api.mapbox.com/mapbox-gl-js/v3.1.2/mapbox-gl.js'></script>
<link href='https://api.mapbox.com/mapbox-gl-js/v3.1.2/mapbox-gl.css' rel='stylesheet' />
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
<div id='map' ></div>

<button id="add-marker-btn" style="position: absolute; top: 10px; left: 10px; z-index: 1; padding: 10px; background: white; border: none; cursor: pointer;">
  ➕ Add Marker
</button>

<script>
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

// document.getElementById('add-marker-btn').addEventListener('click', () => {
//   isAddingMarker = true;
//   map.getCanvas().style.cursor = 'crosshair';
//   alert('Click on the map to add a marker.');
// });



      const offset = 0.0001;
      const polygon = {
        type: 'Feature',
        geometry: {
          type: 'Polygon',
          coordinates: [[
            [125.821812, 7.397880], // bottom-left
            [125.822349, 7.397917], // bottom-right
            [125.822332, 7.399784], // top-right
            [125.821794, 7.399809], // top-left
            [125.821812, 7.397899]  // close the polygon (same as first)

          ]]
        }
      };

      map.addSource('mapbox-dem', {
        type: 'raster-dem',
        url: 'mapbox://mapbox.terrain-rgb',
        tileSize: 512,
        maxzoom: 14
      });
      map.setTerrain({ source: 'mapbox-dem', exaggeration: 1.5 });

      // map.flyTo({
      //   center: center,
      //   zoom: 13,
      //   pitch: 60,
      //   bearing: -20,
      //   speed: 0.5
      // });

      map.addLayer({
        id: 'custom-building',
        type: 'fill-extrusion',
        source: {
          type: 'geojson',
          data: {
            type: 'Feature',
            geometry: {
              type: 'Polygon',
              coordinates: [[
                [125.821812, 7.397899],
                [125.822349, 7.397917],
                [125.822300, 7.399815], // top-right
                [125.821848, 7.398718], // top-left
                [125.821812, 7.397899]
              ]]
            },
            properties: {
              height: 15, // Height in meters
              color: '#3a86ff' // Blue color
            }
          }
        },
        paint: {
          'fill-extrusion-color': ['get', 'color'],
          'fill-extrusion-height': ['get', 'height'],
          'fill-extrusion-opacity': 0.3,
          'fill-extrusion-base': 0
        }
      });

      map.setPitch(60);   // tilt camera
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



map.on('click', (event) => {
  const lng = event.lngLat.lng;
  const lat = event.lngLat.lat;

  // Ask user if they want to add a pin
  const shouldAddPin = confirm(`Do you want to add a pin at this location?\nLatitude: ${lat.toFixed(6)}\nLongitude: ${lng.toFixed(6)}`);

  if (shouldAddPin) {
    // If they confirm, you can add a pin on the map and alert the coordinates
    new mapboxgl.Marker()
      .setLngLat([lng, lat])
      .addTo(map);

    alert(`Pin added at:\nLatitude: ${lat.toFixed(6)}\nLongitude: ${lng.toFixed(6)}`);
  } else {
    alert('Pin not added.');
  }
});

// map.on('click', (event) => {
//   const lng = event.lngLat.lng;
//   const lat = event.lngLat.lat;

//   // Make an AJAX request to the Laravel backend to save this location
//   fetch('/api/save-location', {
//     method: 'POST',
//     headers: {
//       'Content-Type': 'application/json',
//       'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content // CSRF Token
//     },
//     body: JSON.stringify({ lat, lng })
//   })
//   .then(response => response.json())  // Parse the JSON response
//   .then(data => {
//     alert(`Saved: ${data.message}`);
//   })
//   .catch(error => {
//     alert('Error saving location');
//     console.error(error);
//   });
// });

  </script>
</body>

</html>