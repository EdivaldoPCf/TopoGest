<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Getec Topografia - Admin</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
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
    </style>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">
    @vite(['resources/js/app.js'])
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

    <div class="relative w-full max-w-7xl mx-auto px-4 md:px-8 py-8">

        <!-- HEADER -->
        <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8 mb-12">

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

            <!-- RIGHT -->
            <div class="flex flex-wrap items-center gap-4">

                <!-- NOTIFICAÇÕES -->
                <div x-data="{ unreadCount: {{ auth()->user()->unreadNotifications->count() }} }"
                     x-init="
                        if (window.Echo) {
                            window.Echo.private('App.Models.User.{{ auth()->id() }}')
                                .listen('NovaNotificacaoEvent', (e) => {
                                    unreadCount++;
                                });
                        }
                     ">
                    <a href="{{ route('notificacoes.index') }}"
                       class="relative glass glow w-14 h-14 rounded-2xl flex items-center justify-center border border-white/10 hover:bg-[#003366] transition-all duration-300 card-hover">

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

                        <template x-if="unreadCount > 0">
                            <span class="absolute -top-1 -right-1 flex h-4 w-4">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 border border-white/20"></span>
                            </span>
                        </template>
                    </a>
                </div>

                <!-- PERFIL -->
                <a href="{{ route('profile.edit') }}"
                   class="glass glow rounded-3xl px-4 py-3 flex items-center gap-4 border border-white/10 hover:border-white/20 transition-all duration-300 card-hover">

                    <div class="w-14 h-14 rounded-2xl overflow-hidden bg-[#003366] border-2 border-white/20 flex items-center justify-center text-lg font-black uppercase">

                        @if(auth()->user()->photo)
                            <img src="{{ asset('storage/' . auth()->user()->photo) }}"
                                 class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        @endif
                    </div>

                    <div class="leading-tight">
                        <p class="text-[11px] uppercase tracking-[3px] text-white/60">
                            Bem-vindo
                        </p>

                        <h2 class="text-xl font-black uppercase italic">
                            {{ explode(' ', auth()->user()->name)[0] }}
                        </h2>
                    </div>
                </a>

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
                        Getec Topografia Admin
                    </h1>
                </div>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="grid grid-cols-1 lg:grid-cols-[340px_1fr] gap-8">

            <!-- MENU -->
            <aside class="glass glow rounded-[35px] border border-white/10 p-6 h-fit">

                <div class="mb-6">
                    <h3 class="text-lg font-black uppercase italic">
                        Navegação
                    </h3>

                    <p class="text-sm text-white/60 mt-1">
                        Gerencie serviços, clientes e bases.
                    </p>
                </div>

                <!-- ALERTA -->
                @if(isset($temPendentes) && $temPendentes)

                    <a href="{{ route('admin.pendentes') }}"
                       class="flex items-center justify-between bg-gradient-to-r from-yellow-400 to-orange-500 text-[#003366] rounded-2xl px-6 py-5 mb-6 shadow-2xl border border-yellow-200/30 hover:scale-[1.02] transition-all">

                        <div>
                            <p class="text-xs uppercase tracking-[3px] font-black opacity-70">
                                Atenção
                            </p>

                            <h3 class="text-lg font-black italic uppercase">
                                Solicitações ADM
                            </h3>
                        </div>

                        <div class="animate-pulse bg-white text-orange-600 p-3 rounded-2xl shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-6 w-6"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="3"
                                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </a>

                @else

                    <a href="{{ route('admin.pendentes') }}"
                       class="menu-gradient rounded-2xl px-6 py-5 mb-6 flex items-center justify-between shadow-xl card-hover">

                        <div>
                            <p class="text-xs uppercase tracking-[3px] text-white/60 font-black">
                                Administração
                            </p>

                            <h3 class="text-lg font-black italic uppercase">
                                Solicitações ADM
                            </h3>
                        </div>

                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-6 w-6 opacity-70"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>

                @endif

                <!-- MENU ITEMS -->
                <nav class="space-y-4">

                    <a href="{{ route('admin.pastas.index') }}"
                       class="menu-gradient rounded-2xl px-6 py-5 flex items-center justify-between shadow-xl border border-white/10 card-hover">

                        <div>
                            <p class="text-xs uppercase tracking-[3px] text-white/50 font-bold">
                                Gestão
                            </p>

                            <h3 class="text-lg font-black uppercase italic">
                                Serviços
                            </h3>
                        </div>

                        <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center">
                            📁
                        </div>
                    </a>

                    <a href="{{ route('admin.clientes') }}"
                       class="menu-gradient rounded-2xl px-6 py-5 flex items-center justify-between shadow-xl border border-white/10 card-hover">

                        <div>
                            <p class="text-xs uppercase tracking-[3px] text-white/50 font-bold">
                                Gestão
                            </p>

                            <h3 class="text-lg font-black uppercase italic">
                                Clientes
                            </h3>
                        </div>

                        <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center">
                            👥
                        </div>
                    </a>

                    <a href="{{ route('admin.importacao.index') }}"
                       class="menu-gradient rounded-2xl px-6 py-5 flex items-center justify-between shadow-xl border border-white/10 card-hover">

                        <div>
                            <p class="text-xs uppercase tracking-[3px] text-white/50 font-bold">
                                Sincronização
                            </p>

                            <h3 class="text-lg font-black uppercase italic">
                                Importar
                            </h3>
                        </div>

                        <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center">
                            🔄
                        </div>
                    </a>

                    <a href="{{ route('admin.acerto.index') }}"
                       class="menu-gradient rounded-2xl px-6 py-5 flex items-center justify-between shadow-xl border border-white/10 card-hover">

                        <div>
                            <p class="text-xs uppercase tracking-[3px] text-white/50 font-bold">
                                Contratos
                            </p>

                            <h3 class="text-lg font-black uppercase italic">
                                Acerto
                            </h3>
                        </div>

                        <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center">
                            📄
                        </div>
                    </a>

                    <a href="{{ route('admin.marcos.index') }}"
                       class="menu-gradient rounded-2xl px-6 py-5 flex items-center justify-between shadow-xl border border-white/10 card-hover">

                        <div>
                            <p class="text-xs uppercase tracking-[3px] text-white/50 font-bold">
                                Gestão
                            </p>

                            <h3 class="text-lg font-black uppercase italic">
                                Relação de Marcos
                            </h3>
                        </div>

                        <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center">
                            📍
                        </div>
                    </a>

                    <a href="{{ route('admin.bases.index') }}"
                       class="menu-gradient rounded-2xl px-6 py-5 flex items-center justify-between shadow-xl border border-white/10 card-hover">

                        <div>
                            <p class="text-xs uppercase tracking-[3px] text-white/50 font-bold">
                                Gestão
                            </p>

                            <h3 class="text-lg font-black uppercase italic">
                                Localizador de Base
                            </h3>
                        </div>

                        <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center">
                            🛰️
                        </div>
                    </a>

                    {{--
                    <a href="{{ route('admin.gerador.index') }}"
                       class="menu-gradient rounded-2xl px-6 py-5 flex items-center justify-between shadow-xl border border-white/10 card-hover">

                        <div>
                            <p class="text-xs uppercase tracking-[3px] text-white/50 font-bold">
                                Ferramenta
                            </p>

                            <h3 class="text-lg font-black uppercase italic">
                                Gerador Express
                            </h3>
                        </div>

                        <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center">
                            📐
                        </div>
                    </a>
                    --}}

                    <a href="{{ route('admin.imagens.index') }}"
                       class="menu-gradient rounded-2xl px-6 py-5 flex items-center justify-between shadow-xl border border-white/10 card-hover">

                        <div>
                            <p class="text-xs uppercase tracking-[3px] text-white/50 font-bold">
                                Gestão
                            </p>

                            <h3 class="text-lg font-black uppercase italic">
                                Alterar Sistema
                            </h3>
                        </div>

                        <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center">
                            🖼️
                        </div>
                    </a>

                </nav>

                <!-- LOGOUT -->
                <div class="mt-8 pt-6 border-t border-white/10">

                    <form method="POST" action="{{ route('logout', [], false) }}">
                        @csrf

                        <button type="submit"
                                class="w-full bg-red-600 hover:bg-red-700 rounded-2xl px-6 py-4 flex items-center justify-center gap-3 uppercase font-black tracking-wider transition-all shadow-xl">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-5 w-5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>

                            Sair do Sistema
                        </button>
                    </form>

                </div>
            </aside>

            <!-- RIGHT PANEL -->
            <section class="glass glow rounded-[40px] border border-white/10 p-8 md:p-10 min-h-[700px]">

                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 mb-10">

                    <div>
                        <p class="uppercase tracking-[4px] text-sm text-white/50 font-bold mb-2">
                            Central Administrativa
                        </p>

                        <h2 class="text-4xl font-black italic uppercase leading-none">
                            Painel de Informações
                        </h2>
                    </div>

                    <div class="flex items-center gap-4">

                        <div class="bg-[#00E500] text-[#003366] px-5 py-3 rounded-2xl font-black shadow-lg uppercase text-sm">
                            Online
                        </div>

                        <div class="glass px-5 py-3 rounded-2xl border border-white/10">
                            <p class="text-xs uppercase tracking-[3px] text-white/50">
                                Usuário
                            </p>

                            <p class="font-black uppercase italic">
                                {{ auth()->user()->name }}
                            </p>
                        </div>

                    </div>
                </div>

                <!-- DASHBOARD CARDS -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div class="bg-gradient-to-br from-[#003366] to-[#004A7C] rounded-3xl p-6 border border-white/10 shadow-2xl">
                        <p class="text-sm uppercase tracking-[3px] text-white/50 mb-3">
                            Serviços
                        </p>

                        <h3 class="text-2xl font-black italic uppercase mb-6">
                            Pendentes
                        </h3>

                        <div class="flex justify-between items-end gap-4">
                            <span class="text-5xl font-black">
                                📂
                            </span>

                            <div class="text-right">
                                <p class="text-4xl font-black">{{ $totalServicosPendentes }}</p>
                                <span class="text-white/60 text-sm uppercase font-bold">
                                    aguardando
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-[#004A7C] to-[#005d9c] rounded-3xl p-6 border border-white/10 shadow-2xl">
                        <p class="text-sm uppercase tracking-[3px] text-white/50 mb-3">
                            Clientes
                        </p>

                        <h3 class="text-2xl font-black italic uppercase mb-6">
                            Cadastrados
                        </h3>

                        <div class="flex justify-between items-end gap-4">
                            <span class="text-5xl font-black">
                                👥
                            </span>

                            <div class="text-right">
                                <p class="text-4xl font-black">{{ $totalClientes }}</p>
                                <span class="text-white/60 text-sm uppercase font-bold">
                                    ativos
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-[#002244] to-[#003366] rounded-3xl p-6 border border-white/10 shadow-2xl">
                        <p class="text-sm uppercase tracking-[3px] text-white/50 mb-3">
                            Bases
                        </p>

                        <h3 class="text-2xl font-black italic uppercase mb-6">
                            Registradas
                        </h3>

                        <div class="flex justify-between items-end gap-4">
                            <span class="text-5xl font-black">
                                🛰️
                            </span>

                            <div class="text-right">
                                <p class="text-4xl font-black">{{ $totalBases }}</p>
                                <span class="text-white/60 text-sm uppercase font-bold">
                                    no sistema
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-[#003366] to-[#004A7C] rounded-3xl p-6 border border-white/10 shadow-2xl">
                        <p class="text-sm uppercase tracking-[3px] text-white/50 mb-3">
                            Marcos
                        </p>

                        <h3 class="text-2xl font-black italic uppercase mb-6">
                            Sistema
                        </h3>

                        <div class="flex justify-between items-end gap-4">
                            <span class="text-5xl font-black">
                                📌
                            </span>

                            <div class="flex items-center gap-3 text-right">
                                <div class="flex flex-col">
                                    <p class="text-2xl font-black text-[#00E500] leading-none">{{ $totalBca }}</p>
                                    <span class="text-white/60 text-[10px] uppercase font-bold">BCA</span>
                                </div>
                                <div class="w-[1px] h-6 bg-white/20"></div>
                                <div class="flex flex-col">
                                    <p class="text-2xl font-black text-[#00E500] leading-none">{{ $totalEmes }}</p>
                                    <span class="text-white/60 text-[10px] uppercase font-bold">EMES</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </section>

        </div>

    </div>

</body>
</html>