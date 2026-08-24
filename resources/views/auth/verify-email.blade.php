<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação de Email - TopoGest</title>

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
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') . '?v=' . @filemtime(public_path('images/logo-icon.png')) }}">
</head>

<body class="min-h-screen bg-slate-200 overflow-hidden">

    <!-- Background -->
    <div class="fixed inset-0 z-0">
        <div class="absolute inset-0 bg-cover bg-center bg-fixed"
             style="background-image: url('{{ asset('images/background-topo.jpg') . '?v=' . @filemtime(public_path('images/background-topo.jpg')) }}');">
        </div>

        <div class="absolute inset-0 bg-[#001B2E]/50 backdrop-blur-[2px]"></div>
    </div>

    <!-- Glow -->
    <div class="fixed top-[-120px] left-[-120px] w-[320px] h-[320px] bg-cyan-400/20 rounded-full blur-3xl z-0"></div>
    <div class="fixed bottom-[-120px] right-[-120px] w-[320px] h-[320px] bg-blue-500/20 rounded-full blur-3xl z-0"></div>

    <div class="relative z-10 min-h-screen flex flex-col px-4 sm:px-6 lg:px-12 py-6">

        <!-- Header -->
        <header class="w-full max-w-7xl mx-auto flex items-start justify-between mb-10">

            <!-- Logo -->
            <a href="{{ route('welcome') }}"
               class="group flex items-center gap-4 transition-all duration-300 hover:scale-[1.02]">

                <div class="relative flex items-center justify-center w-20 h-20 rounded-2xl bg-white shadow-2xl transition group-hover:scale-105">
                    <img src="{{ asset('images/logo-icon.png') . '?v=' . @filemtime(public_path('images/logo-icon.png')) }}"
                         alt="TopoGest"
                         class="w-full h-full object-contain p-2">
                </div>

                <div class="relative flex items-center h-14 px-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                    <img src="{{ asset('images/logo-text.png') . '?v=' . @filemtime(public_path('images/logo-text.png')) }}"
                         alt="TopoGest"
                         class="relative z-10 h-8 w-auto">
                </div>
            </a>

            <!-- Logout -->
            <form method="POST" action="{{ route('logout', [], false) }}">
                @csrf

                <button type="submit"
                        class="bg-red-600 hover:bg-red-700
                               text-white font-black uppercase tracking-wide
                               px-8 py-3 rounded-2xl shadow-2xl
                               border border-white/10
                               transition-all duration-300
                               hover:-translate-y-1
                               active:translate-y-0
                               text-lg">

                    Sair
                </button>
            </form>
        </header>

        <!-- Conteúdo -->
        <main class="flex-1 flex items-center justify-center">

            <div class="w-full max-w-2xl">

                <div class="relative overflow-hidden rounded-[40px]
                            bg-white/92 backdrop-blur-2xl
                            border border-white/40
                            shadow-[0_25px_80px_rgba(0,0,0,0.35)]">

                    <!-- Linha Superior -->
                    <div class="absolute inset-x-0 top-0 h-2 bg-gradient-to-r from-cyan-400 via-blue-500 to-[#003366]"></div>

                    <div class="p-8 md:p-12">

                        <!-- Ícone -->
                        <div class="flex justify-center mb-6">

                            <div class="w-24 h-24 rounded-full
                                        bg-[#003366]
                                        flex items-center justify-center
                                        shadow-2xl border-4 border-white">

                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-12 h-12 text-white"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8m-18 8h18
                                             a2 2 0 002-2V8a2 2 0 00-2-2H3a2 2 0 00-2 2v6
                                             a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Título -->
                        <div class="text-center mb-8">

                            <h1 class="text-3xl md:text-4xl font-black text-[#003366] uppercase italic tracking-wide mb-4">
                                Verifique seu Email
                            </h1>

                            <p class="text-slate-600 text-sm md:text-base leading-relaxed max-w-xl mx-auto">
                                Obrigado por se cadastrar na plataforma TopoGest.
                                Antes de continuar, confirme seu endereço de e-mail clicando
                                no link que enviamos para sua caixa de entrada.
                            </p>
                        </div>

                        <!-- Success -->
                        @if (session('status') == 'verification-link-sent')

                            <div class="mb-8 rounded-2xl overflow-hidden border border-green-300 shadow-lg">

                                <div class="bg-green-600 text-white px-6 py-4 flex items-center gap-3">

                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="w-6 h-6 flex-shrink-0"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M5 13l4 4L19 7"/>
                                    </svg>

                                    <div class="font-bold text-sm md:text-base">
                                        Um novo link de verificação foi enviado para o seu e-mail.
                                    </div>
                                </div>
                            </div>

                        @endif

                        <!-- Ações -->
                        <div class="flex flex-col md:flex-row gap-5 items-center justify-center">

                            <!-- Reenviar -->
                            <form method="POST"
                                  action="{{ route('verification.send') }}"
                                  class="w-full md:w-auto">

                                @csrf

                                <button type="submit"
                                        class="w-full md:w-auto
                                               bg-[#003366]
                                               hover:bg-[#00264d]
                                               text-white
                                               py-4 px-8 rounded-2xl
                                               text-lg md:text-xl
                                               font-black uppercase tracking-wide
                                               shadow-[0_15px_35px_rgba(0,0,0,0.35)]
                                               border border-white/10
                                               transition-all duration-300
                                               hover:-translate-y-1
                                               active:translate-y-0">

                                    Reenviar Email
                                </button>
                            </form>

                            <!-- Logout -->
                            <form method="POST"
                                  action="{{ route('logout', [], false) }}"
                                  class="w-full md:w-auto">

                                @csrf

                                <button type="submit"
                                        class="w-full md:w-auto
                                               bg-red-600 hover:bg-red-700
                                               text-white
                                               py-4 px-8 rounded-2xl
                                               text-lg md:text-xl
                                               font-black uppercase tracking-wide
                                               shadow-xl
                                               transition-all duration-300
                                               hover:-translate-y-1
                                               active:translate-y-0">

                                    Sair da Conta
                                </button>
                            </form>
                        </div>

                        <!-- Dica -->
                        <div class="mt-10 bg-slate-100 border border-slate-200 rounded-2xl p-5">

                            <div class="flex items-start gap-4">

                                <div class="text-2xl">
                                    💡
                                </div>

                                <div>
                                    <h3 class="font-black text-[#003366] mb-1 uppercase text-sm tracking-wide">
                                        Não encontrou o email?
                                    </h3>

                                    <p class="text-slate-600 text-sm leading-relaxed">
                                        Verifique sua caixa de spam, lixo eletrônico ou promoções.
                                        Caso ainda não tenha recebido, clique em
                                        <strong>"Reenviar Email"</strong>.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center mt-8 text-white/80 text-sm font-medium">
                    © {{ date('Y') }} TopoGest — Todos os direitos reservados
                </div>
            </div>
        </main>
    </div>

</body>
</html>