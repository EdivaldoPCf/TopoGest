```php
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Getec Topografia</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(0, 51, 102, 0.5);
            border-radius: 999px;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen overflow-auto">

    <!-- Background -->
    <div class="fixed inset-0 z-0 bg-cover bg-center bg-no-repeat bg-fixed"
         style="background-image: url('{{ asset('images/background-topo.jpg') }}');">

        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/20 backdrop-blur-[2px]"></div>
    </div>

    <div class="relative z-10 flex flex-col min-h-screen px-4 py-5 md:px-8 md:py-6">

        <!-- HEADER -->
        <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

            <!-- Logo -->
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-4 group w-fit">

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

            <!-- Actions -->
            <div class="flex items-center gap-4 self-end lg:self-auto">

                <!-- Notifications -->
                <a href="{{ route('notificacoes.index') }}"
                   class="relative bg-[#003366]/95 backdrop-blur-md p-4 rounded-2xl text-white shadow-2xl border border-white/20 hover:bg-[#002244] hover:scale-105 transition-all duration-300 flex items-center justify-center">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-7 w-7"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>

                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <span class="absolute -top-1 -right-1 flex h-5 w-5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-5 w-5 bg-red-600 border-2 border-[#003366]"></span>
                        </span>
                    @endif
                </a>

                <!-- User Card -->
                <a href="{{ route('profile.edit') }}"
                   class="group bg-[#003366]/95 backdrop-blur-md text-white flex items-center gap-4 px-5 py-3 rounded-3xl shadow-2xl border border-white/20 hover:bg-[#002244] transition-all duration-300 hover:scale-[1.02]">

                    <div class="w-14 h-14 rounded-full overflow-hidden border-2 border-white shadow-inner bg-[#004A7C] flex items-center justify-center">
                        @if(auth()->user()->photo)
                            <img src="{{ asset('storage/' . auth()->user()->photo) }}"
                                 alt="Perfil"
                                 class="w-full h-full object-cover">
                        @else
                            <span class="font-black text-sm tracking-widest">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </span>
                        @endif
                    </div>

                    <div class="flex flex-col leading-tight">
                        <span class="text-[10px] uppercase tracking-[0.3em] opacity-70 font-semibold">
                            Bem-vindo
                        </span>

                        <span class="font-black text-lg uppercase">
                            {{ explode(' ', auth()->user()->name)[0] }}
                        </span>
                    </div>
                </a>
            </div>
        </header>

        <!-- MAIN -->
        <main class="flex-1 mt-8">

            <div class="grid grid-cols-1 xl:grid-cols-[320px_1fr] gap-8">

                <!-- LEFT NAV -->
                <aside class="rounded-[32px] border border-white/20 bg-white/10 p-6 shadow-2xl backdrop-blur-xl">

                    <div class="mb-8">
                        <p class="text-xs uppercase tracking-[0.35em] text-white/60">Menu</p>
                        <h2 class="mt-3 text-2xl font-black uppercase text-white">Acesso rápido</h2>
                    </div>

                    <nav class="space-y-4">
                        <a href="{{ route('meus.servicos') }}"
                           class="flex items-center gap-4 rounded-3xl border border-white/10 bg-[#003366]/85 px-5 py-4 text-white shadow-xl transition hover:-translate-y-0.5">
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-white text-lg">🏠</span>
                            <div>
                                <p class="text-xs uppercase tracking-[0.35em] text-white/70">Serviços</p>
                                <strong class="block text-lg font-black">Meus Imóveis</strong>
                            </div>
                        </a>

                        <a href="{{ route('documentos.recentes') }}"
                           class="flex items-center gap-4 rounded-3xl border border-white/10 bg-white/10 px-5 py-4 text-white shadow-inner transition hover:bg-white/15">
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[#004A7C] text-white text-lg">📄</span>
                            <div>
                                <p class="text-xs uppercase tracking-[0.35em] text-white/70">Documentos</p>
                                <strong class="block text-lg font-black">Recentes</strong>
                            </div>
                        </a>

                        <a href="https://w.app/chzsgz"
                           target="_blank"
                           class="flex items-center gap-4 rounded-3xl border border-white/10 bg-white/10 px-5 py-4 text-white shadow-inner transition hover:bg-white/15">
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[#0ea5e9] text-white text-lg">📅</span>
                            <div>
                                <p class="text-xs uppercase tracking-[0.35em] text-white/70">Atendimento</p>
                                <strong class="block text-lg font-black">Agendar Horário</strong>
                            </div>
                        </a>

                        <a href="https://w.app/pi3r6d"
                           target="_blank"
                           class="flex items-center gap-4 rounded-3xl border border-white/10 bg-white/10 px-5 py-4 text-white shadow-inner transition hover:bg-white/15">
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[#14b8a6] text-white text-lg">💬</span>
                            <div>
                                <p class="text-xs uppercase tracking-[0.35em] text-white/70">Suporte</p>
                                <strong class="block text-lg font-black">Contato</strong>
                            </div>
                        </a>

                        <a href="{{ route('notificacoes.index') }}"
                           class="flex items-center gap-4 rounded-3xl border border-white/10 bg-white/10 px-5 py-4 text-white shadow-inner transition hover:bg-white/15">
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[#8b5cf6] text-white text-lg">🔔</span>
                            <div>
                                <p class="text-xs uppercase tracking-[0.35em] text-white/70">Avisos</p>
                                <strong class="block text-lg font-black">Notificações</strong>
                            </div>
                        </a>
                    </nav>

                </aside>

                <!-- CONTENT AREA -->
                <section class="rounded-[32px] border border-white/20 bg-white/10 p-8 shadow-2xl backdrop-blur-xl">

                    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between mb-10">
                        <div>
                            <p class="text-xs uppercase tracking-[0.35em] text-white/60">Painel do cliente</p>
                            <h1 class="mt-3 text-4xl font-black uppercase text-white tracking-tight">Olá, {{ explode(' ', auth()->user()->name)[0] }}</h1>
                            <p class="mt-4 max-w-2xl text-white/80 leading-relaxed">Acompanhe seus serviços, documentos e suporte com um acesso rápido e organizado.</p>
                        </div>

                        <div class="rounded-3xl bg-[#003366]/85 px-6 py-4 text-white shadow-xl border border-white/10">
                            <p class="text-xs uppercase tracking-[0.35em] text-white/60">Notificações não lidas</p>
                            <p class="mt-3 text-3xl font-black">{{ auth()->user()->unreadNotifications->count() }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="rounded-[28px] border border-white/10 bg-[#002244]/85 p-6 shadow-2xl">
                            <p class="text-xs uppercase tracking-[0.35em] text-white/60">Imóveis</p>
                            <p class="mt-4 text-5xl font-black text-white">{{ number_format($totalPastas, 0, ',', '.') }}</p>
                            <p class="mt-3 text-sm text-white/70">Serviços vinculados ao seu cadastro.</p>
                        </div>

                        <div class="rounded-[28px] border border-white/10 bg-[#003366]/85 p-6 shadow-2xl">
                            <p class="text-xs uppercase tracking-[0.35em] text-white/60">Documentos</p>
                            <p class="mt-4 text-5xl font-black text-white">{{ number_format($totalArquivos, 0, ',', '.') }}</p>
                            <p class="mt-3 text-sm text-white/70">Arquivos disponíveis para consulta e download.</p>
                        </div>

                        <div class="rounded-[28px] border border-white/10 bg-[#004A7C]/85 p-6 shadow-2xl">
                            <p class="text-xs uppercase tracking-[0.35em] text-white/60">Pendências</p>
                            <p class="mt-4 text-5xl font-black text-white">{{ number_format($pendenciasAbertas, 0, ',', '.') }}</p>
                            <p class="mt-3 text-sm text-white/70">Itens aguardando atualização.</p>
                        </div>

                        <div class="rounded-[28px] border border-white/10 bg-[#0f172a]/85 p-6 shadow-2xl">
                            <p class="text-xs uppercase tracking-[0.35em] text-white/60">Perfil</p>
                            <p class="mt-4 text-5xl font-black text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</p>
                            <p class="mt-3 text-sm text-white/70">Acesse seu perfil para atualizar seus dados.</p>
                        </div>
                    </div>

                    <!-- CERTIFICAÇÃO SIGEF (INCRA) -->
                    @php
                        $pastasSigef = \App\Models\Pasta::where('cliente_id', auth()->id())
                            ->whereNotNull('codigo_sigef')
                            ->get();
                    @endphp

                    <div class="mb-8 rounded-[32px] border border-white/10 bg-white/5 p-6 backdrop-blur-xl">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                            <div class="flex items-start gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center text-3xl shadow-inner border border-white/10">
                                    🌐
                                </div>
                                <div>
                                    <div class="flex items-center gap-3">
                                        <span class="uppercase tracking-[3px] text-xs text-white/70 font-black">Certificação SIGEF</span>
                                        @if($pastasSigef->isNotEmpty())
                                            <span class="bg-green-500/20 text-green-300 text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase border border-green-500/30">Certificado</span>
                                        @else
                                            <span class="bg-white/10 text-white/60 text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase border border-white/10">Consulta Disponível</span>
                                        @endif
                                    </div>
                                    <h2 class="text-2xl font-black text-white mt-1">
                                        Consulta de Imóvel no INCRA
                                    </h2>
                                    <p class="text-sm text-white/80 mt-1 max-w-2xl">
                                        Utilize seu CPF/CNPJ abaixo para buscar as informações do seu imóvel no portal oficial do SIGEF.
                                    </p>
                                    
                                    <!-- CPF/CNPJ Info -->
                                    <div class="mt-4 flex flex-wrap items-center gap-3 bg-white/10 border border-white/10 rounded-2xl p-3 inline-flex">
                                        <span class="text-xs uppercase tracking-wider font-bold text-white/60">Seu CPF/CNPJ:</span>
                                        <span class="font-mono font-bold text-white">{{ auth()->user()->formatted_cpf }}</span>
                                        <button 
                                            onclick="copyCpfToClipboard('{{ auth()->user()->cpf }}')"
                                            class="bg-[#003366] hover:bg-blue-900 text-white px-3 py-1.5 rounded-xl font-bold uppercase text-[10px] tracking-wide transition flex items-center gap-1.5 shadow"
                                        >
                                            <span id="copyText">Copiar</span>
                                            <svg id="copyIcon" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-3">
                                <a href="https://sigef.incra.gov.br/consultar/parcelas/" 
                                   target="_blank" 
                                   class="bg-[#003366] hover:bg-blue-900 text-white px-6 py-3 rounded-2xl font-black uppercase text-xs tracking-wider shadow-lg transition flex items-center gap-2 border border-white/10">
                                    <span>Consultar Geral</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <!-- Certified Properties list -->
                        @if($pastasSigef->isNotEmpty())
                            <div class="mt-6 border-t border-white/10 pt-6">
                                <p class="text-xs uppercase tracking-wider font-bold text-white/60 mb-3">Seus Imóveis Certificados:</p>
                                <div class="space-y-3">
                                    @foreach($pastasSigef as $pastaSigef)
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white/5 border border-white/5 rounded-2xl p-4 hover:bg-white/10 transition">
                                            <div class="flex items-center gap-3">
                                                <span class="text-xl">🏡</span>
                                                <div>
                                                    <span class="font-bold text-white block">{{ $pastaSigef->nome }}</span>
                                                    <span class="text-xs text-white/55 font-mono select-all">{{ $pastaSigef->codigo_sigef }}</span>
                                                </div>
                                            </div>
                                            <a href="https://sigef.incra.gov.br/geo/parcela/detalhe/{{ $pastaSigef->codigo_sigef }}/" 
                                               target="_blank" 
                                               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl font-bold uppercase text-[10px] tracking-wider shadow transition flex items-center gap-1.5 self-start sm:self-auto">
                                                <span>Ver Parcela</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                </svg>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="rounded-[32px] border border-white/10 bg-white/5 p-6">
                        <h2 class="text-2xl font-black uppercase text-white mb-4">Resumo rápido</h2>
                        <p class="text-white/80 leading-relaxed">Use o menu à esquerda para navegar rapidamente pelos principais recursos: Meus Imóveis, Documentos Recentes, Agendamento e Suporte. Seus serviços estão organizados para facilitar o acompanhamento.</p>
                    </div>

                </section>
            </div>
        </main>

        <!-- FOOTER -->
        <footer class="flex flex-col md:flex-row items-center justify-between gap-4 pt-8">

            <div class="text-white/80 text-sm font-medium tracking-wide">
                © {{ date('Y') }} Getec Topografia — Todos os direitos reservados.
            </div>

            <form method="POST" action="{{ route('logout', [], false) }}">
                @csrf

                <button type="submit"
                        class="group flex items-center gap-3 bg-red-600/95 hover:bg-red-700 text-white px-6 py-3 rounded-2xl shadow-2xl border border-red-400/30 transition-all duration-300 hover:scale-105 font-black uppercase tracking-wider">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5 transition-transform duration-300 group-hover:-translate-x-1"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>

                    Sair do Sistema
                </button>
            </form>

        </footer>

    </div>

    <script>
        function copyCpfToClipboard(cpf) {
            navigator.clipboard.writeText(cpf).then(() => {
                const copyText = document.getElementById('copyText');
                if (copyText) {
                    const original = copyText.innerText;
                    copyText.innerText = 'Copiado!';
                    setTimeout(() => {
                        copyText.innerText = original;
                    }, 2000);
                }
            }).catch(err => {
                console.error('Erro ao copiar: ', err);
            });
        }
    </script>
</body>
</html>
```
