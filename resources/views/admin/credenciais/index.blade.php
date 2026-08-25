@extends('layouts.admin')

@section('content')
<div class="w-full animate-fade" x-data="{ modalOpen: false, modalMode: 'add', formAction: '', credId: '', credCodigo: '', credNome: '', credCrea: '' }">

    <!-- HEADER -->
    <div class="flex flex-col xl:flex-row justify-between gap-8 mb-10">
        <!-- TITLE -->
        <div class="bg-white/90 backdrop-blur-md p-6 rounded-2xl border border-slate-200 shadow-sm inline-block w-max pr-12">
            <h2 class="text-3xl font-black text-slate-800 tracking-tight">Credenciais</h2>
            <p class="text-slate-600 mt-1 font-medium">Gerencie as credenciais do SIGEF/INCRA</p>
        </div>

        <!-- BTN ADD -->
        <div class="flex items-center">
            <button @click="modalMode = 'add'; formAction = '{{ route('credenciais.store') }}'; credCodigo = ''; credNome = ''; credCrea = ''; modalOpen = true" 
                    class="bg-[#00E500] hover:bg-[#00cc00] text-slate-900 font-black uppercase px-8 py-4 rounded-3xl shadow-lg transition-transform hover:scale-105 active:scale-95 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Nova Credencial
            </button>
        </div>
    </div>

    <!-- NOTIFICATIONS -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- TABLE -->
    <section class="max-w-7xl">
        <div class="bg-white rounded-[32px] border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-full">
                    <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                        <tr class="uppercase tracking-widest text-sm">
                            <th class="py-5 px-6 text-left font-black">Código</th>
                            <th class="py-5 px-6 text-left font-black">Profissional</th>
                            <th class="py-5 px-6 text-left font-black">CREA</th>
                            <th class="py-5 px-6 text-center font-black w-32">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($credenciais as $cred)
                            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                <td class="py-4 px-6">
                                    <span class="text-[#003366] font-black text-lg bg-blue-50 px-3 py-1 rounded-lg border border-blue-100">{{ $cred->codigo }}</span>
                                </td>
                                <td class="py-4 px-6 text-slate-700 font-bold">
                                    {{ $cred->nome ?: 'Não informado' }}
                                </td>
                                <td class="py-4 px-6 text-slate-600 font-semibold">
                                    {{ $cred->crea ?: 'Não informado' }}
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex justify-center gap-2">
                                        <button @click="modalMode = 'edit'; formAction = '{{ route('credenciais.update', $cred->id) }}'; credCodigo = '{{ $cred->codigo }}'; credNome = '{{ addslashes($cred->nome) }}'; credCrea = '{{ addslashes($cred->crea) }}'; modalOpen = true" 
                                                class="bg-blue-100 hover:bg-blue-200 text-blue-700 p-2 rounded-lg transition-colors" title="Editar">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        
                                        <form action="{{ route('credenciais.destroy', $cred->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta credencial?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-100 hover:bg-red-200 text-red-700 p-2 rounded-lg transition-colors" title="Excluir">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 px-6 text-center text-slate-500 font-semibold">
                                    Nenhuma credencial cadastrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- MODAL ADD/EDIT -->
    <div x-show="modalOpen" 
         style="display: none;"
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            
            <div x-show="modalOpen" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity" 
                 @click="modalOpen = false" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="modalOpen" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-slate-200">
                
                <form :action="formAction" method="POST" class="flex flex-col h-full">
                    @csrf
                    <template x-if="modalMode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    
                    <div class="bg-white px-8 pt-8 pb-6">
                        <div class="mb-6">
                            <h3 class="text-2xl leading-6 font-black text-slate-800" id="modal-title">
                                <span x-text="modalMode === 'add' ? 'Nova Credencial' : 'Editar Credencial'"></span>
                            </h3>
                            <p class="text-sm text-slate-500 mt-2">Preencha os dados da credencial SIGEF.</p>
                        </div>
                        
                        <div class="space-y-5">
                            <div>
                                <label class="block text-slate-700 text-sm font-bold mb-2 uppercase tracking-wide">
                                    Código da Credencial *
                                </label>
                                <input type="text" name="codigo" x-model="credCodigo" required placeholder="Ex: BCA" class="w-full bg-slate-50 border border-slate-300 text-slate-800 px-4 py-3 rounded-xl outline-none focus:border-[#003366] focus:ring-4 focus:ring-blue-400/30 uppercase font-black">
                            </div>
                            
                            <div>
                                <label class="block text-slate-700 text-sm font-bold mb-2 uppercase tracking-wide">
                                    Nome do Profissional
                                </label>
                                <input type="text" name="nome" x-model="credNome" placeholder="Ex: Edivaldo Rodrigues da Silva" class="w-full bg-slate-50 border border-slate-300 text-slate-800 px-4 py-3 rounded-xl outline-none focus:border-[#003366] focus:ring-4 focus:ring-blue-400/30 font-bold">
                            </div>
                            
                            <div>
                                <label class="block text-slate-700 text-sm font-bold mb-2 uppercase tracking-wide">
                                    Registro (CREA/CFT)
                                </label>
                                <input type="text" name="crea" x-model="credCrea" placeholder="Ex: 2684/D-AC" class="w-full bg-slate-50 border border-slate-300 text-slate-800 px-4 py-3 rounded-xl outline-none focus:border-[#003366] focus:ring-4 focus:ring-blue-400/30 font-bold">
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-slate-50 px-8 py-5 flex flex-col sm:flex-row-reverse gap-3 border-t border-slate-200">
                        <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-6 py-3 bg-[#003366] text-base font-black text-white hover:bg-[#002244] focus:outline-none sm:w-auto sm:text-sm uppercase tracking-wider transition-colors">
                            Salvar
                        </button>
                        <button type="button" @click="modalOpen = false" class="w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-sm px-6 py-3 bg-white text-base font-bold text-slate-700 hover:bg-slate-50 focus:outline-none sm:w-auto sm:text-sm uppercase tracking-wider transition-colors">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
