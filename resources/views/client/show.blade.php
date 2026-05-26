<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pasta->nome }} - TopoGest</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(0, 51, 102, 0.7);
            border-radius: 999px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .glass {
            background: rgba(255,255,255,0.82);
            backdrop-filter: blur(18px);
        }

        .animate-fade {
            animation: fadeIn .25s ease;
        }

        .modal-overlay {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity .2s ease, visibility .2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
            padding: 1rem;
        }

        .modal-overlay.open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .modal-container {
            width: 100%;
            max-width: 40rem;
            max-height: calc(100vh - 2rem);
            overflow-y: auto;
        }

        @media (max-width: 768px) {
            .modal-overlay {
                align-items: flex-start;
                padding-top: 2rem;
                padding-bottom: 2rem;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="min-h-screen bg-gray-100 overflow-auto">

    <!-- Background -->
    <div class="fixed inset-0 z-0">
        <img
            src="{{ asset('images/background-topo.jpg') }}"
            alt="Background"
            class="w-full h-full object-cover"
        >
        <div class="absolute inset-0 bg-[#001C36]/40 backdrop-[2px]"></div>
    </div>

    <div class="relative z-10 min-h-screen px-4 md:px-8 py-6">

        <!-- HEADER -->
        <header class="max-w-7xl mx-auto mb-8">

            <div class="glass border border-white/40 rounded-[32px] shadow-2xl px-6 py-5">

                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

                    <!-- Logo -->
                    <div class="flex items-center gap-4">

                        <a href="{{ route('dashboard') }}"
                           class="flex items-center gap-4 group">

                            <div class="relative flex items-center justify-center w-16 h-16 rounded-2xl bg-white shadow-2xl transition group-hover:scale-105">
                                <img
                                    src="{{ asset('images/logo-icon.png') }}"
                                    alt="Logo"
                                    class="w-full h-full object-contain p-2"
                                >
                            </div>

                            <div class="hidden sm:flex relative items-center h-12 px-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                                <img
                                    src="{{ asset('images/logo-text.png') }}"
                                    alt="TopoGest"
                                    class="relative z-10 h-7 w-auto"
                                >
                            </div>
                        </a>

                    </div>

                    <!-- Pasta -->
                    <div class="flex justify-center">
                        <div class="bg-[#003366] text-white px-8 md:px-14 py-3 rounded-full shadow-xl border border-white/10">
                            <h1 class="text-sm md:text-lg font-black uppercase italic tracking-wide text-center">
                                {{ $pasta->nome }}
                            </h1>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-4">

                        <!-- Notifications -->
                        <a href="{{ route('notificacoes.index') }}"
                           class="relative w-12 h-12 rounded-full bg-[#003366] text-white flex items-center justify-center shadow-xl hover:bg-blue-900 transition border border-white/10">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-5 w-5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                                />
                            </svg>

                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="absolute top-1 right-1 w-3 h-3 bg-red-500 rounded-full border-2 border-[#003366] animate-pulse"></span>
                            @endif
                        </a>

                        <!-- User -->
                        <a href="{{ route('profile.edit') }}"
                           class="flex items-center gap-3 bg-[#003366] text-white rounded-2xl px-4 py-2 shadow-xl border border-white/10 hover:bg-[#002244] transition">

                            <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-white bg-[#004A7C] flex items-center justify-center">

                                @if(auth()->user()->photo)
                                    <img
                                        src="{{ asset('storage/' . auth()->user()->photo) }}"
                                        alt="Perfil"
                                        class="w-full h-full object-cover"
                                    >
                                @else
                                    <span class="font-black uppercase text-sm">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                                    </span>
                                @endif

                            </div>

                            <div class="hidden md:flex flex-col leading-tight">
                                <span class="text-[10px] uppercase tracking-widest opacity-70">
                                    Usuário
                                </span>

                                <span class="font-black uppercase text-sm">
                                    {{ explode(' ', Auth::user()->name)[0] }}
                                </span>
                            </div>

                        </a>

                    </div>

                </div>

            </div>

        </header>

        <!-- MAIN -->
        <main class="max-w-7xl mx-auto flex-1 mt-8">

                <!-- CONTENT AREA -->
                <section class="w-full space-y-8">

                    @php
                        $currentPasta = $pasta;
                        $codigoSigef = null;
                        while ($currentPasta) {
                            if ($currentPasta->codigo_sigef) {
                                $codigoSigef = $currentPasta->codigo_sigef;
                                break;
                            }
                            $currentPasta = $currentPasta->parent;
                        }
                    @endphp

                    @if($codigoSigef)
                    <!-- CERTIFICAÇÃO SIGEF (INCRA) -->
                    <div class="glass rounded-[36px] border border-white/40 shadow-2xl p-6 md:p-8 animate-fade">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                            <div class="flex items-start gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center text-3xl shadow-inner border border-blue-100 shrink-0">
                                    🌐
                                </div>
                                <div>
                                    <div class="flex items-center gap-3">
                                        <span class="uppercase tracking-[3px] text-xs text-[#003366] font-black">Certificação SIGEF</span>
                                        <span class="bg-green-100 text-green-800 text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase border border-green-200">Certificado</span>
                                    </div>
                                    <h2 class="text-xl md:text-2xl font-black text-[#003366] mt-1">
                                        Imóvel Certificado no SIGEF
                                    </h2>
                                    <p class="text-xs text-gray-500 font-mono mt-1 select-all">
                                        Código: {{ $codigoSigef }}
                                    </p>
                                </div>
                            </div>
                            
                            <a href="https://sigef.incra.gov.br/geo/parcela/detalhe/{{ $codigoSigef }}/" 
                               target="_blank" 
                               class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-2xl font-black uppercase text-xs tracking-wider shadow-lg transition flex items-center gap-2 self-start sm:self-auto">
                                <span>Ver Parcela</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    @endif

                    <!-- PASTAS (SUBPASTAS) -->
                    @if($pasta->subpastas->isNotEmpty())
                    <div class="glass rounded-[36px] border border-white/40 shadow-2xl p-6 md:p-8 animate-fade">

                        <div class="flex items-center justify-between mb-6 border-b border-[#003366]/10 pb-4">
                            <div class="bg-[#003366] text-white px-6 py-2 rounded-full shadow border border-white/10">
                                <span class="font-black uppercase italic tracking-wider text-xs">
                                    Subpastas
                                </span>
                            </div>
                            <div class="text-[#003366] font-bold text-xs">
                                {{ $pasta->subpastas->count() }} pasta(s)
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($pasta->subpastas as $sub)
                                <div class="bg-white/90 border border-white/50 rounded-3xl p-5 shadow-lg flex flex-col justify-between hover:scale-[1.01] transition">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-yellow-100 flex items-center justify-center text-2xl shrink-0">
                                            📁
                                        </div>
                                        <div class="min-w-0">
                                            <h3 class="font-black text-[#003366] uppercase text-sm truncate leading-snug">
                                                {{ $sub->nome }}
                                            </h3>
                                            <p class="text-xs text-gray-400 mt-0.5">
                                                Criada em {{ $sub->created_at->format('d/m/Y') }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-end mt-4 pt-4 border-t border-gray-100">
                                        <a href="{{ route('client.servico.show', $sub->id) }}"
                                           class="bg-[#003366] hover:bg-blue-900 text-white px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition flex items-center gap-1 shadow">
                                            <span>Abrir</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                    @endif

                    <!-- ARQUIVOS E PENDÊNCIAS -->
                    <div class="glass rounded-[36px] border border-white/40 shadow-2xl p-6 md:p-8 animate-fade">

                        <div class="flex items-center justify-between mb-6 border-b border-[#003366]/10 pb-4">
                            <div class="bg-[#003366] text-white px-6 py-2 rounded-full shadow border border-white/10">
                                <span class="font-black uppercase italic tracking-wider text-xs">
                                    Arquivos e Pendências
                                </span>
                            </div>
                            <div class="text-[#003366] font-bold text-xs">
                                {{ $pasta->arquivos->count() }} arquivo(s) • {{ $pasta->pendencias->count() }} pendência(s)
                            </div>
                        </div>

                        <div class="space-y-6">

                            <!-- PENDÊNCIAS -->
                            @if($pasta->pendencias->isNotEmpty())
                                <div class="space-y-4">
                                    <h4 class="text-xs uppercase tracking-wider font-bold text-yellow-700">Pendentes de Ação</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach($pasta->pendencias as $pen)
                                            <div class="bg-yellow-50 border border-yellow-200 rounded-3xl p-5 flex flex-col justify-between shadow">
                                                <div>
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <span class="text-lg">⚠️</span>
                                                        <h5 class="font-black text-yellow-800 uppercase italic text-sm">
                                                            {{ $pen->titulo }}
                                                        </h5>
                                                    </div>
                                                    <p class="text-xs text-yellow-700/80 leading-relaxed">
                                                        {{ $pen->descricao }}
                                                    </p>
                                                </div>
                                                <div class="flex items-center justify-between gap-4 mt-6 pt-4 border-t border-yellow-200/50">
                                                    <span class="bg-yellow-100 text-yellow-800 text-[9px] font-bold px-2 py-0.5 rounded-full uppercase border border-yellow-200">
                                                        Aguardando Envio
                                                    </span>
                                                    <button
                                                        onclick="openUploadModal('{{ $pen->titulo }}')"
                                                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-1.5 rounded-xl font-bold uppercase text-[10px] tracking-wider transition shadow"
                                                    >
                                                        Enviar Arquivo
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- ARQUIVOS -->
                            <div class="space-y-4">
                                <h4 class="text-xs uppercase tracking-wider font-bold text-[#003366]/70">Documentos Disponíveis</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    @forelse($pasta->arquivos as $arq)
                                        <div class="bg-white/95 border border-white/60 rounded-3xl p-5 shadow-lg flex flex-col justify-between hover:scale-[1.01] transition">
                                            <div class="flex items-start gap-4 mb-4">
                                                <div class="w-12 h-12 rounded-2xl bg-[#003366]/10 flex items-center justify-center text-2xl shrink-0">
                                                    📄
                                                </div>
                                                <div class="min-w-0">
                                                    <h5 class="font-black text-[#003366] uppercase text-sm truncate leading-snug">
                                                        {{ $arq->nome }}
                                                    </h5>
                                                    <p class="text-[10px] text-gray-400 mt-1 uppercase font-bold">
                                                        {{ $arq->tipo }} • {{ $arq->tamanho }} MB
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gray-100">
                                                <button
                                                    type="button"
                                                    onclick="openPreviewModal('{{ asset('storage/'.$arq->path) }}', '{{ $arq->tipo }}', '{{ addslashes($arq->nome) }}')"
                                                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-xl font-black uppercase text-[10px] text-center transition shadow"
                                                >
                                                    Visualizar
                                                </button>

                                                <a href="{{ asset('storage/'.$arq->path) }}"
                                                   download
                                                   class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 rounded-xl font-black uppercase text-[10px] text-center transition shadow"
                                                >
                                                    Download
                                                </a>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-span-full py-8 text-center text-gray-500 italic text-sm">
                                            Nenhum documento disponível nesta pasta.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                        </div>

                    </div>

                </section>

        </main>

    </div>

    <!-- BOTÃO VOLTAR -->
    <a href="{{ route('dashboard') }}"
       class="fixed bottom-6 right-6 z-40 w-14 h-14 rounded-full bg-white text-[#003366] shadow-2xl flex items-center justify-center hover:scale-105 hover:bg-gray-100 transition"
    >
        <svg xmlns="http://www.w3.org/2000/svg"
             class="h-6 w-6"
             fill="none"
             viewBox="0 0 24 24"
             stroke="currentColor">

            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M10 19l-7-7m0 0l7-7m-7 7h18"/>

        </svg>
    </a>

    <!-- MODAL UPLOAD -->
    <div id="uploadModal"
         class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm p-4 modal-overlay">

        <div class="modal-container bg-[#003366] text-white rounded-[38px] p-8 md:p-10 w-full shadow-2xl border border-white/10 animate-fade">

            <form
                action="{{ route('arquivos.store', $pasta->id) }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf

                <div class="text-center mb-8">

                    <h2 class="text-3xl font-black italic uppercase mb-2">
                        Enviar Arquivo
                    </h2>

                    <p id="labelNomeArquivo"
                       class="text-yellow-400 font-bold uppercase italic text-sm">
                    </p>

                </div>

                <input type="hidden" name="nome" id="inputNomeArquivo">

                <div class="mb-8">

                    <div class="relative border-2 border-dashed border-white/30 rounded-3xl bg-white/10 hover:bg-white/15 transition overflow-hidden">

                        <label class="absolute inset-0 flex items-center justify-center cursor-pointer">
                            <input
                                type="file"
                                name="arquivo"
                                id="fileInput"
                                required
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                onchange="showFileName(this)"
                            >
                        </label>

                        <div class="h-56 flex flex-col items-center justify-center px-6 text-center">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-14 w-14 text-white/80 mb-4"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.5"
                                      d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>

                            </svg>

                            <p id="uploadText"
                               class="font-bold text-white/80 italic">
                                Clique ou arraste o arquivo aqui
                            </p>

                            <p id="fileNameDisplay"
                               class="hidden text-green-300 font-black mt-3 break-all">
                            </p>

                        </div>

                    </div>

                </div>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">

                    <button
                        type="submit"
                        class="flex-1 bg-green-500 hover:bg-green-600 text-white py-3 rounded-2xl font-black uppercase shadow-xl transition"
                    >
                        Enviar
                    </button>

                    <button
                        type="button"
                        onclick="closeModal('uploadModal')"
                        class="flex-1 bg-red-500 hover:bg-red-600 text-white py-3 rounded-2xl font-black uppercase shadow-xl transition"
                    >
                        Cancelar
                    </button>

                </div>

            </form>

        </div>

    </div>

    <!-- MODAL PREVIEW -->
    <div id="previewModal"
         class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm p-4 modal-overlay">

        <div class="modal-container bg-[#003366] text-white rounded-[38px] p-8 md:p-10 w-full max-w-4xl shadow-2xl border border-white/10 animate-fade flex flex-col">

            <div class="flex justify-between items-center mb-6">
                <h3 id="previewTitle" class="text-2xl font-black italic uppercase text-yellow-400">
                    Visualizar Arquivo
                </h3>
                <button type="button" onclick="closeModal('previewModal')" class="text-white/60 hover:text-white text-3xl leading-none">&times;</button>
            </div>

            <div id="previewContent" class="flex-1 flex items-center justify-center min-h-[50vh] max-h-[70vh] overflow-auto bg-black/20 rounded-2xl p-4">
                <!-- Content injected dynamically -->
            </div>

        </div>

    </div>

    <script>
        function openUploadModal(nome) {

            document.getElementById('inputNomeArquivo').value = nome;

            document.getElementById('labelNomeArquivo').innerText =
                'Anexar arquivo para: ' + nome;

            const modal = document.getElementById('uploadModal');

            modal.classList.add('open');

            document.getElementById('fileInput').value = '';

            document.getElementById('fileNameDisplay').classList.add('hidden');

            document.getElementById('uploadText').classList.remove('hidden');
        }

        function closeModal(id) {

            const modal = document.getElementById(id);

            modal.classList.remove('open');
        }

        function openPreviewModal(url, type, name) {
            document.getElementById('previewTitle').innerText = name;
            const container = document.getElementById('previewContent');
            container.innerHTML = ''; // Clear previous content

            type = type.toLowerCase();
            if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(type)) {
                container.innerHTML = `<img src="${url}" class="max-w-full max-h-[65vh] object-contain rounded-xl shadow-2xl">`;
            } else if (type === 'pdf') {
                container.innerHTML = `<iframe src="${url}" class="w-full h-[65vh] rounded-xl" frameborder="0"></iframe>`;
            } else {
                container.innerHTML = `
                    <div class="text-center py-10">
                        <span class="text-5xl mb-4 block">📁</span>
                        <p class="text-white font-bold mb-2">Visualização não disponível</p>
                        <p class="text-white/60 text-sm mb-6">Arquivos do tipo .${type.toUpperCase()} não podem ser visualizados diretamente no navegador.</p>
                        <a href="${url}" download class="inline-flex items-center bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-2xl font-black uppercase text-xs transition">
                            Baixar Arquivo
                        </a>
                    </div>
                `;
            }

            const modal = document.getElementById('previewModal');
            modal.classList.add('open');
        }

        function showFileName(input) {

            const display = document.getElementById('fileNameDisplay');

            const text = document.getElementById('uploadText');

            if (input.files && input.files.length > 0) {

                display.innerText = '✓ ' + input.files[0].name;

                display.classList.remove('hidden');

                text.classList.add('hidden');

            } else {

                display.classList.add('hidden');

                text.classList.remove('hidden');
            }
        }

        window.addEventListener('keydown', function(e) {

            if (e.key === 'Escape') {
                closeModal('uploadModal');
                closeModal('previewModal');
            }
        });
    </script>

</body>
</html>