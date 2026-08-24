<!-- resources/views/includes/header-public.blade.php -->
<div class="flex justify-between items-center mb-8 w-full max-w-6xl mx-auto">
    
    <!-- Logo composta como Link para Home (Sempre volta para '/') -->
    <a href="{{ route('welcome') }}" class="relative flex items-center gap-4 hover:opacity-95 transition group">
        <div class="relative flex items-center justify-center w-20 h-20 rounded-2xl bg-white shadow-2xl transition group-hover:scale-105">
            <img src="{{ asset('images/logo-icon.png') . '?v=' . @filemtime(public_path('images/logo-icon.png')) }}" alt="Ícone TG" class="w-full h-full object-contain p-2">
        </div>
        
        <div class="relative flex items-center h-14 px-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <img src="{{ asset('images/logo-text.png') . '?v=' . @filemtime(public_path('images/logo-text.png')) }}" alt="TopoGest" class="relative z-10 h-8 w-auto">
        </div>
    </a>

    <!-- Botão Azul Voltar (Com função JavaScript para voltar no histórico) -->
    <button onclick="history.back()" class="bg-[#003366] text-white px-10 py-3 rounded-2xl font-bold shadow-xl hover:bg-blue-900 transition text-2xl">
        Voltar
    </button>
</div>