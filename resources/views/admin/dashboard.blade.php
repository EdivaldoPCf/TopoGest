@extends('layouts.admin')

@section('header_title', 'Visão Geral')

@section('content')
<div class="mb-8 bg-white/90 backdrop-blur-md p-6 rounded-2xl border border-white shadow-sm inline-block w-max pr-12">
        <h2 class="text-2xl font-bold text-slate-800">Painel de Informações</h2>
        <p class="text-slate-600 mt-1 font-medium">Bem-vindo de volta! Aqui está o resumo do sistema hoje.</p>
    </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                
                <!-- Card 1 -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between h-full">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Serviços Pendentes</p>
                        <div class="p-2 bg-orange-50 text-orange-500 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-baseline gap-2">
                        <h3 class="text-4xl font-extrabold text-slate-800">{{ $totalServicosPendentes ?? 0 }}</h3>
                        <span class="text-sm font-medium text-slate-400">aguardando</span>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between h-full">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Clientes</p>
                        <div class="p-2 bg-blue-50 text-blue-500 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-baseline gap-2">
                        <h3 class="text-4xl font-extrabold text-slate-800">{{ $totalClientes ?? 0 }}</h3>
                        <span class="text-sm font-medium text-slate-400">ativos</span>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between h-full">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Bases Registradas</p>
                        <div class="p-2 bg-emerald-50 text-emerald-500 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-baseline gap-2">
                        <h3 class="text-4xl font-extrabold text-slate-800">{{ $totalBases ?? 0 }}</h3>
                        <span class="text-sm font-medium text-slate-400">no sistema</span>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between h-full">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Marcos (Sistema)</p>
                        <div class="p-2 bg-indigo-50 text-indigo-500 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" /></svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center divide-x divide-slate-200">
                        <div class="pr-4">
                            <h3 class="text-3xl font-extrabold text-slate-800 leading-none">{{ $totalBca ?? 0 }}</h3>
                            <span class="text-xs font-semibold text-slate-400 uppercase">BCA</span>
                        </div>
                        <div class="pl-4">
                            <h3 class="text-3xl font-extrabold text-slate-800 leading-none">{{ $totalEmes ?? 0 }}</h3>
                            <span class="text-xs font-semibold text-slate-400 uppercase">EMES</span>
                        </div>
                    </div>
                </div>

            
@endsection
