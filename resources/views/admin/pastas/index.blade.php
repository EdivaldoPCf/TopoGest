@extends('layouts.admin')

@section('content')
<div class="w-full animate-fade"
     x-data="{
        createModal:false,
        deleteModal:false,
        deleteId:null,
        deleteName:''
     }">

    <!-- HEADER -->
    <div class="mb-8 bg-white/90 backdrop-blur-md p-6 rounded-2xl border border-white shadow-sm inline-block w-max pr-12">
        <h2 class="text-3xl font-black text-slate-800 tracking-tight">Serviços</h2>
        <p class="text-slate-600 mt-1 font-medium">Painel Administrativo de Pastas</p>
        </div>

    @if(session('success'))
        <div class="mb-6 rounded-[20px] bg-emerald-500/15 border border-emerald-500/30 p-4 text-emerald-100 animate-fade">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-[20px] bg-rose-500/15 border border-rose-500/30 p-4 text-rose-100 animate-fade">
            {{ session('error') }}
        </div>
    @endif

    <!-- MAIN CONTENT AREA -->
    <main class="w-full space-y-8">
    <div class="bg-white rounded-[32px] border border-slate-200 shadow-sm p-5 md:p-8">

                <!-- TOPO -->
                <div class="flex flex-col xl:flex-row gap-5 justify-between xl:items-center mb-8">

                    <div>
                        <h2 class="text-2xl font-black text-slate-800">
                            Pastas
                        </h2>
                        <p class="text-slate-500 mt-1 font-medium">
                            Gerencie as pastas cadastradas no sistema.
                        </p>
                    </div>

                    <!-- BOTÕES -->
                    <div class="flex flex-col md:flex-row gap-4">
                        <!-- NOVA -->
                        <button type="button" @click="createModal = true"
                            class="bg-emerald-600 hover:bg-emerald-700 text-slate-800 font-bold px-8 py-4 rounded-2xl shadow-lg shadow-emerald-600/30 transition flex items-center justify-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Criar Pasta
                        </button>
                    </div>

                </div>

                <!-- GRID DE PASTAS -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @forelse($pastas as $pasta)
                        <div class="bg-slate-50 border border-slate-200 rounded-3xl p-6 flex flex-col justify-between hover:shadow-md hover:border-blue-200 transition duration-300">
                            <div class="flex items-start gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-blue-50/50 border border-blue-100 flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-400 group-hover:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>
                        </div>
                                <div class="min-w-0">
                                    <h3 class="font-black text-slate-800 text-lg tracking-wide truncate">
                                        {{ $pasta->nome }}
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-1">
                                        Criada em {{ $pasta->created_at->format('d/m/Y') }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-4 mt-6 pt-4 border-t border-white/5">
                                <span class="px-3 py-1 rounded-full text-[10px] uppercase font-black border {{ $pasta->tipo_servico === 'pronto' ? 'bg-green-500/20 text-green-300 border-green-500/20' : 'bg-yellow-500/20 text-yellow-300 border-yellow-500/20' }}">
                                    {{ $pasta->tipo_servico === 'pronto' ? 'Concluído' : 'Pendente' }}
                                </span>

                                <div class="flex items-center gap-2">
                                    <!-- ABRIR -->
                                    <a href="{{ route('admin.pastas.show', $pasta->id) }}"
                                       class="bg-[#003366] hover:bg-[#004A7C] transition px-4 py-2 rounded-xl text-xs uppercase font-black shadow-lg">
                                        Abrir
                                    </a>

                                    <!-- EXCLUIR -->
                                    <button
                                        @click="
                                            deleteModal = true;
                                            deleteId = {{ $pasta->id }};
                                            deleteName = '{{ addslashes($pasta->nome) }}';
                                        "
                                        class="bg-red-600 hover:bg-red-700 transition px-4 py-2 rounded-xl text-xs uppercase font-black shadow-lg">
                                        Excluir
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-20 text-center text-slate-800/30 font-bold uppercase text-sm">
                            Nenhuma pasta encontrada.
                        </div>
                    @endforelse
                </div>

            </div>

        </main>

    <!-- MODAL CRIAR -->
    <div
        x-show="createModal"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4"
        x-cloak>

        <div class="bg-white rounded-[32px] border border-slate-200 shadow-2xl w-full max-w-lg p-8">

            <!-- TOPO -->
            <div class="flex justify-between items-center mb-8">

                <h2 class="text-3xl font-black uppercase italic text-slate-800">
                    Criar Pasta
                </h2>

                <button
                    @click="createModal = false"
                    class="text-slate-500 hover:text-slate-800 text-3xl leading-none">
                    ×
                </button>

            </div>

            <!-- FORM -->
            <form action="{{ route('pasta.store') }}" method="POST">
                @csrf
                <input type="hidden" name="tipo_servico" value="pendente">

                <!-- INPUT -->
                <div class="mb-8">
                    <label class="block text-slate-800/60 uppercase text-xs font-black mb-3">
                        Nome da Pasta
                    </label>
                    <input type="text"
                           name="nome"
                           required
                           placeholder="Digite o nome da pasta..."
                           class="w-full bg-white text-[#003366] rounded-2xl py-5 px-6 text-lg font-bold outline-none shadow-inner">
                </div>

                <!-- BOTÕES -->
                <div class="flex flex-col md:flex-row gap-4">
                    <button type="submit"
                            class="flex-1 bg-[#003366] hover:bg-[#002244] text-white py-4 rounded-2xl font-black uppercase shadow-xl transition">
                        Criar Pasta
                    </button>

                    <button type="button"
                            @click="createModal = false"
                            class="flex-1 bg-blue-100 hover:bg-blue-200 text-[#003366] py-4 rounded-2xl font-black uppercase shadow-xl transition">
                        Cancelar
                    </button>
                </div>

            </form>

        </div>

    </div>

    <!-- MODAL DELETE -->
    <div
        x-show="deleteModal"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4"
        x-cloak>

        <div class="bg-white rounded-[32px] border border-slate-200 shadow-2xl w-full max-w-lg p-8 text-center">

            <!-- ÍCONE -->
            <div class="w-20 h-20 rounded-full bg-red-500/20 border border-red-500/20 flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>

            <!-- TITULO -->
            <h2 class="text-3xl font-black uppercase italic text-red-400 mb-4">
                Área Sensível
            </h2>

            <!-- TEXTO -->
            <p class="text-slate-800/70 leading-relaxed mb-10">
                A pasta <span class="font-black text-yellow-300 uppercase" x-text="deleteName"></span> será enviada para aprovação de exclusão.
            </p>

            <!-- BOTÕES -->
            <div class="flex flex-col md:flex-row gap-4">
                <!-- CONFIRMAR -->
                <button
                    @click="
                        fetch(`/admin/pastas/${deleteId}/solicitar-exclusao`,{
                            method:'POST',
                            headers:{
                                'X-CSRF-TOKEN':'{{ csrf_token() }}',
                                'Accept':'application/json'
                            }
                        }).then(() => location.reload())
                    "
                    class="flex-1 bg-blue-100 hover:bg-blue-200 text-[#003366] py-4 rounded-2xl font-black uppercase shadow-xl transition">
                    Solicitar Exclusão
                </button>

                <!-- CANCEL -->
                <button
                    @click="deleteModal = false"
                    class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-800 py-4 rounded-2xl font-black uppercase shadow-xl transition">
                    Cancelar
                </button>
            </div>

        </div>

    </div>

</div>

@endsection