<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Localizador de Base - Getec Topografia</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-thumb {
            background: #004A7C;
            border-radius: 999px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
        }

        .glass {
            background: rgba(255,255,255,0.18);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .glass-dark {
            background: rgba(0, 51, 102, 0.55);
            backdrop-filter: blur(18px);
        }

        .animate-fade {
            animation: fade .25s ease;
        }

        @keyframes fade {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">
</head>

<body class="min-h-screen bg-gray-100 overflow-x-hidden">

    <!-- Background -->
    <div class="fixed inset-0 z-0">
        <img src="{{ asset('images/background-topo.jpg') }}"
             class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-black/40"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto p-4 md:p-10"
         x-data="{ modal:false }">

        <!-- HEADER -->
        <div class="flex flex-col xl:flex-row justify-between gap-8 mb-10">

            <!-- LOGO -->
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-4 group w-fit">

                <div class="relative flex items-center justify-center w-20 h-20 rounded-2xl bg-white shadow-2xl transition group-hover:scale-105">
                    <img src="{{ asset('images/logo-icon.png') }}"
                         alt="Getec Topografia"
                         class="w-full h-full object-contain p-2">
                </div>

                <div class="relative flex items-center h-14 px-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                    <img src="{{ asset('images/logo-text.png') }}"
                         alt="Getec Topografia"
                         class="relative z-10 h-8 w-auto">
                </div>
            </a>

            <!-- BUSCAS -->
            <div class="flex flex-col gap-4 w-full xl:w-auto">

                <!-- Coordenadas -->
                <form action="{{ route('admin.bases.buscar') }}"
                      method="GET"
                      class="glass rounded-3xl p-4 shadow-2xl border border-white/20">

                    <div class="flex flex-col md:flex-row gap-3 items-center">

                        <input type="number"
                               step="any"
                               name="norte"
                               placeholder="Coordenada Norte"
                               class="w-full md:w-52 bg-[#003366]/90 text-white px-5 py-3 rounded-2xl outline-none placeholder:text-white/60 font-semibold">

                        <input type="number"
                               step="any"
                               name="este"
                               placeholder="Coordenada Este"
                               class="w-full md:w-52 bg-[#003366]/90 text-white px-5 py-3 rounded-2xl outline-none placeholder:text-white/60 font-semibold">

                        <button type="submit"
                                class="bg-orange-500 hover:bg-orange-600 text-white font-black uppercase px-8 py-3 rounded-2xl transition shadow-lg whitespace-nowrap">
                            Buscar Próxima
                        </button>

                    </div>
                </form>

                <!-- Nome -->
                <form action="{{ route('admin.bases.buscar') }}"
                      method="GET"
                      class="glass rounded-3xl p-4 shadow-2xl border border-white/20">

                    <div class="flex flex-col md:flex-row gap-3">

                        <input type="text"
                               name="nome"
                               placeholder="Pesquisar por nome..."
                               class="flex-1 bg-[#003366]/90 text-white px-5 py-3 rounded-2xl outline-none placeholder:text-white/60 font-semibold">

                        <button type="submit"
                                class="bg-[#004A7C] hover:bg-[#003055] text-white font-black uppercase px-8 py-3 rounded-2xl transition shadow-lg">
                            Buscar
                        </button>

                    </div>
                </form>
            </div>
        </div>

        <!-- CONTAINER -->
        <div class="glass rounded-[35px] border border-white/20 shadow-2xl p-6 md:p-10">

            <!-- TOPO -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">

                <div>
                    <h1 class="text-3xl font-black text-white uppercase italic">
                        Bases Cadastradas
                    </h1>

                    <p class="text-white/70 mt-1">
                        Gerencie e localize bases cadastradas no sistema.
                    </p>
                </div>

                <button @click="modal=true"
                        class="bg-green-500 hover:bg-green-600 text-white px-8 py-3 rounded-2xl font-black uppercase shadow-xl transition">
                    + Adicionar Base
                </button>
            </div>

            <!-- TABELA -->
            <div class="overflow-x-auto rounded-3xl">

                <table class="w-full border-separate border-spacing-y-3">

                    <thead>
                        <tr class="text-white uppercase text-sm">
                            <th class="text-left px-6 py-3">Base</th>
                            <th class="text-center px-6 py-3">Norte</th>
                            <th class="text-center px-6 py-3">Este</th>

                            @if(isset($filtroCoordenada) && $filtroCoordenada)
                                <th class="text-center px-6 py-3">Distância</th>
                            @endif

                            <th class="text-center px-6 py-3">Arquivo</th>
                            <th class="text-center px-6 py-3">Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($bases as $base)

                        @php
                            $isDestaque = (isset($destaqueId) && $destaqueId == $base->id);
                        @endphp

                        <tr class="
                            {{ $isDestaque
                                ? 'bg-green-500/20 border border-green-400'
                                : 'bg-white/10 hover:bg-white/20'
                            }}
                            transition rounded-3xl
                        ">

                            <!-- Nome -->
                            <td class="px-6 py-5 rounded-l-3xl">

                                @if($isDestaque)
                                    <span class="text-[11px] font-black text-green-300 uppercase block mb-1">
                                        Mais Próxima
                                    </span>
                                @endif

                                <span class="font-bold text-white uppercase">
                                    {{ $base->nome }}
                                </span>

                            </td>

                            <!-- Norte -->
                            <td class="text-center text-white font-semibold px-6">
                                @if(!empty($base->norte_original))
                                    {{ $base->norte_original }}
                                @else
                                    {{ number_format($base->norte, 3, ',', '.') }}
                                @endif
                            </td>

                            <!-- Este -->
                            <td class="text-center text-white font-semibold px-6">
                                @if(!empty($base->este_original))
                                    {{ $base->este_original }}
                                @else
                                    {{ number_format($base->este, 3, ',', '.') }}
                                @endif
                            </td>

                            <!-- Distância -->
                            @if(isset($filtroCoordenada) && $filtroCoordenada)
                                <td class="text-center text-orange-300 font-black px-6">
                                    {{ number_format($base->distancia, 2, ',', '.') }} m
                                </td>
                            @endif

                            <!-- ZIP -->
                            <td class="text-center px-6">

                                @if($base->arquivo_zip)

                                    <a href="{{ asset('storage/' . $base->arquivo_zip) }}"
                                       download="{{ $base->nome }}.zip"
                                       class="bg-[#004A7C] hover:bg-[#003055] text-white px-5 py-2 rounded-xl text-xs uppercase font-bold transition">
                                        Download
                                    </a>

                                @else
                                    <span class="text-white/30">N/A</span>
                                @endif

                            </td>

                            <!-- AÇÕES -->
                            <td class="text-center px-6 rounded-r-3xl">

                                <div class="flex justify-center gap-2">

                                    @php
                                        $mapUrl = route('admin.bases.mapa', $base->id);
                                        if (request()->filled('norte') && request()->filled('este')) {
                                            $mapUrl .= '?norte=' . urlencode(request('norte')) . '&este=' . urlencode(request('este'));
                                        }
                                    @endphp

                                    <a href="{{ $mapUrl }}"
                                       class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2 rounded-xl text-xs font-black uppercase transition">
                                        Mapa
                                    </a>

                                    @if($base->google_maps_url)
                                        <button type="button"
                                                onclick="shareBaseLocation('{{ $base->nome }}', '{{ $base->google_maps_url }}')"
                                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-xl text-xs font-black uppercase transition flex items-center justify-center gap-1.5 shadow-md">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 10.742l4.636-2.318M8.684 13.258l4.636 2.318M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Compartilhar
                                        </button>
                                    @endif

                                    <form action="{{ route('admin.bases.destroy', $base->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Deseja excluir esta base?')">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="bg-red-500 hover:bg-red-600 text-white px-5 py-2 rounded-xl text-xs font-black uppercase transition">
                                            Excluir
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6"
                                class="text-center py-20 text-white/60 uppercase font-bold">
                                Nenhuma base encontrada.
                            </td>
                        </tr>

                    @endforelse

                    </tbody>
                </table>
            </div>

            <div class="mt-6 px-6 py-6 rounded-b-3xl bg-white/10 border-t border-white/20">
                {{ $bases->links('vendor.pagination.topogest') }}
            </div>
        </div>

        <!-- BOTÃO VOLTAR -->
        <div class="mt-6">
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center gap-2 text-white/70 hover:text-white transition">

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-5 h-5"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M15 19l-7-7 7-7"/>

                </svg>

                Voltar ao painel
            </a>
        </div>

        <!-- MODAL -->
        <div x-show="modal"
             x-transition
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">

            <div class="glass-dark rounded-[35px] w-full max-w-2xl p-8 border border-white/10 shadow-2xl animate-fade">

                <div class="flex justify-between items-center mb-8">

                    <h2 class="text-3xl text-white font-black uppercase italic">
                        Nova Base
                    </h2>

                    <button @click="modal=false"
                            class="text-white/60 hover:text-white text-3xl leading-none">
                        ×
                    </button>
                </div>

                <form action="{{ route('admin.bases.store') }}"
                      method="POST"
                      enctype="multipart/form-data"
                      class="space-y-6">

                    @csrf

                    <!-- Nome -->
                    <div>
                        <label class="block text-white/70 uppercase text-sm font-bold mb-2">
                            Nome da Base
                        </label>

                        <input type="text"
                               name="nome"
                               required
                               class="w-full bg-white text-[#003366] px-5 py-4 rounded-2xl outline-none font-semibold">
                    </div>

                    <!-- Coordenadas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div>
                            <label class="block text-white/70 uppercase text-sm font-bold mb-2">
                                Norte
                            </label>

                            <input type="number"
                                   step="any"
                                   name="norte"
                                   required
                                   class="w-full bg-white text-[#003366] px-5 py-4 rounded-2xl outline-none font-semibold">
                        </div>

                        <div>
                            <label class="block text-white/70 uppercase text-sm font-bold mb-2">
                                Este
                            </label>

                            <input type="number"
                                   step="any"
                                   name="este"
                                   required
                                   class="w-full bg-white text-[#003366] px-5 py-4 rounded-2xl outline-none font-semibold">
                        </div>

                    </div>

                    <!-- Upload -->
                    <div>

                        <label class="block text-white/70 uppercase text-sm font-bold mb-3">
                            Arquivo ZIP
                        </label>

                        <div class="border-2 border-dashed border-white/20 rounded-3xl p-8 text-center hover:bg-white/5 relative">

                            <label class="relative w-full h-full flex items-center justify-center cursor-pointer">
                                <div class="pointer-events-none text-white">
                                    Clique para selecionar um arquivo .zip ou arraste aqui
                                </div>

                                <input type="file"
                                       name="arquivo_zip"
                                       accept=".zip"
                                       required
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            </label>

                        </div>
                    </div>

                    <!-- BOTÕES -->
                    <div class="flex flex-col md:flex-row gap-4 pt-4">

                        <button type="submit"
                                class="flex-1 bg-green-500 hover:bg-green-600 text-white py-4 rounded-2xl font-black uppercase transition shadow-lg">
                            Salvar Base
                        </button>

                        <button type="button"
                                @click="modal=false"
                                class="flex-1 bg-red-500 hover:bg-red-600 text-white py-4 rounded-2xl font-black uppercase transition shadow-lg">
                            Cancelar
                        </button>

                    </div>

                </form>

            </div>
        </div>
    </div>

    <script>
        function shareBaseLocation(nome, url) {
            if (navigator.share) {
                navigator.share({
                    title: 'Localização da Base: ' + nome,
                    text: 'Confira a localização da base "' + nome + '" no Google Maps.',
                    url: url
                }).catch(err => {
                    console.log('Erro ao compartilhar:', err);
                });
            } else {
                navigator.clipboard.writeText(url).then(() => {
                    Swal.fire({
                        title: 'Link Copiado!',
                        text: 'O link do Google Maps para a base "' + nome + '" foi copiado para a área de transferência.',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 2000,
                        background: '#003366',
                        color: '#fff',
                        borderRadius: 20
                    });
                    setTimeout(() => {
                        window.open(url, '_blank');
                    }, 1000);
                }).catch(err => {
                    window.open(url, '_blank');
                });
            }
        }
    </script>
</body>
</html>