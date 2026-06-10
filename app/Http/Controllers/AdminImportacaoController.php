<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Pasta;
use App\Models\Arquivo;
use App\Models\User;
use App\Models\Marco;
use Smalot\PdfParser\Parser as PdfParser;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;

class AdminImportacaoController extends Controller
{
    private $ignoredFolders = ['GNSS', 'RTK', 'BASE', 'ROVER', 'ARQUIVO METRICA', 'ARQUIVO MÉRICA'];
    private $ignoredExtensions = ['topo', 'tbkp', 'dwl', 'dwl2', 'bak'];
    private $ignoredFiles = [];

    public function index()
    {
        $syncAtivo = \App\Models\Configuracao::where('chave', 'sync_ativo')->first()?->valor ?? '0';
        $syncRootDir = \App\Models\Configuracao::where('chave', 'sync_root_dir')->first()?->valor ?? '';
        return view('admin.importacao.index', compact('syncAtivo', 'syncRootDir'));
    }

    public function buscarPastas(Request $request)
    {
        $path = rtrim($request->input('path'), '\\/');
        
        Log::info('Tentativa de importação - Path recebido: ' . $path);

        if (empty($path) || !is_dir($path)) {
            Log::error('Erro is_dir ou empty: ' . $path);
            return response()->json(['error' => 'Caminho inválido ou não encontrado no servidor.'], 400);
        }

        $basename = basename($path);
        $parent = basename(dirname($path));
        $grandparent = basename(dirname(dirname($path)));

        $pastasImoveis = [];

        try {
            if (preg_match('/^\d{4}$/', $basename)) {
                // Nível 1: Ano
                $ano = $basename;
                $categorias = new \DirectoryIterator($path);
                foreach ($categorias as $categoria) {
                    if ($categoria->isDot() || !$categoria->isDir()) continue;
                    $imoveis = new \DirectoryIterator($categoria->getPathname());
                    foreach ($imoveis as $imovel) {
                        if ($imovel->isDot() || !$imovel->isDir()) continue;
                        $pastasImoveis[] = [
                            'ano' => $ano,
                            'categoria' => $categoria->getBasename(),
                            'imovel' => $imovel->getBasename(),
                            'caminho_completo' => $imovel->getPathname()
                        ];
                    }
                }
            } elseif (preg_match('/^\d{4}$/', $parent)) {
                // Nível 2: Categoria
                $ano = $parent;
                $categoriaNome = $basename;
                $imoveis = new \DirectoryIterator($path);
                foreach ($imoveis as $imovel) {
                    if ($imovel->isDot() || !$imovel->isDir()) continue;
                    $pastasImoveis[] = [
                        'ano' => $ano,
                        'categoria' => $categoriaNome,
                        'imovel' => $imovel->getBasename(),
                        'caminho_completo' => $imovel->getPathname()
                    ];
                }
            } elseif (preg_match('/^\d{4}$/', $grandparent)) {
                // Nível 3: Imóvel
                $ano = $grandparent;
                $categoriaNome = $parent;
                $imovelNome = $basename;
                $pastasImoveis[] = [
                    'ano' => $ano,
                    'categoria' => $categoriaNome,
                    'imovel' => $imovelNome,
                    'caminho_completo' => $path
                ];
            } else {
                return response()->json(['error' => 'O caminho deve apontar para uma pasta de Ano (ex: 2026), Categoria ou Imóvel específico.'], 400);
            }

            return response()->json(['pastas' => $pastasImoveis]);
        } catch (\Exception $e) {
            Log::error("Erro ao buscar pastas de importação: " . $e->getMessage());
            return response()->json(['error' => 'Erro ao ler o diretório: ' . $e->getMessage()], 500);
        }
    }

