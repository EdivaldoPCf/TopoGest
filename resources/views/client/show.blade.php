<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pasta->nome }} - Getec Topografia</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Leaflet.js — Mapa Interativo -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- proj4js — Conversão UTM → WGS84 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.9.0/proj4.js"></script>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(0, 51, 102, 0.7);
            border-radius: 999px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .glass {
            background: rgba(255,255,255,0.82);
            backdrop-filter: blur(18px);
        }

        .animate-fade {
            animation: fadeIn .25s ease;
        }

        .modal-overlay {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity .2s ease, visibility .2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
            padding: 1rem;
        }

        .modal-overlay.open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .modal-container {
            width: 100%;
            max-width: 40rem;
            max-height: calc(100vh - 2rem);
            overflow-y: auto;
        }

        @media (max-width: 768px) {
            .modal-overlay {
                align-items: flex-start;
                padding-top: 2rem;
                padding-bottom: 2rem;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media print {
            body {
                background: white !important;
                color: black !important;
                font-family: Arial, sans-serif !important;
            }
            body > *:not(#sigef-mapa-section) {
                display: none !important;
            }
            .fixed, .modal-overlay, #sigef-mapa-section header, #sigef-mapa-section .px-6, #sigef-mapa-section #sigef-footer, .flex.gap-2, button, a {
                display: none !important;
            }
            #sigef-mapa-section {
                display: block !important;
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                height: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
                background: white !important;
            }
            #leaflet-mapa {
                width: 100% !important;
                height: 550px !important;
                border: 2px solid black !important;
            }
            #sigef-meta {
                display: grid !important;
                grid-template-columns: repeat(4, 1fr) !important;
                background: #f3f4f6 !important;
                border: 2px solid black !important;
                border-top: none !important;
                padding: 15px !important;
                color: black !important;
                margin-bottom: 20px !important;
            }
            #sigef-meta div p {
                color: black !important;
            }
            #print-table-section {
                display: block !important;
                page-break-inside: avoid !important;
            }
        }
        #print-table-section {
            display: none;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-100 overflow-auto">

    <!-- Background -->
    <div class="fixed inset-0 z-0">
        <img
            src="{{ asset('images/background-topo.jpg') }}"
            alt="Background"
            class="w-full h-full object-cover"
        >
        <div class="absolute inset-0 bg-[#001C36]/40 backdrop-[2px]"></div>
    </div>

    <div class="relative z-10 min-h-screen px-4 md:px-8 py-6">

        <!-- HEADER -->
        <header class="max-w-7xl mx-auto mb-8">

            <div class="glass border border-white/40 rounded-[32px] shadow-2xl px-6 py-5">

                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

                    <!-- Logo -->
                    <div class="flex items-center gap-4">

                        <a href="{{ route('dashboard') }}"
                           class="flex items-center gap-4 group">

                            <div class="relative flex items-center justify-center w-16 h-16 rounded-2xl bg-white shadow-2xl transition group-hover:scale-105">
                                <img
                                    src="{{ asset('images/logo-icon.png') }}"
                                    alt="Logo"
                                    class="w-full h-full object-contain p-2"
                                >
                            </div>

                            <div class="hidden sm:flex relative items-center h-12 px-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                                <img
                                    src="{{ asset('images/logo-text.png') }}"
                                    alt="Getec Topografia"
                                    class="relative z-10 h-7 w-auto"
                                >
                            </div>
                        </a>

                    </div>

                    <!-- Pasta -->
                    <div class="flex justify-center">
                        <div class="bg-[#003366] text-white px-8 md:px-14 py-3 rounded-full shadow-xl border border-white/10">
                            <h1 class="text-sm md:text-lg font-black uppercase italic tracking-wide text-center">
                                {{ $pasta->nome }}
                            </h1>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-4">

                        <!-- Notifications -->
                        <a href="{{ route('notificacoes.index') }}"
                           class="relative w-12 h-12 rounded-full bg-[#003366] text-white flex items-center justify-center shadow-xl hover:bg-blue-900 transition border border-white/10">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-5 w-5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                                />
                            </svg>

                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="absolute top-1 right-1 w-3 h-3 bg-red-500 rounded-full border-2 border-[#003366] animate-pulse"></span>
                            @endif
                        </a>

                        <!-- User -->
                        <a href="{{ route('profile.edit') }}"
                           class="flex items-center gap-3 bg-[#003366] text-white rounded-2xl px-4 py-2 shadow-xl border border-white/10 hover:bg-[#002244] transition">

                            <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-white bg-[#004A7C] flex items-center justify-center">

                                @if(auth()->user()->photo)
                                    <img
                                        src="{{ asset('storage/' . auth()->user()->photo) }}"
                                        alt="Perfil"
                                        class="w-full h-full object-cover"
                                    >
                                @else
                                    <span class="font-black uppercase text-sm">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                                    </span>
                                @endif

                            </div>

                            <div class="hidden md:flex flex-col leading-tight">
                                <span class="text-[10px] uppercase tracking-widest opacity-70">
                                    Usuário
                                </span>

                                <span class="font-black uppercase text-sm">
                                    {{ explode(' ', Auth::user()->name)[0] }}
                                </span>
                            </div>

                        </a>

                    </div>

                </div>

            </div>

        </header>

        <!-- MAIN -->
        <main class="max-w-7xl mx-auto flex-1 mt-8">

                <!-- CONTENT AREA -->
                <section class="w-full space-y-8">

                    @php
                        $currentPasta = $pasta;
                        $codigoSigef = null;
                        while ($currentPasta) {
                            if ($currentPasta->codigo_sigef) {
                                $codigoSigef = $currentPasta->codigo_sigef;
                                break;
                            }
                            $currentPasta = $currentPasta->parent;
                        }
                    @endphp

                    @if($codigoSigef)
                    <!-- CERTIFICAÇÃO SIGEF (INCRA) -->
                    <div class="glass rounded-[36px] border border-white/40 shadow-2xl p-6 md:p-8 animate-fade">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                            <div class="flex items-start gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center text-3xl shadow-inner border border-blue-100 shrink-0">
                                    🌐
                                </div>
                                <div>
                                    <div class="flex items-center gap-3">
                                        <span class="uppercase tracking-[3px] text-xs text-[#003366] font-black">Certificação SIGEF</span>
                                        <span class="bg-green-100 text-green-800 text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase border border-green-200">Certificado</span>
                                    </div>
                                    <h2 class="text-xl md:text-2xl font-black text-[#003366] mt-1">
                                        Imóvel Certificado no SIGEF
                                    </h2>
                                    <p class="text-xs text-gray-500 font-mono mt-1 select-all">
                                        Código: {{ $codigoSigef }}
                                    </p>
                                </div>
                            </div>
                            
                            <a href="https://sigef.incra.gov.br/geo/parcela/detalhe/{{ $codigoSigef }}/" 
                               target="_blank" 
                               class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-2xl font-black uppercase text-xs tracking-wider shadow-lg transition flex items-center gap-2 self-start sm:self-auto">
                                <span>Ver Parcela</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    @endif

                    <!-- PASTAS (SUBPASTAS) -->
                    @php
                        $subpastasExibiveis = $pasta->subpastas->filter(fn($s) => !$s->oculto);
                    @endphp
                    @if($subpastasExibiveis->isNotEmpty())
                    <div class="glass rounded-[36px] border border-white/40 shadow-2xl p-6 md:p-8 animate-fade">

                        <div class="flex items-center justify-between mb-6 border-b border-[#003366]/10 pb-4">
                            <div class="bg-[#003366] text-white px-6 py-2 rounded-full shadow border border-white/10">
                                <span class="font-black uppercase italic tracking-wider text-xs">
                                    Subpastas
                                </span>
                            </div>
                            <div class="text-[#003366] font-bold text-xs">
                                {{ $subpastasExibiveis->count() }} pasta(s)
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($subpastasExibiveis as $sub)
                                <div class="bg-white/90 border border-white/50 rounded-3xl p-5 shadow-lg flex flex-col justify-between hover:scale-[1.01] transition">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-yellow-100 flex items-center justify-center text-2xl shrink-0">
                                            📁
                                        </div>
                                        <div class="min-w-0">
                                            <h3 class="font-black text-[#003366] uppercase text-sm truncate leading-snug">
                                                {{ $sub->nome }}
                                            </h3>
                                            <p class="text-xs text-gray-400 mt-0.5">
                                                Criada em {{ $sub->created_at->format('d/m/Y') }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-end mt-4 pt-4 border-t border-gray-100">
                                        <a href="{{ route('client.servico.show', $sub->id) }}"
                                           class="bg-[#003366] hover:bg-blue-900 text-white px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition flex items-center gap-1 shadow">
                                            <span>Abrir</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                    @endif

                    <!-- ARQUIVOS E PENDÊNCIAS -->
                    <div class="glass rounded-[36px] border border-white/40 shadow-2xl p-6 md:p-8 animate-fade">

                        <div class="flex items-center justify-between mb-6 border-b border-[#003366]/10 pb-4">
                            <div class="bg-[#003366] text-white px-6 py-2 rounded-full shadow border border-white/10">
                                <span class="font-black uppercase italic tracking-wider text-xs">
                                    Arquivos e Pendências
                                </span>
                            </div>
                            <div class="text-[#003366] font-bold text-xs">
                                {{ $pasta->arquivos->filter(fn($a) => strtolower($a->tipo) !== 'ods' && !$a->oculto)->count() }} arquivo(s) • {{ $pasta->pendencias->count() }} pendência(s)
                            </div>
                        </div>

                        <div class="space-y-6">

                            <!-- PENDÊNCIAS -->
                            @if($pasta->pendencias->isNotEmpty())
                                <div class="space-y-4">
                                    <h4 class="text-xs uppercase tracking-wider font-bold text-yellow-700">Pendentes de Ação</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach($pasta->pendencias as $pen)
                                            <div class="bg-yellow-50 border border-yellow-200 rounded-3xl p-5 flex flex-col justify-between shadow">
                                                <div>
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <span class="text-lg">⚠️</span>
                                                        <h5 class="font-black text-yellow-800 uppercase italic text-sm">
                                                            {{ $pen->titulo }}
                                                        </h5>
                                                    </div>
                                                    <p class="text-xs text-yellow-700/80 leading-relaxed">
                                                        {{ $pen->descricao }}
                                                    </p>
                                                </div>
                                                <div class="flex items-center justify-between gap-4 mt-6 pt-4 border-t border-yellow-200/50">
                                                    <span class="bg-yellow-100 text-yellow-800 text-[9px] font-bold px-2 py-0.5 rounded-full uppercase border border-yellow-200">
                                                        Aguardando Envio
                                                    </span>
                                                    <button
                                                        onclick="openUploadModal('{{ $pen->titulo }}')"
                                                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-1.5 rounded-xl font-bold uppercase text-[10px] tracking-wider transition shadow"
                                                    >
                                                        Enviar Arquivo
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- ARQUIVOS -->
                            <div class="space-y-4">
                                <h4 class="text-xs uppercase tracking-wider font-bold text-[#003366]/70">Documentos Disponíveis</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    @forelse($pasta->arquivos->filter(fn($a) => strtolower($a->tipo) !== 'ods' && !$a->oculto) as $arq)
                                        <div class="bg-white/95 border border-white/60 rounded-3xl p-5 shadow-lg flex flex-col justify-between hover:scale-[1.01] transition">
                                            <div class="flex items-start gap-4 mb-4">
                                                <div class="w-12 h-12 rounded-2xl bg-[#003366]/10 flex items-center justify-center text-2xl shrink-0">
                                                    📄
                                                </div>
                                                <div class="min-w-0">
                                                    <h5 class="font-black text-[#003366] uppercase text-sm truncate leading-snug">
                                                        {{ $arq->nome }}
                                                    </h5>
                                                    <p class="text-[10px] text-gray-400 mt-1 uppercase font-bold">
                                                        {{ $arq->tipo }} • {{ $arq->tamanho }} MB
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gray-100">
                                                <button
                                                    type="button"
                                                    onclick="openPreviewModal('{{ asset('storage/'.$arq->path) }}', '{{ $arq->tipo }}', '{{ addslashes($arq->nome) }}')"
                                                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-xl font-black uppercase text-[10px] text-center transition shadow"
                                                >
                                                    Visualizar
                                                </button>

                                                <a href="{{ route('arquivo.download', $arq->id) }}"
                                                   class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 rounded-xl font-black uppercase text-[10px] text-center transition shadow"
                                                >
                                                    Download
                                                </a>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-span-full py-8 text-center text-gray-500 italic text-sm">
                                            Nenhum documento disponível nesta pasta.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                        </div>

                    </div>

                    <!-- ============================================================ -->
                    <!-- MAPA SIGEF (carregado automaticamente se houver arquivo ODS) -->
                    <!-- ============================================================ -->
                    <div id="sigef-mapa-section" class="hidden glass rounded-[36px] border border-white/40 shadow-2xl overflow-hidden animate-fade">

                        <!-- Header do Card -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 md:p-8 border-b border-[#003366]/10">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-green-50 flex items-center justify-center text-3xl shadow-inner border border-green-100 shrink-0">
                                    🗺️
                                </div>
                                <div>
                                    <div class="flex items-center gap-3 flex-wrap">
                                        <span class="uppercase tracking-[3px] text-xs text-[#003366] font-black">Planta de Situação</span>
                                        <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase border border-emerald-200">SIGEF / INCRA</span>
                                    </div>
                                    <h2 id="sigef-titulo" class="text-xl md:text-2xl font-black text-[#003366] mt-1">
                                        Carregando dados do imóvel...
                                    </h2>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button onclick="openMemorial()"
                                    class="bg-blue-600 hover:bg-blue-750 text-white px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition flex items-center gap-2 shadow">
                                    📄 Memorial Descritivo
                                </button>
                                <a href="{{ route('pasta.dxf', $pasta->id) }}"
                                   class="bg-emerald-600 hover:bg-emerald-705 text-white px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition flex items-center gap-2 shadow">
                                    📐 Baixar DXF (CAD)
                                </a>
                                <button onclick="window.print()"
                                    class="bg-[#003366] hover:bg-blue-900 text-white px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition flex items-center gap-2 shadow">
                                    🖨️ Imprimir Planta
                                </button>
                                <button id="btn-satelite" onclick="alternarCamada()"
                                    class="bg-white/10 hover:bg-white/20 text-[#003366] border border-white/40 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition flex items-center gap-2 shadow">
                                    🛰️ Satélite
                                </button>
                            </div>
                        </div>

                        <!-- Metadados do Imóvel -->
                        <div id="sigef-meta" class="px-6 md:px-8 py-4 grid grid-cols-2 md:grid-cols-4 gap-3 bg-[#003366]/5 border-b border-[#003366]/10 hidden">
                            <div class="text-center">
                                <p class="text-[10px] uppercase tracking-widest text-[#003366]/60 font-bold">Denominação</p>
                                <p id="meta-imovel" class="text-sm font-black text-[#003366] mt-0.5 truncate">—</p>
                            </div>
                            <div class="text-center">
                                <p class="text-[10px] uppercase tracking-widest text-[#003366]/60 font-bold">Detentor</p>
                                <p id="meta-detentor" class="text-sm font-black text-[#003366] mt-0.5 truncate">—</p>
                            </div>
                            <div class="text-center">
                                <p class="text-[10px] uppercase tracking-widest text-[#003366]/60 font-bold">Município</p>
                                <p id="meta-municipio" class="text-sm font-black text-[#003366] mt-0.5 truncate">—</p>
                            </div>
                            <div class="text-center">
                                <p class="text-[10px] uppercase tracking-widest text-[#003366]/60 font-bold">Área</p>
                                <p id="meta-area" class="text-sm font-black text-[#003366] mt-0.5">—</p>
                            </div>
                        </div>

                        <!-- Mapa -->
                        <div id="leaflet-mapa" class="w-full" style="height: 500px; z-index: 1;"></div>

                        <!-- TABELA TÉCNICA DE IMPRESSÃO (apenas visível ao imprimir) -->
                        <div id="print-table-section" class="p-6 bg-white text-black border-t-2 border-black">
                            <h3 class="text-sm font-black uppercase mb-3 border-b-2 border-black pb-1">Tabela de Dados Técnicos (Vértices)</h3>
                            <table class="w-full text-[10px] text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-black font-bold uppercase">
                                        <th class="py-1">Vértice</th>
                                        <th class="py-1">Norte (Y)</th>
                                        <th class="py-1">Este (X)</th>
                                        <th class="py-1">Altitude (Z)</th>
                                        <th class="py-1">Confrontante</th>
                                        <th class="py-1">Limite</th>
                                    </tr>
                                </thead>
                                <tbody id="print-table-body">
                                    <!-- Preenchido via JS -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Rodapé do mapa com estatísticas -->
                        <div id="sigef-footer" class="hidden px-6 md:px-8 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white/60 border-t border-[#003366]/10">
                            <div class="flex items-center gap-6">
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 rounded-full bg-[#003366] border-2 border-white shadow"></div>
                                    <span id="footer-vertices" class="text-xs font-bold text-[#003366]">0 vértices</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-3 rounded bg-blue-500/40 border-2 border-blue-600"></div>
                                    <span class="text-xs font-bold text-[#003366]">Perímetro do Imóvel</span>
                                </div>
                            </div>
                            <a id="footer-link-sigef" href="#" target="_blank"
                               class="hidden bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-xl font-black uppercase text-xs tracking-wider shadow transition flex items-center gap-2">
                                Ver no SIGEF ↗
                            </a>
                        </div>

                    </div>

                    <!-- Mensagem se não encontrou ODS -->
                    <div id="sigef-nao-encontrado" class="hidden glass rounded-[36px] border border-dashed border-white/30 shadow p-6 md:p-8 text-center animate-fade">
                        <span class="text-4xl mb-3 block">📋</span>
                        <p class="text-white/60 text-sm font-semibold">Nenhum arquivo ODS do SIGEF encontrado nesta pasta.</p>
                        <p class="text-white/40 text-xs mt-1">Faça upload do arquivo de georreferenciamento para visualizar o mapa do imóvel.</p>
                    </div>

                </section>

        </main>

    </div>

    <!-- BOTÃO VOLTAR -->
    <a href="{{ route('dashboard') }}"
       class="fixed bottom-6 right-6 z-40 w-14 h-14 rounded-full bg-white text-[#003366] shadow-2xl flex items-center justify-center hover:scale-105 hover:bg-gray-100 transition"
    >
        <svg xmlns="http://www.w3.org/2000/svg"
             class="h-6 w-6"
             fill="none"
             viewBox="0 0 24 24"
             stroke="currentColor">

            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M10 19l-7-7m0 0l7-7m-7 7h18"/>

        </svg>
    </a>

    <!-- MODAL UPLOAD -->
    <div id="uploadModal"
         class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm p-4 modal-overlay">

        <div class="modal-container bg-[#003366] text-white rounded-[38px] p-8 md:p-10 w-full shadow-2xl border border-white/10 animate-fade">

            <form
                action="{{ route('arquivos.store', $pasta->id) }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf

                <div class="text-center mb-8">

                    <h2 class="text-3xl font-black italic uppercase mb-2">
                        Enviar Arquivo
                    </h2>

                    <p id="labelNomeArquivo"
                       class="text-yellow-400 font-bold uppercase italic text-sm">
                    </p>

                </div>

                <input type="hidden" name="nome" id="inputNomeArquivo">

                <div class="mb-8">

                    <div class="relative border-2 border-dashed border-white/30 rounded-3xl bg-white/10 hover:bg-white/15 transition overflow-hidden">

                        <label class="absolute inset-0 flex items-center justify-center cursor-pointer">
                            <input
                                type="file"
                                name="arquivo"
                                id="fileInput"
                                required
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                onchange="showFileName(this)"
                            >
                        </label>

                        <div class="h-56 flex flex-col items-center justify-center px-6 text-center">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-14 w-14 text-white/80 mb-4"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.5"
                                      d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>

                            </svg>

                            <p id="uploadText"
                               class="font-bold text-white/80 italic">
                                Clique ou arraste o arquivo aqui
                            </p>

                            <p id="fileNameDisplay"
                               class="hidden text-green-300 font-black mt-3 break-all">
                            </p>

                        </div>

                    </div>

                </div>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">

                    <button
                        type="submit"
                        class="flex-1 bg-green-500 hover:bg-green-600 text-white py-3 rounded-2xl font-black uppercase shadow-xl transition"
                    >
                        Enviar
                    </button>

                    <button
                        type="button"
                        onclick="closeModal('uploadModal')"
                        class="flex-1 bg-red-500 hover:bg-red-600 text-white py-3 rounded-2xl font-black uppercase shadow-xl transition"
                    >
                        Cancelar
                    </button>

                </div>

            </form>

        </div>

    </div>

    <!-- MODAL PREVIEW -->
    <div id="previewModal"
         class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm p-4 modal-overlay">

        <div class="modal-container bg-[#003366] text-white rounded-[38px] p-8 md:p-10 w-full max-w-4xl shadow-2xl border border-white/10 animate-fade flex flex-col">

            <div class="flex justify-between items-center mb-6">
                <h3 id="previewTitle" class="text-2xl font-black italic uppercase text-yellow-400">
                    Visualizar Arquivo
                </h3>
                <button type="button" onclick="closeModal('previewModal')" class="text-white/60 hover:text-white text-3xl leading-none">&times;</button>
            </div>

            <div id="previewContent" class="flex-1 flex items-center justify-center min-h-[50vh] max-h-[70vh] overflow-auto bg-black/20 rounded-2xl p-4">
                <!-- Content injected dynamically -->
            </div>

        </div>

    </div>

    <!-- MEMORIAL MODAL -->
    <div id="memorialModal"
         class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm p-4 modal-overlay">
        <div class="modal-container bg-[#003366] text-white rounded-[38px] p-8 md:p-10 w-full max-w-4xl shadow-2xl border border-white/10 animate-fade flex flex-col max-h-[90vh]">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-2xl font-black italic uppercase text-yellow-400">
                        Memorial Descritivo
                    </h3>
                    <p class="text-white/60 text-xs mt-1 uppercase font-bold tracking-wider">
                        Gerado a partir do perímetro SIGEF
                    </p>
                </div>
                <button type="button" onclick="closeModal('memorialModal')" class="text-white/60 hover:text-white text-3xl leading-none">&times;</button>
            </div>

            <div class="flex-1 overflow-auto bg-black/40 border border-white/10 rounded-2xl p-6 mb-6 font-mono text-sm leading-relaxed whitespace-pre-wrap select-all relative min-h-[300px] max-h-[50vh]">
                <div id="loadingMemorial" class="absolute inset-0 flex items-center justify-center bg-black/50 rounded-2xl hidden">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-8 h-8 border-4 border-[#00E500] border-t-transparent rounded-full animate-spin"></div>
                        <span class="text-xs uppercase font-bold tracking-widest text-[#00E500]">Gerando Memorial...</span>
                    </div>
                </div>
                <div id="memorialTextoContent">Carregando...</div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4">
                <button type="button" onclick="copiarMemorial()"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3.5 rounded-2xl font-black uppercase transition flex items-center justify-center gap-2 shadow-lg">
                    📋 Copiar Texto
                </button>
                <a href="{{ route('pasta.memorial-descritivo', [$pasta->id, 'download' => 'txt']) }}"
                   class="flex-1 bg-[#00E500] hover:bg-green-500 text-black py-3.5 rounded-2xl font-black uppercase transition flex items-center justify-center gap-2 text-center shadow-lg">
                    💾 Baixar TXT
                </a>
                <button type="button" onclick="closeModal('memorialModal')"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white py-3.5 rounded-2xl font-black uppercase transition shadow-lg">
                    Fechar
                </button>
            </div>
        </div>
    </div>

    <script>
        function openUploadModal(nome) {

            document.getElementById('inputNomeArquivo').value = nome;

            document.getElementById('labelNomeArquivo').innerText =
                'Anexar arquivo para: ' + nome;

            const modal = document.getElementById('uploadModal');

            modal.classList.add('open');

            document.getElementById('fileInput').value = '';

            document.getElementById('fileNameDisplay').classList.add('hidden');

            document.getElementById('uploadText').classList.remove('hidden');
        }

        function closeModal(id) {

            const modal = document.getElementById(id);

            modal.classList.remove('open');
        }

        function openPreviewModal(url, type, name) {
            document.getElementById('previewTitle').innerText = name;
            const container = document.getElementById('previewContent');
            container.innerHTML = ''; // Clear previous content

            type = type.toLowerCase();
            if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(type)) {
                container.innerHTML = `<img src="${url}" class="max-w-full max-h-[65vh] object-contain rounded-xl shadow-2xl">`;
            } else if (type === 'pdf') {
                container.innerHTML = `<iframe src="${url}" class="w-full h-[65vh] rounded-xl" frameborder="0"></iframe>`;
            } else {
                container.innerHTML = `
                    <div class="text-center py-10">
                        <span class="text-5xl mb-4 block">📁</span>
                        <p class="text-white font-bold mb-2">Visualização não disponível</p>
                        <p class="text-white/60 text-sm mb-6">Arquivos do tipo .${type.toUpperCase()} não podem ser visualizados diretamente no navegador.</p>
                        <a href="${url}" download class="inline-flex items-center bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-2xl font-black uppercase text-xs transition">
                            Baixar Arquivo
                        </a>
                    </div>
                `;
            }

            const modal = document.getElementById('previewModal');
            modal.classList.add('open');
        }

        function showFileName(input) {

            const display = document.getElementById('fileNameDisplay');

            const text = document.getElementById('uploadText');

            if (input.files && input.files.length > 0) {

                display.innerText = '✓ ' + input.files[0].name;

                display.classList.remove('hidden');

                text.classList.add('hidden');

            } else {

                display.classList.add('hidden');

                text.classList.remove('hidden');
            }
        }

        window.addEventListener('keydown', function(e) {

            if (e.key === 'Escape') {
                closeModal('uploadModal');
                closeModal('previewModal');
                closeModal('memorialModal');
            }
        });

        let memorialTextoCompleto = '';

        async function openMemorial() {
            const modal = document.getElementById('memorialModal');
            const loading = document.getElementById('loadingMemorial');
            const content = document.getElementById('memorialTextoContent');
            
            content.innerText = 'Carregando...';
            loading.classList.remove('hidden');
            modal.classList.add('open');
            
            try {
                const resp = await fetch('{{ route("pasta.memorial-descritivo", $pasta->id) }}');
                const data = await resp.json();
                memorialTextoCompleto = data.texto;
                content.innerText = data.texto;
            } catch (e) {
                content.innerText = "Erro ao carregar o memorial descritivo.";
            } finally {
                loading.classList.add('hidden');
            }
        }

        function copiarMemorial() {
            navigator.clipboard.writeText(memorialTextoCompleto).then(() => {
                Swal.fire({
                    title: 'Copiado!',
                    text: 'O Memorial Descritivo foi copiado para a área de transferência.',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 2000,
                    background: '#003366',
                    color: '#fff',
                    borderRadius: 25
                });
            });
        }
    </script>

    <!-- ================================================= -->
    <!-- JavaScript: Carregamento e Renderização do Mapa SIGEF -->
    <!-- ================================================= -->
    <script>
        const PASTA_ID = {{ $pasta->id }};
        const SIGEF_MAPA_URL = '{{ route("pasta.mapa-sigef", $pasta->id) }}';
        @if($codigoSigef)
        const CODIGO_SIGEF = '{{ $codigoSigef }}';
        @else
        const CODIGO_SIGEF = null;
        @endif

        let leafletMap   = null;
        let camadaAtual  = 'streets'; // 'streets' ou 'satellite'
        let camadaStreets = null;
        let camadaSateli  = null;

        // ---- Definições proj4 para zonas UTM SIRGAS 2000 ----
        function definirProjecaoUtm(zona) {
            // Extrair número e hemisfério (ex: '23S' → 23, 'S')
            const match = String(zona).match(/(\d+)([NS]?)/i);
            if (!match) return null;
            const num = parseInt(match[1]);
            const hem = (match[2] || 'S').toUpperCase();
            const projStr = `+proj=utm +zone=${num} +${hem === 'S' ? 'south' : 'north'} +ellps=GRS80 +towgs84=0,0,0 +units=m +no_defs`;
            const epsg = `EPSG:319${num < 10 ? '0' + num : num}S`;
            proj4.defs(epsg, projStr);
            return epsg;
        }

        function utmParaLatLon(E, N, zona) {
            const epsg = definirProjecaoUtm(zona);
            if (!epsg) return null;
            try {
                const [lon, lat] = proj4(epsg, 'WGS84', [E, N]);
                return { lat, lon };
            } catch (e) {
                return null;
            }
        }

        // ---- Converte vértices para array de [lat, lon] ----
        function converterVertices(vertices) {
            return vertices.map(v => {
                if (v.tipo === 'geodesica') {
                    // E = Longitude, N = Latitude
                    return [v.N, v.E];
                } else {
                    // UTM → WGS84
                    const zona = v.zona || '23S';
                    const conv = utmParaLatLon(v.E, v.N, zona);
                    return conv ? [conv.lat, conv.lon] : null;
                }
            }).filter(p => p !== null);
        }

        // ---- Calcular área aproximada do polígono (ha) em WGS84 ----
        function calcularAreaHa(coords) {
            if (coords.length < 3) return 0;
            let area = 0;
            const n = coords.length;
            const R = 6371000; // raio da Terra em metros
            for (let i = 0; i < n; i++) {
                const j = (i + 1) % n;
                const lat1 = coords[i][0] * Math.PI / 180;
                const lat2 = coords[j][0] * Math.PI / 180;
                const dLon = (coords[j][1] - coords[i][1]) * Math.PI / 180;
                area += (coords[j][1] - coords[i][1]) * Math.PI / 180
                    * (2 + Math.sin(lat1) + Math.sin(lat2));
            }
            const areaMq = Math.abs(area * R * R / 2);
            return (areaMq / 10000).toFixed(4); // m² → ha
        }

        // ---- Inicializa o mapa Leaflet ----
        function inicializarMapa(coords, dados) {
            if (!leafletMap) {
                leafletMap = L.map('leaflet-mapa', {
                    zoomControl: true,
                    scrollWheelZoom: true,
                });

                camadaStreets = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© <a href="https://www.openstreetmap.org/">OpenStreetMap</a>',
                    maxZoom: 20,
                });

                camadaSateli = L.tileLayer(
                    'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                    {
                        attribution: 'Tiles © Esri',
                        maxZoom: 20,
                    }
                );

                camadaStreets.addTo(leafletMap);
            }

            // Limpar camadas anteriores de polígono
            leafletMap.eachLayer(layer => {
                if (layer !== camadaStreets && layer !== camadaSateli) {
                    leafletMap.removeLayer(layer);
                }
            });

            if (coords.length < 3) return;

            // Polígono principal
            const poligono = L.polygon(coords, {
                color: '#003366',
                weight: 2.5,
                fillColor: '#3B82F6',
                fillOpacity: 0.25,
            }).addTo(leafletMap);

            // Marcadores nos vértices
            dados.vertices.forEach((v, i) => {
                const coord = coords[i];
                if (!coord) return;

                const icon = L.divIcon({
                    className: '',
                    html: `<div style="
                        width: 10px; height: 10px;
                        background: #003366;
                        border: 2px solid white;
                        border-radius: 50%;
                        box-shadow: 0 1px 4px rgba(0,0,0,0.4);
                    "></div>`,
                    iconSize: [10, 10],
                    iconAnchor: [5, 5],
                });

                L.marker(coord, { icon })
                    .bindTooltip(`<b>${v.codigo || ('M-' + (i + 1))}</b><br>Lat: ${coord[0].toFixed(6)}<br>Lon: ${coord[1].toFixed(6)}`, {
                        permanent: false,
                        direction: 'top',
                    })
                    .addTo(leafletMap);
            });

            // Ajustar zoom para o polígono
            leafletMap.fitBounds(poligono.getBounds(), { padding: [30, 30] });

            // Atualizar metadados
            document.getElementById('sigef-titulo').innerText = dados.imovel || 'Imóvel Georreferenciado';

            const meta = document.getElementById('sigef-meta');
            if (dados.imovel || dados.detentor || dados.municipio || dados.area_ha) {
                if (dados.imovel)    document.getElementById('meta-imovel').innerText = dados.imovel;
                if (dados.detentor)  document.getElementById('meta-detentor').innerText = dados.detentor;
                if (dados.municipio) document.getElementById('meta-municipio').innerText = dados.municipio;

                const areaExibir = dados.area_ha
                    ? dados.area_ha + ' ha'
                    : calcularAreaHa(coords) + ' ha (calc.)';
                document.getElementById('meta-area').innerText = areaExibir;
                meta.classList.remove('hidden');
            }

            // Preencher tabela de impressão técnica
            const body = document.getElementById('print-table-body');
            if (body && dados.vertices) {
                body.innerHTML = '';
                dados.vertices.forEach((v, i) => {
                    const coord = coords[i] || [0, 0];
                    body.innerHTML += `
                        <tr class="border-b border-gray-300">
                            <td class="py-1.5 font-bold">${v.codigo || ('M-' + (i + 1))}</td>
                            <td class="py-1.5">${v.tipo === 'utm' ? parseFloat(v.N).toLocaleString('pt-BR', {minimumFractionDigits: 3}) + ' m' : coord[0].toFixed(6) + '° (Lat)'}</td>
                            <td class="py-1.5">${v.tipo === 'utm' ? parseFloat(v.E).toLocaleString('pt-BR', {minimumFractionDigits: 3}) + ' m' : coord[1].toFixed(6) + '° (Lng)'}</td>
                            <td class="py-1.5">${v.altitude} m</td>
                            <td class="py-1.5 text-gray-700 font-medium">${v.confrontante}</td>
                            <td class="py-1.5 text-gray-500">${v.limite}</td>
                        </tr>
                    `;
                });
            }

            // Rodapé
            const footer = document.getElementById('sigef-footer');
            footer.classList.remove('hidden');
            document.getElementById('footer-vertices').innerText = dados.vertices.length + ' vértices';

            // Link para o SIGEF
            if (CODIGO_SIGEF) {
                const linkSigef = document.getElementById('footer-link-sigef');
                linkSigef.href = `https://sigef.incra.gov.br/geo/parcela/detalhe/${CODIGO_SIGEF}/`;
                linkSigef.classList.remove('hidden');
            }
        }

        // ---- Alterna camada mapa/satélite ----
        function alternarCamada() {
            if (!leafletMap) return;
            const btn = document.getElementById('btn-satelite');
            if (camadaAtual === 'streets') {
                leafletMap.removeLayer(camadaStreets);
                camadaSateli.addTo(leafletMap);
                camadaAtual = 'satellite';
                btn.textContent = '🗺️ Mapa';
            } else {
                leafletMap.removeLayer(camadaSateli);
                camadaStreets.addTo(leafletMap);
                camadaAtual = 'streets';
                btn.textContent = '🛰️ Satélite';
            }
        }

        // ---- Carrega o mapa SIGEF via AJAX ----
        async function carregarMapaSigef() {
            try {
                const resp = await fetch(SIGEF_MAPA_URL, {
                    headers: { 'Accept': 'application/json' }
                });

                if (!resp.ok) {
                    // 404 → sem ODS, não exibir mensagem negativa por padrão
                    // (pasta pode ser nível 2 sem ODS ainda)
                    return;
                }

                const dados = await resp.json();

                if (!dados.vertices || dados.vertices.length === 0) {
                    return;
                }

                const coords = converterVertices(dados.vertices);

                if (coords.length < 3) return;

                // Exibir seção do mapa
                document.getElementById('sigef-mapa-section').classList.remove('hidden');

                // Aguardar DOM estar visível antes de inicializar Leaflet
                setTimeout(() => inicializarMapa(coords, dados), 100);

            } catch (err) {
                console.error('Erro ao carregar mapa SIGEF:', err);
            }
        }

        document.addEventListener('DOMContentLoaded', carregarMapaSigef);
    </script>

</body>
</html>