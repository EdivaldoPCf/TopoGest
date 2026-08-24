?<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Assinatura — TopoGest</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') . '?v=' . @filemtime(public_path('images/logo-icon.png')) }}">
</head>
<body class="min-h-screen bg-[#001a33] text-white flex items-center justify-center p-6">
    <div class="w-full max-w-xl text-center">
        <img src="{{ asset('images/logo-completa.png') . '?v=' . @filemtime(public_path('images/logo-completa.png')) }}" alt="Getec" class="h-16 mx-auto mb-8" onerror="this.style.display='none'">

        @if($contrato)
            <div class="bg-emerald-500/15 border border-emerald-500/30 rounded-[30px] p-10">
                <div class="text-5xl mb-4">✅</div>
                <h1 class="text-2xl font-black uppercase italic text-emerald-300 mb-1">Documento Autêntico</h1>
                <p class="text-emerald-400/70 font-black text-sm mb-6">{{ $contrato->codigo_contrato }}</p>
                <p class="text-white/60 text-sm mb-8">Este contrato foi assinado digitalmente e sua autenticidade está confirmada.</p>
                <div class="space-y-3 text-left bg-white/5 rounded-2xl p-5">
                    <div><span class="text-[10px] uppercase tracking-widest text-white/40 block">Código do Contrato</span><strong>{{ $contrato->codigo_contrato }}</strong></div>
                    <div><span class="text-[10px] uppercase tracking-widest text-white/40 block">Imóvel</span><strong>{{ $contrato->nome_imovel }}</strong></div>
                    <div><span class="text-[10px] uppercase tracking-widest text-white/40 block">Proprietário</span><strong>{{ $contrato->nome_proprietario }}</strong></div>
                    <div><span class="text-[10px] uppercase tracking-widest text-white/40 block">Assinado por</span><strong>{{ $contrato->assinante_nome }}</strong></div>
                    <div><span class="text-[10px] uppercase tracking-widest text-white/40 block">Data/Hora</span><strong>{{ $contrato->assinado_em->format('d/m/Y \à\s H:i:s') }}</strong></div>
                    <div><span class="text-[10px] uppercase tracking-widest text-white/40 block">Hash SHA-256</span><code class="text-[9px] text-white/50 break-all">{{ $contrato->assinatura_hash }}</code></div>
                </div>
            </div>
        @else
            <div class="bg-rose-500/15 border border-rose-500/30 rounded-[30px] p-10">
                <div class="text-5xl mb-4">❌</div>
                <h1 class="text-2xl font-black uppercase italic text-rose-300 mb-2">Documento Não Encontrado</h1>
                <p class="text-white/50 text-sm">O hash informado não corresponde a nenhum contrato assinado em nosso sistema. O documento pode ter sido alterado ou o link está incorreto.</p>
            </div>
        @endif

        <p class="text-white/20 text-xs mt-8">TopoGest &amp; Georreferenciamento — Sistema TopoGest</p>
    </div>
</body>
</html>

