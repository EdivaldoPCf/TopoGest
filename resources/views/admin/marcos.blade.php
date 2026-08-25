@extends('layouts.admin')

@section('content')
<div class="w-full animate-fade">

        <!-- HEADER -->
        <div class="mb-8 bg-white/90 backdrop-blur-md p-6 rounded-2xl border border-slate-200 shadow-sm inline-block w-max pr-12">
            <h2 class="text-3xl font-black text-slate-800 tracking-tight">Painel de Marcos</h2>
            <p class="text-slate-600 mt-1 font-medium">Gerenciamento e Cadastro de Marcos</p>
        </div>

        <!-- MAIN CONTAINER -->
        <section class="max-w-5xl mx-auto mb-12 mt-6">
            <div class="bg-white rounded-[32px] p-8 md:p-10 border border-slate-200 shadow-sm">
                <!-- Formulário de Filtros Dinâmicos -->
                    <form action="" method="GET" class="w-full flex flex-col md:flex-row gap-6 md:items-end justify-between">
                        
                        <div class="flex flex-col md:flex-row gap-6 w-full md:w-auto">
                            <!-- Credenciais Select -->
                            <div class="flex flex-col">
                                <label class="text-slate-500 font-black uppercase tracking-widest text-xs mb-2 flex justify-between">
                                    <span>Credencial</span>
                                    <a href="{{ route('credenciais.index') }}" class="text-blue-500 hover:text-blue-700 underline text-[10px]">Gerenciar Credenciais</a>
                                </label>
                                <select name="credencial" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 text-slate-800 text-lg font-bold rounded-2xl px-5 py-3 outline-none focus:border-[#003366] focus:ring-4 focus:ring-blue-400/30 w-full md:w-64 cursor-pointer shadow-inner">
                                    @foreach($credenciaisList as $cred)
                                        <option value="{{ $cred->codigo }}" {{ $credencial == $cred->codigo ? 'selected' : '' }}>
                                            {{ $cred->codigo }} @if($cred->nome) - {{ explode(' ', trim($cred->nome))[0] }} @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tipos Select -->
                            <div class="flex flex-col">
                                <label class="text-slate-500 font-black uppercase tracking-widest text-xs mb-2">
                                    Tipo de Vértice
                                </label>
                                <select name="tipo" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 text-slate-800 text-lg font-bold rounded-2xl px-5 py-3 outline-none focus:border-[#003366] focus:ring-4 focus:ring-blue-400/30 w-full md:w-40 cursor-pointer shadow-inner">
                                    @foreach(['M' => 'Marco (M)', 'P' => 'Ponto (P)', 'V' => 'Vértice (V)'] as $val => $label)
                                        <option value="{{ $val }}" {{ $tipo == $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                    </form>
                    
                    <hr class="border-slate-100 my-8">

                    <!-- FORM -->
                <form action="{{ route('admin.marcos.store') }}" method="POST" class="space-y-8">

                    @csrf

                    <input type="hidden" name="credencial" value="{{ $credencial }}">
                    <input type="hidden" name="tipo" value="{{ $tipo }}">

                    <!-- Números dos Marcos -->
                    <div>
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                            <label class="text-slate-800 font-black uppercase tracking-widest text-sm">
                                Números dos Marcos
                            </label>

                            <!-- Informação de Último Cadastrado -->
                            <div class="text-slate-700 text-xs bg-slate-100 border border-slate-300 px-4 py-2 rounded-xl flex items-center gap-2">
                                <span>Último cadastrado:</span>
                                <span class="text-[#003366] font-bold tracking-wide">
                                    {{ $ultimo ? $credencial.'-'.$tipo.'-'.str_pad($ultimo->numero, 4, '0', STR_PAD_LEFT) : 'Nenhum' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-col gap-4">
                            <div class="flex flex-wrap items-center gap-4">
                                <!-- Prefixo fixo -->
                                <span class="inline-flex items-center h-16 rounded-2xl bg-slate-100 border border-slate-300 px-5 text-[#003366] text-lg font-black uppercase tracking-[0.2em] shadow-inner">
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
                                           class="w-32 h-16 rounded-2xl bg-white px-4 text-slate-800 border-slate-300 border focus:border-[#003366] focus:border-[#003366] focus:ring-4 focus:ring-blue-400/30 shadow-inner text-center">
                                </div>

                                <span class="text-slate-800 font-bold text-lg">até</span>

                                <!-- Input Fim (Opcional) -->
                                <div class="flex flex-col">
                                    <input type="text"
                                           name="numero_marcos_fim"
                                           maxlength="7"
                                           pattern="[0-9]{1,7}"
                                           inputmode="numeric"
                                           placeholder="Fim (Opcional)"
                                           class="w-40 h-16 rounded-2xl bg-white px-4 text-slate-800 border-slate-300 border focus:border-[#003366] focus:border-[#003366] focus:ring-4 focus:ring-blue-400/30 shadow-inner text-center">
                                </div>
                            </div>

                            <p class="text-slate-500 text-sm">
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

                        <label class="block mb-3 text-slate-800 font-black uppercase tracking-widest text-sm">
                            Nome do Imóvel
                        </label>

                        <input type="text"
                               name="imovel"
                               required
                               placeholder="Digite o nome do imóvel..."
                               class="w-full h-16 rounded-2xl bg-white px-6 text-slate-800 border-slate-300 border focus:border-[#003366] focus:border-[#003366] focus:ring-4 focus:ring-blue-400/30 shadow-inner">

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

            <div class="bg-white rounded-[32px] border border-slate-200 shadow-sm overflow-hidden">

                <!-- HEADER TABLE -->
                <div class="p-8 border-b border-slate-200 flex flex-col xl:flex-row gap-6 xl:items-center xl:justify-between">

                    <div class="flex items-center gap-4">

                        <div class="w-4 h-14 rounded-full bg-[#003366]"></div>

                        <div>
                            <h2 class="text-slate-800 text-3xl font-black italic uppercase tracking-tight">
                                Marcos Cadastrados
                            </h2>

                            <p class="text-slate-500 text-sm uppercase tracking-[0.2em] mt-1">
                                Banco de registros TopoGest
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
                                   class="bg-slate-100 text-slate-800 rounded-full pl-14 pr-6 py-4 placeholder-slate-400 outline-none w-full md:w-96 border border-slate-300 shadow-sm font-bold focus:ring-4 focus:ring-blue-400/30">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-6 h-6 absolute left-5 top-1/2 -translate-y-1/2 text-slate-500"
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

                        <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">

                            <tr class="uppercase tracking-widest text-sm">

                                <th class="py-5 px-6 text-center w-16">
                                    <input type="checkbox" 
                                           id="select-all" 
                                           class="w-5 h-5 rounded border-slate-300 text-[#003366] bg-white focus:ring-blue-400/30 cursor-pointer">
                                </th>

                                <th class="text-left py-5 px-8 font-black group">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'numero', 'sort_dir' => ($sortBy == 'numero' && $sortDir == 'asc' ? 'desc' : 'asc')]) }}" class="flex items-center gap-2 hover:text-yellow-400 transition-colors">
                                        Número do Marco
                                        @if($sortBy == 'numero')
                                            <span class="text-yellow-400">{!! $sortDir == 'asc' ? '&#9650;' : '&#9660;' !!}</span>
                                        @else
                                            <span class="opacity-0 group-hover:opacity-50 transition-opacity">&#9650;</span>
                                        @endif
                                    </a>
                                </th>

                                <th class="text-left py-5 px-8 font-black group">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'imovel', 'sort_dir' => ($sortBy == 'imovel' && $sortDir == 'asc' ? 'desc' : 'asc')]) }}" class="flex items-center gap-2 hover:text-yellow-400 transition-colors">
                                        Imóvel
                                        @if($sortBy == 'imovel')
                                            <span class="text-yellow-400">{!! $sortDir == 'asc' ? '&#9650;' : '&#9660;' !!}</span>
                                        @else
                                            <span class="opacity-0 group-hover:opacity-50 transition-opacity">&#9650;</span>
                                        @endif
                                    </a>
                                </th>

                                <th class="text-left py-5 px-8 font-black group">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_dir' => ($sortBy == 'created_at' && $sortDir == 'asc' ? 'desc' : 'asc')]) }}" class="flex items-center gap-2 hover:text-yellow-400 transition-colors">
                                        Cadastro
                                        @if($sortBy == 'created_at')
                                            <span class="text-yellow-400">{!! $sortDir == 'asc' ? '&#9650;' : '&#9660;' !!}</span>
                                        @else
                                            <span class="opacity-0 group-hover:opacity-50 transition-opacity">&#9650;</span>
                                        @endif
                                    </a>
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
                                               class="marco-checkbox w-5 h-5 rounded border-slate-300 text-[#003366] bg-white focus:ring-blue-400/30 cursor-pointer">
                                    </td>

                                    <td class="py-6 px-8">

                                        <div class="font-black text-lg tracking-tight">
                                            {{ $marco->credencial }}-{{ $marco->tipo }}-{{ str_pad($marco->numero, 4, '0', STR_PAD_LEFT) }}
                                        </div>

                                    </td>

                                    <td class="py-6 px-8">

                                        <div class="font-bold italic text-lg">
                                            {{ $marco->imovel }}
                                        </div>
                                        <div class="mt-2">
                                            <a href="{{ route('admin.marcos.mapa', ['imovel' => urlencode($marco->imovel), 'highlight' => $marco->id]) }}" class="text-xs bg-blue-100 text-blue-600 px-3 py-1 rounded-full font-bold shadow-sm hover:bg-blue-200 transition-colors" title="Ver no Mapa">
                                                <i class="fas fa-map-marker-alt mr-1"></i> Ver Mapa
                                            </a>
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

        

        

    </div>

    <!-- Hidden Bulk Delete Form -->
    <form id="bulk-delete-form" action="{{ route('admin.marcos.destroy-multiple') }}" method="POST" class="hidden">
        @csrf
        <div id="bulk-inputs-container"></div>
    </form>

    </div>
@endsection

@push('scripts')
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

        // WebSocket Integration
        document.addEventListener('DOMContentLoaded', () => {
            if (window.Echo) {
                window.Echo.private('marcos')
                    .listen('MarcosAtualizadosEvent', (e) => {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: e.message || 'Marcos atualizados pelo robô. Recarregando...',
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true
                        }).then(() => {
                            window.location.reload();
                        });
                    });
            }
        });

    </script>
@endpush
