<?php

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\Marco;
use App\Models\Pasta;
use App\Models\User;

/**
 * Dashboard principal do administrador.
 *
 * As demais responsabilidades que antes viviam aqui foram separadas em:
 * Admin\ClienteController, Admin\PermissaoController, Admin\BaseController,
 * Admin\ConteudoController e Admin\ExclusaoPastaController.
 */
class AdminController extends Controller
{
    public function index()
    {
        $temPendentes = User::where('role', 'admin')->where('approved', false)->exists();

        $totalClientes = User::where('role', 'cliente')->count();

        $totalServicosPendentes = Pasta::where('tipo_servico', 'pendente')
            ->whereHas('parent', function ($q) {
                $q->whereHas('parent', fn ($q2) => $q2->whereNull('parent_id'))
                    ->whereNotNull('parent_id');
            })
            ->count();

        $totalBases = Base::count();
        $totalBca = Marco::where('credencial', 'BCA')->count();
        $totalEmes = Marco::where('credencial', 'EMES')->count();

        return view('admin.dashboard', compact('temPendentes', 'totalClientes', 'totalServicosPendentes', 'totalBases', 'totalBca', 'totalEmes'));
    }
}
