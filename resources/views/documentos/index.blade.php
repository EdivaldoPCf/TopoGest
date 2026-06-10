@extends('layouts.app')

@section('content')
<div class="min-h-screen relative overflow-hidden">

    <!-- Background -->
    <div class="absolute inset-0">
        <img src="{{ asset('images/background-topo.jpg') . '?v=' . @filemtime(public_path('images/background-topo.jpg')) }}"
             class="w-full h-full object-cover"
             alt="Background">
        <div class="absolute inset-0 bg-black/35 backdrop-blur-[1px]"></div>
    </div>

    <!-- Conteúdo -->
    <div class="relative z-10 px-6 py-6">

        <!-- HEADER -->
        <div class="flex justify-between items-start mb-10">

            <div>
                <h1 class="text-4xl md:text-5xl font-black text-white tracking-tight mb-3">
                    DOCUMENTOS RECENTES
                </h1>
                <p class="text-white/90 text-lg md:text-xl max-w-2xl">
                    Histórico completo de arquivos enviados e baixados recentemente.
                </p>
            </div>

            <!-- Botão voltar -->
            <a href="{{ route('dashboard') }}"
               class="bg-[#003366] hover:bg-[#004A7C]
                      text-white font-bold px-6 py-3 rounded-2xl
                      shadow-xl transition-all duration-300">

                ← Voltar

            </a>

        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">

            <!-- ENVIADOS -->
            <div class="bg-white/15 backdrop-blur-xl border border-white/20
                        rounded-[35px] shadow-2xl overflow-hidden">

                <!-- Header -->
                <div class="bg-[#003366] px-8 py-5 flex items-center justify-between">

                    <div>
                        <h2 class="text-white text-2xl font-black">
                            Arquivos Enviados
                        </h2>

                        <p class="text-white/70 text-sm">
                            Últimos documentos enviados pela equipe.
                        </p>
                    </div>

                    <div class="bg-white/10 p-4 rounded-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="w-8 h-8 text-white"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3 3m0 0l-3-3m3 3V8"/>
                        </svg>
                    </div>

                </div>

                <!-- Lista -->
                <div id="arquivos-enviados-list" class="p-6 space-y-4 max-h-[600px] overflow-y-auto">

                    @forelse($arquivosEnviados as $arquivo)

                        <div class="bg-white/10 border border-white/10 rounded-2xl p-5
                                    hover:bg-white/20 transition-all duration-300">

                            <div class="flex items-start justify-between gap-4">

                                <div class="flex items-start gap-4">

                                    <!-- Ícone -->
                                    <div class="bg-[#003366] p-3 rounded-xl shadow-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="w-6 h-6 text-white"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke="currentColor">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M12 19V5m0 0l-4 4m4-4l4 4M4 15h16"/>
                                        </svg>
                                    </div>

                                    <!-- Dados -->
                                    <div>

                                        <h3 class="text-white font-bold text-lg">
                                            {{ $arquivo->nome ?? $arquivo->nome_original }}
                                        </h3>

                                        <p class="text-white/70 text-sm mt-1">
                                            Enviado em:
                                            {{ $arquivo->created_at->format('d/m/Y H:i') }}
                                        </p>

                                        <p class="text-white/70 text-sm mt-1">
                                            Imóvel:
                                            {{ $arquivo->pasta->nome ?? 'Não informado' }}
                                        </p>

                                        <p class="text-white/60 text-sm">
                                            Tipo:
                                            {{ strtoupper(pathinfo($arquivo->nome ?? $arquivo->nome_original, PATHINFO_EXTENSION)) }}
                                        </p>

                                    </div>

                                </div>

                                <!-- Botão -->
                                <a href="{{ route('arquivo.download', $arquivo->id) }}"
                                   class="bg-[#004A7C] hover:bg-[#005B99]
                                          text-white px-4 py-2 rounded-xl
                                          text-sm font-bold shadow-lg
                                          transition-all duration-300">

                                    Baixar

                                </a>

                            </div>

                        </div>

                    @empty

                        <div class="text-center py-20">

                            <p class="text-white/60 text-xl">
                                Nenhum arquivo enviado recentemente.
                            </p>

                        </div>

                    @endforelse

                </div>

            </div>

            <!-- BAIXADOS -->
            <div class="bg-white/15 backdrop-blur-xl border border-white/20
                        rounded-[35px] shadow-2xl overflow-hidden">

                <!-- Header -->
                <div class="bg-[#004A7C] px-8 py-5 flex items-center justify-between">

                    <div>
                        <h2 class="text-white text-2xl font-black">
                            Downloads Recentes
                        </h2>

                        <p class="text-white/70 text-sm">
                            Histórico de arquivos baixados pelo cliente.
                        </p>
                    </div>

                    <div class="bg-white/10 p-4 rounded-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="w-8 h-8 text-white"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M4 4v16h16M8 12l2 2 4-4 2 2"/>
                        </svg>
                    </div>

                </div>

                <!-- Lista -->
                <div id="downloads-recentes-list" class="p-6 space-y-4 max-h-[600px] overflow-y-auto">

                    @forelse($downloadsRecentes as $download)

                        <div class="bg-white/10 border border-white/10 rounded-2xl p-5
                                    hover:bg-white/20 transition-all duration-300">

                            <div class="flex items-start justify-between gap-4">

                                <div class="flex items-start gap-4">

                                    <!-- Ícone -->
                                    <div class="bg-green-600 p-3 rounded-xl shadow-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="w-6 h-6 text-white"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke="currentColor">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M12 4v10m0 0l-4-4m4 4l4-4m4 10H4"/>
                                        </svg>
                                    </div>

                                    <!-- Dados -->
                                    <div>

                                        <h3 class="text-white font-bold text-lg">
                                            {{ $download->arquivo_nome }}
                                        </h3>

                                        <p class="text-white/70 text-sm mt-1">
                                            Baixado em:
                                            {{ $download->created_at->format('d/m/Y H:i') }}
                                        </p>

                                        <p class="text-white/70 text-sm mt-1">
                                            Imóvel:
                                            {{ $download->detalhes['imovel_nome'] ?? 'Não identificado' }}
                                        </p>

                                        <p class="text-white/60 text-sm">
                                            Usuário:
                                            {{ $download->user->name ?? 'Cliente' }}
                                        </p>

                                    </div>

                                </div>

                                <div class="flex flex-col items-end gap-3">

                                    @if(!empty($download->detalhes['arquivo_id']))
                                        <a href="{{ route('arquivo.download', $download->detalhes['arquivo_id']) }}"
                                           class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-lg transition-all duration-300">

                                            Baixar

                                        </a>
                                    @else
                                        <div class="bg-white/10 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-lg opacity-50">
                                            Baixar indisponível
                                        </div>
                                    @endif

                                </div>

                            </div>

                        </div>

                    @empty

                        <div class="text-center py-20">

                            <p class="text-white/60 text-xl">
                                Nenhum download recente.
                            </p>

                        </div>

                    @endforelse

                </div>

            </div>

        </div>

    </div>

