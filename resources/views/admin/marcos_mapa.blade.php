@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #map { height: 600px; width: 100%; border-radius: 10px; margin-top: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    .custom-popup .leaflet-popup-content-wrapper {
        background: #1f2937;
        color: #fff;
        border-radius: 8px;
    }
    .custom-popup .leaflet-popup-tip {
        background: #1f2937;
    }
</style>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-map-marked-alt text-blue-500 mr-2"></i> Mapa do Imóvel: {{ $imovelDecoded }}
                    </h2>
                    <a href="{{ route('admin.marcos.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
                        <i class="fas fa-arrow-left mr-1"></i> Voltar
                    </a>
                </div>
                
                <p class="text-gray-600 mt-2">Visualizando {{ count($marcos) }} marcos encontrados com coordenadas.</p>

                <div id="map"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.9.0/proj4.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var map = L.map('map').setView([-10.0, -67.0], 5); // Fallback center

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        var satLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri'
        });
        
        var baseMaps = {
            "Rua": L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'),
            "Satélite": satLayer
        };
        L.control.layers(baseMaps).addTo(map);

        // Satelite as default to look premium
        satLayer.addTo(map);

        var bounds = L.latLngBounds();
        var marcos = @json($marcos);
        var highlightId = {{ $highlightId ?? 'null' }};
        var highlightMarker = null;

        marcos.forEach(function(marco) {
            var lat = null;
            var lng = null;

            if (marco.latitude && marco.longitude) {
                lat = parseFloat(marco.latitude);
                lng = parseFloat(marco.longitude);
            } else if (marco.easting && marco.northing && marco.meridiano_central) {
                // Convert UTM to LatLng
                var mc = parseFloat(marco.meridiano_central);
                var zone = Math.round((mc + 183) / 6);
                var projString = '+proj=utm +zone=' + zone + ' +south +ellps=GRS80 +towgs84=0,0,0,0,0,0,0 +units=m +no_defs';
                
                try {
                    var coords = proj4(projString, 'WGS84', [parseFloat(marco.easting), parseFloat(marco.northing)]);
                    lng = coords[0];
                    lat = coords[1];
                } catch(e) {
                    console.error("Proj4 conversion error", e);
                }
            }

            if (lat !== null && lng !== null) {
                var point = L.latLng(lat, lng);
                bounds.extend(point);

                var isHighlighted = (marco.id == highlightId);

                var marker = L.circleMarker(point, {
                    radius: isHighlighted ? 8 : 5,
                    fillColor: isHighlighted ? "#10b981" : "#3b82f6", // Green for highlighted, Blue for normal
                    color: "#fff",
                    weight: isHighlighted ? 2 : 1,
                    opacity: 1,
                    fillOpacity: 0.9
                }).addTo(map);
                
                var popupContent = `
                    <div class="p-2">
                        <strong class="text-lg ${isHighlighted ? 'text-green-400' : 'text-blue-400'}">${marco.credencial}-${marco.tipo}-${marco.numero}</strong><br/>
                        <span class="text-sm text-gray-300">Lat: ${lat.toFixed(6)}</span><br/>
                        <span class="text-sm text-gray-300">Lng: ${lng.toFixed(6)}</span>
                    </div>
                `;
                marker.bindPopup(popupContent, {className: 'custom-popup'});

                if (isHighlighted) {
                    highlightMarker = marker;
                }
            }
        });

        if (bounds.isValid()) {
            map.fitBounds(bounds, {padding: [50, 50]});
        }

        if (highlightMarker) {
            // Bring highlighted marker to front and open popup
            highlightMarker.bringToFront();
            setTimeout(() => { highlightMarker.openPopup(); }, 500);
        }
    });
</script>
@endsection
