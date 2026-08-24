<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Services\GeradorPdfContrato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContratoController extends Controller
{
    public function __construct(private GeradorPdfContrato $pdf)
    {
    }

    // ─── Tela principal Acerto ────────────────────────────────────────────────
    public function index()
    {
        $emFila     = Contrato::with('recibos')->where('status', 'fila')->latest()->get();
        $vinculados = Contrato::with('recibos')->where('status', 'vinculado')->latest()->paginate(15);

        return view('admin.acerto.index', compact('emFila', 'vinculados'));
    }

    // ─── Gerar novo contrato ──────────────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'nome_proprietario'    => 'required|string|max:255',
            'cpf_proprietario'     => 'required|string|max:14',
            'nome_imovel'          => 'required|string|max:255',
            'localizacao'          => 'required|string|max:500',
            'municipio'            => 'required|string|max:255',
            'codigo_incra'         => 'nullable|string|max:100',
            'matricula'            => 'nullable|string|max:100',
            'tipo_servico'         => 'required|array|min:1',
            'valor_servico'        => 'required|numeric|min:0',
            'valor_entrada'        => 'nullable|numeric|min:0',
            'forma_pagamento'      => 'required|string|max:50',
            'detalhes_pagamento'   => 'nullable|string',
            'nome_contratado'      => 'required|string|max:255',
            'cpf_cnpj_contratado'  => 'required|string|max:20',
            'endereco_contratado'  => 'required|string|max:500',
        ]);

        // Limpa CPF
        $data['cpf_proprietario'] = preg_replace('/[^0-9]/', '', $data['cpf_proprietario']);

        // Trata os tipos de serviço múltiplos
        if (is_array($data['tipo_servico'])) {
            $servicos = $data['tipo_servico'];
            if (count($servicos) > 1) {
                $ultimo = array_pop($servicos);
                $data['tipo_servico'] = implode(', ', $servicos) . ' e ' . $ultimo;
            } else {
                $data['tipo_servico'] = $servicos[0];
            }
        }

        // Cria o registro
        $contrato = Contrato::create($data);

        // Tenta vincular à pasta de nível 3
        $contrato->tentarVincular();

        // Gera o PDF
        $pdfPath = $this->pdf->gerarContrato($contrato);
        $contrato->update(['pdf_path' => $pdfPath]);

        return redirect()
            ->route('admin.acerto.index')
            ->with('success', 'Contrato gerado com sucesso! ' .
                ($contrato->status === 'vinculado'
                    ? 'Vinculado ao cliente.'
                    : 'Aguardando cadastro do proprietário no sistema.'));
    }

    // ─── Download do PDF ──────────────────────────────────────────────────────
    public function download($id)
    {
        $contrato = Contrato::findOrFail($id);

        if (!$contrato->pdf_path || !Storage::disk('public')->exists($contrato->pdf_path)) {
            // Regenera se perdeu o arquivo
            $pdfPath = $this->pdf->gerarContrato($contrato);
            $contrato->update(['pdf_path' => $pdfPath]);
        }

        return response()->download(
            storage_path('app/public/' . $contrato->pdf_path),
            'Contrato_' . Str::slug($contrato->nome_imovel) . '.pdf'
        );
    }

    // ─── Visualizar PDF no Navegador ──────────────────────────────────────────
    public function visualizar($id)
    {
        $contrato = Contrato::findOrFail($id);

        if (!$contrato->pdf_path || !Storage::disk('public')->exists($contrato->pdf_path)) {
            $pdfPath = $this->pdf->gerarContrato($contrato);
            $contrato->update(['pdf_path' => $pdfPath]);
        }

        return response()->file(storage_path('app/public/' . $contrato->pdf_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Contrato_' . Str::slug($contrato->nome_imovel) . '.pdf"'
        ]);
    }

    // ─── Assinatura digital simplificada ─────────────────────────────────────
    public function assinar(Request $request, $id)
    {
        $contrato = Contrato::findOrFail($id);

        if ($contrato->admin_assinado_em) {
            return back()->with('error', 'Este contrato já foi assinado pela Getec.');
        }

        $request->validate([
            'assinante_nome' => 'required|string|max:255',
        ]);

        $ip   = $request->ip();
        $hash = hash('sha256', 'admin_' . $contrato->id . $request->assinante_nome . $ip . now()->timestamp);

        $contrato->update([
            'admin_assinado_em'    => now(),
            'admin_assinatura_hash' => $hash,
            'admin_assinante_nome' => $request->assinante_nome,
            'admin_assinante_ip'   => $ip,
        ]);

        // Regenera PDF com rodapé de assinatura
        $pdfPath = $this->pdf->gerarContrato($contrato);
        $contrato->update(['pdf_path' => $pdfPath]);

        return back()->with('success', 'Contrato assinado digitalmente com sucesso!');
    }

    // ─── Verificar autenticidade ──────────────────────────────────────────────
    public function verificar($hash)
    {
        $contrato = Contrato::where('assinatura_hash', $hash)->first();

        return view('admin.acerto.verificar', compact('contrato'));
    }

    // ─── Excluir contrato ─────────────────────────────────────────────────────
    public function destroy($id)
    {
        $contrato = Contrato::findOrFail($id);

        if ($contrato->pdf_path && Storage::disk('public')->exists($contrato->pdf_path)) {
            Storage::disk('public')->delete($contrato->pdf_path);
        }

        $contrato->delete();

        return back()->with('success', 'Contrato excluído com sucesso.');
    }

    // ─── Gerar Recibo ─────────────────────────────────────────────────────────
    public function gerarRecibo(Request $request, $id)
    {
        $contrato = Contrato::findOrFail($id);

        $request->validate([
            'valor_total' => 'required|string',
            'valor_recebido' => 'required|string',
            'descricao_pagamento' => 'required|string|max:255',
        ]);

        $hash = hash('sha256', $contrato->id . 'recibo' . $request->ip() . now()->timestamp);

        $resultado = $this->pdf->gerarRecibo(
            $contrato,
            $request->valor_total,
            $request->valor_recebido,
            $request->descricao_pagamento,
            $hash,
            'recibo'
        );

        $contrato->recibos()->create([
            'valor_total' => (float) str_replace(',', '.', str_replace('.', '', $request->valor_total)),
            'valor_recebido' => (float) str_replace(',', '.', str_replace('.', '', $request->valor_recebido)),
            'descricao' => $request->descricao_pagamento,
            'hash' => $hash,
            'pdf_path' => $resultado['path'],
        ]);

        return $resultado['pdf']->stream('Recibo_' . Str::slug($contrato->nome_imovel) . '_' . time() . '.pdf', ['Attachment' => false]);
    }

    // ─── Gerar Recibo de Entrada Automático ───────────────────────────────────
    public function gerarReciboEntrada(Request $request, $id)
    {
        $contrato = Contrato::findOrFail($id);

        if (!$contrato->valor_entrada || $contrato->valor_entrada <= 0) {
            return back()->with('error', 'Este contrato não possui valor de entrada cadastrado.');
        }

        $hash = hash('sha256', $contrato->id . 'recibo_entrada' . $request->ip() . now()->timestamp);
        $descricao = 'Sinal / Entrada referente ao contrato.';

        $resultado = $this->pdf->gerarRecibo(
            $contrato,
            number_format($contrato->valor_servico, 2, ',', '.'),
            number_format($contrato->valor_entrada, 2, ',', '.'),
            $descricao,
            $hash,
            'recibo_entrada'
        );

        $contrato->recibos()->create([
            'valor_total' => $contrato->valor_servico,
            'valor_recebido' => $contrato->valor_entrada,
            'descricao' => $descricao,
            'hash' => $hash,
            'pdf_path' => $resultado['path'],
        ]);

        return $resultado['pdf']->stream('Recibo_Entrada_' . Str::slug($contrato->nome_imovel) . '_' . time() . '.pdf', ['Attachment' => false]);
    }

    // ─── Visualizar Recibo ──────────────────────────────────────────────────
    public function visualizarRecibo($id)
    {
        $recibo = \App\Models\Recibo::findOrFail($id);

        if (!$recibo->pdf_path || !Storage::disk('public')->exists($recibo->pdf_path)) {
            return back()->with('error', 'Arquivo do recibo não encontrado.');
        }

        return response()->file(storage_path('app/public/' . $recibo->pdf_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Recibo_' . $id . '.pdf"'
        ]);
    }

    // ─── Excluir Recibo ─────────────────────────────────────────────────────
    public function excluirRecibo($id)
    {
        $recibo = \App\Models\Recibo::findOrFail($id);

        if ($recibo->pdf_path && Storage::disk('public')->exists($recibo->pdf_path)) {
            Storage::disk('public')->delete($recibo->pdf_path);
        }

        $recibo->delete();

        return back()->with('success', 'Recibo excluído com sucesso.');
    }

    // ─── Busca de cliente por CPF ─────────────────────────────────────────────
    public function buscaCpf(Request $request)
    {
        $cpf = preg_replace('/[^0-9]/', '', $request->input('cpf'));
        if (strlen($cpf) !== 11) {
            return response()->json(['encontrado' => false]);
        }

        $cliente = \App\Models\User::where('cpf', $cpf)->first();

        if ($cliente) {
            return response()->json([
                'encontrado' => true,
                'nome' => $cliente->name
            ]);
        }

        return response()->json(['encontrado' => false]);
    }
}