</div>

<script>
    const documentosRecentesJsonUrl = '{{ route('documentos.recentes.json') }}';
    const arquivoDownloadBase = '{{ url('/arquivo') }}';

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatDateTime(value) {
        if (!value) return '-';
        const date = new Date(value);
        return date.toLocaleString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function renderArquivosEnviados(items) {
        if (!items || items.length === 0) {
            return `
                <div class="text-center py-20">
                    <p class="text-white/60 text-xl">
                        Nenhum arquivo enviado recentemente.
                    </p>
                </div>
            `;
        }

        return items.map(item => `
            <div class="bg-white/10 border border-white/10 rounded-2xl p-5 hover:bg-white/20 transition-all duration-300">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="bg-[#003366] p-3 rounded-xl shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19V5m0 0l-4 4m4-4l4 4M4 15h16"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-lg">
                                ${escapeHtml(item.nome ?? item.nome_original)}
                            </h3>
                            <p class="text-white/70 text-sm mt-1">
                                Enviado em: ${escapeHtml(formatDateTime(item.created_at))}
                            </p>
                            <p class="text-white/70 text-sm mt-1">
                                Imóvel: ${escapeHtml(item.pasta?.nome ?? 'Não informado')}
                            </p>
                            <p class="text-white/60 text-sm">
                                Tipo: ${escapeHtml(item.nome ? item.nome.split('.').pop() : item.nome_original?.split('.').pop() ?? '')}
                            </p>
                        </div>
                    </div>
                    <a href="${arquivoDownloadBase}/${item.id}/download" class="bg-[#004A7C] hover:bg-[#005B99] text-white px-4 py-2 rounded-xl text-sm font-bold shadow-lg transition-all duration-300">
                        Baixar
                    </a>
                </div>
            </div>
        `).join('');
    }

    function renderDownloadsRecentes(items) {
        if (!items || items.length === 0) {
            return `
                <div class="text-center py-20">
                    <p class="text-white/60 text-xl">
                        Nenhum download recente.
                    </p>
                </div>
            `;
        }

        return items.map(item => `
            <div class="bg-white/10 border border-white/10 rounded-2xl p-5 hover:bg-white/20 transition-all duration-300">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="bg-green-600 p-3 rounded-xl shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v10m0 0l-4-4m4 4l4-4m4 10H4"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-lg">
                                ${escapeHtml(item.arquivo_nome)}
                            </h3>
                            <p class="text-white/70 text-sm mt-1">
                                Baixado em: ${escapeHtml(formatDateTime(item.created_at))}
                            </p>
                            <p class="text-white/70 text-sm mt-1">
                                Imóvel: ${escapeHtml(item.detalhes?.imovel_nome ?? 'Não identificado')}
                            </p>
                            <p class="text-white/60 text-sm">
                                Usuário: ${escapeHtml(item.user?.name ?? 'Cliente')}
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-3">
                        ${item.detalhes?.arquivo_id ? `
                            <a href="${arquivoDownloadBase}/${item.detalhes.arquivo_id}/download" class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-lg transition-all duration-300">
                                Baixar
                            </a>
                        ` : `
                            <div class="bg-white/10 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-lg opacity-50">
                                Baixar indisponível
                            </div>
                        `}
                    </div>
                </div>
            </div>
        `).join('');
    }

    async function refreshDocumentosRecentes() {
        try {
            const response = await fetch(documentosRecentesJsonUrl, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) {
                return;
            }

            const data = await response.json();
            document.getElementById('arquivos-enviados-list').innerHTML = renderArquivosEnviados(data.arquivosEnviados);
            document.getElementById('downloads-recentes-list').innerHTML = renderDownloadsRecentes(data.downloadsRecentes);
        } catch (error) {
            console.error('Erro ao atualizar documentos recentes:', error);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        refreshDocumentosRecentes();
        setInterval(refreshDocumentosRecentes, 10000);
    });
</script>
@endsection