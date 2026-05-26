<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perguntas Frequentes - TopoGest</title>

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
                         alt="TopoGest"
                         class="w-full h-full object-contain p-2">
                </div>

                <div class="relative flex items-center h-14 px-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                    <img src="{{ asset('images/logo-text.png') }}"
                         alt="TopoGest"
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
                            Tire suas dúvidas sobre serviços, documentos, acompanhamento e funcionamento da plataforma TopoGest.
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
                            'question' => 'Como acompanho o progresso do meu serviço?',
                            'answer' => 'Basta acessar o seu painel e clicar em “Meus Serviços”. O status é atualizado em tempo real pela nossa equipe, mostrando cada etapa do processo como: “Equipe em Campo”, “Em fase de Desenho” ou “Finalizado”.',
                        ],
                        [
                            'question' => 'Onde encontro meus arquivos finais?',
                            'answer' => 'Todos os arquivos ficam disponíveis permanentemente na área “Meus Arquivos”. Você poderá baixar documentos em formatos como PDF, DWG e memoriais técnicos sempre que precisar, sem custo adicional.',
                        ],
                        [
                            'question' => 'Como envio a documentação inicial?',
                            'answer' => 'Ao iniciar um novo serviço, o sistema abrirá automaticamente um campo de upload de arquivos, permitindo enviar fotos, PDFs ou documentos diretamente pelo celular, tablet ou computador.',
                        ],
                        [
                            'question' => 'Posso agendar uma visita técnica?',
                            'answer' => 'Sim. Na área do cliente, utilize o botão “Agendar Horário” para visualizar datas e horários disponíveis em nosso calendário de atendimento técnico.',
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