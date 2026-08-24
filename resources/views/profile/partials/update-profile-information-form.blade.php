<section class="space-y-8">
    <header class="bg-white/85 backdrop-blur-md border border-blue-200 shadow-xl rounded-3xl p-6">
        <div class="flex items-center gap-4 mb-3">
            <div class="w-14 h-14 rounded-2xl bg-[#003366] flex items-center justify-center shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A11.955 11.955 0 0112 15c2.5 0 4.847.765 6.879 2.074M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>

            <div>
                <h2 class="text-2xl font-black italic uppercase tracking-tight text-[#003366]">
                    Informações do Perfil
                </h2>

                <p class="mt-1 text-sm text-gray-600 font-medium leading-relaxed max-w-2xl">
                    Atualize seus dados pessoais e endereço de e-mail da sua conta.
                </p>
            </div>
        </div>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post"
          action="{{ route('profile.update') }}"
          class="bg-white/85 backdrop-blur-md border border-white/40 shadow-2xl rounded-[35px] p-8 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label 
                for="name"
                value="Nome Completo"
                class="text-[#003366] font-black uppercase italic mb-2"
            />

            <x-text-input 
                id="name"
                name="name"
                type="text"
                class="mt-1 block w-full bg-gray-100 border border-gray-300 rounded-2xl px-5 py-3 text-[#003366] font-bold focus:ring-4 focus:ring-blue-400"
                :value="old('name', $user->name)"
                required
                autofocus
                autocomplete="name"
                placeholder="Digite seu nome completo"
            />

            <x-input-error 
                class="mt-3 text-red-600 font-bold"
                :messages="$errors->get('name')" 
            />
        </div>

        <div>
            <x-input-label 
                for="email"
                value="E-mail"
                class="text-[#003366] font-black uppercase italic mb-2"
            />

            <x-text-input 
                id="email"
                name="email"
                type="email"
                class="mt-1 block w-full bg-gray-100 border border-gray-300 rounded-2xl px-5 py-3 text-[#003366] font-bold focus:ring-4 focus:ring-blue-400"
                :value="old('email', $user->email)"
                required
                autocomplete="username"
                placeholder="Digite seu e-mail"
            />

            <x-input-error 
                class="mt-3 text-red-600 font-bold"
                :messages="$errors->get('email')" 
            />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-5 bg-yellow-100 border border-yellow-300 rounded-2xl p-5 shadow-md">
                    
                    <p class="text-sm text-yellow-900 font-bold italic leading-relaxed">
                        ⚠️ Seu endereço de e-mail ainda não foi verificado.
                    </p>

                    <button form="send-verification"
                        class="mt-4 bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded-xl font-black uppercase italic shadow-lg border-b-4 border-yellow-700 transition-all active:translate-y-1 active:border-b-0">
                        Reenviar Verificação
                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-4 bg-green-500 text-white px-4 py-2 rounded-xl font-black italic uppercase shadow-md inline-block">
                            ✓ Novo link enviado para seu e-mail
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex flex-col md:flex-row items-center gap-4 pt-4">
            
            <x-primary-button 
                class="bg-[#003366] hover:bg-[#002244] px-10 py-3 rounded-2xl font-black uppercase italic shadow-xl border-b-4 border-[#001933] transition-all active:translate-y-1 active:border-b-0"
            >
                Salvar Alterações
            </x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="bg-green-500 text-white px-5 py-2 rounded-xl font-black uppercase italic shadow-lg"
                >
                    ✓ Perfil Atualizado
                </p>
            @endif
        </div>
    </form>
</section>