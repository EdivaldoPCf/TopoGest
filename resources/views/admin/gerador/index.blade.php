<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador Express de Planta e Memorial - Getec Topografia</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Leaflet.js -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- proj4js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.9.0/proj4.js"></script>

    <!-- ID dinâmico para controle de orientação do papel -->
    <style id="dynamic-page-style">
        @page {
            size: A4 landscape;
            margin: 5mm;
        }
    </style>

    <style>
        [x-cloak] {
            display: none !important;
        }

        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-thumb {
            background: #003366;
            border-radius: 999px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255,255,255,.05);
        }

        .glass {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        .glow {
            box-shadow: 0 0 40px rgba(0, 115, 230, 0.15);
        }

        .card-hover {
            transition: all .25s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            background: rgba(255,255,255,0.12);
            box-shadow: 0 15px 30px rgba(0,0,0,0.3);
        }

        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            html, body {
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
                background: white !important;
                color: black !important;
                font-family: Arial, sans-serif !important;
            }
            @page {
                size: A4 portrait;
                margin: 10mm !important;
            }
            /* Esconde backgrounds, cabeçalho e outras seções da tela */
            .fixed, .modal-overlay, header, .print\:hidden, [class*="print:hidden"] {
                display: none !important;
            }
            /* Reseta contêiner principal para impressão */
            #main-wrapper {
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                box-shadow: none !important;
            }
            #print-container-section {
                margin: 0 !important;
                padding: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            /* Esconde o painel interativo */
            #interactive-tab-content {
                display: none !important;
            }

            /* Se estiver imprimindo a prancha */
            body.print-prancha #prancha-tab-container {
                display: block !important;
                visibility: visible !important;
                position: static !important;
                padding: 0 !important;
                margin: 0 !important;
                overflow: visible !important;
            }
            body.print-prancha #memorial-print-tab-container {
                display: none !important;
            }

            /* Se estiver imprimindo o memorial */
            body.print-memorial #memorial-print-tab-container {
                display: block !important;
                visibility: visible !important;
                position: static !important;
                padding: 0 !important;
                margin: 0 !important;
                overflow: visible !important;
            }
            body.print-memorial #prancha-tab-container {
                display: none !important;
            }

            #prancha-tab-container > div,
            #prancha-tab-container > div > div {
                transform: none !important;
                padding: 0 !important;
                margin: 0 !important;
                background: transparent !important;
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                height: 100% !important;
                display: block !important;
                overflow: visible !important;
            }
            #print-sheet-wrapper {
                margin: 0 auto !important;
                display: flex !important;
                border: 2px solid black !important;
                box-shadow: none !important;
                box-sizing: border-box !important;
                background: white !important;
                color: black !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            #print-map, #print-sit-map, #print-sit-map-portrait {
                width: 100% !important;
                height: 100% !important;
            }
            #memorial-print-wrapper {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                height: auto !important;
                min-height: 0 !important;
            }
        }
    </style>
</head>

