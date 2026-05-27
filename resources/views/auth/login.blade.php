<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - Getec Topografia</title>

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
</head>

<body class="bg-gray-100 min-h-screen overflow-x-hidden overflow-y-auto">

    <!-- Background -->
    <div class="fixed inset-0 z-0">
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat bg-fixed"
             style="background-image: url('{{ asset('images/background-topo.jpg') }}');">
        </div>

        <div class="absolute inset-0 bg-[#00111f]/55 backdrop-blur-[2px]"></div>
    </div>

    <!-- Container -->
    <div class="relative z-10 min-h-screen flex flex-col">

        <!-- Header -->
        <header class="w-full px-6 md:px-12 py-6">
            <div class="max-w-7xl mx-auto flex justify-between items-center">

                <!-- Logo -->
                <a href="{{ route('welcome') }}"
                   class="group flex items-center gap-4 transition-all duration-300 hover:scale-105">

                    <div class="relative flex items-center justify-center w-20 h-20 rounded-2xl bg-white shadow-2xl transition group-hover:scale-105">
                        <img src="{{ asset('images/logo-icon.png') }}"
                             alt="Getec Topografia"
                             class="w-full h-full object-contain p-2">
                    </div>

                    <div class="relative flex items-center h-14 px-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                        <img src="{{ asset('images/logo-text.png') }}"
                             alt="Getec Topografia"
                             class="relative z-10 h-8 object-contain">
                    </div>
                </a>

                <!-- Voltar -->
                <button onclick="history.back()"
                        class="hidden md:flex items-center gap-2 bg-[#003366]/90 backdrop-blur-md text-white px-8 py-3 rounded-2xl font-black shadow-2xl border border-white/10 hover:bg-[#002244] hover:scale-105 transition-all active:scale-95">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M15 19l-7-7 7-7" />
                    </svg>

                    Voltar
                </button>

            </div>
        </header>

        <!-- Main -->
        <main class="flex-1 flex items-center justify-center px-6 md:px-12 pb-10">

            <div class="w-full max-w-6xl grid lg:grid-cols-2 gap-12 items-center">

                <!-- Texto lateral -->
                <div class="hidden lg:flex flex-col text-white">

                    <span class="uppercase tracking-[0.35em] text-sm font-semibold opacity-80 mb-4">
                        Plataforma Profissional
                    </span>

                    <h1 class="text-6xl font-black leading-none mb-6">
                        Bem-vindo ao
                        <span class="text-blue-300 italic">Getec Topografia</span>
                    </h1>

                    <p class="text-xl leading-relaxed text-white/80 max-w-xl">
                        Gerencie serviços, clientes, solicitações administrativas
                        e acompanhamento operacional com uma interface moderna,
                        rápida e segura.
                    </p>

                    <div class="mt-10 flex items-center gap-4">

                        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl px-5 py-4">
                            <div class="text-3xl font-black">100%</div>
                            <div class="text-sm uppercase tracking-wider text-white/70">
                                Controle
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl px-5 py-4">
                            <div class="text-3xl font-black">24h</div>
                            <div class="text-sm uppercase tracking-wider text-white/70">
                                Disponível
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl px-5 py-4">
                            <div class="text-3xl font-black">Seguro</div>
                            <div class="text-sm uppercase tracking-wider text-white/70">
                                Ambiente
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Card Login -->
                <div class="w-full">

                    <div class="bg-white/92 backdrop-blur-2xl border border-white/40 shadow-[0_25px_80px_rgba(0,0,0,0.35)] rounded-[40px] p-8 md:p-12">

                        <!-- Mobile voltar -->
                        <button onclick="history.back()"
                                class="md:hidden mb-6 flex items-center gap-2 text-[#003366] font-bold">
                            ← Voltar
                        </button>

                        <!-- Top -->
                        <div class="text-center mb-10">

                            <div class="flex justify-center mb-5">
                                <div class="bg-[#003366] text-white px-8 py-3 rounded-2xl shadow-xl">
                                    <h2 class="text-3xl font-black uppercase italic tracking-tight">
                                        Entrar
                                    </h2>
                                </div>
                            </div>

                            <p class="text-gray-600 text-sm md:text-base">
                                Faça login para acessar o painel administrativo.
                            </p>
                        </div>

                        <!-- Erros -->
                        @if ($errors->any())
                            <div class="mb-8 bg-red-500/10 border border-red-500/30 text-red-700 rounded-2xl px-5 py-4 shadow-sm">
                                <div class="flex items-center gap-3">

                                    <div class="bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-black">
                                        !
                                    </div>

                                    <div>
                                        <h3 class="font-black uppercase text-sm">
                                            Erro de autenticação
                                        </h3>

                                        <p class="text-sm">
                                            Usuário ou senha inválidos.
                                        </p>
                                    </div>

                                </div>
                            </div>
                        @endif

                        <!-- Form -->
                        <form method="POST"
                              action="{{ route('login') }}"
                              class="space-y-7">

                            @csrf

                            <!-- Login -->
                            <div>

                                <label for="login_input"
                                       class="flex items-center gap-2 bg-[#003366] text-white px-5 py-2 rounded-t-2xl font-black text-sm uppercase tracking-wide shadow-md w-fit">

                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="h-4 w-4"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm6 0a8 8 0 11-16 0 8 8 0 0116 0z" />
                                    </svg>

                                    Email / CPF / CNPJ
                                </label>

                                <div class="relative">

                                    <input
                                        type="text"
                                        name="email"
                                        id="login_input"
                                        required
                                        autofocus
                                        autocomplete="username"
                                        value="{{ old('email') }}"
                                        placeholder="Digite seu email ou documento"
                                        class="w-full bg-gray-100/90 border border-gray-300 rounded-b-2xl rounded-tr-2xl px-5 py-4 text-lg font-semibold text-[#003366] outline-none transition-all focus:ring-4 focus:ring-[#003366]/20 focus:border-[#003366] shadow-inner">

                                    <div class="absolute right-5 top-1/2 -translate-y-1/2 text-[#003366]/50">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="h-6 w-6"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M5.121 17.804A9 9 0 1118.364 4.56M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>

                                </div>

                            </div>

                            <!-- Password -->
                            <div>

                                <label for="password"
                                       class="flex items-center gap-2 bg-[#003366] text-white px-5 py-2 rounded-t-2xl font-black text-sm uppercase tracking-wide shadow-md w-fit">

                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="h-4 w-4"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2h-1V9a5 5 0 00-10 0v2H6a2 2 0 00-2 2v6a2 2 0 002 2zm3-10V9a3 3 0 016 0v2H9z" />
                                    </svg>

                                    Senha
                                </label>

                                <div class="relative">

                                    <input
                                        type="password"
                                        name="password"
                                        id="password"
                                        required
                                        autocomplete="current-password"
                                        placeholder="Digite sua senha"
                                        class="w-full bg-gray-100/90 border border-gray-300 rounded-b-2xl rounded-tr-2xl px-5 py-4 text-lg font-semibold text-[#003366] outline-none transition-all focus:ring-4 focus:ring-[#003366]/20 focus:border-[#003366] shadow-inner">

                                    <button type="button"
                                            id="togglePassword"
                                            class="absolute right-5 top-1/2 -translate-y-1/2 text-[#003366]/60 hover:text-[#003366] transition">

                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             id="eyeIcon"
                                             class="h-6 w-6"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 0s-3-7-9-7-9 7-9 7 3 7 9 7 9-7 9-7z" />
                                        </svg>

                                    </button>

                                </div>

                            </div>

                            <!-- Remember -->
                            <div class="flex items-center justify-between flex-wrap gap-4">

                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox"
                                           name="remember"
                                           class="w-5 h-5 rounded border-gray-300 text-[#003366] focus:ring-[#003366]">

                                    <span class="text-[#003366] font-semibold group-hover:text-[#001f3f] transition">
                                        Lembrar acesso
                                    </span>
                                </label>

                                <a href="{{ route('password.request') }}"
                                   class="text-[#003366] font-black hover:underline hover:text-blue-900 transition">
                                    Esqueci minha senha
                                </a>

                            </div>

                            <!-- Actions -->
                            <div class="pt-4 space-y-4">

                                <button type="submit"
                                        class="w-full bg-[#003366] hover:bg-[#002244] text-white py-4 rounded-2xl text-2xl font-black uppercase shadow-[0_15px_35px_rgba(0,51,102,0.35)] transition-all hover:scale-[1.02] active:scale-[0.98] border-b-4 border-[#001122]">

                                    Entrar
                                </button>

                                <a href="{{ route('register') }}"
                                   class="w-full flex items-center justify-center bg-[#0f172a] hover:bg-slate-800 text-white py-4 rounded-2xl text-xl font-black uppercase shadow-xl transition-all hover:scale-[1.02] active:scale-[0.98]">

                                    Criar Conta
                                </a>

                            </div>

                        </form>

                    </div>

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