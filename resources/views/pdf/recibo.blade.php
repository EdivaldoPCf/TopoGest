?<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 11pt;
            color: #1a1a1a;
            line-height: 1.6;
            padding: 20px 40px;
        }

        /* ── Cabeçalho ── */
        .header {
            border-bottom: 3px solid #003366;
            padding-bottom: 12px;
            margin-bottom: 30px;
            display: table;
            width: 100%;
        }
        .header-logo {
            display: table-cell;
            width: 90px;
            vertical-align: middle;
        }
        .header-logo img {
            width: 80px;
            height: auto;
        }
        .header-info {
            display: table-cell;
            vertical-align: middle;
            padding-left: 14px;
        }
        .header-info h1 {
            font-size: 14pt;
            color: #003366;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header-info p {
            font-size: 9pt;
            color: #555;
        }
        .header-number {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 150px;
        }
        .header-number span {
            font-size: 8pt;
            color: #888;
            display: block;
            text-transform: uppercase;
        }
        .header-number strong {
            font-size: 11pt;
            color: #003366;
        }

        /* ── Título ── */
        .titulo {
            text-align: center;
            margin: 20px 0 30px;
        }
        .titulo h2 {
            font-size: 18pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #003366;
            letter-spacing: 2px;
        }
        .titulo-linha {
            width: 80px;
            height: 4px;
            background: #00E500;
            margin: 8px auto 0;
        }

        /* ── Corpo do Recibo ── */
        .corpo-recibo {
            text-align: justify;
            font-size: 12pt;
            line-height: 2;
            margin-bottom: 40px;
        }

        .valor-destaque {
            font-weight: bold;
            color: #003366;
            font-size: 13pt;
        }

        /* ── Blocos de Informação ── */
        .bloco {
            margin-bottom: 20px;
            border: 1px solid #d0d8e4;
            border-radius: 4px;
            overflow: hidden;
        }
        .bloco-header {
            background: #003366;
            color: #ffffff;
            font-size: 9.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 8px 14px;
        }
        .bloco-body {
            padding: 12px 14px;
            background: #f8fafd;
        }
        .campo-linha {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }
        .campo-label {
            display: table-cell;
            font-size: 9pt;
            color: #555;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            width: 25%;
            vertical-align: top;
            padding-right: 8px;
        }
        .campo-valor {
            display: table-cell;
            font-size: 10.5pt;
            color: #1a1a1a;
            font-weight: bold;
            vertical-align: top;
        }

        /* ── Assinatura Emitente ── */
        .assinatura-emitente {
            margin-top: 60px;
            text-align: center;
            width: 60%;
            margin-left: auto;
            margin-right: auto;
        }
        .assinatura-linha {
            border-top: 1.5px solid #333;
            margin-bottom: 8px;
        }
        .assinatura-nome {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #003366;
        }
        .assinatura-cargo {
            font-size: 9pt;
            color: #666;
        }

        /* ── Rodapé de assinatura digital ── */
        .rodape-assinatura {
            margin-top: 50px;
            border: 1.5px dashed #00E500;
            border-radius: 6px;
            padding: 15px 20px;
            background: #f0fff0;
        }
        .rodape-assinatura .tag {
            font-size: 8.5pt;
            color: #008800;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: block;
            margin-bottom: 6px;
        }
        .rodape-assinatura p { font-size: 9pt; color: #444; }
        .rodape-assinatura .hash { font-family: monospace; font-size: 8pt; color: #666; word-break: break-all; }

        /* ── Selo Assinatura Digital (Estilo Token A3) ── */
        .selo-assinatura {
            border: 1px solid #999;
            background: #fff;
            padding: 4px;
            width: 200px;
            margin: 0 auto -10px auto; /* puxa pra baixo perto da linha */
            position: relative;
            z-index: 10;
            text-align: left;
            border-radius: 2px;
        }
        .selo-assinatura-titulo {
            font-size: 7.5pt;
            color: #0000cc;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .selo-assinatura-nome {
            font-size: 9.5pt;
            color: #0000cc;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .selo-assinatura-texto {
            font-size: 6.5pt;
            color: #0000cc;
            line-height: 1.1;
        }
        .selo-assinatura-link {
            font-size: 7.5pt;
            color: #0000cc;
            font-weight: bold;
        }
        .selo-assinatura-data {
            font-size: 6.5pt;
            color: #666;
            margin-top: 2px;
        }

        /* ── Rodapé página ── */
        .page-footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #aaa;
        }
    </style>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">
</head>
<body>

    {{-- Cabeçalho --}}
    <div class="header">
        <div class="header-logo">
            @php $logoPath = public_path('images/logo-completa.png'); @endphp
            @if(file_exists($logoPath))
                <img src="{{ $logoPath }}" alt="Getec Topografia">
            @else
                <div style="width:70px;height:70px;background:#003366;border-radius:6px;display:flex;align-items:center;justify-content:center;">
                    <span style="color:#fff;font-weight:bold;font-size:9pt;text-align:center;display:block;padding:4px;">GETEC</span>
                </div>
            @endif
        </div>
        <div class="header-info">
            <h1>GETEC TOPOGRAFIA LTDA</h1>
            <p>CNPJ: 03.715.655/0001-34</p>
        </div>
        <div class="header-number">
            <span>Recibo Referente ao Contrato</span>
            <strong>{{ $contrato->codigo_contrato }}</strong>
        </div>
    </div>

    {{-- Título --}}
    <div class="titulo">
        <h2>Recibo de Pagamento</h2>
        <div class="titulo-linha"></div>
    </div>

    {{-- Corpo --}}
    <div class="corpo-recibo">
        Recebemos de <strong>{{ strtoupper($contrato->nome_proprietario) }}</strong>, 
        inscrito(a) no CPF sob o nº <strong>{{ $contrato->cpfFormatado() }}</strong>, 
        a importância de <span class="valor-destaque">R$ {{ $valor_recebido }}</span>, 
        referente a: <br>
        <strong>{{ $descricao_pagamento }}</strong>.
    </div>

    {{-- Detalhes do Serviço --}}
    <div class="bloco">
        <div class="bloco-header">Detalhes do Serviço Vinculado</div>
        <div class="bloco-body">
            <div class="campo-linha">
                <span class="campo-label">Serviço:</span>
                <span class="campo-valor">{{ strtoupper($contrato->tipo_servico) }}</span>
            </div>
            <div class="campo-linha">
                <span class="campo-label">Imóvel:</span>
                <span class="campo-valor">{{ strtoupper($contrato->nome_imovel) }}</span>
            </div>
            <div class="campo-linha">
                <span class="campo-label">Localização:</span>
                <span class="campo-valor">{{ $contrato->localizacao }} - {{ strtoupper($contrato->municipio) }}</span>
            </div>
            <div class="campo-linha" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd;">
                <span class="campo-label">Valor Total:</span>
                <span class="campo-valor" style="color: #003366;">R$ {{ $valor_total }}</span>
            </div>
        </div>
    </div>

    {{-- Local e data --}}
    <p style="margin-top: 30px; text-align:right; font-size:11pt;">
        {{ strtoupper($contrato->municipio) }}, {{ now()->translatedFormat('d \d\e F \d\e Y') }}
    </p>

    {{-- Assinatura do Emitente --}}
    <div class="assinatura-emitente">
        <div class="selo-assinatura">
            <div class="selo-assinatura-titulo">Assinado Digitalmente</div>
            <div class="selo-assinatura-nome">{{ strtoupper($contrato->nome_contratado) }}</div>
            <div class="selo-assinatura-texto">A conformidade pode ser verificada em:</div>
            <div class="selo-assinatura-link">topogest.com.br/assinador</div>
            <div class="selo-assinatura-data">{{ now()->format('d/m/Y H:i:s') }} | IP: {{ request()->ip() }}</div>
        </div>
        <div class="assinatura-linha"></div>
        <div class="assinatura-nome">{{ strtoupper($contrato->nome_contratado) }}</div>
        <div class="assinatura-cargo">Responsável Técnico<br>Getec Topografia LTDA</div>
    </div>

    <div class="page-footer">
        Documento gerado automaticamente pelo sistema TopoGest
    </div>

</body>
</html>

