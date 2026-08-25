@extends('layouts.admin')

@section('content')
<!-- Forçar o carregamento do Alpine via CDN para garantir funcionamento caso o Vite falhe -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="importadorPastas()">

    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white/90 backdrop-blur-md p-6 rounded-2xl border border-white shadow-sm inline-flex w-max pr-12">
        <div>
            <h1 class="text-3xl font-black text-slate-800">Importação de Trabalhos</h1>
            <p class="mt-1 text-sm text-slate-800/70 font-medium">
                Sincronize arquivos para o sistema em lote.
            </p>
        </div>
    </div>

    <!-- Interface Principal -->
    <div class="bg-white rounded-[32px] shadow-sm border border-slate-200 p-8 mb-8">
        
        <!-- CONTEUDO IMPORTACAO -->
        <div>
            <!-- Botões de Modo de Importação -->
            <div class="flex flex-wrap gap-4 mb-6">
                <button @click="tipoUpload = 'ano'" :class="tipoUpload === 'ano' ? 'bg-[#002244] text-white shadow-lg' : 'bg-white text-slate-800 border border-slate-200 hover:bg-slate-50'" class="px-6 py-3 rounded-xl font-bold uppercase text-xs transition">
                    1. Importar Ano Inteiro
                </button>
                <button @click="tipoUpload = 'categoria'" :class="tipoUpload === 'categoria' ? 'bg-[#002244] text-white shadow-lg' : 'bg-white text-slate-800 border border-slate-200 hover:bg-slate-50'" class="px-6 py-3 rounded-xl font-bold uppercase text-xs transition">
                    2. Importar Categoria Inteira
                </button>
                <button @click="tipoUpload = 'imovel'" :class="tipoUpload === 'imovel' ? 'bg-[#002244] text-white shadow-lg' : 'bg-white text-slate-800 border border-slate-200 hover:bg-slate-50'" class="px-6 py-3 rounded-xl font-bold uppercase text-xs transition">
                    3. Importar Imóvel Específico
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8" x-show="tipoUpload !== 'ano'">
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-2 uppercase tracking-wide">
                        Ano
                    </label>
                    <input type="text" x-model="ano" :disabled="importando"
                           placeholder="Ex: 2026"
                           class="w-full rounded-2xl border-slate-200 focus:border-[#003366] px-5 py-4 text-slate-800 font-medium text-lg placeholder:text-slate-400">
                </div>
                <div x-show="tipoUpload === 'imovel'">
                    <label class="block text-sm font-bold text-slate-800 mb-2 uppercase tracking-wide">
                        Categoria
                    </label>
                    <input type="text" x-model="categoria" :disabled="importando"
                           placeholder="Ex: Topografia"
                           class="w-full rounded-2xl border-slate-200 focus:border-[#003366] px-5 py-4 text-slate-800 font-medium text-lg placeholder:text-slate-400">
                </div>
            </div>

            <div class="mb-8 p-6 bg-slate-50 border border-slate-200 rounded-2xl">
                <label class="block text-sm font-bold text-slate-800 mb-4 uppercase tracking-wide">
                    Status do Serviço (Como os imóveis serão criados?)
                </label>
                <div class="flex flex-wrap gap-6">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="radio" x-model="statusServico" value="pronto" class="w-5 h-5 text-emerald-600 border-slate-300 focus:ring-emerald-600 cursor-pointer">
                        <span class="text-base font-bold text-slate-700 group-hover:text-emerald-700 transition">Pronto (Verde)</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="radio" x-model="statusServico" value="pendente" class="w-5 h-5 text-red-600 border-slate-300 focus:ring-red-600 cursor-pointer">
                        <span class="text-base font-bold text-slate-700 group-hover:text-red-700 transition">Pendente (Vermelho)</span>
                    </label>
                </div>
            </div>

            <div class="mb-8 border-2 border-dashed border-slate-300 rounded-[32px] p-8 text-center" :class="importando ? 'opacity-50' : ''">
                <input type="file" id="folderInput" webkitdirectory directory multiple class="hidden" @change="prepararArquivos" :disabled="importando">
                <label for="folderInput" class="cursor-pointer inline-flex flex-col items-center">
                    <div class="w-20 h-20 rounded-full bg-blue-50/50 border border-blue-100 flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-slate-400 group-hover:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>
                        </div>
                    <span class="text-xl font-black text-slate-800 mb-2" x-text="tipoUpload === 'ano' ? 'Selecione a pasta do Ano' : (tipoUpload === 'categoria' ? 'Selecione a pasta da Categoria' : 'Selecione a pasta do Imóvel')"></span>
                    <span class="text-slate-500 text-sm">O navegador lerá todas as subpastas automaticamente.</span>
                </label>
                
                <div x-show="arquivos.length > 0" x-cloak class="mt-6 p-4 bg-emerald-50 rounded-2xl border border-emerald-100">
                    <p class="text-emerald-700 font-bold"><span x-text="arquivos.length"></span> arquivos encontrados e prontos para envio.</p>
                </div>
            </div>

            <button @click="iniciarUploadWeb" :disabled="(tipoUpload !== 'ano' && !ano) || (tipoUpload === 'imovel' && !categoria) || arquivos.length === 0 || importando || importacaoConcluida"
                    class="w-full px-8 py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-black uppercase tracking-wide transition shadow-lg shadow-emerald-600/30 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg> Iniciar Sincronização em Nuvem
            </button>
        </div>



        <!-- ABA SYNC AUTOMATICO -->
        <div x-show="aba === 'sync'" x-cloak>
            <div class="mb-8 p-6 bg-slate-50 border border-slate-200 rounded-3xl">
                <h3 class="text-xl font-black text-slate-800 mb-4">Configurações do Robô Sincronizador</h3>
                <p class="text-sm text-slate-600 mb-6">O robô roda no plano de fundo (a cada hora, ou conforme agendado no Windows) para buscar arquivos novos nas pastas de serviços <strong class="text-slate-800">Pendentes</strong> e importá-los silenciosamente.</p>
                
                <div class="flex items-center gap-4 mb-6">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="syncAtivo" class="sr-only peer">
                        <div class="w-14 h-7 bg-slate-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-600"></div>
                        <span class="ml-3 text-lg font-bold" :class="syncAtivo ? 'text-emerald-700' : 'text-slate-500'" x-text="syncAtivo ? 'Sincronização LIGADA' : 'Sincronização PAUSADA'"></span>
                    </label>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-800 mb-2 uppercase tracking-wide">
                        Caminho Raiz da Rede (Onde ficam os Anos)
                    </label>
                    <input type="text" x-model="syncRootDir"
                           placeholder="Ex: \\Getec-pc\f\Trabalhos Getec V2"
                           class="w-full rounded-2xl border-slate-200 focus:border-[#003366] px-5 py-4 text-slate-800 font-medium text-lg placeholder:text-slate-400">
                </div>

                <div class="flex flex-col sm:flex-row gap-4">
                    <button @click="salvarConfiguracaoSync" :disabled="salvandoSync"
                            class="px-8 py-4 bg-[#002244] hover:bg-[#002244] text-white rounded-2xl font-black uppercase tracking-wide transition shadow-lg disabled:opacity-50 flex items-center justify-center gap-2">
                        💾 Salvar Configurações
                    </button>
                    
                    <button @click="sincronizarAgora" :disabled="syncNowExecutando"
                            class="px-8 py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-black uppercase tracking-wide transition shadow-lg disabled:opacity-50 flex items-center justify-center gap-2">
                        <span x-show="!syncNowExecutando">🔄 Sincronizar Tudo Agora</span>
                        <span x-show="syncNowExecutando">Sincronizando... Isso pode demorar.</span>
                    </button>
                </div>

                <!-- Barra de Progresso da Sincronização Local (Global) -->
                <div x-show="syncNowExecutando" x-cloak class="mt-6 bg-[#F5F7FA] rounded-2xl p-6 border border-slate-200 space-y-2 animate-fade">
                    <div class="flex items-center justify-between text-xs font-bold">
                        <span class="text-slate-600 uppercase tracking-wider">Progresso da Varredura Física</span>
                        <span class="text-slate-800" x-text="syncPercent + '% (' + syncCurrent + '/' + syncTotal + ')'">0%</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden">
                        <div class="bg-gradient-to-r from-[#003366] to-emerald-600 h-3 rounded-full transition-all duration-300 shadow-[0_0_8px_rgba(16,185,129,0.3)]" :style="'width: ' + syncPercent + '%'"></div>
                    </div>
                    <div class="text-[10px] text-slate-500 truncate" x-text="syncLastFile ? 'Importando: ' + syncLastFile : 'Contando e catalogando arquivos do disco...'"></div>
                </div>

                <p x-show="syncMsg" x-cloak x-text="syncMsg" class="mt-4 font-bold text-sm text-slate-800"></p>
            </div>
        </div>

        <!-- Progresso da Importação -->
        <div x-show="importando || importacaoConcluida || erroFatal" x-cloak x-transition class="mt-8 bg-[#F5F7FA] rounded-2xl p-8 border border-slate-200 relative overflow-hidden">
            
            <div x-show="erroFatal" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 font-bold" x-text="erro"></div>

            <div class="flex items-center justify-between mb-4 relative z-10" x-show="!erroFatal">
                <h3 class="text-2xl font-black text-slate-800" x-text="importacaoConcluida ? 'Importação Concluída!' : 'Sincronizando arquivos...'"></h3>
                <span class="text-[#005B96] font-bold text-xl" x-text="Math.round(progresso) + '%'"></span>
            </div>

            <!-- Barra -->
            <div class="w-full bg-slate-200 rounded-full h-4 mb-6 relative z-10 overflow-hidden">
                <div class="bg-gradient-to-r from-[#003366] to-[#005B96] h-4 rounded-full transition-all duration-300" :style="'width: ' + progresso + '%'"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm font-medium text-slate-600 relative z-10">
                <div>
                    <span class="text-slate-400">Processando:</span> 
                    <span class="text-slate-800 font-bold truncate block" x-text="pastaAtualText"></span>
                </div>
                <div class="md:text-right">
                    <span class="text-slate-400">Progresso:</span>
                    <span class="text-slate-800 font-bold" x-text="processados + ' de ' + totalParaProcessar"></span>
                </div>
            </div>

            <div x-show="importacaoConcluida" class="mt-8 text-center relative z-10">
                <a href="{{ route('admin.pastas.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-[#002244] hover:bg-[#002244] text-white rounded-xl font-bold transition">
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
            tipoUpload: 'imovel',
            statusServico: 'pronto',
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
            erroFatal: false,
            
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

            init() {
                // Initialize WebSocket listener
                if (window.Echo) {
                    window.Echo.channel('sync-progress')
                        .listen('SyncProgressUpdated', (e) => {
                            this.syncNowExecutando = e.status === 'running';
                            this.syncPercent = e.percentage;
                            this.syncCurrent = e.current;
                            this.syncTotal = e.total;
                            this.syncLastFile = e.last_file;
                            
                            if (e.status === 'done') {
                                this.syncNowExecutando = false;
                                this.syncMsg = 'Sincronização concluída com sucesso!';
                            } else if (e.status === 'error') {
                                this.syncNowExecutando = false;
                                this.syncMsg = 'Erro na sincronização: ' + e.last_file;
                            }
                        });
                }
            },

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

                // Fallback polling caso o WebSocket não receba eventos
                pollInterval = setInterval(async () => {
                    if (!this.syncNowExecutando) {
                        clearInterval(pollInterval);
                        return;
                    }
                    try {
                        const progRes = await fetch('{{ route('admin.importacao.syncProgress') }}');
                        if (progRes.ok) {
                            const progressData = await progRes.json();
                            if (progressData.status === 'running') {
                                this.syncPercent = progressData.percentage || 0;
                                this.syncCurrent = progressData.current || 0;
                                this.syncTotal = progressData.total || 0;
                                this.syncLastFile = progressData.last_file || '';
                            } else if (progressData.status === 'done') {
                                this.syncNowExecutando = false;
                                this.syncMsg = 'Sincronização concluída com sucesso!';
                                clearInterval(pollInterval);
                            }
                        }
                    } catch (e) {
                        // Ignora erro de rede temporário no polling
                    }
                }, 5000);

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

                    this.syncMsg = 'Comando enviado! Acompanhe o progresso...';

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
                this.erroFatal = false;
                this.processados = 0;
                this.totalParaProcessar = this.arquivos.length;
                this.erro = '';

                // O caminho relativo vem como: "Lote 90/Documentos/doc.pdf"
                // Vamos extrair o nome do imóvel a partir do primeiro arquivo
                let imovelNome = 'Imovel Desconhecido';
                if (this.arquivos.length > 0 && this.arquivos[0].webkitRelativePath) {
                    imovelNome = this.arquivos[0].webkitRelativePath.split('/')[0];
                }

                // Processar usando um pool de concorrência para evitar travamentos em arquivos muito grandes
                const concurrencyLimit = 5;
                let currentIndex = 0;

                const worker = async () => {
                    while (currentIndex < this.arquivos.length) {
                        if (this.erroFatal) break;
                        
                        const i = currentIndex++;
                        const file = this.arquivos[i];
                        
                        // Atualiza o texto apenas de vez em quando para não sobrecarregar a UI do Alpine com 13 mil re-renders por segundo
                        if (i % 5 === 0 || file.size > 5000000) {
                            this.pastaAtualText = file.webkitRelativePath || file.name;
                        }

                        // Ignorar arquivos gigantes ou formatos brutos de topografia (nuvem de pontos, ortomosaicos) que travam o navegador
                        const ext = file.name.split('.').pop().toLowerCase();
                        const ignoredExtensions = ['tif', 'tiff', 'las', 'laz', 'rcp', 'rcs', 'ecw'];
                        
                        if (ignoredExtensions.includes(ext) || file.size > 150 * 1024 * 1024) {
                            console.log(`Pulando arquivo gigante/bruto: ${file.name}`);
                            this.processados++;
                            continue;
                        }
                        
                        const formData = new FormData();
                        formData.append('tipo_upload', this.tipoUpload);
                        formData.append('status_servico', this.statusServico);
                        if (this.tipoUpload !== 'ano') formData.append('ano', this.ano);
                        if (this.tipoUpload === 'imovel') formData.append('categoria', this.categoria);
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

                            if (!response.ok) {
                                console.error(`Erro ao processar ${file.name}: Status ${response.status}`);
                                if (response.status === 419 || response.status === 401) {
                                    this.erroFatal = true;
                                    this.erro = "Sua sessão de login expirou. Por favor, recarregue a página e faça login novamente para continuar.";
                                    break;
                                }
                            }
                        } catch (error) {
                            console.error(`Erro de rede ao processar ${file.name}:`, error);
                            // Pode ser perda de internet temporária, não vamos cancelar tudo por um erro de rede, apenas logging
                        }

                        this.processados++;
                    }
                };

                const workers = [];
                for (let w = 0; w < concurrencyLimit; w++) {
                    workers.push(worker());
                }

                await Promise.all(workers);

                this.importando = false;
                this.importacaoConcluida = true;
                this.pastaAtualText = 'Sincronização Finalizada';
            },


        };
    }
</script>
@endsection
