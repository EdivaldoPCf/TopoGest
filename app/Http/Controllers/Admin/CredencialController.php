<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Credencial;
use Illuminate\Http\Request;

class CredencialController extends Controller
{
    public function index()
    {
        $credenciais = Credencial::orderBy('codigo')->get();
        return view('admin.credenciais.index', compact('credenciais'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:50|unique:credenciais,codigo',
            'nome' => 'nullable|string|max:255',
            'crea' => 'nullable|string|max:255',
        ]);

        Credencial::create($request->all());

        return redirect()->back()->with('success', 'Credencial cadastrada com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $credencial = Credencial::findOrFail($id);

        $request->validate([
            'codigo' => 'required|string|max:50|unique:credenciais,codigo,' . $credencial->id,
            'nome' => 'nullable|string|max:255',
            'crea' => 'nullable|string|max:255',
        ]);

        $credencial->update($request->all());

        return redirect()->back()->with('success', 'Credencial atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $credencial = Credencial::findOrFail($id);
        $credencial->delete();

        return redirect()->back()->with('success', 'Credencial excluída com sucesso!');
    }
}
