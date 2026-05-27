<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perguntas Frequentes - Getec Topografia</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        .faq-item summary::-webkit-details-marker {
            display: none;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-200 overflow-x-hidden">

    <!-- Background -->
    <div class="fixed inset-0 z-0 bg-cover bg-center bg-no-repeat bg-fixed"
         style="background-image: url('{{ asset('images/background-topo.jpg') }}');">
    </div>

    <!-- Overlay -->
    <div class="fixed inset-0 z-0 bg-[#001826]/50 backdrop-blur-[2px]"></div>

    <div class="relative z-10 min-h-screen px-6 py-8 md:px-10 lg:px-16">

        <!-- Header -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-10">

            <!-- Logo -->
            <a href="{{ route('welcome') }}"
               class="flex items-center gap-5 group w-fit">

                <div class="relative flex items-center justify-center w-20 h-20 rounded-2xl bg-white shadow-2xl transition group-hover:scale-105">
                    <img src="{{ asset('images/logo-icon.png') }}"
                         alt="Getec Topografia"
                         class="w-full h-full object-contain p-2">
                </div>

                <div class="relative flex items-center h-14 px-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                    <img src="{{ asset('images/logo-text.png') }}"
                         alt="Getec Topografia"
                         class="relative z-10 h-8 object-contain">
                </div>
            </a>

            <!-- Botão -->
            <button onclick="history.back()"
                class="bg-[#003366] hover:bg-[#002244] text-white px-8 py-3 rounded-2xl font-black uppercase italic shadow-2xl transition-all duration-300 hover:scale-105 active:scale-95 w-fit">
                ← Voltar
            </button>

        </div>

        <!-- Card Principal -->
        <div class="max-w-6xl mx-auto bg-white/88 backdrop-blur-2xl border border-white/40 rounded-[40px] shadow-2xl overflow-hidden">

            <!-- Header Interno -->
            <div class="bg-[#003366] px-10 py-8 text-white">

                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                    <div>
                        <h1 class="text-4xl md:text-5xl font-black italic uppercase tracking-tight">
                            Perguntas Frequentes
                        </h1>

                        <p class="mt-3 text-white/80 text-lg italic max-w-2xl">
                            Tire suas dúvidas sobre serviços, documentos, acompanhamento e funcionamento da plataforma Getec Topografia.
                        </p>

                        @if(!empty($conteudo['whatsapp']))
                            <div class="mt-6 flex flex-col sm:flex-row gap-4">
                                @if(!empty($conteudo['whatsapp']['contato']))
                                    <a href="{{ $conteudo['whatsapp']['contato'] }}" target="_blank" class="inline-flex items-center justify-center rounded-3xl bg-white text-[#003366] px-6 py-3 font-black uppercase tracking-[1px] shadow-lg shadow-black/10 hover:bg-slate-100 transition">
                                        Contato
                                    </a>
                                @endif
                                @if(!empty($conteudo['whatsapp']['suporte']))
                                    <a href="{{ $conteudo['whatsapp']['suporte'] }}" target="_blank" class="inline-flex items-center justify-center rounded-3xl bg-[#FFD166] text-[#003366] px-6 py-3 font-black uppercase tracking-[1px] shadow-lg shadow-black/10 hover:bg-[#FFCD4A] transition">
                                        Suporte
                                    </a>
                                @endif
                                @if(!empty($conteudo['whatsapp']['agendar']))
                                    <a href="{{ $conteudo['whatsapp']['agendar'] }}" target="_blank" class="inline-flex items-center justify-center rounded-3xl bg-[#06D6A0] text-white px-6 py-3 font-black uppercase tracking-[1px] shadow-lg shadow-black/10 hover:bg-[#05BF8B] transition">
                                        Agendar Horário
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="hidden lg:flex items-center justify-center">
                        <div class="bg-white/10 border border-white/20 rounded-3xl px-6 py-4 backdrop-blur-md">
                            <span class="text-sm uppercase tracking-[4px] font-black text-white/70">
                                FAQ
                            </span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Conteúdo -->
            <div class="p-6 md:p-10 lg:p-14">

                @php
                    $faqItems = $conteudo['faq'] ?? [
                        [
                            'question' => 'O que é Georreferenciamento de Imóveis Rurais (SIGEF/INCRA)?',
                            'answer' => 'É o processo de definição da exata forma, dimensão e localização de uma propriedade rural através de métodos de levantamento topográfico de alta precisão. É obrigatório por lei para desmembramentos, parcelamentos, remembramentos e transferências.',
                        ],
                        [
                            'question' => 'Qual a diferença entre CAR e Georreferenciamento?',
                            'answer' => 'O CAR (Cadastro Ambiental Rural) é um registro eletrônico focado nas informações ambientais da propriedade (APPs, Reserva Legal, etc). Já o Georreferenciamento foca nos limites físicos e dominiais do terreno perante o INCRA e cartórios.',
                        ],
                        [
                            'question' => 'Quanto tempo demora um levantamento topográfico?',
                            'answer' => 'Depende do tamanho da área, da complexidade do terreno (vegetação fechada, relevo) e das condições climáticas. O trabalho de campo pode levar de um dia a várias semanas. Após o campo, realizamos o processamento dos dados e geração das plantas.',
                        ],
                        [
                            'question' => 'Como acompanho o andamento do meu serviço?',
                            'answer' => 'Como nosso cliente, você tem acesso exclusivo a esta plataforma. Basta entrar com seu login para ver o status em tempo real (ex: "Equipe em Campo", "Desenhando Planta"), baixar seus PDFs, memoriais e arquivos DWG sempre que precisar.',
                        ],
                        [
                            'question' => 'Vocês atuam em áreas urbanas?',
                            'answer' => 'Sim! Realizamos levantamentos planialtimétricos, desdobros, locação de obras e retificação de área para terrenos urbanos, auxiliando em projetos arquitetônicos e regularização na prefeitura.',
                        ],
                    ];
                @endphp

                <div class="space-y-6">
                    @foreach($faqItems as $index => $item)
                        <details class="faq-item group bg-[#F5F7FA] hover:bg-white border border-gray-200 rounded-3xl transition-all duration-300 shadow-sm hover:shadow-xl overflow-hidden" @if($index === 0) open @endif>
                            <summary class="cursor-pointer flex items-center justify-between gap-6 px-8 py-7">
                                <div class="flex items-start gap-5">
                                    <div class="bg-[#003366] text-white min-w-[52px] h-[52px] rounded-2xl flex items-center justify-center shadow-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                        </svg>
                                    </div>

                                    <div>
                                        <h2 class="text-[#003366] text-2xl font-black italic leading-tight">
                                            {{ $item['question'] ?? 'Pergunta' }}
                                        </h2>

                                        <p class="text-gray-500 text-sm italic mt-2">
                                            {{ mb_strimwidth($item['answer'] ?? '', 0, 60, '...') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="text-[#003366] transition-transform duration-300 group-open:rotate-180">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </summary>

                            <div class="px-8 pb-8 pt-2">
                                <div class="border-l-4 border-[#003366]/20 pl-6">
                                    <p class="text-lg leading-relaxed text-gray-700 font-medium">
                                        {{ $item['answer'] ?? '' }}
                                    </p>
                                </div>
                            </div>
                        </details>
                    @endforeach
                </div>

            </div>
        </div>

    </div>
</body>
</html>