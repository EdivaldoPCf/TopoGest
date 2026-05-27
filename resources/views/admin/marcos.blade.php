<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador de Marcos - Getec Topografia</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

        .glass {
            background: rgba(255,255,255,0.18);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
    </style>
</head>

<body class="bg-slate-200 min-h-screen antialiased overflow-x-hidden">

    <!-- Background -->
    <div class="fixed inset-0 z-0 bg-cover bg-center bg-no-repeat"
         style="background-image: url('{{ asset('images/background-topo.jpg') }}');">
    </div>

    <!-- Overlay -->
    <div class="fixed inset-0 z-0 bg-[#001122]/50"></div>

    <div class="relative z-10 px-4 md:px-10 py-8">

        <!-- HEADER -->
        <header class="max-w-7xl mx-auto flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-10">

            <!-- LOGO -->
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-4 bg-white/90 rounded-3xl px-5 py-4 shadow-2xl hover:scale-[1.02] transition-all w-fit">

                <div class="relative flex items-center justify-center w-16 h-16 rounded-2xl bg-white shadow-2xl">
                    <img src="{{ asset('images/logo-icon.png') }}"
                         alt="Getec Topografia"
                         class="w-full h-full object-contain p-2">
                </div>

                <div>
                    <img src="{{ asset('images/logo-text.png') }}"
                         class="h-8">
                    <p class="text-[#003366] text-xs font-black tracking-[0.3em] uppercase mt-1">
                        Gerenciador ADM
                    </p>
                </div>
            </a>

            <!-- TITLE -->
            <div class="flex justify-center">
                <div class="bg-[#003366] text-white px-8 md:px-14 py-4 rounded-3xl shadow-[0_10px_30px_rgba(0,0,0,0.35)] border border-white/10">
                    <h1 class="text-2xl md:text-4xl font-black italic uppercase tracking-tight">
                        Gerenciador de Marcos
                    </h1>
                </div>
            </div>

            <!-- STATUS -->
            <div class="hidden lg:flex items-center gap-3 bg-white/90 px-5 py-4 rounded-2xl shadow-xl">
                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                <span class="font-black text-[#003366] uppercase text-sm">
                    Sistema Online
                </span>
            </div>
        </header>

        <!-- FILTERS -->
        <section class="max-w-7xl mx-auto mb-10">

            <div class="glass border border-white/20 rounded-[32px] p-6 shadow-2xl">

                <div class="flex flex-col xl:flex-row gap-8 xl:items-center xl:justify-between">

                    <!-- Credenciais -->
                    <div>
                        <p class="text-white font-black uppercase tracking-widest text-xs mb-4 opacity-80">
                            Credencial
                        </p>

                        <div class="flex flex-wrap gap-4">

                            <a href="?credencial=BCA&tipo={{ $tipo }}"
                               class="px-8 py-3 rounded-2xl font-black shadow-xl transition-all border-2
                               {{ $credencial == 'BCA'
                                    ? 'bg-[#003366] text-white border-white scale-105'
                                    : 'bg-white text-[#003366] border-transparent hover:bg-slate-100 hover:scale-105' }}">

                                Marcos - BCA
                            </a>

                            <a href="?credencial=EMES&tipo={{ $tipo }}"
                               class="px-8 py-3 rounded-2xl font-black shadow-xl transition-all border-2
                               {{ $credencial == 'EMES'
                                    ? 'bg-[#003366] text-white border-white scale-105'
                                    : 'bg-white text-[#003366] border-transparent hover:bg-slate-100 hover:scale-105' }}">

                                Marcos - EMES
                            </a>

                        </div>
                    </div>

                    <!-- Tipos -->
                    <div>
                        <p class="text-white font-black uppercase tracking-widest text-xs mb-4 opacity-80">
                            Tipo
                        </p>

                        <div class="flex gap-4">

                            @foreach(['M', 'P', 'V'] as $t)

                                <a href="?credencial={{ $credencial }}&tipo={{ $t }}"
                                   class="w-16 h-16 rounded-2xl flex items-center justify-center text-xl font-black shadow-2xl border-2 transition-all
                                   {{ $tipo == $t
                                        ? 'bg-[#003366] text-white border-white scale-110'
                                        : 'bg-white text-[#003366] border-transparent hover:scale-105 hover:bg-slate-100' }}">

                                    {{ $t }}
                                </a>

                            @endforeach

                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- FORM -->
        <section class="max-w-5xl mx-auto mb-12">

            <div class="glass rounded-[40px] p-8 md:p-10 border border-white/20 shadow-[0_15px_40px_rgba(0,0,0,0.35)]">

                <!-- FORM -->
                <form action="{{ route('admin.marcos.store') }}" method="POST" class="space-y-8">

                    @csrf

                    <input type="hidden" name="credencial" value="{{ $credencial }}">
                    <input type="hidden" name="tipo" value="{{ $tipo }}">

                    <!-- Números dos Marcos -->
                    <div>
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                            <label class="text-white font-black uppercase tracking-widest text-sm">
                                Números dos Marcos
                            </label>

                            <!-- Informação de Último Cadastrado -->
                            <div class="text-white/85 text-xs bg-[#003366]/40 border border-white/10 px-4 py-2 rounded-xl flex items-center gap-2">
                                <span>Último cadastrado:</span>
                                <span class="text-yellow-400 font-bold tracking-wide">
                                    {{ $ultimo ? $credencial.'-'.$tipo.'-'.sprintf('%04d', $ultimo->numero) : 'Nenhum' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-col gap-4">
                            <div class="flex flex-wrap items-center gap-4">
                                <!-- Prefixo fixo -->
                                <span class="inline-flex items-center h-16 rounded-2xl bg-white/90 px-5 text-[#003366] text-lg font-black uppercase tracking-[0.2em] shadow-inner">
                                    {{ $credencial }}-{{ $tipo }}-
                                </span>

                                <!-- Input Início -->
                                <div class="flex flex-col">
                                    <input type="text"
                                           name="numero_marcos"
                                           required
                                           maxlength="7"
                                           pattern="[0-9]{1,7}"
                                           inputmode="numeric"
                                           placeholder="Início"
                                           class="w-32 h-16 rounded-2xl bg-white/90 px-4 text-[#003366] text-xl font-black outline-none border-2 border-transparent focus:border-[#003366] focus:ring-4 focus:ring-blue-400/30 shadow-inner text-center">
                                </div>

                                <span class="text-white font-bold text-lg">até</span>

                                <!-- Input Fim (Opcional) -->
                                <div class="flex flex-col">
                                    <input type="text"
                                           name="numero_marcos_fim"
                                           maxlength="7"
                                           pattern="[0-9]{1,7}"
                                           inputmode="numeric"
                                           placeholder="Fim (Opcional)"
                                           class="w-40 h-16 rounded-2xl bg-white/90 px-4 text-[#003366] text-xl font-black outline-none border-2 border-transparent focus:border-[#003366] focus:ring-4 focus:ring-blue-400/30 shadow-inner text-center">
                                </div>
                            </div>

                            <p class="text-white/70 text-sm">
                                Insira o número do marco (ex: 1). Para cadastrar vários marcos em lote (ex: do 1 ao 100), preencha também o campo opcional "Fim".
                            </p>

                            @error('numero_marcos')
                                <p class="text-red-400 text-sm font-semibold">{{ $message }}</p>
                            @enderror
                            @error('numero_marcos_fim')
                                <p class="text-red-400 text-sm font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Imóvel -->
                    <div>

                        <label class="block mb-3 text-white font-black uppercase tracking-widest text-sm">
                            Nome do Imóvel
                        </label>

                        <input type="text"
                               name="imovel"
                               required
                               placeholder="Digite o nome do imóvel..."
                               class="w-full h-16 rounded-2xl bg-white/90 px-6 text-[#003366] text-lg font-bold outline-none border-2 border-transparent focus:border-[#003366] focus:ring-4 focus:ring-blue-400/30 shadow-inner">

                    </div>

                    <!-- BTN -->
                    <div class="flex justify-center pt-4">

                        <button type="submit"
                                class="bg-[#003366] hover:bg-[#002244] text-white px-14 md:px-20 py-4 rounded-3xl font-black text-xl md:text-2xl uppercase tracking-wide shadow-[0_10px_30px_rgba(0,0,0,0.4)] transition-all hover:scale-105 active:scale-95 border border-white/10">

                            Adicionar Marco

                        </button>

                    </div>

                </form>

            </div>

        </section>

        <!-- TABLE -->
        <section class="max-w-7xl mx-auto">

            <div class="glass rounded-[40px] border border-white/20 shadow-[0_15px_40px_rgba(0,0,0,0.35)] overflow-hidden">

                <!-- HEADER TABLE -->
                <div class="p-8 border-b border-white/10 flex flex-col xl:flex-row gap-6 xl:items-center xl:justify-between">

                    <div class="flex items-center gap-4">

                        <div class="w-4 h-14 rounded-full bg-[#003366]"></div>

                        <div>
                            <h2 class="text-white text-3xl font-black italic uppercase tracking-tight">
                                Marcos Cadastrados
                            </h2>

                            <p class="text-white/70 text-sm uppercase tracking-[0.2em] mt-1">
                                Banco de registros Getec Topografia
                            </p>
                        </div>

                    </div>

                    <!-- ACTIONS & SEARCH -->
                    <div class="flex flex-wrap items-center gap-4">

                        <!-- Botão de Excluir Selecionados -->
                        <button id="btn-bulk-delete" 
                                type="button" 
                                class="hidden bg-red-600 hover:bg-red-700 text-white px-6 py-4 rounded-full font-black uppercase text-sm shadow-xl transition-all hover:scale-105 flex items-center gap-2 border border-white/10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Excluir Selecionados (<span id="selected-count">0</span>)
                        </button>

                        <!-- SEARCH -->
                        <form action="" method="GET" class="relative">

                            <input type="hidden" name="credencial" value="{{ $credencial }}">
                            <input type="hidden" name="tipo" value="{{ $tipo }}">

                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Pesquisar marcos..."
                                   class="bg-[#003366] text-white rounded-full pl-14 pr-6 py-4 placeholder-white/50 outline-none w-full md:w-96 border border-white/10 shadow-xl font-bold focus:ring-4 focus:ring-blue-400/30">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-6 h-6 absolute left-5 top-1/2 -translate-y-1/2 text-white/60"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2.5"
                                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />

                            </svg>

                        </form>
                    </div>

                </div>

                <!-- TABLE -->
                <div class="overflow-x-auto">

                    <table class="w-full min-w-full lg:min-w-[900px]">

                        <thead class="bg-[#003366] text-white">

                            <tr class="uppercase tracking-widest text-sm">

                                <th class="py-5 px-6 text-center w-16">
                                    <input type="checkbox" 
                                           id="select-all" 
                                           class="w-5 h-5 rounded border-slate-300 text-[#003366] focus:ring-blue-400/30 cursor-pointer">
                                </th>

                                <th class="text-left py-5 px-8 font-black">
                                    Número do Marco
                                </th>

                                <th class="text-left py-5 px-8 font-black">
                                    Imóvel
                                </th>

                                <th class="text-left py-5 px-8 font-black">
                                    Cadastro
                                </th>

                                <th class="text-center py-5 px-8 font-black">
                                    Ação
                                </th>

                            </tr>

                        </thead>

                        <tbody class="bg-white/80 text-[#003366]">

                            @forelse($marcos as $marco)

                                <tr class="border-b border-slate-200 hover:bg-blue-50/60 transition-all cursor-pointer">

                                    <td class="py-6 px-6 text-center">
                                        <input type="checkbox" 
                                               name="marco_ids[]" 
                                               value="{{ $marco->id }}" 
                                               class="marco-checkbox w-5 h-5 rounded border-slate-300 text-[#003366] focus:ring-blue-400/30 cursor-pointer">
                                    </td>

                                    <td class="py-6 px-8">

                                        <div class="font-black text-lg tracking-tight">
                                            {{ $marco->credencial }}-{{ $marco->tipo }}-{{ sprintf('%04d', $marco->numero) }}
                                        </div>

                                    </td>

                                    <td class="py-6 px-8">

                                        <div class="font-bold italic text-lg">
                                            {{ $marco->imovel }}
                                        </div>

                                    </td>

                                    <td class="py-6 px-8">

                                        <div class="font-semibold opacity-70">
                                            {{ $marco->created_at->format('d/m/Y') }}
                                        </div>

                                        <div class="text-sm uppercase tracking-[0.12em] mt-1">
                                            {{ $marco->user?->abbreviated_name ?? '—' }}
                                        </div>

                                    </td>

                                    <td class="py-6 px-8 text-center">

                                        <form action="{{ route('admin.marcos.destroy', $marco->id) }}"
                                              method="POST"
                                              class="delete-form inline-block">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-xl font-black uppercase text-sm shadow-lg transition-all hover:scale-105">

                                                Excluir

                                             </button>

                                        </form>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="5" class="text-center py-24">

                                        <div class="flex flex-col items-center">

                                            <div class="w-24 h-24 rounded-full bg-[#003366]/10 flex items-center justify-center mb-6">

                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     class="w-12 h-12 text-[#003366]/40"
                                                     fill="none"
                                                     viewBox="0 0 24 24"
                                                     stroke="currentColor">

                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          stroke-width="1.5"
                                                          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />

                                                </svg>

                                            </div>

                                            <h3 class="text-2xl font-black text-[#003366] uppercase mb-2">
                                                Nenhum marco encontrado
                                            </h3>

                                            <p class="text-slate-500 font-semibold">
                                                Cadastre o primeiro marco para começar.
                                            </p>

                                        </div>

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

                <!-- PAGINAÇÃO -->
                <div class="px-8 py-6 bg-white/90 border-t border-white/10">
                    {{ $marcos->links('vendor.pagination.topogest') }}
                </div>

            </div>

        </section>

        <!-- FOOTER -->
        <div class="max-w-7xl mx-auto mt-10 flex justify-start">

            <a href="{{ route('dashboard') }}"
               class="bg-red-600 hover:bg-red-700 text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest shadow-2xl border-b-[6px] border-red-900 active:border-b-0 active:translate-y-1 transition-all">

                Voltar

            </a>

        </div>

    </div>

    <!-- Hidden Bulk Delete Form -->
    <form id="bulk-delete-form" action="{{ route('admin.marcos.destroy-multiple') }}" method="POST" class="hidden">
        @csrf
        <div id="bulk-inputs-container"></div>
    </form>

    <!-- SWEET ALERTS & SCRIPTS -->
    <script>

        // Seleção múltipla
        const selectAllCheckbox = document.getElementById('select-all');
        const milestoneCheckboxes = document.querySelectorAll('.marco-checkbox');
        const bulkDeleteBtn = document.getElementById('btn-bulk-delete');
        const selectedCountSpan = document.getElementById('selected-count');
        const bulkDeleteForm = document.getElementById('bulk-delete-form');
        const bulkInputsContainer = document.getElementById('bulk-inputs-container');

        function updateBulkButton() {
            const checkedBoxes = document.querySelectorAll('.marco-checkbox:checked');
            const totalCount = checkedBoxes.length;

            if (totalCount > 0) {
                selectedCountSpan.textContent = totalCount;
                bulkDeleteBtn.classList.remove('hidden');
            } else {
                bulkDeleteBtn.classList.add('hidden');
            }

            // Atualiza checkbox mestre
            const totalCheckboxes = milestoneCheckboxes.length;
            if (totalCheckboxes > 0) {
                selectAllCheckbox.checked = (totalCount === totalCheckboxes);
                selectAllCheckbox.indeterminate = (totalCount > 0 && totalCount < totalCheckboxes);
            }
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                milestoneCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                updateBulkButton();
            });
        }

        milestoneCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkButton);
        });

        // Clique na linha da tabela para marcar checkbox (com exclusão de elementos de ação)
        document.querySelectorAll('tbody tr').forEach(row => {
            row.addEventListener('click', function(e) {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'BUTTON' || e.target.closest('form') || e.target.closest('button')) {
                    return;
                }
                const checkbox = row.querySelector('.marco-checkbox');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    updateBulkButton();
                }
            });
        });

        // Clique para exclusão em massa
        if (bulkDeleteBtn) {
            bulkDeleteBtn.addEventListener('click', function() {
                const checkedBoxes = document.querySelectorAll('.marco-checkbox:checked');
                const totalCount = checkedBoxes.length;

                if (totalCount === 0) return;

                Swal.fire({
                    title: 'Excluir Marcos Selecionados?',
                    html: `Deseja realmente excluir os <b>${totalCount}</b> marcos selecionados?<br>Esta ação não poderá ser desfeita.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, excluir todos',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#003366',
                    background: '#0f172a',
                    color: '#fff',
                    borderRadius: 25
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Limpa inputs anteriores
                        bulkInputsContainer.innerHTML = '';
                        
                        // Adiciona os IDs selecionados
                        checkedBoxes.forEach(checkbox => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'ids[]';
                            input.value = checkbox.value;
                            bulkInputsContainer.appendChild(input);
                        });

                        // Envia formulário
                        bulkDeleteForm.submit();
                    }
                });
            });
        }

        // Confirmar exclusão individual
        document.querySelectorAll('.delete-form').forEach(form => {

            form.addEventListener('submit', function(e) {

                e.preventDefault();

                Swal.fire({
                    title: 'Excluir Marco?',
                    text: 'Esta ação não poderá ser desfeita.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, excluir',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#003366',
                    background: '#0f172a',
                    color: '#fff',
                    borderRadius: 25

                }).then((result) => {

                    if (result.isConfirmed) {
                        form.submit();
                    }

                });

            });

        });

        // Duplicidade
        @if(session('error_duplicate'))

            Swal.fire({
                title: 'Marco já cadastrado!',
                html: `
                    <div style="text-align:left">
                        <p style="margin-bottom:15px;">
                            O marco <b>{{ session('error_duplicate')['marco'] }}</b> já existe.
                        </p>

                        <p><b>Imóvel:</b> {{ session('error_duplicate')['imovel'] }}</p>

                        <p><b>Cadastro:</b> {{ session('error_duplicate')['data'] }}</p>
                    </div>
                `,
                icon: 'warning',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#003366',
                borderRadius: 25
            });

        @endif

        // Prefixo inválido
        @if(session('error_prefixo'))

            Swal.fire({
                title: 'Código inválido',
                text: "{{ session('error_prefixo') }}",
                icon: 'error',
                confirmButtonColor: '#003366',
                borderRadius: 25
            });

        @endif

        // Erro genérico
        @if(session('error'))

            Swal.fire({
                title: 'Erro!',
                text: "{{ session('error') }}",
                icon: 'error',
                confirmButtonColor: '#003366',
                borderRadius: 25
            });

        @endif

        // Sucesso
        @if(session('success'))

            Swal.fire({
                title: 'Sucesso!',
                text: "{{ session('success') }}",
                icon: 'success',
                timer: 2200,
                showConfirmButton: false,
                borderRadius: 25
            });

        @endif

    </script>

</body>
</html>