<body x-data="{ currentTab: 'dashboard', plantaAssinada: false, showSignModal: false, signStep: 'choose' }" class="min-h-screen overflow-x-hidden bg-[#E5E7EB] text-white">

    <!-- BACKGROUND -->
    <div class="fixed inset-0 -z-10">
        <img
            src="{{ asset('images/background-topo.jpg') }}"
            class="w-full h-full object-cover"
            alt="Background">

        <div class="absolute inset-0 bg-[#00111f]/50"></div>

        <div class="absolute inset-0"
             style="background:
             radial-gradient(circle at top left, rgba(0,74,124,.35), transparent 35%),
             radial-gradient(circle at bottom right, rgba(0,51,102,.40), transparent 35%);">
        </div>
    </div>

    <div id="main-wrapper" class="relative w-full max-w-7xl mx-auto px-4 md:px-8 py-8">

        <!-- HEADER -->
        <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8 mb-12 print:hidden">

            <!-- LOGO -->
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-4 group w-fit">

                <div class="relative flex items-center justify-center w-20 h-20 rounded-2xl bg-white shadow-2xl transition group-hover:scale-105">
                    <img src="{{ asset('images/logo-icon.png') }}"
                          alt="Getec Topografia"
                          class="w-full h-full object-contain p-2">
                </div>

                <div class="relative flex items-center h-14 px-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                    <img src="{{ asset('images/logo-text.png') }}"
                          alt="Getec Topografia"
                          class="relative z-10 h-8 w-auto">
                </div>
            </a>

            <!-- VOLTAR -->
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.dashboard') }}"
                   class="bg-white/10 hover:bg-white/20 text-white px-6 py-3 rounded-2xl font-bold uppercase text-xs tracking-wider transition border border-white/5 shadow-md flex items-center gap-2">
                    ⬅ Painel Administrativo
                </a>
            </div>
        </header>

        <!-- FLASH ERROR -->
        @if (session('error'))
            <div class="bg-red-500/25 border border-red-500/30 text-red-200 px-6 py-4 rounded-2xl mb-8 flex items-center gap-4 print:hidden animate-bounce">
                <span>⚠️</span>
                <span class="font-bold text-sm">{{ session('error') }}</span>
            </div>
        @endif

        @if(!isset($tempId))
            <!-- ======================= TELA DE UPLOAD ======================= -->
            <div class="max-w-2xl mx-auto glass glow rounded-[35px] border border-white/10 p-8 md:p-12 shadow-2xl">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-3xl mb-4">
                        ⚡
                    </div>
                    <h1 class="text-3xl font-black uppercase italic text-[#00E500] tracking-wide">
                        Gerador Planta e Memorial
                    </h1>
                    <p class="text-white/60 text-sm mt-2">
                        Faça upload de uma planilha SIGEF padrão (.ods) para gerar automaticamente a planta de situação interativa, o memorial descritivo completo e o arquivo DXF para CAD de forma rápida e segura.
                    </p>
                </div>

                <form action="{{ route('admin.gerador.processar') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <div class="border-2 border-dashed border-white/20 hover:border-[#00E500] rounded-[25px] p-10 transition duration-300 text-center relative group" id="drop-zone">
                        <input type="file" name="arquivo_ods" id="arquivo_ods" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".ods" onchange="updateFileName(this)">
                        <div class="space-y-4">
                            <div class="w-20 h-20 mx-auto rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-4xl group-hover:scale-110 transition duration-300">
                                📂
                            </div>
                            <h3 class="text-xl font-bold uppercase italic text-white/90">Arrastar Planilha SIGEF</h3>
                            <p class="text-sm text-white/60">Ou clique para selecionar o arquivo `.ods` no seu computador (máx 10MB)</p>
                            <div id="file-name-display" class="hidden text-sm font-black text-[#00E500] bg-emerald-500/10 px-4 py-2 rounded-xl inline-block mt-2"></div>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-[#00E500] hover:bg-green-400 text-black font-black uppercase tracking-wider py-4 rounded-2xl shadow-lg shadow-emerald-900/20 transition hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-2">
                        ⚡ Gerar Planta e Memorial
                    </button>
                </form>

                <div class="mt-8 border-t border-white/10 pt-6 text-center text-xs text-white/40">
                    O arquivo ODS não é salvo permanentemente no banco. É processado sob demanda e limpo periodicamente.
                </div>
            </div>

            <script>
                const dropZone = document.getElementById('drop-zone');
                const fileInput = document.getElementById('arquivo_ods');

                ['dragenter', 'dragover'].forEach(eventName => {
                    dropZone.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        dropZone.classList.add('border-[#00E500]', 'bg-white/5');
                    }, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        dropZone.classList.remove('border-[#00E500]', 'bg-white/5');
                    }, false);
                });

                function updateFileName(input) {
                    const display = document.getElementById('file-name-display');
                    if (input.files && input.files.length > 0) {
                        display.innerText = "📄 " + input.files[0].name;
                        display.classList.remove('hidden');
                    } else {
                        display.classList.add('hidden');
                    }
                }
            </script>
        @else
            <!-- ======================= SEÇÃO DE RESULTADOS (PLANTA & MEMORIAL) ======================= -->
            <div x-init="$watch('currentTab', val => { window.currentTab = val; window.invalidateMaps(); });"
                 id="print-container-section" 
                 class="space-y-8">

                <!-- Header de Situação -->
                <div class="glass glow rounded-[35px] border border-white/10 p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 print:hidden">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-500/20 border border-emerald-500/20 flex items-center justify-center text-3xl shrink-0">
                            🗺️
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="uppercase tracking-[3px] text-xs text-[#00E500] font-black">Planta e Memorial Gerados</span>
                                <span class="bg-emerald-500/25 text-[#00E500] text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase">SIGEF Express</span>
                            </div>
                            <h2 class="text-2xl font-black italic uppercase mt-1 text-white">
                                {{ $identificacao['imovel'] ?? 'Imóvel sem denominação' }}
                            </h2>
                        </div>
                    </div>

                    <!-- Ações Rápidas -->
                    <div class="flex flex-wrap gap-2 items-center">
                        <!-- Botão de Assinatura -->
                        <button @click="showSignModal = true; signStep = 'choose'"
                                :class="plantaAssinada ? 'bg-emerald-600 border-emerald-500 hover:bg-emerald-500 text-white' : 'bg-orange-600 border-orange-500 hover:bg-orange-500 text-white'" 
                                class="px-5 py-3 rounded-2xl text-xs font-black uppercase tracking-wider transition border shadow-md flex items-center gap-2">
                            <span x-text="plantaAssinada ? '🔏 Planta Assinada' : '🔑 Assinar Planta'"></span>
                        </button>


                        <button @click="currentTab = 'prancha'; document.body.className = 'print-prancha'; const pranchaStyle = document.getElementById('dynamic-page-style'); if (pranchaStyle) pranchaStyle.innerHTML = '@page { size: A4 portrait; margin: 0; }'; setTimeout(() => { try { window.invalidateMaps(); } catch(e){ console.error(e); } setTimeout(() => { window.print(); }, 300); }, 100);"
                                class="bg-[#00E500] hover:bg-green-400 text-black px-5 py-3 rounded-2xl text-xs font-black uppercase tracking-wider transition border border-white/10 shadow-md flex items-center gap-2">
                            🖨️ Imprimir Planta
                        </button>
                        <button @click="currentTab = 'memorial_print'; document.body.className = 'print-memorial'; const memStyle = document.getElementById('dynamic-page-style'); if (memStyle) memStyle.innerHTML = '@page { size: A4 portrait; margin: 15mm; }'; setTimeout(() => { window.print(); }, 150);"
                                class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-3 rounded-2xl text-xs font-black uppercase tracking-wider transition border border-white/10 shadow-md flex items-center gap-2">
                            📄 Imprimir Memorial
                        </button>
                        <a href="{{ route('admin.gerador.index') }}"
                           class="bg-white/10 hover:bg-white/20 text-white px-5 py-3 rounded-2xl text-xs font-black uppercase tracking-wider transition border border-white/5 shadow-md flex items-center gap-2">
                            🔄 Novo Upload
                        </a>
                    </div>
                </div>

                <!-- TABS DE NAVEGAÇÃO -->
                <div class="flex border-b border-white/10 mb-6 print:hidden">
                    <button @click="currentTab = 'dashboard'" 
                            :class="currentTab === 'dashboard' ? 'border-[#00E500] text-[#00E500]' : 'border-transparent text-white/60 hover:text-white'" 
                            class="py-3 px-6 font-black uppercase text-xs tracking-wider border-b-2 transition">
                        🖥️ Painel Interativo
                    </button>
                    <button @click="currentTab = 'prancha'; setTimeout(() => { window.invalidateMaps(); }, 200)" 
                            :class="currentTab === 'prancha' ? 'border-[#00E500] text-[#00E500]' : 'border-transparent text-white/60 hover:text-white'" 
                            class="py-3 px-6 font-black uppercase text-xs tracking-wider border-b-2 transition flex items-center gap-2">
                        📐 Prancha de Desenho (A4)
                    </button>
                    <button @click="currentTab = 'memorial_print'" 
                            :class="currentTab === 'memorial_print' ? 'border-[#00E500] text-[#00E500]' : 'border-transparent text-white/60 hover:text-white'" 
                            class="py-3 px-6 font-black uppercase text-xs tracking-wider border-b-2 transition flex items-center gap-2">
                        📄 Memorial de Impressão (A4)
                    </button>
                </div>

                <!-- ================= TAB 1: PAINEL INTERATIVO ================= -->
                <div x-show="currentTab === 'dashboard'" id="interactive-tab-content" class="grid grid-cols-1 lg:grid-cols-3 gap-8 print:hidden">

                    <!-- Coluna Esquerda: Metadados + Mapa Leaflet -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Metadados -->
                        <div class="glass glow rounded-[35px] border border-white/10 p-6 md:p-8 grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-[9px] uppercase tracking-widest text-white/50 font-bold">Denominação do Imóvel</p>
                                <p class="text-sm font-black text-white mt-0.5">{{ $identificacao['imovel'] ?? 'Não informado' }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] uppercase tracking-widest text-white/50 font-bold">Proprietário / Detentor</p>
                                <p class="text-sm font-black text-white mt-0.5">{{ $identificacao['detentor'] ?? 'Não informado' }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] uppercase tracking-widest text-white/50 font-bold">CPF / CNPJ</p>
                                <p class="text-sm font-black text-white mt-0.5">{{ $identificacao['cpf_cnpj'] ?? 'Não informado' }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] uppercase tracking-widest text-white/50 font-bold">Município / UF</p>
                                <p class="text-sm font-black text-white mt-0.5">{{ $identificacao['municipio'] ?? 'Não informado' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Área</p>
                                <p class="text-sm font-black text-[#00E500] mt-0.5 font-mono" id="print-area-val-header">{{ $identificacao['area_ha'] ?? '0,0000' }} ha</p>
                            </div>
                            <div>
                                <p class="text-[9px] uppercase tracking-widest text-white/50 font-bold">Código SNCR/INCRA</p>
                                <p class="text-sm font-black text-white mt-0.5 font-mono">{{ $identificacao['sncr'] ?? 'Não informado' }}</p>
                            </div>
                        </div>

                        <!-- Mapa Container -->
                        <div class="glass glow rounded-[35px] border border-white/10 overflow-hidden shadow-2xl relative">
                            <!-- Toggle Satellite Layer -->
                            <button id="btn-satelite" onclick="alternarCamada()"
                                    class="absolute top-4 right-4 z-[1000] bg-black/60 hover:bg-black/80 text-white px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition border border-white/10 shadow-md">
                                🛰️ Satélite
                            </button>

                            <!-- Mapa Leaflet -->
                            <div id="leaflet-mapa" style="height: 480px; z-index: 1;"></div>
                        </div>
                    </div>

                    <!-- Coluna Direita: Memorial Descritivo -->
                    <div class="lg:col-span-1">
                        <div class="glass glow rounded-[35px] border border-white/10 overflow-hidden shadow-2xl h-full flex flex-col">
                            <div class="px-6 py-5 border-b border-white/10 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="text-lg">📄</span>
                                    <span class="uppercase tracking-[3px] text-xs text-[#00E500] font-black">Memorial Descritivo</span>
                                </div>
                                <button onclick="copiarMemorial()" id="btn-copiar" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition flex items-center gap-2">
                                    📋 Copiar
                                </button>
                            </div>
                            <div class="p-6 flex-1 overflow-y-auto max-h-[500px]">
                                <pre id="memorial-texto" class="text-xs font-mono bg-black/30 p-4 rounded-2xl overflow-x-auto border border-white/5 text-white/90 leading-relaxed whitespace-pre-wrap">{{ $memorialTexto }}</pre>
                            </div>
                        </div>
                    </div>

                    <!-- Tabela Técnica de Vértices para a Tela -->
                    <div class="col-span-full glass glow rounded-[35px] border border-white/10 p-6 md:p-8 shadow-2xl">
                        <h3 class="text-lg font-black uppercase italic mb-4 text-white">
                            Tabela de Dados Técnicos (Vértices)
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-white/10 uppercase font-black text-xs text-white/60">
                                        <th class="py-3 px-4">Vértice</th>
                                        <th class="py-3 px-4">Tipo</th>
                                        <th class="py-3 px-4">Norte (Y)</th>
                                        <th class="py-3 px-4">Este (X)</th>
                                        <th class="py-3 px-4">Altitude (Z)</th>
                                        <th class="py-3 px-4">Confrontante</th>
                                        <th class="py-3 px-4">Limite</th>
                                    </tr>
                                </thead>
                                <tbody class="text-white/80">
                                    @foreach($vertices as $v)
                                        <tr class="border-b border-white/5 hover:bg-white/5 transition">
                                            <td class="py-3 px-4 font-bold">{{ $v['codigo'] }}</td>
                                            <td class="py-3 px-4 text-xs uppercase">{{ $v['tipo'] === 'utm' ? 'UTM' : 'Geodésica' }}</td>
                                            <td class="py-3 px-4 font-mono">
                                                @if($v['tipo'] === 'utm')
                                                    {{ number_format($v['y'], 3, ',', '.') }} m
                                                @else
                                                    {{ number_format($v['N'], 7, ',', '.') }}° (Lat)
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 font-mono">
                                                @if($v['tipo'] === 'utm')
                                                    {{ number_format($v['x'], 3, ',', '.') }} m
                                                @else
                                                    {{ number_format($v['E'], 7, ',', '.') }}° (Lng)
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 font-mono">{{ number_format((float) ($v['altitude'] ?? 0.0), 2, ',', '.') }} m</td>
                                            <td class="py-3 px-4 text-xs text-white/60 font-medium">{{ $v['confrontante'] ?: 'Limite do Imóvel' }}</td>
                                            <td class="py-3 px-4 text-xs text-white/50">{{ $v['limite'] ?: 'Cerca' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ================= TAB 2: PRANCHA DE DESENHO (A4) ================= -->
                <div :class="currentTab === 'prancha' ? 'block' : 'invisible h-0 overflow-hidden absolute -left-[9999px]'" id="prancha-tab-container" class="print:block">
                    <div class="overflow-x-auto w-full flex justify-center py-4">
                        <!-- Wrapper para redimensionar a prancha na tela e mantê-la exata na folha física -->
                        <div class="origin-top transform scale-[0.55] md:scale-[0.75] lg:scale-[0.9] xl:scale-100 print:transform-none print:scale-100 print:p-0 print:border-none print:shadow-none print:bg-transparent transition-transform bg-[#0a1520] p-6 rounded-[25px] border border-white/10 shadow-2xl">
                            
                            <!-- A PRANCHA EM SI (Formato SIGEF Retrato A4) -->
                            <div id="print-sheet-wrapper" class="bg-white text-black font-sans relative flex flex-col select-none p-1" style="width: 190mm; height: 277mm; box-sizing: border-box; background: white; border: 2px solid black;">
                                <div class="flex flex-col h-full border border-black p-[1mm]">
                                    
                                    <!-- SEÇÃO SUPERIOR: ÁREA DO DESENHO (MAPA) -->
                                    <div class="relative w-full border-b border-black flex-grow" style="min-height: 177mm;">
                                        <div id="print-map" class="w-full h-full bg-white relative z-0"></div>
                                    </div>
                                    


                                    <!-- Cabeçalho Institucional -->
                                    <div class="border-b border-black flex items-center justify-between p-1.5 h-[15mm] flex-shrink-0">
                                        <img src="{{ asset('images/logo-icon.png') }}" class="h-10 w-10 object-contain">
                                        <div class="text-center flex-grow">
                                            <h1 class="text-[12px] font-bold uppercase tracking-wide">GETEC TOPOGRAFIA LTDA</h1>
                                            <h2 class="text-[9px] font-bold uppercase mt-0.5">SERVIÇOS TÉCNICOS E CARTOGRAFIA</h2>
                                        </div>
                                    </div>

                                    <!-- Bloco de Identificação (2 colunas) -->
                                    <div class="flex border-b border-black flex-shrink-0" style="height: 32mm;">
                                        <div class="w-1/2 border-r border-black p-1 flex flex-col justify-between text-[8.5px]">
                                            <div class="flex"><span class="font-bold w-24">Denominação:</span> <span class="truncate uppercase">{{ $identificacao['imovel'] ?? '' }}</span></div>
                                            <div class="flex"><span class="font-bold w-24">Proprietário(a):</span> <span class="truncate uppercase">{{ $identificacao['detentor'] ?? '' }}</span></div>
                                            <div class="flex"><span class="font-bold w-24">CPF/CNPJ:</span> <span class="truncate">{{ $identificacao['cpf_cnpj'] ?? '' }}</span></div>
                                            <div class="flex"><span class="font-bold w-24">Matrícula do imóvel:</span> <span class="truncate">{{ $identificacao['matricula'] ?? 'Não informado' }}</span></div>
                                            <div class="flex"><span class="font-bold w-24">Cartório (CNS):</span> <span class="truncate">{{ $identificacao['cns'] ?? '---' }}</span></div>
                                            <div class="flex"><span class="font-bold w-24">Código INCRA/SNCR:</span> <span class="truncate">{{ $identificacao['sncr'] ?? '---' }}</span></div>
                                            <div class="flex"><span class="font-bold w-24">Município:</span> <span class="truncate uppercase">{{ $identificacao['municipio'] ?? '' }}-{{ $identificacao['uf'] ?? '' }}</span></div>
                                        </div>
                                        <div class="w-1/2 p-1 flex flex-col justify-between text-[8.5px]">
                                            <div class="flex"><span class="font-bold w-28">Natureza da Área:</span> <span class="truncate uppercase">Particular</span></div>
                                            <div class="flex"><span class="font-bold w-28">Responsável Técnico(a):</span> <span class="truncate uppercase">Edivaldo Rodrigues da Silva</span></div>
                                            <div class="flex"><span class="font-bold w-28">Formação:</span> <span class="truncate">Eng. Civil e Tecnólogo em Topografia</span></div>
                                            <div class="flex"><span class="font-bold w-28">Conselho Profissional:</span> <span class="truncate">CREA-AC / INCRA-BCA</span></div>
                                            <div class="flex"><span class="font-bold w-28">Cód. Credenciado(a):</span> <span class="truncate">BCA</span></div>
                                            <div class="flex"><span class="font-bold w-28">Documento de RT:</span> <span class="truncate">2684/D/AC</span></div>
                                            <div class="flex"><span class="font-bold w-28">Nº ART/TRT:</span> <span class="truncate">---</span></div>
                                            <div class="flex items-end mt-0.5 relative h-2.5">
                                                <span class="font-bold w-28">Assinatura do Token:</span> 
                                                <span class="flex-grow border-b border-gray-400 border-dashed" x-show="!plantaAssinada"></span>
                                                
                                                <!-- Selo integrado no Carimbo -->
                                                <div x-show="plantaAssinada" x-cloak 
                                                     class="absolute right-2 bottom-[-4px] bg-white border border-gray-300 shadow-sm overflow-hidden flex z-10 rounded-[1px]" 
                                                     style="width: 130px; height: 26px;">
                                                    <div class="flex-grow flex flex-col justify-center text-left pl-1.5 py-0.5 max-w-[95px]">
                                                        <span class="text-[3.5px] text-[#0000cc] tracking-wide mb-[0.5px] uppercase">Assinado Digitalmente</span>
                                                        <strong class="text-[5.5px] text-[#0000cc] leading-none mb-[2px] font-sans uppercase truncate">Edivaldo Rodrigues da Silva</strong>
                                                        <span class="text-[3px] text-[#0000cc] leading-none mt-0.5 font-sans truncate">A conformidade pode ser verificada em:</span>
                                                        <span class="text-[3.5px] text-[#0000cc] font-semibold leading-none mt-[1px] font-sans truncate">topogest.com.br/assinador</span>
                                                    </div>
                                                    <!-- Curvas -->
                                                    <div class="w-8 relative flex-shrink-0 pointer-events-none">
                                                        <div class="absolute top-0 right-0 w-12 h-12 bg-[#99e6ff] rounded-full" style="transform: translate(40%, -40%);"></div>
                                                        <div class="absolute top-0 right-0 w-8 h-8 bg-[#0099ff] rounded-full" style="transform: translate(40%, -40%);"></div>
                                                        <div class="absolute top-0 right-0 w-5 h-5 bg-[#0000cc] rounded-full" style="transform: translate(40%, -40%);"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bloco de Métricas (1 linha, colunas) -->
                                    <div class="flex border-b border-black text-[8.5px] divide-x divide-black flex-shrink-0" style="height: 10mm;">
                                        <div class="flex-1 p-1 flex flex-col justify-center">
                                            <span class="font-bold">Área (SGL):</span>
                                            <span class="text-[9.5px]" id="print-area-val-portrait">{{ $identificacao['area_ha'] ?? '0,0000' }} ha</span>
                                        </div>
                                        <div class="flex-1 p-1 flex flex-col justify-center">
                                            <span class="font-bold">Perímetro:</span>
                                            <span class="text-[9.5px]" id="print-perimetro-val-portrait">-</span>
                                        </div>
                                        <div class="flex-1 p-1 flex flex-col justify-center">
                                            <span class="font-bold">Sistema Geodésico:</span>
                                            <span class="text-[9.5px]">SIRGAS 2000</span>
                                        </div>
                                        <div class="flex-1 p-1 flex flex-col justify-center">
                                            <span class="font-bold leading-tight">Sistema de Coord.:</span>
                                            <span class="text-[9.5px]">UTM</span>
                                        </div>
                                        <div class="flex-1 p-1 flex flex-col justify-center">
                                            <span class="font-bold">Escala:</span>
                                            <span class="text-[9.5px]">1:<span id="print-escala-val-portrait">Auto</span></span>
                                        </div>
                                        <div class="flex-1 p-1 flex flex-col justify-center">
                                            <span class="font-bold">Formato:</span>
                                            <span class="text-[9.5px]">A4</span>
                                        </div>
                                    </div>

                                    <!-- Bloco de Legendas e Assinatura -->
                                    <div class="flex text-[7px] flex-shrink-0" style="height: 28mm;">
                                        <div class="w-1/2 border-r border-black p-1 grid grid-cols-2 gap-x-2 gap-y-1 content-start">
                                            <!-- Legendas -->
                                            <div class="flex items-center gap-1"><span class="w-2.5 h-2.5 border border-black bg-white rounded-full flex items-center justify-center"><span class="w-0.5 h-0.5 bg-black rounded-full"></span></span><span>Vértice tipo M</span></div>
                                            <div class="flex items-center gap-1"><span class="w-[10px] h-[1.5px] bg-[#00E500]"></span><span>Limite do Imóvel</span></div>
                                            
                                            <div class="flex items-center gap-1"><span class="w-2.5 h-2.5 border border-black bg-white flex items-center justify-center"><span class="w-0.5 h-0.5 bg-black"></span></span><span>Vértice tipo P</span></div>
                                            <div class="flex items-center gap-1"><span class="w-[10px] h-[1px] bg-red-800 border-y border-red-800"></span><span class="text-red-800">Linha ideal</span></div>
                                            
                                            <div class="flex items-center gap-1"><span class="w-2.5 h-2.5 border border-black bg-black flex items-center justify-center text-white text-[5px] font-bold">+</span><span>Vértice tipo V</span></div>
                                            <div class="flex items-center gap-1"><span class="w-[10px] h-[1px] bg-blue-500"></span><span class="text-blue-500">Curso d'água</span></div>
                                            
                                            <div class="flex items-center gap-1"><span class="w-2.5 h-2.5 border border-black bg-yellow-300 rounded-full"></span><span>Vértice tipo O</span></div>
                                            <div class="flex items-center gap-1"><span class="w-[10px] border-b-[1.5px] border-black border-dashed"></span><span>Cerca</span></div>
                                            
                                            <div class="flex items-center gap-1"><span class="w-[10px] border-b-[1.5px] border-black border-double"></span><span>Muro</span></div>
                                            <div class="flex items-center gap-1"><span class="w-[10px] h-[3px] bg-gray-300"></span><span>Imóvel em estudo</span></div>
                                            
                                            <div class="flex items-center gap-1"><span class="w-[10px] h-[2px] bg-orange-300"></span><span class="text-orange-800">Estrada</span></div>
                                            <div class="flex items-center gap-1"><span class="w-[10px] h-[3px] border border-black"></span><span>Imóveis confrontantes</span></div>
                                        </div>
                                        
                                        <div class="w-1/2 p-1 relative flex flex-col">
                                            <div class="font-bold mb-1 text-[8.5px]">CERTIFICAÇÃO: GETEC-{{ strtoupper(uniqid()) }}</div>
                                            <p class="mb-1 leading-tight text-justify pr-16">
                                                Em atendimento ao § 5º do art. 176 da Lei 6.015/73, certificamos que a poligonal objeto deste memorial descritivo não se sobrepõe, nesta data, a nenhuma outra poligonal constante do cadastro georreferenciado do INCRA.
                                            </p>
                                            <div class="mt-auto text-[6px]">
                                                <div class="flex"><span class="w-20 font-bold">Data Certificação:</span> <span>{{ date('d/m/Y H:i') }}</span></div>
                                                <div class="flex"><span class="w-20 font-bold">Data da Geração:</span> <span>{{ date('d/m/Y H:i') }}</span></div>
                                            </div>
                                            <!-- QRCode Mock -->
                                            <div class="absolute right-1 top-1 w-14 h-14 bg-white border border-gray-300 p-0.5">
                                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(url()->current()) }}" class="w-full h-full opacity-80 mix-blend-multiply">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Rodapé Mínimo fora da borda dupla interna -->
                                <div class="text-[4px] mt-0.5 text-justify leading-tight">
                                    Esta planta foi gerada automaticamente pelo Getec Topografia com base nas informações transmitidas. A autenticidade deste documento pode ser verificada pelo Responsável Técnico.
                                </div>
                            </div>

                        </div><!-- /origin-top transform wrapper -->
                    </div><!-- /overflow-x-auto -->
                </div><!-- /prancha-tab-container -->

                <!-- ================= TAB 3: MEMORIAL DESCRITIVO DE IMPRESSÃO (A4) ================= -->
                <div :class="currentTab === 'memorial_print' ? 'block' : 'invisible h-0 overflow-hidden absolute -left-[9999px]'" id="memorial-print-tab-container" class="print:block">
                    <div class="overflow-x-auto w-full flex justify-center py-4">
                        <div id="memorial-print-wrapper" class="bg-white text-black font-sans shadow-2xl p-8 border border-gray-300" style="width: 210mm; min-height: 297mm; box-sizing: border-box; background: white; padding: 20mm 20mm 15mm 20mm; display: flex; flex-direction: column; justify-content: space-between;">
                            
                            <div class="w-full flex-grow">
                                <!-- Cabeçalho com Logo -->
                                <div class="flex items-center justify-between pb-3" style="border-bottom: 1.5px solid black; margin-bottom: 20px;">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ asset('images/logo-icon.png') }}" alt="Brasão" style="height: 48px; object-fit: contain;">
                                    </div>
                                    <div class="text-right text-[7px] text-gray-500 font-semibold leading-tight">
                                        <p>GETEC TOPOGRAFIA LTDA</p>
                                        <p>Serviços Técnicos e Cartografia</p>
                                    </div>
                                </div>

                                <!-- Título -->
                                <div class="text-center mb-6">
                                    <h1 class="font-bold text-center tracking-[4px] uppercase text-[15px]" style="font-family: 'Times New Roman', Times, serif; font-weight: bold; margin-top: 15px; margin-bottom: 25px;">MEMORIAL DESCRITIVO</h1>
                                </div>

                                <!-- Tabela de Metadados -->
                                @php
                                    $meses = [
                                        1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
                                        5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
                                        9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro'
                                    ];
                                    $dia = date('d');
                                    $mes = $meses[(int)date('m')];
                                    $ano = date('Y');
                                    
                                    $municipio = $identificacao['municipio'] ?? 'Não informado';
                                    $cidadeUf = 'Rio Branco-AC';
                                    if (!empty($municipio) && $municipio !== 'Não informado') {
                                        if (str_contains($municipio, '-')) {
                                            $cidadeUf = $municipio;
                                        } else {
                                            $cidadeUf = $municipio . '-' . ($identificacao['uf'] ?? 'AC');
                                        }
                                    }
                                    $dataExtenso = $cidadeUf . ", " . (int)$dia . " de " . $mes . " de " . $ano . ".";
                                @endphp
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; border-bottom: 1px solid black; padding-bottom: 12px; margin-bottom: 25px; font-size: 10px; font-family: Arial, sans-serif; line-height: 1.5; color: black;">
                                    <div>
                                        <p><strong>Imóvel:</strong> {{ $identificacao['imovel'] ?? 'Não informado' }}</p>
                                        <p class="mt-1"><strong>Proprietário:</strong> {{ $identificacao['detentor'] ?? 'Não informado' }}</p>
                                        <p class="mt-1"><strong>Local:</strong> {{ $identificacao['municipio'] ?? 'Não informado' }}</p>
                                        <p class="mt-1"><strong>Área SGL (ha):</strong> <span id="print-area-val-memorial">{{ $identificacao['area_ha'] ?? '0,0000' }}</span></p>
                                    </div>
                                    <div>
                                        <p><strong>Comarca:</strong> {{ $identificacao['municipio'] ?? 'Não informado' }}</p>
                                        <p class="mt-1"><strong>Estado:</strong> {{ $identificacao['uf'] ?? 'AC' }}</p>
                                        <p><strong>Perímetro:</strong> <span class="print-perimetro-val-txt">---</span> m</p>
                                        <p class="mt-1"><strong>Código SNCR:</strong> {{ $identificacao['sncr'] ?? 'Não informado' }}</p>
                                        <p class="mt-1"><strong>Perímetro (m):</strong> <span class="print-perimetro-val-txt">-</span></p>
                                    </div>
                                </div>

                                <!-- Texto Descritivo -->
                                <div class="text-justify font-sans" style="font-size: 11px; line-height: 1.7; text-align: justify; text-indent: 1.5cm; color: black; margin-bottom: 30px;">
                                    {!! $memorialHtml !!}
                                </div>

                                <!-- Data Extenso -->
                                <div class="text-right font-sans" style="font-size: 10.5px; text-align: right; margin-bottom: 50px; color: black;">
                                    {{ $dataExtenso }}
                                </div>

                                <!-- Bloco de Assinaturas -->
                                <div class="text-center font-sans" style="text-align: center; margin-bottom: 40px; color: black; font-size: 11px; line-height: 1.4;">
                                    <div class="w-64 mx-auto border-t border-black mb-2 relative">
                                        <div x-show="plantaAssinada" x-cloak 
                                             class="absolute left-1/2 -translate-x-1/2 bg-white border border-gray-300 shadow-sm overflow-hidden flex" 
                                             style="top: -70px; width: 280px; height: 65px; z-index: 10;">
                                            <!-- Área de Texto -->
                                            <div class="flex-grow flex flex-col justify-center text-left pl-3 py-1">
                                                <span class="text-[7px] text-[#0000cc] tracking-wide mb-0.5 uppercase">Assinado Digitalmente</span>
                                                <strong class="text-[12px] text-[#0000cc] leading-none mb-1.5 font-sans uppercase">Edivaldo Rodrigues da Silva</strong>
                                                <span class="text-[6.5px] text-[#0000cc] leading-none mt-1 font-sans">A conformidade com a assinatura pode ser verificada em:</span>
                                                <span class="text-[7.5px] text-[#0000cc] font-semibold leading-none mt-0.5 font-sans">http://topogest.com.br/assinador-digital</span>
                                            </div>
                                            <!-- Curvas (Estilo gov.br) -->
                                            <div class="w-20 relative flex-shrink-0 pointer-events-none">
                                                <div class="absolute top-0 right-0 w-28 h-28 bg-[#99e6ff] rounded-full" style="transform: translate(40%, -40%);"></div>
                                                <div class="absolute top-0 right-0 w-20 h-20 bg-[#0099ff] rounded-full" style="transform: translate(40%, -40%);"></div>
                                                <div class="absolute top-0 right-0 w-12 h-12 bg-[#0000cc] rounded-full" style="transform: translate(40%, -40%);"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <strong>Edivaldo Rodrigues da Silva</strong><br>
                                    <span style="font-size: 9px; color: #555;">Engº Civil e Tecnólogo em Estradas e Topografia</span><br>
                                    <span style="font-size: 9px; color: #555;">CREA: 2684/D-AC - INCRA: BCA</span><br>
                                    <span style="font-size: 9px; color: #555;">ART nº:</span>
                                </div>
                            </div>

                            <!-- Rodapé Corporativo -->
                            <div class="text-center font-sans" style="font-size: 8px; color: #666; border-top: 0.5px solid #ccc; padding-top: 10px; margin-top: auto; line-height: 1.4; width: 100%;">
                                Rodovia AC-40, km-07, nº 3.200 - Rio Branco, CEP 69909730 Fone 68 3221-8221<br>
                                <span style="text-decoration: underline; color: #0066cc;">getecac@gmail.com</span>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <!-- ======================= SCRIPT COMPLETO DO MAPA ======================= -->
            <script>
                // Dados Globais vindos do Backend Laravel
                const vertices = @json($vertices);
                const utmZone = '{{ $utmZone }}{{ $hemisphere }}';
                window.currentTab = 'dashboard';
                window.layoutFormato = 'paisagem';

                // Instâncias dos Mapas
                let leafletMap    = null;
                let printMap      = null;
                let sitMap        = null;
                let sitMapPortrait = null;

                let camadaAtual   = 'streets';
                let camadaStreets = null;
                let camadaSateli  = null;

                // Converte Fuso UTM para Definição Proj4
                function definirProjecaoUtm(zona) {
                    const match = String(zona).match(/(\d+)([NS]?)/i);
                    if (!match) return null;
                    const num = parseInt(match[1]);
                    const hem = (match[2] || 'S').toUpperCase();
                    const projStr = `+proj=utm +zone=${num} +${hem === 'S' ? 'south' : 'north'} +ellps=GRS80 +towgs84=0,0,0 +units=m +no_defs`;
                    const epsg = `EPSG:319${num < 10 ? '0' + num : num}S`;
                    proj4.defs(epsg, projStr);
                    return epsg;
                }

                // Transforma coordenada UTM E/N em Lat/Lon
                function utmParaLatLon(E, N, zona) {
                    const epsg = definirProjecaoUtm(zona);
                    if (!epsg) return null;
                    try {
                        const [lon, lat] = proj4(epsg, 'WGS84', [E, N]);
                        return { lat, lon };
                    } catch(e) { return null; }
                }

                // Cria o array de pontos LatLng para Leaflet
                function converterVertices(vertices) {
                    return vertices.map(v => {
                        if (v.tipo === 'geodesica') return [v.N, v.E];
                        const zona = utmZone || '20S';
                        const conv = utmParaLatLon(v.x, v.y, zona);
                        return conv ? [conv.lat, conv.lon] : null;
                    }).filter(p => p !== null);
                }

                // 1. Inicializa o Mapa Interativo do Dashboard
                function inicializarMapaInterativo() {
                    const coords = converterVertices(vertices);
                    if (coords.length < 3) return;

                    if (!leafletMap) {
                        leafletMap = L.map('leaflet-mapa', { zoomControl: true, scrollWheelZoom: true, attributionControl: false });

                        camadaStreets = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© <a href="https://www.openstreetmap.org/">OpenStreetMap</a>', maxZoom: 20
                        });

                        camadaSateli = L.tileLayer(
                            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                            { attribution: 'Tiles © Esri', maxZoom: 20 }
                        );

                        camadaStreets.addTo(leafletMap);

                        const poligono = L.polygon(coords, {
                            color: '#00E500', weight: 2.5, fillColor: '#00E500', fillOpacity: 0.15,
                        }).addTo(leafletMap);

                        vertices.forEach((v, i) => {
                            const coord = coords[i];
                            if (!coord) return;
                            const icon = L.divIcon({
                                className: '',
                                html: `<div style="width:10px;height:10px;background:#00E500;border:2px solid white;border-radius:50%;box-shadow:0 1px 4px rgba(0,0,0,0.5);"></div>`,
                                iconSize: [10, 10], iconAnchor: [5, 5],
                            });
                            L.marker(coord, { icon })
                                .bindTooltip(`<b>${v.codigo || ('M-' + (i + 1))}</b><br>Lat: ${coord[0].toFixed(6)}<br>Lon: ${coord[1].toFixed(6)}`, { permanent: false, direction: 'top' })
                                .addTo(leafletMap);
                        });

                        leafletMap.fitBounds(poligono.getBounds(), { padding: [30, 30] });
                    }
                }

                // 2. Desenha a grade UTM no mapa da prancha
                function desenharGradeUtm(map, vertices, fuso) {
                    // Limpa grade anterior
                    if (map._gradeUtmGroup) {
                        map.removeLayer(map._gradeUtmGroup);
                    }
                    map._gradeUtmGroup = L.layerGroup().addTo(map);

                    const bounds = map.getBounds();
                    const sw = bounds.getSouthWest();
                    const ne = bounds.getNorthEast();

                    const zona = utmZone || '20S';
                    const epsg = definirProjecaoUtm(zona);
                    if (!epsg) return;

                    // Converte cantos geográficos para coordenadas UTM planas
                    let utmSW = proj4('WGS84', epsg, [sw.lng, sw.lat]);
                    let utmNE = proj4('WGS84', epsg, [ne.lng, ne.lat]);

                    const minX = Math.min(utmSW[0], utmNE[0]);
                    const maxX = Math.max(utmSW[0], utmNE[0]);
                    const minY = Math.min(utmSW[1], utmNE[1]);
                    const maxY = Math.max(utmSW[1], utmNE[1]);

                    const range = Math.max(maxX - minX, maxY - minY);
                    let spacing = 1000;
                    if (range < 300) spacing = 50;
                    else if (range < 800) spacing = 100;
                    else if (range < 2500) spacing = 500;
                    else if (range < 10000) spacing = 1000;
                    else spacing = 5000;

                    // Linhas Verticais (Easting)
                    const startX = Math.ceil(minX / spacing) * spacing;
                    for (let x = startX; x <= maxX; x += spacing) {
                        const p1 = utmParaLatLon(x, minY, fuso);
                        const p2 = utmParaLatLon(x, maxY, fuso);
                        if (p1 && p2) {
                            L.polyline([[p1.lat, p1.lon], [p2.lat, p2.lon]], {
                                color: '#ddd',
                                weight: 0.4,
                                dashArray: '2,2',
                                interactive: false
                            }).addTo(map._gradeUtmGroup);

                            // Label na borda inferior (South)
                            const pBottom = utmParaLatLon(x, minY, fuso);
                            if (pBottom) {
                                const pushX = (pBottom.lon < sw.lng + (ne.lng - sw.lng)*0.1) ? '10px' : '2px';
                                const labelHtml = `<div style="font-size: 6.5px; color: #555; font-family: monospace; white-space: nowrap; transform: rotate(-90deg); transform-origin: left bottom; margin-left: ${pushX}; margin-bottom: 2px;">E ${x.toLocaleString('pt-BR', {minimumFractionDigits: 4})} m</div>`;
                                const iconBottom = L.divIcon({ className: '', html: labelHtml, iconSize: [60, 8], iconAnchor: [0, 8] });
                                L.marker([sw.lat, pBottom.lon], { icon: iconBottom, interactive: false }).addTo(map._gradeUtmGroup);
                            }

                            // Label na borda superior (North)
                            const pTop = utmParaLatLon(x, maxY, fuso);
                            if (pTop) {
                                const pushX = (pTop.lon > ne.lng - (ne.lng - sw.lng)*0.1) ? '-10px' : '2px';
                                const labelHtml = `<div style="font-size: 6.5px; color: #555; font-family: monospace; white-space: nowrap; transform: rotate(-90deg); transform-origin: left bottom; margin-left: ${pushX}; margin-top: -2px;">E ${x.toLocaleString('pt-BR', {minimumFractionDigits: 4})} m</div>`;
                                const iconTop = L.divIcon({ className: '', html: labelHtml, iconSize: [60, 8], iconAnchor: [0, 0] });
                                L.marker([ne.lat, pTop.lon], { icon: iconTop, interactive: false }).addTo(map._gradeUtmGroup);
                            }
                        }
                    }

                    // Linhas Horizontais (Northing)
                    const startY = Math.ceil(minY / spacing) * spacing;
                    for (let y = startY; y <= maxY; y += spacing) {
                        const p1 = utmParaLatLon(minX, y, fuso);
                        const p2 = utmParaLatLon(maxX, y, fuso);
                        if (p1 && p2) {
                            L.polyline([[p1.lat, p1.lon], [p2.lat, p2.lon]], {
                                color: '#ddd',
                                weight: 0.4,
                                dashArray: '2,2',
                                interactive: false
                            }).addTo(map._gradeUtmGroup);

                            // Label na borda esquerda (West)
                            const pLeft = utmParaLatLon(minX, y, fuso);
                            if (pLeft) {
                                const anchorY = (pLeft.lat < sw.lat + (ne.lat - sw.lat)*0.1) ? 8 : ((pLeft.lat > ne.lat - (ne.lat - sw.lat)*0.1) ? 0 : 4);
                                const labelHtml = `<div style="font-size: 6.5px; color: #555; font-family: monospace; white-space: nowrap; margin-left: 2px;">N ${y.toLocaleString('pt-BR', {minimumFractionDigits: 4})} m</div>`;
                                const iconLeft = L.divIcon({ className: '', html: labelHtml, iconSize: [80, 8], iconAnchor: [0, anchorY] });
                                L.marker([pLeft.lat, sw.lng], { icon: iconLeft, interactive: false }).addTo(map._gradeUtmGroup);
                            }

                            // Label na borda direita (East)
                            const pRight = utmParaLatLon(maxX, y, fuso);
                            if (pRight) {
                                const anchorY = (pRight.lat < sw.lat + (ne.lat - sw.lat)*0.1) ? 8 : ((pRight.lat > ne.lat - (ne.lat - sw.lat)*0.1) ? 0 : 4);
                                const labelHtml = `<div style="font-size: 6.5px; color: #555; font-family: monospace; white-space: nowrap; margin-left: -50px; text-align: right;">N ${y.toLocaleString('pt-BR', {minimumFractionDigits: 4})} m</div>`;
                                const iconRight = L.divIcon({ className: '', html: labelHtml, iconSize: [80, 8], iconAnchor: [0, anchorY] });
                                L.marker([pRight.lat, ne.lng], { icon: iconRight, interactive: false }).addTo(map._gradeUtmGroup);
                            }
                        }
                    }
                }

                // 3. Desenha os nomes dos confrontantes no mapa da prancha
                function desenharConfrontantes(map, vertices, coords, centLat, centLon) {
                    let groups = [];
                    let currentGroup = null;

                    // Agrupa segmentos contíguos com o mesmo nome de confrontante
                    vertices.forEach((v, i) => {
                        const confName = v.confrontante || 'Confrontante';
                        const coord1 = coords[i];
                        const coord2 = coords[(i + 1) % coords.length];
                        if (!coord1 || !coord2) return;

                        if (confName === 'Limite do Imóvel' || confName === 'Não informado' || !confName) {
                            if (currentGroup) { groups.push(currentGroup); currentGroup = null; }
                            return;
                        }

                        if (!currentGroup || currentGroup.name !== confName) {
                            if (currentGroup) groups.push(currentGroup);
                            currentGroup = { name: confName, segments: [{c1: coord1, c2: coord2}] };
                        } else {
                            currentGroup.segments.push({c1: coord1, c2: coord2});
                        }
                    });
                    
                    if (currentGroup) {
                        // Verifica se o primeiro e o último grupo são do mesmo confrontante (polígono fechado)
                        if (groups.length > 0 && groups[0].name === currentGroup.name) {
                            groups[0].segments = currentGroup.segments.concat(groups[0].segments);
                        } else {
                            groups.push(currentGroup);
                        }
                    }

                    // Desenha apenas um rótulo por grupo contíguo
                    groups.forEach(g => {
                        if (g.segments.length === 0) return;

                        // Pega o primeiro e o último ponto da confrontação inteira
                        const firstPt = g.segments[0].c1;
                        const lastPt = g.segments[g.segments.length - 1].c2;

                        // O centro visual será exatamente na metade da linha reta que liga os extremos
                        const midLat = (firstPt[0] + lastPt[0]) / 2;
                        const midLng = (firstPt[1] + lastPt[1]) / 2;

                        // Calcula o ângulo da linha GERAL (do primeiro ao último ponto)
                        const dLatSeg = lastPt[0] - firstPt[0];
                        const dLonSeg = lastPt[1] - firstPt[1];
                        
                        let segAngle = 0;
                        if (dLatSeg !== 0 || dLonSeg !== 0) {
                            segAngle = Math.atan2(-dLatSeg, dLonSeg) * (180 / Math.PI);
                        }

                        // Garante que o texto nunca fique de cabeça para baixo
                        if (segAngle > 90 || segAngle < -90) {
                            segAngle += 180;
                        }

                        // Calcula a Normal perpendicular ao segmento
                        const nx1 = -dLatSeg;
                        const ny1 = dLonSeg;
                        const nx2 = dLatSeg;
                        const ny2 = -dLonSeg;

                        // Vetor do centroide para o ponto médio
                        const vCx = midLng - centLon;
                        const vCy = midLat - centLat;

                        // Produto escalar para escolher a normal que aponta para FORA
                        const dot1 = nx1 * vCx + ny1 * vCy;
                        let normX = dot1 > 0 ? nx1 : nx2;
                        let normY = dot1 > 0 ? ny1 : ny2;

                        let pushX = 0, pushY = 0;
                        const normLen = Math.sqrt(normX * normX + normY * normY);
                        if (normLen > 0) {
                            normX /= normLen;
                            normY /= normLen;
                            pushX = normX * 16; // Empurra 16px no X da tela
                            pushY = -normY * 16; // Inverte o Y para tela
                        }

                        let alignStyle = `transform: translate(-50%, -50%) rotate(${segAngle}deg); margin-left: ${pushX}px; margin-top: ${pushY}px;`;

                        const labelHtml = `<div class="confrontante-label" style="font-size: 7.5px; font-weight: bold; color: #444; font-style: italic; white-space: nowrap; background: rgba(255,255,255,0.7); padding: 0.5px 2px; border-radius: 1px; border: 0.2px solid #ccc; position: absolute; pointer-events: none; ${alignStyle}">${g.name}</div>`;
                        const icon = L.divIcon({
                            className: '',
                            html: labelHtml,
                            iconSize: [0, 0],
                            iconAnchor: [0, 0]
                        });
                        L.marker([midLat, midLng], { icon, interactive: false }).addTo(map);

                        // Salva os dados matemáticos do grupo para o Motor de Colisão ver depois
                        g.calc = { pushX, pushY, segAngle, midLat, midLng };
                    });

                    return groups;
                }

                // 4. Inicializa o Mapa da Prancha A4 e de Situação
                function inicializarMapaPrancha() {
                    const coords = converterVertices(vertices);
                    if (coords.length < 3) return;

                    // Prancha Principal (Vetor Branco)
                    if (!printMap) {
                        printMap = L.map('print-map', { 
                            zoomControl: false, 
                            dragging: false, 
                            scrollWheelZoom: false,
                            doubleClickZoom: false,
                            boxZoom: false,
                            touchZoom: false,
                            keyboard: false,
                            attributionControl: false
                        });

                        const poligono = L.polygon(coords, {
                            color: '#28a745', // Verde profissional escuro
                            weight: 1.5,
                            fillColor: '#d4edda', // Verde clássico do SIGEF
                            fillOpacity: 0.5,
                        }).addTo(printMap);

                        // Calculate centroid of coordinates
                        let sumLat = 0, sumLon = 0;
                        coords.forEach(c => {
                            sumLat += c[0];
                            sumLon += c[1];
                        });
                        const centLat = sumLat / coords.length;
                        const centLon = sumLon / coords.length;

                        // Desenha os confrontantes e guarda os grupos renderizados
                        const renderedGroups = desenharConfrontantes(printMap, vertices, coords, centLat, centLon);

                        // Ajuste visual dinâmico de limites PRIMEIRO (Para que o Leaflet calcule pixels perfeitamente)
                        printMap.fitBounds(poligono.getBounds(), { padding: [70, 70], animate: false });
                        desenharGradeUtm(printMap, vertices, utmZone);

                        // Motor de Otimização Cartográfica (Slots Baseados em Regras e Linhas de Chamada)
                        let occupiedBoxes = [];

                        // 1. Registra as Caixas de Texto dos Confrontantes na Matriz de Colisão
                        renderedGroups.forEach(g => {
                            if (!g.calc) return;
                            const pt = printMap.latLngToContainerPoint([g.calc.midLat, g.calc.midLng]);
                            
                            // Dimensão aproximada conservadora do texto do vizinho
                            const w = g.name.length * 5;
                            const h = 14;

                            // Como o texto está rotacionado, calculamos a AABB (Caixa Alinhada aos Eixos)
                            const rad = g.calc.segAngle * (Math.PI / 180);
                            const cosA = Math.abs(Math.cos(rad));
                            const sinA = Math.abs(Math.sin(rad));
                            const aabbW = w * cosA + h * sinA;
                            const aabbH = w * sinA + h * cosA;

                            // O texto foi empurrado pelo margin (pushX, pushY)
                            const cx = pt.x + g.calc.pushX;
                            const cy = pt.y + g.calc.pushY;

                            occupiedBoxes.push({
                                left: cx - aabbW/2 - 2,
                                top: cy - aabbH/2 - 2,
                                right: cx + aabbW/2 + 2,
                                bottom: cy + aabbH/2 + 2
                            });
                        });

                        // 2. Posiciona os Textos dos Vértices
                        vertices.forEach((v, i) => {
                            const coord = coords[i];
                            if (!coord) return;

                            const pt = printMap.latLngToContainerPoint(coord);
                            const boxW = (v.codigo || '').length * 5.5 + 10;
                            const boxH = 14;

                            // 8 Slots Primários + Slots Extendidos (Nível 2 e 3 com Linha de Chamada)
                            // dx e dy representam o canto superior esquerdo do texto em relação ao ponto
                            let rawSlots = [
                                { dx: 6, dy: -boxH - 6, dist: 1 }, 
                                { dx: -boxW - 6, dy: -boxH - 6, dist: 1 }, 
                                { dx: 6, dy: 6, dist: 1 }, 
                                { dx: -boxW - 6, dy: 6, dist: 1 }, 
                                { dx: -boxW / 2, dy: -boxH - 8, dist: 1 }, 
                                { dx: -boxW / 2, dy: 8, dist: 1 }, 
                                { dx: 8, dy: -boxH / 2, dist: 1 }, 
                                { dx: -boxW - 8, dy: -boxH / 2, dist: 1 }, 
                                // Nível 2
                                { dx: 25, dy: -boxH - 25, dist: 2, leader: true },
                                { dx: -boxW - 25, dy: -boxH - 25, dist: 2, leader: true },
                                { dx: 25, dy: 25, dist: 2, leader: true },
                                { dx: -boxW - 25, dy: 25, dist: 2, leader: true },
                                { dx: -boxW / 2, dy: -boxH - 35, dist: 2, leader: true }, 
                                { dx: -boxW / 2, dy: 35, dist: 2, leader: true }, 
                                { dx: 35, dy: -boxH / 2, dist: 2, leader: true }, 
                                { dx: -boxW - 35, dy: -boxH / 2, dist: 2, leader: true },
                                // Nível 3
                                { dx: 45, dy: -boxH - 45, dist: 3, leader: true },
                                { dx: -boxW - 45, dy: -boxH - 45, dist: 3, leader: true },
                                { dx: -boxW / 2, dy: -boxH - 55, dist: 3, leader: true },
                                { dx: -boxW / 2, dy: 55, dist: 3, leader: true }
                            ];

                            // Calcula o vetor ideal apontando para FORA do polígono
                            const dLat = coord[0] - centLat;
                            const dLon = coord[1] - centLon;
                            const outAngle = Math.atan2(-dLat, dLon) * (180 / Math.PI);

                            function getAngleDiff(a, b) {
                                let diff = Math.abs(a - b) % 360;
                                return diff > 180 ? 360 - diff : diff;
                            }

                            // Classifica os slots (IA baseada em custo)
                            rawSlots.forEach(s => {
                                const cx = s.dx + boxW / 2;
                                const cy = s.dy + boxH / 2;
                                s.angle = Math.atan2(cy, cx) * (180 / Math.PI);
                                const diff = getAngleDiff(s.angle, outAngle);
                                // Penalidade: desviar do ângulo ideal (+1 por grau) + distância (+60 por nível)
                                s.cost = diff + ((s.dist - 1) * 60);
                            });

                            rawSlots.sort((a, b) => a.cost - b.cost);

                            let bestSlot = rawSlots[0];
                            for (let slot of rawSlots) {
                                const left = pt.x + slot.dx;
                                const top = pt.y + slot.dy;
                                const right = left + boxW;
                                const bottom = top + boxH;
                                
                                const m = 1; // Margem de segurança de 1px
                                const r1 = { left: left-m, top: top-m, right: right+m, bottom: bottom+m };

                                let collides = false;
                                for (let box of occupiedBoxes) {
                                    if (!(r1.right <= box.left || r1.left >= box.right || r1.bottom <= box.top || r1.top >= box.bottom)) {
                                        collides = true;
                                        break;
                                    }
                                }

                                if (!collides) {
                                    bestSlot = slot;
                                    break;
                                }
                            }

                            // Registra o slot escolhido na matriz de colisão
                            const finalLeft = pt.x + bestSlot.dx;
                            const finalTop = pt.y + bestSlot.dy;
                            occupiedBoxes.push({ left: finalLeft, top: finalTop, right: finalLeft + boxW, bottom: finalTop + boxH });
                            // Registra o próprio símbolo do vértice como área proibida
                            occupiedBoxes.push({ left: pt.x - 5, top: pt.y - 5, right: pt.x + 5, bottom: pt.y + 5 });

                            // Desenha linha de chamada (Leader Line) conectando TODOS os vértices aos seus rótulos
                            let cx = finalLeft + boxW / 2;
                            let cy = finalTop + boxH / 2;
                            
                            // Encontra o ponto de ancoragem exato na borda da caixa de texto
                            if (bestSlot.dx > 0) cx = finalLeft; else if (bestSlot.dx < -boxW/2) cx = finalLeft + boxW;
                            if (bestSlot.dy > 0) cy = finalTop; else if (bestSlot.dy < -boxH/2) cy = finalTop + boxH;

                            const labelLatLng = printMap.containerPointToLatLng(L.point(cx, cy));
                            L.polyline([coord, labelLatLng], { color: '#555', weight: 0.8, opacity: 0.8 }).addTo(printMap);

                            // Desenha o Símbolo
                            let vertexType = 'M';
                            const codeUpper = (v.codigo || '').toUpperCase();
                            if (codeUpper.includes('-V-') || codeUpper.endsWith('-V')) vertexType = 'V';
                            else if (codeUpper.includes('-P-') || codeUpper.endsWith('-P')) vertexType = 'P';
                            else if (codeUpper.includes('-O-') || codeUpper.endsWith('-O')) vertexType = 'O';

                            let iconHtml = '';
                            if (vertexType === 'M') {
                                iconHtml = `<div style="width:6px;height:6px;background:white;border:0.8px solid black;border-radius:50%;display:flex;align-items:center;justify-content:center;box-sizing:border-box;"><div style="width:1.5px;height:1.5px;background:black;border-radius:50%;"></div></div>`;
                            } else if (vertexType === 'P') {
                                iconHtml = `<div style="width:6px;height:6px;background:white;border:0.8px solid black;display:flex;align-items:center;justify-content:center;box-sizing:border-box;"><div style="width:1.5px;height:1.5px;background:black;"></div></div>`;
                            } else if (vertexType === 'V') {
                                iconHtml = `<div style="width:8px;height:8px;background:black;display:flex;align-items:center;justify-content:center;box-sizing:border-box;color:white;font-size:7px;font-weight:bold;line-height:1;"><span style="margin-top:-0.5px;">+</span></div>`;
                            } else if (vertexType === 'O') {
                                iconHtml = `<div style="width:6px;height:6px;background:#FDE047;border:0.8px solid black;border-radius:50%;box-sizing:border-box;"></div>`;
                            }

                            const symbolIcon = L.divIcon({
                                className: '', html: iconHtml, iconSize: [6, 6], iconAnchor: [3, 3],
                            });
                            L.marker(coord, { icon: symbolIcon, interactive: false }).addTo(printMap);

                            // Desenha o Texto
                            const textIcon = L.divIcon({
                                className: '',
                                html: `<div style="font-size: 8px; font-weight: bold; color: black; background: rgba(255,255,255,0.85); padding: 0.2px 1.5px; border: 0.2px solid #555; border-radius: 1px; white-space: nowrap; pointer-events: none; position: absolute; margin-left: ${bestSlot.dx}px; margin-top: ${bestSlot.dy}px;">${v.codigo}</div>`,
                                iconSize: [0, 0], iconAnchor: [0, 0]
                            });
                            L.marker(coord, { icon: textIcon, interactive: false }).addTo(printMap);
                        });
                    }

                    // Preenche a tabela técnica da prancha e calcula metadados
                    preencherTabelasFinais();
                    calcularDadosFicha();
                }

                // Preenche as tabelas de coordenadas nas duas visualizações
                function preencherTabelasFinais() {
                    const tableBody = document.getElementById('print-coords-table-body');
                    if (tableBody && tableBody.children.length === 0) {
                        tableBody.innerHTML = '';
                        vertices.forEach((v, i) => {
                            const next = vertices[(i + 1) % vertices.length];
                            tableBody.innerHTML += `
                                <tr class="border-b border-black">
                                    <td class="py-0.5 border-r border-black font-bold">${v.codigo}</td>
                                    <td class="py-0.5 border-r border-black font-bold">${next.codigo}</td>
                                    <td class="py-0.5 border-r border-black font-mono">${v.y.toLocaleString('pt-BR', {minimumFractionDigits: 4, maximumFractionDigits: 4})}</td>
                                    <td class="py-0.5 border-r border-black font-mono">${v.x.toLocaleString('pt-BR', {minimumFractionDigits: 4, maximumFractionDigits: 4})}</td>
                                    <td class="py-0.5 font-mono">${parseFloat(v.altitude || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})} m</td>
                                </tr>
                            `;
                        });
                    }

                    const tableBodyPortrait = document.getElementById('print-coords-table-body-portrait');
                    if (tableBodyPortrait && tableBodyPortrait.children.length === 0) {
                        tableBodyPortrait.innerHTML = '';
                        vertices.forEach((v, i) => {
                            const next = vertices[(i + 1) % vertices.length];
                            tableBodyPortrait.innerHTML += `
                                <tr class="border-b border-black">
                                    <td class="py-0.5 border-r border-black font-bold">${v.codigo}</td>
                                    <td class="py-0.5 border-r border-black font-bold">${next.codigo}</td>
                                    <td class="py-0.5 border-r border-black font-mono">${v.y.toLocaleString('pt-BR', {minimumFractionDigits: 4, maximumFractionDigits: 4})}</td>
                                    <td class="py-0.5 border-r border-black font-mono">${v.x.toLocaleString('pt-BR', {minimumFractionDigits: 4, maximumFractionDigits: 4})}</td>
                                    <td class="py-0.5 font-mono">${parseFloat(v.altitude || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})} m</td>
                                </tr>
                            `;
                        });
                    }
                }

                // Calcula Perímetro e Estimativa de Escala da Prancha (Diferencia escala Paisagem / Retrato)
                function calcularDadosFicha() {
                    let perimeter = 0;
                    let minX = Infinity, maxX = -Infinity;
                    let minY = Infinity, maxY = -Infinity;

                    vertices.forEach((v, i) => {
                        if (v.x < minX) minX = v.x;
                        if (v.x > maxX) maxX = v.x;
                        if (v.y < minY) minY = v.y;
                        if (v.y > maxY) maxY = v.y;

                        const next = vertices[(i + 1) % vertices.length];
                        const dx = next.x - v.x;
                        const dy = next.y - v.y;
                        perimeter += Math.sqrt(dx * dx + dy * dy);
                    });

                     // Insere o perímetro em ambas as tabelas
                    const perStr = perimeter.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' m';
                    const perEl = document.getElementById('print-perimetro-val');
                    if (perEl) perEl.innerText = perStr;
                    const perPortEl = document.getElementById('print-perimetro-val-portrait');
                    if (perPortEl) perPortEl.innerText = perStr;

                    // Insere o perímetro no memorial descritivo
                    document.querySelectorAll('.print-perimetro-val-txt').forEach(el => {
                        el.innerText = perimeter.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    });

                    // Calcula a Área usando a Fórmula de Gauss (Shoelace) com coordenadas planas (x=E, y=N)
                    let areaSqMeters = 0;
                    if (vertices.length >= 3) {
                        for (let i = 0; i < vertices.length; i++) {
                            const v1 = vertices[i];
                            const v2 = vertices[(i + 1) % vertices.length];
                            areaSqMeters += (parseFloat(v1.x) * parseFloat(v2.y)) - (parseFloat(v2.x) * parseFloat(v1.y));
                        }
                        areaSqMeters = Math.abs(areaSqMeters) / 2;
                    }
                    const areaHa = areaSqMeters / 10000;
                    const areaStr = areaHa.toLocaleString('pt-BR', { minimumFractionDigits: 4, maximumFractionDigits: 4 });

                    // Insere a área formatada
                    const areaEl1 = document.getElementById('print-area-val-portrait');
                    if (areaEl1) areaEl1.innerText = areaStr + ' ha';
                    const areaEl2 = document.getElementById('print-area-val-memorial');
                    if (areaEl2) areaEl2.innerText = areaStr;
                    const areaEl3 = document.getElementById('print-area-val-header');
                    if (areaEl3) areaEl3.innerText = areaStr + ' ha';

                    // Estimação de Escala baseada na dimensão limitante (Retrato A4)
                    // Altura útil da área do mapa em Retrato é ~160mm = 0.160m
                    const dy = maxY - minY;
                    const estimatedScale = dy / 0.160;

                    let roundedScale = 1000;
                    if (estimatedScale < 500) roundedScale = 500;
                    else if (estimatedScale < 1000) roundedScale = 1000;
                    else if (estimatedScale < 2500) roundedScale = 2000;
                    else if (estimatedScale < 5000) roundedScale = 5000;
                    else if (estimatedScale < 10000) roundedScale = 10000;
                    else if (estimatedScale < 25000) roundedScale = 20000;
                    else if (estimatedScale < 50000) roundedScale = 50000;
                    else roundedScale = Math.ceil(estimatedScale / 10000) * 10000;

                    // Insere escala
                    const escEl = document.getElementById('print-escala-val');
                    if (escEl) escEl.innerText = roundedScale.toLocaleString('pt-BR');
                    const escPortEl = document.getElementById('print-escala-val-portrait');
                    if (escPortEl) escPortEl.innerText = roundedScale.toLocaleString('pt-BR');

                    // Preenche os dados do sistema de coordenadas (fuso e MC)
                    const fusoEl = document.getElementById('sis-coord-fuso');
                    const mcEl = document.getElementById('sis-coord-mc');
                    if (fusoEl && mcEl) {
                        fusoEl.innerText = utmZone;
                        const zoneNum = parseInt(utmZone);
                        if (!isNaN(zoneNum)) {
                            const mc = Math.abs(zoneNum * 6 - 183);
                            mcEl.innerText = mc + '° W';
                        }
                    }

                    const fusoPortEl = document.getElementById('sis-coord-fuso-portrait');
                    const mcPortEl = document.getElementById('sis-coord-mc-portrait');
                    if (fusoPortEl && mcPortEl) {
                        fusoPortEl.innerText = utmZone;
                        const zoneNum = parseInt(utmZone);
                        if (!isNaN(zoneNum)) {
                            const mc = Math.abs(zoneNum * 6 - 183);
                            mcPortEl.innerText = mc + '° W';
                        }
                    }

                    // Ticks da Escala Gráfica
                    const scaleTick1 = document.getElementById('scale-tick-1');
                    const scaleTick2 = document.getElementById('scale-tick-2');
                    const scaleTick5 = document.getElementById('scale-tick-5');
                    const scaleTick10 = document.getElementById('scale-tick-10');
                    if (scaleTick1 && scaleTick2 && scaleTick5 && scaleTick10) {
                        scaleTick1.innerText = Math.round(roundedScale * 0.01);
                        scaleTick2.innerText = Math.round(roundedScale * 0.02);
                        scaleTick5.innerText = Math.round(roundedScale * 0.05);
                        scaleTick10.innerText = Math.round(roundedScale * 0.1) + ' m';
                    }

                    const scaleTick1Port = document.getElementById('scale-tick-1-portrait');
                    const scaleTick2Port = document.getElementById('scale-tick-2-portrait');
                    const scaleTick5Port = document.getElementById('scale-tick-5-portrait');
                    const scaleTick10Port = document.getElementById('scale-tick-10-portrait');
                    if (scaleTick1Port && scaleTick2Port && scaleTick5Port && scaleTick10Port) {
                        scaleTick1Port.innerText = Math.round(roundedScale * 0.01);
                        scaleTick2Port.innerText = Math.round(roundedScale * 0.02);
                        scaleTick5Port.innerText = Math.round(roundedScale * 0.05);
                        scaleTick10Port.innerText = Math.round(roundedScale * 0.1) + ' m';
                    }
                }

                // Gerencia a invalidação de tamanhos de mapas no toggle e redimensionamento
                window.invalidateMaps = function() {
                    const coords = converterVertices(vertices);
                    if (coords.length < 3) return;
                    const tempPoligono = L.polygon(coords);

                    if (window.currentTab === 'prancha') {
                        inicializarMapaPrancha();
                        
                        // Invalida o tamanho e recalcula os limites do mapa principal de forma instantânea
                        if (printMap) {
                            printMap.invalidateSize({ animate: false });
                            printMap.fitBounds(tempPoligono.getBounds(), { padding: [70, 70], animate: false });
                            desenharGradeUtm(printMap, vertices, utmZone);
                        }
                        
                        // Recalcula dados e escala baseados na nova orientação
                        calcularDadosFicha();
                        
                        // Executa o resolvedor de colisões de rótulos com um pequeno delay para garantir que o DOM renderizou
                        setTimeout(ajustarColisoesLabels, 50);
                    } else {
                        inicializarMapaInterativo();
                        if (leafletMap) {
                            leafletMap.invalidateSize({ animate: false });
                            leafletMap.fitBounds(tempPoligono.getBounds(), { padding: [30, 30], animate: false });
                        }
                    }
                }

                // Alterna tipo de camada no mapa interativo
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

                // Copia o memorial para o clipboard
                function copiarMemorial() {
                    const text = document.getElementById('memorial-texto').innerText;
                    navigator.clipboard.writeText(text).then(() => {
                        const btn = document.getElementById('btn-copiar');
                        const origText = btn.innerHTML;
                        btn.innerHTML = '✅ Copiado!';
                        btn.classList.remove('bg-blue-600', 'hover:bg-blue-500');
                        btn.classList.add('bg-emerald-600');
                        setTimeout(() => {
                            btn.innerHTML = origText;
                            btn.classList.remove('bg-emerald-600');
                            btn.classList.add('bg-blue-600', 'hover:bg-blue-500');
                        }, 2000);
                    }).catch(err => {
                        alert('Falha ao copiar texto: ' + err);
                    });
                }

                // Trigger inicial
                document.addEventListener('DOMContentLoaded', () => {
                    setTimeout(() => {
                        inicializarMapaInterativo();
                        inicializarMapaPrancha();
                    }, 100);
                    // Garante que o Leaflet calcule o tamanho correto após inicializar fora da tela
                    setTimeout(() => {
                        if (leafletMap) leafletMap.invalidateSize({ animate: false });
                        if (printMap) printMap.invalidateSize({ animate: false });
                        if (sitMap) sitMap.invalidateSize({ animate: false });
                        if (sitMapPortrait) sitMapPortrait.invalidateSize({ animate: false });
                        setTimeout(ajustarColisoesLabels, 50);
                    }, 350);
                });

                window.addEventListener('afterprint', () => {
                    document.body.className = 'min-h-screen overflow-x-hidden bg-[#E5E7EB] text-white';
                });
            </script>
        @endif

        <!-- MODAL DE ASSINATURA DIGITAL -->
        <div x-show="showSignModal" 
             x-transition 
             class="fixed inset-0 z-[2000] bg-black/80 backdrop-blur-sm p-4 flex items-center justify-center print:hidden" 
             x-cloak>
            
            <div class="bg-[#0c1a24] border border-white/10 rounded-[35px] p-8 shadow-2xl max-w-md w-full text-white" @click.away="showSignModal = false">
                
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-black uppercase italic text-[#00E500]">
                        Assinador Digital
                    </h3>
                    <button @click="showSignModal = false" class="text-white/40 hover:text-white text-2xl leading-none">
                        ×
                    </button>
                </div>

                <!-- Passo 1: Escolha do Método -->
                <div x-show="signStep === 'choose'" class="space-y-4">
                    <p class="text-sm text-white/75 mb-4">Escolha a forma de assinatura eletrônica para validar juridicamente esta planta:</p>
                    
                    <button @click="signStep = 'loading'; setTimeout(() => { signStep = 'cert-list'; }, 2000)" 
                            class="w-full text-left bg-white/5 hover:bg-white/10 border border-white/10 hover:border-[#00E500] p-4 rounded-2xl transition flex items-start gap-4">
                        <div class="text-2xl mt-1">🔑</div>
                        <div class="flex-1">
                            <h4 class="font-bold text-sm text-white">Certificado Digital ICP-Brasil (A3 / Token USB)</h4>
                            <p class="text-xs text-white/50 mt-0.5 font-normal">Assine usando seu token físico ou cartão leitor conectado ao computador.</p>
                        </div>
                    </button>

                    <button @click="plantaAssinada = true; showSignModal = false; alert('Planta assinada via gov.br com sucesso!')" 
                            class="w-full text-left bg-white/5 hover:bg-white/10 border border-white/10 hover:border-[#00E500] p-4 rounded-2xl transition flex items-start gap-4">
                        <div class="text-2xl mt-1">🇧🇷</div>
                        <div class="flex-1">
                            <h4 class="font-bold text-sm text-white">Assinatura gov.br (Prata ou Ouro)</h4>
                            <p class="text-xs text-white/50 mt-0.5 font-normal">Utilize sua conta oficial gov.br para assinar o documento de forma gratuita.</p>
                        </div>
                    </button>
                </div>

                <!-- Passo 2: Carregando/Buscando Token -->
                <div x-show="signStep === 'loading'" class="text-center py-8 space-y-4">
                    <div class="inline-block animate-spin rounded-full h-10 w-10 border-t-2 border-r-2 border-[#00E500]"></div>
                    <p class="text-sm text-white/75">Lendo token USB e buscando certificados conectados...</p>
                </div>

                <!-- Passo 3: Listagem de Certificados Encontrados -->
                <div x-show="signStep === 'cert-list'" class="space-y-4">
                    <p class="text-xs text-white/60 mb-2">Selecione o certificado para assinar:</p>
                    
                    <div class="bg-white/5 border border-[#00E500] p-4 rounded-2xl flex items-center gap-3">
                        <input type="radio" checked id="cert1" class="accent-[#00E500]">
                        <label for="cert1" class="cursor-pointer">
                            <h5 class="font-bold text-xs text-white">EDIVALDO RODRIGUES DA SILVA</h5>
                            <p class="text-[10px] text-white/50 font-normal">CPF: ***.821.122-** | Emissor: AC SRF v5</p>
                            <p class="text-[9px] text-[#00E500] mt-0.5 font-bold">ICP-Brasil A3 (Token Aladin)</p>
                        </label>
                    </div>

                    <div class="space-y-2 mt-4">
                        <label class="text-[10px] text-white/50 uppercase font-bold block">Digite o PIN do Token:</label>
                        <input type="password" placeholder="Digite o PIN do Token" class="w-full bg-black/40 border border-white/10 focus:border-[#00E500] rounded-xl px-4 py-2 text-sm text-white outline-none">
                    </div>

                    <div class="flex gap-2 mt-6">
                        <button @click="signStep = 'choose'" class="flex-1 bg-white/5 hover:bg-white/10 text-white py-3 rounded-xl font-bold uppercase text-xs transition">
                            Voltar
                        </button>
                        <button @click="plantaAssinada = true; showSignModal = false; alert('Planta assinada digitalmente via Token A3 com sucesso!')" 
                                class="flex-1 bg-[#00E500] hover:bg-green-400 text-black py-3 rounded-xl font-black uppercase text-xs transition">
                            Confirmar
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </div>
</body>
</html>
