<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Mapbox Shaded Area Test</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
 <script src='https://api.mapbox.com/mapbox-gl-js/v3.1.2/mapbox-gl.js'></script>
<link href='https://api.mapbox.com/mapbox-gl-js/v3.1.2/mapbox-gl.css' rel='stylesheet' />
@vite(['resources/css/app.css', 'resources/js/app.js'])
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/jscastro76/threebox@v.2.2.2/dist/threebox.min.js" type="text/javascript"></script>
<link href="https://cdn.jsdelivr.net/gh/jscastro76/threebox@v.2.2.2/dist/threebox.css" rel="stylesheet" />

  <style>
    body {
      margin: 0;
      padding: 0;
    }

    #map {
      width: 100%;
      height: 100vh;
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
        // Choose from Mapbox's core styles, or make your own style with Mapbox Studio
         style: 'mapbox://styles/mapbox/satellite-streets-v12',
        center : [125.822101, 7.398548],
        zoom: 15.5,
        pitch: 45,
        bearing: -17.6,
        container: 'map',
        antialias: true
    });

 map.on('style.load', () => {
        // Insert the layer beneath any symbol layer.
        const layers = map.getStyle().layers;
        const labelLayerId = layers.find(
            (layer) => layer.type === 'symbol' && layer.layout['text-field']
        ).id;

        // The 'building' layer in the Mapbox Streets
        // vector tileset contains building height data
        // from OpenStreetMap.
       map.addLayer(
  {
    'id': 'add-3d-buildings',
    'source': 'composite',
    'source-layer': 'building',
    'filter': ['==', 'extrude', 'true'],
    'type': 'fill-extrusion',
    'minzoom': 15,
    'paint': {
      'fill-extrusion-color': '#3a86ff', // ✅ Changed to blue
      'fill-extrusion-height': [
        'interpolate',
        ['linear'],
        ['zoom'],
        15,
        0,
        15.05,
        ['*', ['get', 'height'], 8]
      ],
      'fill-extrusion-base': [
        'interpolate',
        ['linear'],
        ['zoom'],
        15,
        0,
        15.05,
        ['get', 'min_height']
      ],
      'fill-extrusion-opacity': 1
    }
  },
  labelLayerId
);

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

  

  </script>
</body>

</html>