<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pasta;
use Illuminate\Support\Facades\Auth;

class ServicoController extends Controller
{
    /**
     * Lista os serviços na Dashboard do Cliente
     */
public function meusServicos()
{
    $servicos = \App\Models\Pasta::where('cliente_id', auth()->id())
        ->whereHas('parent.parent', function($query) {
            $query->whereNull('parent_id'); 
        })->get();

    // Mudamos de 'client.index' para 'client.servicos'
    return view('client.servicos', compact('servicos'));
}

    /**
     * Exibe os detalhes de um serviço/pasta específico para o cliente
     */
    public function show($id)
{
    // Carrega a pasta com subpastas, arquivos e pendências para o cliente
    $pasta = Pasta::with(['subpastas', 'arquivos', 'pendencias'])->findOrFail($id);
    
    // Verificação de segurança (opcional): garante que o cliente só veja as próprias pastas
    if (auth()->user()->role !== 'admin' && $pasta->cliente_id !== auth()->id()) {
        // Se a pasta não for dele, tenta ver se a pasta pai é dele
        if (!$pasta->parent || $pasta->parent->cliente_id !== auth()->id()) {
            abort(403, 'Acesso negado.');
        }
    }

    return response()
        ->view('client.show', compact('pasta'))
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache');
}
    /**
     * Finaliza o status de um serviço (Ação do Admin)
     */
    public function finalizar($id)
    {
        $servico = Pasta::findOrFail($id);
        $servico->update(['tipo_servico' => 'finalizado']);

        return redirect()->back()->with('success', 'Serviço finalizado com sucesso!');
    }

    /**
     * Exclui um serviço (Ação do Admin)
     */
    public function destroy($id)
    {
        $servico = Pasta::findOrFail($id);
        $servico->delete();

        return redirect()->route('dashboard')->with('success', 'Serviço excluído permanentemente.');
    }

    /**
     * Método store (Corrigido o erro de sintaxe na linha 75)
     */
    public function store(Request $request)
    {
        Pasta::create([
            'nome' => $request->nome,
            'parent_id' => $request->parent_id,
            'tipo_servico' => 'pendente',
            'cliente_id' => $request->cliente_id,
            'identificador_cliente' => $request->identificador_cliente,
            'categoria_servico' => $request->categoria_servico,
        ]);

        return redirect()->back()->with('success', 'Serviço cadastrado com sucesso!');
    }
}