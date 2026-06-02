<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Getec Topografia') }}</title>

    <!-- Fonte -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Tailwind / Vite -->
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

        .glass-card {
            background: rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.15);
            box-shadow: 0 20px 45px rgba(0,0,0,0.35);
        }

        .topogest-overlay {
            background: linear-gradient(
                135deg,
                rgba(0, 25, 45, 0.88),
                rgba(0, 51, 102, 0.72)
            );
        }
    </style>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">
</head>

<body class="font-sans antialiased text-white overflow-x-hidden">

    <!-- Overlay -->
    <div class="fixed inset-0 topogest-overlay z-0"></div>

    <!-- Conteúdo -->
    <div class="relative z-10 min-h-screen flex flex-col items-center justify-center px-4 py-8">

        <!-- Logo -->
        <div class="mb-8">
            <a href="{{ route('welcome') ?? '/' }}"
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
        </div>

        <!-- Card Principal -->
        <div class="glass-card w-full sm:max-w-md rounded-[35px] px-7 py-8 md:px-10 md:py-10">

            <!-- Título opcional -->
            <div class="text-center mb-6">
                <h1 class="text-3xl font-black uppercase italic tracking-tight text-white">
                    Getec Topografia
                </h1>

                <p class="text-white/70 text-sm mt-1">
                    Sistema Inteligente de Gestão Topográfica
                </p>
            </div>

            <!-- Conteúdo -->
            {{ $slot }}
        </div>

        <!-- Rodapé -->
        <div class="mt-8 text-center text-white/60 text-xs tracking-wider uppercase">
            © {{ date('Y') }} Getec Topografia • Todos os direitos reservados
        </div>
    </div>

</body>
</html>