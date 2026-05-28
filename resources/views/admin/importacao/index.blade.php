@extends('layouts.app')

@section('content')
<!-- Forçar o carregamento do Alpine via CDN para garantir funcionamento caso o Vite falhe -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="importadorPastas()">

    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-[#003366]">Importação de Trabalhos</h1>
            <p class="mt-1 text-sm text-[#003366]/70 font-medium">
                Sincronize arquivos para o sistema em lote.
            </p>
        </div>
    </div>

    <!-- TABS -->
    <div class="flex gap-4 mb-6">
        <button @click="aba = 'nuvem'" :class="aba === 'nuvem' ? 'bg-[#003366] text-white shadow-lg' : 'bg-white text-[#003366] hover:bg-slate-50'" class="px-6 py-3 rounded-2xl font-black uppercase text-sm transition">
            ☁️ Via Navegador (Nuvem)
        </button>
        <button @click="aba = 'local'" :class="aba === 'local' ? 'bg-[#003366] text-white shadow-lg' : 'bg-white text-[#003366] hover:bg-slate-50'" class="px-6 py-3 rounded-2xl font-black uppercase text-sm transition">
            💻 Via Caminho Local (PowerShell)
        </button>
        <button @click="aba = 'sync'" :class="aba === 'sync' ? 'bg-[#003366] text-white shadow-lg' : 'bg-white text-[#003366] hover:bg-slate-50'" class="px-6 py-3 rounded-2xl font-black uppercase text-sm transition">
            🔄 Sincronização Automática
        </button>
    </div>

    <!-- Interface Principal -->
    <div class="bg-white rounded-[32px] shadow-sm border border-[#003366]/10 p-8 mb-8">
        
        <!-- ABA NUVEM -->
        <div x-show="aba === 'nuvem'" x-cloak>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-bold text-[#003366] mb-2 uppercase tracking-wide">
                        Ano
                    </label>
                    <input type="text" x-model="ano" :disabled="importando"
                           placeholder="Ex: 2026"
                           class="w-full rounded-2xl border-slate-200 focus:border-[#003366] px-5 py-4 text-[#003366] font-medium text-lg placeholder:text-slate-400">
                </div>
                <div>
                    <label class="block text-sm font-bold text-[#003366] mb-2 uppercase tracking-wide">
                        Categoria
                    </label>
                    <input type="text" x-model="categoria" :disabled="importando"
                           placeholder="Ex: Topografia"
                           class="w-full rounded-2xl border-slate-200 focus:border-[#003366] px-5 py-4 text-[#003366] font-medium text-lg placeholder:text-slate-400">
                </div>
            </div>

            <div class="mb-8 border-2 border-dashed border-slate-300 rounded-[32px] p-8 text-center" :class="importando ? 'opacity-50' : ''">
                <input type="file" id="folderInput" webkitdirectory directory multiple class="hidden" @change="prepararArquivos" :disabled="importando">
                <label for="folderInput" class="cursor-pointer inline-flex flex-col items-center">
                    <div class="w-20 h-20 rounded-full bg-blue-50 flex items-center justify-center text-4xl mb-4 text-[#003366]">
                        📁
                    </div>
                    <span class="text-xl font-black text-[#003366] mb-2">Clique para selecionar uma pasta do Imóvel</span>
                    <span class="text-slate-500 text-sm">O navegador lerá todas as subpastas automaticamente.</span>
                </label>
                
                <div x-show="arquivos.length > 0" x-cloak class="mt-6 p-4 bg-emerald-50 rounded-2xl border border-emerald-100">
                    <p class="text-emerald-700 font-bold"><span x-text="arquivos.length"></span> arquivos encontrados e prontos para envio.</p>
                </div>
            </div>

            <button @click="iniciarUploadWeb" :disabled="!ano || !categoria || arquivos.length === 0 || importando || importacaoConcluida"
                    class="w-full px-8 py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-black uppercase tracking-wide transition shadow-lg shadow-emerald-600/30 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                🚀 Iniciar Sincronização em Nuvem
            </button>
        </div>

        <!-- ABA LOCAL -->
        <div x-show="aba === 'local'" x-cloak>
            <div class="mb-8">
                <label class="block text-sm font-bold text-[#003366] mb-2 uppercase tracking-wide">
                    Caminho da Pasta do Ano (Servidor Local)
                </label>
                <div class="flex flex-col sm:flex-row gap-4">
                    <input type="text" x-model="caminho" :disabled="buscando || importando"
                           placeholder="Ex: \\Getec-pc\f\Trabalhos Getec V2\2026"
                           class="flex-1 rounded-2xl border-slate-200 focus:border-[#003366] px-5 py-4 text-[#003366] font-medium text-lg placeholder:text-slate-400">
                    
                    <button @click="iniciarBusca" :disabled="!caminho || buscando || importando"
                            class="px-8 py-4 bg-[#005B96] hover:bg-[#004A7C] text-white rounded-2xl font-black uppercase tracking-wide transition shadow-lg shadow-[#005B96]/30 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        Buscar Pastas
                    </button>
                </div>
                <p x-show="erro" x-cloak x-text="erro" class="mt-3 text-red-500 font-bold text-sm"></p>
            </div>

            <!-- Resultados da Busca Local -->
            <div x-show="pastas.length > 0 && !importando && !importacaoConcluida" x-cloak x-transition class="bg-slate-50 rounded-2xl p-6 border border-slate-200">
                <h3 class="text-xl font-black text-[#003366] mb-2">
                    <span x-text="pastas.length"></span> Imóveis encontrados
                </h3>
                
                <button @click="iniciarImportacaoLocal"
                        class="mt-4 px-8 py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-black uppercase transition shadow-lg">
                    Iniciar Sincronização Local
                </button>
            </div>
        </div>

        <!-- ABA SYNC AUTOMATICO -->
        <div x-show="aba === 'sync'" x-cloak>
            <div class="mb-8 p-6 bg-slate-50 border border-slate-200 rounded-3xl">
                <h3 class="text-xl font-black text-[#003366] mb-4">Configurações do Robô Sincronizador</h3>
                <p class="text-sm text-slate-600 mb-6">O robô roda no plano de fundo (a cada hora, ou conforme agendado no Windows) para buscar arquivos novos nas pastas de serviços <strong class="text-[#003366]">Pendentes</strong> e importá-los silenciosamente.</p>
                
                <div class="flex items-center gap-4 mb-6">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="syncAtivo" class="sr-only peer">
                        <div class="w-14 h-7 bg-slate-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-600"></div>
                        <span class="ml-3 text-lg font-bold" :class="syncAtivo ? 'text-emerald-700' : 'text-slate-500'" x-text="syncAtivo ? 'Sincronização LIGADA' : 'Sincronização PAUSADA'"></span>
                    </label>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-bold text-[#003366] mb-2 uppercase tracking-wide">
                        Caminho Raiz da Rede (Onde ficam os Anos)
                    </label>
                    <input type="text" x-model="syncRootDir"
                           placeholder="Ex: \\Getec-pc\f\Trabalhos Getec V2"
                           class="w-full rounded-2xl border-slate-200 focus:border-[#003366] px-5 py-4 text-[#003366] font-medium text-lg placeholder:text-slate-400">
                </div>

                <div class="flex flex-col sm:flex-row gap-4">
                    <button @click="salvarConfiguracaoSync" :disabled="salvandoSync"
                            class="px-8 py-4 bg-[#003366] hover:bg-[#002244] text-white rounded-2xl font-black uppercase tracking-wide transition shadow-lg disabled:opacity-50 flex items-center justify-center gap-2">
                        💾 Salvar Configurações
                    </button>
                    
                    <button @click="sincronizarAgora" :disabled="syncNowExecutando"
                            class="px-8 py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-black uppercase tracking-wide transition shadow-lg disabled:opacity-50 flex items-center justify-center gap-2">
                        <span x-show="!syncNowExecutando">🔄 Sincronizar Tudo Agora</span>
                        <span x-show="syncNowExecutando">Sincronizando... Isso pode demorar.</span>
                    </button>
                </div>

                <!-- Barra de Progresso da Sincronização Local (Global) -->
                <div x-show="syncNowExecutando" x-cloak class="mt-6 bg-[#F5F7FA] rounded-2xl p-6 border border-[#003366]/10 space-y-2 animate-fade">
                    <div class="flex items-center justify-between text-xs font-bold">
                        <span class="text-slate-600 uppercase tracking-wider">Progresso da Varredura Física</span>
                        <span class="text-[#003366]" x-text="syncPercent + '% (' + syncCurrent + '/' + syncTotal + ')'">0%</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden">
                        <div class="bg-gradient-to-r from-[#003366] to-emerald-600 h-3 rounded-full transition-all duration-300 shadow-[0_0_8px_rgba(16,185,129,0.3)]" :style="'width: ' + syncPercent + '%'"></div>
                    </div>
                    <div class="text-[10px] text-slate-500 truncate" x-text="syncLastFile ? 'Importando: ' + syncLastFile : 'Contando e catalogando arquivos do disco...'"></div>
                </div>

                <p x-show="syncMsg" x-cloak x-text="syncMsg" class="mt-4 font-bold text-sm text-[#003366]"></p>
            </div>
        </div>

        <!-- Progresso da Importação -->
        <div x-show="importando || importacaoConcluida" x-cloak x-transition class="mt-8 bg-[#F5F7FA] rounded-2xl p-8 border border-[#003366]/10 relative overflow-hidden">
            
            <div class="flex items-center justify-between mb-4 relative z-10">
                <h3 class="text-2xl font-black text-[#003366]" x-text="importacaoConcluida ? 'Importação Concluída!' : 'Sincronizando arquivos...'"></h3>
                <span class="text-[#005B96] font-bold text-xl" x-text="Math.round(progresso) + '%'"></span>
            </div>

            <!-- Barra -->
            <div class="w-full bg-slate-200 rounded-full h-4 mb-6 relative z-10 overflow-hidden">
                <div class="bg-gradient-to-r from-[#003366] to-[#005B96] h-4 rounded-full transition-all duration-300" :style="'width: ' + progresso + '%'"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm font-medium text-slate-600 relative z-10">
                <div>
                    <span class="text-slate-400">Processando:</span> 
                    <span class="text-[#003366] font-bold truncate block" x-text="pastaAtualText"></span>
                </div>
                <div class="md:text-right">
                    <span class="text-slate-400">Progresso:</span>
                    <span class="text-[#003366] font-bold" x-text="processados + ' de ' + totalParaProcessar"></span>
                </div>
            </div>

            <div x-show="importacaoConcluida" class="mt-8 text-center relative z-10">
                <a href="{{ route('admin.pastas.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-[#003366] hover:bg-[#002244] text-white rounded-xl font-bold transition">
                    Ver Pastas
                </a>
            </div>
        </div>

    </div>

</div>

<script>
    function importadorPastas() {
        return {
            aba: 'nuvem',
            
            // Nuvem state
            ano: '',
            categoria: '',
            arquivos: [],
            
            // Local state
            caminho: '',
            pastas: [],
            
            // Shared state
            buscando: false,
            importando: false,
            importacaoConcluida: false,
            erro: '',
            
            // Sync state
            syncAtivo: {{ $syncAtivo === '1' ? 'true' : 'false' }},
            syncRootDir: '{{ addslashes($syncRootDir) }}',
            salvandoSync: false,
            syncNowExecutando: false,
            syncMsg: '',
            syncPercent: 0,
            syncCurrent: 0,
            syncTotal: 0,
            syncLastFile: '',
            
            // Progresso
            processados: 0,
            totalParaProcessar: 0,
            pastaAtualText: '-',

            get progresso() {
                if (this.totalParaProcessar === 0) return 0;
                return (this.processados / this.totalParaProcessar) * 100;
            },

            // --- FUNÇÕES SYNC ---
            async salvarConfiguracaoSync() {
                this.salvandoSync = true;
                this.syncMsg = 'Salvando...';
                try {
                    const response = await fetch('{{ route('admin.importacao.syncSettings') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            sync_ativo: this.syncAtivo ? '1' : '0',
                            sync_root_dir: this.syncRootDir
                        })
                    });
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.error);
                    this.syncMsg = data.message;
                } catch (error) {
                    this.syncMsg = 'Erro ao salvar: ' + error.message;
                }
                this.salvandoSync = false;
            },

            async sincronizarAgora() {
                this.syncNowExecutando = true;
                this.syncMsg = 'Iniciando varredura e sincronização, aguarde...';
                this.syncPercent = 0;
                this.syncCurrent = 0;
                this.syncTotal = 0;
                this.syncLastFile = '';

                let pollInterval;

                try {
                    const response = await fetch('{{ route('admin.importacao.syncNow') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.error || 'Erro ao iniciar sincronização');

                    // Iniciar polling de progresso
                    pollInterval = setInterval(async () => {
                        try {
                            const progressResp = await fetch('{{ route('admin.importacao.syncProgress') }}');
                            if (progressResp.ok) {
                                const progressData = await progressResp.json();
                                this.syncPercent = progressData.percentage || 0;
                                this.syncCurrent = progressData.current || 0;
                                this.syncTotal = progressData.total || 0;
                                this.syncLastFile = progressData.last_file || '';

                                if (progressData.status === 'done') {
                                    clearInterval(pollInterval);
                                    this.syncNowExecutando = false;
                                    this.syncMsg = 'Sincronização concluída com sucesso!';
                                } else if (progressData.status === 'error') {
                                    clearInterval(pollInterval);
                                    this.syncNowExecutando = false;
                                    this.syncMsg = 'Erro na sincronização: ' + (progressData.last_file || 'Ocorreu um erro.');
                                }
                            }
                        } catch (e) {
                            console.error('Erro ao buscar progresso:', e);
                        }
                    }, 800);

                } catch (error) {
                    if (pollInterval) clearInterval(pollInterval);
                    this.syncNowExecutando = false;
                    this.syncMsg = 'Erro na sincronização: ' + error.message;
                }
            },

            // --- FUNÇÕES NUVEM ---
            prepararArquivos(e) {
                this.arquivos = Array.from(e.target.files);
                this.erro = '';
            },

            async iniciarUploadWeb() {
                this.importando = true;
                this.importacaoConcluida = false;
                this.processados = 0;
                this.totalParaProcessar = this.arquivos.length;
                this.erro = '';

                // O caminho relativo vem como: "Lote 90/Documentos/doc.pdf"
                // Vamos extrair o nome do imóvel a partir do primeiro arquivo
                let imovelNome = 'Imovel Desconhecido';
                if (this.arquivos.length > 0 && this.arquivos[0].webkitRelativePath) {
                    imovelNome = this.arquivos[0].webkitRelativePath.split('/')[0];
                }

                // Processar em lotes de 1 para evitar timeout e limite do PHP
                for (let i = 0; i < this.arquivos.length; i++) {
                    const file = this.arquivos[i];
                    this.pastaAtualText = file.webkitRelativePath || file.name;
                    
                    const formData = new FormData();
                    formData.append('ano', this.ano);
                    formData.append('categoria', this.categoria);
                    formData.append('imovel', imovelNome);
                    formData.append('caminho_relativo', file.webkitRelativePath || file.name);
                    formData.append('arquivo', file);

                    try {
                        const response = await fetch('{{ route('admin.importacao.uploadWeb') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData
                        });

                        const data = await response.json();
                        if (!response.ok) {
                            console.error(`Erro ao processar ${file.name}:`, data.error);
                        }
                    } catch (error) {
                        console.error(`Erro de rede ao processar ${file.name}:`, error);
                    }

                    this.processados++;
                }

                this.importando = false;
                this.importacaoConcluida = true;
                this.pastaAtualText = 'Sincronização Finalizada';
            },

            // --- FUNÇÕES LOCAL ---
            async iniciarBusca() {
                this.erro = '';
                this.buscando = true;
                this.pastas = [];
                this.importacaoConcluida = false;
                this.processados = 0;

                try {
                    const response = await fetch('{{ route('admin.importacao.buscar') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ path: this.caminho })
                    });

                    const data = await response.json();
                    if (!response.ok) throw new Error(data.error);

                    this.pastas = data.pastas;
                    this.totalParaProcessar = this.pastas.length;
                } catch (error) {
                    this.erro = error.message;
                } finally {
                    this.buscando = false;
                }
            },

            async iniciarImportacaoLocal() {
                this.importando = true;
                this.importacaoConcluida = false;
                this.processados = 0;
                this.totalParaProcessar = this.pastas.length;

                for (let i = 0; i < this.pastas.length; i++) {
                    const pasta = this.pastas[i];
                    this.pastaAtualText = pasta.imovel;

                    try {
                        const response = await fetch('{{ route('admin.importacao.processar') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(pasta)
                        });
                        const data = await response.json();
                    } catch (error) {
                        console.error(`Erro de rede ao processar ${pasta.imovel}:`, error);
                    }
                    this.processados++;
                }

                this.importando = false;
                this.importacaoConcluida = true;
                this.pastaAtualText = 'Finalizado';
            }
        };
    }
</script>
@endsection
