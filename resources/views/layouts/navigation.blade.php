<nav x-data="{ open: false }" class="bg-white/90 backdrop-blur-xl border-b border-white/20 shadow-lg">
    
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20 items-center">

            <!-- Logo -->
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-4 hover:opacity-90 transition">

                    <div class="relative flex items-center justify-center w-16 h-16 rounded-2xl bg-white shadow-2xl transition group-hover:scale-105">
                        <img src="{{ asset('images/logo-icon.png') . '?v=' . @filemtime(public_path('images/logo-icon.png')) }}" 
                             alt="Logo" 
                             class="w-full h-full object-contain p-2">
                    </div>

                    <div class="relative flex items-center h-12 px-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
                        <img src="{{ asset('images/logo-text.png') . '?v=' . @filemtime(public_path('images/logo-text.png')) }}" 
                             alt="TopoGest" 
                             class="relative z-10 h-6 w-auto">
                    </div>

                </a>

                <!-- Navigation Links -->
                <div class="hidden sm:flex items-center gap-4 ml-8">

                    <a href="{{ route('dashboard') }}"
                       class="bg-[#003366] text-white px-5 py-2 rounded-xl font-bold shadow-md hover:bg-blue-900 transition text-sm uppercase tracking-wide">
                        Dashboard
                    </a>

                    <a href="{{ route('meus.servicos') }}"
                       class="bg-white text-[#003366] border border-[#003366]/20 px-5 py-2 rounded-xl font-bold shadow-sm hover:bg-gray-100 transition text-sm uppercase tracking-wide">
                        Meus Imóveis
                    </a>

                </div>
            </div>

            <!-- Desktop User Menu -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">

                <x-dropdown align="right" width="56">

                    <x-slot name="trigger">
                        <button class="flex items-center gap-3 bg-[#003366] text-white px-4 py-2 rounded-2xl shadow-xl border border-white/10 hover:bg-[#002244] transition">

                            <div class="w-11 h-11 rounded-full overflow-hidden border-2 border-white shadow-inner bg-[#004A7C] flex items-center justify-center">

                                @if(Auth::user()->photo)
                                    <img src="{{ asset('storage/' . Auth::user()->photo) }}"
                                         alt="Perfil"
                                         class="w-full h-full object-cover">
                                @else
                                    <span class="font-black text-sm uppercase">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                                    </span>
                                @endif

                            </div>

                            <div class="flex flex-col text-left leading-tight">
                                <span class="text-[10px] uppercase tracking-widest opacity-70 font-bold">
                                    Bem-vindo
                                </span>

                                <span class="font-black text-sm uppercase">
                                    {{ explode(' ', Auth::user()->name)[0] }}
                                </span>
                            </div>

                            <svg class="fill-current h-4 w-4 opacity-80"
                                 xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                      clip-rule="evenodd" />
                            </svg>

                        </button>
                    </x-slot>

                    <x-slot name="content">

                        <div class="px-4 py-3 border-b border-gray-200">
                            <p class="text-xs uppercase tracking-widest text-gray-400 font-bold">
                                Conta
                            </p>

                            <p class="font-bold text-[#003366] mt-1">
                                {{ Auth::user()->name }}
                            </p>

                            <p class="text-sm text-gray-500 truncate">
                                {{ Auth::user()->email }}
                            </p>
                        </div>

                        <x-dropdown-link :href="route('profile.edit')" class="font-semibold">
                            Meu Perfil
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('dashboard')" class="font-semibold">
                            Dashboard
                        </x-dropdown-link>

                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout', [], false) }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                class="text-red-600 font-bold"
                                onclick="event.preventDefault();
                                         this.closest('form').submit();">

                                Sair do Sistema

                            </x-dropdown-link>
                        </form>

                    </x-slot>

                </x-dropdown>

            </div>

            <!-- Mobile Hamburger -->
            <div class="flex items-center sm:hidden">

                <button @click="open = ! open"
                        class="bg-[#003366] text-white p-3 rounded-xl shadow-lg hover:bg-blue-900 transition">

                    <svg class="h-6 w-6"
                         stroke="currentColor"
                         fill="none"
                         viewBox="0 0 24 24">

                        <path :class="{'hidden': open, 'inline-flex': ! open }"
                              class="inline-flex"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16" />

                        <path :class="{'hidden': ! open, 'inline-flex': open }"
                              class="hidden"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />

                    </svg>

                </button>

            </div>

        </div>
    </div>

    <!-- Responsive Navigation -->
    <div :class="{'block': open, 'hidden': ! open}"
         class="hidden sm:hidden bg-white/95 backdrop-blur-xl border-t border-gray-200">

        <div class="px-4 py-4 space-y-3">

            <x-responsive-nav-link :href="route('dashboard')"
                                   :active="request()->routeIs('dashboard')"
                                   class="font-bold text-[#003366]">
                Dashboard
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('meus.servicos')"
                                   class="font-bold text-[#003366]">
                Meus Imóveis
            </x-responsive-nav-link>

        </div>

        <!-- Mobile User Info -->
        <div class="border-t border-gray-200 px-4 py-4">

            <div class="flex items-center gap-3 mb-4">

                <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-[#003366] bg-[#004A7C] flex items-center justify-center text-white font-black">

                    @if(Auth::user()->photo)
                        <img src="{{ asset('storage/' . Auth::user()->photo) }}"
                             class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    @endif

                </div>

                <div>
                    <div class="font-black text-[#003366]">
                        {{ Auth::user()->name }}
                    </div>

                    <div class="text-sm text-gray-500">
                        {{ Auth::user()->email }}
                    </div>
                </div>

            </div>

            <div class="space-y-2">

                <x-responsive-nav-link :href="route('profile.edit')"
                                       class="font-semibold">
                    Meu Perfil
                </x-responsive-nav-link>

                <!-- Logout -->
                <form method="POST" action="{{ route('logout', [], false) }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                        class="text-red-600 font-bold"
                        onclick="event.preventDefault();
                                 this.closest('form').submit();">

                        Sair do Sistema

                    </x-responsive-nav-link>
                </form>

            </div>

        </div>

    </div>

</nav>