<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\StatusPermissaoNotification;
use App\Services\WhatsappService;
use App\Support\FiltroUsuarios;
use Illuminate\Http\Request;

/**
 * Gestão de permissões de administrador (solicitações pendentes e aprovação/remoção).
 */
class PermissaoController extends Controller
{
    public function __construct(private WhatsappService $whatsapp)
    {
    }

    public function pendentes(Request $request)
    {
        $solicitacoesRaw = User::where('role', 'admin')->where('approved', 0)->latest()->get();

        $administradoresRaw = User::where(fn ($query) => $query->where('role', 'admin')->where('approved', 1))
            ->orWhere('id', auth()->id())
            ->latest()
            ->get();

        $solicitacoes = FiltroUsuarios::aplicar($solicitacoesRaw, $request->input('search_pendentes'));
        $administradores = FiltroUsuarios::aplicar($administradoresRaw, $request->input('search_admins'));

        return view('admin.pendentes', compact('solicitacoes', 'administradores'));
    }

    public function gestaoPermissoes(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $action = $request->input('action');
        $adminAtual = auth()->user()->name;
        $linkWpp = null;
        $mensagem = '';

        if ($action === 'confirmar') {
            $user->update(['role' => 'admin', 'approved' => 1]);
            $user->notify(new StatusPermissaoNotification('aprovado', $adminAtual));
            $linkWpp = $this->notificarWhatsapp($user, "Olá {$user->name}, sua solicitação para Administrador no TopoGest foi APROVADA por {$adminAtual}.");
            $mensagem = 'Privilégio concedido.';
        } elseif ($action === 'negar') {
            $user->update(['role' => 'cliente', 'approved' => 1]);
            $user->notify(new StatusPermissaoNotification('reprovada', $adminAtual));
            $linkWpp = $this->notificarWhatsapp($user, "Olá {$user->name}, sua solicitação para Administrador no TopoGest foi RECUSADA por {$adminAtual}.");
            $mensagem = 'Solicitação negada.';
        } elseif ($action === 'remover') {
            $user->update(['role' => 'cliente', 'approved' => 1]);
            $mensagem = 'Privilégio removido.';
        }

        return response()->json(['success' => true, 'message' => $mensagem, 'whatsapp_url' => $linkWpp]);
    }

    /**
     * Envia a mensagem pela API (se habilitada) ou devolve um link wa.me como fallback.
     */
    private function notificarWhatsapp(User $user, string $mensagem): ?string
    {
        if ($this->whatsapp->enabled()) {
            $this->whatsapp->send($user->phone, $mensagem);

            return null;
        }

        return $user->phone ? $this->whatsapp->createLink($user->phone, $mensagem) : null;
    }
}
