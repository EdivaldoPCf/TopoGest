<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - Getec Topografia</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        ::selection{
            background: #003366;
            color: white;
        }

        ::-webkit-scrollbar{
            width: 10px;
        }

        ::-webkit-scrollbar-thumb{
            background: #003366;
            border-radius: 999px;
        }
    </style>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">
</head>

<body class="min-h-screen bg-slate-200 overflow-hidden">

    <!-- Background -->
    <div class="fixed inset-0 z-0">
        <div class="absolute inset-0 bg-cover bg-center bg-fixed"
             style="background-image: url('{{ asset('images/background-topo.jpg') }}');">
        </div>

        <div class="absolute inset-0 bg-[#001B2E]/50 backdrop-blur-[2px]"></div>
    </div>

    <!-- Glow Effects -->
    <div class="fixed top-[-120px] left-[-120px] w-[320px] h-[320px] bg-cyan-400/20 rounded-full blur-3xl z-0"></div>
    <div class="fixed bottom-[-120px] right-[-120px] w-[320px] h-[320px] bg-blue-500/20 rounded-full blur-3xl z-0"></div>

    <div class="relative z-10 min-h-screen flex flex-col px-4 sm:px-6 lg:px-12 py-6">

        <!-- Header -->
        <header class="w-full max-w-7xl mx-auto flex items-start justify-between mb-10">

            <!-- Logo -->
            <a href="{{ route('welcome') }}"
               class="group flex items-center gap-4 transition-all duration-300 hover:scale-[1.02]">

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

            <!-- Voltar -->
            <a href="{{ route('login') }}"
               class="bg-[#003366] hover:bg-[#00264d]
                      text-white font-black uppercase tracking-wide
                      px-8 py-3 rounded-2xl shadow-2xl
                      border border-white/10
                      transition-all duration-300
                      hover:-translate-y-1
                      active:translate-y-0
                      text-lg md:text-xl">

                Voltar
            </a>
        </header>

        <!-- Conteúdo -->
        <main class="flex-1 flex items-center justify-center">

            <div class="w-full max-w-2xl">

                <div class="relative overflow-hidden rounded-[40px]
                            bg-white/92 backdrop-blur-2xl
                            border border-white/40
                            shadow-[0_25px_80px_rgba(0,0,0,0.35)]">

                    <!-- Linha Top -->
                    <div class="absolute inset-x-0 top-0 h-2 bg-gradient-to-r from-cyan-400 via-blue-500 to-[#003366]"></div>

                    <div class="p-8 md:p-12">

                        <!-- Título -->
                        <div class="text-center mb-10">

                            <div class="inline-flex items-center gap-3
                                        bg-[#003366]
                                        text-white
                                        px-7 py-3
                                        rounded-2xl
                                        shadow-xl">

                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-7 h-7"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M12 11c0-.53.21-1.04.59-1.41
                                             .37-.38.88-.59 1.41-.59h1
                                             a2 2 0 012 2v1m-5 4h.01M6 10V8
                                             a6 6 0 1112 0v2m-9 0h10a2 2 0 012 2v6
                                             a2 2 0 01-2 2H9a2 2 0 01-2-2v-6
                                             a2 2 0 012-2z"/>
                                </svg>

                                <span class="text-2xl md:text-3xl font-black uppercase tracking-wide italic">
                                    Nova Senha
                                </span>
                            </div>

                            <p class="text-slate-600 mt-5 text-sm md:text-base leading-relaxed">
                                Crie uma nova senha segura para acessar sua conta.
                            </p>
                        </div>

                        <!-- Errors -->
                        @if ($errors->any())
                            <div class="mb-8 rounded-2xl overflow-hidden border border-red-300 shadow-lg">

                                <div class="bg-red-600 text-white px-6 py-4 flex items-center gap-3">

                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="w-6 h-6 flex-shrink-0"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M12 9v2m0 4h.01m-6.938 4h13.856
                                                 c1.54 0 2.502-1.667 1.732-3L13.732 4
                                                 c-.77-1.333-2.694-1.333-3.464 0L3.34 16
                                                 c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>

                                    <div class="font-bold text-sm md:text-base">
                                        Verifique os dados informados e tente novamente.
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Form -->
                        <form method="POST"
                              action="{{ route('password.store') }}"
                              class="space-y-7">

                            @csrf

                            <!-- Token -->
                            <input type="hidden"
                                   name="token"
                                   value="{{ $request->route('token') }}">

                            <!-- Email -->
                            <div>

                                <label class="block mb-2 text-[#003366] font-black uppercase tracking-wide text-sm">
                                    Email
                                </label>

                                <input id="email"
                                       type="email"
                                       name="email"
                                       required
                                       autofocus
                                       autocomplete="username"
                                       value="{{ old('email', $request->email) }}"

                                       class="w-full h-14 px-5 rounded-2xl
                                              bg-slate-100 border border-slate-300
                                              focus:border-[#003366]
                                              focus:ring-4 focus:ring-blue-500/20
                                              outline-none transition-all
                                              text-[#003366]
                                              font-semibold shadow-inner">

                                @error('email')
                                    <p class="text-red-600 text-sm font-bold mt-2">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div>

                                <label class="block mb-2 text-[#003366] font-black uppercase tracking-wide text-sm">
                                    Nova Senha
                                </label>

                                <div class="relative">

                                    <input id="password"
                                           type="password"
                                           name="password"
                                           required
                                           autocomplete="new-password"
                                           placeholder="Digite sua nova senha"

                                           class="w-full h-14 px-5 pr-14 rounded-2xl
                                                  bg-slate-100 border border-slate-300
                                                  focus:border-[#003366]
                                                  focus:ring-4 focus:ring-blue-500/20
                                                  outline-none transition-all
                                                  text-[#003366]
                                                  font-semibold shadow-inner">

                                    <button type="button"
                                            onclick="togglePassword('password', this)"
                                            class="absolute right-4 top-1/2 -translate-y-1/2 text-[#003366] hover:opacity-70 transition">

                                        👁
                                    </button>
                                </div>

                                @error('password')
                                    <p class="text-red-600 text-sm font-bold mt-2">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div>

                                <label class="block mb-2 text-[#003366] font-black uppercase tracking-wide text-sm">
                                    Confirmar Nova Senha
                                </label>

                                <div class="relative">

                                    <input id="password_confirmation"
                                           type="password"
                                           name="password_confirmation"
                                           required
                                           autocomplete="new-password"
                                           placeholder="Confirme sua nova senha"

                                           class="w-full h-14 px-5 pr-14 rounded-2xl
                                                  bg-slate-100 border border-slate-300
                                                  focus:border-[#003366]
                                                  focus:ring-4 focus:ring-blue-500/20
                                                  outline-none transition-all
                                                  text-[#003366]
                                                  font-semibold shadow-inner">

                                    <button type="button"
                                            onclick="togglePassword('password_confirmation', this)"
                                            class="absolute right-4 top-1/2 -translate-y-1/2 text-[#003366] hover:opacity-70 transition">

                                        👁
                                    </button>
                                </div>

                                @error('password_confirmation')
                                    <p class="text-red-600 text-sm font-bold mt-2">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Botão -->
                            <div class="pt-4 flex flex-col items-center gap-5">

                                <button type="submit"
                                        class="w-full max-w-md
                                               bg-[#003366]
                                               hover:bg-[#00264d]
                                               text-white
                                               py-4 rounded-2xl
                                               text-xl md:text-2xl
                                               font-black uppercase tracking-wide
                                               shadow-[0_15px_35px_rgba(0,0,0,0.35)]
                                               border border-white/10
                                               transition-all duration-300
                                               hover:-translate-y-1
                                               active:translate-y-0">

                                    Redefinir Senha
                                </button>

                                <a href="{{ route('login') }}"
                                   class="text-[#003366]
                                          font-bold hover:underline
                                          transition text-sm md:text-base">

                                    Voltar para o login
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center mt-8 text-white/80 text-sm font-medium">
                    © {{ date('Y') }} Getec Topografia — Todos os direitos reservados
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script>
        function togglePassword(id, button) {

            const input = document.getElementById(id);

            if (input.type === 'password') {
                input.type = 'text';
                button.innerHTML = '🙈';
            } else {
                input.type = 'password';
                button.innerHTML = '👁';
            }
        }
    </script>

</body>
</html>