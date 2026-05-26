<section class="space-y-8">
    <header class="bg-white/85 backdrop-blur-md border border-red-200 shadow-xl rounded-3xl p-6">
        <div class="flex items-center gap-4 mb-3">
            <div class="w-14 h-14 rounded-2xl bg-red-600 flex items-center justify-center shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7L5 7M10 11v6m4-6v6M9 7V4h6v3m-7 0h8l-1 12a2 2 0 01-2 2H11a2 2 0 01-2-2L8 7z" />
                </svg>
            </div>

            <div>
                <h2 class="text-2xl font-black italic uppercase tracking-tight text-red-700">
                    Excluir Conta
                </h2>

                <p class="mt-1 text-sm text-gray-600 font-medium leading-relaxed max-w-2xl">
                    Ao excluir sua conta, todos os seus dados, documentos e informações serão removidos permanentemente do sistema. 
                    Antes de continuar, recomendamos fazer backup dos arquivos importantes.
                </p>
            </div>
        </div>
    </header>

    <div class="flex justify-start">
        <x-danger-button
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="bg-red-600 hover:bg-red-700 active:scale-95 transition-all px-8 py-3 rounded-2xl text-sm font-black uppercase tracking-widest shadow-xl border-b-4 border-red-900"
        >
            Excluir Conta
        </x-danger-button>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}"
              class="bg-[#004A7C] text-white rounded-[35px] p-8 shadow-2xl border border-white/10">
            @csrf
            @method('delete')

            <div class="flex flex-col items-center text-center">
                
                <div class="w-20 h-20 rounded-full bg-red-600 flex items-center justify-center shadow-2xl mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z" />
                    </svg>
                </div>

                <h2 class="text-3xl font-black italic uppercase tracking-tight text-red-400 mb-4">
                    Confirmar Exclusão
                </h2>

                <p class="text-white/90 text-lg italic leading-relaxed max-w-md mb-8">
                    Esta ação é <span class="font-black text-red-400 uppercase">irreversível</span>.  
                    Digite sua senha para confirmar a exclusão permanente da sua conta.
                </p>
            </div>

            <div class="mb-8">
                <x-input-label 
                    for="password" 
                    value="Senha"
                    class="text-white font-black uppercase italic mb-2"
                />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full bg-white/90 border-0 rounded-2xl px-5 py-3 text-[#003366] font-bold focus:ring-4 focus:ring-red-400"
                    placeholder="Digite sua senha"
                />

                <x-input-error 
                    :messages="$errors->userDeletion->get('password')" 
                    class="mt-3 text-red-300 font-bold"
                />
            </div>

            <div class="flex flex-col md:flex-row justify-center gap-4">
                
                <x-secondary-button 
                    x-on:click="$dispatch('close')"
                    class="bg-gray-300 hover:bg-gray-400 text-[#003366] px-8 py-3 rounded-2xl font-black uppercase italic shadow-lg border-b-4 border-gray-500 transition-all active:translate-y-1 active:border-b-0"
                >
                    Cancelar
                </x-secondary-button>

                <x-danger-button 
                    class="bg-red-600 hover:bg-red-700 px-8 py-3 rounded-2xl font-black uppercase italic shadow-xl border-b-4 border-red-900 transition-all active:translate-y-1 active:border-b-0"
                >
                    Excluir Permanentemente
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>