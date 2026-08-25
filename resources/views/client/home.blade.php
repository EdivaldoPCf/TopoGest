@extends('layouts.cliente')

@section('content')

<!-- TAB: PAINEL PRINCIPAL -->
<div x-show="tab === 'painel'" x-transition class="space-y-10">
    <div class="mb-8 bg-white/90 backdrop-blur-md p-6 rounded-2xl border border-slate-200 shadow-sm inline-block w-full md:w-max md:pr-12">
        <h1 class="text-3xl font-black text-slate-800 tracking-tight uppercase">Olá, {{ explode(' ', auth()->user()->name)[0] }}</h1>
        <p class="text-slate-600 mt-1 font-medium">Acompanhe seus serviços, documentos e suporte com um acesso rápido e organizado.</p>
    </div>

    <!-- Widgets -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Imóveis -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col transition hover:shadow-md cursor-pointer hover:border-blue-300" @click="tab = 'contratos'">
            <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-2">Imóveis</p>
            <h3 class="text-4xl font-black text-[#003366]">{{ $totalPastas ?? 0 }}</h3>
            <p class="text-xs text-slate-400 mt-2">Serviços vinculados ao seu cadastro.</p>
        </div>

        <!-- Documentos -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col transition hover:shadow-md cursor-pointer hover:border-blue-300" @click="tab = 'download'">
            <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-2">Documentos</p>
            <h3 class="text-4xl font-black text-[#003366]">{{ $totalArquivos ?? 0 }}</h3>
            <p class="text-xs text-slate-400 mt-2">Arquivos disponíveis para download.</p>
        </div>

        <!-- Pendências -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col transition hover:shadow-md cursor-pointer hover:border-blue-300" @click="tab = 'andamento'">
            <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-2">Pendências</p>
            <h3 class="text-4xl font-black text-[#003366]">{{ $pendenciasAbertas ?? 0 }}</h3>
            <p class="text-xs text-slate-400 mt-2">Itens aguardando atualização.</p>
        </div>

        <!-- Notificações -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col transition hover:shadow-md cursor-pointer hover:border-blue-300" @click="tab = 'notificacoes'">
            <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-2">Notificações</p>
            <h3 class="text-4xl font-black text-[#003366]">{{ auth()->user()->unreadNotifications->count() ?? 0 }}</h3>
            <p class="text-xs text-slate-400 mt-2">Avisos não lidos.</p>
        </div>
    </div>

    <!-- SIGEF e Ações -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
        
        <!-- Certificação SIGEF -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 lg:p-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-[#003366] shadow-inner border border-slate-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <span class="uppercase tracking-widest text-[10px] text-slate-500 font-bold bg-slate-100 px-2 py-1 rounded">Certificação SIGEF</span>
                        <h2 class="text-2xl font-black uppercase text-slate-800 mt-2 tracking-tight">Consulta de Imóvel no INCRA</h2>
                        <p class="text-sm text-slate-500 mt-2">
                            Utilize seu CPF/CNPJ abaixo para buscar as informações do seu imóvel no portal oficial do SIGEF.
                        </p>
                        
                        <!-- CPF/CNPJ Info -->
                        <div class="mt-4 flex flex-wrap items-center gap-3 bg-slate-50 border border-slate-200 rounded-xl p-3 inline-flex">
                            <span class="text-[10px] uppercase tracking-wider font-bold text-slate-500">Seu CPF/CNPJ:</span>
                            <span class="font-mono font-bold text-slate-800">{{ auth()->user()->formatted_cpf }}</span>
                            <button 
                                onclick="copyCpfToClipboard('{{ auth()->user()->cpf }}')"
                                class="bg-[#003366] hover:bg-blue-900 text-white px-3 py-1.5 rounded-lg font-bold uppercase text-[10px] tracking-wide transition flex items-center gap-1.5 shadow"
                            >
                                <span id="copyText">Copiar</span>
                                <svg id="copyIcon" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="flex-shrink-0">
                    <a href="https://sigef.incra.gov.br/consultar/parcelas/" 
                       target="_blank" 
                       class="bg-[#003366] hover:bg-blue-900 text-white px-6 py-3.5 rounded-xl font-black uppercase text-xs tracking-wider shadow-lg shadow-[#003366]/20 transition flex items-center gap-2 border border-blue-800 w-full justify-center md:w-auto">
                        <span>Consultar Geral</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>
            </div>

            @if(isset($pastasSigef) && $pastasSigef->isNotEmpty())
                <div class="mt-8 border-t border-slate-200 pt-6">
                    <p class="text-xs uppercase tracking-wider font-bold text-slate-500 mb-4">Seus Imóveis Certificados:</p>
                    <div class="space-y-3">
                        @foreach($pastasSigef as $pastaSigef)
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50 border border-slate-200 rounded-xl p-4 hover:bg-white transition shadow-sm">
                                <div class="flex items-center gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <div>
                                        <span class="font-bold text-slate-800 block">{{ $pastaSigef->nome }}</span>
                                        <span class="text-xs text-slate-500 font-mono select-all">{{ $pastaSigef->codigo_sigef }}</span>
                                    </div>
                                </div>
                                <a href="https://sigef.incra.gov.br/geo/parcela/detalhe/{{ $pastaSigef->codigo_sigef }}/" 
                                   target="_blank" 
                                   class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-bold uppercase text-[10px] tracking-wider shadow transition flex items-center gap-1.5 self-start sm:self-auto">
                                    <span>Ver Parcela</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Perfil e Resumo -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 lg:p-8 flex flex-col justify-between">
            <div>
                <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center text-[#003366] shadow-inner border border-slate-200 mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                </div>
                <h2 class="text-2xl font-black uppercase text-slate-800 mb-2 tracking-tight">Seu Perfil</h2>
                <p class="text-slate-500 text-sm leading-relaxed mb-6">
                    Mantenha seus dados atualizados para receber notificações sobre andamentos e novos documentos de seus serviços.
                </p>
                <a href="{{ route('profile.edit') }}" class="inline-flex bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-2.5 rounded-xl font-bold uppercase text-xs tracking-wider shadow-sm transition items-center gap-2 border border-slate-300">
                    <span>Acessar Perfil</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- TAB: MEUS CONTRATOS -->
<div x-show="tab === 'contratos'" x-cloak x-transition class="space-y-10">
    <div class="mb-8 bg-white/90 backdrop-blur-md p-6 rounded-2xl border border-slate-200 shadow-sm inline-block w-full md:w-max md:pr-12">
        <h2 class="text-3xl font-black text-slate-800 tracking-tight uppercase">Meus Contratos</h2>
        <p class="text-slate-600 mt-1 font-medium">Histórico e informações dos seus imóveis e serviços fechados.</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 text-center py-16">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-slate-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
        <p class="text-slate-500 font-medium">Os dados dos contratos serão carregados aqui em breve.</p>
    </div>
</div>

<!-- TAB: ANDAMENTO -->
<div x-show="tab === 'andamento'" x-cloak x-transition class="space-y-6" x-data="{ filtroStatus: 'todos', search: '' }">
    <div class="bg-white/90 backdrop-blur-md p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-black text-slate-800 tracking-tight uppercase">Meus Servi&ccedil;os</h2>
            <p class="text-slate-600 mt-1 font-medium">Acompanhe as etapas e pend&ecirc;ncias dos seus servi&ccedil;os.</p>
        </div>
        
        <!-- Filtros e Busca -->
        <div class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative w-full sm:w-auto">
                <input type="text" x-model="search" placeholder="Pesquisar servi&ccedil;o..." class="w-full sm:w-64 pl-10 pr-4 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
            <select x-model="filtroStatus" class="w-full sm:w-auto px-4 py-2 border border-slate-300 rounded-xl text-sm font-medium text-slate-700 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none cursor-pointer">
                <option value="todos">Todos os Servi&ccedil;os</option>
                <option value="pendente">Pendentes</option>
                <option value="pronto">Prontos</option>
            </select>
        </div>
    </div>
    
    @if(isset($pastas) && $pastas->isNotEmpty())
        <div class="space-y-4">
            @foreach($pastas as $pasta)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 hover:shadow-md transition" 
                     x-show="(filtroStatus === 'todos' || filtroStatus === '{{ $pasta->pendencias()->where('status','pendente')->count() > 0 ? 'pendente' : 'pronto' }}') && ('{{ strtolower($pasta->nome) }}'.includes(search.toLowerCase()))">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">{{ $pasta->nome }}</h3>
                                <p class="text-xs text-slate-500 uppercase tracking-wider mt-1 font-medium">Cadastrado em {{ $pasta->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            @if($pasta->pendencias()->where('status','pendente')->count() > 0)
                                <span class="px-3 py-1 bg-amber-100 text-amber-700 text-[10px] uppercase tracking-wider font-black rounded-full border border-amber-200">Pendente</span>
                            @else
                                <span class="px-3 py-1 bg-emerald-100 text-emerald-700 text-[10px] uppercase tracking-wider font-black rounded-full border border-emerald-200">Pronto</span>
                            @endif
                            <button @click="tab = 'download'" class="text-xs font-bold text-[#003366] hover:text-blue-500 transition underline underline-offset-4 decoration-2 decoration-[#003366]/30">Ver Arquivos</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 text-center py-16">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-slate-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <p class="text-slate-500 font-medium">Os andamentos dos seus servi&ccedil;os ser&atilde;o exibidos aqui.</p>
        </div>
    @endif
</div>
<!-- TAB: DOWNLOAD -->
<div x-show="tab === 'download'" x-cloak x-transition class="space-y-10">
    <div class="mb-8 bg-white/90 backdrop-blur-md p-6 rounded-2xl border border-slate-200 shadow-sm inline-block w-full md:w-max md:pr-12">
        <h2 class="text-3xl font-black text-slate-800 tracking-tight uppercase">Download de Plantas</h2>
        <p class="text-slate-600 mt-1 font-medium">Arquivos finalizados disponíveis para download e impressão.</p>
    </div>
    
    @if(isset($pastas) && $pastas->isNotEmpty())
        @php $hasArquivos = false; @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($pastas as $pasta)
                @if($pasta->arquivos->isNotEmpty())
                    @php $hasArquivos = true; @endphp
                    @foreach($pasta->arquivos as $arquivo)
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 hover:shadow-md transition flex flex-col justify-between">
                            <div>
                                <div class="flex items-center gap-3 mb-4 text-emerald-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $pasta->nome }}</span>
                                </div>
                                <h3 class="text-md font-bold text-slate-800 break-all">{{ $arquivo->nome_original }}</h3>
                            </div>
                            <div class="mt-6 pt-4 border-t border-slate-100 flex justify-between items-center">
                                <span class="text-xs text-slate-400">{{ $arquivo->created_at ? $arquivo->created_at->format('d/m/Y') : '' }}</span>
                                <a href="#" class="px-4 py-2 bg-[#003366] hover:bg-blue-900 text-white rounded-lg text-xs font-bold uppercase tracking-wider transition flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    Baixar
                                </a>
                            </div>
                        </div>
                    @endforeach
                @endif
            @endforeach
        </div>
        
        @if(!$hasArquivos)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 text-center py-16">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-slate-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                <p class="text-slate-500 font-medium">Seus arquivos para download estarão disponíveis aqui assim que forem adicionados.</p>
            </div>
        @endif
    @else
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 text-center py-16">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-slate-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            <p class="text-slate-500 font-medium">Seus arquivos para download estarão disponíveis aqui.</p>
        </div>
    @endif
</div>

<!-- TAB: NOTIFICAÇÕES -->
<div x-show="tab === 'notificacoes'" x-cloak x-transition class="space-y-10">
    <div class="mb-8 bg-white/90 backdrop-blur-md p-6 rounded-2xl border border-slate-200 shadow-sm inline-block w-full md:w-max md:pr-12">
        <h2 class="text-3xl font-black text-slate-800 tracking-tight uppercase">Notificações</h2>
        <p class="text-slate-600 mt-1 font-medium">Avisos e mensagens sobre os seus serviços.</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 text-center py-16">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-slate-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
        <p class="text-slate-500 font-medium">Você não possui notificações no momento.</p>
    </div>
</div>

@push('scripts')
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
@endpush
@endsection