    public function runPowerShell(Request $request)
    {
        $path = rtrim($request->input('path'), '\\/');
        if (empty($path)) {
            return response()->json(['error' => 'Por favor, informe o caminho.'], 400);
        }

        try {
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            if ($isWindows) {
                $php = PHP_BINARY;
                if (preg_match('/php-fpm|php-cgi/i', $php)) {
                    $possiblePaths = [
                        str_replace(['php-fpm', 'php-cgi', 'sbin'], ['php', 'php', 'bin'], $php),
                        str_replace(['php-fpm', 'php-cgi'], 'php', $php),
                        '/usr/local/bin/php',
                        '/usr/bin/php',
                        'php'
                    ];
                    foreach ($possiblePaths as $phpPath) {
                        if (file_exists($phpPath) && is_executable($phpPath)) {
                            $php = $phpPath;
                            break;
                        }
                    }
                    if ($php === PHP_BINARY) {
                        $php = 'php';
                    }
                }
                $artisanPath = base_path('artisan');
                // Open a new visible cmd window to run the command
                $cmd = "start \"Importacao PowerShell\" cmd /c \"\"" . escapeshellarg($php) . "\" \"" . escapeshellarg($artisanPath) . "\" pastas:import \"" . escapeshellcmd($path) . "\" && echo. && echo Importacao concluida. Pode fechar esta janela. && pause\"";
                pclose(popen($cmd, "r"));
                
                return response()->json([
                    'status' => 'success', 
                    'message' => 'Comando iniciado em uma nova janela do PowerShell/CMD!'
                ]);
            } else {
                return response()->json(['error' => 'Este comando funciona apenas no servidor Windows local.'], 400);
            }
        } catch (\Exception $e) {
            Log::error("Erro ao iniciar PowerShell: " . $e->getMessage());
            return response()->json(['error' => 'Erro ao iniciar PowerShell: ' . $e->getMessage()], 500);
        }
    }

