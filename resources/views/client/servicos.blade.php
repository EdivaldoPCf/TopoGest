<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Imóveis - TopoGest</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(0, 51, 102, 0.7);
            border-radius: 999px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 51, 102, 1);
        }
    </style>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') . '?v=' . @filemtime(public_path('images/logo-icon.png')) }}">
</head>

<body class="bg-gray-100 min-h-screen overflow-auto">

    <!-- Background -->
    <div class="fixed inset-0 z-0 bg-cover bg-center bg-no-repeat bg-fixed"
         style="background-image: url('{{ asset('images/background-topo.jpg') . '?v=' . @filemtime(public_path('images/background-topo.jpg')) }}');">

        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/20 backdrop-blur-[2px]"></div>
    </div>

    <div class="relative z-10 flex flex-col min-h-screen px-4 py-5 md:px-8 md:py-6 max-w-7xl mx-auto">

        <!-- HEADER -->
        <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-8">

            <!-- Logo -->
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

            <!-- User Actions -->
            <div class="flex items-center gap-4 self-end lg:self-auto">

                <!-- Notifications -->
                <a href="{{ route('notificacoes.index') }}"
                   class="relative bg-[#003366]/95 backdrop-blur-md p-4 rounded-2xl text-white shadow-2xl border border-white/20 hover:bg-[#002244] hover:scale-105 transition-all duration-300 flex items-center justify-center">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-7 w-7"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>

                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <span class="absolute -top-1 -right-1 flex h-5 w-5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-5 w-5 bg-red-600 border-2 border-[#003366]"></span>
                        </span>
                    @endif
                </a>

                <!-- Profile -->
                <a href="{{ route('profile.edit') }}"
                   class="group bg-[#003366]/95 backdrop-blur-md text-white flex items-center gap-4 px-5 py-3 rounded-3xl shadow-2xl border border-white/20 hover:bg-[#002244] transition-all duration-300 hover:scale-[1.02]">

                    <div class="w-14 h-14 rounded-full overflow-hidden border-2 border-white shadow-inner bg-[#004A7C] flex items-center justify-center">

                        @if(auth()->user()->photo)
                            <img src="{{ asset('storage/' . auth()->user()->photo) }}"
                                 class="w-full h-full object-cover"
                                 alt="Perfil">
                        @else
                            <span class="font-black text-sm tracking-widest">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </span>
                        @endif

                    </div>

                    <div class="flex flex-col leading-tight">
                        <span class="text-[10px] uppercase tracking-[0.3em] opacity-70 font-semibold">
                            Bem-vindo
                        </span>

                        <span class="font-black text-lg uppercase">
                            {{ explode(' ', auth()->user()->name)[0] }}
                        </span>
                    </div>
                </a>

            </div>
        </header>

        <!-- MAIN -->
        <main class="flex-1 mt-8">

                <!-- CONTENT AREA -->
                <section class="w-full space-y-8">

                    <!-- TITLE -->
                    <div class="inline-flex items-center gap-4 bg-[#003366]/95 text-white px-8 py-4 rounded-3xl shadow-2xl border border-white/10">

                        <div class="bg-white/10 p-3 rounded-2xl">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-8 h-8"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M3 10l9-7 9 7v9a2 2 0 01-2 2h-4a2 2 0 01-2-2V12H9v7a2 2 0 01-2 2H3z"/>
                            </svg>
                        </div>

                        <div>
                            <h1 class="text-2xl md:text-3xl font-black uppercase tracking-tight italic">
                                Meus Imóveis
                            </h1>

                            <p class="text-xs opacity-80 font-medium mt-1">
                                Acompanhe seus imóveis e serviços vinculados.
                            </p>
                        </div>

                    </div>

                    <!-- LIST -->
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 pb-10">

                        @forelse($servicos as $servico)

                            <div class="group relative overflow-hidden bg-white/85 backdrop-blur-xl rounded-[30px] border border-white/60 shadow-2xl p-6 hover:-translate-y-1 hover:shadow-[0_20px_50px_rgba(0,0,0,0.25)] transition-all duration-300">

                                <!-- Glow -->
                                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition duration-500 bg-gradient-to-br from-white/30 to-transparent"></div>

                                <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">

                                    <!-- Info -->
                                    <div class="flex-1">

                                        <div class="flex items-start gap-4 mb-4">

                                            <div class="bg-[#003366] text-white p-4 rounded-2xl shadow-lg">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     class="w-7 h-7"
                                                     fill="none"
                                                     viewBox="0 0 24 24"
                                                     stroke="currentColor">

                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M3 10l9-7 9 7v9a2 2 0 01-2 2h-4a2 2 0 01-2-2V12H9v7a2 2 0 01-2 2H3z"/>
                                                </svg>
                                            </div>

                                            <div>
                                                <h2 class="text-[#003366] text-2xl font-black uppercase italic tracking-tight leading-tight">
                                                    {{ $servico->nome }}
                                                </h2>

                                                <p class="text-gray-500 font-semibold mt-1">
                                                    {{ $servico->categoria_servico ?? 'Serviço Geral' }}
                                                </p>
                                            </div>

                                        </div>

                                        <!-- Status -->
                                        <div class="flex items-center gap-3 flex-wrap">

                                            <span class="px-5 py-2 rounded-full text-xs font-black uppercase tracking-widest shadow-md
                                                {{ $servico->tipo_servico == 'pendente'
                                                    ? 'bg-yellow-400 text-yellow-900'
                                                    : 'bg-green-500 text-white' }}">

                                                {{ $servico->tipo_servico }}

                                            </span>

                                            <span class="text-sm text-gray-500 font-semibold">
                                                Atualizado recentemente
                                            </span>

                                        </div>

                                    </div>

                                    <!-- Action -->
                                    <div class="flex items-center">

                                        <a href="{{ route('client.servico.show', $servico->id) }}"
                                           class="group/button flex items-center gap-3 bg-[#003366] hover:bg-[#002244] text-white px-7 py-4 rounded-2xl font-black uppercase tracking-wide shadow-2xl transition-all duration-300 hover:scale-105">

                                            Abrir

                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 class="w-5 h-5 transition-transform duration-300 group-hover/button:translate-x-1"
                                                 fill="none"
                                                 viewBox="0 0 24 24"
                                                 stroke="currentColor">

                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M9 5l7 7-7 7"/>
                                            </svg>

                                        </a>

                                    </div>

                                </div>

                            </div>

                        @empty

                            <div class="col-span-full">

                                <div class="bg-[#003366]/95 backdrop-blur-xl text-white rounded-[32px] shadow-2xl border border-white/20 p-12 text-center">

                                    <div class="flex justify-center mb-6">
                                        <div class="bg-white/10 p-5 rounded-3xl">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 class="w-14 h-14"
                                                 fill="none"
                                                 viewBox="0 0 24 24"
                                                 stroke="currentColor">

                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      stroke-width="1.8"
                                                      d="M3 10l9-7 9 7v9a2 2 0 01-2 2h-4a2 2 0 01-2-2V12H9v7a2 2 0 01-2 2H3z"/>
                                            </svg>
                                        </div>
                                    </div>

                                    <h2 class="text-3xl font-black uppercase italic mb-3">
                                        Nenhum imóvel encontrado
                                    </h2>

                                    <p class="text-white/80 max-w-xl mx-auto">
                                        Você ainda não possui imóveis ou serviços vinculados à sua conta.
                                    </p>

                                </div>

                            </div>

                        @endforelse

                    </div>

                </section>

        </main>

        <!-- FOOTER -->
        <footer class="pt-6 flex justify-between items-center">

            <a href="{{ route('dashboard') }}"
               class="group flex items-center gap-3 bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-2xl shadow-2xl border border-red-400/30 transition-all duration-300 hover:scale-105 font-black uppercase tracking-wider">

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-5 w-5 transition-transform duration-300 group-hover:-translate-x-1"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M15 19l-7-7 7-7"/>
                </svg>

                Voltar

            </a>

            <div class="hidden md:block text-white/80 text-sm font-medium tracking-wide">
                © {{ date('Y') }} TopoGest
            </div>

        </footer>

    </div>

</body>
</html>