<section class="space-y-8">
    <header class="bg-white/85 backdrop-blur-md border border-blue-200 shadow-xl rounded-3xl p-6">
        <div class="flex items-center gap-4 mb-3">
            <div class="w-14 h-14 rounded-2xl bg-[#003366] flex items-center justify-center shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0-1.657 1.343-3 3-3s3 1.343 3 3v2a3 3 0 01-3 3h-6a3 3 0 01-3-3v-2c0-1.657 1.343-3 3-3s3 1.343 3 3m0 0V7a3 3 0 10-6 0v4" />
                </svg>
            </div>

            <div>
                <h2 class="text-2xl font-black italic uppercase tracking-tight text-[#003366]">
                    Atualizar Senha
                </h2>

                <p class="mt-1 text-sm text-gray-600 font-medium leading-relaxed max-w-2xl">
                    Utilize uma senha forte e segura para proteger sua conta e manter seus dados protegidos.
                </p>
            </div>
        </div>
    </header>

    <form method="post" action="{{ route('password.update') }}"
          class="bg-white/85 backdrop-blur-md border border-white/40 shadow-2xl rounded-[35px] p-8 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label 
                for="update_password_current_password" 
                value="Senha Atual"
                class="text-[#003366] font-black uppercase italic mb-2"
            />

            <x-text-input 
                id="update_password_current_password"
                name="current_password"
                type="password"
                class="mt-1 block w-full bg-gray-100 border border-gray-300 rounded-2xl px-5 py-3 text-[#003366] font-bold focus:ring-4 focus:ring-blue-400"
                autocomplete="current-password"
                placeholder="Digite sua senha atual"
            />

            <x-input-error 
                :messages="$errors->updatePassword->get('current_password')" 
                class="mt-3 text-red-600 font-bold"
            />
        </div>

        <div>
            <x-input-label 
                for="update_password_password" 
                value="Nova Senha"
                class="text-[#003366] font-black uppercase italic mb-2"
            />

            <x-text-input 
                id="update_password_password"
                name="password"
                type="password"
                class="mt-1 block w-full bg-gray-100 border border-gray-300 rounded-2xl px-5 py-3 text-[#003366] font-bold focus:ring-4 focus:ring-blue-400"
                autocomplete="new-password"
                placeholder="Digite a nova senha"
            />

            <x-input-error 
                :messages="$errors->updatePassword->get('password')" 
                class="mt-3 text-red-600 font-bold"
            />
        </div>

        <div>
            <x-input-label 
                for="update_password_password_confirmation" 
                value="Confirmar Nova Senha"
                class="text-[#003366] font-black uppercase italic mb-2"
            />

            <x-text-input 
                id="update_password_password_confirmation"
                name="password_confirmation"
                type="password"
                class="mt-1 block w-full bg-gray-100 border border-gray-300 rounded-2xl px-5 py-3 text-[#003366] font-bold focus:ring-4 focus:ring-blue-400"
                autocomplete="new-password"
                placeholder="Repita a nova senha"
            />

            <x-input-error 
                :messages="$errors->updatePassword->get('password_confirmation')" 
                class="mt-3 text-red-600 font-bold"
            />
        </div>

        <div class="flex flex-col md:flex-row items-center gap-4 pt-4">
            <x-primary-button 
                class="bg-[#003366] hover:bg-[#002244] px-10 py-3 rounded-2xl font-black uppercase italic shadow-xl border-b-4 border-[#001933] transition-all active:translate-y-1 active:border-b-0"
            >
                Salvar Alterações
            </x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="bg-green-500 text-white px-5 py-2 rounded-xl font-black uppercase italic shadow-lg"
                >
                    ✓ Senha Atualizada
                </p>
            @endif
        </div>
    </form>
</section>