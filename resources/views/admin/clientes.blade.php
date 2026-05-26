<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - TopoGest</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        ::-webkit-scrollbar{
            width:10px;
            height:10px;
        }

        ::-webkit-scrollbar-thumb{
            background:#003366;
            border-radius:999px;
        }

        [x-cloak]{
            display:none!important;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>

<body class="min-h-screen bg-[#eef3f8] overflow-x-hidden">

    <!-- Background -->
    <div class="fixed inset-0 z-0">
        <div class="absolute inset-0 bg-cover bg-center"
             style="background-image:url('{{ asset('images/background-topo.jpg') }}')"></div>

        <div class="absolute inset-0 bg-white/80 backdrop-blur-[3px]"></div>
    </div>

    <div
        x-data="clientesPage()"
        class="relative z-10 min-h-screen"
    >

        <!-- HEADER -->
        <header class="px-6 lg:px-12 pt-8 pb-6">

            <div class="max-w-[95%] mx-auto">

                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">

                    <!-- LOGO -->
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center gap-4 group w-fit">

                        <div class="relative">

                            <div class="w-16 h-16 rounded-2xl bg-white shadow-2xl flex items-center justify-center group-hover:scale-105 transition">
                                <img src="{{ asset('images/logo-icon.png') }}"
                                     class="w-full h-full object-contain p-2">
                            </div>

                            <div class="absolute -inset-1 rounded-2xl border border-white/40"></div>
                        </div>

                        <div>
                            <h1 class="text-3xl font-black text-[#003366] tracking-tight">
                                TopoGest
                            </h1>

                            <p class="text-sm text-[#003366]/70 font-semibold">
                                Gestão de Clientes
                            </p>
                        </div>
                    </a>

                    <!-- SEARCH -->
                    <div class="w-full lg:w-auto">

                        <div class="relative">

                            <input
                                type="text"
                                x-model="search"
                                placeholder="Pesquisar nome, email, CPF ou telefone..."
                                class="w-full lg:w-[430px] h-14 rounded-2xl bg-white/90 border border-white shadow-2xl pl-14 pr-5 text-[#003366] font-semibold outline-none focus:ring-4 focus:ring-[#003366]/10 transition"
                            >

                            <div class="absolute left-5 top-4 text-[#003366]/60">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-6 h-6"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </header>

        <!-- CONTENT -->
        <main class="px-6 lg:px-12 pb-12">

            <div class="max-w-[95%] mx-auto">

                <!-- CARD -->
                <div class="bg-white/70 backdrop-blur-xl border border-white rounded-[32px] shadow-[0_20px_80px_rgba(0,0,0,0.08)] overflow-hidden">

                    <!-- TOP -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 px-8 py-7 border-b border-slate-200/70">

                        <div>
                            <h2 class="text-3xl font-black text-[#003366] tracking-tight">
                                Clientes
                            </h2>

                            <p class="text-[#003366]/60 font-medium mt-1">
                                Gerencie os clientes cadastrados no sistema
                            </p>
                        </div>

                        <div class="flex items-center gap-3">

                            <div class="bg-[#003366] text-white px-5 py-2 rounded-2xl shadow-lg">
                                <span class="font-bold text-sm">
                                    {{ $usuarios->count() }} clientes
                                </span>
                            </div>

                        </div>

                    </div>

                    <!-- TABLE -->
                    <div class="overflow-x-auto">

                        <table class="w-full">

                            <thead class="bg-[#f8fbff] sticky top-0 z-20">

                                <tr class="border-b border-slate-200 whitespace-nowrap">

                                    <th class="text-left px-6 py-5 text-xs font-black uppercase tracking-wider text-[#003366]/60">
                                        Cliente
                                    </th>

                                    <th class="text-left px-4 py-5 text-xs font-black uppercase tracking-wider text-[#003366]/60">
                                        Documento
                                    </th>

                                    <th class="text-left px-4 py-5 text-xs font-black uppercase tracking-wider text-[#003366]/60">
                                        Email
                                    </th>

                                    <th class="text-left px-4 py-5 text-xs font-black uppercase tracking-wider text-[#003366]/60">
                                        Telefone
                                    </th>

                                    <th class="text-left px-4 py-5 text-xs font-black uppercase tracking-wider text-[#003366]/60">
                                        Cadastro
                                    </th>

                                    <th class="text-center px-6 py-5 text-xs font-black uppercase tracking-wider text-[#003366]/60">
                                        Ações
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                @forelse($usuarios as $cliente)

                                    <tr
                                        x-data="{ editing:false }"

                                        x-show="
                                            search === '' ||
                                            $el.innerText.toLowerCase().includes(search.toLowerCase()) ||
                                            (search.replace(/\D/g,'').length > 0 &&
                                            $el.innerText.replace(/\D/g,'').includes(search.replace(/\D/g,'')))
                                        "

                                        class="border-b border-slate-100 hover:bg-[#f8fbff]/80 transition whitespace-nowrap"
                                    >

                                        <!-- CLIENTE -->
                                        <td class="px-6 py-6">

                                            <div class="flex items-center gap-4">

                                                <div class="w-14 h-14 rounded-2xl overflow-hidden bg-[#003366] text-white flex items-center justify-center font-black text-lg shadow-lg">

                                                    @if($cliente->photo)

                                                        <img
                                                            src="{{ asset('storage/'.$cliente->photo) }}"
                                                            class="w-full h-full object-cover"
                                                        >

                                                    @else

                                                        {{ strtoupper(substr($cliente->name,0,2)) }}

                                                    @endif

                                                </div>

                                                <div class="min-w-0">

                                                    <div
                                                        x-show="!editing"
                                                        class="font-black text-[#003366] text-sm truncate"
                                                    >
                                                        {{ $cliente->name }}
                                                    </div>

                                                    <input
                                                        x-show="editing"
                                                        type="text"
                                                        name="name"
                                                        value="{{ $cliente->name }}"
                                                        class="bg-white border border-slate-300 rounded-xl px-4 h-11 w-full text-sm font-bold text-[#003366] outline-none focus:ring-4 focus:ring-[#003366]/10"
                                                    >

                                                    <div class="text-xs text-slate-500 mt-1">
                                                        Cliente TopoGest
                                                    </div>

                                                </div>

                                            </div>

                                        </td>

                                        <!-- CPF -->
                                        <td class="px-4 py-6">

                                            <span
                                                x-show="!editing"
                                                class="font-semibold text-[#003366]"
                                            >
                                                {{ $cliente->formatted_cpf }}
                                            </span>

                                            <input
                                                x-show="editing"
                                                type="text"
                                                name="cpf"
                                                maxlength="18"
                                                value="{{ $cliente->formatted_cpf }}"
                                                class="doc-mask bg-white border border-slate-300 rounded-xl px-4 h-11 w-48 text-sm font-bold text-[#003366] outline-none focus:ring-4 focus:ring-[#003366]/10"
                                            >

                                        </td>

                                        <!-- EMAIL -->
                                        <td class="px-4 py-6 max-w-[180px] lg:max-w-[240px] truncate" title="{{ $cliente->email }}">

                                             <span
                                                 x-show="!editing"
                                                 class="font-semibold text-slate-600"
                                             >
                                                 {{ $cliente->email }}
                                             </span>

                                            <input
                                                x-show="editing"
                                                type="email"
                                                name="email"
                                                value="{{ $cliente->email }}"
                                                class="bg-white border border-slate-300 rounded-xl px-4 h-11 w-full text-sm font-bold text-[#003366] outline-none focus:ring-4 focus:ring-[#003366]/10"
                                            >

                                        </td>

                                        <!-- PHONE -->
                                        <td class="px-4 py-6">

                                            <span
                                                x-show="!editing"
                                                class="font-semibold text-slate-600"
                                            >
                                                {{ $cliente->formatted_phone }}
                                            </span>

                                            <input
                                                x-show="editing"
                                                type="text"
                                                name="phone"
                                                maxlength="16"
                                                value="{{ $cliente->formatted_phone }}"
                                                class="phone-mask bg-white border border-slate-300 rounded-xl px-4 h-11 w-40 text-sm font-bold text-[#003366] outline-none focus:ring-4 focus:ring-[#003366]/10"
                                            >

                                        </td>

                                        <!-- DATE -->
                                        <td class="px-4 py-6">

                                            <div class="inline-flex items-center gap-2 bg-slate-100 text-slate-600 rounded-xl px-4 py-2 text-xs font-bold">
                                                {{ $cliente->created_at->format('d/m/Y') }}
                                            </div>

                                        </td>

                                        <!-- ACTIONS -->
                                        <td class="px-6 py-6">

                                            <div class="flex items-center justify-center gap-3">

                                                <!-- OPEN -->
                                                <a
                                                    x-show="!editing"
                                                    href="{{ route('admin.clientes.gestao',$cliente->id) }}"
                                                    class="h-11 px-5 rounded-xl bg-[#003366] text-white text-xs font-black uppercase tracking-wide flex items-center justify-center hover:scale-[1.03] hover:bg-[#00264d] transition shadow-lg"
                                                >
                                                    Abrir
                                                </a>

                                                <!-- EDIT -->
                                                <button
                                                    @click="editing = !editing"
                                                    :class="
                                                        editing
                                                        ? 'bg-slate-500 hover:bg-slate-600'
                                                        : 'bg-orange-500 hover:bg-orange-600'
                                                    "
                                                    class="h-11 px-5 rounded-xl text-white text-xs font-black uppercase tracking-wide transition shadow-lg"
                                                >
                                                    <span x-text="editing ? 'Cancelar' : 'Editar'"></span>
                                                </button>

                                                <!-- SAVE -->
                                                <button
                                                    x-show="editing"
                                                    @click="confirmSave($event, {{ $cliente->id }}, '{{ addslashes($cliente->name) }}')"
                                                    class="h-11 px-5 rounded-xl bg-green-600 hover:bg-green-700 text-white text-xs font-black uppercase tracking-wide transition shadow-lg"
                                                >
                                                    Salvar
                                                </button>

                                            </div>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td colspan="6" class="py-24 text-center">

                                            <div class="flex flex-col items-center">

                                                <div class="w-24 h-24 rounded-full bg-slate-100 flex items-center justify-center mb-6">

                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                         class="w-12 h-12 text-slate-400"
                                                         fill="none"
                                                         viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round"
                                                              stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M17 20h5V4H2v16h5m10 0v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6m10 0H7"/>
                                                    </svg>

                                                </div>

                                                <h3 class="text-2xl font-black text-[#003366]">
                                                    Nenhum cliente encontrado
                                                </h3>

                                                <p class="text-slate-500 mt-2">
                                                    Ainda não existem clientes cadastrados.
                                                </p>

                                            </div>

                                        </td>

                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </main>

        <!-- MODAL -->
        <div
            id="customModal"
            class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-6"
        >

            <div class="bg-white rounded-[32px] w-full max-w-lg shadow-[0_20px_80px_rgba(0,0,0,0.2)] overflow-hidden">

                <div class="p-8">

                    <div class="w-20 h-20 rounded-3xl bg-[#003366]/10 flex items-center justify-center mx-auto mb-6">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="w-10 h-10 text-[#003366]"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01"/>
                        </svg>

                    </div>

                    <h2
                        id="modalTitle"
                        class="text-3xl font-black text-center text-[#003366] mb-4"
                    >
                        Atenção
                    </h2>

                    <p
                        id="modalMessage"
                        class="text-center text-slate-600 leading-relaxed text-lg"
                    ></p>

                    <div class="flex items-center justify-center gap-4 mt-10">

                        <button
                            id="btnCancel"
                            onclick="closeModal()"
                            class="h-12 px-8 rounded-2xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-black uppercase text-sm transition"
                        >
                            Cancelar
                        </button>

                        <button
                            id="btnConfirm"
                            class="h-12 px-8 rounded-2xl bg-[#003366] hover:bg-[#00264d] text-white font-black uppercase text-sm transition shadow-lg"
                        >
                            Confirmar
                        </button>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <script>

        function clientesPage(){
            return {
                search:''
            }
        }

        const modal = document.getElementById('customModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        const btnConfirm = document.getElementById('btnConfirm');
        const btnCancel = document.getElementById('btnCancel');

        let currentRowInfo = null;

        document.addEventListener('input', function (e) {

            if (e.target.classList.contains('doc-mask')) {

                let v = e.target.value.replace(/\D/g, '');

                if (v.length <= 11) {

                    v = v
                        .replace(/(\d{3})(\d)/, '$1.$2')
                        .replace(/(\d{3})(\d)/, '$1.$2')
                        .replace(/(\d{3})(\d{1,2})$/, '$1-$2');

                } else {

                    v = v.slice(0,14)
                        .replace(/^(\d{2})(\d)/, '$1.$2')
                        .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
                        .replace(/\.(\d{3})(\d)/, '.$1/$2')
                        .replace(/(\d{4})(\d)/, '$1-$2');
                }

                e.target.value = v;
            }

            if (e.target.classList.contains('phone-mask')) {

                let v = e.target.value.replace(/\D/g,'');

                if(v.length > 11) v = v.slice(0,11);

                if(v.length > 2){
                    v = '(' + v.substring(0,2) + ') ' + v.substring(2);
                }

                if(v.length > 10){
                    v = v.substring(0,10) + '-' + v.substring(10);
                }

                e.target.value = v;
            }

        });

        function confirmSave(event, id, clientName){

            const row = event.target.closest('tr');

            currentRowInfo = {
                id:id,
                name:row.querySelector('input[name="name"]').value,
                cpf:row.querySelector('input[name="cpf"]').value,
                email:row.querySelector('input[name="email"]').value,
                phone:row.querySelector('input[name="phone"]').value,
            };

            modalTitle.innerText = 'Salvar alterações';
            modalMessage.innerHTML = `
                Você está prestes a atualizar os dados do cliente
                <strong class="text-[#003366]">${clientName}</strong>.
                <br><br>
                Deseja continuar?
            `;

            btnConfirm.innerText = 'Salvar';
            btnCancel.style.display = 'block';

            btnConfirm.onclick = executeSave;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal(){

            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }

        function executeSave(){

            btnConfirm.innerText = 'Salvando...';
            btnConfirm.disabled = true;

            fetch(`/admin/clientes/${currentRowInfo.id}`, {

                method:'POST',

                headers:{
                    'Content-Type':'application/json',
                    'Accept':'application/json',
                    'X-CSRF-TOKEN':'{{ csrf_token() }}'
                },

                body:JSON.stringify({
                    _method:'PUT',
                    ...currentRowInfo
                })

            })
            .then(async response => {

                if(response.ok){

                    showSuccess('Cliente atualizado com sucesso.');

                }else{

                    let msg = 'Erro ao salvar alterações.';

                    try{
                        const result = await response.json();
                        msg = result.message || msg;
                    }catch(e){}

                    showError(msg);
                }

            })
            .catch(() => {

                showError('Erro de conexão com o servidor.');

            });
        }

        function showSuccess(msg){

            modalTitle.innerText = 'Sucesso';
            modalMessage.innerHTML = `<strong>${msg}</strong>`;

            btnCancel.style.display = 'none';

            btnConfirm.innerText = 'OK';
            btnConfirm.disabled = false;

            btnConfirm.onclick = () => location.reload();
        }

        function showError(msg){

            modalTitle.innerText = 'Erro';
            modalMessage.innerHTML = `<strong>${msg}</strong>`;

            btnConfirm.innerText = 'Fechar';
            btnConfirm.disabled = false;

            btnConfirm.onclick = closeModal;
        }

    </script>

</body>
</html>