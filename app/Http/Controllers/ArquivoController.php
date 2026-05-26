<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class ArquivoController extends Controller
{
    public function store(Request $request, $pastaId)
    {
        $request->validate([
            'arquivo' => 'required|file|max:10240', // Limite de 10MB
        ]);

        $file = $request->file('arquivo');
        $path = $file->store('arquivos', 'public');

        $data = [
            'tipo' => $file->getClientOriginalExtension(),
            'pasta_id' => $pastaId,
        ];

        if (Schema::hasColumn('arquivos', 'nome')) {
            $data['nome'] = $file->getClientOriginalName();
        }

        if (Schema::hasColumn('arquivos', 'nome_original')) {
            $data['nome_original'] = $file->getClientOriginalName();
        }

        if (Schema::hasColumn('arquivos', 'path')) {
            $data['path'] = $path;
        }

        if (Schema::hasColumn('arquivos', 'caminho')) {
            $data['caminho'] = $path;
        }

        if (Schema::hasColumn('arquivos', 'tamanho')) {
            $data['tamanho'] = round($file->getSize() / 1024 / 1024, 2);
        }

        Arquivo::create($data);

        return redirect()->back()->with('success', 'Arquivo enviado!');
    }

    public function destroy($id)
    {
        $arquivo = Arquivo::findOrFail($id);

        $filePath = $arquivo->path ?? $arquivo->caminho;
        Storage::disk('public')->delete($filePath);
        $arquivo->delete();

        return redirect()->back()->with('success', 'Arquivo excluído!');
    }

    public function download($id)
    {
        $arquivo = Arquivo::findOrFail($id);

        $filePath = $arquivo->path ?? $arquivo->caminho;
        $fileName = $arquivo->nome ?? $arquivo->nome_original ?? basename($filePath);

        if (!$filePath || !Storage::disk('public')->exists($filePath)) {
            return redirect()->back()->with('error', 'Arquivo não encontrado para download.');
        }

        $currentExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        if (empty($currentExtension)) {
            $pathExtension = pathinfo($filePath, PATHINFO_EXTENSION);
            if (!empty($pathExtension)) {
                $fileName .= '.' . $pathExtension;
            }
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'acao' => 'download',
            'detalhes' => [
                'arquivo_nome' => $fileName,
                'arquivo_id' => $arquivo->id,
                'imovel_nome' => $arquivo->pasta->nome ?? null,
            ],
            'ip_address' => request()->ip(),
        ]);

        return Storage::disk('public')->download($filePath, $fileName);
    }
    // ... outros métodos acima ...

    public function buscarBase(\Illuminate\Http\Request $request)
    {
        $norte = $request->input('norte');
        $este = $request->input('este');

        // Busca todas as bases e ordena pela menor distância matemática
        // Cálculo: raiz quadrada de ((N1-N2)² + (E1-E2)²)
        $bases = \App\Models\Base::select('*')
            ->selectRaw("SQRT(POW(norte - ?, 2) + POW(este - ?, 2)) AS distancia", [$norte, $este])
            ->orderBy('distancia', 'asc')
            ->get();

        // Retorna a mesma view das bases, mas agora com a lista filtrada/ordenada
        return view('admin.bases.index', compact('bases'))->with('destaque', $bases->first()->id ?? null);
    }
} // Final da classe
