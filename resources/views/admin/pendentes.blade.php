<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitações ADM - TopoGest</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #003366;
            border-radius: 999px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255,255,255,.2);
        }

        body {
            overflow-x: hidden;
        }

        .glass-card {
            background: rgba(255,255,255,.18);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255,255,255,.25);
            box-shadow: 0 15px 40px rgba(0,0,0,.25);
        }

        .table-row {
            transition: .25s ease;
        }

        .table-row:hover {
            transform: translateY(-2px);
        }

        .pulse-dot {
            animation: pulseDot 1.4s infinite;
        }

        @keyframes pulseDot {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: .6; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') . '?v=' . @filemtime(public_path('images/logo-icon.png')) }}">
</head>

<body class="bg-gray-200 min-h-screen antialiased p-4 md:p-10">

    <!-- BACKGROUND -->
    <div class="fixed inset-0 z-0 bg-cover bg-center bg-no-repeat bg-fixed"
         style="background-image: url('{{ asset('images/background-topo.jpg') . '?v=' . @filemtime(public_path('images/background-topo.jpg')) }}');">
    </div>

    <!-- OVERLAY -->
    <div class="fixed inset-0 bg-[#001B33]/40 z-0"></div>

    <div class="relative z-10 max-w-7xl mx-auto space-y-10">

        <!-- HEADER -->
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6">

            <!-- LOGO -->
            <a href="{{ route('dashboard') }}"
               class="flex items-center group transition duration-300 hover:scale-105 w-fit">

                <div class="relative flex items-center justify-center w-20 h-20 rounded-2xl bg-white shadow-2xl transition group-hover:scale-105">
                    <img src="{{ asset('images/logo-icon.png') . '?v=' . @filemtime(public_path('images/logo-icon.png')) }}"
                         alt="TopoGest"
                         class="w-full h-full object-contain p-2">
                </div>

                <div class="relative ml-3 flex items-center h-14 px-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                    <img src="{{ asset('images/logo-text.png') . '?v=' . @filemtime(public_path('images/logo-text.png')) }}"
                         alt="TopoGest"
                         class="relative z-10 h-8 w-auto">
                </div>
            </a>

            <!-- TOP ACTIONS -->
            <div class="flex flex-col md:flex-row gap-4 md:items-center">

                <a href="{{ route('dashboard') }}"
                   class="bg-white/20 backdrop-blur-md border border-white/20 text-white px-6 py-3 rounded-2xl font-black uppercase tracking-wide shadow-xl hover:bg-white/30 transition-all flex items-center justify-center gap-3">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M3 10l9-7 9 7M9 21V9h6v12" />
                    </svg>

                    Dashboard
                </a>

                <div class="bg-[#003366]/80 text-white px-6 py-3 rounded-2xl shadow-2xl border border-white/10 font-black uppercase tracking-wide">
                    Gestão de Permissões
                </div>
            </div>
        </div>

        <!-- SEARCH PENDENTES -->
        <div class="glass-card rounded-[35px] p-6 md:p-8">

            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-8">

                <div>
                    <h2 class="text-white text-3xl font-black uppercase italic tracking-tight">
                        Solicitações Pendentes
                    </h2>

                    <p class="text-white/70 mt-2">
                        Gerencie solicitações de novos administradores.
                    </p>
                </div>

                <form action="{{ route('admin.pendentes') }}"
                      method="GET"
                      class="relative">

                    <input type="hidden"
                           name="search_admins"
                           value="{{ request('search_admins') }}">

                    <input type="text"
                           name="search_pendentes"
                           value="{{ request('search_pendentes') }}"
                           placeholder="Pesquisar solicitações..."
                           class="bg-[#003366] text-white placeholder-white/50 pl-14 pr-6 py-4 rounded-full w-full md:w-[400px] outline-none border border-white/20 shadow-xl focus:ring-4 focus:ring-blue-500/30 transition-all font-semibold">

                    <div class="absolute left-5 top-4 text-white/70">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-6 w-6"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </form>
            </div>

            <!-- TABLE -->
            <div class="overflow-x-auto rounded-3xl">

                <table class="w-full border-separate border-spacing-y-4 min-w-full lg:min-w-[900px]">

                    <thead>
                        <tr class="text-white uppercase text-sm tracking-widest">
                            <th class="text-left px-6">Nome</th>
                            <th class="text-left px-6">CPF</th>
                            <th class="text-left px-6">Email</th>
                            <th class="text-left px-6">Cadastro</th>
                            <th class="text-center px-6">Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($solicitacoes as $solicitacao)

                            <tr class="table-row bg-white/90 shadow-xl">

                                <td class="py-5 px-6 rounded-l-3xl">

                                    <div class="flex items-center gap-4">

                                        <div class="w-12 h-12 rounded-full bg-[#003366] text-white flex items-center justify-center font-black text-sm shadow-lg">
                                            {{ strtoupper(substr($solicitacao->name, 0, 2)) }}
                                        </div>

                                        <div>
                                            <div class="text-[#003366] font-black text-lg leading-none">
                                                {{ $solicitacao->name }}
                                            </div>

                                            <div class="text-xs uppercase tracking-widest text-gray-500 mt-1">
                                                Solicitação Pendente
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-5 px-6 font-bold text-[#003366] whitespace-nowrap">
                                    {{ $solicitacao->cpf }}
                                </td>

                                <td class="py-5 px-6 text-[#003366] italic">
                                    {{ $solicitacao->email }}
                                </td>

                                <td class="py-5 px-6 text-[#003366]/70 font-semibold whitespace-nowrap">
                                    {{ $solicitacao->created_at->format('d/m/Y') }}
                                </td>

                                <td class="py-5 px-6 rounded-r-3xl">

                                    <div class="flex justify-center gap-3">

                                        <button onclick="openActionModal('confirmar', {{ $solicitacao->id }}, '{{ addslashes($solicitacao->name) }}')"
                                                class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-2xl font-black uppercase text-xs shadow-lg transition-all hover:scale-105">

                                            Confirmar
                                        </button>

                                        <button onclick="openActionModal('negar', {{ $solicitacao->id }}, '{{ addslashes($solicitacao->name) }}')"
                                                class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-2xl font-black uppercase text-xs shadow-lg transition-all hover:scale-105">

                                            Negar
                                        </button>

                                    </div>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="5"
                                    class="text-center py-20 text-white/80 font-black uppercase tracking-wider">

                                    Nenhuma solicitação pendente encontrada.

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>
            </div>
        </div>

        <!-- ADMINS -->
        <div class="glass-card rounded-[35px] p-6 md:p-8">

            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-8">

                <div>
                    <h2 class="text-white text-3xl font-black uppercase italic tracking-tight">
                        Administradores Atuais
                    </h2>

                    <p class="text-white/70 mt-2">
                        Usuários com acesso administrativo ativo.
                    </p>
                </div>

                <form action="{{ route('admin.pendentes') }}"
                      method="GET"
                      class="relative">

                    <input type="hidden"
                           name="search_pendentes"
                           value="{{ request('search_pendentes') }}">

                    <input type="text"
                           name="search_admins"
                           value="{{ request('search_admins') }}"
                           placeholder="Pesquisar administradores..."
                           class="bg-[#003366] text-white placeholder-white/50 pl-14 pr-6 py-4 rounded-full w-full md:w-[400px] outline-none border border-white/20 shadow-xl focus:ring-4 focus:ring-blue-500/30 transition-all font-semibold">

                    <div class="absolute left-5 top-4 text-white/70">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-6 w-6"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </form>
            </div>

            <!-- TABLE -->
            <div class="overflow-x-auto rounded-3xl">

                <table class="w-full border-separate border-spacing-y-4 min-w-full lg:min-w-[900px]">

                    <thead>
                        <tr class="text-white uppercase text-sm tracking-widest">
                            <th class="text-left px-6">Nome</th>
                            <th class="text-left px-6">CPF</th>
                            <th class="text-left px-6">Email</th>
                            <th class="text-left px-6">Cadastro</th>
                            <th class="text-center px-6">Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($administradores as $admin)

                            <tr class="table-row bg-white/90 shadow-xl">

                                <td class="py-5 px-6 rounded-l-3xl">

                                    <div class="flex items-center gap-4">

                                        <div class="w-12 h-12 rounded-full bg-[#003366] text-white flex items-center justify-center font-black text-sm shadow-lg">
                                            {{ strtoupper(substr($admin->name, 0, 2)) }}
                                        </div>

                                        <div>
                                            <div class="text-[#003366] font-black text-lg leading-none">
                                                {{ $admin->name }}
                                            </div>

                                            <div class="text-xs uppercase tracking-widest text-gray-500 mt-1 flex items-center gap-2">

                                                <span class="w-2 h-2 rounded-full bg-green-500 pulse-dot"></span>

                                                Administrador Ativo
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-5 px-6 font-bold text-[#003366] whitespace-nowrap">
                                    {{ $admin->cpf }}
                                </td>

                                <td class="py-5 px-6 text-[#003366] italic">
                                    {{ $admin->email }}
                                </td>

                                <td class="py-5 px-6 text-[#003366]/70 font-semibold whitespace-nowrap">
                                    {{ $admin->created_at->format('d/m/Y') }}
                                </td>

                                <td class="py-5 px-6 rounded-r-3xl">

                                    <div class="flex justify-center">

                                        @if(auth()->id() === $admin->id)

                                            <span class="bg-[#003366] text-white px-6 py-2 rounded-2xl font-black uppercase text-xs shadow-lg">
                                                Você
                                            </span>

                                        @else

                                            <button onclick="openActionModal('remover', {{ $admin->id }}, '{{ addslashes($admin->name) }}')"
                                                    class="bg-orange-600 hover:bg-orange-700 text-white px-5 py-2 rounded-2xl font-black uppercase text-xs shadow-lg transition-all hover:scale-105">

                                                Retirar Privilégio
                                            </button>

                                        @endif

                                    </div>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="5"
                                    class="text-center py-20 text-white/80 font-black uppercase tracking-wider">

                                    Nenhum administrador encontrado.

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>
            </div>
        </div>
    </div>

    <!-- MODAL -->
    <div id="customModal"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300 p-4">

        <div class="bg-[#003366] text-white rounded-[35px] p-8 md:p-10 max-w-lg w-full text-center shadow-2xl border border-white/10 scale-95 transition-all"
             id="modalBox">

            <div class="w-20 h-20 mx-auto rounded-full bg-white/10 flex items-center justify-center mb-6 border border-white/10">

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-10 w-10"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <h2 id="modalTitle"
                class="text-3xl font-black uppercase mb-5">
                Atenção
            </h2>

            <p id="modalMessage"
               class="text-lg text-white/90 leading-relaxed mb-8">
            </p>

            <div class="flex flex-col sm:flex-row justify-center gap-4">

                <button id="btnConfirm"
                        class="bg-green-500 hover:bg-green-600 text-white font-black uppercase px-8 py-3 rounded-2xl transition-all shadow-xl">
                    Confirmar
                </button>

                <button id="btnCancel"
                        onclick="closeModal()"
                        class="bg-red-500 hover:bg-red-600 text-white font-black uppercase px-8 py-3 rounded-2xl transition-all shadow-xl">
                    Cancelar
                </button>

            </div>
        </div>
    </div>

    <!-- SCRIPT -->
    <script>
        const modal = document.getElementById('customModal');
        const modalBox = document.getElementById('modalBox');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        const btnConfirm = document.getElementById('btnConfirm');
        const btnCancel = document.getElementById('btnCancel');

        let currentActionInfo = null;

        function openActionModal(actionType, id, userName) {

            currentActionInfo = { actionType, id, userName };

            modalTitle.innerText = "Confirmação";

            btnConfirm.innerText = "Finalizar";

            btnConfirm.disabled = false;

            btnCancel.style.display = "inline-flex";

            if(actionType === 'confirmar') {

                modalMessage.innerHTML =
                    `Você está prestes a <strong>CONCEDER</strong> privilégios administrativos para <br><strong class="text-yellow-300">${userName}</strong>.<br><br>Deseja continuar?`;

            } else if(actionType === 'negar') {

                modalMessage.innerHTML =
                    `Você está prestes a <strong>NEGAR</strong> a solicitação de <br><strong class="text-yellow-300">${userName}</strong>.<br><br>Deseja continuar?`;

            } else if(actionType === 'remover') {

                modalMessage.innerHTML =
                    `Você está prestes a <strong>REMOVER</strong> os privilégios administrativos de <br><strong class="text-yellow-300">${userName}</strong>.<br><br>Deseja continuar?`;
            }

            btnConfirm.onclick = executeAction;

            modal.classList.remove('opacity-0', 'pointer-events-none');

            setTimeout(() => {
                modalBox.classList.remove('scale-95');
                modalBox.classList.add('scale-100');
            }, 10);
        }

        function closeModal() {

            modalBox.classList.remove('scale-100');
            modalBox.classList.add('scale-95');

            modal.classList.add('opacity-0', 'pointer-events-none');
        }

        function executeAction() {

            btnConfirm.innerText = "Processando...";
            btnConfirm.disabled = true;

            const payload = {
                _token: '{{ csrf_token() }}',
                action: currentActionInfo.actionType
            };

            fetch(`/admin/gestao-permissoes/${currentActionInfo.id}`, {
                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },

                body: JSON.stringify(payload)
            })

            .then(async response => {

                const result = await response.json();

                if(response.ok) {

                    showSuccess(
                        result.message || 'Operação realizada com sucesso.',
                        result.whatsapp_url || null
                    );

                } else {

                    showError(
                        result.message || 'Erro ao processar a solicitação.'
                    );
                }
            })

            .catch(() => {

                showError('Erro crítico de conexão.');
            });
        }

        function showSuccess(msg, wppUrl = null) {

            modalTitle.innerText = "Sucesso";

            modalMessage.innerHTML =
                `<span class="text-2xl font-bold text-green-300">${msg}</span>`;

            btnCancel.style.display = "none";

            btnConfirm.innerText = "OK";

            btnConfirm.disabled = false;

            btnConfirm.className =
                "bg-[#00AEEF] hover:bg-blue-500 text-white font-black uppercase px-8 py-3 rounded-2xl transition-all shadow-xl";

            btnConfirm.onclick = () => {

                if(wppUrl) {
                    window.open(wppUrl, '_blank');
                }

                window.location.reload();
            };
        }

        function showError(msg) {

            modalTitle.innerText = "Erro";

            modalMessage.innerHTML =
                `<span class="text-xl text-red-300">${msg}</span>`;

            btnCancel.style.display = "none";

            btnConfirm.innerText = "Fechar";

            btnConfirm.disabled = false;

            btnConfirm.className =
                "bg-red-600 hover:bg-red-700 text-white font-black uppercase px-8 py-3 rounded-2xl transition-all shadow-xl";

            btnConfirm.onclick = closeModal;
        }
    </script>

</body>
</html>