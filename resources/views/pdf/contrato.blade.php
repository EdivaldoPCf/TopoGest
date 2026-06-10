?<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 10.5pt;
            color: #1a1a1a;
            line-height: 1.6;
        }

        /* ── Cabeçalho ── */
        .header {
            border-bottom: 3px solid #003366;
            padding-bottom: 12px;
            margin-bottom: 18px;
            display: table;
            width: 100%;
        }
        .header-logo {
            display: table-cell;
            width: 80px;
            vertical-align: middle;
        }
        .header-logo img {
            width: 70px;
            height: auto;
        }
        .header-info {
            display: table-cell;
            vertical-align: middle;
            padding-left: 14px;
        }
        .header-info h1 {
            font-size: 13pt;
            color: #003366;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header-info p {
            font-size: 8.5pt;
            color: #555;
        }
        .header-number {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 140px;
        }
        .header-number span {
            font-size: 8pt;
            color: #888;
            display: block;
        }
        .header-number strong {
            font-size: 10pt;
            color: #003366;
        }

        /* ── Título ── */
        .titulo {
            text-align: center;
            margin: 18px 0 10px;
        }
        .titulo h2 {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #003366;
            letter-spacing: 1.5px;
        }
        .titulo-linha {
            width: 60px;
            height: 3px;
            background: #003366;
            margin: 6px auto 0;
        }

        /* ── Blocos ── */
        .bloco {
            margin-bottom: 16px;
            border: 1px solid #d0d8e4;
            border-radius: 4px;
            overflow: hidden;
        }
        .bloco-header {
            background: #003366;
            color: #ffffff;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 6px 12px;
        }
        .bloco-body {
            padding: 10px 12px;
            background: #f8fafd;
        }
        .campo-linha {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }
        .campo-label {
            display: table-cell;
            font-size: 8.5pt;
            color: #555;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            width: 35%;
            vertical-align: top;
            padding-right: 8px;
        }
        .campo-valor {
            display: table-cell;
            font-size: 10pt;
            color: #1a1a1a;
            font-weight: bold;
            vertical-align: top;
        }

        /* ── Cláusulas ── */
        .clausulas {
            margin-top: 6px;
        }
        .clausula {
            margin-bottom: 12px;
        }
        .clausula-titulo {
            font-size: 10pt;
            font-weight: bold;
            color: #003366;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .clausula p {
            font-size: 10pt;
            text-align: justify;
        }

        /* ── Valor destaque ── */
        .valor-box {
            background: #003366;
            color: #fff;
            padding: 8px 16px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 4px;
        }
        .valor-box span { font-size: 8.5pt; opacity: .8; display: block; }
        .valor-box strong { font-size: 14pt; }

        /* ── Assinaturas ── */
        .assinaturas {
            margin-top: 36px;
            display: table;
            width: 100%;
        }
        .assinatura-col {
            display: table-cell;
            width: 48%;
            text-align: center;
            vertical-align: top;
        }
        .assinatura-col + .assinatura-col {
            padding-left: 4%;
        }
        .assinatura-linha {
            border-top: 1.5px solid #333;
            margin-bottom: 5px;
            margin-top: 40px;
        }
        .assinatura-nome {
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .assinatura-cargo {
            font-size: 8.5pt;
            color: #666;
        }

        /* ── Rodapé de assinatura digital ── */
        .rodape-assinatura {
            margin-top: 20px;
            border: 1.5px dashed #003366;
            border-radius: 4px;
            padding: 10px 14px;
            background: #f0f5ff;
        }
        .rodape-assinatura .tag {
            font-size: 7.5pt;
            color: #003366;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: block;
            margin-bottom: 4px;
        }
        .rodape-assinatura p { font-size: 8pt; color: #444; }
        .rodape-assinatura .hash { font-family: monospace; font-size: 7pt; color: #666; word-break: break-all; }

        /* ── Selo Assinatura Digital (Estilo Token A3) ── */
        .selo-assinatura {
            border: 1px solid #999;
            background: #fff;
            padding: 4px;
            width: 190px;
            margin: 0 auto -10px auto; /* puxa pra baixo perto da linha */
            position: relative;
            z-index: 10;
            text-align: left;
            border-radius: 2px;
        }
        .selo-assinatura-titulo {
            font-size: 6.5pt;
            color: #0000cc;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .selo-assinatura-nome {
            font-size: 8.5pt;
            color: #0000cc;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .selo-assinatura-texto {
            font-size: 5.5pt;
            color: #0000cc;
            line-height: 1.1;
        }
        .selo-assinatura-link {
            font-size: 6.5pt;
            color: #0000cc;
            font-weight: bold;
        }
        .selo-assinatura-data {
            font-size: 5.5pt;
            color: #666;
            margin-top: 2px;
        }

        /* ── Rodapé página ── */
        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 1px solid #dde3ea;
            padding: 6px 0;
            text-align: center;
            font-size: 7.5pt;
            color: #aaa;
        }
    </style>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') . '?v=' . @filemtime(public_path('images/logo-icon.png')) }}">
</head>
<body>

    {{-- Rodapé da página --}}
    <div class="page-footer">
        TopoGest LTDA cnpj:03.715.655/0001-34 &nbsp;|&nbsp; Contrato {{ $contrato->codigo_contrato }} &nbsp;|&nbsp; Gerado em {{ now()->format('d/m/Y H:i') }}
    </div>

    {{-- Cabeçalho --}}
    <div class="header">
        <div class="header-logo">
            @php $logoPath = public_path('images/logo-completa.png'); @endphp
            @if(file_exists($logoPath))
                <img src="{{ $logoPath }}" alt="TopoGest">
            @else
                <div style="width:70px;height:70px;background:#003366;border-radius:6px;display:flex;align-items:center;justify-content:center;">
                    <span style="color:#fff;font-weight:bold;font-size:9pt;text-align:center;display:block;padding:4px;">GETEC</span>
                </div>
            @endif
        </div>
        <div class="header-info">
            <h1>TopoGest LTDA cnpj:03.715.655/0001-34</h1>
            <p>Serviços Topográficos, Georreferenciamento e Medições</p>
        </div>
        <div class="header-number">
            <span>Número do Contrato</span>
            <strong>{{ $contrato->codigo_contrato }}</strong>
        </div>
    </div>

    {{-- Título --}}
    <div class="titulo">
        <h2>Contrato de Prestação de Serviços Topográficos</h2>
        <div class="titulo-linha"></div>
    </div>

    {{-- Parte 1: Contratante --}}
    <div class="bloco">
        <div class="bloco-header">Parte I — Contratante (Proprietário do Imóvel)</div>
        <div class="bloco-body">
            <div class="campo-linha">
                <span class="campo-label">Nome Completo:</span>
                <span class="campo-valor">{{ strtoupper($contrato->nome_proprietario) }}</span>
            </div>
            <div class="campo-linha">
                <span class="campo-label">CPF:</span>
                <span class="campo-valor">{{ $contrato->cpfFormatado() }}</span>
            </div>
            <div class="campo-linha">
                <span class="campo-label">Imóvel / Área:</span>
                <span class="campo-valor">{{ strtoupper($contrato->nome_imovel) }}</span>
            </div>
            <div class="campo-linha">
                <span class="campo-label">Localização:</span>
                <span class="campo-valor">{{ $contrato->localizacao }}</span>
            </div>
            <div class="campo-linha">
                <span class="campo-label">Município:</span>
                <span class="campo-valor">{{ strtoupper($contrato->municipio) }}</span>
            </div>
            @if($contrato->matricula)
            <div class="campo-linha">
                <span class="campo-label">Matrícula:</span>
                <span class="campo-valor">{{ $contrato->matricula }}</span>
            </div>
            @endif
            @if($contrato->codigo_incra)
            <div class="campo-linha">
                <span class="campo-label">Código INCRA:</span>
                <span class="campo-valor">{{ $contrato->codigo_incra }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Parte 2: Contratado --}}
    <div class="bloco">
        <div class="bloco-header">Parte II — Contratada (TopoGest LTDA)</div>
        <div class="bloco-body">
            <div class="campo-linha">
                <span class="campo-label">Razão Social:</span>
                <span class="campo-valor">TopoGest LTDA</span>
            </div>
            <div class="campo-linha">
                <span class="campo-label">CNPJ:</span>
                <span class="campo-valor">03.715.655/0001-34</span>
            </div>
            <div class="campo-linha">
                <span class="campo-label">Responsável Técnico:</span>
                <span class="campo-valor">{{ strtoupper($contrato->nome_contratado) }}</span>
            </div>
            <div class="campo-linha">
                <span class="campo-label">CPF (Resp. Técnico):</span>
                <span class="campo-valor">{{ $contrato->cpf_cnpj_contratado }}</span>
            </div>
            <div class="campo-linha">
                <span class="campo-label">Endereço:</span>
                <span class="campo-valor">{{ $contrato->endereco_contratado }}</span>
            </div>
        </div>
    </div>

    {{-- Serviço e Valor --}}
    <div class="bloco">
        <div class="bloco-header">Objeto do Contrato e Valor</div>
        <div class="bloco-body">
            <div class="campo-linha">
                <span class="campo-label">Serviço a executar:</span>
                <span class="campo-valor">{{ strtoupper($contrato->tipo_servico) }}</span>
            </div>
            <div style="margin-top: 8px;">
                <div class="valor-box">
                    <span>Valor Total do Serviço</span>
                    <strong>{{ $contrato->valorFormatado() }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Cláusulas --}}
    <div class="clausulas">

        <div class="clausula">
            <div class="clausula-titulo">Cláusula 1ª — Do Objeto</div>
            <p>A CONTRATADA obriga-se a executar o serviço de <strong>{{ strtoupper($contrato->tipo_servico) }}</strong> no imóvel denominado <strong>{{ strtoupper($contrato->nome_imovel) }}</strong>, situado em <strong>{{ $contrato->localizacao }}, {{ strtoupper($contrato->municipio) }}</strong>, de acordo com as normas técnicas vigentes do INCRA, ABNT e legislação aplicável.</p>
        </div>

        <div class="clausula">
            <div class="clausula-titulo">Cláusula 2ª — Do Prazo</div>
            <p>O prazo de execução dos serviços será definido em comum acordo entre as partes, com início após a assinatura deste instrumento e entrega da documentação necessária pelo CONTRATANTE. Eventuais prorrogações deverão ser formalizadas por escrito e devidamente justificadas.</p>
        </div>

        <div class="clausula">
            <div class="clausula-titulo">Cláusula 3ª — Do Preço e Forma de Pagamento</div>
            <p>O CONTRATANTE pagará à CONTRATADA o valor total de <strong>{{ $contrato->valorFormatado() }}</strong>.</p>
            @if($contrato->valor_entrada > 0)
                <p style="margin-top: 4px;">O pagamento inclui uma entrada/sinal no valor de <strong>R$ {{ number_format($contrato->valor_entrada, 2, ',', '.') }}</strong>.</p>
            @endif
            @php
                $restante = $contrato->valor_servico - ($contrato->valor_entrada ?? 0);
                $parcelasText = '';
                if (preg_match('/Parcelado em (\d)x/i', $contrato->forma_pagamento, $matches)) {
                    $qtdParcelas = (int)$matches[1];
                    if ($qtdParcelas > 0) {
                        $valorParcela = $restante / $qtdParcelas;
                        $parcelasText = ' no valor de R$ ' . number_format($valorParcela, 2, ',', '.') . ' cada';
                    }
                }
            @endphp
            <p style="margin-top: 4px;"><strong>Forma de Pagamento:</strong> {{ $contrato->forma_pagamento }}{{ $parcelasText }}</p>
            @if($contrato->detalhes_pagamento)
                <p style="margin-top: 4px;"><strong>Condições:</strong> {{ $contrato->detalhes_pagamento }}</p>
            @endif
            <p style="margin-top: 4px;">O pagamento deverá ser efetuado em conta bancária a ser indicada pela CONTRATADA ou de outra forma acordada entre as partes.</p>
        </div>

        <div class="clausula">
            <div class="clausula-titulo">Cláusula 4ª — Das Obrigações da Contratada</div>
            <p>Cabe à CONTRATADA: (i) executar os serviços com qualidade e dentro dos padrões técnicos normativos; (ii) manter sigilo sobre informações do imóvel; (iii) providenciar equipamentos, mão de obra e ART (Anotação de Responsabilidade Técnica) necessários; (iv) entregar a documentação técnica completa ao final dos trabalhos.</p>
        </div>

        <div class="clausula">
            <div class="clausula-titulo">Cláusula 5ª — Das Obrigações do Contratante</div>
            <p>Cabe ao CONTRATANTE: (i) disponibilizar acesso ao imóvel para realização dos serviços; (ii) fornecer documentação fundiária solicitada pela CONTRATADA; (iii) efetuar os pagamentos nos prazos acordados; (iv) comunicar quaisquer impedimentos de acesso com antecedência mínima de 48 horas.</p>
        </div>

        <div class="clausula">
            <div class="clausula-titulo">Cláusula 6ª — Da Rescisão</div>
            <p>O presente contrato poderá ser rescindido por qualquer das partes mediante comunicação prévia de 15 (quinze) dias, por escrito. Em caso de descumprimento de obrigações, a parte infratora ficará sujeita ao pagamento de multa equivalente a 10% do valor total do contrato, além de eventuais perdas e danos.</p>
        </div>

        <div class="clausula">
            <div class="clausula-titulo">Cláusula 7ª — Do Foro</div>
            <p>As partes elegem o foro da comarca de <strong>{{ strtoupper($contrato->municipio) }}</strong> para dirimir quaisquer controvérsias oriundas deste instrumento, com renúncia expressa a qualquer outro, por mais privilegiado que seja.</p>
        </div>

        <div class="clausula">
            <div class="clausula-titulo">Cláusula 8ª — Da Aceitação</div>
            <p>Por estarem justos e contratados, as partes assinam o presente instrumento em 2 (duas) vias de igual teor e forma, juntamente com 2 (duas) testemunhas.</p>
        </div>

    </div>

    {{-- Local e data --}}
    <p style="margin-top: 20px; text-align:right; font-size:10pt;">
        {{ strtoupper($contrato->municipio) }}, {{ now()->translatedFormat('d \d\e F \d\e Y') }}
    </p>

    {{-- Assinaturas --}}
    <div class="assinaturas">
        <div class="assinatura-col">
            @if($contrato->assinado_em)
                <div class="selo-assinatura">
                    <div class="selo-assinatura-titulo">Assinado Digitalmente</div>
                    <div class="selo-assinatura-nome">{{ $contrato->assinante_nome }}</div>
                    <div class="selo-assinatura-texto">A conformidade pode ser verificada em:</div>
                    <div class="selo-assinatura-link">topogest.com.br/assinador</div>
                    <div class="selo-assinatura-data">{{ $contrato->assinado_em->format('d/m/Y H:i:s') }} | IP: {{ $contrato->assinante_ip }}</div>
                </div>
            @endif
            <div class="assinatura-linha"></div>
            <div class="assinatura-nome">{{ strtoupper($contrato->nome_proprietario) }}</div>
            <div class="assinatura-cargo">CPF: {{ $contrato->cpfFormatado() }}<br>Contratante</div>
        </div>
        <div class="assinatura-col">
            @if($contrato->admin_assinado_em)
                <div class="selo-assinatura">
                    <div class="selo-assinatura-titulo">Assinado Digitalmente</div>
                    <div class="selo-assinatura-nome">{{ $contrato->admin_assinante_nome }}</div>
                    <div class="selo-assinatura-texto">A conformidade pode ser verificada em:</div>
                    <div class="selo-assinatura-link">topogest.com.br/assinador</div>
                    <div class="selo-assinatura-data">{{ $contrato->admin_assinado_em->format('d/m/Y H:i:s') }} | IP: {{ $contrato->admin_assinante_ip }}</div>
                </div>
            @endif
            <div class="assinatura-linha"></div>
            <div class="assinatura-nome">{{ strtoupper($contrato->nome_contratado) }}</div>
            <div class="assinatura-cargo">Responsável Técnico<br>TopoGest LTDA</div>
        </div>
    </div>

</body>
</html>

