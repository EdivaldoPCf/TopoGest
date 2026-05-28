<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Configuracao;
use App\Models\Pasta;
use App\Models\Arquivo;
use App\Models\Marco;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

class SyncPendentesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pastas:sync-pendentes {--pasta_id= : ID da pasta especifica para sincronizar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza automaticamente as pastas pendentes buscando arquivos novos no computador';

    protected $ignoredExtensions = ['topo', 'tbkp', 'dwl', 'dwl2', 'bak'];
    protected $ignoredFiles = ['thumbs.db'];
    protected $ignoredFolders = ['GNSS', 'RTK', 'BASE', 'ROVER', 'ARQUIVO METRICA', 'ARQUIVO MÉRICA'];
    protected $tecnicoCpf = '016.326.682-14';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $pastaId = $this->option('pasta_id');
        $cacheKey = $pastaId ? 'sync_progress_' . $pastaId : 'sync_progress_global';

        try {
            $ativo = Configuracao::where('chave', 'sync_ativo')->first()?->valor;
            $rootDir = Configuracao::where('chave', 'sync_root_dir')->first()?->valor;

            if (!$pastaId && $ativo !== '1') {
                $this->info("Sincronização desativada nas configurações.");
                return;
            }

            if (empty($rootDir) || !is_dir($rootDir)) {
                $this->error("Caminho raiz inválido ou não configurado: {$rootDir}");
                \Illuminate\Support\Facades\Cache::put($cacheKey, [
                    'current' => 0,
                    'total' => 0,
                    'status' => 'error',
                    'percentage' => 0,
                    'last_file' => "Caminho raiz inválido ou não configurado: {$rootDir}"
                ], 300);
                return;
            }

            $this->info("Iniciando sincronização...");

            // Busca pastas Nível 3
            $query = Pasta::whereHas('parent', function($q) {
                              $q->whereHas('parent', function($q2) {
                                  $q2->whereNull('parent_id');
                              });
                          });
                          
            if ($pastaId) {
                // Se for pasta específica, permite sincronizar mesmo se estiver "pronto" ou "finalizada"
                $query->where('id', $pastaId);
            } else {
                // Se for sincronização geral, pega apenas as pendentes
                $query->where('tipo_servico', 'pendente');
            }

            $imoveis = $query->get();
            $totalFiles = 0;
            $caminhos = [];

            foreach ($imoveis as $imovel) {
                $categoria = $imovel->parent->nome;
                $ano = $imovel->parent->parent->nome;
                $caminhoFisico = $rootDir . DIRECTORY_SEPARATOR . $ano . DIRECTORY_SEPARATOR . $categoria . DIRECTORY_SEPARATOR . $imovel->nome;

                if (is_dir($caminhoFisico)) {
                    $caminhos[] = ['imovel' => $imovel, 'caminho' => $caminhoFisico];
                    
                    $iterator = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($caminhoFisico, FilesystemIterator::SKIP_DOTS),
                        RecursiveIteratorIterator::SELF_FIRST
                    );
                    foreach ($iterator as $file) {
                        if ($file->isFile()) {
                            $ext = strtolower($file->getExtension());
                            $basename = strtolower($file->getBasename());

                            if (in_array($ext, $this->ignoredExtensions) || in_array($basename, $this->ignoredFiles) || str_starts_with($file->getFilename(), '~$') || $basename === 'thumbs.db' || str_contains($basename, 'topo.zip')) {
                                continue;
                            }

                            // Normalize path separators to forward slash
                            $normalizedPath = str_replace('\\', '/', $file->getPathname());
                            $normalizedCaminhoFisico = str_replace('\\', '/', $caminhoFisico);
                            $relPath = trim(str_replace($normalizedCaminhoFisico, '', $normalizedPath), '/');

                            $inIgnoredFolder = false;
                            if ($relPath !== '') {
                                $parts = explode('/', $relPath);
                                foreach ($parts as $part) {
                                    $upperPart = mb_strtoupper($part, 'UTF-8');
                                    if (in_array($upperPart, ['GNSS', 'RTK', 'BASE', 'ROVER']) || stripos($upperPart, 'METRICA') !== false || stripos($upperPart, 'MÉTRICA') !== false) {
                                        $inIgnoredFolder = true;
                                        break;
                                    }
                                }
                            }
                            if ($inIgnoredFolder) continue;

                            $totalFiles++;
                        }
                    }
                }
            }

