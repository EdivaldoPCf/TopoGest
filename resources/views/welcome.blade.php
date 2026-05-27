<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Getec Topografia - Início</title>

    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen overflow-hidden text-white">

    <!-- Background -->
    <div class="fixed inset-0 z-0">
        <img 
            src="{{ asset('images/background-topo.jpg') }}"
            alt="Background"
            class="w-full h-full object-cover"
        >

        <!-- Overlay -->
        <div class="absolute inset-0 bg-[#001B2E]/70 backdrop-blur-[2px]"></div>
    </div>

    <!-- Conteúdo -->
    <main class="relative z-10 min-h-screen flex flex-col justify-between px-6 py-6 md:px-12 md:py-10">

        <!-- HEADER -->
        <header class="flex items-center justify-between">

            <!-- Contato -->
            <a href="https://w.app/eudvu7"
               target="_blank"
               rel="noopener noreferrer"
               class="group">

                <div class="px-6 py-3 rounded-2xl bg-[#003366]/90 border border-white/10 shadow-2xl
                            hover:bg-[#004A7C] hover:scale-105 transition-all duration-300">

                    <span class="font-semibold tracking-wide">
                        Contato
                    </span>
                </div>
            </a>

            <!-- Ações -->
            <div class="flex items-center gap-4">

                <a href="{{ route('login') }}"
                   class="px-8 py-3 rounded-2xl bg-white/10 border border-white/10
                          backdrop-blur-md shadow-xl font-semibold
                          hover:bg-white/20 hover:-translate-y-1
                          transition-all duration-300">
                    Entrar
                </a>

                <a href="{{ route('register') }}"
                   class="px-8 py-3 rounded-2xl bg-[#004A7C]
                          shadow-2xl font-semibold
                          hover:bg-[#005B99] hover:-translate-y-1
                          transition-all duration-300">
                    Cadastro
                </a>

            </div>
        </header>

        <!-- CENTRO -->
        <section class="flex-1 flex items-center justify-center">

            <div class="relative flex flex-col items-center">

                <!-- Glow -->
                <div class="absolute w-[420px] h-[420px] bg-[#004A7C]/30 blur-3xl rounded-full"></div>

                <!-- Card -->
                <div class="relative bg-white border border-white/10
                            backdrop-blur-xl rounded-[40px]
                            px-10 py-12 shadow-[0_20px_80px_rgba(0,0,0,0.45)]">

                    <!-- Linha decorativa -->
                    <div class="absolute -bottom-4 left-1/2 -translate-x-1/2
                                w-64 h-6 bg-[#003366]/60 blur-2xl rounded-full">
                    </div>

                    <!-- Logo -->
                    <img 
                        src="{{ asset('images/logo-completa.png') }}?v={{ file_exists(public_path('images/logo-completa.png')) ? filemtime(public_path('images/logo-completa.png')) : time() }}"
                        alt="Getec Topografia"
                        class="relative z-10 w-[420px] md:w-[520px] h-auto object-contain drop-shadow-2xl"
                    >

                </div>

            </div>

        </section>

        <!-- FOOTER -->
        <footer class="flex flex-col md:flex-row items-center justify-between gap-5">

            <a href="{{ route('perguntas_frequentes') }}"
               class="w-full md:w-auto">

                <div class="bg-[#003366]/90 border border-white/10 backdrop-blur-md
                            px-8 py-4 rounded-2xl shadow-2xl
                            hover:bg-[#004A7C] hover:-translate-y-1
                            transition-all duration-300 text-center">

                    <span class="font-semibold text-lg">
                        Perguntas Frequentes
                    </span>

                </div>
            </a>

            <div class="flex flex-col md:flex-row gap-5 w-full md:w-auto">

                <a href="{{ route('quem_somos') }}">
                    <div class="bg-[#003366]/90 border border-white/10 backdrop-blur-md
                                px-10 py-4 rounded-2xl shadow-2xl
                                hover:bg-[#004A7C] hover:-translate-y-1
                                transition-all duration-300 text-center">

                        <span class="font-semibold text-lg">
                            Quem Somos
                        </span>

                    </div>
                </a>

                <a href="{{ route('sobre') }}">
                    <div class="bg-[#003366]/90 border border-white/10 backdrop-blur-md
                                px-10 py-4 rounded-2xl shadow-2xl
                                hover:bg-[#004A7C] hover:-translate-y-1
                                transition-all duration-300 text-center">

                        <span class="font-semibold text-lg">
                            Sobre
                        </span>

                    </div>
                </a>

            </div>

        </footer>

    </main>

</body>
</html>