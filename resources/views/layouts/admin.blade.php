<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TopoGest - Painel Administrativo</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') . '?v=' . @filemtime(public_path('images/logo-icon.png')) }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>

<body class="bg-slate-50 text-slate-800 antialiased" x-data="{ sidebarOpen: false }">

    <!-- Mobile sidebar backdrop -->
    <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-20 bg-slate-900/50 lg:hidden" @click="sidebarOpen = false" x-cloak></div>

    <!-- SIDEBAR -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-30 w-72 bg-[#002244] text-white transition-transform duration-300 lg:translate-x-0 lg:fixed lg:inset-y-0 lg:left-0 lg:flex lg:w-72 lg:flex-col shadow-xl">
        <!-- Logo Area -->
        <div class="flex items-center justify-center h-20 px-6 border-b border-white/10 bg-[#001a33]">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                <img src="{{ asset('images/logo-icon.png') . '?v=' . @filemtime(public_path('images/logo-icon.png')) }}" alt="TopoGest" class="h-14 w-14 rounded-full object-cover bg-white">
                <div class="bg-white px-2 py-1 rounded-md shadow-sm ml-2 flex items-center justify-center"><img src="{{ asset('images/logo-text.png') . '?v=' . @filemtime(public_path('images/logo-text.png')) }}" alt="TopoGest" class="h-6 w-auto"></div>
            </a>
        </div>

        <!-- Navigation -->
        <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <p class="px-3 text-xs font-semibold text-white/40 uppercase tracking-wider mb-2 mt-4">Menu Principal</p>
            
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 text-white font-medium' : 'hover:bg-white/5 text-slate-300 hover:text-white transition-colors' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                Dashboard
            </a>
            
            <a href="{{ route('admin.importacao.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.importacao.index') ? 'bg-white/10 text-white font-medium' : 'hover:bg-white/5 text-slate-300 hover:text-white transition-colors' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                Importação (Arquivos)
            </a>

            <a href="{{ route('admin.pastas.index') ?? '#' }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.pastas.index') ? 'bg-white/10 text-white font-medium' : 'hover:bg-white/5 text-slate-300 hover:text-white transition-colors' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>
                Serviços (Pastas)
            </a>
            
            <a href="{{ route('admin.clientes') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.clientes') ? 'bg-white/10 text-white font-medium' : 'hover:bg-white/5 text-slate-300 hover:text-white transition-colors' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                Clientes
            </a>

            <a href="{{ route('admin.bases.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.bases.index') ? 'bg-white/10 text-white font-medium' : 'hover:bg-white/5 text-slate-300 hover:text-white transition-colors' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                Bases Geodésicas
            </a>
            
            <a href="{{ route('admin.marcos.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.marcos.index') ? 'bg-white/10 text-white font-medium' : 'hover:bg-white/5 text-slate-300 hover:text-white transition-colors' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" /></svg>
                Marcos
            </a>
            
            <div class="flex items-center justify-between px-3 py-2.5 rounded-lg opacity-50 cursor-not-allowed text-slate-400" title="Em desenvolvimento">
                <div class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                    Gerador Express
                </div>
                <span class="text-[9px] uppercase tracking-wider font-bold bg-white/10 px-2 py-0.5 rounded text-white/50">Em Breve</span>
            </div>
            
            <a href="{{ route('admin.acerto.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.acerto.index') ? 'bg-white/10 text-white font-medium' : 'hover:bg-white/5 text-slate-300 hover:text-white transition-colors' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Contratos / Acertos
            </a>

            <a href="{{ route('admin.imagens.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.imagens.index') ? 'bg-white/10 text-white font-medium' : 'hover:bg-white/5 text-slate-300 hover:text-white transition-colors' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                Imagens do Site
            </a>

            @if(isset($temPendentes) && $temPendentes)
            <div class="mt-6">
                <p class="px-3 text-xs font-semibold text-orange-400 uppercase tracking-wider mb-2 mt-4">Atenção</p>
                <a href="{{ route('admin.pendentes') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.pendentes') ? 'bg-white/10 text-white font-medium' : 'hover:bg-white/5 text-slate-300 hover:text-white transition-colors' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    Solicitações ADM
                </a>
            </div>
            @endif
        </div>

        <!-- User Area -->
        <div class="mt-auto border-t border-white/10 bg-[#001a33] p-4">
            <div class="flex items-center justify-between mb-4">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
                    <div class="h-10 w-10 rounded-full bg-blue-100 border border-blue-200 flex items-center justify-center text-[#003366] font-bold text-sm overflow-hidden shadow-sm shrink-0">
                        @if(auth()->user()->photo)
                            <img src="{{ asset('storage/' . auth()->user()->photo) }}" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        @endif
                    </div>
                    <div class="text-left hidden sm:block">
                        <p class="text-sm font-semibold text-white leading-none">{{ explode(' ', auth()->user()->name)[0] }}</p>
                        <p class="text-[10px] text-white/50 mt-1 uppercase tracking-wider">Perfil</p>
                    </div>
                </a>
                
                <!-- Notifications -->
                <div x-data="{ unreadCount: {{ auth()->user()->unreadNotifications->count() ?? 0 }} }"
                     x-init="if (window.Echo) { window.Echo.private('App.Models.User.{{ auth()->id() }}').listen('NovaNotificacaoEvent', (e) => { unreadCount++; }); }">
                    <a href="{{ route('notificacoes.index') }}" class="relative p-2 text-white/50 hover:text-white transition-colors rounded-full hover:bg-white/10 flex">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        
                        <template x-if="unreadCount > 0">
                            <span class="absolute top-1 right-1 flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                            </span>
                        </template>
                    </a>
                </div>
            </div>

            <form method="POST" action="{{ route('logout', [], false) }}">
                @csrf
                <button type="submit" class="flex w-full items-center justify-center gap-2 px-3 py-2 rounded-lg bg-white/5 hover:bg-red-500/20 text-slate-300 hover:text-red-400 transition-colors text-sm font-medium border border-white/5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Sair do Sistema</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="flex-1 flex flex-col min-w-0 lg:ml-72 min-h-screen">
        
        <!-- MOBILE HEADER -->
        <header class="lg:hidden bg-slate-50/95 backdrop-blur-md border-b border-slate-200 h-16 flex items-center justify-between px-4 sticky top-0 z-40">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="text-slate-600 hover:text-[#002244] focus:outline-none p-2 -ml-2 rounded-md">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
                <img src="{{ asset('images/logo-icon.png') . '?v=' . @filemtime(public_path('images/logo-icon.png')) }}" alt="TopoGest" class="h-8 w-8 rounded-full bg-white object-cover shadow-sm">
            </div>
            
            <a href="{{ route('profile.edit') }}" class="h-8 w-8 rounded-full bg-blue-100 border border-blue-200 flex items-center justify-center text-[#003366] font-bold text-xs overflow-hidden shadow-sm">
                @if(auth()->user()->photo)
                    <img src="{{ asset('storage/' . auth()->user()->photo) }}" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                @endif
            </a>
        </header>

        <!-- PAGE CONTENT -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-transparent p-4 sm:p-6 lg:p-8">
    <!-- BACKGROUND -->
    <div class="fixed inset-0 z-0 pointer-events-none opacity-30 ">
        <img src="{{ asset('images/background-topo.jpg') . '?v=' . @filemtime(public_path('images/background-topo.jpg')) }}" class="w-full h-full object-cover " alt="Background">
    </div>
    <div class="relative z-10">
        @yield('content')
    </div>
</main>

    </div>

    @stack('scripts')
</body>
</html>






