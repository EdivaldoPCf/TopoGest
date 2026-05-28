<?php

namespace App\Http\Controllers;

use App\Models\Marco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarcoController extends Controller
{
    public function index(Request $request)
    {
        $credencial = $request->get('credencial', 'BCA');
        $tipo = $request->get('tipo', 'M');
        $search = $request->get('search');

        $query = Marco::where('credencial', $credencial)->where('tipo', $tipo);

        if ($search) {
            // Extrai apenas o número se o usuário digitar "BCA-M-250" ou "250"
            $numericSearch = preg_replace('/[^0-9]/', '', $search);

            $query->where(function ($q) use ($search, $numericSearch) {
                $q->where('imovel', 'LIKE', "%{$search}%")
                  ->orWhere('numero', $numericSearch);
            });
        }

        $marcos = $query->with('user')
            ->orderBy('numero', 'desc')
            ->paginate(20)
            ->withQueryString();
        $ultimo = Marco::where('credencial', $credencial)
            ->where('tipo', $tipo)
            ->orderByRaw('CAST(numero AS UNSIGNED) DESC')
            ->first();

        $totalBca = Marco::where('credencial', 'BCA')->count();
        $totalEmes = Marco::where('credencial', 'EMES')->count();

        return view('admin.marcos', compact('marcos', 'ultimo', 'credencial', 'tipo', 'totalBca', 'totalEmes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'numero_marcos' => ['required', 'regex:/^[0-9]{1,7}$/'],
            'numero_marcos_fim' => ['nullable', 'regex:/^[0-9]{1,7}$/'],
            'imovel' => 'required|string',
            'credencial' => 'required',
            'tipo' => 'required'
        ], [
            'numero_marcos.regex' => 'Digite apenas os dígitos do marco inicial (até 7 caracteres).',
            'numero_marcos_fim.regex' => 'Digite apenas os dígitos do marco final (até 7 caracteres).',
        ]);

        $credencial = $request->credencial;
        $tipo = $request->tipo;
        $numeroInicio = (int) $request->numero_marcos;
        $numeroFim = $request->numero_marcos_fim ? (int) $request->numero_marcos_fim : null;

        if ($numeroFim !== null) {
            if ($numeroFim < $numeroInicio) {
                return back()->withErrors(['numero_marcos_fim' => 'O número final não pode ser menor que o inicial.'])->withInput();
            }
            if (($numeroFim - $numeroInicio) > 500) {
                return back()->withErrors(['numero_marcos_fim' => 'O limite máximo para cadastro em lote é de 500 marcos por vez.'])->withInput();
            }

            $duplicates = [];
            $savedCount = 0;

            for ($num = $numeroInicio; $num <= $numeroFim; $num++) {
                $resultado = $this->saveMarco($credencial, $tipo, $num, $request->imovel);
                if ($resultado['status'] === 'duplicate') {
                    $duplicates[] = "{$credencial}-{$tipo}-" . sprintf('%04d', $num);
                } else {
                    $savedCount++;
                }
            }

            if (count($duplicates) > 0) {
                if ($savedCount > 0) {
                    return back()->with('success', "Cadastro de {$savedCount} marcos realizado com sucesso! " . count($duplicates) . " marcos já estavam cadastrados e foram pulados: " . implode(', ', $duplicates));
                } else {
                    return back()->withErrors(['numero_marcos' => 'Todos os marcos no intervalo informado já estavam cadastrados.'])->withInput();
                }
            }

            return back()->with('success', "Cadastro de {$savedCount} marcos realizado com sucesso!");
        } else {
            // Processa um único número
            $resultado = $this->saveMarco($credencial, $tipo, $numeroInicio, $request->imovel);
            if ($resultado['status'] === 'duplicate') {
                return back()->with('error_duplicate', $resultado);
            }
            return back()->with('success', 'Marco cadastrado com sucesso!');
        }
    }

    public function destroy($id)
    {
        $marco = Marco::findOrFail($id);
        $marco->delete();

        return back()->with('success', 'Marco excluído com sucesso!');
    }

    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'Nenhum marco selecionado para exclusão.');
        }

        Marco::whereIn('id', $ids)->delete();

        return back()->with('success', 'Marcos selecionados excluídos com sucesso!');
    }

    private function saveMarco($cred, $tipo, $num, $imovel)
{
    // 2. VALIDAÇÃO DE DUPLICIDADE
    $existente = \App\Models\Marco::where('credencial', $cred)
        ->where('tipo', $tipo)
        ->where('numero', $num)
        ->first();

    if ($existente) {
        return [
            'status' => 'duplicate',
            'marco' => "{$cred}-{$tipo}-" . sprintf('%04d', $num),
            'imovel' => $existente->imovel,
            'data' => $existente->created_at->format('d/m/Y')
        ];
    }

    \App\Models\Marco::create([
        'credencial' => $cred,
        'tipo' => $tipo,
        'numero' => $num,
        'user_id' => auth()->id(),
        'imovel' => $imovel
    ]);

    return ['status' => 'success'];
}

    public function mapa(Request $request, $imovel)
    {
        $imovelDecoded = urldecode($imovel);
        $highlightId = $request->query('highlight');
        
        // Fetch all marcos for this imovel that have coordinates
        $marcos = Marco::where('imovel', $imovelDecoded)
            ->where(function ($q) {
                $q->whereNotNull('latitude')->orWhereNotNull('easting');
            })
            ->get();

        if ($marcos->isEmpty()) {
            return back()->with('error', 'Nenhum marco com coordenada encontrada para este imóvel.');
        }

        return view('admin.marcos_mapa', compact('marcos', 'imovelDecoded', 'highlightId'));
    }
}