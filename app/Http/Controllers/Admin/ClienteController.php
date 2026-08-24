<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Arquivo;
use App\Models\Pasta;
use App\Models\User;
use App\Rules\CpfValido;
use App\Support\FiltroUsuarios;
use Illuminate\Http\Request;

/**
 * Gestão de clientes (listagem, edição de cadastro e visão detalhada).
 */
class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $usuarios = FiltroUsuarios::aplicar(
            User::where('role', 'cliente')->latest()->get(),
            $request->input('search')
        );

        return view('admin.clientes', compact('usuarios'));
    }

    public function atualizar(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'cpf' => ['required', 'string', new CpfValido(), 'unique:users,cpf,' . $id],
            'phone' => 'required|string',
        ]);

        $documento = preg_replace('/[^0-9]/', '', $request->cpf);

        $user->update([
            'name' => ucwords(mb_strtolower($request->name)),
            'email' => $request->email,
            'cpf' => $documento,
            'tipo' => strlen($documento) > 11 ? 'PJ' : 'PF',
            'phone' => preg_replace('/[^0-9]/', '', $request->phone),
        ]);

        return response()->json(['success' => true, 'message' => 'Dados atualizados com sucesso!']);
    }

    public function gestao($id)
    {
        $cliente = User::findOrFail($id);

        $pendentes = Pasta::ownedBy($id)
            ->where('tipo_servico', 'pendente')
            ->whereHas('parent.parent', fn ($query) => $query->whereNull('parent_id'))
            ->get();

        $prontos = Pasta::ownedBy($id)
            ->where('tipo_servico', 'pronto')
            ->whereHas('parent.parent', fn ($query) => $query->whereNull('parent_id'))
            ->get();

        $documentos = Arquivo::whereHas('pasta', fn ($q) => $q->where('cliente_id', $id))
            ->latest()
            ->get();

        return view('admin.clientes.gestao', compact('cliente', 'pendentes', 'prontos', 'documentos'));
    }
}
