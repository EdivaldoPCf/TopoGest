<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Getec Topografia') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        /* Evita flicker de elementos controlados por Alpine antes de inicializar */
        [x-cloak] { display: none !important; }

        body {
            background-image: url('{{ asset('images/background-topo.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .glass-container {
            background: rgba(255, 255, 255, 0.10);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .shadow-topogest {
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.35);
        }
    </style>
</head>

<body class="font-sans antialiased text-white overflow-x-hidden">
    
    <!-- Overlay escuro -->
    <div class="fixed inset-0 bg-[#001827]/70 z-0"></div>

    <!-- Conteúdo -->
    <div class="relative z-10 min-h-screen flex flex-col">
        
        <!-- Header -->
        <header class="w-full px-6 md:px-10 py-5 flex items-center justify-between">
            
            <a href="{{ Auth::check() ? route('dashboard') : route('welcome') }}" 
               class="flex items-center gap-4 group hover:scale-[1.02] transition duration-300">
                
                <div class="relative flex items-center justify-center w-20 h-20 rounded-2xl bg-white shadow-2xl transition group-hover:scale-105">
                    <img 
                        src="{{ asset('images/logo-icon.png') }}" 
                        alt="Logo"
                        class="w-full h-full object-contain p-2"
                    >
                </div>

                <div class="relative flex items-center h-14 px-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                    <img 
                        src="{{ asset('images/logo-text.png') }}" 
                        alt="Getec Topografia"
                        class="relative z-10 h-8 w-auto"
                    >
                </div>
            </a>

            @guest
                <div class="hidden md:flex items-center gap-4">
                    <a href="{{ route('login') }}"
                       class="bg-[#003366] hover:bg-[#002244] transition px-6 py-2 rounded-xl font-bold shadow-lg">
                        Entrar
                    </a>

                    <a href="{{ route('register') }}"
                       class="bg-white text-[#003366] hover:bg-gray-100 transition px-6 py-2 rounded-xl font-bold shadow-lg">
                        Cadastro
                    </a>
                </div>
            @endguest
        </header>

        <!-- Conteúdo principal -->
        <main class="flex-1 flex items-center justify-center px-4 py-10">
            <div class="glass-container shadow-topogest rounded-[40px] w-full max-w-7xl p-6 md:p-10">
                @yield('content')
            </div>
        </main>

        <!-- Rodapé -->
        <footer class="w-full text-center text-sm text-white/70 py-5 tracking-wide">
            © {{ date('Y') }} Getec Topografia • Sistema de Gestão Topográfica
        </footer>

    </div>

</body>
</html>