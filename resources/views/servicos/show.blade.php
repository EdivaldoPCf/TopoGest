@extends('layouts.app')

@section('content')

<div class="relative min-h-screen overflow-hidden">

    <!-- Background -->
    <div class="fixed inset-0 -z-10">
        <img 
            src="{{ asset('images/background-topo.jpg') }}"
            alt="Background"
            class="w-full h-full object-cover"
        >
        <div class="absolute inset-0 bg-[#001526]/80 backdrop-blur-[2px]"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Header -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-10">

            <div>
                <a 
                    href="{{ route('pasta.show', $servico->pasta_id) }}"
                    class="inline-flex items-center gap-2 text-white/70 hover:text-white transition text-sm uppercase tracking-wide font-bold"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>

                    Voltar para {{ $servico->pasta->nome }}
                </a>

                <h1 class="mt-4 text-3xl md:text-4xl font-black italic uppercase tracking-tight text-white">
                    {{ $servico->nome_imovel }}
                </h1>

                <p class="mt-2 text-white/60 text-sm">
                    Painel completo de gerenciamento do imóvel e documentos.
                </p>
            </div>

            <!-- Status -->
            <div class="flex flex-wrap items-center gap-3">

                <div class="px-5 py-2 rounded-2xl bg-white/10 border border-white/10 backdrop-blur-xl shadow-lg">
                    <span class="block text-[11px] uppercase tracking-widest text-white/50 font-bold">
                        Status
                    </span>

                    <span class="text-sm font-black uppercase italic
                        {{ $servico->status === 'pendente' ? 'text-yellow-400' : 'text-green-400' }}">
                        {{ ucfirst($servico->status) }}
                    </span>
                </div>

                <div class="px-5 py-2 rounded-2xl bg-white/10 border border-white/10 backdrop-blur-xl shadow-lg">
                    <span class="block text-[11px] uppercase tracking-widest text-white/50 font-bold">
                        Tipo
                    </span>

                    <span class="text-sm font-black uppercase italic text-cyan-300">
                        {{ $servico->tipo_servico ?? 'Não definido' }}
                    </span>
                </div>

            </div>
        </div>

        <!-- Layout -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

            <!-- Sidebar -->
            <div class="xl:col-span-1 space-y-6">

                <!-- Card -->
                <div class="bg-white/10 backdrop-blur-2xl border border-white/10 rounded-[28px] p-8 shadow-2xl">

                    <div class="flex items-center gap-4 mb-8">

                        <div class="w-16 h-16 rounded-2xl bg-[#003366] flex items-center justify-center shadow-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10l9-7 9 7v11a2 2 0 01-2 2h-4a2 2 0 01-2-2V14H9v7a2 2 0 01-2 2H3z"/>
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-white text-xl font-black uppercase italic">
                                Informações
                            </h2>

                            <p class="text-white/50 text-sm">
                                Dados do imóvel
                            </p>
                        </div>
                    </div>

                    <div class="space-y-5">

                        <div>
                            <span class="text-white/40 text-xs uppercase tracking-widest font-bold">
                                Nome do Imóvel
                            </span>

                            <p class="text-white font-bold text-lg mt-1">
                                {{ $servico->nome_imovel }}
                            </p>
                        </div>

                        <div>
                            <span class="text-white/40 text-xs uppercase tracking-widest font-bold">
                                Status Atual
                            </span>

                            <p class="mt-1">
                                <span class="px-4 py-1 rounded-full text-xs font-black uppercase
                                    {{ $servico->status === 'pendente'
                                        ? 'bg-yellow-400/20 text-yellow-300 border border-yellow-400/20'
                                        : 'bg-green-400/20 text-green-300 border border-green-400/20' }}">
                                    {{ ucfirst($servico->status) }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <span class="text-white/40 text-xs uppercase tracking-widest font-bold">
                                Tipo de Serviço
                            </span>

                            <p class="text-cyan-300 font-bold mt-1 uppercase">
                                {{ $servico->tipo_servico ?? 'Não definido' }}
                            </p>
                        </div>

                    </div>

                    <!-- Actions -->
                    <div class="mt-10 space-y-4">

                        <button
                            class="w-full h-14 rounded-2xl bg-gradient-to-r from-[#004A7C] to-[#0066B2] text-white font-black uppercase tracking-wide shadow-xl hover:scale-[1.02] transition"
                        >
                            Editar Dados
                        </button>

                        <button
                            class="w-full h-14 rounded-2xl bg-gradient-to-r from-[#00E676] to-[#00C853] text-black font-black uppercase tracking-wide shadow-xl hover:scale-[1.02] transition"
                        >
                            Concluir Serviço
                        </button>

                    </div>
                </div>

            </div>

            <!-- Content -->
            <div class="xl:col-span-2 space-y-8">

                <!-- Documentos -->
                <div class="bg-white/10 backdrop-blur-2xl border border-white/10 rounded-[28px] p-8 shadow-2xl">

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">

                        <div>
                            <h3 class="text-2xl font-black uppercase italic text-white">
                                Documentos & Projetos
                            </h3>

                            <p class="text-white/50 text-sm mt-1">
                                Arquivos vinculados ao imóvel
                            </p>
                        </div>

                        <button
                            class="h-12 px-6 rounded-2xl bg-[#004A7C] text-white font-black uppercase tracking-wide shadow-xl hover:bg-[#005A99] transition"
                        >
                            + Upload de Arquivo
                        </button>
                    </div>

                    <!-- Empty -->
                    <div class="border border-dashed border-white/10 rounded-3xl p-14 text-center bg-black/10">

                        <div class="w-20 h-20 rounded-3xl bg-white/5 mx-auto flex items-center justify-center mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>

                        <h4 class="text-white text-xl font-bold italic">
                            Nenhum arquivo enviado
                        </h4>

                        <p class="text-white/40 text-sm mt-2">
                            Faça upload de documentos, plantas, projetos ou arquivos relacionados.
                        </p>
                    </div>
                </div>

                <!-- Pendências -->
                <div class="bg-white/10 backdrop-blur-2xl border border-red-500/20 rounded-[28px] p-8 shadow-2xl">

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">

                        <div>
                            <h3 class="text-2xl font-black uppercase italic text-red-400">
                                Pendências Ativas
                            </h3>

                            <p class="text-white/50 text-sm mt-1">
                                Solicitações e itens pendentes
                            </p>
                        </div>

                        <button
                            class="h-12 px-6 rounded-2xl bg-gradient-to-r from-[#FF3D3D] to-[#D50000] text-white font-black uppercase tracking-wide shadow-xl hover:scale-[1.02] transition"
                        >
                            + Nova Pendência
                        </button>
                    </div>

                    <!-- Empty -->
                    <div class="border border-dashed border-red-400/20 rounded-3xl p-14 text-center bg-black/10">

                        <div class="w-20 h-20 rounded-3xl bg-red-500/10 mx-auto flex items-center justify-center mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-red-400/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/>
                            </svg>
                        </div>

                        <h4 class="text-white text-xl font-bold italic">
                            Nenhuma pendência registrada
                        </h4>

                        <p class="text-white/40 text-sm mt-2">
                            Não existem solicitações pendentes para este imóvel no momento.
                        </p>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

@endsection