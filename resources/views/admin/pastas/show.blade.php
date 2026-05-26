<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pasta->nome }} - TopoGest</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

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
    </style>
</head>

<body class="min-h-screen overflow-x-hidden bg-[#E5E7EB] text-white">

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

    <div x-data="app()" 
         @keydown.escape.window="closeModal('createFolderModal'); closeModal('uploadModal'); closeModal('pendenciaModal'); deleteModal = false; previewModal = false; openSigefModal = false; finalizeModal = false; revertModal = false" 
         class="relative w-full max-w-7xl mx-auto px-4 md:px-8 py-8 animate-fade">

        <!-- HEADER -->
        <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8 mb-12">

            <!-- LOGO -->
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-4 group w-fit">

                <div class="relative flex items-center justify-center w-20 h-20 rounded-2xl bg-white shadow-2xl transition group-hover:scale-105">
                    <img src="{{ asset('images/logo-icon.png') }}"
                         alt="TopoGest"
                         class="w-full h-full object-contain p-2">
                </div>

                <div class="relative flex items-center h-14 px-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                    <img src="{{ asset('images/logo-text.png') }}"
                         alt="TopoGest"
                         class="relative z-10 h-8 w-auto">
                </div>
            </a>

            <!-- RIGHT -->
            <div class="flex flex-wrap items-center gap-4">

                <!-- NOTIFICAÇÕES -->
                <a href="{{ route('notificacoes.index') }}"
                   class="glass glow w-14 h-14 rounded-2xl flex items-center justify-center border border-white/10 hover:bg-[#003366] transition-all duration-300 card-hover">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-6 w-6"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </a>

                <!-- PERFIL -->
                <div class="glass glow rounded-3xl px-4 py-3 flex items-center gap-4 border border-white/10 flex items-center justify-center font-black uppercase text-sm">
                    <div class="w-14 h-14 rounded-2xl overflow-hidden bg-[#003366] border-2 border-white/20 flex items-center justify-center text-lg font-black">
                        @if(Auth::user()->photo)
                            <img src="{{ asset('storage/' . Auth::user()->photo) }}"
                                 class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        @endif
                    </div>

                    <div class="leading-tight">
                        <p class="text-[11px] uppercase tracking-[3px] text-white/60">
                            Bem-vindo
                        </p>

                        <h2 class="text-xl font-black uppercase italic">
                            {{ explode(' ', Auth::user()->name)[0] }}
                        </h2>
                    </div>
                </div>

            </div>
        </header>

        <!-- TOP TITLE -->
        <div class="mb-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="inline-flex items-center gap-3 glass glow rounded-3xl px-8 py-5 border border-white/10">
                <div class="w-3 h-12 rounded-full bg-[#00E500]"></div>
                <div>
                    <p class="uppercase tracking-[4px] text-xs text-white/60">
                        Pasta Atual
                    </p>
                    <h1 class="text-3xl md:text-4xl font-black italic uppercase tracking-tight">
                        {{ $pasta->nome }}
                    </h1>
                </div>
            </div>

            <!-- BACK BUTTON -->
            @if($pasta->parent)
                <a href="{{ route('admin.pastas.show', $pasta->parent->id) }}"
                   class="bg-white/10 hover:bg-white/20 text-white px-6 py-4 rounded-2xl font-black uppercase text-sm transition border border-white/10 flex items-center gap-2 shadow-lg w-fit">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Voltar
                </a>
            @else
                <a href="{{ route('admin.pastas.index') }}"
                   class="bg-white/10 hover:bg-white/20 text-white px-6 py-4 rounded-2xl font-black uppercase text-sm transition border border-white/10 flex items-center gap-2 shadow-lg w-fit">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Voltar
                </a>
            @endif
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-[20px] bg-emerald-500/15 border border-emerald-500/30 p-4 text-emerald-100 animate-fade">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 rounded-[20px] bg-rose-500/15 border border-rose-500/30 p-4 text-rose-100 animate-fade">
                {{ session('error') }}
            </div>
        @endif

            <!-- MAIN CONTENT AREA -->
            <main class="w-full space-y-8">

                <!-- BREADCRUMBS -->
                <div class="flex items-center flex-wrap gap-2 text-sm text-white/60 mb-6 bg-white/5 border border-white/10 rounded-2xl px-5 py-3 w-fit">
                    <a href="{{ route('admin.pastas.index') }}" class="hover:text-[#00E500] transition">Início</a>
                    @if($pasta->parent)
                        @if($pasta->parent->parent)
                            <span>/</span>
                            <a href="{{ route('admin.pastas.show', $pasta->parent->parent->id) }}" class="hover:text-[#00E500] transition">{{ $pasta->parent->parent->nome }}</a>
                        @endif
                        <span>/</span>
                        <a href="{{ route('admin.pastas.show', $pasta->parent->id) }}" class="hover:text-[#00E500] transition">{{ $pasta->parent->nome }}</a>
                    @endif
                    <span>/</span>
                    <span class="text-white font-bold">{{ $pasta->nome }}</span>
                </div>

                @php
                    $isLevel2 = $pasta->parent_id && !$pasta->parent?->parent_id;
                    $isLevel3 = $pasta->parent_id && $pasta->parent?->parent_id && !$pasta->parent?->parent?->parent_id;
                @endphp

                <!-- CONTROLE DO SERVIÇO (Nível 3) -->
                @if($isLevel3)
                <div class="glass glow rounded-[35px] border border-white/10 overflow-hidden shadow-2xl p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 animate-fade">
                    <div class="flex items-start gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-[#003366] flex items-center justify-center text-3xl shrink-0">
                            ⚙️
                        </div>
                        <div>
                            <div class="flex items-center gap-3">
                                <span class="uppercase tracking-[3px] text-xs text-[#00E500] font-black">Status do Serviço</span>
                                <span class="px-3 py-1 rounded-full text-[10px] uppercase font-black border {{ $pasta->tipo_servico === 'pronto' ? 'bg-green-500/20 text-green-300 border-green-500/20' : 'bg-yellow-500/20 text-yellow-300 border-yellow-500/20' }}">
                                    {{ $pasta->tipo_servico === 'pronto' ? 'Concluído' : 'Pendente' }}
                                </span>
                            </div>
                            <h2 class="text-2xl font-black mt-1">Controle do Serviço</h2>
                            <p class="text-sm text-white/50 mt-1">Defina se este serviço está concluído ou pendente no sistema.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        @if($pasta->tipo_servico === 'pendente')
                            <button @click="finalizeModal = true" 
                                    class="bg-[#00E500] hover:bg-green-500 text-black px-6 py-4 rounded-2xl font-black uppercase text-sm transition whitespace-nowrap shadow-lg">
                                Finalizar Serviço
                            </button>
                        @else
                            <button @click="revertModal = true" 
                                    class="bg-yellow-500 hover:bg-yellow-400 text-black px-6 py-4 rounded-2xl font-black uppercase text-sm transition whitespace-nowrap shadow-lg">
                                Retornar para Pendente
                            </button>
                        @endif
                    </div>
                </div>
                @endif

                <!-- SIGEF INTEGRATION CARD -->
                @if($isLevel3)
                <div class="glass glow rounded-[35px] border border-white/10 overflow-hidden shadow-2xl p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 animate-fade">
                    <div class="flex items-start gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-[#003366] flex items-center justify-center text-3xl shrink-0">
                            🌐
                        </div>
                        <div>
                            <div class="flex items-center gap-3">
                                <span class="uppercase tracking-[3px] text-xs text-[#00E500] font-black">Integração SIGEF</span>
                                @if($pasta->codigo_sigef)
                                    <span class="bg-emerald-500/25 text-[#00E500] text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">Vinculado</span>
                                @else
                                    <span class="bg-yellow-500/25 text-yellow-300 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">Pendente</span>
                                @endif
                            </div>
                            <h2 class="text-2xl font-black mt-1">Código da Parcela (SIGEF)</h2>
                            @if($pasta->codigo_sigef)
                                <p class="text-sm font-mono text-white/60 mt-1 select-all">{{ $pasta->codigo_sigef }}</p>
                            @else
                                <p class="text-sm text-white/40 mt-1">Nenhum código SIGEF vinculado a este imóvel.</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        @if($pasta->codigo_sigef)
                            <a href="https://sigef.incra.gov.br/geo/parcela/detalhe/{{ $pasta->codigo_sigef }}/" 
                               target="_blank" 
                               class="bg-[#004A7C] hover:bg-blue-700 text-white px-5 py-3 rounded-2xl font-black uppercase text-xs transition whitespace-nowrap">
                                Visualizar no SIGEF
                            </a>
                            <button @click="openSigefModal = true" 
                                    class="bg-white/10 hover:bg-white/20 text-white px-5 py-3 rounded-2xl font-black uppercase text-xs transition whitespace-nowrap">
                                Alterar Código
                            </button>
                        @else
                            <button @click="openSigefModal = true" 
                                    class="bg-[#00E500] hover:bg-green-500 text-black px-5 py-3 rounded-2xl font-black uppercase text-xs transition whitespace-nowrap">
                                Vincular Código
                            </button>
                        @endif
                    </div>
                </div>
                @endif

                <!-- SUBPASTAS -->
                <div class="glass glow rounded-[35px] border border-white/10 overflow-hidden shadow-2xl">

                    <div class="px-8 py-6 border-b border-white/10 flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-black uppercase italic">
                                Subpastas
                            </h2>
                            <p class="text-sm text-white/40">
                                Organização do serviço
                            </p>
                        </div>
                        <div class="flex items-center gap-4">
                            <button @click="openCreateModal"
                                    class="bg-[#00E500] hover:bg-green-600 text-black font-black uppercase px-5 py-2.5 rounded-xl text-xs shadow-lg transition flex items-center gap-1.5">
                                <span>+ {{ $isLevel2 ? 'Novo Imóvel' : 'Nova Pasta' }}</span>
                            </button>
                        </div>
                    </div>

                    <div class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                            @forelse($pasta->subpastas as $sub)
                                <div class="glass glow rounded-3xl border border-white/5 p-6 flex flex-col justify-between hover:scale-[1.02] transition duration-300 card-hover">
                                    <div>
                                        <div class="flex items-start gap-4 mb-4">
                                            <div class="w-14 h-14 rounded-2xl bg-yellow-500/20 border border-yellow-500/20 flex items-center justify-center text-3xl shrink-0">
                                                📁
                                            </div>
                                            <div class="min-w-0">
                                                <h3 class="font-black uppercase text-white text-base tracking-wide truncate">
                                                    {{ $sub->nome }}
                                                </h3>
                                                <p class="text-xs text-white/40 mt-0.5">
                                                    Criada em {{ $sub->created_at->format('d/m/Y') }}
                                                </p>
                                            </div>
                                        </div>

                                        @if($sub->cliente_id)
                                            <!-- Badge Cliente -->
                                            <div class="mt-4 bg-white/5 border border-white/10 rounded-2xl p-4 space-y-2">
                                                <div>
                                                    <span class="text-[9px] uppercase tracking-widest text-white/50 block font-bold">Cliente</span>
                                                    <span class="text-sm font-black text-white uppercase truncate block leading-tight">{{ $sub->cliente?->name ?? 'Não Cadastrado' }}</span>
                                                </div>
                                                @if($sub->categoria_servico)
                                                    <div>
                                                        <span class="text-[9px] uppercase tracking-widest text-white/50 block font-bold">Categoria</span>
                                                        <span class="text-xs font-bold text-[#00E500] uppercase truncate block leading-tight">{{ $sub->categoria_servico }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @elseif($sub->identificador_cliente)
                                            <!-- Badge Cliente Pendente/Aguardando Cadastro -->
                                            <div class="mt-4 bg-yellow-500/10 border border-yellow-500/20 rounded-2xl p-4 space-y-2">
                                                <div>
                                                    <span class="text-[9px] uppercase tracking-widest text-yellow-400 block font-bold">Cliente (Não Cadastrado)</span>
                                                    <span class="text-xs font-bold text-white uppercase truncate block leading-tight">
                                                        CPF: {{ strlen($sub->identificador_cliente) === 11 ? preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "$1.$2.$3-$4", $sub->identificador_cliente) : $sub->identificador_cliente }}
                                                    </span>
                                                    <span class="text-[9px] text-yellow-300/80 block mt-1">Aguardando cadastro do cliente...</span>
                                                </div>
                                                @if($sub->categoria_servico)
                                                    <div>
                                                        <span class="text-[9px] uppercase tracking-widest text-white/50 block font-bold">Categoria</span>
                                                        <span class="text-xs font-bold text-[#00E500] uppercase truncate block leading-tight">{{ $sub->categoria_servico }}</span>
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
                                            @click="openDeleteModal('{{ route('admin.pastas.destroy', $sub->id) }}', '{{ addslashes($sub->nome) }}')"
                                            class="bg-red-600 hover:bg-red-700 transition px-4 py-2 rounded-xl text-xs uppercase font-black shadow-lg">
                                            Excluir
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-full py-16 text-center text-white/30 font-bold uppercase text-sm">
                                    Nenhuma subpasta encontrada.
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>

                <!-- DOCUMENTOS E PENDÊNCIAS -->
                @if($pasta->parent_id && $pasta->parent?->parent_id)
                <div class="glass glow rounded-[35px] border border-white/10 overflow-hidden shadow-2xl">

                    <div class="px-8 py-6 border-b border-white/10 flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-black uppercase italic">
                                Arquivos e Pendências
                            </h2>
                            <p class="text-sm text-white/40">
                                Documentos e pendências vinculadas a este serviço
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button @click="openUploadModal" class="bg-[#004A7C] hover:bg-blue-700 text-white font-black px-4 py-2.5 rounded-xl text-xs transition border border-white/10 uppercase">
                                Upload Arquivo
                            </button>
                            <button @click="openPendenciaModal" class="bg-yellow-500 hover:bg-yellow-400 text-black font-black px-4 py-2.5 rounded-xl text-xs transition uppercase">
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
                                                <p class="text-xs text-white/70 leading-relaxed">
                                                    {{ $pendencia->descricao }}
                                                </p>
                                            </div>
                                            <div class="flex items-center justify-between gap-4 mt-6 pt-4 border-t border-yellow-500/10">
                                                <span class="bg-yellow-500/20 text-yellow-300 text-[9px] font-bold px-2 py-0.5 rounded-full uppercase">
                                                    Aguardando
                                                </span>
                                                <button
                                                    @click="openDeleteModal('{{ route('pendencias.destroy', $pendencia->id) }}', '{{ addslashes($pendencia->titulo) }}')"
                                                    class="bg-red-600/80 hover:bg-red-700 text-white px-4 py-1.5 rounded-xl font-black uppercase text-[10px] transition">
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
                            <h3 class="text-xs uppercase tracking-[3px] text-[#00E500] font-bold mb-4">Documentos da Pasta</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                                @forelse($pasta->arquivos as $arq)
                                    <div class="glass border border-white/5 rounded-3xl p-6 flex flex-col justify-between hover:scale-[1.01] transition animate-fade">
                                        <div class="flex items-start gap-4 mb-4">
                                            <div class="w-12 h-12 rounded-2xl bg-blue-500/20 border border-blue-500/20 flex items-center justify-center text-2xl shrink-0">
                                                📄
                                            </div>
                                            <div class="min-w-0">
                                                <h4 class="font-black text-white uppercase text-sm truncate leading-snug">
                                                    {{ $arq->nome }}
                                                </h4>
                                                <p class="text-[10px] text-white/40 mt-1 uppercase font-bold">
                                                    {{ $arq->tipo }} • {{ $arq->tamanho }} MB
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 mt-6 pt-4 border-t border-white/5">
                                            <button
                                                @click="openPreviewModal('{{ asset('storage/'.$arq->path) }}', '{{ $arq->tipo }}', '{{ addslashes($arq->nome) }}')"
                                                class="flex-1 bg-[#003366] hover:bg-[#004A7C] text-white py-2 rounded-xl font-black uppercase text-[10px] text-center transition">
                                                Visualizar
                                            </button>

                                            <a href="{{ asset('storage/'.$arq->path) }}"
                                               download
                                               class="flex-1 bg-[#00E500] hover:bg-green-500 text-black py-2 rounded-xl font-black uppercase text-[10px] text-center transition">
                                                Baixar
                                            </a>

                                            <button
                                                @click="openDeleteModal('{{ route('arquivos.destroy', $arq->id) }}', '{{ addslashes($arq->nome) }}')"
                                                class="bg-red-600 hover:bg-red-700 text-white p-2 rounded-xl font-black uppercase text-[10px] transition shrink-0">
                                                Excluir
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-full py-12 text-center text-white/30 font-bold uppercase text-sm">
                                        Nenhum arquivo enviado.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                </div>
                @endif

            </main>

        <!-- CREATE FOLDER MODAL -->
        <div x-show="createFolderModal"
             x-transition
             class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm p-4 modal-overlay"
             x-cloak>

            <div class="modal-container bg-[#003366] border border-white/10 rounded-[35px] p-8 shadow-2xl text-white">

                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-3xl font-black uppercase italic text-[#00E500]">
                            {{ $isLevel2 ? 'Novo Imóvel' : 'Nova Subpasta' }}
                        </h2>
                        <p class="text-white/60 mt-2">
                            {{ $isLevel2 ? 'Associe o imóvel a um cliente usando CPF.' : 'Crie uma nova subpasta vinculada a esta pasta.' }}
                        </p>
                    </div>

                    <button @click="closeModal('createFolderModal')"
                            class="text-white/40 hover:text-white text-3xl leading-none">
                        ×
                    </button>
                </div>

                <form action="{{ route('pasta.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $pasta->id }}">

                    <div class="mb-6">
                        <label class="block text-white/70 uppercase text-xs font-bold mb-2">
                            Nome do Imóvel / Subpasta
                        </label>
                        <input type="text"
                               name="nome"
                               required
                               class="w-full bg-white text-[#003366] px-5 py-3 rounded-2xl outline-none font-bold">
                    </div>

                    @if($isLevel2)
                        <div class="mb-6">
                            <label class="block text-white/70 uppercase text-xs font-bold mb-2">
                                CPF do Cliente
                            </label>
                            <input type="text"
                                   name="identificador_cliente"
                                   required
                                   placeholder="Somente os 11 dígitos"
                                   class="w-full bg-white text-[#003366] px-5 py-3 rounded-2xl outline-none font-bold">
                        </div>

                        <div class="mb-6">
                            <label class="block text-white/70 uppercase text-xs font-bold mb-2">
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
                                class="flex-1 bg-red-600 hover:bg-red-700 text-white py-3 rounded-2xl font-black uppercase transition">
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

            <div class="modal-container bg-[#003366] border border-white/10 rounded-[35px] p-8 shadow-2xl text-white">

                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-3xl font-black uppercase italic text-[#00E500]">
                            Upload de Arquivo
                        </h2>
                        <p class="text-white/60 mt-2">
                            Anexe um documento para esta pasta.
                        </p>
                    </div>

                    <button @click="closeModal('uploadModal')"
                            class="text-white/40 hover:text-white text-3xl leading-none">
                        ×
                    </button>
                </div>

                <form action="{{ route('arquivos.store', $pasta->id) }}"
                      method="POST"
                      enctype="multipart/form-data">
                    @csrf

                    <div class="mb-6">
                        <label class="block text-white/70 uppercase text-xs font-bold mb-2">
                            Nome do Documento
                        </label>
                        <input type="text"
                               name="nome"
                               required
                               class="w-full bg-white text-[#003366] px-5 py-3 rounded-2xl outline-none font-bold">
                    </div>

                    <div class="mb-6">
                        <label class="block text-white/70 uppercase text-xs font-bold mb-2">
                            Selecionar Arquivo
                        </label>

                        <div class="relative">
                            <label class="relative w-full h-full flex items-center justify-center cursor-pointer bg-white/10 border border-white/20 rounded-2xl p-6 hover:bg-white/15 transition border-dashed">
                                <div class="pointer-events-none text-white/80 font-bold uppercase text-xs">Clique para selecionar o arquivo</div>
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
                                class="flex-1 bg-red-600 hover:bg-red-700 text-white py-3 rounded-2xl font-black uppercase transition">
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

            <div class="modal-container bg-[#003366] border border-white/10 rounded-[35px] p-8 shadow-2xl text-white">

                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-3xl font-black uppercase italic text-[#00E500]">
                            Nova Pendência
                        </h2>
                        <p class="text-white/60 mt-2">
                            Registre um item pendente para o cliente.
                        </p>
                    </div>

                    <button @click="closeModal('pendenciaModal')"
                            class="text-white/40 hover:text-white text-3xl leading-none">
                        ×
                    </button>
                </div>

                <form action="{{ route('pendencias.store', $pasta->id) }}" method="POST">
                    @csrf

                    <div class="mb-6">
                        <label class="block text-white/70 uppercase text-xs font-bold mb-2">
                            Título da Pendência
                        </label>
                        <input type="text"
                               name="titulo"
                               required
                               class="w-full bg-white text-[#003366] px-5 py-3 rounded-2xl outline-none font-bold">
                    </div>

                    <div class="mb-6">
                        <label class="block text-white/70 uppercase text-xs font-bold mb-2">
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
                                class="flex-1 bg-red-600 hover:bg-red-700 text-white py-3 rounded-2xl font-black uppercase transition">
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

            <div class="modal-container bg-[#003366] border border-white/10 rounded-[35px] p-8 shadow-2xl max-w-4xl flex flex-col animate-fade text-white">

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-3xl font-black uppercase italic text-[#00E500]" x-text="previewName">
                        Visualizar Arquivo
                    </h2>
                    <button @click="previewModal = false"
                            class="text-white/40 hover:text-white text-3xl leading-none">
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
                            <p class="text-white font-bold mb-2">Visualização não disponível</p>
                            <p class="text-white/40 text-sm mb-6">Arquivos do tipo .<span x-text="previewType.toUpperCase()"></span> não podem ser visualizados diretamente no navegador.</p>
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

            <div class="modal-container bg-[#003366] border border-white/10 rounded-[35px] p-8 shadow-2xl text-white">

                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-3xl font-black uppercase italic text-[#00E500]">
                            Código da Parcela (SIGEF)
                        </h2>
                        <p class="text-white/60 mt-2">
                            Insira o GUID/Código identificador da parcela no SIGEF.
                        </p>
                    </div>

                    <button @click="openSigefModal = false"
                            class="text-white/40 hover:text-white text-3xl leading-none">
                        ×
                    </button>
                </div>

                <form action="{{ route('admin.pastas.update-sigef', $pasta->id) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="mb-6">
                        <label class="block text-white/70 uppercase text-xs font-bold mb-2">
                            Código SIGEF (GUID)
                        </label>
                        <input type="text"
                               name="codigo_sigef"
                               value="{{ $pasta->codigo_sigef }}"
                               placeholder="Ex: c03260c8-47c0-43db-9ab4-1a91e5210987"
                               class="w-full bg-white text-[#003366] px-5 py-3 rounded-2xl outline-none font-mono font-bold">
                        <p class="text-white/40 text-xs mt-2">
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
                                class="flex-1 bg-red-600 hover:bg-red-700 text-white py-3 rounded-2xl font-black uppercase transition">
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

            <div class="bg-[#003366] border border-white/10 rounded-[35px] p-10 w-full max-w-md shadow-2xl text-white text-center">

                <h2 class="text-3xl font-black mb-4 text-red-500 uppercase italic">
                    Excluir Item?
                </h2>

                <p class="text-white/60 mb-10">
                    Deseja realmente excluir
                    <span class="font-black text-white" x-text="deleteName"></span>?
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
                            class="flex-1 bg-white/10 hover:bg-white/20 py-3 rounded-2xl font-black uppercase transition text-white border border-white/10">
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

            <div class="bg-[#003366] border border-white/10 rounded-[35px] p-10 w-full max-w-md shadow-2xl text-white text-center">

                <h2 class="text-3xl font-black mb-4 text-emerald-400 uppercase italic">
                    Finalizar Serviço?
                </h2>

                <p class="text-white/60 mb-10 leading-relaxed">
                    Deseja realmente finalizar o serviço <span class="font-black text-white uppercase">{{ $pasta->nome }}</span>? Ele será movido para Serviços Prontos e seu cliente será notificado.
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
                            class="flex-1 bg-white/10 hover:bg-white/20 py-3 rounded-2xl font-black uppercase transition text-white border border-white/10">
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

            <div class="bg-[#003366] border border-white/10 rounded-[35px] p-10 w-full max-w-md shadow-2xl text-white text-center">

                <h2 class="text-3xl font-black mb-4 text-yellow-400 uppercase italic">
                    Retornar para Pendente?
                </h2>

                <p class="text-white/60 mb-10 leading-relaxed">
                    Deseja retornar o serviço <span class="font-black text-white uppercase">{{ $pasta->nome }}</span> para a lista de Pendentes?
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
                            class="flex-1 bg-white/10 hover:bg-white/20 py-3 rounded-2xl font-black uppercase transition text-white border border-white/10">
                        Cancelar
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
                previewUrl: '',
                previewType: '',
                previewName: '',

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

</body>
</html>