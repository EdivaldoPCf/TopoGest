<x-guest-layout>

    <div class="min-h-screen flex items-center justify-center px-4 py-10 relative overflow-hidden">

        <!-- BACKGROUND -->
        <div class="absolute inset-0">
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat"
                 style="background-image: url('{{ asset('images/background-topo.jpg') }}');">
            </div>

            <div class="absolute inset-0 bg-[#001B33]/70 backdrop-blur-sm"></div>
        </div>

        <!-- CONTENT -->
        <div class="relative z-10 w-full max-w-md">

            <!-- LOGO -->
            <div class="flex justify-center mb-8">

                <a href="{{ route('dashboard') }}"
                   class="group flex items-center gap-3 transition duration-300 hover:scale-105">

                    <div class="relative flex items-center justify-center w-20 h-20 rounded-2xl bg-white shadow-2xl transition group-hover:scale-105">
                        <img src="{{ asset('images/logo-icon.png') }}"
                             alt="TopoGest"
                             class="w-full h-full object-contain p-2">
                    </div>

                    <div class="relative flex items-center h-14 px-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                        <img src="{{ asset('images/logo-text.png') }}"
                             alt="TopoGest"
                             class="relative z-10 h-8">
                    </div>

                </a>

            </div>

            <!-- CARD -->
            <div class="bg-white/15 backdrop-blur-2xl border border-white/20 rounded-[35px] shadow-2xl p-8 md:p-10">

                <!-- ICON -->
                <div class="flex justify-center mb-6">

                    <div class="w-20 h-20 rounded-full bg-[#003366] flex items-center justify-center shadow-2xl border border-white/10">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-10 w-10 text-white"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2h-1V9a5 5 0 00-10 0v2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                        </svg>

                    </div>

                </div>

                <!-- TITLE -->
                <div class="text-center mb-8">

                    <h1 class="text-3xl font-black uppercase tracking-tight text-white mb-3">
                        Área Segura
                    </h1>

                    <p class="text-white/80 leading-relaxed text-sm md:text-base">
                        Confirme sua senha para continuar acessando esta área protegida do sistema.
                    </p>

                </div>

                <!-- FORM -->
                <form method="POST"
                      action="{{ route('password.confirm') }}"
                      class="space-y-6">

                    @csrf

                    <!-- PASSWORD -->
                    <div>

                        <label for="password"
                               class="block text-sm font-bold uppercase tracking-wider text-white mb-3">

                            Senha
                        </label>

                        <div class="relative">

                            <input id="password"
                                   type="password"
                                   name="password"
                                   required
                                   autocomplete="current-password"
                                   placeholder="Digite sua senha"
                                   class="w-full bg-white/90 text-[#003366] placeholder-[#003366]/50 rounded-2xl px-5 py-4 pr-14 outline-none border border-white/20 focus:ring-4 focus:ring-blue-400/30 transition-all shadow-inner font-semibold">

                            <div class="absolute right-5 top-4 text-[#003366]">

                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-6 w-6"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M12 11c0 .552-.448 1-1 1s-1-.448-1-1 .448-1 1-1 1 .448 1 1zm0 0V9m0 6h.01M5.938 4h12.124C19.133 4 20 4.867 20 5.938v12.124A1.938 1.938 0 0118.062 20H5.938A1.938 1.938 0 014 18.062V5.938C4 4.867 4.867 4 5.938 4z"/>
                                </svg>

                            </div>

                        </div>

                        @if($errors->get('password'))

                            <div class="mt-3 bg-red-500/20 border border-red-400/30 text-red-100 px-4 py-3 rounded-xl text-sm">

                                {{ $errors->first('password') }}

                            </div>

                        @endif

                    </div>

                    <!-- BUTTON -->
                    <div class="pt-2">

                        <button type="submit"
                                class="w-full bg-[#003366] hover:bg-[#002244] text-white py-4 rounded-2xl font-black uppercase tracking-widest shadow-2xl transition-all hover:scale-[1.02] active:scale-[0.98] border border-white/10">

                            Confirmar Senha

                        </button>

                    </div>

                </form>

            </div>

            <!-- FOOTER -->
            <div class="mt-6 text-center text-white/60 text-xs uppercase tracking-[0.25em]">
                TopoGest • Sistema Seguro
            </div>

        </div>

    </div>

</x-guest-layout>