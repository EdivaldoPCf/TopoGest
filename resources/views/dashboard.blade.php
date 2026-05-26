@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-cover bg-center bg-fixed px-6 py-8"
     style="background-image: url('{{ asset('images/background-topo.jpg') }}');">

    <div class="max-w-7xl mx-auto">

        <!-- Header -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
            
            <div>
                <h1 class="text-4xl font-black italic uppercase text-white drop-shadow-lg tracking-tight">
                    Gestão de Pastas
                </h1>
                <p class="text-white/80 italic mt-1">
                    Organize arquivos, pendências e documentos do serviço.
                </p>
            </div>

            <!-- Ações -->
            <div class="flex flex-wrap gap-3">
                <button onclick="abrirModalPasta()"
                    class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-2xl shadow-xl font-black uppercase italic tracking-wide transition-all hover:scale-105 active:scale-95">
                    + Nova Pasta
                </button>

                <button onclick="abrirModal()"
                    class="bg-[#003366] hover:bg-[#002244] text-white px-6 py-3 rounded-2xl shadow-xl font-black uppercase italic tracking-wide transition-all hover:scale-105 active:scale-95">
                    + Nova Pendência
                </button>
            </div>
        </div>

        <!-- Card Principal -->
        <div class="bg-white/85 backdrop-blur-xl rounded-[32px] shadow-2xl border border-white/30 overflow-hidden">

            <!-- Título -->
            <div class="bg-[#003366] px-8 py-5">
                <h2 class="text-white text-2xl font-black italic uppercase tracking-tight">
                    Arquivos Literais
                </h2>
            </div>

            <!-- Tabela -->
            <div class="overflow-x-auto">
                <table class="w-full">

                    <thead class="bg-gray-100 text-[#003366] uppercase text-xs font-black tracking-wider">
                        <tr>
                            <th class="px-6 py-4 text-left">Nome</th>
                            <th class="px-6 py-4 text-left">Data</th>
                            <th class="px-6 py-4 text-left">Tamanho</th>
                            <th class="px-6 py-4 text-left">Tipo</th>
                            <th class="px-6 py-4 text-right">Ações</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">

                        @forelse($pastas as $pasta)
                        <tr class="hover:bg-blue-50/60 transition-all duration-200">

                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">

                                    <div class="bg-yellow-100 text-yellow-600 p-2 rounded-xl shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="h-6 w-6"
                                             fill="currentColor"
                                             viewBox="0 0 20 20">
                                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                                        </svg>
                                    </div>

                                    <div>
                                        <p class="font-black text-[#003366] text-lg italic uppercase">
                                            {{ $pasta->nome }}
                                        </p>
                                    </div>

                                </div>
                            </td>

                            <td class="px-6 py-5 text-sm text-gray-600 font-semibold">
                                {{ $pasta->created_at->format('d/m/Y') }}
                            </td>

                            <td class="px-6 py-5 text-sm text-gray-500 italic">
                                --
                            </td>

                            <td class="px-6 py-5">
                                <span class="bg-[#003366]/10 text-[#003366] px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wide">
                                    Pasta
                                </span>
                            </td>

                            <td class="px-6 py-5">
                                <div class="flex justify-end items-center gap-3">

                                    <a href="{{ route('pasta.show', $pasta->id) }}"
                                       class="bg-[#004A7C] hover:bg-[#003366] text-white px-5 py-2 rounded-xl text-xs font-black uppercase italic shadow-lg transition-all hover:scale-105">
                                        Abrir
                                    </a>

                                    <button onclick="confirmarExclusao({{ $pasta->id }})"
                                        class="bg-red-500 hover:bg-red-600 text-white px-5 py-2 rounded-xl text-xs font-black uppercase italic shadow-lg transition-all hover:scale-105">
                                        Excluir
                                    </button>

                                </div>
                            </td>

                        </tr>

                        @empty

                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">

                                <div class="flex flex-col items-center justify-center text-gray-500">

                                    <div class="bg-gray-100 p-5 rounded-full mb-4">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="h-10 w-10"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="1.5"
                                                  d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                                        </svg>
                                    </div>

                                    <p class="text-2xl font-black italic uppercase">
                                        Nenhuma pasta encontrada
                                    </p>

                                    <span class="text-sm italic mt-2">
                                        Crie uma nova pasta para começar.
                                    </span>

                                </div>

                            </td>
                        </tr>

                        @endforelse

                    </tbody>

                </table>
            </div>
        </div>

    </div>
