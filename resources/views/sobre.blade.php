<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre - Getec Topografia</title>

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

        <!-- Header Público -->
        @include('includes.header-public')

        <!-- Main Card -->
        <div class="bg-white/90 backdrop-blur-xl border border-white/40 rounded-[32px] lg:rounded-[42px] shadow-2xl overflow-hidden">

            <!-- Hero -->
            <div class="relative px-8 md:px-14 py-10 md:py-14 bg-gradient-to-r from-[#003366] via-[#004A7C] to-[#005B96] text-white">

                <div class="absolute inset-0 opacity-10">
                    <div class="w-full h-full bg-[radial-gradient(circle_at_top_right,white,transparent_45%)]"></div>
                </div>

                <div class="relative z-10 max-w-3xl">
                    <span class="inline-flex items-center px-5 py-2 rounded-full bg-white/10 border border-white/20 text-sm font-bold uppercase tracking-[0.2em] mb-6">
                        Engenharia & Topografia
                    </span>

                    <h1 class="text-4xl md:text-5xl font-black leading-tight mb-6">
                        Excelência e precisão em serviços de Georreferenciamento
                    </h1>

                    <p class="text-lg md:text-xl leading-relaxed text-white/90 font-medium">
                        A Getec Topografia oferece soluções completas em Georreferenciamento, Levantamentos Planialtimétricos, Loteamentos e Regularização Fundiária.
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

            <!-- Conteúdo -->
            <div class="px-8 md:px-14 py-10 md:py-14 text-[#003366]">

                <!-- Sobre -->
                <section class="mb-16">

                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-14 h-14 rounded-2xl bg-[#003366] text-white flex items-center justify-center shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-7 w-7"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M13 16h-1v-4h-1m1-4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z"/>
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-3xl font-black">Sobre o Getec Topografia</h2>
                            <p class="text-sm uppercase tracking-[0.2em] text-[#004A7C]/70 font-bold mt-1">
                                Gestão moderna para topografia
                            </p>
                        </div>
                    </div>

                    <div class="space-y-8 text-lg leading-relaxed text-[#003366]/90 font-medium">
                        @if(!empty($conteudo['sobre']))
                            {!! nl2br(e($conteudo['sobre'])) !!}
                        @else
                            <p>
                                A <span class="font-black">Getec Topografia</span> é uma empresa de engenharia e serviços cartográficos
                                dedicada a garantir a segurança jurídica da sua propriedade através de medições precisas e tecnologia de ponta.
                            </p>

                            <p>
                                Atuamos com Georreferenciamento de Imóveis Rurais (SIGEF/INCRA), Cadastro Ambiental Rural (CAR),
                                Desmembramentos, Unificações, e Retificação de Área para propriedades urbanas e rurais.
                            </p>

                            <p>
                                Contamos com equipamentos GNSS/RTK de altíssima precisão e uma equipe técnica credenciada e altamente qualificada,
                                entregando rapidez e total transparência aos nossos clientes através da nossa plataforma digital exclusiva.
                            </p>
                        @endif
                    </div>
                </section>

                <!-- O que fazemos -->
                <section class="mb-16">

                    <div class="flex items-center gap-4 mb-10">
                        <div class="w-14 h-14 rounded-2xl bg-[#004A7C] text-white flex items-center justify-center shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-7 w-7"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M19 11H5m14-7H5m14 14H5"/>
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-3xl font-black">O que fazemos?</h2>
                            <p class="text-sm uppercase tracking-[0.2em] text-[#004A7C]/70 font-bold mt-1">
                                Soluções para clientes e equipes
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                        <!-- Cliente -->
                        <div class="bg-[#F5F7FA] border border-[#003366]/10 rounded-3xl p-8 shadow-sm hover:shadow-xl transition duration-300">

                            <div class="w-14 h-14 rounded-2xl bg-[#003366] text-white flex items-center justify-center mb-6 shadow-md">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-7 w-7"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M5.121 17.804A11.955 11.955 0 0112 16c2.5 0 4.847.765 6.879 2.072M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>

                            <h3 class="text-2xl font-black mb-5">
                                Propriedades Rurais
                            </h3>

                            <p class="text-lg leading-relaxed font-medium text-[#003366]/85">
                                Regularização completa perante o INCRA, Receita Federal e Cartórios. Georreferenciamento (SIGEF), 
                                CAR, CCIR e laudos técnicos especializados para garantir a conformidade da sua fazenda ou sítio.
                            </p>

                        </div>

                        <!-- Equipe -->
                        <div class="bg-[#F5F7FA] border border-[#003366]/10 rounded-3xl p-8 shadow-sm hover:shadow-xl transition duration-300">

                            <div class="w-14 h-14 rounded-2xl bg-[#005B96] text-white flex items-center justify-center mb-6 shadow-md">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-7 w-7"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M9 17v-2a4 4 0 014-4h4m0 0l-3-3m3 3l-3 3"/>
                                </svg>
                            </div>

                            <h3 class="text-2xl font-black mb-5">
                                Projetos Urbanos
                            </h3>

                            <p class="text-lg leading-relaxed font-medium text-[#003366]/85">
                                Levantamentos planialtimétricos cadastrais para projetos de engenharia e arquitetura, 
                                demarcação de lotes, desdobros, locação de obras e retificação de áreas em áreas urbanas.
                            </p>

                        </div>

                    </div>
                </section>

                <!-- Diferenciais -->
                <section>

                    <div class="flex items-center gap-4 mb-10">
                        <div class="w-14 h-14 rounded-2xl bg-[#003366] text-white flex items-center justify-center shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-7 w-7"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-3xl font-black">
                                Por que escolher o Getec Topografia?
                            </h2>

                            <p class="text-sm uppercase tracking-[0.2em] text-[#004A7C]/70 font-bold mt-1">
                                Eficiência, precisão e inovação
                            </p>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-[#003366] to-[#005B96] rounded-[32px] p-10 md:p-12 text-white shadow-2xl">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">

                            <div>
                                <div class="text-4xl font-black mb-4">
                                    ✓
                                </div>

                                <h3 class="text-2xl font-black mb-3">
                                    Agilidade
                                </h3>

                                <p class="text-white/85 text-lg leading-relaxed">
                                    Equipe técnica experiente que domina os processos de aprovação em cartórios e órgãos públicos.
                                </p>
                            </div>

                            <div>
                                <div class="text-4xl font-black mb-4">
                                    ◎
                                </div>

                                <h3 class="text-2xl font-black mb-3">
                                    Tecnologia de Ponta
                                </h3>

                                <p class="text-white/85 text-lg leading-relaxed">
                                    Utilizamos equipamentos RTK de última geração e softwares avançados para garantir precisão milimétrica.
                                </p>
                            </div>

                            <div>
                                <div class="text-4xl font-black mb-4">
                                    ⚡
                                </div>

                                <h3 class="text-2xl font-black mb-3">
                                    Acompanhamento Online
                                </h3>

                                <p class="text-white/85 text-lg leading-relaxed">
                                    Portal exclusivo onde o cliente visualiza andamentos, mapas e memoriais em tempo real.
                                </p>
                            </div>

                        </div>

                        <div class="border-t border-white/20 pt-8">
                            <p class="text-xl leading-relaxed text-white/90 font-medium">
                                Acreditamos que a topografia vai além de medir terras. Trata-se de garantir a 
                                proteção do patrimônio das famílias e empresas. Por isso, aliamos rigor técnico 
                                à tecnologia para entregar a você o melhor serviço de engenharia cartográfica da região.
                            </p>
                        </div>

                    </div>

                </section>

            </div>
        </div>
    </div>

</body>
</html>