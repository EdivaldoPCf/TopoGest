@extends('layouts.admin')

@push('styles')
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
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .card-hover {
            transition: all .25s ease;
        }

        .card-hover:hover {
            transform: translateY(-4px) scale(1.02);
        }

        .glow {
            box-shadow:
                0 0 0 rgba(0,0,0,0),
                0 10px 40px rgba(0,0,0,.25);
        }

        .menu-gradient {
            background: linear-gradient(135deg, #003366 0%, #004A7C 100%);
        }

        .menu-gradient:hover {
            background: linear-gradient(135deg, #004A7C 0%, #005d9c 100%);
        }

        .glass-dark {
            background: rgba(0,51,102,.65);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .animate-fade {
            animation: fade .25s ease;
        }

        @keyframes fade {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        .modal-overlay.open,
        [x-show] {
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

        @media print {
            body {
                background: white !important;
                color: black !important;
                font-family: Arial, sans-serif !important;
            }
            /* Reset wrapper paddings and width for print */
            #main-wrapper {
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                box-shadow: none !important;
            }
            /* Hide non-printable elements */
            .fixed, .modal-overlay, header, #sigef-mapa-section header, #sigef-mapa-section #sigef-footer, .flex.gap-2, button, a {
                display: none !important;
            }
            #sigef-mapa-section {
                display: block !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
                background: white !important;
                color: black !important;
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
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
</style>
@endpush

@section('content')
<div class="w-full animate-fade" x-data="app()">


    <!-- HEADER -->
    <div class="mb-8 bg-white/90 backdrop-blur-md p-6 rounded-2xl border border-white shadow-sm inline-block w-max pr-12">
        <h2 class="text-3xl font-black text-slate-800 tracking-tight">{{ $pasta->nome }}</h2>
        <p class="text-slate-600 mt-1 font-medium">Pasta Atual</p>
    </div>


<main class="w-full space-y-8">

                <!-- BREADCRUMBS -->
                <div class="flex items-center flex-wrap gap-2 text-sm text-slate-500 mb-6 bg-white/5 border border-white/10 rounded-2xl px-5 py-3 w-fit">
                    <a href="{{ route('admin.pastas.index') }}" class="hover:text-slate-800 transition">Início</a>
                    @if($pasta->parent)
                        @if($pasta->parent->parent)
                            <span>/</span>
                            <a href="{{ route('admin.pastas.show', $pasta->parent->parent->id) }}" class="hover:text-slate-800 transition">{{ $pasta->parent->parent->nome }}</a>
                        @endif
                        <span>/</span>
                        <a href="{{ route('admin.pastas.show', $pasta->parent->id) }}" class="hover:text-slate-800 transition">{{ $pasta->parent->nome }}</a>
                    @endif
                    <span>/</span>
                    <span class="text-slate-800 font-bold">{{ $pasta->nome }}</span>
                </div>

                @php
                    $isLevel2 = $pasta->parent_id && !$pasta->parent?->parent_id;
                    $isLevel3 = $pasta->parent_id && $pasta->parent?->parent_id && !$pasta->parent?->parent?->parent_id;
                @endphp



                <!-- SUBPASTAS -->
                <div class="bg-white rounded-[32px] border border-slate-200 shadow-sm p-5 md:p-8">

                    <div class="px-8 py-6 border-b border-white/10 flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-black uppercase italic">
                                Subpastas
                            </h2>
                            <p class="text-sm text-slate-500">
                                Organização do serviço
                            </p>
                        </div>
                        <div class="flex items-center gap-3 flex-wrap">
                            @if($isLevel3)
                                <button @click="sincronizarPastaUnica({{ $pasta->id }})" :disabled="syncLocalExecutando" class="bg-emerald-600 hover:bg-emerald-500 text-slate-800 font-black px-4 py-2.5 rounded-xl text-xs transition border border-white/10 uppercase flex items-center gap-2 disabled:opacity-50">
                                    <span x-show="!syncLocalExecutando">🔄 Sincronizar</span>
                                    <span x-show="syncLocalExecutando">Sincronizando...</span>
                                </button>

                                <button @click="{{ $pasta->tipo_servico === 'pendente' ? 'finalizeModal = true' : 'revertModal = true' }}"
                                        title="Controle do Serviço"
                                        class="w-10 h-10 bg-white/5 hover:bg-white/10 flex items-center justify-center rounded-xl text-lg shadow-sm transition">
                                    ⚙️
                                </button>
                                
                                <button type="button" @click="openCpfModal = true"
                                        title="Cliente / Proprietário"
                                        class="w-10 h-10 bg-white/5 hover:bg-white/10 flex items-center justify-center rounded-xl text-lg shadow-sm transition relative">
                                    👥
                                    <div class="absolute -top-1 -right-1 w-3 h-3 rounded-full border-2 border-[#00111f] {{ $pasta->cliente_id ? 'bg-emerald-500' : ($pasta->identificador_cliente ? 'bg-yellow-400' : 'bg-rose-500') }}"></div>
                                </button>
                                
                                <button type="button" @click="openSigefModal = true"
                                        title="Integração SIGEF"
                                        class="w-10 h-10 bg-white/5 hover:bg-white/10 flex items-center justify-center rounded-xl text-lg shadow-sm transition relative">
                                    🌐
                                    <div class="absolute -top-1 -right-1 w-3 h-3 rounded-full border-2 border-[#00111f] {{ $pasta->codigo_sigef ? 'bg-emerald-500' : 'bg-yellow-400' }}"></div>
                                </button>
                            @endif

                            <button type="button" @click="openCreateModal"
                                    class="bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-600/30 font-black uppercase px-5 py-2.5 rounded-xl text-xs shadow-lg transition flex items-center gap-1.5 ml-2">
                                <span>+ {{ $isLevel2 ? 'Novo Imóvel' : 'Nova Pasta' }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Barra de Progresso da Sincronização Local -->
                    <div x-show="syncLocalExecutando" x-cloak class="mx-8 mt-6 bg-white/5 border border-white/10 rounded-2xl p-4 space-y-2 animate-fade">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-slate-500 uppercase tracking-wider">Progresso da Sincronização</span>
                            <span class="text-slate-800" x-text="syncPercent + '% (' + syncCurrent + '/' + syncTotal + ')'">0%</span>
                        </div>
                        <div class="w-full bg-white/10 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-[#00E500] h-2.5 rounded-full transition-all duration-300 shadow-[0_0_10px_#00E500]" :style="'width: ' + syncPercent + '%'"></div>
                        </div>
                        <div class="text-[10px] text-slate-500 truncate" x-text="syncLastFile ? 'Processando: ' + syncLastFile : 'Calculando arquivos na pasta física...'"></div>
                    </div>

                    <div class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                            @forelse($pasta->subpastas as $sub)
                                <div class="bg-white rounded-[32px] border border-slate-200 shadow-sm p-5 md:p-8">
                                    <div>
                                        <div class="flex items-start gap-4 mb-4">
                                            <div class="w-14 h-14 rounded-2xl bg-yellow-500/20 border border-yellow-500/20 flex items-center justify-center text-3xl shrink-0">
                                                📁
                                            </div>
                                            <div class="min-w-0">
                                                <h3 class="font-black uppercase text-slate-800 text-base tracking-wide truncate">
                                                    {{ $sub->nome }}
                                                </h3>
                                                <p class="text-xs text-slate-500 mt-0.5">
                                                    Criada em {{ $sub->created_at->format('d/m/Y') }}
                                                </p>
                                            </div>
                                        </div>

                                        @if($sub->cliente_id)
                                            <!-- Badge Cliente -->
                                            <div class="mt-4 bg-white/5 border border-white/10 rounded-2xl p-4 space-y-2">
                                                <div>
                                                    <span class="text-[9px] uppercase tracking-widest text-slate-500 block font-bold">Cliente</span>
                                                    <span class="text-sm font-black text-slate-800 uppercase truncate block leading-tight">{{ $sub->cliente?->name ?? 'Não Cadastrado' }}</span>
                                                </div>
                                                @if($sub->categoria_servico)
                                                    <div>
                                                        <span class="text-[9px] uppercase tracking-widest text-slate-500 block font-bold">Categoria</span>
                                                        <span class="text-xs font-bold text-slate-800 uppercase truncate block leading-tight">{{ $sub->categoria_servico }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @elseif($sub->identificador_cliente)
                                            <!-- Badge Cliente Pendente/Aguardando Cadastro -->
                                            <div class="mt-4 bg-yellow-500/10 border border-yellow-500/20 rounded-2xl p-4 space-y-2">
                                                <div>
                                                    <span class="text-[9px] uppercase tracking-widest text-yellow-400 block font-bold">Cliente (Não Cadastrado)</span>
                                                    <span class="text-xs font-bold text-slate-800 uppercase truncate block leading-tight">
                                                        CPF: {{ strlen($sub->identificador_cliente) === 11 ? preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "$1.$2.$3-$4", $sub->identificador_cliente) : $sub->identificador_cliente }}
                                                    </span>
                                                    <span class="text-[9px] text-yellow-300/80 block mt-1">Aguardando cadastro do cliente...</span>
                                                </div>
                                                @if($sub->categoria_servico)
                                                    <div>
                                                        <span class="text-[9px] uppercase tracking-widest text-slate-500 block font-bold">Categoria</span>
                                                        <span class="text-xs font-bold text-slate-800 uppercase truncate block leading-tight">{{ $sub->categoria_servico }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @elseif($isLevel2)
                                            <!-- Badge Cliente Pendente SEM CPF (Aguardando Cliente) -->
                                            <div class="mt-4 bg-yellow-500/10 border border-yellow-500/20 rounded-2xl p-4 space-y-2">
                                                <div>
                                                    <span class="text-[9px] uppercase tracking-widest text-yellow-400 block font-bold">Cliente (Não Cadastrado)</span>
                                                    <span class="text-xs font-bold text-slate-800 uppercase truncate block leading-tight">
                                                        CPF NÃO ENCONTRADO
                                                    </span>
                                                    <span class="text-[9px] text-yellow-300/80 block mt-1">Aguardando identificação do cliente...</span>
                                                </div>
                                                @if($sub->categoria_servico)
                                                    <div>
                                                        <span class="text-[9px] uppercase tracking-widest text-slate-500 block font-bold">Categoria</span>
                                                        <span class="text-xs font-bold text-slate-800 uppercase truncate block leading-tight">{{ $sub->categoria_servico }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex items-center justify-end gap-2 mt-6 pt-4 border-t border-white/5">
                                        <a href="{{ route('admin.pastas.show', $sub->id) }}"
                                           class="bg-[#003366] hover:bg-[#004A7C] transition px-4 py-2 rounded-xl text-xs uppercase font-black shadow-lg">
                                            Abrir
                                        </a>

                                        <button
                                            type="button"
                                            onclick="togglePastaOculto({{ $sub->id }}, this)"
                                            class="p-2 rounded-xl text-xs font-black shadow-lg border border-white/10 transition flex items-center justify-center {{ $sub->oculto ? 'bg-amber-600 text-slate-800 hover:bg-amber-500' : 'bg-white/5 text-slate-500 hover:text-slate-800 hover:bg-white/10' }}"
                                            title="{{ $sub->oculto ? 'Esta pasta está oculta do cliente. Clique para mostrar.' : 'Esta pasta está visível para o cliente. Clique para ocultar.' }}">
                                            <span>{{ $sub->oculto ? '🙈 Oculta' : '👁️ Visível' }}</span>
                                        </button>

                                        <button
                                            @click="openDeleteModal('{{ route('admin.pastas.destroy', $sub->id) }}', '{{ addslashes($sub->nome) }}')"
                                            class="bg-red-600 hover:bg-red-700 transition px-4 py-2 rounded-xl text-xs uppercase font-black shadow-lg">
                                            Excluir
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-full py-16 text-center text-slate-800/30 font-bold uppercase text-sm">
                                    Nenhuma subpasta encontrada.
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>

                <!-- DOCUMENTOS E PENDÊNCIAS -->
                @if($pasta->parent_id && $pasta->parent?->parent_id)
                <div class="bg-white rounded-[32px] border border-slate-200 shadow-sm p-5 md:p-8">

                    <div class="px-8 py-6 border-b border-white/10 flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-black uppercase italic">
                                Arquivos e Pendências
                            </h2>
                            <p class="text-sm text-slate-500">
                                Documentos e pendências vinculadas a este serviço
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" @click="openUploadModal" class="bg-[#004A7C] hover:bg-blue-700 text-slate-800 font-black px-4 py-2.5 rounded-xl text-xs transition border border-white/10 uppercase">
                                Upload Arquivo
                            </button>
                            <button type="button" @click="openPendenciaModal" class="bg-yellow-500 hover:bg-yellow-400 text-black font-black px-4 py-2.5 rounded-xl text-xs transition uppercase">
                                Nova Pendência
                            </button>
                        </div>
                    </div>

                    <div class="p-8 space-y-8">
                        <!-- PENDÊNCIAS -->
                        @if($pasta->pendencias->isNotEmpty())
                            <div>
                                <h3 class="text-xs uppercase tracking-[3px] text-yellow-400 font-bold mb-4">Pendências do Cliente</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($pasta->pendencias as $pendencia)
                                        <div class="bg-yellow-500/10 border border-yellow-500/25 rounded-3xl p-6 flex flex-col justify-between hover:bg-yellow-500/15 transition animate-fade">
                                            <div>
                                                <div class="flex items-center gap-3 mb-2">
                                                    <span class="text-xl">⚠️</span>
                                                    <h4 class="font-black text-yellow-400 uppercase italic text-sm tracking-wide">
                                                        {{ $pendencia->titulo }}
                                                    </h4>
                                                </div>
                                                <p class="text-xs text-slate-800/70 leading-relaxed">
                                                    {{ $pendencia->descricao }}
                                                </p>
                                            </div>
                                            <div class="flex items-center justify-between gap-4 mt-6 pt-4 border-t border-yellow-500/10">
                                                <span class="bg-yellow-500/20 text-yellow-300 text-[9px] font-bold px-2 py-0.5 rounded-full uppercase">
                                                    Aguardando
                                                </span>
                                                <button
                                                    @click="openDeleteModal('{{ route('pendencias.destroy', $pendencia->id) }}', '{{ addslashes($pendencia->titulo) }}')"
                                                    class="bg-red-600/80 hover:bg-red-700 text-slate-800 px-4 py-1.5 rounded-xl font-black uppercase text-[10px] transition">
                                                    Excluir
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- ARQUIVOS -->
                        <div>
                            <h3 class="text-xs uppercase tracking-[3px] text-slate-800 font-bold mb-4">Documentos da Pasta</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                                @forelse($pasta->arquivos as $arq)
                                    <div class="glass border border-white/5 rounded-3xl p-6 flex flex-col justify-between hover:scale-[1.01] transition animate-fade">
                                        @php
                                            $icon = '📄';
                                            $bgClass = 'bg-gray-500/20 border-gray-500/20';
                                            switch (strtolower($arq->tipo)) {
                                                case 'pdf': $icon = '📕'; $bgClass = 'bg-red-500/20 border-red-500/20'; break;
                                                case 'doc':
                                                case 'docx': $icon = '📘'; $bgClass = 'bg-blue-500/20 border-blue-500/20'; break;
                                                case 'xls':
                                                case 'xlsx':
                                                case 'ods': $icon = '📗'; $bgClass = 'bg-green-500/20 border-green-500/20'; break;
                                                case 'dwg':
                                                case 'dxf': $icon = '📐'; $bgClass = 'bg-indigo-500/20 border-indigo-500/20'; break;
                                                case 'jpg':
                                                case 'jpeg':
                                                case 'png': $icon = '🖼️'; $bgClass = 'bg-yellow-500/20 border-yellow-500/20'; break;
                                                case 'zip':
                                                case 'rar': $icon = '📦'; $bgClass = 'bg-orange-500/20 border-orange-500/20'; break;
                                            }
                                        @endphp
                                        <div class="flex items-start gap-4 mb-4">
                                            <div class="w-12 h-12 rounded-2xl {{ $bgClass }} border flex items-center justify-center text-2xl shrink-0">
                                                {{ $icon }}
                                            </div>
                                            <div class="min-w-0">
                                                <h4 class="font-black text-slate-800 uppercase text-sm truncate leading-snug">
                                                    {{ $arq->nome }}
                                                </h4>
                                                <p class="text-[10px] text-slate-500 mt-1 uppercase font-bold">
                                                    {{ $arq->tipo }} • {{ $arq->tamanho }} MB
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 mt-6 pt-4 border-t border-white/5">
                                            <button
                                                @click="openPreviewModal('{{ asset('storage/'.$arq->path) }}', '{{ $arq->tipo }}', '{{ addslashes($arq->nome) }}')"
                                                class="flex-1 bg-[#003366] hover:bg-[#004A7C] text-slate-800 py-2 rounded-xl font-black uppercase text-[10px] text-center transition">
                                                Visualizar
                                            </button>

                                            <a href="{{ route('arquivo.download', $arq->id) }}"
                                               class="flex-1 bg-[#00E500] hover:bg-green-500 text-black py-2 rounded-xl font-black uppercase text-[10px] text-center transition">
                                                Baixar
                                            </a>

                                            <button
                                                type="button"
                                                onclick="toggleArquivoOculto({{ $arq->id }}, this)"
                                                class="p-2 rounded-xl text-[10px] font-black shadow-lg border border-white/10 transition flex items-center justify-center {{ $arq->oculto ? 'bg-amber-600 text-slate-800 hover:bg-amber-500' : 'bg-white/5 text-slate-500 hover:text-slate-800 hover:bg-white/10' }}"
                                                title="{{ $arq->oculto ? 'Este arquivo está oculto do cliente. Clique para mostrar.' : 'Este arquivo está visível para o cliente. Clique para ocultar.' }}">
                                                <span>{{ $arq->oculto ? '🙈' : '👁️' }}</span>
                                            </button>

                                            <button
                                                @click="openDeleteModal('{{ route('arquivos.destroy', $arq->id) }}', '{{ addslashes($arq->nome) }}')"
                                                class="bg-red-600 hover:bg-red-700 text-slate-800 p-2 rounded-xl font-black uppercase text-[10px] transition shrink-0">
                                                Excluir
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-full py-12 text-center text-slate-800/30 font-bold uppercase text-sm">
                                        Nenhum arquivo enviado.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                </div>
                @endif

                <!-- ============================================================ -->
                <!-- MAPA SIGEF (carregado automaticamente se houver arquivo ODS) -->
                <!-- ============================================================ -->
                @if($pasta->parent_id && $pasta->parent?->parent_id && !$pasta->parent?->parent?->parent_id)
                <div id="sigef-mapa-section" class="bg-white rounded-[32px] border border-slate-200 shadow-sm p-5 md:p-8">

                    <!-- Header -->
                    <div class="px-8 py-6 border-b border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-emerald-500/20 border border-emerald-500/20 flex items-center justify-center text-3xl shrink-0">
                                🗺️
                            </div>
                            <div>
                                <div class="flex items-center gap-3 flex-wrap">
                                    <span class="uppercase tracking-[3px] text-xs text-slate-800 font-black">Planta de Situação</span>
                                    <span class="bg-emerald-500/25 text-slate-800 text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase">SIGEF / INCRA</span>
                                </div>
                                <h2 id="sigef-titulo" class="text-2xl font-black italic uppercase mt-1">
                                    Carregando dados...
                                </h2>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">

                            <button id="btn-satelite" onclick="alternarCamada()"
                                class="bg-white/10 hover:bg-white/20 text-slate-800 px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition flex items-center gap-2 border border-white/5 shadow-md">
                                🛰️ Satélite
                            </button>
                        </div>
                    </div>

                    <!-- Metadados -->
                    <div id="sigef-meta" class="hidden px-8 py-4 grid grid-cols-2 md:grid-cols-4 gap-4 border-b border-white/10 bg-white/5">
                        <div>
                            <p class="text-[9px] uppercase tracking-widest text-slate-500 font-bold">Denominação</p>
                            <p id="meta-imovel" class="text-sm font-black text-slate-800 mt-0.5 truncate">—</p>
                        </div>
                        <div>
                            <p class="text-[9px] uppercase tracking-widest text-slate-500 font-bold">Detentor</p>
                            <p id="meta-detentor" class="text-sm font-black text-slate-800 mt-0.5 truncate">—</p>
                        </div>
                        <div>
                            <p class="text-[9px] uppercase tracking-widest text-slate-500 font-bold">Município</p>
                            <p id="meta-municipio" class="text-sm font-black text-slate-800 mt-0.5 truncate">—</p>
                        </div>
                        <div>
                            <p class="text-[9px] uppercase tracking-widest text-slate-500 font-bold">Área</p>
                            <p id="meta-area" class="text-sm font-black text-slate-800 mt-0.5">—</p>
                        </div>
                    </div>

                    <!-- Mapa Leaflet -->
                    <div id="leaflet-mapa" style="height: 520px; z-index: 1;"></div>

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

                    <!-- Rodapé -->
                    <div id="sigef-footer" class="hidden px-8 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-t border-white/10 bg-white/5">
                        <div class="flex items-center gap-6">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded-full bg-[#00E500] border-2 border-white/20 shadow"></div>
                                <span id="footer-vertices" class="text-xs font-bold text-slate-800/80">0 vértices</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-3 rounded bg-blue-500/40 border-2 border-blue-400"></div>
                                <span class="text-xs font-bold text-slate-800/80">Perímetro do Imóvel</span>
                            </div>
                        </div>
                        <a id="footer-link-sigef" href="#" target="_blank"
                           class="hidden bg-[#00E500] hover:bg-green-400 text-black px-5 py-2 rounded-xl font-black uppercase text-xs tracking-wider shadow transition flex items-center gap-2">
                            Ver no SIGEF ↗
                        </a>
                    </div>

                </div>
                @endif

            </main>

        <!-- CREATE FOLDER MODAL -->
        <div x-show="createFolderModal"
             x-transition
             class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm p-4 modal-overlay"
             x-cloak>

            <div class="modal-container bg-[#003366] border border-white/10 rounded-[35px] p-8 shadow-2xl text-slate-800">

                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-3xl font-black uppercase italic text-slate-800">
                            {{ $isLevel2 ? 'Novo Imóvel' : 'Nova Subpasta' }}
                        </h2>
                        <p class="text-slate-500 mt-2">
                            {{ $isLevel2 ? 'Associe o imóvel a um cliente usando CPF.' : 'Crie uma nova subpasta vinculada a esta pasta.' }}
                        </p>
                    </div>

                    <button @click="closeModal('createFolderModal')"
                            class="text-slate-500 hover:text-slate-800 text-3xl leading-none">
                        ×
                    </button>
                </div>

                <form action="{{ route('pasta.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $pasta->id }}">

                    <div class="mb-6">
                        <label class="block text-slate-800/70 uppercase text-xs font-bold mb-2">
                            Nome do Imóvel / Subpasta
                        </label>
                        <input type="text"
                               name="nome"
                               required
                               class="w-full bg-white text-[#003366] px-5 py-3 rounded-2xl outline-none font-bold">
                    </div>

                    @if($isLevel2)
                        <div class="mb-6">
                            <label class="block text-slate-800/70 uppercase text-xs font-bold mb-2">
                                CPF do Cliente
                            </label>
                            <input type="text"
                                   name="identificador_cliente"
                                   required
                                   placeholder="Somente os 11 dígitos"
                                   class="w-full bg-white text-[#003366] px-5 py-3 rounded-2xl outline-none font-bold">
                        </div>

                        <div class="mb-6">
                            <label class="block text-slate-800/70 uppercase text-xs font-bold mb-2">
                                Categoria do Serviço
                            </label>
                            <input type="text"
                                   name="categoria_servico"
                                   placeholder="Ex: Geo, Desmembramento, Levantamento"
                                   class="w-full bg-white text-[#003366] px-5 py-3 rounded-2xl outline-none font-bold">
                        </div>
                    @endif

                    <div class="flex gap-4">
                        <button type="submit"
                                class="flex-1 bg-[#00E500] hover:bg-green-500 text-black py-3 rounded-2xl font-black uppercase transition">
                            Criar
                        </button>

                        <button type="button"
                                @click="closeModal('createFolderModal')"
                                class="flex-1 bg-red-600 hover:bg-red-700 text-slate-800 py-3 rounded-2xl font-black uppercase transition">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- UPLOAD FILE MODAL -->
        <div x-show="uploadModal"
             x-transition
             class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm p-4 modal-overlay"
             x-cloak>

            <div class="modal-container bg-[#003366] border border-white/10 rounded-[35px] p-8 shadow-2xl text-slate-800">

                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-3xl font-black uppercase italic text-slate-800">
                            Upload de Arquivo
                        </h2>
                        <p class="text-slate-500 mt-2">
                            Anexe um documento para esta pasta.
                        </p>
                    </div>

                    <button @click="closeModal('uploadModal')"
                            class="text-slate-500 hover:text-slate-800 text-3xl leading-none">
                        ×
                    </button>
                </div>

                <form action="{{ route('arquivos.store', $pasta->id) }}"
                      method="POST"
                      enctype="multipart/form-data">
                    @csrf

                    <div class="mb-6">
                        <label class="block text-slate-800/70 uppercase text-xs font-bold mb-2">
                            Nome do Documento
                        </label>
                        <input type="text"
                               name="nome"
                               required
                               class="w-full bg-white text-[#003366] px-5 py-3 rounded-2xl outline-none font-bold">
                    </div>

                    <div class="mb-6">
                        <label class="block text-slate-800/70 uppercase text-xs font-bold mb-2">
                            Selecionar Arquivo
                        </label>

                        <div class="relative">
                            <label class="relative w-full h-full flex items-center justify-center cursor-pointer bg-white/10 border border-white/20 rounded-2xl p-6 hover:bg-white/15 transition border-dashed">
                                <div class="pointer-events-none text-slate-800/80 font-bold uppercase text-xs">Clique para selecionar o arquivo</div>
                                <input type="file"
                                       name="arquivo"
                                       required
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            </label>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit"
                                class="flex-1 bg-[#00E500] hover:bg-green-500 text-black py-3 rounded-2xl font-black uppercase transition">
                            Enviar
                        </button>

                        <button type="button"
                                @click="closeModal('uploadModal')"
                                class="flex-1 bg-red-600 hover:bg-red-700 text-slate-800 py-3 rounded-2xl font-black uppercase transition">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- PENDÊNCIA MODAL -->
        <div x-show="pendenciaModal"
             x-transition
             class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm p-4 modal-overlay"
             x-cloak>

            <div class="modal-container bg-[#003366] border border-white/10 rounded-[35px] p-8 shadow-2xl text-slate-800">

                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-3xl font-black uppercase italic text-slate-800">
                            Nova Pendência
                        </h2>
                        <p class="text-slate-500 mt-2">
                            Registre um item pendente para o cliente.
                        </p>
                    </div>

                    <button @click="closeModal('pendenciaModal')"
                            class="text-slate-500 hover:text-slate-800 text-3xl leading-none">
                        ×
                    </button>
                </div>

                <form action="{{ route('pendencias.store', $pasta->id) }}" method="POST">
                    @csrf

                    <div class="mb-6">
                        <label class="block text-slate-800/70 uppercase text-xs font-bold mb-2">
                            Título da Pendência
                        </label>
                        <input type="text"
                               name="titulo"
                               required
                               class="w-full bg-white text-[#003366] px-5 py-3 rounded-2xl outline-none font-bold">
                    </div>

                    <div class="mb-6">
                        <label class="block text-slate-800/70 uppercase text-xs font-bold mb-2">
                            Descrição
                        </label>
                        <textarea name="descricao"
                                  rows="4"
                                  required
                                  class="w-full bg-white text-[#003366] px-5 py-3 rounded-2xl outline-none resize-none font-bold"></textarea>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit"
                                class="flex-1 bg-[#00E500] hover:bg-green-500 text-black py-3 rounded-2xl font-black uppercase transition">
                            Salvar
                        </button>

                        <button type="button"
                                @click="closeModal('pendenciaModal')"
                                class="flex-1 bg-red-600 hover:bg-red-700 text-slate-800 py-3 rounded-2xl font-black uppercase transition">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- PREVIEW MODAL -->
        <div x-show="previewModal"
             x-transition
             class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm p-4 modal-overlay"
             x-cloak>

            <div class="modal-container bg-[#003366] border border-white/10 rounded-[35px] p-8 shadow-2xl max-w-4xl flex flex-col animate-fade text-slate-800">

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-3xl font-black uppercase italic text-slate-800" x-text="previewName">
                        Visualizar Arquivo
                    </h2>
                    <button @click="previewModal = false"
                            class="text-slate-500 hover:text-slate-800 text-3xl leading-none">
                        ×
                    </button>
                </div>

                <div class="flex-1 flex items-center justify-center min-h-[50vh] max-h-[70vh] overflow-auto bg-black/20 rounded-2xl p-4">
                    <template x-if="['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(previewType)">
                        <img :src="previewUrl" class="max-w-full max-h-[65vh] object-contain rounded-xl shadow-2xl">
                    </template>
                    <template x-if="previewType === 'pdf'">
                        <iframe :src="previewUrl" class="w-full h-[65vh] rounded-xl" frameborder="0"></iframe>
                    </template>
                    <template x-if="!['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf'].includes(previewType)">
                        <div class="text-center py-10">
                            <span class="text-5xl mb-4 block">📁</span>
                            <p class="text-slate-800 font-bold mb-2">Visualização não disponível</p>
                            <p class="text-slate-500 text-sm mb-6">Arquivos do tipo .<span x-text="previewType.toUpperCase()"></span> não podem ser visualizados diretamente no navegador.</p>
                            <a :href="previewUrl" download class="inline-flex items-center bg-[#00E500] hover:bg-green-500 text-black px-6 py-3 rounded-2xl font-black uppercase text-xs transition">
                                Baixar Arquivo
                            </a>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- SIGEF MODAL -->
        <div x-show="openSigefModal"
             x-transition
             class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm p-4 modal-overlay"
             x-cloak>

            <div class="modal-container bg-[#003366] border border-white/10 rounded-[35px] p-8 shadow-2xl text-slate-800">

                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-3xl font-black uppercase italic text-slate-800">
                            Código da Parcela (SIGEF)
                        </h2>
                        <p class="text-slate-500 mt-2">
                            Insira o GUID/Código identificador da parcela no SIGEF.
                        </p>
                    </div>

                    <button @click="openSigefModal = false"
                            class="text-slate-500 hover:text-slate-800 text-3xl leading-none">
                        ×
                    </button>
                </div>

                <form action="{{ route('admin.pastas.update-sigef', $pasta->id) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="mb-6">
                        <label class="block text-slate-800/70 uppercase text-xs font-bold mb-2">
                            Código SIGEF (GUID)
                        </label>
                        <input type="text"
                               name="codigo_sigef"
                               value="{{ $pasta->codigo_sigef }}"
                               placeholder="Ex: c03260c8-47c0-43db-9ab4-1a91e5210987"
                               class="w-full bg-white text-[#003366] px-5 py-3 rounded-2xl outline-none font-mono font-bold">
                        <p class="text-slate-500 text-xs mt-2">
                            Você pode encontrar esse código na URL do imóvel certificado no SIGEF.
                        </p>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit"
                                class="flex-1 bg-[#00E500] hover:bg-green-500 text-black py-3 rounded-2xl font-black uppercase transition">
                            Salvar
                        </button>

                        <button type="button"
                                @click="openSigefModal = false"
                                class="flex-1 bg-red-600 hover:bg-red-700 text-slate-800 py-3 rounded-2xl font-black uppercase transition">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- CPF MODAL -->
        <div x-show="openCpfModal"
             class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm p-4 modal-overlay"
             x-cloak>

            <div class="modal-container bg-[#003366] border border-white/10 rounded-[35px] p-8 shadow-2xl text-slate-800">

                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-3xl font-black uppercase italic text-slate-800">
                            Vincular CPF do Proprietário
                        </h2>
                        <p class="text-slate-500 mt-2">
                            Insira o CPF do proprietário para que o imóvel seja vinculado a ele automaticamente quando se cadastrar.
                        </p>
                    </div>

                    <button @click="openCpfModal = false"
                            class="text-slate-500 hover:text-slate-800 text-3xl leading-none">
                        ×
                    </button>
                </div>

                <form action="{{ route('admin.pastas.vincularCpf', $pasta->id) }}" method="POST">
                    @csrf

                    <div class="mb-6">
                        <label class="block text-slate-800/70 uppercase text-xs font-bold mb-2">
                            CPF (Somente Números)
                        </label>
                        <input type="text"
                               name="identificador_cliente"
                               value="{{ $pasta->identificador_cliente }}"
                               required
                               maxlength="11"
                               class="w-full bg-white text-[#003366] px-5 py-3 rounded-2xl outline-none font-bold">
                    </div>

                    <div class="flex gap-4">
                        <button type="submit"
                                class="flex-1 bg-[#00E500] hover:bg-green-500 text-black py-3 rounded-2xl font-black uppercase transition">
                            Vincular CPF
                        </button>

                        <button type="button"
                                @click="openCpfModal = false"
                                class="flex-1 bg-red-600 hover:bg-red-700 text-slate-800 py-3 rounded-2xl font-black uppercase transition">
                            Cancelar
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- DELETE MODAL -->
        <div x-show="deleteModal"
             x-transition
             class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4">

            <div class="bg-[#003366] border border-white/10 rounded-[35px] p-10 w-full max-w-md shadow-2xl text-slate-800 text-center">

                <h2 class="text-3xl font-black mb-4 text-red-500 uppercase italic">
                    Excluir Item?
                </h2>

                <p class="text-slate-500 mb-10">
                    Deseja realmente excluir
                    <span class="font-black text-slate-800" x-text="deleteName"></span>?
                </p>

                <div class="flex gap-4">

                    <form :action="deleteUrl" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')

                        <button class="w-full bg-red-600 hover:bg-red-700 py-3 rounded-2xl font-black uppercase transition">
                            Excluir
                        </button>
                    </form>

                    <button @click="deleteModal = false"
                            class="flex-1 bg-white/10 hover:bg-white/20 py-3 rounded-2xl font-black uppercase transition text-slate-800 border border-white/10">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>

        <!-- FINALIZE SERVICE MODAL -->
        <div x-show="finalizeModal"
             x-transition
             class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4"
             x-cloak>

            <div class="bg-[#003366] border border-white/10 rounded-[35px] p-10 w-full max-w-md shadow-2xl text-slate-800 text-center">

                <h2 class="text-3xl font-black mb-4 text-emerald-400 uppercase italic">
                    Finalizar Serviço?
                </h2>

                <p class="text-slate-500 mb-10 leading-relaxed">
                    Deseja realmente finalizar o serviço <span class="font-black text-slate-800 uppercase">{{ $pasta->nome }}</span>? Ele será movido para Serviços Prontos e seu cliente será notificado.
                </p>

                <div class="flex gap-4">
                    <form action="{{ route('admin.pastas.finalizar', $pasta->id) }}" method="POST" class="flex-1">
                        @csrf
                        @method('PATCH')

                        <button class="w-full bg-[#00E500] hover:bg-green-500 text-black py-3 rounded-2xl font-black uppercase transition">
                            Finalizar
                        </button>
                    </form>

                    <button @click="finalizeModal = false"
                            class="flex-1 bg-white/10 hover:bg-white/20 py-3 rounded-2xl font-black uppercase transition text-slate-800 border border-white/10">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>

        <!-- REVERT SERVICE MODAL -->
        <div x-show="revertModal"
             x-transition
             class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4"
             x-cloak>

            <div class="bg-[#003366] border border-white/10 rounded-[35px] p-10 w-full max-w-md shadow-2xl text-slate-800 text-center">

                <h2 class="text-3xl font-black mb-4 text-yellow-400 uppercase italic">
                    Retornar para Pendente?
                </h2>

                <p class="text-slate-500 mb-10 leading-relaxed">
                    Deseja retornar o serviço <span class="font-black text-slate-800 uppercase">{{ $pasta->nome }}</span> para a lista de Pendentes?
                </p>

                <div class="flex gap-4">
                    <form action="{{ route('admin.pastas.tornar-pendente', $pasta->id) }}" method="POST" class="flex-1">
                        @csrf
                        @method('PATCH')

                        <button class="w-full bg-yellow-500 hover:bg-yellow-400 text-black py-3 rounded-2xl font-black uppercase transition">
                            Retornar
                        </button>
                    </form>

                    <button @click="revertModal = false"
                            class="flex-1 bg-white/10 hover:bg-white/20 py-3 rounded-2xl font-black uppercase transition text-slate-800 border border-white/10">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>

        <!-- MEMORIAL MODAL -->
        <div x-show="memorialModal"
             x-transition
             class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm p-4 modal-overlay"
             x-cloak>
            <div class="modal-container bg-[#003366] border border-white/10 rounded-[35px] p-8 shadow-2xl max-w-4xl flex flex-col text-slate-800 max-h-[90vh]">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-3xl font-black uppercase italic text-slate-800">
                            Memorial Descritivo
                        </h2>
                        <p class="text-slate-500 text-xs mt-1 uppercase font-bold tracking-wider">
                            Gerado a partir do perímetro SIGEF
                        </p>
                    </div>
                    <button @click="memorialModal = false"
                            class="text-slate-500 hover:text-slate-800 text-3xl leading-none">
                        ×
                    </button>
                </div>

                <div class="flex-1 overflow-auto bg-black/40 border border-white/10 rounded-2xl p-6 mb-6 font-mono text-sm leading-relaxed whitespace-pre-wrap select-all relative min-h-[300px] max-h-[50vh]">
                    <div x-show="loadingMemorial" class="absolute inset-0 flex items-center justify-center bg-black/50 rounded-2xl">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-8 h-8 border-4 border-[#00E500] border-t-transparent rounded-full animate-spin"></div>
                            <span class="text-xs uppercase font-bold tracking-widest text-slate-800">Gerando Memorial...</span>
                        </div>
                    </div>
                    <div x-text="memorialTexto"></div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4">
                    <button @click="copiarMemorial"
                            :disabled="loadingMemorial"
                            class="flex-1 bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-slate-800 py-3.5 rounded-2xl font-black uppercase transition flex items-center justify-center gap-2 shadow-lg">
                        📋 Copiar Texto
                    </button>
                    <a href="{{ route('pasta.memorial-descritivo', [$pasta->id, 'download' => 'txt']) }}"
                       class="flex-1 bg-[#00E500] hover:bg-green-500 text-black py-3.5 rounded-2xl font-black uppercase transition flex items-center justify-center gap-2 text-center shadow-lg">
                        💾 Baixar TXT
                    </a>
                    <button @click="memorialModal = false"
                            class="flex-1 bg-red-600 hover:bg-red-700 text-slate-800 py-3.5 rounded-2xl font-black uppercase transition shadow-lg">
                        Fechar
                    </button>
                </div>
            </div>
        </div>

    </div>

    <script>
        function app() {
            return {
                deleteModal: false,
                deleteUrl: '',
                deleteName: '',
                finalizeModal: false,
                revertModal: false,
                createFolderModal: false,
                uploadModal: false,
                pendenciaModal: false,
                previewModal: false,
                openSigefModal: false,
                openCpfModal: false,
                previewUrl: '',
                previewType: '',
                previewName: '',
                memorialModal: false,
                memorialTexto: '',
                loadingMemorial: false,
                syncLocalExecutando: false,
                syncPercent: 0,
                syncCurrent: 0,
                syncTotal: 0,
                syncLastFile: '',

                async sincronizarPastaUnica(id) {
                    this.syncLocalExecutando = true;
                    this.syncPercent = 0;
                    this.syncCurrent = 0;
                    this.syncTotal = 0;
                    this.syncLastFile = 'Iniciando sincronização...';

                    let pollInterval;

                    try {
                        const response = await fetch('/pastas/' + id + '/sync', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.error || 'Erro ao iniciar sincronização');

                        // Start polling to wait for completion
                        pollInterval = setInterval(async () => {
                            try {
                                const progressResp = await fetch('/importacao/sync-progress?pasta_id=' + id);
                                if (progressResp.ok) {
                                    const progressData = await progressResp.json();
                                    
                                    this.syncPercent = progressData.percentage || 0;
                                    this.syncCurrent = progressData.current || 0;
                                    this.syncTotal = progressData.total || 0;
                                    this.syncLastFile = progressData.last_file || '';

                                    if (progressData.status === 'done') {
                                        clearInterval(pollInterval);
                                        this.syncLocalExecutando = false;
                                        Swal.fire({
                                            title: 'Sincronizado!',
                                            text: 'Sincronização da pasta concluída com sucesso!',
                                            icon: 'success',
                                            confirmButtonColor: '#003366',
                                        }).then(() => {
                                            window.location.reload();
                                        });
                                    } else if (progressData.status === 'error') {
                                        clearInterval(pollInterval);
                                        this.syncLocalExecutando = false;
                                        Swal.fire({
                                            title: 'Erro!',
                                            text: progressData.last_file || 'Ocorreu um erro na sincronização.',
                                            icon: 'error',
                                            confirmButtonColor: '#003366',
                                        });
                                    }
                                }
                            } catch (e) {
                                console.error('Erro ao buscar progresso:', e);
                            }
                        }, 800);

                    } catch (error) {
                        if (pollInterval) clearInterval(pollInterval);
                        this.syncLocalExecutando = false;
                        Swal.fire({
                            title: 'Erro!',
                            text: error.message,
                            icon: 'error',
                            confirmButtonColor: '#003366',
                        });
                    }
                },

                async openMemorial() {
                    this.loadingMemorial = true;
                    this.memorialModal = true;
                    try {
                        const resp = await fetch('{{ route("pasta.memorial-descritivo", $pasta->id) }}');
                        const data = await resp.json();
                        this.memorialTexto = data.texto;
                    } catch (e) {
                        this.memorialTexto = "Erro ao carregar o memorial descritivo.";
                    } finally {
                        this.loadingMemorial = false;
                    }
                },

                copiarMemorial() {
                    navigator.clipboard.writeText(this.memorialTexto).then(() => {
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
                },

                openPreviewModal(url, type, name) {
                    this.previewUrl = url
                    this.previewType = type.toLowerCase()
                    this.previewName = name
                    this.previewModal = true
                },

                openDeleteModal(url, name) {
                    this.deleteUrl = url
                    this.deleteName = name
                    this.deleteModal = true
                },

                openCreateModal() {
                    this.createFolderModal = true
                },

                openUploadModal() {
                    this.uploadModal = true
                },

                openPendenciaModal() {
                    this.openSigefModal = false // Close any other modals
                    this.pendenciaModal = true
                },

                closeModal(modal) {
                    if (typeof this[modal] !== 'undefined') {
                        this[modal] = false
                    }
                }
            }
        }
    </script>

    <!-- ================================================= -->
    <!-- JavaScript: Mapa SIGEF com Leaflet.js              -->
    <!-- ================================================= -->
    <script>
        const PASTA_ID      = {{ $pasta->id }};
        const SIGEF_MAPA_URL = '{{ route("pasta.mapa-sigef", $pasta->id) }}';
        @if($pasta->codigo_sigef)
        const CODIGO_SIGEF  = '{{ $pasta->codigo_sigef }}';
        @else
        const CODIGO_SIGEF  = null;
        @endif

        let leafletMap    = null;
        let camadaAtual   = 'streets';
        let camadaStreets = null;
        let camadaSateli  = null;

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

        function utmParaLatLon(E, N, zona) {
            const epsg = definirProjecaoUtm(zona);
            if (!epsg) return null;
            try {
                const [lon, lat] = proj4(epsg, 'WGS84', [E, N]);
                return { lat, lon };
            } catch(e) { return null; }
        }

        function converterVertices(vertices) {
            return vertices.map(v => {
                if (v.tipo === 'geodesica') return [v.N, v.E];
                const zona = v.zona || '23S';
                const conv = utmParaLatLon(v.E, v.N, zona);
                return conv ? [conv.lat, conv.lon] : null;
            }).filter(p => p !== null);
        }

        function calcularAreaHa(coords) {
            if (coords.length < 3) return 0;
            let area = 0;
            const n = coords.length;
            const R = 6371000;
            for (let i = 0; i < n; i++) {
                const j = (i + 1) % n;
                area += (coords[j][1] - coords[i][1]) * Math.PI / 180
                    * (2 + Math.sin(coords[i][0] * Math.PI / 180) + Math.sin(coords[j][0] * Math.PI / 180));
            }
            return (Math.abs(area * R * R / 2) / 10000);
        }

        function formatarAreaBr(area) {
            if (!area) return '0,0000';
            const num = typeof area === 'string' ? parseFloat(area.toString().replace(',', '.')) : area;
            if (isNaN(num)) return area;
            return num.toLocaleString('pt-BR', { minimumFractionDigits: 4, maximumFractionDigits: 4 });
        }

        function inicializarMapa(coords, dados) {
            if (!leafletMap) {
                leafletMap = L.map('leaflet-mapa', { zoomControl: true, scrollWheelZoom: true });
                camadaStreets = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© <a href="https://www.openstreetmap.org/">OpenStreetMap</a>', maxZoom: 20
                });
                camadaSateli = L.tileLayer(
                    'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                    { attribution: 'Tiles © Esri', maxZoom: 20 }
                );
                camadaStreets.addTo(leafletMap);
            }

            leafletMap.eachLayer(layer => {
                if (layer !== camadaStreets && layer !== camadaSateli) leafletMap.removeLayer(layer);
            });

            if (coords.length < 3) return;

            const poligono = L.polygon(coords, {
                color: '#00E500', weight: 2.5, fillColor: '#00E500', fillOpacity: 0.15,
            }).addTo(leafletMap);

            dados.vertices.forEach((v, i) => {
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

            document.getElementById('sigef-titulo').innerText = dados.imovel || 'Imóvel Georreferenciado';

            const meta = document.getElementById('sigef-meta');
            if (dados.imovel || dados.detentor || dados.municipio || dados.area_ha) {
                if (dados.imovel)    document.getElementById('meta-imovel').innerText = dados.imovel;
                if (dados.detentor)  document.getElementById('meta-detentor').innerText = dados.detentor;
                if (dados.municipio) document.getElementById('meta-municipio').innerText = dados.municipio;
                document.getElementById('meta-area').innerText = (dados.area_ha ? formatarAreaBr(dados.area_ha) + ' ha' : formatarAreaBr(calcularAreaHa(coords)) + ' ha (calc.)');
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

            const footer = document.getElementById('sigef-footer');
            footer.classList.remove('hidden');
            document.getElementById('footer-vertices').innerText = dados.vertices.length + ' vértices';

            if (CODIGO_SIGEF) {
                const linkSigef = document.getElementById('footer-link-sigef');
                linkSigef.href = `https://sigef.incra.gov.br/geo/parcela/detalhe/${CODIGO_SIGEF}/`;
                linkSigef.classList.remove('hidden');
            }
        }

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

        async function carregarMapaSigef() {
            try {
                const resp = await fetch(SIGEF_MAPA_URL, { headers: { 'Accept': 'application/json' } });
                if (!resp.ok) return;
                const rawData = await resp.json();
                const poligonos = rawData.poligonos ? rawData.poligonos : [rawData];
                if (!poligonos || poligonos.length === 0) return;
                
                const dados = poligonos[0];
                if (!dados.vertices || dados.vertices.length === 0) return;
                
                const coords = converterVertices(dados.vertices);
                if (coords.length < 3) return;
                
                const mapSection = document.getElementById('sigef-mapa-section');
                if (mapSection) {
                    mapSection.classList.remove('hidden');
                    setTimeout(() => inicializarMapa(coords, dados), 100);
                }
            } catch(err) {
                console.error('Erro ao carregar mapa SIGEF:', err);
            }
        }

        document.addEventListener('DOMContentLoaded', carregarMapaSigef);

        async function toggleArquivoOculto(id, btn) {
            try {
                const response = await fetch('/admin/arquivos/' + id + '/toggle-oculto', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.error || 'Erro ao alterar visibilidade');
                
                const isOculto = data.oculto;
                const span = btn.querySelector('span');
                if (isOculto) {
                    btn.className = "p-2 rounded-xl text-[10px] font-black shadow-lg border border-white/10 bg-amber-600 text-slate-800 hover:bg-amber-500 transition flex items-center justify-center";
                    if (span) span.innerText = "🙈";
                    btn.title = "Este arquivo está oculto do cliente. Clique para mostrar.";
                } else {
                    btn.className = "p-2 rounded-xl text-[10px] font-black shadow-lg border border-white/10 bg-white/5 text-slate-500 hover:text-slate-800 hover:bg-white/10 transition flex items-center justify-center";
                    if (span) span.innerText = "👁️";
                    btn.title = "Este arquivo está visível para o cliente. Clique para ocultar.";
                }

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: data.message,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            } catch (error) {
                Swal.fire({
                    title: 'Erro!',
                    text: error.message,
                    icon: 'error',
                    confirmButtonColor: '#003366',
                });
            }
        }

        async function togglePastaOculto(id, btn) {
            try {
                const response = await fetch('/admin/pastas/' + id + '/toggle-oculto', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.error || 'Erro ao alterar visibilidade');
                
                const isOculto = data.oculto;
                const span = btn.querySelector('span');
                if (isOculto) {
                    btn.className = "p-2 rounded-xl text-xs font-black shadow-lg border border-white/10 bg-amber-600 text-slate-800 hover:bg-amber-500 transition flex items-center justify-center";
                    if (span) span.innerText = "🙈 Oculta";
                    btn.title = "Esta pasta está oculta do cliente. Clique para mostrar.";
                } else {
                    btn.className = "p-2 rounded-xl text-xs font-black shadow-lg border border-white/10 bg-white/5 text-slate-500 hover:text-slate-800 hover:bg-white/10 transition flex items-center justify-center";
                    if (span) span.innerText = "👁️ Visível";
                    btn.title = "Esta pasta está visível para o cliente. Clique para ocultar.";
                }

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: data.message,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            } catch (error) {
                Swal.fire({
                    title: 'Erro!',
                    text: error.message,
                    icon: 'error',
                    confirmButtonColor: '#003366',
                });
            }
        }
    </script>



</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.9.0/proj4.js"></script>
@endpush
