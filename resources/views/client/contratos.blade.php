?<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Contratos - Getec Topografia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">
</head>
<body class="bg-gray-100 min-h-screen overflow-auto" x-data="{ modalAssinar: false, contratoIdAssinar: null, modalRecibos: false, recibos: [], signStep: 'choose', documentoAssinado: false, assinanteNome: '{{ auth()->user()->name }}' }">

    <!-- Background -->
    <div class="fixed inset-0 z-0 bg-cover bg-center bg-no-repeat bg-fixed"
         style="background-image: url('{{ asset('images/background-topo.jpg') }}');">
        <div class="absolute inset-0 bg-black/20 backdrop-blur-[2px]"></div>
    </div>

    <div class="relative z-10 flex flex-col min-h-screen px-4 py-5 md:px-8 md:py-6">

        <!-- HEADER -->
        <header class="flex flex-col md:flex-row items-center justify-between gap-4">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 bg-white/10 backdrop-blur-md px-5 py-3 rounded-2xl border border-white/20 shadow-lg hover:bg-white/20 transition">
                <span class="text-white text-xl">⬅️</span>
                <span class="text-white font-bold uppercase tracking-wider text-sm">Voltar ao Painel</span>
            </a>
            
            <div class="bg-[#003366]/90 backdrop-blur-md px-6 py-3 rounded-2xl border border-white/10 shadow-lg">
                <h1 class="text-white font-black uppercase text-lg tracking-widest">Meus Contratos</h1>
            </div>
        </header>

        <!-- MAIN CONTENT -->
        <main class="flex-1 mt-8">
            <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-[32px] p-6 md:p-10 shadow-2xl">
                
                <div class="mb-8 border-b border-white/10 pb-6">
                    <h2 class="text-3xl font-black text-white uppercase tracking-tight">Contratos Firmados</h2>
                    <p class="mt-2 text-white/70">Abaixo estão listados os seus contratos gerados. Você pode baixar as vias em PDF a qualquer momento.</p>
                </div>

                @if(session('success'))
                    <div class="mb-6 rounded-[20px] bg-emerald-500/15 border border-emerald-500/30 p-4 text-emerald-100">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="mb-6 rounded-[20px] bg-rose-500/15 border border-rose-500/30 p-4 text-rose-100">{{ session('error') }}</div>
                @endif

                @if($contratos->isEmpty())
                    <div class="flex flex-col items-center justify-center py-16 text-center">
                        <div class="w-24 h-24 bg-white/5 rounded-full flex items-center justify-center text-5xl mb-6 shadow-inner border border-white/5">
                            📄
                        </div>
                        <h3 class="text-xl font-bold text-white uppercase">Nenhum contrato encontrado</h3>
                        <p class="text-white/60 mt-2">Você ainda não possui contratos vinculados a esta conta.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($contratos as $contrato)
                            <div class="bg-gradient-to-br from-white/10 to-white/5 border border-white/10 rounded-3xl p-6 shadow-xl flex flex-col justify-between hover:bg-white/15 transition duration-300">
                                
                                <div>
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="w-12 h-12 bg-[#003366] rounded-xl flex items-center justify-center text-white text-2xl shadow">
                                            ✍️
                                        </div>
                                        <span class="bg-[#00E500]/20 text-[#00E500] border border-[#00E500]/30 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider">
                                            {{ $contrato->status === 'vinculado' ? 'Ativo' : 'Em Análise' }}
                                        </span>
                                    </div>

                                    <h3 class="text-white font-black uppercase text-sm tracking-widest mb-1 opacity-60">Imóvel</h3>
                                    <p class="text-white font-bold text-lg leading-tight mb-4 line-clamp-2" title="{{ $contrato->nome_imovel }}">
                                        {{ $contrato->nome_imovel }}
                                    </p>

                                    <div class="space-y-2 mb-6">
                                        <div class="bg-black/20 rounded-xl p-3 border border-white/5">
                                            <span class="text-[10px] text-white/50 uppercase font-bold tracking-wider block">Serviço:</span>
                                            <span class="text-white font-bold text-sm truncate block">{{ $contrato->tipo_servico }}</span>
                                        </div>
                                        <div class="flex gap-2">
                                            <div class="flex-1 bg-black/20 rounded-xl p-3 border border-white/5">
                                                <span class="text-[10px] text-white/50 uppercase font-bold tracking-wider block">Data:</span>
                                                <span class="text-white font-bold text-xs">{{ $contrato->created_at->format('d/m/Y') }}</span>
                                            </div>
                                            <div class="flex-1 bg-black/20 rounded-xl p-3 border border-white/5">
                                                <span class="text-[10px] text-white/50 uppercase font-bold tracking-wider block">Assinatura:</span>
                                                @if($contrato->assinado_em)
                                                    <span class="text-[#00E500] font-bold text-xs">Assinado</span>
                                                @else
                                                    <span class="text-yellow-400 font-bold text-xs">Pendente</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-2 w-full mt-2">
                                    <a href="{{ route('client.contratos.download', $contrato->id) }}" 
                                       class="w-full bg-[#004A7C] hover:bg-[#003366] text-white text-center py-3 rounded-xl uppercase font-black tracking-widest text-xs transition shadow-lg flex items-center justify-center gap-2">
                                        📥 Baixar PDF
                                    </a>

                                    @if(!$contrato->assinado_em)
                                        <button @click="modalAssinar = true; contratoIdAssinar = {{ $contrato->id }}; signStep = 'choose'; documentoAssinado = false;" 
                                                class="w-full bg-emerald-600 hover:bg-emerald-500 text-white text-center py-3 rounded-xl uppercase font-black tracking-widest text-xs transition shadow-lg flex items-center justify-center gap-2">
                                            ✍️ Assinar
                                        </button>
                                    @endif

                                    @if($contrato->recibos->count() > 0)
                                        <button @click="modalRecibos = true; recibos = {{ json_encode($contrato->recibos) }}" 
                                                class="w-full bg-purple-600 hover:bg-purple-500 text-white text-center py-3 rounded-xl uppercase font-black tracking-widest text-xs transition shadow-lg flex items-center justify-center gap-2">
                                            🧾 Ver Recibos ({{ $contrato->recibos->count() }})
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>
        </main>
    </div>

