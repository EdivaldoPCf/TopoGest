<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - TopoGest</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #003366;
            border-radius: 999px;
        }

        ::selection {
            background: #003366;
            color: white;
        }
    </style>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') . '?v=' . @filemtime(public_path('images/logo-icon.png')) }}">
</head>

<body class="bg-gray-100 min-h-screen overflow-x-hidden overflow-y-auto">

    <!-- Background -->
    <div class="fixed inset-0 z-0">
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat bg-fixed"
             style="background-image: url('{{ asset('images/background-topo.jpg') . '?v=' . @filemtime(public_path('images/background-topo.jpg')) }}');">
        </div>

        <div class="absolute inset-0 bg-[#001B2E]/80 backdrop-blur-[2px]"></div>
    </div>

    <!-- Container -->
    <div class="relative z-10 min-h-screen flex flex-col">

        <!-- Header -->
        <header class="w-full px-6 md:px-12 py-6 relative z-20">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <!-- Logo -->
                <a href="{{ route('welcome') }}" class="group flex items-center gap-3 transition-transform duration-300 hover:scale-105">
                    <img src="{{ asset('images/logo-icon.png') . '?v=' . @filemtime(public_path('images/logo-icon.png')) }}" alt="TopoGest" class="h-10 sm:h-12 w-auto object-contain">
                    <img src="{{ asset('images/logo-text.png') . '?v=' . @filemtime(public_path('images/logo-text.png')) }}" alt="TopoGest" class="h-7 sm:h-8 w-auto object-contain transition-opacity duration-300 group-hover:opacity-80">
                </a>
                
                <!-- Voltar -->
                <button onclick="history.back()" class="hidden md:flex items-center gap-2 text-white/90 hover:text-white border border-white/20 bg-white/5 hover:bg-white/10 px-5 py-2.5 rounded-xl font-medium transition-colors focus:ring-2 focus:ring-white/50 outline-none text-sm shadow-sm backdrop-blur-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                    Voltar
                </button>
            </div>
        </header>

        <!-- Main -->
        <main class="flex-1 flex flex-col md:flex-row items-center justify-center px-4 md:px-12 pb-10 pt-4 md:pt-0 relative z-20 w-full max-w-7xl mx-auto gap-12 lg:gap-24">
            
            <!-- Texto lateral -->
            <div class="w-full md:w-1/2 flex flex-col text-white mb-8 md:mb-0">
                <span class="uppercase tracking-[0.2em] text-sm font-semibold text-blue-300 mb-4 flex items-center gap-2">
                    <span class="w-8 h-px bg-blue-300/50"></span> Plataforma Profissional
                </span>
                
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black leading-[1.1] mb-6 tracking-tight">
                    Bem-vindo ao <br class="hidden sm:block">
                    <span class="text-[#34d399]">TopoGest</span>
                </h1>
                
                <p class="text-lg sm:text-xl leading-relaxed text-white/80 max-w-lg">
                    Gerencie serviços, clientes, solicitações administrativas e acompanhamento operacional com uma interface moderna, rápida e segura.
                </p>
            </div>

            <!-- Card Login -->
            <div class="w-full md:w-1/2 max-w-md mx-auto">
                <div class="bg-[#001B2E]/60 backdrop-blur-xl border border-white/10 shadow-[0_8px_30px_rgb(0,0,0,0.4)] rounded-3xl p-6 sm:p-10 relative overflow-hidden">
                    
                    <!-- Detalhe luminoso no topo do card -->
                    <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-[#004A7C] to-transparent opacity-80"></div>

                    <!-- Mobile voltar -->
                    <button onclick="history.back()" class="md:hidden mb-6 flex items-center gap-2 text-white/70 hover:text-white font-medium text-sm transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        Voltar
                    </button>

                    <!-- Top -->
                    <div class="text-center mb-8">
                        <h2 class="text-[28px] sm:text-[32px] font-bold text-white tracking-tight mb-2">ENTRAR</h2>
                        <p class="text-white/60 text-sm">
                            Faça login para acessar o painel administrativo.
                        </p>
                    </div>

                    <!-- Erros -->
                    @if ($errors->any())
                        <div class="mb-6 bg-red-500/10 border border-red-500/30 text-red-200 rounded-xl px-4 py-3 text-sm shadow-sm">
                            <div class="flex gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                <div>
                                    <span class="font-bold block mb-1">Erro de autenticação</span>
                                    Usuário ou senha inválidos.
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Form -->
                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf
                        
                        <!-- Login -->
                        <div class="space-y-1.5">
                            <label for="login_input" class="block text-xs font-bold uppercase tracking-wider text-white/80 ml-1">
                                Email / CPF / CNPJ
                            </label>
                            <div class="relative">
                                <input type="text" name="email" id="login_input" required autofocus autocomplete="username" value="{{ old('email') }}" placeholder="Digite seu email ou documento" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3.5 text-base text-white placeholder-white/30 outline-none transition-all focus:ring-2 focus:ring-[#004A7C] focus:border-[#004A7C] hover:bg-white/10 h-[52px]">
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 text-white/30 pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="space-y-1.5">
                            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-white/80 ml-1">
                                Senha
                            </label>
                            <div class="relative">
                                <input type="password" name="password" id="password" required autocomplete="current-password" placeholder="Digite sua senha" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3.5 text-base text-white placeholder-white/30 outline-none transition-all focus:ring-2 focus:ring-[#004A7C] focus:border-[#004A7C] hover:bg-white/10 h-[52px] pr-12">
                                <button type="button" id="togglePassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/40 hover:text-white transition-colors focus:outline-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" id="eyeIcon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 0s-3-7-9-7-9 7-9 7 3 7 9 7 9-7 9-7z" /></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Remember -->
                        <div class="flex items-center justify-between pt-1 pb-2">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" name="remember" class="w-4 h-4 rounded border-white/20 bg-white/5 text-blue-400 focus:ring-blue-400 focus:ring-offset-0 transition-colors">
                                <span class="text-white/70 text-sm font-medium group-hover:text-white transition-colors">
                                    Lembrar acesso
                                </span>
                            </label>
                            <a href="{{ route('password.request') }}" class="text-sm font-medium text-blue-400 hover:text-blue-300 transition-colors hover:underline focus:outline-none focus:underline">
                                Esqueci minha senha
                            </a>
                        </div>

                        <!-- Actions -->
                        <div class="pt-2 space-y-3">
                            <button type="submit" class="w-full bg-[#004A7C] hover:bg-[#005B99] text-white h-[52px] rounded-xl font-bold uppercase tracking-wide transition-all focus:ring-2 focus:ring-white/50 focus:outline-none hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0">
                                Entrar
                            </button>
                            <a href="{{ route('register') }}" class="w-full flex items-center justify-center bg-transparent hover:bg-white/5 border border-white/20 text-white h-[52px] rounded-xl font-bold uppercase tracking-wide transition-all focus:ring-2 focus:ring-white/50 focus:outline-none">
                                Criar Conta
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>

    </div>

    <!-- Scripts -->
    <script>
        const loginInput = document.getElementById('login_input');

        loginInput.addEventListener('input', (e) => {
            let v = e.target.value;

            // Se tiver letras ou @, considera email
            if (/[a-zA-Z@]/.test(v)) return;

            // Apenas números
            let numeric = v.replace(/\D/g, '');

            if (numeric.length > 0) {

                if (numeric.length <= 11) {

                    // CPF
                    v = numeric.replace(/(\d{3})(\d)/, '$1.$2');
                    v = v.replace(/(\d{3})(\d)/, '$1.$2');
                    v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');

                } else {

                    // CNPJ
                    v = numeric.slice(0, 14);
                    v = v.replace(/^(\d{2})(\d)/, '$1.$2');
                    v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                    v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
                    v = v.replace(/(\d{4})(\d)/, '$1-$2');
                }

                e.target.value = v;
            }
        });

        // Mostrar/Ocultar senha
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', () => {

            const type = passwordInput.getAttribute('type') === 'password'
                ? 'text'
                : 'password';

            passwordInput.setAttribute('type', type);
        });
    </script>

</body>
</html>