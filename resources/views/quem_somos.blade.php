<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quem Somos - TopoGest</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#D9D9D9] overflow-x-hidden">

    <!-- Background -->
    <div class="fixed inset-0 -z-10">
        <div class="absolute inset-0 bg-cover bg-center bg-fixed"
             style="background-image: url('{{ asset('images/background-topo.jpg') }}');">
        </div>

        <!-- Overlay -->
        <div class="absolute inset-0 bg-[#001B33]/55 backdrop-blur-[2px]"></div>
    </div>

    <div class="relative z-10 w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">

        <!-- Header -->
        @include('includes.header-public')

        <!-- Main Card -->
        <div class="bg-white/90 backdrop-blur-xl border border-white/40 rounded-[32px] lg:rounded-[42px] shadow-2xl overflow-hidden">

            <!-- Top Banner -->
            <div class="relative px-8 md:px-14 py-10 md:py-14 bg-gradient-to-r from-[#003366] via-[#004A7C] to-[#005B96] text-white">

                <div class="absolute inset-0 opacity-10">
                    <div class="w-full h-full bg-[radial-gradient(circle_at_top_right,white,transparent_45%)]"></div>
                </div>

                <div class="relative z-10 max-w-3xl">
                    <span class="inline-flex items-center px-5 py-2 rounded-full bg-white/10 border border-white/20 text-sm font-bold uppercase tracking-[0.2em] mb-6">
                        TopoGest
                    </span>

                    <h1 class="text-4xl md:text-5xl font-black leading-tight mb-6">
                        Tecnologia e precisão para a gestão topográfica moderna
                    </h1>

                    <p class="text-lg md:text-xl leading-relaxed text-white/90 font-medium">
                        Conectamos equipes de campo, clientes e processos técnicos
                        em uma plataforma inteligente, organizada e transparente.
                    </p>

                    @if(!empty($conteudo['whatsapp']))
                        <div class="mt-8 flex flex-col sm:flex-row gap-4">
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
            </div>

            <!-- Content -->
            <div class="px-8 md:px-14 py-10 md:py-14 text-[#003366]">

                <!-- Sobre -->
                <section class="mb-14">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-14 h-14 rounded-2xl bg-[#003366] text-white flex items-center justify-center shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M13 16h-1v-4h-1m1-4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z"/>
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-3xl font-black">Quem Somos</h2>
                            <p class="text-sm uppercase tracking-[0.2em] text-[#004A7C]/70 font-bold mt-1">
                                Engenharia + Tecnologia
                            </p>
                        </div>
                    </div>

                    <div class="space-y-8 text-lg leading-relaxed text-[#003366]/90 font-medium">
                        @if(!empty($conteudo['quem_somos']))
                            {!! nl2br(e($conteudo['quem_somos'])) !!}
                        @else
                            <p>
                                O <span class="font-black">TopoGest</span> nasceu com o propósito
                                de modernizar a forma como serviços topográficos são organizados,
                                acompanhados e entregues. Nossa plataforma centraliza informações,
                                melhora a comunicação entre equipes e reduz falhas operacionais.
                            </p>

                            <p>
                                Transformamos processos técnicos tradicionais em fluxos digitais
                                inteligentes, proporcionando mais eficiência para profissionais e
                                transparência total para os clientes.
                            </p>

                            <p>
                                Com um ambiente intuitivo e seguro, oferecemos controle completo
                                sobre arquivos, solicitações, pendências e acompanhamento em tempo real.
                            </p>
                        @endif
                    </div>
                </section>

                <!-- Missão / Visão -->
                <section class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-14">

                    <!-- Missão -->
                    <div class="bg-[#F5F7FA] border border-[#003366]/10 rounded-3xl p-8 shadow-sm hover:shadow-xl transition duration-300">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 rounded-2xl bg-green-500 text-white flex items-center justify-center shadow-md">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>

                            <h3 class="text-2xl font-black">
                                Nossa Missão
                            </h3>
                        </div>

                        <p class="text-lg leading-relaxed font-medium text-[#003366]/90">
                            Proporcionar autonomia, precisão e eficiência na gestão de serviços
                            geográficos, integrando tecnologia, organização e excelência técnica.
                        </p>
                    </div>

                    <!-- Visão -->
                    <div class="bg-[#F5F7FA] border border-[#003366]/10 rounded-3xl p-8 shadow-sm hover:shadow-xl transition duration-300">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14m-6 2H5a2 2 0 01-2-2V10a2 2 0 012-2h4l5-3v14l-5-3z"/>
                                </svg>
                            </div>

                            <h3 class="text-2xl font-black">
                                Nossa Visão
                            </h3>
                        </div>

                        <p class="text-lg leading-relaxed font-medium text-[#003366]/90">
                            Ser referência em inovação e integração digital para o setor topográfico,
                            aproximando clientes, equipes técnicas e gestão operacional.
                        </p>
                    </div>
                </section>

                <!-- Valores -->
                <section>

                    <div class="flex items-center gap-4 mb-10">
                        <div class="w-14 h-14 rounded-2xl bg-[#003366] text-white flex items-center justify-center shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3
                                      3-1.343 3-3-1.343-3-3-3zm0 0V5m0 14v-3m7-5h-3M8
                                      12H5m11.364 4.364l-2.121-2.121M9.757
                                      9.757L7.636 7.636m8.728
                                      0l-2.121 2.121M9.757 14.243l-2.121 2.121"/>
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-3xl font-black">Nossos Valores</h2>
                            <p class="text-sm uppercase tracking-[0.2em] text-[#004A7C]/70 font-bold mt-1">
                                O que move nossa plataforma
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        <!-- Precisão -->
                        <div class="bg-white border border-[#003366]/10 rounded-3xl p-8 shadow-sm hover:-translate-y-1 hover:shadow-xl transition duration-300">
                            <div class="w-14 h-14 rounded-2xl bg-[#003366] text-white flex items-center justify-center mb-6 shadow-md">
                                ✓
                            </div>

                            <h3 class="text-2xl font-black mb-4">
                                Precisão
                            </h3>

                            <p class="text-lg leading-relaxed font-medium text-[#003366]/80">
                                Rigor técnico em cada funcionalidade e em cada etapa do processo.
                            </p>
                        </div>

                        <!-- Transparência -->
                        <div class="bg-white border border-[#003366]/10 rounded-3xl p-8 shadow-sm hover:-translate-y-1 hover:shadow-xl transition duration-300">
                            <div class="w-14 h-14 rounded-2xl bg-[#004A7C] text-white flex items-center justify-center mb-6 shadow-md">
                                ◎
                            </div>

                            <h3 class="text-2xl font-black mb-4">
                                Transparência
                            </h3>

                            <p class="text-lg leading-relaxed font-medium text-[#003366]/80">
                                Informações claras, acessíveis e atualizadas em tempo real.
                            </p>
                        </div>

                        <!-- Inovação -->
                        <div class="bg-white border border-[#003366]/10 rounded-3xl p-8 shadow-sm hover:-translate-y-1 hover:shadow-xl transition duration-300">
                            <div class="w-14 h-14 rounded-2xl bg-[#005B96] text-white flex items-center justify-center mb-6 shadow-md">
                                ⚡
                            </div>

                            <h3 class="text-2xl font-black mb-4">
                                Inovação
                            </h3>

                            <p class="text-lg leading-relaxed font-medium text-[#003366]/80">
                                Tecnologia aplicada para resolver desafios reais do setor.
                            </p>
                        </div>

                    </div>
                </section>

            </div>
        </div>
    </div>

</body>
</html>