            \Illuminate\Support\Facades\Cache::put($cacheKey, [
                'current' => 0,
                'total' => $totalFiles,
                'status' => 'running',
                'percentage' => 0,
                'last_file' => ''
            ], 300);

            $syncedFiles = 0;
            $currentProgress = 0;

            foreach ($caminhos as $item) {
                $imovel = $item['imovel'];
                $caminhoFisico = $item['caminho'];

                $this->info("Sincronizando: {$imovel->nome}");
                $syncedFiles += $this->syncFolderContent($caminhoFisico, $imovel, $cacheKey, $currentProgress, $totalFiles);
            }

            \Illuminate\Support\Facades\Cache::put($cacheKey, [
                'current' => $totalFiles,
                'total' => $totalFiles,
                'status' => 'done',
                'percentage' => 100,
                'last_file' => 'Finalizado'
            ], 300);

            $this->info("Sincronização concluída. Novos arquivos: {$syncedFiles}");
        } catch (\Throwable $e) {
            Log::error("Erro na execução do comando pastas:sync-pendentes: " . $e->getMessage());
            \Illuminate\Support\Facades\Cache::put($cacheKey, [
                'current' => 0,
                'total' => 0,
                'status' => 'error',
                'percentage' => 0,
                'last_file' => 'Erro: ' . $e->getMessage()
            ], 300);
        }
    }

    private function syncFolderContent($caminhoCompleto, $pastaImovel, $cacheKey = null, &$currentProgress = 0, $totalFiles = 0)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($caminhoCompleto, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $cpfEncontrado = null;
        $verticesEncontrados = [];
        $pastasMap = ['' => $pastaImovel->id];
        $countNovos = 0;

        foreach ($iterator as $file) {
            // Normalize path separators to forward slash
            $normalizedPath = str_replace('\\', '/', $file->getPathname());
            $normalizedCaminhoCompleto = str_replace('\\', '/', $caminhoCompleto);
            $relPath = trim(str_replace($normalizedCaminhoCompleto, '', $normalizedPath), '/');

            // Ignorar se qualquer parte do caminho relativo for uma pasta a ser ignorada
            if ($relPath !== '') {
                $parts = explode('/', $relPath);
                $inIgnoredFolder = false;
                foreach ($parts as $part) {
                    $upperPart = mb_strtoupper($part, 'UTF-8');
                    if (in_array($upperPart, ['GNSS', 'RTK', 'BASE', 'ROVER']) || stripos($upperPart, 'METRICA') !== false || stripos($upperPart, 'MÉTRICA') !== false) {
                        $inIgnoredFolder = true;
                        break;
                    }
                }
                if ($inIgnoredFolder) {
                    continue;
                }
            }

            if ($file->isDir()) {
                // Criar subpasta para garantir que a estrutura 1:1 seja criada (mesmo se vazia)
                $currentParentId = $pastaImovel->id;
                
                if ($relPath !== '') {
                    $parts = explode('/', $relPath);
                    $currentPath = '';
                    
                    foreach ($parts as $part) {
                        $currentPath .= ($currentPath === '' ? '' : '/') . $part;
                        
                        if (!isset($pastasMap[$currentPath])) {
                            $novaPasta = Pasta::firstOrCreate([
                                'nome' => $part,
                                'parent_id' => $currentParentId
                            ], [
                                'tipo_servico' => 'pendente'
                            ]);
                            $pastasMap[$currentPath] = $novaPasta->id;
                        }
                        $currentParentId = $pastasMap[$currentPath];
                    }
                }
            }

            if ($file->isFile()) {
                $ext = strtolower($file->getExtension());
                $basename = strtolower($file->getBasename());

                // Ignorar arquivos indesejados, extensões configuradas e arquivos temporários do Office
                if (in_array($ext, $this->ignoredExtensions) || in_array($basename, $this->ignoredFiles) || str_starts_with($file->getFilename(), '~$') || $basename === 'thumbs.db' || str_contains($basename, 'topo.zip')) {
                    continue;
                }

                // Atualiza o progresso no cache
                if ($cacheKey) {
                    $currentProgress++;
                    $percentage = $totalFiles > 0 ? round(($currentProgress / $totalFiles) * 100) : 0;
                    \Illuminate\Support\Facades\Cache::put($cacheKey, [
                        'current' => $currentProgress,
                        'total' => $totalFiles,
                        'status' => 'running',
                        'percentage' => $percentage,
                        'last_file' => $file->getBasename()
                    ], 300);
                }

                // Determinar a subpasta correta
                $relPathPai = trim(str_replace($normalizedCaminhoCompleto, '', str_replace('\\', '/', $file->getPath())), '/');
                $currentParentId = $pastaImovel->id;
                
                if ($relPathPai !== '') {
                    $parts = explode('/', $relPathPai);
                    $currentPath = '';
                    
                    foreach ($parts as $part) {
                        $currentPath .= ($currentPath === '' ? '' : '/') . $part;
                        
                        if (!isset($pastasMap[$currentPath])) {
                            $novaPasta = Pasta::firstOrCreate([
                                'nome' => $part,
                                'parent_id' => $currentParentId
                            ], [
                                'tipo_servico' => 'pendente'
                            ]);
                            $pastasMap[$currentPath] = $novaPasta->id;
                        }
                        $currentParentId = $pastasMap[$currentPath];
                    }
                }

                // Verificar se arquivo já existe no DB
                $arquivoExistente = Arquivo::where('nome', $file->getBasename())
                                 ->where('pasta_id', $currentParentId)
                                 ->first();
                                 
                if ($arquivoExistente) {
                    // Substituir arquivo físico se já existir
                    @unlink(storage_path('app/public/' . $arquivoExistente->path));
                    $arquivoExistente->delete();
                }

                // NOVO ARQUIVO! (Ou re-importado). Fazer o processamento.
                $countNovos++;
                $parsedData = null;
                if ($ext === 'pdf') {
                    $parsedData = $this->parsePdfForData($file->getPathname());
                } elseif (in_array($ext, ['ods', 'odt', 'docx'])) {
                    if ($ext === 'ods') {
                        $this->processarOdsParaMarcos($file->getPathname(), $pastaImovel->nome);
                    }
                    $parsedData = $this->parseZipBasedFileForData($file->getPathname());
                }

                if ($parsedData) {
                    if ($parsedData['cpf'] && !$cpfEncontrado) {
                        $cpfEncontrado = $parsedData['cpf'];
                    }
                    if (!empty($parsedData['vertices'])) {
                        $verticesEncontrados = array_merge($verticesEncontrados, $parsedData['vertices']);
                    }
                }

                // Copiar o arquivo para o Storage
                $conteudoArquivo = file_get_contents($file->getPathname());
                $novoCaminho = 'arquivos/' . $currentParentId . '/' . uniqid() . '_' . $file->getBasename();
                
                Storage::disk('public')->put($novoCaminho, $conteudoArquivo);

                Arquivo::create([
                    'pasta_id' => $currentParentId,
                    'nome' => $file->getBasename(),
                    'path' => $novoCaminho,
                    'tamanho' => round($file->getSize() / 1024 / 1024, 2),
                    'tipo' => strtoupper($ext),
                ]);
            }
        }

        // Atrelar CPF e Usuário se aplicável
        if ($cpfEncontrado) {
            $cpfNumerico = preg_replace('/[^0-9]/', '', $cpfEncontrado);
            if (!$pastaImovel->identificador_cliente) {
                $pastaImovel->identificador_cliente = $cpfNumerico;

                $cliente = User::where('cpf', $cpfNumerico)
                               ->orWhere('cnpj', $cpfNumerico)
                               ->first();

                if ($cliente) {
                    $pastaImovel->cliente_id = $cliente->id;
                }
                $pastaImovel->save();
            }
        }

        // Salvar vértices encontrados
        if (!empty($verticesEncontrados)) {
            $verticesUnicos = array_unique($verticesEncontrados);
            foreach ($verticesUnicos as $vertice) {
                $parts = explode('-', $vertice);
                if (count($parts) >= 3) {
                    Marco::firstOrCreate([
                        'credencial' => $parts[0],
                        'tipo' => $parts[1],
                        'numero' => $parts[2],
                    ], [
                        'imovel' => $pastaImovel->nome,
                        'user_id' => 1 // ID default caso não tenha, o sistema é o owner
                    ]);
                }
            }
        }

        return $countNovos;
    }

    private function parsePdfForData($pdfPath)
    {
        $result = ['cpf' => null, 'vertices' => []];
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($pdfPath);
            $text = $pdf->getText();

            if (preg_match_all('/\d{3}\.\d{3}\.\d{3}\-\d{2}/', $text, $matches)) {
                foreach ($matches[0] as $cpf) {
                    if ($cpf !== $this->tecnicoCpf) {
                        $result['cpf'] = $cpf;
                        break;
                    }
                }
            }
            if (preg_match_all('/BCA-[MPV][\w\d\-]+/', $text, $matches)) {
                $result['vertices'] = $matches[0];
            }
        } catch (\Throwable $e) {
            Log::warning("SyncPendentes: Falha ao ler o PDF {$pdfPath}: " . $e->getMessage());
        }
        return $result;
    }

    private function parseZipBasedFileForData($filePath)
    {
        $result = ['cpf' => null, 'vertices' => []];
        try {
            $zip = new \ZipArchive;
            if ($zip->open($filePath) === TRUE) {
                $content = $zip->getFromName('content.xml');
                if (!$content) {
                    $content = $zip->getFromName('word/document.xml');
                }
                $zip->close();

                if ($content) {
                    $contentWithSpaces = str_replace('><', '> <', $content);
                    $text = strip_tags($contentWithSpaces);

                    if (preg_match_all('/\d{3}\.\d{3}\.\d{3}\-\d{2}/', $text, $matches)) {
                        foreach ($matches[0] as $cpf) {
                            if ($cpf !== $this->tecnicoCpf) {
                                $result['cpf'] = $cpf;
                                break;
                            }
                        }
                    }
                    if (preg_match_all('/BCA-[MPV][\w\d\-]+/', $text, $matches)) {
                        $result['vertices'] = $matches[0];
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("SyncPendentes: Falha ao extrair texto do arquivo compactado {$filePath}: " . $e->getMessage());
        }
        return $result;
    }

    private function processarOdsParaMarcos($filePath, $imovelName)
    {
        try {
            $parserService = app(\App\Services\SigefOdsParserService::class);
            $dadosOds = $parserService->parseOdsFile($filePath);
            $vertices = $dadosOds['vertices'] ?? [];
            
            foreach ($vertices as $v) {
                $codigo = trim($v['codigo']);
                if (preg_match('/^([A-Z0-9]+)\-([A-Z])\-(.+)$/i', $codigo, $mParts)) {
                    $cred = strtoupper($mParts[1]);
                    $tipo = strtoupper($mParts[2]);
                    $num = str_pad($mParts[3], 4, '0', STR_PAD_LEFT);

                    $marcoExistente = \App\Models\Marco::where('credencial', $cred)
                        ->where('tipo', $tipo)
                        ->where('numero', $num)
                        ->first();

                    if ($marcoExistente) {
                        if (is_null($marcoExistente->latitude) && is_null($marcoExistente->easting)) {
                            if ($v['tipo'] === 'geodesica') {
                                $marcoExistente->latitude = $v['N'];
                                $marcoExistente->longitude = $v['E'];
                            } else {
                                $marcoExistente->easting = $v['E'];
                                $marcoExistente->northing = $v['N'];
                            }
                            $marcoExistente->save();
                        }
                    } else {
                        $novoMarco = new \App\Models\Marco();
                        $novoMarco->user_id = 1; // Sistema
                        $novoMarco->credencial = $cred;
                        $novoMarco->tipo = $tipo;
                        $novoMarco->numero = $num;
                        $novoMarco->imovel = substr($imovelName, 0, 100);

                        if ($v['tipo'] === 'geodesica') {
                            $novoMarco->latitude = $v['N'];
                            $novoMarco->longitude = $v['E'];
                        } else {
                            $novoMarco->easting = $v['E'];
                            $novoMarco->northing = $v['N'];
                        }
                        $novoMarco->save();
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning("Erro ao processar ODS para extrair coordenadas no Sync: " . $e->getMessage());
        }
    }
}
