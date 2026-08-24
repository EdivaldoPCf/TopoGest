<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pasta;
use App\Models\User;
use App\Notifications\SolicitacaoExclusaoPasta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Dupla autorização para exclusão de pastas: um admin solicita e outro aprova/recusa.
 */
class ExclusaoPastaController extends Controller
{
    public function solicitar($id)
    {
        $pasta = Pasta::findOrFail($id);

        $admins = User::where('role', 'admin')
            ->where('id', '!=', auth()->id())
            ->get();

        Notification::send($admins, new SolicitacaoExclusaoPasta($pasta));

        return response()->json([
            'success' => true,
            'message' => 'Solicitação enviada! A exclusão aguarda a aprovação de outro administrador.',
        ]);
    }

    public function processar(Request $request)
    {
        $request->validate(['notificacao_id' => 'required', 'status' => 'required|in:aprovar,recusar']);

        $notificacao = auth()->user()->notifications()->findOrFail($request->notificacao_id);
        $dados = $notificacao->data;

        if ($request->status === 'aprovar') {
            $pasta = Pasta::find($dados['pasta_id']);

            if ($pasta) {
                $pasta->delete();
                $mensagem = "A pasta '{$dados['pasta_nome']}' foi excluída.";
                DB::table('notifications')->where('data', 'like', '%"pasta_id":' . $dados['pasta_id'] . '%')->delete();
            } else {
                $mensagem = 'Pasta já excluída.';
                $notificacao->delete();
            }
        } else {
            $mensagem = 'Exclusão recusada.';
            $notificacao->markAsRead();
            $notificacao->delete();
        }

        return response()->json(['success' => true, 'message' => $mensagem]);
    }
}
