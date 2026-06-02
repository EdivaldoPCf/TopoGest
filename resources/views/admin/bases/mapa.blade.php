<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa da Base - {{ $base->nome }}</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-thumb {
            background: #00E500;
            border-radius: 999px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.08);
        }

        .glass {
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .leaflet-popup-content-wrapper {
            border-radius: 18px;
            background: #003366;
            color: white;
            font-weight: bold;
        }

        .leaflet-popup-tip {
            background: #003366;
        }

        .leaflet-control-zoom a {
            background: #003366 !important;
            color: white !important;
            border: none !important;
        }

        #map {
            width: 100%;
            min-height: 600px !important;
            height: 100% !important;
        }

        .distance-label {
            background: rgba(0, 0, 0, 0.65);
            color: #fff;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid rgba(255,255,255,0.12);
            box-shadow: 0 0 10px rgba(0,0,0,0.4);
        }

        .animate-fade {
            animation: fade .35s ease;
        }

        @keyframes fade {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">
</head>

<body class="min-h-screen overflow-hidden bg-[#071018] text-white">

    <!-- BACKGROUND -->
    <div class="fixed inset-0 z-0">
        <img src="{{ asset('images/background-topo.jpg') }}"
             class="w-full h-full object-cover">

        <div class="absolute inset-0 bg-black/55"></div>
    </div>

    <div class="relative z-10 h-screen p-4 md:p-6 flex flex-col gap-5 animate-fade">

        <!-- TOPO -->
        <div class="glass rounded-[28px] border border-white/10 p-5 shadow-2xl">

            <div class="flex flex-col lg:flex-row justify-between gap-5">

                <!-- INFO -->
                <div class="flex items-center gap-5">

                    <!-- ÍCONE -->
                    <div class="w-16 h-16 rounded-2xl bg-[#00E500]/20 border border-[#00E500]/30 flex items-center justify-center shadow-lg">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="w-8 h-8 text-[#00E500]"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 01.553-.894l5-2.5a1 1 0 01.894 0l6 3a1 1 0 010 1.788l-6 3a1 1 0 01-.894 0L5 8.118v7.764l4 2V20z"/>

                        </svg>

                    </div>

                    <!-- DADOS -->
                    <div>

                        <h1 class="text-2xl md:text-3xl font-black uppercase italic text-[#00E500] tracking-tight">
                            {{ $base->nome }}
                        </h1>

                        <div class="flex flex-col md:flex-row gap-2 md:gap-6 mt-2 text-sm text-white/70">

                            <span>
                                <strong class="text-white">Norte:</strong>
                                {{ number_format($base->norte, 3, ',', '.') }}
                            </span>

                            <span>
                                <strong class="text-white">Este:</strong>
                                {{ number_format($base->este, 3, ',', '.') }}
                            </span>

                            @if($base->latitude && $base->longitude)
                                <span>
                                    <strong class="text-white">Lat/Lng:</strong>
                                    {{ $base->latitude }}, {{ $base->longitude }}
                                </span>
                            @endif

                        </div>

                    </div>

                </div>

                <!-- BOTÕES -->
                <div class="flex items-center gap-3">

                    @if($base->google_maps_url)
                        <button onclick="shareBaseLocation('{{ $base->nome }}', '{{ $base->google_maps_url }}')"
                                class="bg-emerald-600 hover:bg-emerald-700 transition px-5 py-3 rounded-2xl font-black uppercase text-xs shadow-lg flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 10.742l4.636-2.318M8.684 13.258l4.636 2.318M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Compartilhar
                        </button>
                    @endif

                    <button onclick="toggleFullscreen()"
                            class="bg-[#003366] hover:bg-[#004A7C] transition px-5 py-3 rounded-2xl font-black uppercase text-xs shadow-lg">

                        Tela Cheia

                    </button>

                    <a href="{{ route('admin.bases.index') }}"
                       class="bg-red-600 hover:bg-red-700 transition px-6 py-3 rounded-2xl font-black uppercase text-xs shadow-lg">

                        Voltar

                    </a>

                </div>

            </div>

        </div>

        <!-- MAPA -->
        <div class="relative flex-1 overflow-hidden rounded-[35px] border border-white/10 shadow-2xl">

            <!-- CARD INFO -->
            <div class="absolute top-5 left-5 z-[1000] glass rounded-2xl border border-white/10 p-4 shadow-xl max-w-xs">

                <div class="flex items-center gap-3 mb-3">

                    <div class="w-3 h-3 rounded-full bg-green-400 animate-pulse"></div>

                    <span class="uppercase text-xs font-black tracking-widest text-white/70">
                        Base Localizada
                    </span>

                </div>

                <h3 class="font-black text-lg uppercase text-[#00E500]">
                    {{ $base->nome }}
                </h3>

                <p class="text-sm text-white/70 mt-2 leading-relaxed">
                    Visualização geográfica da base topográfica cadastrada no sistema.
                </p>

                @if($base->google_maps_url)
                    <div class="mt-4">
                        <button onclick="shareBaseLocation('{{ $base->nome }}', '{{ $base->google_maps_url }}')"
                                class="w-full bg-emerald-600 hover:bg-emerald-700 transition py-2.5 rounded-xl font-bold uppercase text-xs shadow-md flex items-center justify-center gap-1.5 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 10.742l4.636-2.318M8.684 13.258l4.636 2.318M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Compartilhar Base
                        </button>
                    </div>
                @endif

                @if(isset($searchNorte) && isset($searchEste) && $searchNorte !== null && $searchEste !== null)
                    <div class="mt-4 rounded-2xl border border-white/10 bg-[#00182f]/80 p-3 text-sm text-white/80">
                        <div class="font-bold text-white uppercase text-xs tracking-widest mb-2">Ponto da Busca</div>
                        <div>Norte (UTM): {{ $searchNorte }}</div>
                        <div>Este (UTM): {{ $searchEste }}</div>
                        @if(isset($searchLat) && isset($searchLng) && is_numeric($searchLat) && is_numeric($searchLng))
                            <div class="mt-2 text-xs text-white/60">
                                Convertido para Lat/Lng: {{ number_format($searchLat, 6, ',', '.') }}, {{ number_format($searchLng, 6, ',', '.') }}
                            </div>
                        @else
                            <div class="mt-2 text-xs text-red-300">
                                Conversão UTM inválida para Lat/Lng.
                            </div>
                        @endif
                        <div class="text-xs text-white/60 mt-1">O mapa mostra o ponto de busca em UTM e a base mais próxima encontrada.</div>
                    </div>
                @endif

                <!-- Top 3 Bases Mais Próximas -->
                @if(isset($basesProximas) && $basesProximas->count() > 0)
                    <div class="mt-4 pt-4 border-t border-white/10">
                        <div class="font-bold text-[#38bdf8] uppercase text-[11px] tracking-widest mb-3 flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#38bdf8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Top 3 Mais Próximas
                        </div>
                        <div class="space-y-2 max-h-[220px] overflow-y-auto pr-1">
                            @foreach($basesProximas as $index => $prox)
                                <div class="group cursor-pointer rounded-xl bg-white/5 hover:bg-white/10 border border-white/5 p-2.5 transition"
                                     onclick="focusProxBase({{ $index }})">
                                    <div class="flex justify-between items-start gap-1">
                                        <span class="font-bold text-xs text-white uppercase truncate max-w-[130px] group-hover:text-[#38bdf8] transition">
                                            {{ $prox->nome }}
                                        </span>
                                        <span class="text-[9px] font-black text-[#38bdf8] bg-[#38bdf8]/10 px-2 py-0.5 rounded-full whitespace-nowrap">
                                            {{ number_format($prox->distancia, 1, ',', '.') }} m
                                        </span>
                                    </div>
                                    <div class="text-[9px] text-white/50 mt-1 flex justify-between">
                                        <span>N: {{ number_format($prox->norte, 1, ',', '.') }}</span>
                                        <span>E: {{ number_format($prox->este, 1, ',', '.') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

            <!-- MAP -->
            <div id="map" class="w-full h-full"></div>

        </div>

    </div>

    <script>

        try {
            function parseCoord(value, fallback) {
                if (value === null || value === undefined) return fallback;
                // Ensure we're working with a string, replace comma decimal (Brazilian) with dot
                var s = String(value).replace(',', '.');
                var n = parseFloat(s);
                return (Number.isFinite(n) ? n : fallback);
            }

            var lat = parseCoord(@json($baseLat ?? null), -9.974);
            var lng = parseCoord(@json($baseLng ?? null), -67.807);
            var baseName = @json($base->nome);
            var baseNorte = @json($base->norte);
            var baseEste = @json($base->este);
            var searchNorte = @json($searchNorte ?? null);
            var searchEste = @json($searchEste ?? null);
            var searchLat = @json($searchLat ?? null);
            var searchLng = @json($searchLng ?? null);
            var basesProximas = @json($basesProximas ?? []);
            var proxMarkers = [];

            function isValidLatLng(value, min, max) {
                return typeof value === 'number' && Number.isFinite(value) && value >= min && value <= max;
            }

            console.group('Getec Topografia Map Debug');
            console.log('map script loaded');
            console.log('map element', document.getElementById('map'));
            console.log('Leaflet loaded', typeof L !== 'undefined', typeof L);
            console.log('input coords', { lat: lat, lng: lng, searchLat: searchLat, searchLng: searchLng });

            if (!isValidLatLng(lat, -90, 90) || !isValidLatLng(lng, -180, 180)) {
                console.warn('Invalid base lat/lng, using fallback', { lat: lat, lng: lng });
                lat = -9.974;
                lng = -67.807;
            }

            var mapEl = document.getElementById('map');
            if (!mapEl) {
                throw new Error('Map container #map not found');
            }

            var map = L.map('map').setView([lat, lng], 16);

            var satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: '\u00A9 Esri — Source: Esri, Maxar, Earthstar Geographics, and the GIS User Community'
            });

            var osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '\u00A9 OpenStreetMap contributors'
            });

            satelliteLayer.addTo(map);
            L.control.layers({
                'Satélite': satelliteLayer,
                'OpenStreetMap': osmLayer
            }).addTo(map);

            setTimeout(function() {
                console.log('Invalidating Leaflet size');
                map.invalidateSize();
            }, 200);

            // Destacar a base localizada (Verde, grande e piscante)
            var customIcon = L.divIcon({
                html: '<div class="relative"><div style="width:22px;height:22px;background:#00E500;border:4px solid white;border-radius:999px;box-shadow:0 0 20px rgba(0,229,0,.8);"></div><div style="position:absolute;inset:0;border-radius:999px;border:2px solid rgba(0,229,0,.5);animation:pulse 2s infinite;"></div></div>',
                className: '',
                iconSize: [22, 22],
                iconAnchor: [11, 11]
            });

            var marker = L.marker([lat, lng], { icon: customIcon }).addTo(map);
            marker.bindPopup('<div style="min-width:180px"><div style="font-size:16px;font-weight:900;margin-bottom:8px;color:#00E500">' + baseName + '</div><div style="font-size:13px;line-height:1.6"><strong>N:</strong> ' + baseNorte + '<br><strong>E:</strong> ' + baseEste + '</div></div>').openPopup();

            L.circle([lat, lng], { color: '#00E500', fillColor: '#00E500', fillOpacity: 0.15, radius: 60 }).addTo(map);

            var searchLatFloat = parseCoord(searchLat, NaN);
            var searchLngFloat = parseCoord(searchLng, NaN);
            var hasSearchPoint = isValidLatLng(searchLatFloat, -90, 90) && isValidLatLng(searchLngFloat, -180, 180);
            console.log('search point valid', hasSearchPoint, { searchLatFloat: searchLatFloat, searchLngFloat: searchLngFloat });

            var centerLat = hasSearchPoint ? searchLatFloat : lat;
            var centerLng = hasSearchPoint ? searchLngFloat : lng;

            if (hasSearchPoint) {
                var searchIcon = L.divIcon({
                    html: '<div class="relative"><div style="width:18px;height:18px;background:#ff8c00;border:3px solid white;border-radius:999px;box-shadow:0 0 18px rgba(255,140,0,.85);"></div></div>',
                    className: '',
                    iconSize: [18, 18],
                    iconAnchor: [9, 9]
                });

                var searchMarker = L.marker([searchLatFloat, searchLngFloat], { icon: searchIcon }).addTo(map);
                searchMarker.bindPopup('<div style="min-width:180px"><div style="font-size:16px;font-weight:900;margin-bottom:8px;color:#ff8c00">Ponto de Busca</div><div style="font-size:13px;line-height:1.6"><strong>Lat:</strong> ' + searchLatFloat.toFixed(6) + '<br><strong>Lng:</strong> ' + searchLngFloat.toFixed(6) + '</div></div>');
                L.circle([searchLatFloat, searchLngFloat], { color: '#ff8c00', fillColor: '#ff8c00', fillOpacity: 0.12, radius: 40 }).addTo(map);

                var connectionLine = L.polyline([[lat, lng], [searchLatFloat, searchLngFloat]], {
                    color: '#ff8c00',
                    weight: 3,
                    opacity: 0.85,
                    dashArray: '8, 6'
                }).addTo(map);

                var distanceMeters = L.latLng(lat, lng).distanceTo(L.latLng(searchLatFloat, searchLngFloat));
                var distanceText = distanceMeters >= 1000
                    ? (distanceMeters / 1000).toFixed(2) + ' km'
                    : distanceMeters.toFixed(2) + ' m';

                connectionLine.bindTooltip(distanceText, {
                    permanent: true,
                    direction: 'center',
                    className: 'distance-label',
                    offset: [0, 0]
                }).openTooltip();
            }

            // Adicionar marcadores e linhas para as bases mais próximas (em azul)
            basesProximas.forEach(function(prox, index) {
                var pLat = parseCoord(prox.lat_resolvido, NaN);
                var pLng = parseCoord(prox.lng_resolvido, NaN);
                if (isValidLatLng(pLat, -90, 90) && isValidLatLng(pLng, -180, 180)) {
                    var proxIcon = L.divIcon({
                        html: '<div class="relative"><div style="width:16px;height:16px;background:#38bdf8;border:3px solid white;border-radius:999px;box-shadow:0 0 15px rgba(56,189,248,.85);"></div></div>',
                        className: '',
                        iconSize: [16, 16],
                        iconAnchor: [8, 8]
                    });

                    var pMarker = L.marker([pLat, pLng], { icon: proxIcon }).addTo(map);
                    
                    var popupHtml = '<div style="min-width:180px">' +
                                    '<div style="font-size:14px;font-weight:900;margin-bottom:6px;color:#38bdf8">' + prox.nome + '</div>' +
                                    '<div style="font-size:12px;line-height:1.5">' +
                                    '<strong>Distância:</strong> ' + parseFloat(prox.distancia).toFixed(1).replace('.', ',') + ' m<br>' +
                                    '<strong>N:</strong> ' + prox.norte + '<br>' +
                                    '<strong>E:</strong> ' + prox.este + '</div>' +
                                    '<a href="/admin/bases/' + prox.id + '/mapa' + (searchNorte && searchEste ? '?norte=' + encodeURIComponent(searchNorte) + '&este=' + encodeURIComponent(searchEste) : '') + '" style="display:inline-block;margin-top:8px;background:#38bdf8;color:#000;font-size:10px;font-weight:black;padding:5px 10px;border-radius:6px;text-transform:uppercase;text-decoration:none">Ver esta base</a>' +
                                    '</div>';
                                    
                    pMarker.bindPopup(popupHtml);
                    proxMarkers[index] = pMarker;

                    var pLine = L.polyline([[centerLat, centerLng], [pLat, pLng]], {
                        color: '#38bdf8',
                        weight: 2,
                        opacity: 0.6,
                        dashArray: '5, 5'
                    }).addTo(map);

                    var pDist = L.latLng(centerLat, centerLng).distanceTo(L.latLng(pLat, pLng));
                    var pDistText = pDist >= 1000
                        ? (pDist / 1000).toFixed(2) + ' km'
                        : pDist.toFixed(1) + ' m';

                    pLine.bindTooltip(pDistText, {
                        permanent: false,
                        direction: 'center',
                        className: 'distance-label',
                        offset: [0, 0]
                    });
                }
            });

            // Enquadrar todos os pontos importantes na tela
            var groupPoints = [];
            groupPoints.push([lat, lng]);
            if (hasSearchPoint) {
                groupPoints.push([searchLatFloat, searchLngFloat]);
            }
            basesProximas.forEach(function(prox) {
                var pLat = parseCoord(prox.lat_resolvido, NaN);
                var pLng = parseCoord(prox.lng_resolvido, NaN);
                if (isValidLatLng(pLat, -90, 90) && isValidLatLng(pLng, -180, 180)) {
                    groupPoints.push([pLat, pLng]);
                }
            });

            if (groupPoints.length > 1) {
                var bounds = L.latLngBounds(groupPoints);
                map.fitBounds(bounds.pad(0.2));
            }

            // Expor função para focar nas bases próximas globalmente
            window.focusProxBase = function(index) {
                if (proxMarkers[index]) {
                    proxMarkers[index].openPopup();
                    map.setView(proxMarkers[index].getLatLng(), 16);
                }
            };

            console.groupEnd();
        } catch (error) {
            console.error('Erro ao inicializar o mapa:', error);
            console.error('Leaflet object:', window.L);
            console.error('Map element:', document.getElementById('map'));
        }

        function toggleFullscreen() {
            var elem = document.documentElement;
            if (!document.fullscreenElement) {
                elem.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        }

        function shareBaseLocation(nome, url) {
            if (navigator.share) {
                navigator.share({
                    title: 'Localização da Base: ' + nome,
                    text: 'Confira a localização da base "' + nome + '" no Google Maps.',
                    url: url
                }).catch(err => {
                    console.log('Erro ao compartilhar:', err);
                });
            } else {
                navigator.clipboard.writeText(url).then(() => {
                    Swal.fire({
                        title: 'Link Copiado!',
                        text: 'O link do Google Maps para a base "' + nome + '" foi copiado para a área de transferência.',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 2000,
                        background: '#003366',
                        color: '#fff',
                        borderRadius: 20
                    });
                    setTimeout(() => {
                        window.open(url, '_blank');
                    }, 1000);
                }).catch(err => {
                    window.open(url, '_blank');
                });
            }
        }

    </script>

</body>
</html>