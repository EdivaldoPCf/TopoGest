<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pasta;
use App\Models\Arquivo;
use App\Models\Configuracao;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DesktopSyncController extends Controller
{
    /**
     * Recebe um arquivo do Desktop App e processa em tempo real.
     */
    public function syncFile(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file',
                'relative_path' => 'required|string', // Ex: 2026/CAR/Fazenda Sol/documento.pdf
                'action' => 'required|string', // add, change, unlink
                'override_ano' => 'nullable|string',
                'override_categoria' => 'nullable|string'
            ]);

            $relativePath = str_replace('\\', '/', $request->relative_path);
            
            // Força a inserção na hierarquia correta se o app desktop enviou os modificadores estruturais (Nível 1 e 2 ausentes localmente)
            if ($request->filled('override_ano') && $request->filled('override_categoria')) {
                $relativePath = $request->override_ano . '/' . $request->override_categoria . '/' . ltrim($relativePath, '/');
            }

            $action = $request->action;

            // Se for exclusão (unlink)
            if ($action === 'unlink') {
                $arquivo = Arquivo::where('nome', basename($relativePath))->first();
                if ($arquivo) {
                    @Storage::disk('public')->delete($arquivo->path);
                    $arquivo->delete();
                    return response()->json(['message' => 'Arquivo removido com sucesso.']);
                }
                return response()->json(['message' => 'Arquivo não encontrado para remoção.']);
            }

            // Para ADD ou CHANGE
            $parts = explode('/', $relativePath);
            $fileName = array_pop($parts); // O último item é o arquivo

            // Constrói a árvore de pastas no banco de dados (Nível 1, 2, 3...)
            $currentParentId = null;
            $isIgnored = false;

            foreach ($parts as $part) {
                $upperPart = mb_strtoupper($part, 'UTF-8');
                if (in_array($upperPart, ['GNSS', 'RTK', 'BASE', 'ROVER']) || stripos($upperPart, 'METRICA') !== false) {
                    $isIgnored = true;
                }

                $novaPasta = Pasta::firstOrCreate([
                    'nome' => $part,
                    'parent_id' => $currentParentId
                ], [
                    'tipo_servico' => 'pendente',
                    'oculto' => $isIgnored ? 1 : 0
                ]);
                
                $currentParentId = $novaPasta->id;
            }

            // Salva o arquivo fisicamente
            $file = $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension());
            $novoCaminho = 'arquivos/' . $currentParentId . '/' . uniqid() . '_' . $fileName;
            
            Storage::disk('public')->put($novoCaminho, file_get_contents($file));

            // Verifica se já existia para substituir
            $arquivoExistente = Arquivo::where('nome', $fileName)
                                       ->where('pasta_id', $currentParentId)
                                       ->first();

            if ($arquivoExistente) {
                @Storage::disk('public')->delete($arquivoExistente->path);
                $arquivoExistente->delete();
            }

            // Cria o registro no banco
            Arquivo::create([
                'pasta_id' => $currentParentId,
                'nome' => $fileName,
                'path' => $novoCaminho,
                'tamanho' => round($file->getSize() / 1024 / 1024, 2),
                'tipo' => strtoupper($ext),
                'oculto' => $isIgnored ? 1 : 0
            ]);

            // Dispara o robô assíncrono para extrair coordenadas, se for ODS ou PDF
            // Como é MVP, podemos chamar o SyncPendentesCommand passando a pasta ID
            if (in_array($ext, ['ods', 'pdf', 'zip'])) {
                \Illuminate\Support\Facades\Artisan::queue('pastas:sync-pendentes', [
                    '--pasta_id' => $currentParentId
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Arquivo sincronizado e processado.',
                'path' => $relativePath
            ]);

        } catch (\Exception $e) {
            Log::error("Erro no Desktop Sync: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
