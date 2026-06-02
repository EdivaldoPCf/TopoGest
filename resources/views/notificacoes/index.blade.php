<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificações - Getec Topografia</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        [x-cloak] { display: none !important; }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.05);
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.18);
            border-radius: 10px;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">
</head>

<body class="min-h-screen antialiased p-4 md:p-8 overflow-hidden bg-[#0b1724]">

    <!-- Background -->
    <div class="fixed inset-0 z-0 bg-cover bg-center bg-no-repeat"
         style="background-image: url('{{ asset('images/background-topo.jpg') }}');">
    </div>

    <div class="fixed inset-0 bg-[#00111f]/70 backdrop-blur-[3px] z-0"></div>

    <div x-data="notificacoesApp()"
         class="relative z-10 w-full max-w-7xl mx-auto h-[calc(100vh-2rem)] flex flex-col">

        <!-- Header -->
        <div class="flex flex-col lg:flex-row justify-between items-center gap-6 mb-6">

            <!-- Logo -->
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-4 hover:scale-[1.02] transition-all duration-300">

                <div class="relative flex items-center justify-center w-20 h-20 rounded-2xl bg-white shadow-2xl transition group-hover:scale-105">
                    <img src="{{ asset('images/logo-icon.png') }}"
                         alt="Getec Topografia"
                         class="w-full h-full object-contain p-2">
                </div>

                <div class="relative flex items-center h-12 px-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                    <img src="{{ asset('images/logo-text.png') }}"
                         alt="Getec Topografia"
                         class="relative z-10 h-7 w-auto">
                </div>

            </a>

            <!-- Title -->
            <div class="bg-[#003366]/90 backdrop-blur-md text-white px-8 py-3 rounded-2xl shadow-2xl border border-white/10">
                <h1 class="text-xl md:text-2xl font-black italic uppercase tracking-tight">
                    Central de Notificações
                </h1>
            </div>

            <!-- User -->
            <div class="flex items-center gap-4">

                <a href="{{ route('profile.edit') }}"
                   class="bg-[#003366]/90 backdrop-blur-md text-white px-5 py-2 rounded-2xl flex items-center gap-3 border border-white/10 shadow-xl hover:bg-[#002244] transition">

                    <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-white bg-[#004A7C] flex items-center justify-center font-black uppercase">

                        @if(auth()->user()->photo)
                            <img src="{{ asset('storage/' . auth()->user()->photo) }}"
                                 class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        @endif

                    </div>

                    <div class="leading-tight hidden sm:block">
                        <span class="block text-[10px] uppercase tracking-widest opacity-60 font-bold">
                            Usuário
                        </span>

                        <span class="font-black uppercase">
                            {{ explode(' ', auth()->user()->name)[0] }}
                        </span>
                    </div>

                </a>

            </div>

        </div>

        <!-- Main Content -->
        <div class="flex-1 flex overflow-hidden rounded-[35px] border border-white/10 shadow-2xl backdrop-blur-xl bg-white/10">

            <!-- Sidebar -->
            <div class="w-full md:w-[360px] bg-white/85 border-r border-gray-200 flex flex-col">

                <!-- Sidebar Header -->
                <div class="bg-[#003366] text-white px-6 py-5 border-b border-white/10">
                    <div class="flex items-center justify-between">

                        <div>
                            <h2 class="text-2xl font-black italic uppercase tracking-tight">
                                Notificações
                            </h2>

                            <p class="text-xs opacity-70 mt-1 uppercase tracking-widest">
                                {{ count($notificacoes) }} notificações
                            </p>
                        </div>

                        <div class="bg-white/10 px-3 py-1 rounded-full text-xs font-bold uppercase">
                            Inbox
                        </div>

                    </div>
                </div>

                <!-- Notification List -->
                <div class="overflow-y-auto flex-1 custom-scrollbar">

                    <template x-for="(notif, index) in lista" :key="notif.id">

                        <div @click="selecionar(index)"
                             :class="ativa === index
                                ? 'bg-[#003366] text-white border-l-[6px] border-cyan-300'
                                : 'bg-white/70 text-[#003366] hover:bg-white border-b border-gray-200'"
                             class="relative px-6 py-5 cursor-pointer transition-all duration-200">

                            <!-- Unread Dot -->
                            <template x-if="!notif.lida">
                                <div class="absolute top-4 right-4 w-3 h-3 bg-red-500 rounded-full animate-pulse shadow-lg"></div>
                            </template>

                            <!-- Type Badge -->
                            <div class="mb-3">

                                <span class="text-[10px] uppercase tracking-widest font-black px-3 py-1 rounded-full"
                                      :class="ativa === index
                                        ? 'bg-white/15 text-cyan-200'
                                        : 'bg-[#003366]/10 text-[#003366]'">

                                    <span x-text="notif.type === 'solicitacao_exclusao'
                                        ? 'Solicitação'
                                        : 'Sistema'"></span>

                                </span>

                            </div>

                            <!-- Title -->
                            <h3 class="font-black text-lg uppercase italic leading-tight mb-2"
                                x-text="notif.titulo">
                            </h3>

                            <!-- Message -->
                            <p class="text-sm leading-relaxed opacity-80 line-clamp-2 italic"
                               x-text="notif.mensagem">
                            </p>

                            <!-- Date -->
                            <div class="mt-4 text-[11px] uppercase tracking-widest opacity-50 font-bold">
                                <span x-text="notif.data_formatada"></span>
                            </div>

                        </div>

                    </template>

                    <!-- Empty -->
                    <template x-if="lista.length === 0">

                        <div class="flex flex-col items-center justify-center h-full text-center p-10">

                            <div class="w-24 h-24 rounded-full bg-[#003366]/10 flex items-center justify-center mb-6">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-12 w-12 text-[#003366]/40"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.5"
                                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />

                                </svg>
                            </div>

                            <h3 class="text-xl font-black uppercase italic text-[#003366] mb-2">
                                Nenhuma notificação
                            </h3>

                            <p class="text-gray-500 italic">
                                Tudo está atualizado.
                            </p>

                        </div>

                    </template>

                </div>

            </div>

            <!-- Content -->
            <div class="hidden md:flex flex-1 bg-[#003366]/95 text-white flex-col relative overflow-hidden">

                <!-- Selected -->
                <template x-if="ativa !== null">

                    <div class="flex flex-col h-full p-10 lg:p-14">

                        <!-- Header -->
                        <div class="border-b border-white/10 pb-6 mb-8">

                            <div class="flex items-center gap-3 mb-4">

                                <span class="bg-cyan-400/20 text-cyan-200 px-4 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">
                                    Detalhes
                                </span>

                                <span class="text-xs uppercase tracking-widest opacity-50 font-bold"
                                      x-text="lista[ativa].data_formatada">
                                </span>

                            </div>

                            <h2 class="text-4xl font-black italic uppercase tracking-tight leading-tight"
                                x-text="lista[ativa].titulo">
                            </h2>

                        </div>

                        <!-- Message -->
                        <div class="flex-1 overflow-y-auto custom-scrollbar pr-4">

                            <p class="text-xl leading-relaxed italic text-white/90 whitespace-pre-line"
                               x-text="lista[ativa].mensagem">
                            </p>

                        </div>

                        <!-- Actions -->
                        <div class="pt-8 mt-8 border-t border-white/10">

                            <!-- Approval -->
                            <template x-if="lista[ativa].type === 'solicitacao_exclusao'">

                                <div class="flex flex-col items-center gap-6">

                                    <div class="bg-yellow-400/10 border border-yellow-400/20 text-yellow-300 px-6 py-3 rounded-2xl font-black uppercase italic text-sm animate-pulse">
                                        ⚠️ Esta ação requer aprovação
                                    </div>

                                    <div class="flex flex-wrap justify-center gap-5">

                                        <button @click="responderExclusao(lista[ativa].id, 'aprovar')"
                                                class="bg-[#00E500] text-black px-10 py-4 rounded-2xl font-black uppercase italic shadow-2xl hover:scale-105 hover:bg-green-400 transition-all active:scale-95">

                                            Aprovar Exclusão

                                        </button>

                                        <button @click="responderExclusao(lista[ativa].id, 'recusar')"
                                                class="bg-red-500 text-white px-10 py-4 rounded-2xl font-black uppercase italic shadow-2xl hover:scale-105 hover:bg-red-600 transition-all active:scale-95">

                                            Recusar

                                        </button>

                                    </div>

                                </div>

                            </template>

                            <!-- Default -->
                            <template x-if="lista[ativa].type !== 'solicitacao_exclusao'">

                                <div class="flex flex-col items-center gap-5">

                                    <template x-if="lista[ativa].link">

                                        <a :href="lista[ativa].link"
                                           class="bg-white text-[#003366] px-8 py-4 rounded-2xl font-black uppercase italic shadow-2xl hover:scale-105 transition-all active:scale-95">

                                            Acessar Arquivo / Pasta

                                        </a>

                                    </template>

                                    <button @click="marcarLidaManual(lista[ativa].id, ativa)"
                                            class="text-xs uppercase tracking-[0.25em] opacity-40 hover:opacity-100 transition-all font-black italic">

                                        Marcar como lida

                                    </button>

                                </div>

                            </template>

                        </div>

                    </div>

                </template>

                <!-- Empty State -->
                <template x-if="ativa === null">

                    <div class="flex-1 flex flex-col items-center justify-center text-center p-10">

                        <div class="w-32 h-32 rounded-full bg-white/5 flex items-center justify-center mb-8">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-16 w-16 text-white/20"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.5"
                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />

                            </svg>

                        </div>

                        <h2 class="text-3xl font-black italic uppercase opacity-70 mb-3">
                            Selecione uma notificação
                        </h2>

                        <p class="text-white/40 italic">
                            Escolha um item da lista para visualizar os detalhes.
                        </p>

                    </div>

                </template>

            </div>

        </div>

        <!-- Footer -->
        <div class="flex justify-between items-center mt-6">

            <a href="{{ route('dashboard') }}"
               class="bg-red-600 text-white px-6 py-3 rounded-2xl font-black uppercase italic shadow-xl hover:bg-red-700 transition-all active:scale-95">

                Voltar

            </a>

            <div class="text-white/60 text-xs uppercase tracking-widest font-bold italic">
                Getec Topografia © {{ date('Y') }}
            </div>

        </div>

        <!-- Confirm Modal -->
        <div x-show="showModalConfirmacao"
             x-cloak
             x-transition
             class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-md p-4">

            <div class="bg-[#003366] text-white p-10 rounded-[40px] w-full max-w-md text-center shadow-2xl border border-white/10">

                <h2 class="text-3xl font-black italic uppercase text-yellow-300 mb-5">
                    Confirmar
                </h2>

                <p class="text-lg italic leading-relaxed mb-10">
                    Tem certeza que deseja
                    <strong x-text="acaoParaProcessar === 'aprovar'
                        ? 'APROVAR a exclusão permanente'
                        : 'RECUSAR o pedido'">
                    </strong>?
                </p>

                <div class="flex justify-center gap-5">

                    <button @click="executarAcao()"
                            class="bg-[#00E500] text-black px-8 py-3 rounded-2xl font-black uppercase italic shadow-lg hover:scale-105 transition">

                        Sim

                    </button>

                    <button @click="showModalConfirmacao = false"
                            class="bg-red-500 text-white px-8 py-3 rounded-2xl font-black uppercase italic shadow-lg hover:scale-105 transition">

                        Cancelar

                    </button>

                </div>

            </div>

        </div>

        <!-- Result Modal -->
        <div x-show="showModalResultado"
             x-cloak
             x-transition
             class="fixed inset-0 z-[110] flex items-center justify-center bg-black/90 backdrop-blur-xl p-4">

            <div class="bg-[#003366] text-white p-10 rounded-[40px] w-full max-w-md text-center shadow-2xl border border-white/10">

                <template x-if="sucesso">

                    <div class="bg-green-500 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 shadow-2xl">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-12 w-12 text-white"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="3"
                                  d="M5 13l4 4L19 7" />

                        </svg>

                    </div>

                </template>

                <h2 class="text-3xl font-black italic uppercase mb-4"
                    x-text="sucesso ? 'Concluído' : 'Erro'">
                </h2>

                <p class="text-lg italic leading-relaxed mb-8"
                   x-text="mensagemResultado">
                </p>

                <button @click="window.location.reload()"
                        class="bg-white text-[#003366] px-10 py-3 rounded-2xl font-black uppercase italic shadow-xl hover:scale-105 transition">

                    OK

                </button>

            </div>

        </div>

    </div>

    <script>
        function notificacoesApp() {
            return {
                lista: @json($notificacoes),
                ativa: null,

                showModalConfirmacao: false,
                showModalResultado: false,

                mensagemResultado: '',
                sucesso: true,

                notifParaProcessar: null,
                acaoParaProcessar: null,

                init() {
                    if (this.lista.length > 0) {
                        this.selecionar(0);
                    }
                },

                selecionar(index) {
                    this.ativa = index;

                    const notifId = this.lista[index].id;

                    if (!this.lista[index].lida) {
                        this.enviarLeitura(notifId);
                        this.lista[index].lida = true;
                    }
                },

                responderExclusao(notifId, acao) {
                    this.notifParaProcessar = notifId;
                    this.acaoParaProcessar = acao;
                    this.showModalConfirmacao = true;
                },

                executarAcao() {

                    fetch(`/admin/pastas/processar-exclusao`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },

                        body: JSON.stringify({
                            notificacao_id: this.notifParaProcessar,
                            status: this.acaoParaProcessar
                        })

                    })

                    .then(response => response.json())

                    .then(data => {
                        this.showModalConfirmacao = false;
                        this.mensagemResultado = data.message;
                        this.sucesso = data.success;
                        this.showModalResultado = true;
                    })

                    .catch(() => {
                        this.showModalConfirmacao = false;
                        this.mensagemResultado = "Erro ao processar.";
                        this.sucesso = false;
                        this.showModalResultado = true;
                    });

                },

                enviarLeitura(notifId) {

                    fetch(`/notificacoes/${notifId}/ler`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    });

                },

                marcarLidaManual(notifId) {

                    this.enviarLeitura(notifId);

                    this.mensagemResultado = 'Notificação marcada como lida!';
                    this.sucesso = true;
                    this.showModalResultado = true;

                }
            }
        }
    </script>

</body>
</html>