@extends('layouts.admin')

@push('styles')
    <!-- Leaflet & Proj4 -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.9.0/proj4.js"></script>
    
    <!-- Leaflet Plugins (Fullscreen & Geocoder) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.fullscreen@latest/Control.FullScreen.css" />
    <script src="https://cdn.jsdelivr.net/npm/leaflet.fullscreen@latest/Control.FullScreen.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<style>
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-thumb {
            background: #004A7C;
            border-radius: 999px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
        }

        .glass {
            background: rgba(255,255,255,0.18);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .glass-dark {
            background: rgba(0, 51, 102, 0.55);
            backdrop-filter: blur(18px);
        }

        .animate-fade {
            animation: fade .25s ease;
        }

        @keyframes fade {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endpush

@section('content')
<div class="w-full animate-fade"
         x-data="{ modal:false, modalMapaBusca: false }">

        <!-- HEADER -->
        <div class="flex flex-col xl:flex-row justify-between gap-8 mb-10">

            <!-- TITLE -->
            <div class="bg-white/90 backdrop-blur-md p-6 rounded-2xl border border-slate-200 shadow-sm inline-block w-max pr-12">
                <h2 class="text-3xl font-black text-slate-800 tracking-tight">Painel de Bases</h2>
                <p class="text-slate-600 mt-1 font-medium">Localize e cadastre coordenadas</p>
            </div>

            <!-- BUSCAS -->
            <div class="flex flex-col gap-4 w-full xl:w-auto">

                <!-- Coordenadas -->
                <form action="{{ route('admin.bases.buscar') }}"
                      method="GET"
                      id="searchFormCoords"
                      class="bg-white rounded-3xl p-4 shadow-sm border border-slate-200">

                    <div class="flex flex-col md:flex-row gap-3 items-center">

                        <input type="number"
                               step="any"
                               name="norte"
                               id="inputNorte"
                               placeholder="Coordenada Norte"
                               class="w-full md:w-52 bg-white border border-slate-300 text-slate-800 px-5 py-3 rounded-2xl outline-none placeholder:text-slate-500 font-semibold">

                        <input type="number"
                               step="any"
                               name="este"
                               id="inputEste"
                               placeholder="Coordenada Este"
                               class="w-full md:w-52 bg-white border border-slate-300 text-slate-800 px-5 py-3 rounded-2xl outline-none placeholder:text-slate-500 font-semibold">

                        <div class="flex gap-2 w-full md:w-auto">
                            <button type="submit"
                                    class="flex-1 bg-orange-500 hover:bg-orange-600 text-white font-black uppercase px-6 py-3 rounded-2xl transition shadow-lg whitespace-nowrap">
                                Buscar Próxima
                            </button>

                            <button type="button"
                                    onclick="abrirModalMapa()"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-black uppercase px-5 py-3 rounded-2xl transition shadow-lg whitespace-nowrap flex items-center justify-center gap-2">
                                Mapa
                            </button>
                        </div>

                    </div>
                </form>

                <!-- Nome -->
                <form action="{{ route('admin.bases.buscar') }}"
                      method="GET"
                      class="bg-white rounded-3xl p-4 shadow-sm border border-slate-200">

                    <div class="flex flex-col md:flex-row gap-3">

                        <input type="text"
                               name="nome"
                               placeholder="Pesquisar por nome..."
                               class="flex-1 bg-white border border-slate-300 text-slate-800 px-5 py-3 rounded-2xl outline-none placeholder:text-slate-500 font-semibold">

                        <button type="submit"
                                class="bg-[#004A7C] hover:bg-[#003055] text-white font-black uppercase px-8 py-3 rounded-2xl transition shadow-lg">
                            Buscar
                        </button>

                    </div>
                </form>
            </div>
        </div>

        <!-- CONTAINER -->
        <div class="bg-white rounded-[32px] border border-slate-200 shadow-sm p-6 md:p-10">

            <!-- TOPO -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">

                <div>
                    <h1 class="text-3xl font-black text-slate-800 uppercase italic">
                        Bases Cadastradas
                    </h1>

                    <p class="text-slate-600 mt-1">
                        Gerencie e localize bases cadastradas no sistema.
                    </p>
                </div>

                <button @click="modal=true"
                        class="bg-green-500 hover:bg-green-600 text-white px-8 py-3 rounded-2xl font-black uppercase shadow-xl transition">
                    + Adicionar Base
                </button>
            </div>

            <!-- TABELA -->
            <div class="overflow-x-auto rounded-3xl">

                <table class="w-full border-separate border-spacing-y-3">

                    <thead>
                        <tr class="text-slate-500 uppercase text-xs font-bold border-b border-slate-200 bg-slate-50">
                            <th class="text-left px-6 py-3">Base</th>
                            <th class="text-center px-6 py-3">Norte</th>
                            <th class="text-center px-6 py-3">Este</th>

                            @if(isset($filtroCoordenada) && $filtroCoordenada)
                                <th class="text-center px-6 py-3">Distância</th>
                            @endif

                            <th class="text-center px-6 py-3">Arquivo</th>
                            <th class="text-center px-6 py-3">Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($bases as $base)

                        @php
                            $isDestaque = (isset($destaqueId) && $destaqueId == $base->id);
                        @endphp

                        <tr class="
                            {{ $isDestaque
                                ? 'bg-green-500/20 border border-green-400'
                                : 'hover:bg-slate-50'
                            }}
                            transition rounded-3xl
                        ">

                            <!-- Nome -->
                            <td class="px-6 py-5 rounded-l-3xl">

                                @if($isDestaque)
                                    <span class="text-[11px] font-black text-green-300 uppercase block mb-1">
                                        Mais Próxima
                                    </span>
                                @endif

                                <span class="font-bold text-slate-800 uppercase">
                                    {{ $base->nome }}
                                </span>

                            </td>

                            <!-- Norte -->
                            <td class="text-center text-slate-800 font-semibold px-6">
                                @if(!empty($base->norte_original))
                                    {{ $base->norte_original }}
                                @else
                                    {{ number_format($base->norte, 3, ',', '.') }}
                                @endif
                            </td>

                            <!-- Este -->
                            <td class="text-center text-slate-800 font-semibold px-6">
                                @if(!empty($base->este_original))
                                    {{ $base->este_original }}
                                @else
                                    {{ number_format($base->este, 3, ',', '.') }}
                                @endif
                            </td>

                            <!-- Distância -->
                            @if(isset($filtroCoordenada) && $filtroCoordenada)
                                <td class="text-center text-orange-300 font-black px-6">
                                    {{ number_format($base->distancia, 2, ',', '.') }} m
                                </td>
                            @endif

                            <!-- ZIP -->
                            <td class="text-center px-6">

                                @if($base->arquivo_zip)

                                    <a href="{{ asset('storage/' . $base->arquivo_zip) }}"
                                       download="{{ $base->nome }}.zip"
                                       class="bg-[#004A7C] hover:bg-[#003055] text-white px-5 py-2 rounded-xl text-xs uppercase font-bold transition">
                                        Download
                                    </a>

                                @else
                                    <span class="text-slate-800/30">N/A</span>
                                @endif

                            </td>

                            <!-- AÇÕES -->
                            <td class="text-center px-6 rounded-r-3xl">

                                <div class="flex justify-center gap-2">

                                    @php
                                        $mapUrl = route('admin.bases.mapa', $base->id);
                                        if (request()->filled('norte') && request()->filled('este')) {
                                            $mapUrl .= '?norte=' . urlencode(request('norte')) . '&este=' . urlencode(request('este'));
                                        }
                                    @endphp

                                    <a href="{{ $mapUrl }}"
                                       class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2 rounded-xl text-xs font-black uppercase transition">
                                        Mapa
                                    </a>

                                    @if($base->google_maps_url)
                                        <button type="button"
                                                onclick="shareBaseLocation('{{ $base->nome }}', '{{ $base->google_maps_url }}')"
                                                class="bg-emerald-600 hover:bg-emerald-700 text-slate-800 px-5 py-2 rounded-xl text-xs font-black uppercase transition flex items-center justify-center gap-1.5 shadow-md">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 10.742l4.636-2.318M8.684 13.258l4.636 2.318M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Compartilhar
                                        </button>
                                    @endif

                                    <form action="{{ route('admin.bases.destroy', $base->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Deseja excluir esta base?')">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="bg-red-500 hover:bg-red-600 text-white px-5 py-2 rounded-xl text-xs font-black uppercase transition">
                                            Excluir
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6"
                                class="text-center py-20 text-slate-800/60 uppercase font-bold">
                                Nenhuma base encontrada.
                            </td>
                        </tr>

                    @endforelse

                    </tbody>
                </table>
            </div>

            <div class="mt-6 px-6 py-6 rounded-b-3xl bg-white/10 border-t border-white/20">
                {{ $bases->links('vendor.pagination.topogest') }}
            </div>
        </div>

        <!-- BOTÃO VOLTAR -->
        <div class="mt-6">
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 transition">

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-5 h-5"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M15 19l-7-7 7-7"/>

                </svg>

                Voltar ao painel
            </a>
        </div>

        <!-- MODAL -->
        <div x-show="modal"
             x-transition
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">

            <div class="glass-dark rounded-[35px] w-full max-w-2xl p-8 border border-white/10 shadow-2xl animate-fade">

                <div class="flex justify-between items-center mb-8">

                    <h2 class="text-3xl text-slate-800 font-black uppercase italic">
                        Nova Base
                    </h2>

                    <button @click="modal=false"
                            class="text-slate-800/60 hover:text-slate-800 text-3xl leading-none">
                        ×
                    </button>
                </div>

                <form action="{{ route('admin.bases.store') }}"
                      method="POST"
                      enctype="multipart/form-data"
                      class="space-y-6">

                    @csrf

                    <!-- Nome -->
                    <div>
                        <label class="block text-slate-500 uppercase text-sm font-bold mb-2">
                            Nome da Base
                        </label>

                        <input type="text"
                               name="nome"
                               required
                               class="w-full bg-white text-[#003366] px-5 py-4 rounded-2xl outline-none font-semibold">
                    </div>

                    <!-- Coordenadas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div>
                            <label class="block text-slate-500 uppercase text-sm font-bold mb-2">
                                Norte
                            </label>

                            <input type="number"
                                   step="any"
                                   name="norte"
                                   required
                                   class="w-full bg-white text-[#003366] px-5 py-4 rounded-2xl outline-none font-semibold">
                        </div>

                        <div>
                            <label class="block text-slate-500 uppercase text-sm font-bold mb-2">
                                Este
                            </label>

                            <input type="number"
                                   step="any"
                                   name="este"
                                   required
                                   class="w-full bg-white text-[#003366] px-5 py-4 rounded-2xl outline-none font-semibold">
                        </div>

                    </div>

                    <!-- Upload -->
                    <div>

                        <label class="block text-slate-500 uppercase text-sm font-bold mb-3">
                            Arquivo ZIP
                        </label>

                        <div class="border-2 border-dashed border-white/20 rounded-3xl p-8 text-center hover:bg-white/5 relative">

                            <label class="relative w-full h-full flex items-center justify-center cursor-pointer">
                                <div class="pointer-events-none text-slate-800">
                                    Clique para selecionar um arquivo .zip ou arraste aqui
                                </div>

                                <input type="file"
                                       name="arquivo_zip"
                                       accept=".zip"
                                       required
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            </label>

                        </div>
                    </div>

                    <!-- BOTÕES -->
                    <div class="flex flex-col md:flex-row gap-4 pt-4">

                        <button type="submit"
                                class="flex-1 bg-green-500 hover:bg-green-600 text-white py-4 rounded-2xl font-black uppercase transition shadow-lg">
                            Salvar Base
                        </button>

                        <button type="button"
                                @click="modal=false"
                                class="flex-1 bg-red-500 hover:bg-red-600 text-white py-4 rounded-2xl font-black uppercase transition shadow-lg">
                            Cancelar
                        </button>

                    </div>

                </form>

            </div>
        </div>
    </div>

        <!-- MODAL DE BUSCA POR MAPA -->
        <div id="modalMapaBusca"
             style="display: none;"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300">

            <div id="modalMapaBuscaContent" 
                 class="glass-dark rounded-[35px] w-full max-w-5xl p-6 md:p-8 border border-white/10 shadow-2xl flex flex-col h-[85vh] scale-95 transition-all duration-300">

                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-3xl text-slate-800 font-black uppercase italic">
                            Selecionar Ponto no Mapa
                        </h2>
                        <p class="text-slate-600 mt-1">Clique em qualquer local no mapa para inserir o alfinete e localizar as bases mais próximas.</p>
                    </div>

                    <button type="button" onclick="fecharModalMapa()"
                            class="text-slate-800/60 hover:text-slate-800 text-4xl leading-none transition">
                        &times;
                    </button>
                </div>

                <div class="flex-1 w-full rounded-2xl overflow-hidden border-2 border-white/20 shadow-inner relative bg-gray-900">
                    <div id="mapBusca" class="absolute inset-0 w-full h-full z-10">
                        <button type="button" 
                                id="btnFloatingSearch" 
                                onclick="event.preventDefault(); document.getElementById('btnBuscarPorPino').click();" 
                                class="absolute bottom-8 right-8 z-[9999] bg-green-500 hover:bg-green-600 text-white font-black uppercase px-6 py-4 rounded-xl border-[3px] border-white shadow-2xl transition-transform hover:scale-105 hidden"
                                style="pointer-events: auto;">
                            🔍 PESQUISAR ESTE LOCAL
                        </button>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-end items-center gap-4 mt-6">
                    
                    <div class="flex-1 w-full sm:w-auto text-slate-800/80 font-mono text-sm bg-black/30 px-4 py-3 rounded-xl border border-white/10 text-center sm:text-left">
                        <span id="coordsDisplay">Aguardando seleção no mapa...</span>
                    </div>

                    <div class="flex w-full sm:w-auto gap-3">
                        <button type="button"
                                onclick="fecharModalMapa()"
                                class="flex-1 sm:flex-none bg-red-500 hover:bg-red-600 text-white px-8 py-3 rounded-2xl font-black uppercase transition shadow-lg">
                            Cancelar
                        </button>

                        <button type="button"
                                id="btnBuscarPorPino"
                                disabled
                                class="flex-1 sm:flex-none bg-green-500 hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed text-slate-800 px-8 py-3 rounded-2xl font-black uppercase transition shadow-lg">
                            Pesquisar
                        </button>
                    </div>

                </div>
            </div>
        </div>
@endsection

@push('scripts')
<script>
        let mapBusca = null;
        let pinoBusca = null;
        let selectedLat = null;
        let selectedLng = null;

        function abrirModalMapa() {
            let modal = document.getElementById('modalMapaBusca');
            let content = document.getElementById('modalMapaBuscaContent');
            
            modal.style.display = 'flex';
            
            setTimeout(() => {
                modal.classList.remove('opacity-0', 'pointer-events-none');
                content.classList.remove('scale-95');
                content.classList.add('scale-100');
                
                setTimeout(() => initMapBusca(), 300);
            }, 10);
        }

        function fecharModalMapa() {
            let modal = document.getElementById('modalMapaBusca');
            let content = document.getElementById('modalMapaBuscaContent');
            
            modal.classList.add('opacity-0', 'pointer-events-none');
            content.classList.remove('scale-100');
            content.classList.add('scale-95');
            
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        function initMapBusca() {
            if (!mapBusca) {
                mapBusca = L.map('mapBusca', {
                    fullscreenControl: true,
                    fullscreenControlOptions: {
                        position: 'topleft'
                    }
                }).setView([-9.974, -67.807], 8); // Padrão: Acre
                
                // Camada Híbrida do Google (Satélite + Ruas)
                L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    subdomains:['mt0','mt1','mt2','mt3'],
                    attribution: '© Google Maps'
                }).addTo(mapBusca);
                
                // Barra de Pesquisa (Geocoder)
                L.Control.geocoder({
                    defaultMarkGeocode: false,
                    placeholder: "Buscar cidade (ex: Rio Branco)..."
                })
                .on('markgeocode', function(e) {
                    // Centraliza o mapa no local pesquisado
                    mapBusca.fitBounds(e.geocode.bbox);
                })
                .addTo(mapBusca);
                
                mapBusca.on('click', function(e) {
                    selectedLat = e.latlng.lat;
                    selectedLng = e.latlng.lng;
                    
                    if(pinoBusca) {
                        pinoBusca.setLatLng(e.latlng);
                    } else {
                        pinoBusca = L.marker(e.latlng).addTo(mapBusca);
                    }
                    
                    document.getElementById('coordsDisplay').innerText = `Lat: ${selectedLat.toFixed(6)} | Lng: ${selectedLng.toFixed(6)}`;
                    document.getElementById('btnBuscarPorPino').disabled = false;
                    
                    // Mostra o botão flutuante DOM nativo
                    let btnFloat = document.getElementById('btnFloatingSearch');
                    if(btnFloat) btnFloat.classList.remove('hidden');
                });
                
            } else {
                mapBusca.invalidateSize();
            }
        }

        document.getElementById('btnBuscarPorPino').addEventListener('click', function() {
            if(selectedLat === null || selectedLng === null) return;
            
            // Determinar a Zona UTM baseada na longitude
            let zone = Math.floor((selectedLng + 180) / 6) + 1;
            let isSouth = selectedLat < 0;
            
            // Definição Proj4 para UTM (WGS84)
            let projString = `+proj=utm +zone=${zone} ${isSouth ? '+south' : ''} +datum=WGS84 +units=m +no_defs`;
            
            try {
                let utm = proj4('EPSG:4326', projString, [selectedLng, selectedLat]);
                let este = utm[0];
                let norte = utm[1];
                
                // Preencher o formulário
                document.getElementById('inputNorte').value = norte.toFixed(3);
                document.getElementById('inputEste').value = este.toFixed(3);
                
                // Submeter form
                document.getElementById('searchFormCoords').submit();
                
            } catch (err) {
                console.error("Erro na conversão Proj4js", err);
                Swal.fire('Erro', 'Não foi possível converter a coordenada. Tente novamente.', 'error');
            }
        });

        function shareBaseLocation(nome, url) {
            let text = 'Confira a localização da base "' + nome + '" no Google Maps: ' + url;
            let whatsappUrl = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(text);
            window.open(whatsappUrl, '_blank');
        }
    </script>
@endpush
