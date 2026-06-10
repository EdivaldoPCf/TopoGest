<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - TopoGest</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #003366;
            border-radius: 999px;
        }

        body {
            overflow-x: hidden;
        }

        .glass-card {
            background: rgba(255,255,255,.15);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255,255,255,.2);
            box-shadow: 0 15px 40px rgba(0,0,0,.25);
        }
    </style>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') . '?v=' . @filemtime(public_path('images/logo-icon.png')) }}">
</head>

<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    <!-- BACKGROUND -->
    <div class="fixed inset-0 z-0 bg-cover bg-center bg-no-repeat"
         style="background-image: url('{{ asset('images/background-topo.jpg') . '?v=' . @filemtime(public_path('images/background-topo.jpg')) }}');">
    </div>

    <!-- OVERLAY -->
    <div class="fixed inset-0 z-0 bg-[#001B33]/70 backdrop-blur-sm"></div>

    <!-- CONTENT -->
    <div class="relative z-10 w-full max-w-md">

        <!-- LOGO -->
        <div class="flex justify-center mb-8">

            <a href="{{ route('login') }}"
               class="group flex items-center gap-3 transition-all duration-300 hover:scale-105">

                <div class="relative flex items-center justify-center w-20 h-20 rounded-2xl bg-white shadow-2xl transition group-hover:scale-105">
                    <img src="{{ asset('images/logo-icon.png') . '?v=' . @filemtime(public_path('images/logo-icon.png')) }}"
                         alt="TopoGest"
                         class="w-full h-full object-contain p-2">
                </div>

                <div class="relative flex items-center h-14 px-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                    <img src="{{ asset('images/logo-text.png') . '?v=' . @filemtime(public_path('images/logo-text.png')) }}"
                         alt="TopoGest"
                         class="relative z-10 h-8">
                </div>

            </a>

        </div>

        <!-- CARD -->
        <div class="glass-card rounded-[35px] p-8 md:p-10">

            <!-- ICON -->
            <div class="flex justify-center mb-6">

                <div class="w-20 h-20 rounded-full bg-[#003366] flex items-center justify-center shadow-2xl border border-white/10">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-10 w-10 text-white"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8m-18 8h18a2 2 0 002-2V8a2 2 0 00-2-2H3a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                    </svg>

                </div>

            </div>

            <!-- TITLE -->
            <div class="text-center mb-8">

                <h1 class="text-3xl font-black uppercase tracking-tight text-white mb-3">
                    Recuperar Senha
                </h1>

                <p class="text-white/80 text-sm md:text-base leading-relaxed">
                    Informe seu endereço de email e enviaremos um link seguro
                    para redefinir sua senha.
                </p>

            </div>

            <!-- SUCCESS -->
            @if (session('status'))

                <div class="mb-6 bg-green-500/20 border border-green-400/30 text-green-100 px-5 py-4 rounded-2xl text-sm text-center shadow-lg">

                    {{ session('status') }}

                </div>

            @endif

            <!-- FORM -->
            <form method="POST"
                  action="{{ route('password.email') }}"
                  class="space-y-6">

                @csrf

                <!-- EMAIL -->
                <div>

                    <label for="email"
                           class="block text-sm font-bold uppercase tracking-wider text-white mb-3">

                        Email de Recuperação

                    </label>

                    <div class="relative">

                        <input id="email"
                               type="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               autofocus
                               placeholder="Digite seu email"
                               class="w-full bg-white/90 text-[#003366] placeholder-[#003366]/50 rounded-2xl px-5 py-4 pr-14 outline-none border border-white/20 focus:ring-4 focus:ring-blue-400/30 transition-all shadow-inner font-semibold">

                        <div class="absolute right-5 top-4 text-[#003366]">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-6 w-6"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M16 12H8m0 0l4-4m-4 4l4 4"/>
                            </svg>

                        </div>

                    </div>

                    @error('email')

                        <div class="mt-3 bg-red-500/20 border border-red-400/30 text-red-100 px-4 py-3 rounded-xl text-sm">

                            {{ $message }}

                        </div>

                    @enderror

                </div>

                <!-- BUTTONS -->
                <div class="flex flex-col sm:flex-row gap-4 pt-2">

                    <!-- SUBMIT -->
                    <button type="submit"
                            class="flex-1 bg-[#003366] hover:bg-[#002244] text-white py-4 rounded-2xl font-black uppercase tracking-widest shadow-2xl transition-all hover:scale-[1.02] active:scale-[0.98] border border-white/10">

                        Enviar Link

                    </button>

                    <!-- BACK -->
                    <a href="{{ route('login') }}"
                       class="flex-1 bg-red-600 hover:bg-red-700 text-white py-4 rounded-2xl font-black uppercase tracking-widest shadow-2xl transition-all hover:scale-[1.02] active:scale-[0.98] text-center border border-red-800">

                        Voltar

                    </a>

                </div>

            </form>

        </div>

        <!-- FOOTER -->
        <div class="mt-6 text-center text-white/60 text-xs uppercase tracking-[0.25em]">
            TopoGest • Recuperação Segura
        </div>

    </div>

</body>
</html>