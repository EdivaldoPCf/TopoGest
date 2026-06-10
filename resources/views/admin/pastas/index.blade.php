<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        Serviços - TopoGest
    </title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
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
    </style>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') . '?v=' . @filemtime(public_path('images/logo-icon.png')) }}">
</head>

<body class="min-h-screen overflow-x-hidden bg-[#E5E7EB] text-white">

<!-- BACKGROUND -->
<div class="fixed inset-0 -z-10">
    <img
        src="{{ asset('images/background-topo.jpg') . '?v=' . @filemtime(public_path('images/background-topo.jpg')) }}"
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
        createModal:false,
        deleteModal:false,
        deleteId:null,
        deleteName:''
     }">

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
    <div class="mb-10">
        <div class="inline-flex items-center gap-3 glass glow rounded-3xl px-8 py-5 border border-white/10">
            <div class="w-3 h-12 rounded-full bg-[#00E500]"></div>
            <div>
                <p class="uppercase tracking-[4px] text-xs text-white/60">
                    Painel Administrativo
                </p>
                <h1 class="text-3xl md:text-4xl font-black italic uppercase tracking-tight">
                    Serviços
                </h1>
            </div>
        </div>
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

            <div class="glass glow rounded-[35px] border border-white/10 shadow-2xl p-5 md:p-8">

                <!-- TOPO -->
                <div class="flex flex-col xl:flex-row gap-5 justify-between xl:items-center mb-8">

                    <div>
                        <h2 class="text-2xl font-black uppercase italic text-[#00E500]">
                            Pastas
                        </h2>
                        <p class="text-white/50 mt-1 text-sm">
                            Gerencie as pastas cadastradas no sistema.
                        </p>
                    </div>

                    <!-- BOTÕES -->
                    <div class="flex flex-col md:flex-row gap-4">
                        <!-- NOVA -->
                        <button
                            @click="createModal = true"
                            class="bg-[#00E500] hover:bg-green-600 text-black font-black uppercase px-8 py-4 rounded-2xl shadow-xl transition flex items-center justify-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Criar Pasta
                        </button>
                    </div>

                </div>

                <!-- GRID DE PASTAS -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @forelse($pastas as $pasta)
                        <div class="glass glow rounded-3xl border border-white/10 p-6 flex flex-col justify-between hover:scale-[1.02] transition duration-300 card-hover">
                            <div class="flex items-start gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-yellow-500/20 border border-yellow-500/20 flex items-center justify-center text-3xl">
                                    📁
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-black uppercase text-white text-lg tracking-wide truncate">
                                        {{ $pasta->nome }}
                                    </h3>
                                    <p class="text-xs text-white/40 mt-1">
                                        Criada em {{ $pasta->created_at->format('d/m/Y') }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-4 mt-6 pt-4 border-t border-white/5">
                                <span class="px-3 py-1 rounded-full text-[10px] uppercase font-black border {{ $pasta->tipo_servico === 'pronto' ? 'bg-green-500/20 text-green-300 border-green-500/20' : 'bg-yellow-500/20 text-yellow-300 border-yellow-500/20' }}">
                                    {{ $pasta->tipo_servico === 'pronto' ? 'Concluído' : 'Pendente' }}
                                </span>

                                <div class="flex items-center gap-2">
                                    <!-- ABRIR -->
                                    <a href="{{ route('admin.pastas.show', $pasta->id) }}"
                                       class="bg-[#003366] hover:bg-[#004A7C] transition px-4 py-2 rounded-xl text-xs uppercase font-black shadow-lg">
                                        Abrir
                                    </a>

                                    <!-- EXCLUIR -->
                                    <button
                                        @click="
                                            deleteModal = true;
                                            deleteId = {{ $pasta->id }};
                                            deleteName = '{{ addslashes($pasta->nome) }}';
                                        "
                                        class="bg-red-600 hover:bg-red-700 transition px-4 py-2 rounded-xl text-xs uppercase font-black shadow-lg">
                                        Excluir
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-20 text-center text-white/30 font-bold uppercase text-sm">
                            Nenhuma pasta encontrada.
                        </div>
                    @endforelse
                </div>

            </div>

        </main>

    <!-- MODAL CRIAR -->
    <div
        x-show="createModal"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4"
        x-cloak>

        <div class="glass-dark rounded-[35px] border border-white/10 shadow-2xl w-full max-w-lg p-8">

            <!-- TOPO -->
            <div class="flex justify-between items-center mb-8">

                <h2 class="text-3xl font-black uppercase italic text-[#00E500]">
                    Criar Pasta
                </h2>

                <button
                    @click="createModal = false"
                    class="text-white/40 hover:text-white text-3xl leading-none">
                    ×
                </button>

            </div>

            <!-- FORM -->
            <form action="{{ route('pasta.store') }}" method="POST">
                @csrf
                <input type="hidden" name="tipo_servico" value="pendente">

                <!-- INPUT -->
                <div class="mb-8">
                    <label class="block text-white/60 uppercase text-xs font-black mb-3">
                        Nome da Pasta
                    </label>
                    <input type="text"
                           name="nome"
                           required
                           placeholder="Digite o nome da pasta..."
                           class="w-full bg-white text-[#003366] rounded-2xl py-5 px-6 text-lg font-bold outline-none shadow-inner">
                </div>

                <!-- BOTÕES -->
                <div class="flex flex-col md:flex-row gap-4">
                    <button type="submit"
                            class="flex-1 bg-[#00E500] hover:bg-green-600 text-black py-4 rounded-2xl font-black uppercase shadow-xl transition">
                        Criar Pasta
                    </button>

                    <button type="button"
                            @click="createModal = false"
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white py-4 rounded-2xl font-black uppercase shadow-xl transition">
                        Cancelar
                    </button>
                </div>

            </form>

        </div>

    </div>

    <!-- MODAL DELETE -->
    <div
        x-show="deleteModal"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4"
        x-cloak>

        <div class="glass-dark rounded-[35px] border border-white/10 shadow-2xl w-full max-w-lg p-8 text-center">

            <!-- ÍCONE -->
            <div class="w-20 h-20 rounded-full bg-red-500/20 border border-red-500/20 flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>

            <!-- TITULO -->
            <h2 class="text-3xl font-black uppercase italic text-red-400 mb-4">
                Área Sensível
            </h2>

            <!-- TEXTO -->
            <p class="text-white/70 leading-relaxed mb-10">
                A pasta <span class="font-black text-yellow-300 uppercase" x-text="deleteName"></span> será enviada para aprovação de exclusão.
            </p>

            <!-- BOTÕES -->
            <div class="flex flex-col md:flex-row gap-4">
                <!-- CONFIRMAR -->
                <button
                    @click="
                        fetch(`/admin/pastas/${deleteId}/solicitar-exclusao`,{
                            method:'POST',
                            headers:{
                                'X-CSRF-TOKEN':'{{ csrf_token() }}',
                                'Accept':'application/json'
                            }
                        }).then(() => location.reload())
                    "
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white py-4 rounded-2xl font-black uppercase shadow-xl transition">
                    Solicitar Exclusão
                </button>

                <!-- CANCEL -->
                <button
                    @click="deleteModal = false"
                    class="flex-1 bg-white/10 hover:bg-white/20 text-white py-4 rounded-2xl font-black uppercase shadow-xl transition">
                    Cancelar
                </button>
            </div>

        </div>

    </div>

</div>

</body>
</html>