<!-- MODAL ASSINAR -->
<template x-teleport="body">
    <div x-show="modalAssinar" x-cloak
         class="fixed inset-0 z-[9999] bg-black/80 backdrop-blur-sm overflow-y-auto p-4 flex justify-center items-center">
        
        <div class="bg-[#002244]/90 backdrop-blur-md rounded-[35px] border border-white/10 shadow-2xl w-full max-w-md p-8 text-center mt-10 text-left relative" @click.away="modalAssinar = false">
            
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-black uppercase italic text-[#00E500]">
                    Assinador Digital
                </h3>
                <button @click="modalAssinar = false" class="text-white/40 hover:text-white text-2xl leading-none">
                    ×
                </button>
            </div>

            <!-- Formulário que será submetido -->
            <form :action="`/meus-contratos/${contratoIdAssinar}/assinar`" method="POST" id="formAssinaturaClient">
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
                        <input type="radio" checked id="certClient" class="accent-[#00E500]">
                        <label for="certClient" class="cursor-pointer">
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
            </form>
        </div>
    </div>
</template>

<!-- MODAL RECIBOS -->
<template x-teleport="body">
    <div x-show="modalRecibos" x-cloak
         class="fixed inset-0 z-[9999] bg-black/70 backdrop-blur-sm overflow-y-auto p-4 flex justify-center items-start pt-10 pb-20">
    <div class="bg-[#002244]/90 backdrop-blur-md rounded-[35px] border border-white/10 shadow-2xl w-full max-w-lg p-8 text-center mt-10 relative">
        <button @click="modalRecibos = false" class="absolute top-6 right-6 text-white/50 hover:text-white transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div class="text-4xl mb-4">🧾</div>
        <h2 class="text-2xl font-black text-white uppercase italic mb-6">Meus Recibos</h2>

        <div class="mb-4 text-left">
            <div class="space-y-3 max-h-60 overflow-y-auto pr-2">
                <template x-for="recibo in recibos" :key="recibo.id">
                    <div class="flex items-center justify-between bg-white/5 border border-white/10 p-4 rounded-xl">
                        <div>
                            <p class="text-sm font-bold text-white" x-text="recibo.descricao"></p>
                            <p class="text-xs text-[#00E500] font-bold" x-text="'R$ ' + parseFloat(recibo.valor_recebido).toLocaleString('pt-BR', {minimumFractionDigits: 2})"></p>
                        </div>
                        <a :href="`/meus-contratos/recibo/${recibo.id}/visualizar`" target="_blank" class="px-4 py-2 bg-blue-600/80 hover:bg-blue-500 rounded-lg text-white font-black text-xs uppercase tracking-wider transition shadow-md">
                            Visualizar PDF
                        </a>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
</template>

</body>
</html>

