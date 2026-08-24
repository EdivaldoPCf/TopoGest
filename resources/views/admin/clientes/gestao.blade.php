<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão: {{ $cliente->name }} - TopoGest</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

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
            background: rgba(255,255,255,.12);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .glass-dark {
            background: rgba(0,51,102,.65);
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

        .animate-fade {
            animation: fade .25s ease;
        }

        .modal-overlay {
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
        }

        .modal-container {
            width: 100%;
            max-width: 45rem;
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
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') . '?v=' . @filemtime(public_path('images/logo-icon.png')) }}">
</head>

<body class="min-h-screen overflow-x-hidden bg-[#E5E7EB] text-white">

<!-- BACKGROUND -->
<div class="fixed inset-0 -z-10">
    <img src="{{ asset('images/background-topo.jpg') . '?v=' . @filemtime(public_path('images/background-topo.jpg')) }}"
         class="w-full h-full object-cover"
         alt="Background">
    <div class="absolute inset-0 bg-[#00111f]/50"></div>
    <div class="absolute inset-0"
         style="background:
         radial-gradient(circle at top left, rgba(0,74,124,.35), transparent 35%),
         radial-gradient(circle at bottom right, rgba(0,51,102,.40), transparent 35%);">
    </div>
</div>

<div class="relative w-full max-w-7xl mx-auto px-4 md:px-8 py-8 animate-fade"
     x-data="{
        tab: 'pendentes',
        searchPendentes: '',
        searchProntos: '',
        searchDocumentos: '',
        filtroPendentes: '',
        filtroProntos: '',
        filtroDocumentos: '',
        previewModal: false,
        previewUrl: '',
        previewType: '',
        previewName: '',
        pesquisar(tipo) {
            if (tipo === 'pendentes') {
                this.filtroPendentes = this.searchPendentes.toLowerCase();
            }
            if (tipo === 'prontos') {
                this.filtroProntos = this.searchProntos.toLowerCase();
            }
            if (tipo === 'documentos') {
                this.filtroDocumentos = this.searchDocumentos.toLowerCase();
            }
        },
        openPreviewModal(url, type, name) {
            this.previewUrl = url;
            this.previewType = type.toLowerCase();
            this.previewName = name;
            this.previewModal = true;
        }
     }"
     @keydown.escape.window="previewModal = false">

    <!-- HEADER -->
    <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8 mb-12">
        <!-- LOGO -->
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-4 group w-fit">
            <div class="relative flex items-center justify-center w-20 h-20 rounded-2xl bg-white shadow-2xl transition group-hover:scale-105">
                <img src="{{ asset('images/logo-icon.png') . '?v=' . @filemtime(public_path('images/logo-icon.png')) }}"
                     alt="TopoGest"
                     class="w-full h-full object-contain p-2">
            </div>
            <div class="relative flex items-center h-14 px-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                <img src="{{ asset('images/logo-text.png') . '?v=' . @filemtime(public_path('images/logo-text.png')) }}"
                     alt="TopoGest"
                     class="relative z-10 h-8 w-auto">
            </div>
        </a>

        <!-- ACTIONS -->
        <div class="flex items-center gap-4 self-end lg:self-auto">
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
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </a>

            <!-- VOLTAR -->
            <a href="{{ route('admin.clientes') }}"
               class="bg-white/10 hover:bg-white/20 text-white px-6 py-4 rounded-2xl font-black uppercase text-sm transition border border-white/10 flex items-center gap-2 shadow-lg card-hover">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Voltar
            </a>
        </div>
    </header>

    <!-- CLIENT PROFILE BANNER -->
    <div class="glass glow rounded-[35px] border border-white/10 p-8 mb-8 animate-fade">
        <div class="flex flex-col md:flex-row items-center md:items-start gap-8">
            <!-- Profile Photo -->
            <div class="w-24 h-24 rounded-3xl overflow-hidden border-2 border-[#00E500]/30 shadow-2xl bg-white flex items-center justify-center text-[#003366] font-black text-3xl shrink-0">
                @if($cliente->photo)
                    <img src="{{ asset('storage/'.$cliente->photo) }}" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr($cliente->name,0,2)) }}
                @endif
            </div>

            <!-- Text Info -->
            <div class="flex-1 text-center md:text-left space-y-2">
                <span class="text-xs uppercase tracking-[3px] text-white/50 font-bold block">Perfil do Cliente</span>
                <h2 class="text-3xl font-black uppercase italic text-[#00E500] tracking-wide leading-tight">
                    {{ $cliente->name }}
                </h2>
                <div class="flex flex-wrap justify-center md:justify-start gap-x-6 gap-y-2 text-sm text-white/70">
                    <span class="flex items-center gap-1.5">
                        📧 {{ $cliente->email }}
                    </span>
                    <span class="flex items-center gap-1.5 font-mono">
                        📇 CPF/CNPJ: {{ $cliente->cpf }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        📞 Telefone: {{ $cliente->phone }}
                    </span>
                </div>
            </div>

            <!-- Statistics Column -->
            <div class="grid grid-cols-3 md:flex md:flex-col gap-4 text-center md:text-right shrink-0 border-t md:border-t-0 md:border-l border-white/10 pt-6 md:pt-0 md:pl-8 w-full md:w-auto">
                <div>
                    <span class="text-[10px] uppercase tracking-widest text-white/50 block font-bold">Pendentes</span>
                    <strong class="text-2xl font-black text-yellow-400">{{ $pendentes->count() }}</strong>
                </div>
                <div>
                    <span class="text-[10px] uppercase tracking-widest text-white/50 block font-bold">Concluídos</span>
                    <strong class="text-2xl font-black text-emerald-400">{{ $prontos->count() }}</strong>
                </div>
                <div>
                    <span class="text-[10px] uppercase tracking-widest text-white/50 block font-bold">Documentos</span>
                    <strong class="text-2xl font-black text-[#00E500]">{{ $documentos->count() }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- TABS BAR -->
    <div class="glass glow rounded-3xl border border-white/10 p-2 flex flex-col sm:flex-row gap-2 mb-8 animate-fade">
        <button @click="tab='pendentes'"
                :class="tab === 'pendentes' ? 'bg-[#003366] text-[#00E500] border-[#00E500]/30 shadow-lg' : 'text-white/70 hover:bg-white/5 hover:text-white'"
                class="flex-1 py-4 px-6 rounded-2xl font-black uppercase text-sm tracking-wider transition border border-transparent flex items-center justify-center gap-2">
            <span>⏳</span>
            <span>Serviços Pendentes</span>
            <span class="bg-white/10 text-white text-[10px] px-2 py-0.5 rounded-full" :class="tab === 'pendentes' ? 'bg-yellow-400/20 text-yellow-300' : ''">{{ $pendentes->count() }}</span>
        </button>

        <button @click="tab='prontos'"
                :class="tab === 'prontos' ? 'bg-[#003366] text-[#00E500] border-[#00E500]/30 shadow-lg' : 'text-white/70 hover:bg-white/5 hover:text-white'"
                class="flex-1 py-4 px-6 rounded-2xl font-black uppercase text-sm tracking-wider transition border border-transparent flex items-center justify-center gap-2">
            <span>✓</span>
            <span>Serviços Prontos</span>
            <span class="bg-white/10 text-white text-[10px] px-2 py-0.5 rounded-full" :class="tab === 'prontos' ? 'bg-emerald-400/20 text-emerald-300' : ''">{{ $prontos->count() }}</span>
        </button>

        <button @click="tab='documentos'"
                :class="tab === 'documentos' ? 'bg-[#003366] text-[#00E500] border-[#00E500]/30 shadow-lg' : 'text-white/70 hover:bg-white/5 hover:text-white'"
                class="flex-1 py-4 px-6 rounded-2xl font-black uppercase text-sm tracking-wider transition border border-transparent flex items-center justify-center gap-2">
            <span>📄</span>
            <span>Documentos</span>
            <span class="bg-white/10 text-white text-[10px] px-2 py-0.5 rounded-full" :class="tab === 'documentos' ? 'bg-blue-400/20 text-blue-300' : ''">{{ $documentos->count() }}</span>
        </button>
    </div>

    <!-- CONTENT CARD -->
    <div class="glass glow rounded-[35px] border border-white/10 shadow-2xl p-6 md:p-8 animate-fade min-h-[500px]">

        <!-- SEARCH AND FILTER BAR -->
        <div class="flex flex-col xl:flex-row gap-6 justify-between items-start xl:items-center mb-8 pb-6 border-b border-white/10">
            <div>
                <h3 class="text-2xl font-black uppercase italic text-[#00E500]">
                    <span x-show="tab === 'pendentes'">Serviços Pendentes</span>
                    <span x-show="tab === 'prontos'">Serviços Prontos</span>
                    <span x-show="tab === 'documentos'">Documentos do Cliente</span>
                </h3>
                <p class="text-xs text-white/50 mt-1">
                    <span x-show="tab === 'pendentes'">Listagem de parcelas e imóveis em andamento no sistema.</span>
                    <span x-show="tab === 'prontos'">Listagem de parcelas finalizadas e certificadas.</span>
                    <span x-show="tab === 'documentos'">Todos os arquivos enviados pelo cliente ou gerados pela administração.</span>
                </p>
            </div>

            <!-- Search Box -->
            <div class="flex flex-col sm:flex-row gap-3 w-full xl:w-auto">
                <div class="relative w-full sm:w-[320px]">
                    <input type="text"
                           :placeholder="tab === 'documentos' ? 'Pesquisar documento...' : 'Pesquisar serviço...'"
                           x-model="tab === 'pendentes' ? searchPendentes : (tab === 'prontos' ? searchProntos : searchDocumentos)"
                           @keyup.enter="pesquisar(tab)"
                           class="w-full bg-[#003366]/40 border border-white/10 rounded-2xl py-3 pl-12 pr-4 text-xs font-semibold text-white placeholder:text-white/40 outline-none focus:ring-2 focus:ring-[#00E500]/20">
                    <span class="absolute left-4 top-3 text-white/40">🔍</span>
                </div>
                <button @click="pesquisar(tab)"
                        class="bg-[#00E500] hover:bg-green-500 text-black font-black uppercase text-xs tracking-wider px-6 py-3 rounded-2xl shadow-md transition">
                    Buscar
                </button>
            </div>
        </div>

        <!-- PENDENTES TAB CONTENT -->
        <div x-show="tab === 'pendentes'" x-transition>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse($pendentes as $servico)
                    <div x-show="filtroPendentes === '' || '{{ strtolower($servico->nome) }} {{ $servico->created_at->format('d/m/Y') }}'.includes(filtroPendentes)"
                         class="glass glow rounded-3xl border border-white/10 p-6 flex flex-col justify-between hover:scale-[1.01] transition duration-300 card-hover">
                        <div>
                           <div class="flex items-start gap-4">
                               <div class="w-12 h-12 rounded-2xl bg-yellow-500/20 border border-yellow-500/20 flex items-center justify-center text-2xl shrink-0">
                                   📁
                               </div>
                               <div class="min-w-0">
                                   <h4 class="font-black uppercase text-white text-base tracking-wide truncate">
                                       {{ $servico->nome }}
                                   </h4>
                                   <p class="text-[10px] text-white/40 mt-1">
                                       Cadastrado em {{ $servico->created_at->format('d/m/Y') }}
                                   </p>
                               </div>
                           </div>

                           <!-- Metadata Badges -->
                           <div class="mt-4 flex flex-wrap gap-2">
                               <span class="bg-yellow-500/10 text-yellow-300 border border-yellow-500/10 px-2.5 py-0.5 rounded-full text-[9px] uppercase font-bold">
                                   Pendente
                               </span>
                               @if($servico->categoria_servico)
                                   <span class="bg-white/5 text-white/70 border border-white/10 px-2.5 py-0.5 rounded-full text-[9px] uppercase font-bold">
                                       {{ $servico->categoria_servico }}
                                   </span>
                               @elseif($servico->parent?->nome)
                                   <span class="bg-white/5 text-white/70 border border-white/10 px-2.5 py-0.5 rounded-full text-[9px] uppercase font-bold">
                                       {{ $servico->parent->nome }}
                                   </span>
                               @endif
                               @if($servico->parent?->parent?->nome)
                                   <span class="bg-white/5 text-white/70 border border-white/10 px-2.5 py-0.5 rounded-full text-[9px] uppercase font-bold">
                                       Ano: {{ $servico->parent->parent->nome }}
                                   </span>
                               @endif
                           </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 mt-6 pt-4 border-t border-white/5">
                           <a href="{{ route('admin.pastas.show', $servico->id) }}"
                              class="bg-[#003366] hover:bg-[#004A7C] text-white px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition shadow-lg flex items-center gap-1">
                               <span>Abrir</span>
                               <span>→</span>
                           </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-16 text-center text-white/30 font-bold uppercase text-sm">
                       Nenhum serviço pendente encontrado.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- PRONTOS TAB CONTENT -->
        <div x-show="tab === 'prontos'" x-transition>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse($prontos as $servico)
                    <div x-show="filtroProntos === '' || '{{ strtolower($servico->nome) }} {{ $servico->updated_at->format('d/m/Y') }}'.includes(filtroProntos)"
                         class="glass glow rounded-3xl border border-white/10 p-6 flex flex-col justify-between hover:scale-[1.01] transition duration-300 card-hover">
                        <div>
                           <div class="flex items-start gap-4">
                               <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 border border-emerald-500/20 flex items-center justify-center text-2xl shrink-0">
                                   📁
                               </div>
                               <div class="min-w-0">
                                   <h4 class="font-black uppercase text-white text-base tracking-wide truncate">
                                       {{ $servico->nome }}
                                   </h4>
                                   <p class="text-[10px] text-white/40 mt-1">
                                       Concluído em {{ $servico->updated_at->format('d/m/Y') }}
                                   </p>
                               </div>
                           </div>

                           <!-- Metadata Badges -->
                           <div class="mt-4 flex flex-wrap gap-2">
                               <span class="bg-emerald-500/10 text-emerald-300 border border-emerald-500/10 px-2.5 py-0.5 rounded-full text-[9px] uppercase font-bold">
                                   Concluído
                               </span>
                               @if($servico->categoria_servico)
                                   <span class="bg-white/5 text-white/70 border border-white/10 px-2.5 py-0.5 rounded-full text-[9px] uppercase font-bold">
                                       {{ $servico->categoria_servico }}
                                   </span>
                               @elseif($servico->parent?->nome)
                                   <span class="bg-white/5 text-white/70 border border-white/10 px-2.5 py-0.5 rounded-full text-[9px] uppercase font-bold">
                                       {{ $servico->parent->nome }}
                                   </span>
                               @endif
                               @if($servico->parent?->parent?->nome)
                                   <span class="bg-white/5 text-white/70 border border-white/10 px-2.5 py-0.5 rounded-full text-[9px] uppercase font-bold">
                                       Ano: {{ $servico->parent->parent->nome }}
                                   </span>
                               @endif
                           </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 mt-6 pt-4 border-t border-white/5">
                           <a href="{{ route('admin.pastas.show', $servico->id) }}"
                              class="bg-[#003366] hover:bg-[#004A7C] text-white px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition shadow-lg flex items-center gap-1">
                               <span>Abrir</span>
                               <span>→</span>
                           </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-16 text-center text-white/30 font-bold uppercase text-sm">
                       Nenhum serviço pronto encontrado.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- DOCUMENTOS TAB CONTENT -->
        <div x-show="tab === 'documentos'" x-transition>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse($documentos as $doc)
                    <div x-show="filtroDocumentos === '' || '{{ strtolower($doc->pasta->nome) }} {{ strtolower($doc->nome) }} {{ $doc->created_at->format('d/m/Y') }}'.includes(filtroDocumentos)"
                         class="glass border border-white/10 rounded-3xl p-6 flex flex-col justify-between hover:scale-[1.01] transition duration-300 card-hover">
                        <div>
                           <div class="flex items-start gap-4 mb-4">
                               <div class="w-12 h-12 rounded-2xl bg-blue-500/25 border border-blue-500/20 flex items-center justify-center text-2xl shrink-0">
                                   📄
                               </div>
                               <div class="min-w-0">
                                   <span class="text-[9px] uppercase tracking-wider text-[#00E500] font-black block truncate">
                                       Imóvel: {{ $doc->pasta->nome }}
                                   </span>
                                   <h4 class="font-black text-white uppercase text-sm truncate mt-0.5 leading-snug">
                                       {{ $doc->nome }}
                                   </h4>
                                   <p class="text-[10px] text-white/40 mt-1 uppercase font-bold">
                                       {{ $doc->tipo }} • {{ $doc->tamanho }} MB
                                   </p>
                               </div>
                           </div>
                        </div>

                        <div class="flex items-center gap-2 mt-6 pt-4 border-t border-white/5">
                           <button type="button"
                                   @click="openPreviewModal('{{ asset('storage/'.$doc->path) }}', '{{ $doc->tipo }}', '{{ addslashes($doc->nome) }}')"
                                   class="flex-1 bg-[#003366] hover:bg-[#004A7C] text-white py-2 rounded-xl font-black uppercase text-[10px] text-center transition">
                               Visualizar
                           </button>

                           <a href="{{ route('arquivo.download', $doc->id) }}"
                              class="flex-1 bg-[#00E500] hover:bg-green-500 text-black py-2 rounded-xl font-black uppercase text-[10px] text-center transition">
                               Baixar
                           </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-16 text-center text-white/30 font-bold uppercase text-sm">
                       Nenhum documento encontrado.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- PREVIEW MODAL -->
    <div x-show="previewModal"
         x-transition
         class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm p-4 modal-overlay"
         x-cloak>

        <div class="modal-container bg-[#003366] border border-white/10 rounded-[35px] p-8 shadow-2xl flex flex-col animate-fade text-white">

            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-black uppercase italic text-[#00E500]" x-text="previewName">
                    Visualizar Arquivo
                </h3>
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

</div>

</body>
</html>