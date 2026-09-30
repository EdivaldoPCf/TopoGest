<!DOCTYPE html>
<html lang="pt-br" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TopoGest - Gestão Topográfica Digital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">
</head>
<body class="antialiased bg-[#001B2E] text-slate-300 font-sans selection:bg-[#004A7C] selection:text-white" x-data="{ mobileMenuOpen: false, scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 20)">

    <!-- Navbar -->
    <nav :class="{'bg-[#001B2E]/95 backdrop-blur-md shadow-lg border-b border-white/5': scrolled, 'bg-transparent border-b border-transparent': !scrolled}" class="fixed w-full z-50 transition-all duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <div class="flex-shrink-0 flex items-center">
                    <a href="#inicio" aria-label="Página Inicial" class="flex items-center gap-2.5 group">
                        <img class="h-8 sm:h-10 w-auto object-contain transition-transform duration-300 group-hover:scale-105" src="{{ asset('images/logo-icon.png') }}?v={{ time() }}" alt="Símbolo TopoGest">
                        <img class="h-6 sm:h-7 w-auto object-contain transition-opacity duration-300 group-hover:opacity-80" src="{{ asset('images/logo-text.png') }}?v={{ time() }}" alt="TopoGest">
                    </a>
                </div>
                
                <!-- Links Desktop -->
                <div class="hidden md:block">
                    <div class="ml-10 flex items-center space-x-8">
                        <a href="#inicio" class="text-white hover:text-[#34d399] transition-colors duration-200 font-medium text-sm">Início</a>
                        <a href="#recursos" class="text-slate-300 hover:text-white transition-colors duration-200 font-medium text-sm">Recursos</a>
                        <a href="#sobre" class="text-slate-300 hover:text-white transition-colors duration-200 font-medium text-sm">Sobre</a>
                        <a href="#faq" class="text-slate-300 hover:text-white transition-colors duration-200 font-medium text-sm">FAQ</a>
                        <a href="#contato" class="text-slate-300 hover:text-white transition-colors duration-200 font-medium text-sm">Contato</a>
                    </div>
                </div>
                
                <!-- CTA Desktop -->
                <div class="hidden md:flex items-center space-x-4">
                    <a href="{{ route('login') }}" class="text-white/90 hover:text-white font-medium px-5 py-2.5 border border-white/20 rounded-lg hover:bg-white/10 hover:border-white/30 transition-all duration-200 text-sm">Entrar</a>
                    <a href="{{ route('register') }}" class="bg-[#004A7C] hover:bg-[#005B99] text-white font-medium px-6 py-2.5 rounded-lg shadow-lg hover:shadow-[#004A7C]/30 hover:-translate-y-0.5 transition-all duration-200 text-sm">Criar conta</a>
                </div>
                
                <!-- Mobile menu button -->
                <div class="-mr-2 flex md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" aria-label="Menu principal" class="inline-flex items-center justify-center p-2 rounded-md text-slate-300 hover:text-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[#34d399] transition-colors duration-200">
                        <svg x-show="!mobileMenuOpen" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-show="mobileMenuOpen" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu panel -->
        <div x-show="mobileMenuOpen" x-transition class="md:hidden bg-[#001B2E] border-b border-white/10 shadow-xl" style="display: none;">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a @click="mobileMenuOpen = false" href="#inicio" class="text-white block px-3 py-2 rounded-md text-base font-medium hover:bg-white/5 transition-colors duration-200">Início</a>
                <a @click="mobileMenuOpen = false" href="#recursos" class="text-slate-300 hover:text-white block px-3 py-2 rounded-md text-base font-medium hover:bg-white/5 transition-colors duration-200">Recursos</a>
                <a @click="mobileMenuOpen = false" href="#sobre" class="text-slate-300 hover:text-white block px-3 py-2 rounded-md text-base font-medium hover:bg-white/5 transition-colors duration-200">Sobre</a>
                <a @click="mobileMenuOpen = false" href="#faq" class="text-slate-300 hover:text-white block px-3 py-2 rounded-md text-base font-medium hover:bg-white/5 transition-colors duration-200">FAQ</a>
                <a @click="mobileMenuOpen = false" href="#contato" class="text-slate-300 hover:text-white block px-3 py-2 rounded-md text-base font-medium hover:bg-white/5 transition-colors duration-200">Contato</a>
            </div>
            <div class="pt-4 pb-4 border-t border-white/10">
                <div class="px-5 flex flex-col space-y-3">
                    <a href="{{ route('login') }}" class="w-full text-center text-white/90 hover:text-white font-medium px-4 py-2 border border-white/20 rounded-lg hover:bg-white/10 transition-all duration-200">Entrar</a>
                    <a href="{{ route('register') }}" class="w-full text-center bg-[#004A7C] hover:bg-[#005B99] text-white font-medium px-5 py-2 rounded-lg shadow-lg transition-all duration-200">Criar conta</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header id="inicio" class="relative min-h-[100vh] flex flex-col items-center justify-center overflow-hidden">
        
        <!-- Background Layer -->
        <div class="absolute inset-0 z-0">
            <!-- Imagem base -->
            <img src="{{ asset('images/background-topo.jpg') }}" alt="" class="w-full h-full object-cover object-center opacity-25 pointer-events-none">
            
            <!-- Overlay principal (escurece a imagem toda para legibilidade) -->
            <div class="absolute inset-0 bg-[#001B2E]/75"></div>
            
            <!-- Gradiente inferior para transição suave para a próxima seção -->
            <div class="absolute inset-x-0 bottom-0 h-48 bg-gradient-to-t from-[#001B2E] to-transparent"></div>
        </div>

        <!-- Conteúdo Hero -->
        <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center flex flex-col items-center justify-center flex-grow pt-24 pb-12">
            
            <!-- Símbolo -->
            <img src="{{ asset('images/logo-icon.png') }}?v={{ time() }}" alt="Símbolo TopoGest" class="h-20 md:h-[90px] w-auto mb-10 opacity-95 hover:scale-105 transition-transform duration-500">
            
            <!-- Título -->
            <h1 class="text-4xl tracking-tight font-extrabold text-white sm:text-5xl md:text-[4rem] max-w-4xl leading-[1.1] md:leading-[1.15]">
                Gestão topográfica, <br class="hidden sm:block">
                <span class="text-[#34d399] inline-block mt-2 md:mt-0">simples e organizada.</span>
            </h1>
            
            <!-- Subtítulo -->
            <p class="mt-8 max-w-[42rem] mx-auto text-lg sm:text-xl text-slate-300 leading-relaxed font-light">
                Centralize serviços, documentos, bases, marcos e clientes em um único lugar. Precisão e agilidade no seu fluxo de trabalho diário.
            </p>
            
            <!-- Botões -->
            <div class="mt-12 flex flex-col sm:flex-row justify-center gap-5 w-full px-4 sm:px-0">
                <a href="{{ route('register') }}" class="w-full sm:w-auto flex items-center justify-center px-10 py-4 border border-transparent text-base font-semibold rounded-xl text-white bg-[#004A7C] hover:bg-[#005B99] hover:shadow-xl hover:shadow-[#004A7C]/25 hover:-translate-y-1 focus:ring-2 focus:ring-offset-2 focus:ring-offset-[#001B2E] focus:ring-[#004A7C] transition-all duration-200">
                    Começar agora
                </a>
                <a href="#recursos" class="w-full sm:w-auto flex items-center justify-center px-10 py-4 border border-white/20 text-base font-semibold rounded-xl text-white/95 bg-white/5 hover:bg-white/10 hover:text-white hover:border-white/30 hover:-translate-y-1 focus:ring-2 focus:ring-offset-2 focus:ring-offset-[#001B2E] focus:ring-white/20 transition-all duration-200 backdrop-blur-sm">
                    Conhecer o TopoGest
                </a>
            </div>

        </div>

        <!-- Indicador de Scroll -->
        <div class="relative z-10 pb-8 flex flex-col items-center justify-end animate-bounce mt-auto opacity-70 hover:opacity-100 transition-opacity">
            <a href="#recursos" class="text-sm text-slate-400 hover:text-white flex flex-col items-center transition-colors duration-200" aria-label="Rolar para recursos">
                <span class="mb-2 font-medium tracking-wide">Conheça o sistema</span>
                <svg class="w-5 h-5 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </a>
        </div>
        
    </header>

    <!-- Recursos Section -->
    <section id="recursos" class="py-24 bg-[#001B2E]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-sm text-[#34d399] font-bold tracking-widest uppercase">Recursos do Sistema</h2>
                <p class="mt-3 text-3xl leading-8 font-extrabold tracking-tight text-white sm:text-4xl">
                    Controle total dos seus projetos
                </p>
                <p class="mt-4 max-w-2xl text-lg sm:text-xl text-slate-400 mx-auto font-light leading-relaxed">
                    Ferramentas projetadas especificamente para centralizar as rotinas de profissionais e empresas de topografia.
                </p>
            </div>

            <div class="mt-20">
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    
                    <!-- Card 1 -->
                    <div class="pt-6 relative group h-full">
                        <div class="absolute inset-0 bg-gradient-to-b from-[#004A7C]/10 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-10 blur-xl"></div>
                        <div class="bg-white/5 border border-white/10 rounded-2xl px-6 pb-8 h-full group-hover:border-white/20 group-hover:bg-white/[0.07] transition-all duration-300">
                            <div class="-mt-6">
                                <div>
                                    <span class="inline-flex items-center justify-center p-3.5 bg-[#004A7C] rounded-xl shadow-lg border border-white/10">
                                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </span>
                                </div>
                                <h3 class="mt-8 text-xl font-semibold text-white tracking-tight group-hover:text-[#34d399] transition-colors duration-200">Gestão de Serviços</h3>
                                <p class="mt-4 text-base text-slate-400 leading-relaxed font-light">
                                    Organize serviços pendentes e concluídos. Acompanhe o status e a evolução de cada projeto da sua equipe com facilidade.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div class="pt-6 relative group h-full">
                        <div class="absolute inset-0 bg-gradient-to-b from-[#004A7C]/10 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-10 blur-xl"></div>
                        <div class="bg-white/5 border border-white/10 rounded-2xl px-6 pb-8 h-full group-hover:border-white/20 group-hover:bg-white/[0.07] transition-all duration-300">
                            <div class="-mt-6">
                                <div>
                                    <span class="inline-flex items-center justify-center p-3.5 bg-[#004A7C] rounded-xl shadow-lg border border-white/10">
                                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </span>
                                </div>
                                <h3 class="mt-8 text-xl font-semibold text-white tracking-tight group-hover:text-[#34d399] transition-colors duration-200">Clientes</h3>
                                <p class="mt-4 text-base text-slate-400 leading-relaxed font-light">
                                    Centralize informações e acompanhe o histórico de serviços, contatos e documentos de cada cliente rapidamente.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3 -->
                    <div class="pt-6 relative group h-full">
                        <div class="absolute inset-0 bg-gradient-to-b from-[#004A7C]/10 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-10 blur-xl"></div>
                        <div class="bg-white/5 border border-white/10 rounded-2xl px-6 pb-8 h-full group-hover:border-white/20 group-hover:bg-white/[0.07] transition-all duration-300">
                            <div class="-mt-6">
                                <div>
                                    <span class="inline-flex items-center justify-center p-3.5 bg-[#004A7C] rounded-xl shadow-lg border border-white/10">
                                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                                        </svg>
                                    </span>
                                </div>
                                <h3 class="mt-8 text-xl font-semibold text-white tracking-tight group-hover:text-[#34d399] transition-colors duration-200">Documentos</h3>
                                <p class="mt-4 text-base text-slate-400 leading-relaxed font-light">
                                    Organize arquivos, plantas, relatórios e pastas dos serviços de maneira segura e acessível na nuvem.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4 -->
                    <div class="pt-6 relative group h-full">
                        <div class="absolute inset-0 bg-gradient-to-b from-[#004A7C]/10 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-10 blur-xl"></div>
                        <div class="bg-white/5 border border-white/10 rounded-2xl px-6 pb-8 h-full group-hover:border-white/20 group-hover:bg-white/[0.07] transition-all duration-300">
                            <div class="-mt-6">
                                <div>
                                    <span class="inline-flex items-center justify-center p-3.5 bg-[#004A7C] rounded-xl shadow-lg border border-white/10">
                                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </span>
                                </div>
                                <h3 class="mt-8 text-xl font-semibold text-white tracking-tight group-hover:text-[#34d399] transition-colors duration-200">Bases e Marcos</h3>
                                <p class="mt-4 text-base text-slate-400 leading-relaxed font-light">
                                    Consulte bases topográficas e marcos de forma estruturada, com coordenadas e histórico completo.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Card 5 -->
                    <div class="pt-6 relative group h-full">
                        <div class="absolute inset-0 bg-gradient-to-b from-[#004A7C]/10 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-10 blur-xl"></div>
                        <div class="bg-white/5 border border-white/10 rounded-2xl px-6 pb-8 h-full group-hover:border-white/20 group-hover:bg-white/[0.07] transition-all duration-300">
                            <div class="-mt-6">
                                <div>
                                    <span class="inline-flex items-center justify-center p-3.5 bg-[#004A7C] rounded-xl shadow-lg border border-white/10">
                                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                        </svg>
                                    </span>
                                </div>
                                <h3 class="mt-8 text-xl font-semibold text-white tracking-tight group-hover:text-[#34d399] transition-colors duration-200">Mapa Interativo</h3>
                                <p class="mt-4 text-base text-slate-400 leading-relaxed font-light">
                                    Visualize informações geográficas utilizando os potentes recursos integrados de mapa do sistema.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Card 6 -->
                    <div class="pt-6 relative group h-full">
                        <div class="absolute inset-0 bg-gradient-to-b from-[#004A7C]/10 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-10 blur-xl"></div>
                        <div class="bg-white/5 border border-white/10 rounded-2xl px-6 pb-8 h-full group-hover:border-white/20 group-hover:bg-white/[0.07] transition-all duration-300">
                            <div class="-mt-6">
                                <div>
                                    <span class="inline-flex items-center justify-center p-3.5 bg-[#004A7C] rounded-xl shadow-lg border border-white/10">
                                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                    </span>
                                </div>
                                <h3 class="mt-8 text-xl font-semibold text-white tracking-tight group-hover:text-[#34d399] transition-colors duration-200">Notificações</h3>
                                <p class="mt-4 text-base text-slate-400 leading-relaxed font-light">
                                    Acompanhe atualizações, mudanças de status e atividades importantes para não perder nenhum detalhe.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- Sobre Section -->
    <section id="sobre" class="py-24 relative overflow-hidden bg-[#001423]">
        <!-- Decorative Elements -->
        <div class="hidden lg:block absolute top-0 right-0 -mr-20 -mt-20 opacity-[0.03] pointer-events-none" aria-hidden="true">
            <svg class="w-[600px] h-[600px] text-white" fill="currentColor" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="45" stroke="currentColor" stroke-width="1" fill="none" />
                <circle cx="50" cy="50" r="35" stroke="currentColor" stroke-width="1" fill="none" stroke-dasharray="4,4" />
                <circle cx="50" cy="50" r="25" stroke="currentColor" stroke-width="1" fill="none" />
                <circle cx="50" cy="50" r="15" stroke="currentColor" stroke-width="1" fill="none" stroke-dasharray="2,2" />
                <circle cx="50" cy="50" r="2" fill="currentColor" />
                <line x1="50" y1="0" x2="50" y2="100" stroke="currentColor" stroke-width="0.5" />
                <line x1="0" y1="50" x2="100" y2="50" stroke="currentColor" stroke-width="0.5" />
            </svg>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="lg:grid lg:grid-cols-2 lg:gap-16 items-center">
                <div>
                    <h2 class="text-3xl font-extrabold text-white tracking-tight sm:text-4xl">
                        A plataforma do topógrafo
                    </h2>
                    <div class="mt-6 text-lg text-slate-300 space-y-6 leading-relaxed font-light">
                        <p>
                            O TopoGest foi desenvolvido para centralizar a gestão de serviços topográficos, reduzindo a dispersão de documentos, informações de clientes e dados de campo.
                        </p>
                        <p>
                            Nossa missão é entregar uma ferramenta focada e objetiva, que permite à sua equipe eliminar a desorganização de pastas locais e focar no que realmente importa: a precisão e a qualidade das entregas.
                        </p>
                    </div>
                </div>
                <div class="mt-12 lg:mt-0 flex justify-center">
                    <div class="relative">
                        <div class="absolute -inset-4 bg-gradient-to-br from-[#004A7C] to-[#34d399] rounded-full blur-2xl opacity-20"></div>
                        <div class="relative z-10 flex flex-col items-center justify-center p-8">
                            <img src="{{ asset('images/logo-completa.png') }}?v={{ time() }}" alt="Marca TopoGest" class="w-48 md:w-64 h-auto object-contain drop-shadow-2xl opacity-90">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="bg-[#001B2E] py-24 border-t border-white/5">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
                    Perguntas Frequentes
                </h2>
                <p class="mt-4 text-xl text-slate-400 font-light">
                    Tudo o que você precisa saber sobre o TopoGest.
                </p>
            </div>
            
            <div class="mt-14 space-y-4">
                
                <!-- Item 1 -->
                <div x-data="{ open: false }" class="border border-white/10 rounded-xl overflow-hidden bg-white/5 hover:bg-white/[0.07] transition-colors duration-200">
                    <button @click="open = !open" :aria-expanded="open" class="flex justify-between items-center w-full px-6 py-5 text-left focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[#34d399]/50">
                        <span class="font-medium text-white text-lg">O que é o TopoGest?</span>
                        <svg class="h-5 w-5 text-[#34d399] transform transition-transform duration-200 flex-shrink-0" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="px-6 pb-6 text-slate-300 leading-relaxed font-light" style="display: none;">
                        O TopoGest é uma plataforma SaaS (Software as a Service) voltada exclusivamente para a gestão de serviços, documentos, clientes e informações geoespaciais no setor de topografia e agrimensura.
                    </div>
                </div>

                <!-- Item 2 -->
                <div x-data="{ open: false }" class="border border-white/10 rounded-xl overflow-hidden bg-white/5 hover:bg-white/[0.07] transition-colors duration-200">
                    <button @click="open = !open" :aria-expanded="open" class="flex justify-between items-center w-full px-6 py-5 text-left focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[#34d399]/50">
                        <span class="font-medium text-white text-lg">Quem pode utilizar o TopoGest?</span>
                        <svg class="h-5 w-5 text-[#34d399] transform transition-transform duration-200 flex-shrink-0" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="px-6 pb-6 text-slate-300 leading-relaxed font-light" style="display: none;">
                        Engenheiros, topógrafos, empresas de agrimensura e qualquer profissional ou equipe que lide com prestação de serviços topográficos e queira centralizar seus dados e organizar o fluxo de trabalho.
                    </div>
                </div>

                <!-- Item 3 -->
                <div x-data="{ open: false }" class="border border-white/10 rounded-xl overflow-hidden bg-white/5 hover:bg-white/[0.07] transition-colors duration-200">
                    <button @click="open = !open" :aria-expanded="open" class="flex justify-between items-center w-full px-6 py-5 text-left focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[#34d399]/50">
                        <span class="font-medium text-white text-lg">Posso organizar documentos dos meus serviços?</span>
                        <svg class="h-5 w-5 text-[#34d399] transform transition-transform duration-200 flex-shrink-0" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="px-6 pb-6 text-slate-300 leading-relaxed font-light" style="display: none;">
                        Sim! Você pode criar pastas, enviar arquivos, plantas (como DXF) e documentos relacionados a cada serviço, mantendo um histórico seguro, acessível na nuvem e pronto para análise.
                    </div>
                </div>

                <!-- Item 4 -->
                <div x-data="{ open: false }" class="border border-white/10 rounded-xl overflow-hidden bg-white/5 hover:bg-white/[0.07] transition-colors duration-200">
                    <button @click="open = !open" :aria-expanded="open" class="flex justify-between items-center w-full px-6 py-5 text-left focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[#34d399]/50">
                        <span class="font-medium text-white text-lg">O sistema possui recursos de mapa?</span>
                        <svg class="h-5 w-5 text-[#34d399] transform transition-transform duration-200 flex-shrink-0" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="px-6 pb-6 text-slate-300 leading-relaxed font-light" style="display: none;">
                        Com certeza. O TopoGest conta com visualização em mapa de bases topográficas, marcos e outras informações geográficas para facilitar sua tomada de decisão e gestão territorial.
                    </div>
                </div>

                <!-- Item 5 -->
                <div x-data="{ open: false }" class="border border-white/10 rounded-xl overflow-hidden bg-white/5 hover:bg-white/[0.07] transition-colors duration-200">
                    <button @click="open = !open" :aria-expanded="open" class="flex justify-between items-center w-full px-6 py-5 text-left focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[#34d399]/50">
                        <span class="font-medium text-white text-lg">Como faço para criar minha conta?</span>
                        <svg class="h-5 w-5 text-[#34d399] transform transition-transform duration-200 flex-shrink-0" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="px-6 pb-6 text-slate-300 leading-relaxed font-light" style="display: none;">
                        O processo é simples: clique em "Criar conta" na navegação superior, informe os dados solicitados e você já poderá começar a organizar seus projetos no TopoGest.
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Contato Section -->
    <section id="contato" class="bg-[#001423] py-24 border-t border-white/5">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
                Precisa de ajuda?
            </h2>
            <p class="mt-4 text-xl text-slate-400 font-light">
                Entre em contato conosco para tirar dúvidas sobre o TopoGest.
            </p>
            <div class="mt-10">
                <a href="https://w.app/eudvu7" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center px-8 py-4 border border-transparent text-lg font-semibold rounded-xl text-white bg-[#059669] hover:bg-[#047857] shadow-lg hover:shadow-xl hover:shadow-[#059669]/20 hover:-translate-y-1 focus:ring-2 focus:ring-offset-2 focus:ring-offset-[#001423] focus:ring-[#059669] transition-all duration-300">
                    <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12.031 21.365a9.882 9.882 0 0 1-5.06-1.38L2 21l1.04-4.815a9.907 9.907 0 1 1 8.99 5.18zM17.25 15.68c-.28-.14-1.63-.8-1.88-.89-.25-.09-.43-.14-.61.14-.18.28-.71.89-.87 1.07-.16.18-.32.21-.6.07a7.512 7.512 0 0 1-2.21-1.36 8.286 8.286 0 0 1-1.53-1.9c-.16-.28-.02-.43.12-.57.13-.13.28-.33.42-.5.14-.17.19-.28.28-.47.09-.19.05-.35-.02-.5-.07-.14-.61-1.47-.84-2.02-.22-.54-.45-.47-.61-.48-.16-.01-.34-.01-.52-.01-.18 0-.48.07-.73.34-.25.28-.96.94-.96 2.29s.99 2.66 1.13 2.85c.14.19 1.94 2.96 4.7 4.13.66.28 1.17.45 1.57.57.66.21 1.26.18 1.74.11.53-.08 1.63-.67 1.86-1.31.23-.64.23-1.19.16-1.31-.07-.12-.25-.19-.53-.33z"/>
                    </svg>
                    Falar via WhatsApp
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-[#001B2E] border-t border-white/10 pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <!-- Marca -->
                <div class="mb-8 md:mb-0 flex flex-col items-center md:items-start">
                    <div class="flex items-center gap-2 mb-3">
                        <img src="{{ asset('images/logo-icon.png') }}?v={{ time() }}" alt="Símbolo TopoGest" class="h-8 w-auto opacity-90">
                        <img src="{{ asset('images/logo-text.png') }}?v={{ time() }}" alt="TopoGest" class="h-6 w-auto opacity-90">
                    </div>
                    <p class="text-slate-400 text-sm">Gestão & Topografia Digital</p>
                </div>
                
                <!-- Links Navegação -->
                <div class="flex flex-wrap justify-center gap-x-8 gap-y-4 mb-8 md:mb-0 text-sm">
                    <a href="#inicio" class="text-slate-400 hover:text-[#34d399] transition-colors duration-200">Início</a>
                    <a href="#recursos" class="text-slate-400 hover:text-[#34d399] transition-colors duration-200">Recursos</a>
                    <a href="#sobre" class="text-slate-400 hover:text-[#34d399] transition-colors duration-200">Sobre</a>
                    <a href="#faq" class="text-slate-400 hover:text-[#34d399] transition-colors duration-200">FAQ</a>
                    <a href="#contato" class="text-slate-400 hover:text-[#34d399] transition-colors duration-200">Contato</a>
                </div>

                <!-- Links Autenticação -->
                <div class="flex space-x-6 text-sm font-medium">
                    <a href="{{ route('login') }}" class="text-slate-400 hover:text-white transition-colors duration-200">Entrar</a>
                    <a href="{{ route('register') }}" class="text-[#34d399] hover:text-[#10b981] transition-colors duration-200">Criar conta</a>
                </div>
            </div>
            
            <!-- Copyright -->
            <div class="mt-12 border-t border-white/5 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-slate-500 text-sm text-center md:text-left">
                    &copy; {{ date('Y') }} TopoGest. Todos os direitos reservados.
                </p>
            </div>
        </div>
    </footer>

</body>
</html>