</div>

<!-- Modal Pasta -->
<div id="modalPasta"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 backdrop-blur-sm p-4">

    <div class="bg-[#EAE8E3] rounded-[32px] shadow-2xl w-full max-w-md overflow-hidden">

        <div class="bg-green-600 px-8 py-5">
            <h3 class="text-white text-2xl font-black italic uppercase text-center">
                Criar Nova Pasta
            </h3>
        </div>

        <form action="{{ route('pasta.store') }}" method="POST" class="p-8 space-y-6">
            @csrf

            <div>
                <label class="block text-[#003366] text-sm font-black uppercase italic mb-2">
                    Nome da Pasta
                </label>

                <input type="text"
                       name="nome"
                       placeholder="Ex: 2026"
                       required
                       class="w-full rounded-2xl border-none bg-gray-200 px-5 py-4 outline-none focus:ring-4 focus:ring-green-300 font-bold">
            </div>

            <div class="flex justify-between gap-4 pt-4">

                <button type="button"
                        onclick="fecharModalPasta()"
                        class="flex-1 bg-red-500 hover:bg-red-600 text-white py-3 rounded-2xl font-black uppercase italic shadow-lg transition">
                    Cancelar
                </button>

                <button type="submit"
                        class="flex-1 bg-green-500 hover:bg-green-600 text-white py-3 rounded-2xl font-black uppercase italic shadow-lg transition">
                    Criar
                </button>

            </div>
        </form>

    </div>
</div>

<!-- Modal Pendência -->
<div id="modalPendencia"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 backdrop-blur-sm p-4">

    <div class="bg-[#EAE8E3] rounded-[32px] shadow-2xl w-full max-w-lg overflow-hidden">

        <div class="bg-[#003366] px-8 py-5">
            <h3 class="text-white text-2xl font-black italic uppercase text-center">
                Nova Pendência
            </h3>
        </div>

        <form action="{{ route('arquivo.pendencia', $servico->id ?? 0) }}"
              method="POST"
              class="p-8 space-y-6">
            @csrf

            <div>
                <label class="block text-[#003366] text-sm font-black uppercase italic mb-2">
                    Título
                </label>

                <input type="text"
                       name="titulo"
                       placeholder="Ex: Planta do imóvel"
                       required
                       class="w-full rounded-2xl border-none bg-gray-200 px-5 py-4 outline-none focus:ring-4 focus:ring-blue-300 font-bold">
            </div>

            <div>
                <label class="block text-[#003366] text-sm font-black uppercase italic mb-2">
                    Descrição
                </label>

                <textarea name="descricao"
                          rows="5"
                          placeholder="Descreva a pendência..."
                          class="w-full rounded-2xl border-none bg-gray-200 px-5 py-4 outline-none resize-none focus:ring-4 focus:ring-blue-300 font-medium"></textarea>
            </div>

            <div class="flex justify-between gap-4 pt-4">

                <button type="button"
                        onclick="fecharModal()"
                        class="flex-1 bg-red-500 hover:bg-red-600 text-white py-3 rounded-2xl font-black uppercase italic shadow-lg transition">
                    Cancelar
                </button>

                <button type="submit"
                        class="flex-1 bg-[#003366] hover:bg-[#002244] text-white py-3 rounded-2xl font-black uppercase italic shadow-lg transition">
                    Enviar
                </button>

            </div>
        </form>

    </div>
</div>

<script>
    // Modal Pasta
    function abrirModalPasta() {
        const modal = document.getElementById('modalPasta');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function fecharModalPasta() {
        const modal = document.getElementById('modalPasta');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Modal Pendência
    function abrirModal() {
        const modal = document.getElementById('modalPendencia');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function fecharModal() {
        const modal = document.getElementById('modalPendencia');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Exclusão
    function confirmarExclusao(id) {

        if (confirm('Deseja excluir permanentemente esta pasta?')) {

            let form = document.createElement('form');

            form.action = `/servico/${id}`;
            form.method = 'POST';

            form.innerHTML = `
                @csrf
                @method('DELETE')
            `;

            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endsection