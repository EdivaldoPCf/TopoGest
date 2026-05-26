<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificacaoController extends Controller
{
    public function index()
    {
        $notificacoes = auth()->user()->notifications->map(function($n) {
            return [
                'id' => $n->id,
                'titulo' => $n->data['titulo'] ?? ($n->data['mensagem'] ?? 'Notificação'),
                'descricao' => $n->data['descricao'] ?? ($n->data['mensagem'] ?? ''),
                'mensagem' => $n->data['descricao'] ?? ($n->data['mensagem'] ?? ''),
                'link' => $n->data['link'] ?? null,
                'type' => str_contains($n->type, 'SolicitacaoExclusao') ? 'solicitacao_exclusao' : 'padrao',
                'lida' => $n->read_at !== null,
                'data_formatada' => $n->created_at->format('d/m/Y H:i'),
            ];
        });

        return view('notificacoes.index', compact('notificacoes'));
    }

    public function marcarComoLida($id)
    {
        $notificacao = auth()->user()->notifications()->find($id);
        if ($notificacao) {
            $notificacao->markAsRead();
        }
        return response()->json(['success' => true]);
    }
}