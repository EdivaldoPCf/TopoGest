<?php

namespace App\Services;

use App\Models\Contrato;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Support\Facades\Storage;

/**
 * Geração e armazenamento dos PDFs de contrato e recibo (Acerto).
 *
 * Centraliza o Pdf::loadView + criação de diretório + gravação no disco que antes
 * estava repetido em ContratoController (gerarPdf, gerarRecibo, gerarReciboEntrada).
 */
class GeradorPdfContrato
{
    /** Gera o PDF do contrato e devolve o caminho relativo no disco público. */
    public function gerarContrato(Contrato $contrato): string
    {
        $pdf = Pdf::loadView('pdf.contrato', ['contrato' => $contrato])->setPaper('a4', 'portrait');

        return $this->salvar($pdf, 'contratos', 'contrato_' . $contrato->id);
    }

    /**
     * Gera o PDF de um recibo, salva no disco e devolve o objeto PDF (para stream) e o caminho.
     *
     * @return array{pdf: DomPdf, path: string}
     */
    public function gerarRecibo(
        Contrato $contrato,
        string $valorTotalExibicao,
        string $valorRecebidoExibicao,
        string $descricao,
        string $hash,
        string $prefixoArquivo
    ): array {
        $pdf = Pdf::loadView('pdf.recibo', [
            'contrato' => $contrato,
            'valor_total' => $valorTotalExibicao,
            'valor_recebido' => $valorRecebidoExibicao,
            'descricao_pagamento' => $descricao,
            'hash' => $hash,
        ])->setPaper('a4', 'portrait');

        $path = $this->salvar($pdf, 'recibos', $prefixoArquivo . '_' . $contrato->id);

        return ['pdf' => $pdf, 'path' => $path];
    }

    /** Grava o PDF em <dir>/<prefixo>_<timestamp>.pdf no disco público e retorna o caminho. */
    private function salvar(DomPdf $pdf, string $dir, string $prefixo): string
    {
        if (! Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }

        $path = $dir . '/' . $prefixo . '_' . time() . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }
}
