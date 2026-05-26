<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - TopoGest</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        ::-webkit-scrollbar{
            width: 10px;
        }

        ::-webkit-scrollbar-thumb{
            background: #003366;
            border-radius: 999px;
        }

        ::selection{
            background: #003366;
            color: white;
        }
    </style>
</head>

<body class="min-h-screen bg-slate-200 overflow-x-hidden overflow-y-auto">

    <!-- Background -->
    <div class="fixed inset-0 z-0">
        <div class="absolute inset-0 bg-cover bg-center bg-fixed"
             style="background-image: url('{{ asset('images/background-topo.jpg') }}');">
        </div>

        <div class="absolute inset-0 bg-[#001B2E]/45 backdrop-blur-[2px]"></div>
    </div>

    <!-- Glow -->
    <div class="fixed top-[-120px] left-[-120px] w-[320px] h-[320px] bg-cyan-400/20 rounded-full blur-3xl z-0"></div>
    <div class="fixed bottom-[-120px] right-[-120px] w-[320px] h-[320px] bg-blue-500/20 rounded-full blur-3xl z-0"></div>

    <div class="relative z-10 min-h-screen flex flex-col px-4 sm:px-6 lg:px-12 py-6">

        <!-- HEADER -->
        <header class="w-full max-w-7xl mx-auto flex items-start justify-between mb-10">

            <!-- Logo -->
            <a href="{{ route('welcome') }}"
               class="group flex items-center gap-4 transition-all duration-300 hover:scale-[1.02]">

                <div class="relative flex items-center justify-center w-20 h-20 rounded-2xl bg-white shadow-2xl transition group-hover:scale-105">
                    <img src="{{ asset('images/logo-icon.png') }}"
                         alt="TopoGest"
                         class="w-full h-full object-contain p-2">
                </div>

                <div class="relative flex items-center h-14 px-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                    <img src="{{ asset('images/logo-text.png') }}"
                         alt="TopoGest"
                         class="relative z-10 h-8 w-auto">
                </div>
            </a>

            <!-- Voltar -->
            <button onclick="history.back()"
                    class="bg-[#003366] hover:bg-[#00264d] text-white font-black uppercase tracking-wide
                           px-8 py-3 rounded-2xl shadow-2xl border border-white/10
                           transition-all duration-300 hover:-translate-y-1 active:translate-y-0
                           text-lg md:text-xl">
                Voltar
            </button>
        </header>

        <!-- CARD -->
        <main class="flex-1 flex items-center justify-center">

            <div class="w-full max-w-3xl">

                <div class="relative overflow-hidden rounded-[40px]
                            bg-white/92 backdrop-blur-2xl
                            border border-white/40
                            shadow-[0_25px_80px_rgba(0,0,0,0.35)]">

                    <!-- Top Gradient -->
                    <div class="absolute inset-x-0 top-0 h-2 bg-gradient-to-r from-cyan-400 via-blue-500 to-[#003366]"></div>

                    <div class="p-7 md:p-12">

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
                                          d="M18 9v3m0 0v3m0-3h3m-3 0h-3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>

                                <span class="text-2xl md:text-3xl font-black uppercase tracking-wide italic">
                                    Cadastro
                                </span>
                            </div>

                            <p class="text-slate-600 mt-5 text-sm md:text-base">
                                Crie sua conta na plataforma TopoGest
                            </p>
                        </div>

                        <!-- ALERTAS -->
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

                                        @if($errors->has('email'))
                                            {{ $errors->first('email') == 'The email field format is invalid.'
                                                ? 'Erro: formato de e-mail inválido.'
                                                : 'Erro: e-mail já cadastrado.' }}

                                        @elseif($errors->has('cpf'))
                                            Erro: CPF/CNPJ inválido ou já cadastrado.

                                        @elseif($errors->has('password'))
                                            {{ $errors->first('password') }}

                                        @else
                                            Verifique os dados informados.
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- FORM -->
                        <form method="POST"
                              action="{{ route('register') }}"
                              class="space-y-7">

                            @csrf

                            <!-- Nome -->
                            <div>
                                <label class="block mb-2 text-[#003366] font-black uppercase tracking-wide text-sm">
                                    Nome Completo / Razão Social
                                </label>

                                <input type="text"
                                       name="name"
                                       required
                                       autofocus
                                       value="{{ old('name') }}"
                                       placeholder="Digite seu nome completo"

                                       class="w-full h-14 px-5 rounded-2xl
                                              bg-slate-100/90
                                              border border-slate-300
                                              focus:border-[#003366]
                                              focus:ring-4 focus:ring-blue-500/20
                                              outline-none transition-all
                                              text-[#003366]
                                              font-semibold
                                              shadow-inner">
                            </div>

                            <!-- CPF + Tipo -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                <!-- Documento -->
                                <div>
                                    <label class="block mb-2 text-[#003366] font-black uppercase tracking-wide text-sm">
                                        CPF / CNPJ
                                    </label>

                                    <input type="text"
                                           id="documento"
                                           name="cpf"
                                           required
                                           maxlength="18"
                                           value="{{ old('cpf') }}"
                                           placeholder="000.000.000-00"

                                           class="w-full h-14 px-5 rounded-2xl
                                                  {{ $errors->has('cpf') ? 'bg-red-100 border-red-400' : 'bg-slate-100 border-slate-300' }}
                                                  border
                                                  focus:border-[#003366]
                                                  focus:ring-4 focus:ring-blue-500/20
                                                  outline-none transition-all
                                                  text-[#003366]
                                                  font-semibold
                                                  shadow-inner">
                                </div>

                                <!-- Tipo -->
                                <div>
                                    <label class="block mb-2 text-[#003366] font-black uppercase tracking-wide text-sm">
                                        Tipo de Conta
                                    </label>

                                    <div class="relative">

                                        <select name="role"
                                                class="appearance-none w-full h-14 px-5 rounded-2xl
                                                       bg-slate-100 border border-slate-300
                                                       focus:border-[#003366]
                                                       focus:ring-4 focus:ring-blue-500/20
                                                       outline-none transition-all
                                                       text-[#003366]
                                                       font-semibold
                                                       shadow-inner cursor-pointer">

                                            <option value="cliente">
                                                Cliente
                                            </option>

                                            <option value="admin">
                                                Administrador (Equipe Técnica)
                                            </option>
                                        </select>

                                        <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-[#003366]">
                                            ▼
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block mb-2 text-[#003366] font-black uppercase tracking-wide text-sm">
                                    Email
                                </label>

                                <input type="email"
                                       name="email"
                                       required
                                       value="{{ old('email') }}"
                                       placeholder="exemplo@email.com"

                                       class="w-full h-14 px-5 rounded-2xl
                                              {{ $errors->has('email') ? 'bg-red-100 border-red-400' : 'bg-slate-100 border-slate-300' }}
                                              border
                                              focus:border-[#003366]
                                              focus:ring-4 focus:ring-blue-500/20
                                              outline-none transition-all
                                              text-[#003366]
                                              font-semibold
                                              shadow-inner">
                            </div>

                            <!-- Telefone -->
                            <div>
                                <label class="block mb-2 text-[#003366] font-black uppercase tracking-wide text-sm">
                                    Celular (WhatsApp)
                                </label>

                                <input type="text"
                                       id="phone"
                                       name="phone"
                                       required
                                       maxlength="16"
                                       value="{{ old('phone') }}"
                                       placeholder="(00) 00000-0000"

                                       class="w-full h-14 px-5 rounded-2xl
                                              bg-slate-100 border border-slate-300
                                              focus:border-[#003366]
                                              focus:ring-4 focus:ring-blue-500/20
                                              outline-none transition-all
                                              text-[#003366]
                                              font-semibold
                                              shadow-inner">
                            </div>

                            <!-- Senhas -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                <div class="relative">
                                    <label class="block mb-2 text-[#003366] font-black uppercase tracking-wide text-sm">
                                        Senha
                                    </label>

                                    <input type="password"
                                           id="password"
                                           name="password"
                                           required
                                           placeholder="********"

                                           class="w-full h-14 pr-14 pl-5 rounded-2xl
                                                  bg-slate-100 border border-slate-300
                                                  focus:border-[#003366]
                                                  focus:ring-4 focus:ring-blue-500/20
                                                  outline-none transition-all
                                                  text-[#003366]
                                                  font-semibold
                                                  shadow-inner">

                                    <button type="button"
                                            id="togglePassword"
                                            class="absolute top-1/2 right-4 -translate-y-1/2 text-slate-500 hover:text-[#003366] transition"
                                            aria-label="Mostrar senha">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.27 2.943 9.542 7-1.272 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="relative">
                                    <label class="block mb-2 text-[#003366] font-black uppercase tracking-wide text-sm">
                                        Confirmar Senha
                                    </label>

                                    <input type="password"
                                           id="password_confirmation"
                                           name="password_confirmation"
                                           required
                                           placeholder="********"

                                           class="w-full h-14 pr-14 pl-5 rounded-2xl
                                                  bg-slate-100 border border-slate-300
                                                  focus:border-[#003366]
                                                  focus:ring-4 focus:ring-blue-500/20
                                                  outline-none transition-all
                                                  text-[#003366]
                                                  font-semibold
                                                  shadow-inner">

                                    <button type="button"
                                            id="togglePasswordConfirmation"
                                            class="absolute top-1/2 right-4 -translate-y-1/2 text-slate-500 hover:text-[#003366] transition"
                                            aria-label="Mostrar confirmação de senha">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.27 2.943 9.542 7-1.272 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- BOTÕES -->
                            <div class="pt-6 flex flex-col items-center gap-5">

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

                                    Criar Conta
                                </button>

                                <a href="{{ route('login') }}"
                                   class="w-full max-w-md
                                          bg-[#001F3F]
                                          hover:bg-[#003366]
                                          text-white
                                          py-3 rounded-2xl
                                          text-lg
                                          font-black uppercase tracking-wide
                                          text-center
                                          shadow-xl
                                          transition-all duration-300">

                                    Já tenho conta
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Rodapé -->
                <div class="text-center mt-8 text-white/80 text-sm font-medium">
                    © {{ date('Y') }} TopoGest — Todos os direitos reservados
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script>
        const docInput = document.getElementById('documento');
        const phoneInput = document.getElementById('phone');

        // CPF / CNPJ
        docInput.addEventListener('input', (e) => {

            let v = e.target.value.replace(/\D/g, '');

            if (v.length <= 11) {

                v = v.replace(/(\d{3})(\d)/, '$1.$2');
                v = v.replace(/(\d{3})(\d)/, '$1.$2');
                v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');

            } else {

                v = v.slice(0, 14);

                v = v.replace(/^(\d{2})(\d)/, '$1.$2');
                v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
                v = v.replace(/(\d{4})(\d)/, '$1-$2');
            }

            e.target.value = v;
        });

        // Telefone
        phoneInput.addEventListener('input', (e) => {

            let v = e.target.value.replace(/\D/g, '');

            if (v.length > 11) {
                v = v.slice(0, 11);
            }

            v = v.replace(/^(\d{2})(\d)/g, '($1) $2');
            v = v.replace(/(\d)(\d{4})$/, '$1-$2');

            e.target.value = v;
        });

        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const togglePasswordConfirmation = document.getElementById('togglePasswordConfirmation');
        const passwordConfirmationInput = document.getElementById('password_confirmation');

        const setupPasswordToggle = (toggle, input, label) => {
            if (toggle && input) {
                toggle.addEventListener('click', () => {
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    toggle.setAttribute('aria-label', type === 'password' ? `Mostrar ${label}` : `Ocultar ${label}`);
                });
            }
        };

        setupPasswordToggle(togglePassword, passwordInput, 'senha');
        setupPasswordToggle(togglePasswordConfirmation, passwordConfirmationInput, 'confirmação de senha');
    </script>
</body>
</html>