    public function processarPasta(Request $request)
    {
        $dados = $request->validate([
            'ano' => 'required|string',
            'categoria' => 'required|string',
            'imovel' => 'required|string',
            'caminho_completo' => 'required|string',
        ]);

        $caminhoCompleto = $dados['caminho_completo'];

        if (!is_dir($caminhoCompleto)) {
            return response()->json(['error' => 'Diretório não encontrado: ' . $caminhoCompleto], 404);
        }

        try {
            DB::beginTransaction();

            // 1. Cria a Estrutura de Pastas no Banco de Dados
            $pastaAno = Pasta::firstOrCreate(
                ['nome' => $dados['ano'], 'parent_id' => null],
                ['tipo_servico' => 'pronto']
            );

            $pastaCategoria = Pasta::firstOrCreate(
                ['nome' => $dados['categoria'], 'parent_id' => $pastaAno->id],
                ['tipo_servico' => 'pronto']
            );

            // Verifica se a pasta Nível 3 já existe
            $pastaImovel = Pasta::where('nome', $dados['imovel'])
                                ->where('parent_id', $pastaCategoria->id)
                                ->first();

            if ($pastaImovel) {
                DB::rollBack();
                return response()->json(['status' => 'skipped', 'message' => 'Pasta já existe no sistema.']);
            }

            $pastaImovel = Pasta::create([
                'nome' => $dados['imovel'],
                'parent_id' => $pastaCategoria->id,
                'tipo_servico' => 'pronto',
            ]);

            // 2. Vasculhar arquivos, extrair dados e copiar
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($caminhoCompleto, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            $cpfsEncontrados = [];
            $verticesEncontrados = [];
            
            // Map to cache folder instances: ['relative/path' => Pasta_ID]
            $pastasMap = ['' => $pastaImovel->id];

            foreach ($iterator as $file) {
                // Normalize path separators to forward slash
                $normalizedPath = str_replace('\\', '/', $file->getPathname());
                $normalizedCaminhoCompleto = str_replace('\\', '/', $caminhoCompleto);
                $relPath = trim(str_replace($normalizedCaminhoCompleto, '', $normalizedPath), '/');

                $isIgnored = false;
                $isPppBase = false;

                // Check ignored folders or if it's a PPP base folder
                if ($relPath !== '') {
                    $parts = explode('/', $relPath);
                    foreach ($parts as $part) {
                        $upperPart = mb_strtoupper($part, 'UTF-8');
                        if (in_array($upperPart, ['GNSS', 'RTK', 'BASE', 'ROVER']) || stripos($upperPart, 'METRICA') !== false || stripos($upperPart, 'MÉTRICA') !== false) {
                            $isIgnored = true;
                        }
                        if ($upperPart === 'PPP') {
                            $isPppBase = true;
                        }
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
                                    'tipo_servico' => 'pronto',
                                    'oculto' => $isIgnored ? 1 : 0
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

                    // Skip only system temp files and thumbs.db completely
                    if (str_starts_with($file->getFilename(), '~$') || $basename === 'thumbs.db') {
                        continue;
                    }

                    // Check ignored extensions and specific files like topo.zip
                    if (in_array($ext, $this->ignoredExtensions) || in_array($basename, $this->ignoredFiles) || str_contains($basename, 'topo.zip') || str_contains($basename, 'topos.zip')) {
                        $isIgnored = true;
                    }

                    // Handle PPP zip files if it contains @
                    if ($isPppBase && $ext === 'zip' && str_contains($basename, '@')) {
                        $baseProcessor = app(\App\Services\BaseProcessorService::class);
                        $baseProcessor->processPppZip($file->getPathname());
                        $isIgnored = true; // Mark the original zip as hidden or we could continue to skip it. We'll store it as hidden.
                    }

                    // Extração de dados (CPF e Vértices)
                    $parsedData = null;
                    if ($ext === 'pdf') {
                        $parsedData = $this->parsePdfForData($file->getPathname());
                    } elseif (in_array($ext, ['ods', 'odt', 'docx'])) {
                        if ($ext === 'ods') {
                            $this->processarOdsParaMarcos($file->getPathname(), $dados['imovel']);
                        }
                        $parsedData = $this->parseZipBasedFileForData($file->getPathname());
                    }

                    if ($parsedData) {
                        if (!empty($parsedData['cpf'])) {
                            $cpfsEncontrados[] = $parsedData['cpf'];
                        }
                        if (!empty($parsedData['vertices'])) {
                            $verticesEncontrados = array_merge($verticesEncontrados, $parsedData['vertices']);
                        }
                    }

                    // Determinar a subpasta correta
                    $relPath = trim(str_replace($caminhoCompleto, '', $file->getPath()), '\\/');
                    
                    if ($relPath !== '') {
                        $parts = explode(DIRECTORY_SEPARATOR, $relPath);
                        $currentPath = '';
                        $currentParentId = $pastaImovel->id;
                        
                        foreach ($parts as $part) {
                            $currentPath .= ($currentPath === '' ? '' : DIRECTORY_SEPARATOR) . $part;
                            
                            if (!isset($pastasMap[$currentPath])) {
                                $novaPasta = Pasta::firstOrCreate([
                                    'nome' => $part,
                                    'parent_id' => $currentParentId
                                ], [
                                    'tipo_servico' => 'pronto',
                                    'oculto' => $isIgnored ? 1 : 0
                                ]);
                                $pastasMap[$currentPath] = $novaPasta->id;
                            }
                            $currentParentId = $pastasMap[$currentPath];
                        }
                    } else {
                        $currentParentId = $pastaImovel->id;
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
                        'oculto' => $isIgnored ? 1 : 0
                    ]);
                }
            }

            // Verifica se é projeto de órgão governamental
            $isGovernmentProject = preg_match('/\b(INCRA|INTERACRE|FAPEC|TERRA LEGAL|GOVERNO|PREFEITURA)\b/i', $dados['categoria'] . ' ' . $dados['imovel']);

            // 3. Atrelar CPF e Usuário se aplicável
            if (!$isGovernmentProject && !empty($cpfsEncontrados)) {
                $cpfsEncontrados = array_unique($cpfsEncontrados);
                foreach ($cpfsEncontrados as $cpfEncontrado) {
                    $cpfNumerico = preg_replace('/[^0-9]/', '', $cpfEncontrado);
                    
                    $cliente = User::where('cpf', $cpfNumerico)
                                   ->orWhere('cnpj', $cpfNumerico)
                                   ->first();

                    if ($cliente) {
                        if (empty($pastaImovel->cliente_id)) {
                            $pastaImovel->identificador_cliente = $cpfNumerico;
                            $pastaImovel->cliente_id = $cliente->id;
                            $pastaImovel->save();
                        } elseif ($pastaImovel->cliente_id !== $cliente->id) {
                            $pastaImovel->clientesSecundarios()->syncWithoutDetaching([$cliente->id]);
                        }
                    } else {
                        // Se não encontrou cliente mas tem o CPF, salva no identificador caso esteja vazio
                        if (empty($pastaImovel->identificador_cliente)) {
                            $pastaImovel->identificador_cliente = $cpfNumerico;
                            $pastaImovel->save();
                        }
                    }
                }
            }

            // 4. Salvar vértices encontrados
            $verticesUnicos = array_unique($verticesEncontrados);
            foreach ($verticesUnicos as $vertice) {
                // Tenta associar o marco à propriedade
                $parts = explode('-', $vertice);
                if (count($parts) >= 3) {
                    Marco::firstOrCreate([
                        'credencial' => $parts[0],
                        'tipo' => $parts[1],
                        'numero' => $parts[2],
                    ], [
                        'imovel' => $dados['imovel'],
                        'user_id' => auth()->id() ?: 1
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'cpf' => $cpfEncontrado,
                'vertices' => count($verticesUnicos),
                'message' => 'Pasta processada com sucesso.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao processar pasta {$dados['caminho_completo']}: " . $e->getMessage());
            return response()->json(['error' => 'Erro ao processar pasta: ' . $e->getMessage()], 500);
        }
    }

    private function isCpfAdmin($cpf) {
        $cleanCpf = preg_replace('/[^0-9]/', '', $cpf);
        static $adminCpfs = null;
        if ($adminCpfs === null) {
            $adminCpfs = \App\Models\User::where('role', 'admin')->pluck('cpf')->map(function($c) {
                return preg_replace('/[^0-9]/', '', $c);
            })->toArray();
        }
        return in_array($cleanCpf, $adminCpfs);
    }

    private function parsePdfForData($pdfPath)
    {
        $result = ['cpf' => null, 'vertices' => []];
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($pdfPath);
            $text = $pdf->getText();

            // Buscar CPF (000.000.000-00)
            if (preg_match_all('/\d{3}\.\d{3}\.\d{3}\-\d{2}/', $text, $matches)) {
                foreach ($matches[0] as $cpf) {
                    if (!$this->isCpfAdmin($cpf)) {
                        $result['cpf'] = $cpf;
                        break; // Pega o primeiro que não seja admin
                    }
                }
            }

            // Buscar Vértices (BCA-M, BCA-P, BCA-V)
            if (preg_match_all('/BCA-[MPV]-\d{1,5}(?![0-9])/', $text, $matches)) {
                $result['vertices'] = $matches[0];
            }

        } catch (\Exception $e) {
            Log::warning("Falha ao ler o PDF {$pdfPath}: " . $e->getMessage());
        }

        return $result;
    }

    private function parseZipBasedFileForData($filePath)
    {
        $result = ['cpf' => null, 'vertices' => []];
        try {
            $zip = new \ZipArchive;
            if ($zip->open($filePath) === TRUE) {
                // Para ODS e ODT
                $content = $zip->getFromName('content.xml');
                // Para DOCX
                if (!$content) {
                    $content = $zip->getFromName('word/document.xml');
                }
                $zip->close();

                if ($content) {
                    // Adicionar espaços entre as tags para evitar que textos de células vizinhas se juntem
                    $contentWithSpaces = str_replace('><', '> <', $content);
                    $text = strip_tags($contentWithSpaces);

                    // Buscar CPF (000.000.000-00) ou sem formatação, vamos manter o regex atual para segurança:
                    if (preg_match_all('/\d{3}\.\d{3}\.\d{3}\-\d{2}/', $text, $matches)) {
                        foreach ($matches[0] as $cpf) {
                            if (!$this->isCpfAdmin($cpf)) {
                                $result['cpf'] = $cpf;
                                break;
                            }
                        }
                    }

                    // Buscar Vértices (BCA-M, BCA-P, BCA-V)
                    if (preg_match_all('/BCA-[MPV]-\d{1,5}(?![0-9])/', $text, $matches)) {
                        $result['vertices'] = $matches[0];
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Falha ao extrair texto do arquivo compactado {$filePath}: " . $e->getMessage());
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
                    
                    if (!in_array($cred, ['BCA', 'EMES'])) {
                        continue;
                    }

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
                            // Não alteramos o user_id, mantendo quem cadastrou originalmente
                            $marcoExistente->save();
                        }
                    } else {
                        $novoMarco = new \App\Models\Marco();
                        $novoMarco->user_id = auth()->id() ?? 1;
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
            
            event(new \App\Events\MarcosAtualizadosEvent('Novos marcos importados via ODS'));
        } catch (\Exception $e) {
            Log::warning("Erro ao processar ODS para extrair coordenadas: " . $e->getMessage());
        }
    }

    public function uploadWeb(Request $request)
    {
        // Libera a sessão do PHP imediatamente para permitir requisições concorrentes da mesma sessão
        session_write_close();

        $request->validate([
            'tipo_upload' => 'required|in:ano,categoria,imovel',
            'status_servico' => 'required|in:pronto,pendente',
            'caminho_relativo' => 'required|string',
            'arquivo' => 'required|file'
        ]);

        try {
            DB::beginTransaction();

            $tipo = $request->tipo_upload;
            $statusServico = $request->status_servico;
            $relPath = trim($request->caminho_relativo);
            $file = $request->file('arquivo');

            $parts = explode('/', str_replace('\\', '/', $relPath));
            $fileName = array_pop($parts);
            $basename = strtolower($fileName);
            $ext = strtolower($file->getClientOriginalExtension());

            $ano = '';
            $categoria = '';
            $imovel = '';

            if ($tipo === 'ano') {
                if (count($parts) < 3) return response()->json(['status' => 'ignored']);
                $ano = array_shift($parts);
                $categoria = array_shift($parts);
                $imovel = array_shift($parts);
            } elseif ($tipo === 'categoria') {
                if (count($parts) < 2) return response()->json(['status' => 'ignored']);
                $ano = trim($request->ano);
                if (!$ano) return response()->json(['error' => 'Ano não informado'], 400);
                $categoria = array_shift($parts);
                $imovel = array_shift($parts);
            } elseif ($tipo === 'imovel') {
                if (count($parts) < 1) return response()->json(['status' => 'ignored']);
                $ano = trim($request->ano);
                $categoria = trim($request->categoria);
                if (!$ano || !$categoria) return response()->json(['error' => 'Ano ou Categoria não informados'], 400);
                $imovel = array_shift($parts);
            }

            // Filtros de ignorar arquivos e pastas
            $isIgnored = false;
            $isPppBase = false;

            if (str_starts_with($fileName, '~$') || $basename === 'thumbs.db') {
                DB::rollBack();
                return response()->json(['status' => 'ignored']);
            }

            if (in_array($ext, $this->ignoredExtensions) || in_array($basename, $this->ignoredFiles) || str_contains($basename, 'topo.zip') || str_contains($basename, 'topos.zip')) {
                $isIgnored = true;
            }

            foreach ($parts as $part) {
                if (in_array(mb_strtolower($part, 'UTF-8'), array_map(fn($p) => mb_strtolower($p, 'UTF-8'), $this->ignoredFolders))) {
                    $isIgnored = true;
                }
                if (mb_strtoupper($part, 'UTF-8') === 'PPP') {
                    $isPppBase = true;
                }
            }

            // Handle PPP zip files se houver @
            if ($isPppBase && $ext === 'zip' && str_contains($basename, '@')) {
                $baseProcessor = app(\App\Services\BaseProcessorService::class);
                $baseProcessor->processPppZip($file->getRealPath());
                $isIgnored = true; 
            }

            // 1. Criar a estrutura base COM CACHE LOCK para evitar concorrência
            $lockKey = 'cria_pastas_user_' . (auth()->id() ?: 'guest');
            $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 15);
            
            $currentParentId = null;
            $pastaImovel = null;

            $lock->block(10, function () use (&$pastaImovel, &$currentParentId, $ano, $categoria, $imovel, $statusServico, $parts, $isIgnored) {
                $pastaAno = Pasta::firstOrCreate(['nome' => $ano, 'parent_id' => null], ['tipo_servico' => 'pronto']);
                $pastaCat = Pasta::firstOrCreate(['nome' => $categoria, 'parent_id' => $pastaAno->id], ['tipo_servico' => 'pronto']);
                $pastaImovel = Pasta::firstOrCreate(['nome' => $imovel, 'parent_id' => $pastaCat->id], ['tipo_servico' => $statusServico]);

                $currentParentId = $pastaImovel->id;
                
                // Criar a estrutura de subpastas (Documentos, etc)
                foreach ($parts as $part) {
                    if (trim($part) === '') continue;
                    $novaPasta = Pasta::firstOrCreate([
                        'nome' => $part,
                        'parent_id' => $currentParentId
                    ], [
                        'tipo_servico' => 'pronto',
                        'oculto' => $isIgnored ? 1 : 0
                    ]);
                    $currentParentId = $novaPasta->id;
                }
            });

            // 3. Processar o arquivo
            $tamanho = round($file->getSize() / 1024 / 1024, 2);

            // Copiar o arquivo para o Storage
            $novoCaminho = 'arquivos/' . $currentParentId . '/' . uniqid() . '_' . $file->getClientOriginalName();
            Storage::disk('public')->put($novoCaminho, file_get_contents($file->getRealPath()));

            Arquivo::create([
                'pasta_id' => $currentParentId,
                'nome' => $file->getClientOriginalName(),
                'path' => $novoCaminho,
                'tamanho' => $tamanho,
                'tipo' => strtoupper($ext),
                'oculto' => $isIgnored ? 1 : 0
            ]);

            // Extração de dados (CPF e Vértices)
            $parsedData = null;
            if ($ext === 'pdf') {
                $parsedData = $this->parsePdfForData($file->getRealPath());
            } elseif (in_array($ext, ['ods', 'odt', 'docx'])) {
                if ($ext === 'ods') {
                    $this->processarOdsParaMarcos($file->getRealPath(), $imovel);
                }
                $parsedData = $this->parseZipBasedFileForData($file->getRealPath());
            }

            if ($parsedData) {
                if (!empty($parsedData['cpf'])) {
                    $cpfNumerico = preg_replace('/[^0-9]/', '', $parsedData['cpf']);
                    
                    $cliente = User::where('cpf', $cpfNumerico)
                                   ->orWhere('cnpj', $cpfNumerico)
                                   ->first();
                                   
                    if ($cliente) {
                        if (empty($pastaImovel->cliente_id)) {
                            $pastaImovel->identificador_cliente = $cpfNumerico;
                            $pastaImovel->cliente_id = $cliente->id;
                            $pastaImovel->save();
                        } elseif ($pastaImovel->cliente_id !== $cliente->id) {
                            $pastaImovel->clientesSecundarios()->syncWithoutDetaching([$cliente->id]);
                        }
                    } else {
                        if (empty($pastaImovel->identificador_cliente)) {
                            $pastaImovel->identificador_cliente = $cpfNumerico;
                            $pastaImovel->save();
                        }
                    }
                }

                if (!empty($parsedData['vertices'])) {
                    foreach (array_unique($parsedData['vertices']) as $vertice) {
                        $parts = explode('-', $vertice);
                        if (count($parts) >= 3) {
                            Marco::firstOrCreate([
                                'credencial' => $parts[0],
                                'tipo' => $parts[1],
                                'numero' => $parts[2],
                            ], [
                                'imovel' => $imovel,
                                'user_id' => auth()->id() ?: 1
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return response()->json(['status' => 'success']);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Erro no uploadWeb: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function salvarConfiguracaoSync(Request $request)
    {
        $request->validate([
            'sync_ativo' => 'required|in:0,1',
            'sync_root_dir' => 'nullable|string'
        ]);

        \App\Models\Configuracao::updateOrCreate(
            ['chave' => 'sync_ativo'],
            ['valor' => $request->sync_ativo]
        );

        \App\Models\Configuracao::updateOrCreate(
            ['chave' => 'sync_root_dir'],
            ['valor' => $request->sync_root_dir]
        );

        return response()->json(['status' => 'success', 'message' => 'Configurações salvas com sucesso!']);
    }

    public function syncAgora()
    {
        try {
            session_write_close();
            
            $cacheKey = 'sync_progress_global';
            \Illuminate\Support\Facades\Cache::put($cacheKey, [
                'current' => 0,
                'total' => 0,
                'status' => 'starting',
                'percentage' => 0,
                'last_file' => 'Aguardando inicialização do robô local...'
            ], 300);

            // Write trigger file for run_worker.bat running on Windows host
            $triggerFile = storage_path('sync_request.txt');
            file_put_contents($triggerFile, '1');
            Log::info("Escreveu arquivo de trigger de sync global em: " . $triggerFile);

            // If running natively on Windows host (not inside Docker Linux), spawn directly as well
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            if ($isWindows) {
                $php = PHP_BINARY;
                if (preg_match('/php-fpm|php-cgi/i', $php)) {
                    $possiblePaths = [
                        str_replace(['php-fpm', 'php-cgi', 'sbin'], ['php', 'php', 'bin'], $php),
                        str_replace(['php-fpm', 'php-cgi'], 'php', $php),
                        '/usr/local/bin/php',
                        '/usr/bin/php',
                        'php'
                    ];
                    foreach ($possiblePaths as $path) {
                        if (file_exists($path) && is_executable($path)) {
                            $php = $path;
                            break;
                        }
                    }
                    if ($php === PHP_BINARY) {
                        $php = 'php';
                    }
                }
                $artisanPath = base_path('artisan');
                $cmd = "start /B \"\" " . escapeshellarg($php) . " " . escapeshellarg($artisanPath) . " pastas:sync-pendentes > NUL 2>&1";
                Log::info("Iniciando sync global direto no Windows: " . $cmd);
                pclose(popen($cmd, "r"));
            }
            
            return response()->json([
                'status' => 'success', 
                'message' => 'Sincronização geral solicitada ao robô!'
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function syncPastaUnica($id)
    {
        try {
            session_write_close();
            
            $cacheKey = 'sync_progress_' . $id;
            \Illuminate\Support\Facades\Cache::put($cacheKey, [
                'current' => 0,
                'total' => 0,
                'status' => 'starting',
                'percentage' => 0,
                'last_file' => 'Aguardando inicialização do robô local...'
            ], 300);

            // Write trigger file for run_worker.bat running on Windows host
            $triggerFile = storage_path('sync_request_pasta.txt');
            file_put_contents($triggerFile, $id);
            Log::info("Escreveu arquivo de trigger de pasta única ({$id}) em: " . $triggerFile);

            // If running natively on Windows host (not inside Docker Linux), spawn directly as well
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            if ($isWindows) {
                $php = PHP_BINARY;
                if (preg_match('/php-fpm|php-cgi/i', $php)) {
                    $possiblePaths = [
                        str_replace(['php-fpm', 'php-cgi', 'sbin'], ['php', 'php', 'bin'], $php),
                        str_replace(['php-fpm', 'php-cgi'], 'php', $php),
                        '/usr/local/bin/php',
                        '/usr/bin/php',
                        'php'
                    ];
                    foreach ($possiblePaths as $path) {
                        if (file_exists($path) && is_executable($path)) {
                            $php = $path;
                            break;
                        }
                    }
                    if ($php === PHP_BINARY) {
                        $php = 'php';
                    }
                }
                $artisanPath = base_path('artisan');
                $cmd = "start /B \"\" " . escapeshellarg($php) . " " . escapeshellarg($artisanPath) . " pastas:sync-pendentes --pasta_id=" . escapeshellarg($id) . " > NUL 2>&1";
                Log::info("Iniciando sync de pasta única direto no Windows: " . $cmd);
                pclose(popen($cmd, "r"));
            }
            
            return response()->json([
                'status' => 'success', 
                'message' => 'Sincronização da pasta solicitada ao robô!'
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function toggleArquivoOculto($id)
    {
        try {
            $arquivo = Arquivo::findOrFail($id);
            $arquivo->oculto = !$arquivo->oculto;
            $arquivo->save();

            return response()->json([
                'status' => 'success',
                'oculto' => $arquivo->oculto,
                'message' => $arquivo->oculto ? 'Arquivo ocultado do cliente.' : 'Arquivo visível para o cliente.'
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function togglePastaOculto($id)
    {
        try {
            $pasta = Pasta::findOrFail($id);
            $pasta->oculto = !$pasta->oculto;
            $pasta->save();

            return response()->json([
                'status' => 'success',
                'oculto' => $pasta->oculto,
                'message' => $pasta->oculto ? 'Pasta ocultada do cliente.' : 'Pasta visível para o cliente.'
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getSyncProgress(Request $request)
    {
        $pastaId = $request->query('pasta_id');
        $cacheKey = $pastaId ? 'sync_progress_' . $pastaId : 'sync_progress_global';

        $progress = \Illuminate\Support\Facades\Cache::get($cacheKey, [
            'current' => 0,
            'total' => 0,
            'status' => 'idle',
            'percentage' => 0,
            'last_file' => ''
        ]);

        return response()->json($progress);
    }
}
