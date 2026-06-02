?<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acerto — Getec Topografia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-thumb { background: #003366; border-radius: 999px; }
        ::-webkit-scrollbar-track { background: rgba(255,255,255,.05); }
        .glass { background: rgba(255,255,255,.12); backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px); }
        .glass-dark { background: rgba(0,51,102,.65); backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px); }
        .glow { box-shadow: 0 10px 40px rgba(0,0,0,.25); }
        .menu-gradient { background: linear-gradient(135deg, #003366 0%, #004A7C 100%); }
        .menu-gradient:hover { background: linear-gradient(135deg, #004A7C 0%, #005d9c 100%); }
        .animate-fade { animation: fade .25s ease; }
        @keyframes fade { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
        .tab-active { border-bottom: 3px solid #00E500; color: #00E500; }
        .input-style {
            width: 100%;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.15);
            color: #fff;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 14px;
            outline: none;
            transition: border-color .2s;
        }
        .input-style:focus { border-color: #00E500; background: rgba(255,255,255,.12); }
        .input-style::placeholder { color: rgba(255,255,255,.3); }
        .input-style option { background: #003366; color: #fff; }
        label.field-label { display: block; font-size: 10px; text-transform: uppercase; letter-spacing: 2px; color: rgba(255,255,255,.5); font-weight: 700; margin-bottom: 6px; }
        .swal2-container { z-index: 10000 !important; }
    </style>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">
</head>
<body class="min-h-screen overflow-x-hidden bg-[#E5E7EB] text-white">

<!-- BACKGROUND -->
<div class="fixed inset-0 -z-10">
    <img src="{{ asset('images/background-topo.jpg') }}" class="w-full h-full object-cover" alt="Background">
    <div class="absolute inset-0 bg-[#00111f]/55"></div>
    <div class="absolute inset-0" style="background: radial-gradient(circle at top left, rgba(0,74,124,.35), transparent 35%), radial-gradient(circle at bottom right, rgba(0,51,102,.4), transparent 35%);"></div>
</div>

<div x-data="{
        tab: localStorage.getItem('acertoTab') || 'contrato',
        init() {
            this.$watch('tab', value => localStorage.setItem('acertoTab', value))
        },
        modalAssinar: false,
        modalAssinarRecibo: false,
        contratoIdAssinar: null,
        assinanteNome: '',
        signStep: 'choose',
        documentoAssinado: false,
        modalListarRecibos: false,
        recibosContratoAtual: [],
        temReciboEntrada: false,
        valorEntradaContrato: 0,
        valorTotal: '',
        valorRecebido: '',
        descricaoPagamento: '',
        contratoIdRecibo: null,
        abrirRecibos(contratoId, recibos, valorEntrada, valorServico, sugestaoParcela, descParcela) {
            this.contratoIdRecibo = contratoId;
            this.recibosContratoAtual = JSON.parse(recibos);
            this.valorEntradaContrato = parseFloat(valorEntrada);
            this.temReciboEntrada = this.recibosContratoAtual.some(r => r.descricao.includes('Sinal / Entrada'));
            this.valorTotal = valorServico;
            this.valorRecebido = sugestaoParcela;
            this.descricaoPagamento = descParcela;
            this.modalListarRecibos = true;
        },
        nomeProprietario: '{{ old('nome_proprietario') }}',
        cpfProprietario: '{{ old('cpf_proprietario') }}',
        buscandoCpf: false,
        buscarCpf() {
            let cpf = this.cpfProprietario.replace(/\D/g, '');
            if (cpf.length === 11) {
                this.buscandoCpf = true;
                fetch('{{ route('admin.acerto.buscaCpf') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ cpf: cpf })
                })
                .then(r => r.json())
                .then(data => {
                    this.buscandoCpf = false;
                    if (data.encontrado) {
                        this.nomeProprietario = data.nome;
                    }
                })
                .catch(() => { this.buscandoCpf = false; });
            }
        },
        nomeContratado: '{{ old('nome_contratado', 'Getec Topografia & Georreferenciamento') }}',
        cpfContratado: '{{ old('cpf_cnpj_contratado') }}',
        buscandoCpfContratado: false,
        buscarCpfContratado() {
            let cpf = this.cpfContratado.replace(/\D/g, '');
            if (cpf.length === 11) {
                this.buscandoCpfContratado = true;
                fetch('{{ route('admin.acerto.buscaCpf') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ cpf: cpf })
                })
                .then(r => r.json())
                .then(data => {
                    this.buscandoCpfContratado = false;
                    if (data.encontrado) {
                        this.nomeContratado = data.nome;
                    }
                })
                .catch(() => { this.buscandoCpfContratado = false; });
            }
        }
     }"
     class="relative w-full max-w-7xl mx-auto px-4 md:px-8 py-8 animate-fade">

    <!-- HEADER -->
    <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8 mb-12">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-4 group w-fit">
            <div class="relative flex items-center justify-center w-20 h-20 rounded-2xl bg-white shadow-2xl transition group-hover:scale-105">
                <img src="{{ asset('images/logo-icon.png') }}" alt="Getec" class="w-full h-full object-contain p-2">
            </div>
            <div class="relative flex items-center h-14 px-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                <img src="{{ asset('images/logo-text.png') }}" alt="Getec Topografia" class="relative z-10 h-8 w-auto">
            </div>
        </a>
        <div class="flex flex-wrap items-center gap-4">
            <a href="{{ route('dashboard') }}" class="glass glow px-5 py-3 rounded-2xl border border-white/10 hover:bg-white/10 transition font-black uppercase text-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Dashboard
            </a>
        </div>
    </header>

    <!-- TITLE -->
    <div class="mb-8">
        <div class="inline-flex items-center gap-3 glass glow rounded-3xl px-8 py-5 border border-white/10">
            <div class="w-3 h-12 rounded-full bg-[#00E500]"></div>
            <div>
                <p class="uppercase tracking-[4px] text-xs text-white/60">Gestão</p>
                <h1 class="text-3xl md:text-4xl font-black italic uppercase tracking-tight">Acerto</h1>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-[20px] bg-emerald-500/15 border border-emerald-500/30 p-4 text-emerald-100 animate-fade">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-6 rounded-[20px] bg-rose-500/15 border border-rose-500/30 p-4 text-rose-100 animate-fade">{{ session('error') }}</div>
    @endif

    <!-- TABS -->
    <div class="glass glow rounded-[35px] border border-white/10 shadow-2xl overflow-hidden">

        <!-- Tab nav -->
        <div class="flex border-b border-white/10 px-8">
            <button @click="tab = 'contrato'"
                    :class="tab === 'contrato' ? 'tab-active' : 'text-white/50 hover:text-white'"
                    class="py-5 px-6 font-black uppercase text-sm tracking-wider transition mr-2">
                📄 Gerar Contrato
            </button>
            <button @click="tab = 'fila'"
                    :class="tab === 'fila' ? 'tab-active' : 'text-white/50 hover:text-white'"
                    class="py-5 px-6 font-black uppercase text-sm tracking-wider transition mr-2 flex items-center gap-2">
                🕐 Em Fila
                @if($emFila->count() > 0)
                    <span class="bg-yellow-500 text-black text-[10px] font-black px-2 py-0.5 rounded-full">{{ $emFila->count() }}</span>
                @endif
            </button>
            <button @click="tab = 'historico'"
                    :class="tab === 'historico' ? 'tab-active' : 'text-white/50 hover:text-white'"
                    class="py-5 px-6 font-black uppercase text-sm tracking-wider transition">
                📋 Histórico
            </button>
        </div>

        <!-- ═══ ABA: GERAR CONTRATO ═══════════════════════════════════════════ -->
        <div x-show="tab === 'contrato'" x-cloak class="p-8">
            <form action="{{ route('admin.acerto.store') }}" method="POST">
                @csrf

                <!-- Seção: Contratante -->
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-1 h-8 rounded-full bg-[#00E500]"></div>
                        <h2 class="text-xl font-black uppercase italic text-[#00E500]">Contratante — Proprietário</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                        <div>
                            <label class="field-label">Nome Completo *</label>
                            <input type="text" name="nome_proprietario" x-model="nomeProprietario" required placeholder="Ex: João da Silva" class="input-style">
                        </div>
                        <div>
                            <label class="field-label flex justify-between">
                                <span>CPF do Proprietário *</span>
                                <span x-show="buscandoCpf" class="text-yellow-400 normal-case tracking-normal">Buscando...</span>
                            </label>
                            <input type="text" name="cpf_proprietario" x-model="cpfProprietario" @input.debounce.500ms="buscarCpf" required placeholder="000.000.000-00" maxlength="14" class="input-style"
                                   oninput="this.value=this.value.replace(/\D/g,'').replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d{1,2})$/,'$1-$2')">
                        </div>
                        <div>
                            <label class="field-label">Nome do Imóvel / Área *</label>
                            <input type="text" name="nome_imovel" required placeholder="Ex: Fazenda Boa Esperança" class="input-style" value="{{ old('nome_imovel') }}">
                        </div>
                        <div class="md:col-span-2">
                            <label class="field-label">Localização / Endereço *</label>
                            <input type="text" name="localizacao" required placeholder="Ex: Zona Rural, Setor 04, Gleba 12" class="input-style" value="{{ old('localizacao') }}">
                        </div>
                        <div>
                            <label class="field-label">Município *</label>
                            <input type="text" name="municipio" required placeholder="Ex: Porto Velho" class="input-style" value="{{ old('municipio') }}">
                        </div>
                        <div>
                            <label class="field-label">Matrícula (opcional)</label>
                            <input type="text" name="matricula" placeholder="Nº da matrícula no cartório" class="input-style" value="{{ old('matricula') }}">
                        </div>
                        <div>
                            <label class="field-label">Código INCRA (opcional)</label>
                            <input type="text" name="codigo_incra" placeholder="Ex: RO-3900000-000000" class="input-style" value="{{ old('codigo_incra') }}">
                        </div>
                    </div>
                </div>

                <div class="border-t border-white/5 mb-8"></div>

                <!-- Seção: Serviço -->
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-1 h-8 rounded-full bg-blue-400"></div>
                        <h2 class="text-xl font-black uppercase italic text-blue-300">Serviço Contratado</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        <div class="md:col-span-2 lg:col-span-3" x-data="{ openTab: 'rural' }">
                            <label class="field-label mb-3">Tipos de Serviço (selecione um ou mais) *</label>
                            
                            <div class="space-y-3">
                                <!-- Rurais -->
                                <div class="border border-white/10 rounded-2xl overflow-hidden bg-white/5">
                                    <button type="button" @click="openTab = openTab === 'rural' ? null : 'rural'" class="w-full flex items-center justify-between p-4 hover:bg-white/5 transition">
                                        <span class="font-bold text-sm uppercase text-[#00E500]">🌿 Serviços Rurais</span>
                                        <span x-text="openTab === 'rural' ? '▲' : '▼'" class="text-xs text-white/50"></span>
                                    </button>
                                    <div x-show="openTab === 'rural'" x-collapse class="p-4 border-t border-white/10 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3 bg-black/20">
                                        @foreach(['Georreferenciamento de Imóvel Rural', 'Desmembramento de Área Rural', 'Medição e Divisão de Terras'] as $servico)
                                            <label class="flex items-center gap-3 p-3 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 cursor-pointer transition">
                                                <input type="checkbox" name="tipo_servico[]" value="{{ $servico }}" class="w-4 h-4 text-[#00E500] bg-transparent border-white/20 rounded focus:ring-[#00E500]" {{ (is_array(old('tipo_servico')) && in_array($servico, old('tipo_servico'))) ? 'checked' : '' }}>
                                                <span class="text-sm font-bold text-white/80 select-none">{{ $servico }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Urbanos -->
                                <div class="border border-white/10 rounded-2xl overflow-hidden bg-white/5">
                                    <button type="button" @click="openTab = openTab === 'urbano' ? null : 'urbano'" class="w-full flex items-center justify-between p-4 hover:bg-white/5 transition">
                                        <span class="font-bold text-sm uppercase text-blue-400">🏙️ Serviços Urbanos</span>
                                        <span x-text="openTab === 'urbano' ? '▲' : '▼'" class="text-xs text-white/50"></span>
                                    </button>
                                    <div x-show="openTab === 'urbano'" x-collapse class="p-4 border-t border-white/10 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3 bg-black/20">
                                        @foreach(['Levantamento Topográfico Urbano', 'Desmembramento de Área Urbana', 'Levantamento Topográfico para Regularização Fundiária'] as $servico)
                                            <label class="flex items-center gap-3 p-3 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 cursor-pointer transition">
                                                <input type="checkbox" name="tipo_servico[]" value="{{ $servico }}" class="w-4 h-4 text-blue-500 bg-transparent border-white/20 rounded focus:ring-blue-500" {{ (is_array(old('tipo_servico')) && in_array($servico, old('tipo_servico'))) ? 'checked' : '' }}>
                                                <span class="text-sm font-bold text-white/80 select-none">{{ $servico }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Gerais -->
                                <div class="border border-white/10 rounded-2xl overflow-hidden bg-white/5">
                                    <button type="button" @click="openTab = openTab === 'geral' ? null : 'geral'" class="w-full flex items-center justify-between p-4 hover:bg-white/5 transition">
                                        <span class="font-bold text-sm uppercase text-amber-400">📋 Serviços Gerais / Outros</span>
                                        <span x-text="openTab === 'geral' ? '▲' : '▼'" class="text-xs text-white/50"></span>
                                    </button>
                                    <div x-show="openTab === 'geral'" x-collapse class="p-4 border-t border-white/10 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3 bg-black/20">
                                        @foreach(['Levantamento Planimétrico', 'Levantamento Planialtimétrico', 'Elaboração de Memorial Descritivo', 'Retificação de Área', 'Implantação de Marcos e Picadas', 'Aerofotogrametria', 'Outro'] as $servico)
                                            <label class="flex items-center gap-3 p-3 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 cursor-pointer transition">
                                                <input type="checkbox" name="tipo_servico[]" value="{{ $servico }}" class="w-4 h-4 text-amber-500 bg-transparent border-white/20 rounded focus:ring-amber-500" {{ (is_array(old('tipo_servico')) && in_array($servico, old('tipo_servico'))) ? 'checked' : '' }}>
                                                <span class="text-sm font-bold text-white/80 select-none">{{ $servico }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mt-5">
                        <div>
                            <label class="field-label">Valor do Serviço (R$) *</label>
                            <input type="number" name="valor_servico" required min="0" step="0.01" placeholder="0.00" class="input-style" value="{{ old('valor_servico') }}">
                        </div>
                        <div>
                            <label class="field-label">Valor da Entrada (R$)</label>
                            <input type="number" name="valor_entrada" min="0" step="0.01" placeholder="0.00" class="input-style" value="{{ old('valor_entrada') }}">
                        </div>
                        <div>
                            <label class="field-label">Forma de Pagamento *</label>
                            <select name="forma_pagamento" required class="input-style">
                                <option value="À vista" {{ old('forma_pagamento') === 'À vista' ? 'selected' : '' }}>À vista</option>
                                <option value="Entrada / Sinal" {{ old('forma_pagamento') === 'Entrada / Sinal' ? 'selected' : '' }}>Entrada / Sinal</option>
                                <option value="Entrada + Parcelado" {{ old('forma_pagamento') === 'Entrada + Parcelado' ? 'selected' : '' }}>Entrada + Parcelado</option>
                                <option value="Parcelado em 2x" {{ old('forma_pagamento') === 'Parcelado em 2x' ? 'selected' : '' }}>Parcelado em 2x</option>
                                <option value="Parcelado em 3x" {{ old('forma_pagamento') === 'Parcelado em 3x' ? 'selected' : '' }}>Parcelado em 3x</option>
                                <option value="Parcelado em 4x" {{ old('forma_pagamento') === 'Parcelado em 4x' ? 'selected' : '' }}>Parcelado em 4x</option>
                                <option value="Parcelado em 5x" {{ old('forma_pagamento') === 'Parcelado em 5x' ? 'selected' : '' }}>Parcelado em 5x</option>
                                <option value="Parcelado em 6x" {{ old('forma_pagamento') === 'Parcelado em 6x' ? 'selected' : '' }}>Parcelado em 6x</option>
                                <option value="Outro" {{ old('forma_pagamento') === 'Outro' ? 'selected' : '' }}>Outro / Personalizado</option>
                            </select>
                        </div>
                        <div class="md:col-span-2 lg:col-span-3">
                            <label class="field-label">Detalhes do Pagamento (Opcional)</label>
                            <input type="text" name="detalhes_pagamento" placeholder="Ex: Entrada de R$ 2.000,00 e 3 parcelas de R$ 1.000,00" class="input-style" value="{{ old('detalhes_pagamento') }}">
                        </div>
                    </div>
                </div>

                <div class="border-t border-white/5 mb-8"></div>

                <!-- Seção: Responsável Técnico -->
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-1 h-8 rounded-full bg-amber-400"></div>
                        <h2 class="text-xl font-black uppercase italic text-amber-300">Responsável Técnico</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                        <div class="xl:col-span-2">
                            <label class="field-label flex justify-between">
                                <span>Nome do Responsável Técnico *</span>
                                <span x-show="buscandoCpfContratado" class="text-yellow-400 normal-case tracking-normal">Buscando...</span>
                            </label>
                            <input type="text" name="nome_contratado" required placeholder="Ex: João da Silva (Getec Topografia)" class="input-style" x-model="nomeContratado">
                        </div>
                        <div>
                            <label class="field-label">CPF do Responsável *</label>
                            <input type="text" name="cpf_cnpj_contratado" x-model="cpfContratado" @input.debounce.500ms="buscarCpfContratado" required placeholder="000.000.000-00" maxlength="14" class="input-style"
                                   oninput="this.value=this.value.replace(/\D/g,'').replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d{1,2})$/,'$1-$2')">
                        </div>
                        <div class="md:col-span-2 xl:col-span-3">
                            <label class="field-label">Endereço Completo *</label>
                            <input type="text" name="endereco_contratado" required placeholder="Rua, Nº, Bairro, Município/UF, CEP" class="input-style" value="{{ old('endereco_contratado') }}">
                        </div>
                    </div>
                </div>

                <!-- Botão -->
                <div class="flex justify-end pt-4 border-t border-white/10">
                    <button type="submit"
                            class="bg-[#00E500] hover:bg-green-500 text-black font-black uppercase px-10 py-4 rounded-2xl shadow-xl transition flex items-center gap-3 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Gerar Contrato em PDF
                    </button>
                </div>
            </form>
        </div>

        <!-- ═══ ABA: EM FILA ══════════════════════════════════════════════════ -->
        <div x-show="tab === 'fila'" x-cloak class="p-8">
            @if($emFila->isEmpty())
                <div class="py-20 text-center">
                    <div class="text-5xl mb-4">✅</div>
                    <p class="text-white/40 font-bold uppercase text-sm">Nenhum contrato aguardando imóvel.</p>
                </div>
            @else
                <p class="text-white/50 text-sm mb-6 uppercase tracking-wider font-bold">Esses contratos foram gerados mas ainda não possuem uma pasta de imóvel criada no sistema com o CPF correspondente.</p>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    @foreach($emFila as $c)
                        @php
                            $sugestaoParcela = '';
                            $descParcela = '';
                            if (preg_match('/Parcelado em (\d)x/i', $c->forma_pagamento, $matches)) {
                                $qtd = (int)$matches[1];
                                if ($qtd > 0) {
                                    $sugestaoParcela = number_format(($c->valor_servico - ($c->valor_entrada ?? 0)) / $qtd, 2, ',', '.');
                                    $descParcela = "Pagamento da parcela 1 de {$qtd}";
                                }
                            }
                        @endphp
                        <div class="glass border border-yellow-500/20 rounded-3xl p-6">
                            <div class="flex items-start justify-between gap-4 mb-4">
                                <div>
                                    <span class="text-[10px] uppercase tracking-widest text-yellow-400 font-bold">Em Fila &middot; {{ $c->codigo_contrato }}</span>
                                    <h3 class="text-lg font-black text-white uppercase mt-1">{{ $c->nome_imovel }}</h3>
                                    <p class="text-sm text-white/50">{{ $c->nome_proprietario }} — CPF: {{ $c->cpfFormatado() }}</p>
                                </div>
                                <span class="bg-yellow-500/20 text-yellow-300 text-[10px] font-black px-3 py-1 rounded-full uppercase shrink-0">Aguardando</span>
                            </div>
                            <div class="text-xs text-white/40 mb-4">
                                Serviço: <span class="text-white/70 font-bold">{{ $c->tipo_servico }}</span> &nbsp;|&nbsp;
                                Valor: <span class="text-[#00E500] font-bold">{{ $c->valorFormatado() }}</span>
                            </div>
                            <div class="flex items-center gap-2 mt-4 flex-wrap">
                                <a href="{{ route('admin.acerto.visualizar', $c->id) }}" target="_blank"
                                   class="inline-flex items-center gap-2 bg-blue-600/80 hover:bg-blue-500 text-white px-4 py-2 rounded-xl font-black text-xs uppercase transition">
                                    👁️ Ver
                                </a>
                                <a href="{{ route('admin.acerto.download', $c->id) }}"
                                   class="inline-flex items-center gap-2 bg-[#003366] hover:bg-[#004A7C] text-white px-4 py-2 rounded-xl font-black text-xs uppercase transition">
                                    📥 PDF
                                </a>
                                @if(!$c->assinado_em)
                                    <button type="button" @click="modalAssinar = true; contratoIdAssinar = {{ $c->id }}; assinanteNome = '{{ addslashes($c->nome_contratado) }}'; signStep = 'choose'; documentoAssinado = false;"
                                            class="inline-flex items-center gap-2 bg-emerald-600/80 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl font-black text-xs uppercase transition">
                                        ✍️ Assinar
                                    </button>
                                @endif
                                <button type="button" @click="abrirRecibos({{ $c->id }}, '{{ json_encode($c->recibos) }}', {{ $c->valor_entrada ?? 0 }}, '{{ number_format($c->valor_servico, 2, ',', '.') }}', '{{ $sugestaoParcela }}', '{{ $descParcela }}')" class="inline-flex items-center gap-2 bg-purple-600/80 hover:bg-purple-500 text-white px-4 py-2 rounded-xl font-black text-xs uppercase transition">
                                    🧾 Recibos
                                </button>
                                <button type="button" onclick="confirmDelete({{ $c->id }})" class="inline-flex items-center gap-2 bg-red-600/80 hover:bg-red-500 text-white px-4 py-2 rounded-xl font-black text-xs uppercase transition">
                                    🗑 Excluir
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- ═══ ABA: HISTÓRICO ════════════════════════════════════════════════ -->
        <div x-show="tab === 'historico'" x-cloak class="p-8">
            @if($vinculados->isEmpty())
                <div class="py-20 text-center">
                    <div class="text-5xl mb-4">📋</div>
                    <p class="text-white/40 font-bold uppercase text-sm">Nenhum contrato vinculado ainda.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($vinculados as $c)
                        @php
                            $sugestaoParcela = '';
                            $descParcela = '';
                            if (preg_match('/Parcelado em (\d)x/i', $c->forma_pagamento, $matches)) {
                                $qtd = (int)$matches[1];
                                if ($qtd > 0) {
                                    $sugestaoParcela = number_format(($c->valor_servico - ($c->valor_entrada ?? 0)) / $qtd, 2, ',', '.');
                                    $descParcela = "Pagamento da parcela 1 de {$qtd}";
                                }
                            }
                        @endphp
                        <div class="glass border border-white/5 rounded-3xl p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-3 flex-wrap mb-1">
                                    <span class="text-[10px] uppercase tracking-widest text-[#00E500] font-bold">{{ $c->codigo_contrato }}</span>
                                    @if($c->admin_assinado_em && $c->assinado_em)
                                        <span class="bg-emerald-500/20 text-emerald-300 text-[10px] font-black px-2 py-0.5 rounded-full">✔ Totalmente Assinado</span>
                                    @elseif($c->admin_assinado_em || $c->assinado_em)
                                        <span class="bg-yellow-500/20 text-yellow-300 text-[10px] font-black px-2 py-0.5 rounded-full">⏳ Assinatura Parcial</span>
                                    @else
                                        <span class="bg-white/10 text-white/50 text-[10px] font-black px-2 py-0.5 rounded-full">Pendente assinatura</span>
                                    @endif
                                    @if($c->pasta)
                                        <span class="bg-blue-500/20 text-blue-300 text-[10px] font-black px-2 py-0.5 rounded-full">📁 Vinculado</span>
                                    @endif
                                </div>
                                <h3 class="text-base font-black text-white uppercase truncate">{{ $c->nome_imovel }}</h3>
                                <p class="text-xs text-white/50 truncate">{{ $c->nome_proprietario }} · {{ $c->tipo_servico }} · {{ $c->valorFormatado() }}</p>
                                @if($c->pasta)
                                    <a href="{{ route('admin.pastas.show', $c->pasta_id) }}" class="text-[10px] text-blue-400 hover:text-blue-300 transition mt-1 inline-block">→ Ver pasta: {{ $c->pasta->nome }}</a>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 shrink-0 flex-wrap">
                                <a href="{{ route('admin.acerto.visualizar', $c->id) }}" target="_blank"
                                   class="bg-blue-600/80 hover:bg-blue-500 text-white px-4 py-2 rounded-xl font-black text-xs uppercase transition flex items-center gap-1.5">
                                    👁️ Ver
                                </a>
                                <a href="{{ route('admin.acerto.download', $c->id) }}"
                                   class="bg-[#003366] hover:bg-[#004A7C] text-white px-4 py-2 rounded-xl font-black text-xs uppercase transition flex items-center gap-1.5">
                                    📥 PDF
                                </a>
                                @if(!$c->admin_assinado_em)
                                    <button @click="modalAssinar = true; contratoIdAssinar = {{ $c->id }}; assinanteNome = '{{ addslashes($c->nome_contratado) }}'; signStep = 'choose'; documentoAssinado = false;"
                                            class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl font-black text-xs uppercase transition flex items-center gap-1.5">
                                        ✍️ Assinar
                                    </button>
                                @else
                                    <span class="text-xs text-white/30 italic">{{ $c->admin_assinado_em->format('d/m/Y H:i') }}</span>
                                @endif
                                <button type="button" @click="abrirRecibos({{ $c->id }}, '{{ json_encode($c->recibos) }}', {{ $c->valor_entrada ?? 0 }}, '{{ number_format($c->valor_servico, 2, ',', '.') }}', '{{ $sugestaoParcela }}', '{{ $descParcela }}')" class="bg-purple-600/80 hover:bg-purple-500 text-white px-3 py-2 rounded-xl font-black text-xs uppercase transition flex items-center gap-1.5 ml-2">
                                    🧾 Recibos
                                </button>
                                <button type="button" onclick="confirmDelete({{ $c->id }})" class="bg-red-600/80 hover:bg-red-500 text-white px-3 py-2 rounded-xl font-black text-xs uppercase transition flex items-center gap-1.5 ml-2">
                                    🗑
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6">{{ $vinculados->links() }}</div>
            @endif
        </div>

<!-- MODAL ASSINAR -->
<template x-teleport="body">
    <div x-show="modalAssinar" x-cloak
         class="fixed inset-0 z-[9999] bg-black/80 backdrop-blur-sm overflow-y-auto p-4 flex justify-center items-center">
        
        <div class="bg-[#0c1a24] border border-white/10 rounded-[35px] p-8 shadow-2xl max-w-md w-full text-white text-left" @click.away="modalAssinar = false">
            
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-black uppercase italic text-[#00E500]">
                    Assinador Digital
                </h3>
                <button @click="modalAssinar = false" class="text-white/40 hover:text-white text-2xl leading-none">
                    ×
                </button>
            </div>

            <!-- Formulário que será submetido -->
            <form :action="`/acerto/${contratoIdAssinar}/assinar`" method="POST" id="formAssinaturaAdmin">
                @csrf
                <input type="hidden" name="assinante_nome" x-model="assinanteNome">

                <!-- Passo 1: Escolha do Método -->
                <div x-show="signStep === 'choose'" class="space-y-4">
                    <p class="text-sm text-white/75 mb-4">Escolha a forma de assinatura eletrônica para validar este contrato:</p>
                    
                    <button type="button" @click="signStep = 'loading'; setTimeout(() => { signStep = 'cert-list'; }, 2000)" 
                            class="w-full text-left bg-white/5 hover:bg-white/10 border border-white/10 hover:border-[#00E500] p-4 rounded-2xl transition flex items-start gap-4">
                        <div class="text-2xl mt-1">🔑</div>
                        <div class="flex-1">
                            <h4 class="font-bold text-sm text-white">Certificado Digital ICP-Brasil (A3)</h4>
                            <p class="text-xs text-white/50 mt-0.5 font-normal">Assine usando seu token físico conectado ao computador.</p>
                        </div>
                    </button>

                    <button type="button" @click="signStep = 'sem-token'" 
                            class="w-full text-left bg-white/5 hover:bg-white/10 border border-white/10 hover:border-[#00E500] p-4 rounded-2xl transition flex items-start gap-4 mt-3">
                        <div class="text-2xl mt-1">✍️</div>
                        <div class="flex-1">
                            <h4 class="font-bold text-sm text-white">Assinatura Eletrônica Simples</h4>
                            <p class="text-xs text-white/50 mt-0.5 font-normal">Assinar utilizando os dados cadastrados no contrato (Nome e CPF).</p>
                        </div>
                    </button>
                </div>

                <!-- Passo 2: Carregando -->
                <div x-show="signStep === 'loading'" class="text-center py-8 space-y-4">
                    <div class="inline-block animate-spin rounded-full h-10 w-10 border-t-2 border-r-2 border-[#00E500]"></div>
                    <p class="text-sm text-white/75">Lendo token USB e buscando certificados conectados...</p>
                </div>

                <!-- Passo 3: PIN do Certificado -->
                <div x-show="signStep === 'cert-list'" class="space-y-4">
                    <p class="text-xs text-white/60 mb-2">Selecione o certificado para assinar:</p>
                    
                    <div class="bg-white/5 border border-[#00E500] p-4 rounded-2xl flex items-center gap-3">
                        <input type="radio" checked id="certAdmin" class="accent-[#00E500]">
                        <label for="certAdmin" class="cursor-pointer">
                            <h5 class="font-bold text-xs text-white uppercase" x-text="assinanteNome"></h5>
                            <p class="text-[10px] text-white/50 font-normal">Emissor: AC SRF v5</p>
                            <p class="text-[9px] text-[#00E500] mt-0.5 font-bold">ICP-Brasil A3 (Token Aladin)</p>
                        </label>
                    </div>

                    <div class="space-y-2 mt-4">
                        <label class="text-[10px] text-white/50 uppercase font-bold block">Digite o PIN do Token:</label>
                        <input type="password" placeholder="Digite o PIN do Token" class="w-full bg-black/40 border border-white/10 focus:border-[#00E500] rounded-xl px-4 py-2 text-sm text-white outline-none">
                    </div>

                    <div class="flex gap-2 mt-6">
                        <button type="button" @click="signStep = 'choose'" class="flex-1 bg-white/5 hover:bg-white/10 text-white py-3 rounded-xl font-bold uppercase text-xs transition">
                            Voltar
                        </button>
                        <button type="submit" @click="documentoAssinado = true" 
                                class="flex-1 bg-[#00E500] hover:bg-green-400 text-black py-3 rounded-xl font-black uppercase text-xs transition">
                            Confirmar Assinatura
                        </button>
                    </div>
                </div>

                <!-- Passo: Assinatura sem token -->
                <div x-show="signStep === 'sem-token'" class="space-y-4">
                    <p class="text-xs text-white/60 mb-2">Confirmar assinatura com os dados do responsável do contrato:</p>
                    
                    <div class="bg-white/5 border border-[#00E500] p-4 rounded-2xl flex items-center gap-3">
                        <div class="flex-1">
                            <h5 class="font-bold text-xs text-white uppercase" x-text="assinanteNome || 'Responsável Técnico'"></h5>
                            <p class="text-[10px] text-white/50 font-normal mt-1">Assinatura Eletrônica Interna</p>
                        </div>
                    </div>

                    <div class="flex gap-2 mt-6">
                        <button type="button" @click="signStep = 'choose'" class="flex-1 bg-white/5 hover:bg-white/10 text-white py-3 rounded-xl font-bold uppercase text-xs transition">
                            Voltar
                        </button>
                        <button type="submit" @click="documentoAssinado = true" 
                                class="flex-1 bg-[#00E500] hover:bg-green-400 text-black py-3 rounded-xl font-black uppercase text-xs transition">
                            Confirmar Assinatura
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<!-- MODAL ASSINAR RECIBO (TOKEN) -->
<template x-teleport="body">
    <div x-show="modalAssinarRecibo" x-cloak
         class="fixed inset-0 z-[99999] bg-black/80 backdrop-blur-sm overflow-y-auto p-4 flex justify-center items-center">
        
        <div class="bg-[#0c1a24] border border-white/10 rounded-[35px] p-8 shadow-2xl max-w-md w-full text-white text-left" @click.away="modalAssinarRecibo = false">
            
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-black uppercase italic text-[#00E500]">
                    Assinador Digital (Recibo)
                </h3>
                <button @click="modalAssinarRecibo = false" class="text-white/40 hover:text-white text-2xl leading-none">
                    ×
                </button>
            </div>

            <!-- Passo 1: Escolha do Método -->
            <div x-show="signStep === 'choose'" class="space-y-4">
                <p class="text-sm text-white/75 mb-4">Escolha a forma de assinatura eletrônica para emitir o recibo:</p>
                
                <button type="button" @click="signStep = 'loading'; setTimeout(() => { signStep = 'cert-list'; }, 2000)" 
                        class="w-full text-left bg-white/5 hover:bg-white/10 border border-white/10 hover:border-[#00E500] p-4 rounded-2xl transition flex items-start gap-4">
                    <div class="text-2xl mt-1">🔑</div>
                    <div class="flex-1">
                        <h4 class="font-bold text-sm text-white">Certificado Digital ICP-Brasil (A3)</h4>
                        <p class="text-xs text-white/50 mt-0.5 font-normal">Assine usando seu token físico conectado ao computador.</p>
                    </div>
                </button>

                <button type="button" @click="signStep = 'sem-token'" 
                        class="w-full text-left bg-white/5 hover:bg-white/10 border border-white/10 hover:border-[#00E500] p-4 rounded-2xl transition flex items-start gap-4 mt-3">
                    <div class="text-2xl mt-1">✍️</div>
                    <div class="flex-1">
                        <h4 class="font-bold text-sm text-white">Assinatura Eletrônica Simples</h4>
                        <p class="text-xs text-white/50 mt-0.5 font-normal">Assinar utilizando os dados cadastrados no contrato (Nome e CPF).</p>
                    </div>
                </button>
            </div>

            <!-- Passo 2: Carregando -->
            <div x-show="signStep === 'loading'" class="text-center py-8 space-y-4">
                <div class="inline-block animate-spin rounded-full h-10 w-10 border-t-2 border-r-2 border-[#00E500]"></div>
                <p class="text-sm text-white/75">Lendo token USB e buscando certificados conectados...</p>
            </div>

            <!-- Passo 3: PIN do Certificado -->
            <div x-show="signStep === 'cert-list'" class="space-y-4">
                <p class="text-xs text-white/60 mb-2">Selecione o certificado para assinar o Recibo:</p>
                
                <div class="bg-white/5 border border-[#00E500] p-4 rounded-2xl flex items-center gap-3">
                    <input type="radio" checked id="certAdminRecibo" class="accent-[#00E500]">
                    <label for="certAdminRecibo" class="cursor-pointer">
                        <h5 class="font-bold text-xs text-white uppercase" x-text="assinanteNome"></h5>
                        <p class="text-[10px] text-white/50 font-normal">Emissor: AC SRF v5</p>
                        <p class="text-[9px] text-[#00E500] mt-0.5 font-bold">ICP-Brasil A3 (Token Aladin)</p>
                    </label>
                </div>

                <div class="space-y-2 mt-4">
                    <label class="text-[10px] text-white/50 uppercase font-bold block">Digite o PIN do Token:</label>
                    <input type="password" placeholder="Digite o PIN do Token" class="w-full bg-black/40 border border-white/10 focus:border-[#00E500] rounded-xl px-4 py-2 text-sm text-white outline-none">
                </div>

                <div class="flex gap-2 mt-6">
                    <button type="button" @click="signStep = 'choose'" class="flex-1 bg-white/5 hover:bg-white/10 text-white py-3 rounded-xl font-bold uppercase text-xs transition">
                        Voltar
                    </button>
                    <button type="button" @click="documentoAssinado = true; document.getElementById('formGerarRecibo').submit();" 
                            class="flex-1 bg-[#00E500] hover:bg-green-400 text-black py-3 rounded-xl font-black uppercase text-xs transition">
                        Confirmar Assinatura
                    </button>
                </div>
            </div>

            <!-- Passo: Assinatura sem token -->
            <div x-show="signStep === 'sem-token'" class="space-y-4">
                <p class="text-xs text-white/60 mb-2">Confirmar assinatura com os dados do responsável do contrato:</p>
                
                <div class="bg-white/5 border border-[#00E500] p-4 rounded-2xl flex items-center gap-3">
                    <div class="flex-1">
                        <h5 class="font-bold text-xs text-white uppercase" x-text="assinanteNome || 'Responsável Técnico'"></h5>
                        <p class="text-[10px] text-white/50 font-normal mt-1">Assinatura Eletrônica Interna</p>
                    </div>
                </div>

                <div class="flex gap-2 mt-6">
                    <button type="button" @click="signStep = 'choose'" class="flex-1 bg-white/5 hover:bg-white/10 text-white py-3 rounded-xl font-bold uppercase text-xs transition">
                        Voltar
                    </button>
                    <button type="button" @click="documentoAssinado = true; document.getElementById('formGerarRecibo').submit();" 
                            class="flex-1 bg-[#00E500] hover:bg-green-400 text-black py-3 rounded-xl font-black uppercase text-xs transition">
                        Confirmar Assinatura
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- MODAL RECIBOS (LISTA E GERAR) -->
<template x-teleport="body">
    <div x-show="modalListarRecibos" x-cloak
         class="fixed inset-0 z-[9999] bg-black/70 backdrop-blur-sm overflow-y-auto p-4 flex justify-center items-start pt-10 pb-20">
    <div class="glass-dark rounded-[35px] border border-white/10 shadow-2xl w-full max-w-lg p-8 text-center mt-10 relative">
        <button @click="modalListarRecibos = false" class="absolute top-6 right-6 text-white/50 hover:text-white transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div class="text-4xl mb-4">🧾</div>
        <h2 class="text-2xl font-black uppercase italic mb-6">Recibos do Contrato</h2>

        <!-- LISTA DE RECIBOS GERADOS -->
        <div class="mb-8 text-left" x-show="recibosContratoAtual.length > 0">
            <h3 class="text-sm font-bold text-white/70 uppercase mb-3 border-b border-white/10 pb-2">Recibos Salvos</h3>
            <div class="space-y-3 max-h-48 overflow-y-auto pr-2">
                <template x-for="recibo in recibosContratoAtual" :key="recibo.id">
                    <div class="flex items-center justify-between bg-white/5 border border-white/10 p-3 rounded-xl">
                        <div>
                            <p class="text-sm font-bold text-white" x-text="recibo.descricao"></p>
                            <p class="text-xs text-white/50" x-text="'R$ ' + parseFloat(recibo.valor_recebido).toLocaleString('pt-BR', {minimumFractionDigits: 2})"></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a :href="`/acerto/recibo/${recibo.id}/visualizar`" target="_blank" class="p-2 bg-blue-600/80 hover:bg-blue-500 rounded-lg text-white transition" title="Visualizar">
                                👁️
                            </a>
                            <button @click="confirmDeleteRecibo(recibo.id, $data)" class="p-2 bg-red-600/80 hover:bg-red-500 rounded-lg text-white transition" title="Excluir">
                                🗑
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- GERAR RECIBO DE ENTRADA -->
        <div class="mb-8" x-show="valorEntradaContrato > 0 && !temReciboEntrada">
            <form :action="`/acerto/${contratoIdRecibo}/recibo-entrada`" method="POST" target="_blank" @submit="setTimeout(() => window.location.reload(), 1000)">
                @csrf
                <button type="submit" class="w-full bg-pink-600 hover:bg-pink-500 text-white py-3 rounded-xl font-black uppercase transition text-sm flex items-center justify-center gap-2">
                    📥 Gerar Recibo de Entrada (Sinal)
                </button>
            </form>
        </div>

        <!-- GERAR NOVO RECIBO -->
        <div class="border-t border-white/10 pt-6">
            <h3 class="text-sm font-bold text-white/70 uppercase mb-4 text-left">Gerar Novo Recibo</h3>
            <form :action="`/acerto/${contratoIdRecibo}/recibo`" method="POST" class="space-y-4" id="formGerarRecibo">
                @csrf
                
                <div class="text-left mb-4">
                    <label class="field-label text-white/70">Valor Total do Serviço</label>
                    <input type="text" name="valor_total" x-model="valorTotal"
                           required placeholder="Ex: 5.000,00"
                           class="w-full bg-white/10 border border-white/20 text-white rounded-xl py-3 px-4 text-sm font-bold outline-none focus:border-[#00E500]">
                </div>

                <div class="text-left mb-4">
                    <label class="field-label text-white/70">Valor Recebido Neste Ato</label>
                    <input type="text" name="valor_recebido" x-model="valorRecebido"
                           required placeholder="Ex: 1.000,00"
                           class="w-full bg-white border border-white/20 text-[#003366] rounded-xl py-3 px-4 text-base font-black outline-none shadow-inner">
                </div>

                <div class="text-left mb-6">
                    <label class="field-label text-white/70">Descrição / Referência</label>
                    <input type="text" name="descricao_pagamento" x-model="descricaoPagamento"
                           required placeholder="Ex: Pagamento da 1ª Parcela"
                           class="w-full bg-white/10 border border-white/20 text-white rounded-xl py-3 px-4 text-sm font-bold outline-none focus:border-[#00E500]">
                </div>

                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-500 text-white py-3 rounded-xl font-black uppercase transition text-sm">
                    Gerar e Salvar Recibo
                </button>
            </form>
        </div>
    </div>
</div>
</template>

    </div>
</div>

<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Excluir Contrato?',
        text: 'Esta ação não poderá ser desfeita. O contrato e seu PDF serão removidos.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#003366',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/acerto/${id}`;
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            form.appendChild(methodField);
            
            const tokenField = document.createElement('input');
            tokenField.type = 'hidden';
            tokenField.name = '_token';
            tokenField.value = '{{ csrf_token() }}';
            form.appendChild(tokenField);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function confirmDeleteRecibo(id, alpineData) {
    Swal.fire({
        title: 'Excluir Recibo?',
        text: 'O arquivo PDF do recibo será excluído permanentemente.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#003366',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/acerto/recibo/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    '_method': 'DELETE'
                })
            }).then(response => {
                if(response.ok) {
                    Swal.fire('Excluído!', 'O recibo foi excluído.', 'success');
                    alpineData.recibosContratoAtual = alpineData.recibosContratoAtual.filter(r => r.id !== id);
                } else {
                    Swal.fire('Erro!', 'Não foi possível excluir o recibo.', 'error');
                }
            }).catch(() => {
                Swal.fire('Erro!', 'Erro de conexão.', 'error');
            });
        }
    });
}
</script>

</body>
</html>

