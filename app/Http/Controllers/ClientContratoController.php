<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Contrato;
use App\Services\GeradorPdfContrato;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClientContratoController extends Controller
{
    public function __construct(private GeradorPdfContrato $pdf)
    {
    }

    public function index()
    {
        $user = Auth::user();
        
        // Carrega os contratos vinculados ao cliente
        $contratos = $user->contratos()->with('recibos')->latest()->get();

        return view('client.contratos', compact('contratos'));
    }

    public function download($id)
    {
        $contrato = Contrato::where('id', $id)
            ->where('cliente_id', Auth::id())
            ->firstOrFail();

        if (!$contrato->pdf_path || !Storage::disk('public')->exists($contrato->pdf_path)) {
            return back()->with('error', 'O arquivo PDF não foi encontrado.');
        }

        return response()->download(
            storage_path('app/public/' . $contrato->pdf_path),
            'Contrato_' . Str::slug($contrato->nome_imovel) . '.pdf'
        );
    }

    public function assinar(Request $request, $id)
    {
        $contrato = Contrato::where('id', $id)
            ->where('cliente_id', Auth::id())
            ->firstOrFail();

        if ($contrato->assinado_em) {
            return back()->with('error', 'Este contrato já foi assinado.');
        }

        $ip = $request->ip();
        $assinanteNome = Auth::user()->name;
        $hash = hash('sha256', $contrato->id . $assinanteNome . $ip . now()->timestamp);

        $contrato->update([
            'assinado_em' => now(),
            'assinatura_hash' => $hash,
            'assinante_nome' => $assinanteNome,
            'assinante_ip' => $ip,
        ]);

        $contrato->update(['pdf_path' => $this->pdf->gerarContrato($contrato)]);

        return back()->with('success', 'Contrato assinado digitalmente com sucesso!');
    }

    public function visualizarRecibo($id)
    {
        $recibo = \App\Models\Recibo::where('id', $id)
            ->whereHas('contrato', function ($q) {
                $q->where('cliente_id', Auth::id());
            })
            ->firstOrFail();

        if (!$recibo->pdf_path || !Storage::disk('public')->exists($recibo->pdf_path)) {
            return back()->with('error', 'Arquivo do recibo não encontrado.');
        }

        return response()->file(storage_path('app/public/' . $recibo->pdf_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Recibo_' . $id . '.pdf"'
        ]);
    }
}
