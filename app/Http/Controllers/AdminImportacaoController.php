<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\Configuracao;
use App\Models\Pasta;
use App\Services\BaseProcessorService;
use App\Services\Importacao\FiltroImportacao;
use App\Services\Importacao\ImportadorMarcos;
use App\Services\Importacao\ImportadorPasta;
use App\Services\Importacao\SincronizadorRobo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminImportacaoController extends Controller
{
    public function __construct(
        private ImportadorPasta $importador,
        private ImportadorMarcos $marcos,
        private SincronizadorRobo $sincronizador,
        private FiltroImportacao $filtro,
        private BaseProcessorService $baseProcessor,
    ) {
    }

    public function index()
    {
        $syncAtivo = Configuracao::where('chave', 'sync_ativo')->first()?->valor ?? '0';
        $syncRootDir = Configuracao::where('chave', 'sync_root_dir')->first()?->valor ?? '';

        return view('admin.importacao.index', compact('syncAtivo', 'syncRootDir'));
    }

    /**
     * Lista as pastas de imóvel encontradas a partir de um caminho (Ano, Categoria ou Imóvel).
     */
    public function buscarPastas(Request $request)
    {
        $path = rtrim($request->input('path'), '\\/');

        Log::info('Tentativa de importação - Path recebido: ' . $path);

        if (empty($path) || ! is_dir($path)) {
            Log::error('Erro is_dir ou empty: ' . $path);

            return response()->json(['error' => 'Caminho inválido ou não encontrado no servidor.'], 400);
        }

        $basename = basename($path);
        $parent = basename(dirname($path));
        $grandparent = basename(dirname(dirname($path)));

        try {
            if (preg_match('/^\d{4}$/', $basename)) {
                $pastasImoveis = $this->imoveisDoAno($path, $basename);
            } elseif (preg_match('/^\d{4}$/', $parent)) {
                $pastasImoveis = $this->imoveisDaCategoria($path, $parent, $basename);
            } elseif (preg_match('/^\d{4}$/', $grandparent)) {
                $pastasImoveis = [[
                    'ano' => $grandparent,
                    'categoria' => $parent,
                    'imovel' => $basename,
                    'caminho_completo' => $path,
                ]];
            } else {
                return response()->json(['error' => 'O caminho deve apontar para uma pasta de Ano (ex: 2026), Categoria ou Imóvel específico.'], 400);
            }

            return response()->json(['pastas' => $pastasImoveis]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar pastas de importação: ' . $e->getMessage());

            return response()->json(['error' => 'Erro ao ler o diretório: ' . $e->getMessage()], 500);
        }
    }

    public function runPowerShell(Request $request)
    {
        $path = rtrim($request->input('path'), '\\/');

        if (empty($path)) {
            return response()->json(['error' => 'Por favor, informe o caminho.'], 400);
        }

        if (! $this->sincronizador->ehWindows()) {
            return response()->json(['error' => 'Este comando funciona apenas no servidor Windows local.'], 400);
        }

        try {
            $this->sincronizador->executarImportacaoEmJanela($path);

            return response()->json([
                'status' => 'success',
                'message' => 'Comando iniciado em uma nova janela do PowerShell/CMD!',
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao iniciar PowerShell: ' . $e->getMessage());

            return response()->json(['error' => 'Erro ao iniciar PowerShell: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Importa uma pasta de imóvel inteira (servidor local) replicando sua estrutura no sistema.
     */
    public function processarPasta(Request $request)
    {
        $dados = $request->validate([
            'ano' => 'required|string',
            'categoria' => 'required|string',
            'imovel' => 'required|string',
            'caminho_completo' => 'required|string',
        ]);

        if (! is_dir($dados['caminho_completo'])) {
            return response()->json(['error' => 'Diretório não encontrado: ' . $dados['caminho_completo']], 404);
        }

        try {
            DB::beginTransaction();

            $pastaAno = Pasta::firstOrCreate(['nome' => $dados['ano'], 'parent_id' => null], ['tipo_servico' => 'pronto']);
            $pastaCategoria = Pasta::firstOrCreate(['nome' => $dados['categoria'], 'parent_id' => $pastaAno->id], ['tipo_servico' => 'pronto']);

            if (Pasta::where('nome', $dados['imovel'])->where('parent_id', $pastaCategoria->id)->exists()) {
                DB::rollBack();

                return response()->json(['status' => 'skipped', 'message' => 'Pasta já existe no sistema.']);
            }

            $pastaImovel = Pasta::create([
                'nome' => $dados['imovel'],
                'parent_id' => $pastaCategoria->id,
                'tipo_servico' => 'pronto',
            ]);

            $userId = auth()->id() ?: 1;
            $resultado = $this->importador->importarDiretorio($dados['caminho_completo'], $pastaImovel, 'pronto', $userId);

            $ehProjetoGovernamental = preg_match('/\b(INCRA|INTERACRE|FAPEC|TERRA LEGAL|GOVERNO|PREFEITURA)\b/i', $dados['categoria'] . ' ' . $dados['imovel']);
            if (! $ehProjetoGovernamental) {
                foreach ($resultado['cpfs'] as $cpf) {
                    $this->importador->associarClientePorCpf($pastaImovel, $cpf);
                }
            }

            $this->marcos->salvarVerticesTexto($resultado['vertices'], $dados['imovel'], $userId);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'cpf' => $resultado['cpfs'][0] ?? null,
                'vertices' => count(array_unique($resultado['vertices'])),
                'message' => 'Pasta processada com sucesso.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao processar pasta {$dados['caminho_completo']}: " . $e->getMessage());

            return response()->json(['error' => 'Erro ao processar pasta: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Recebe um único arquivo enviado pelo navegador (upload em lote) e o posiciona na árvore.
     */
    public function uploadWeb(Request $request)
    {
        // Libera a sessão do PHP imediatamente para permitir requisições concorrentes da mesma sessão.
        session_write_close();

        $request->validate([
            'tipo_upload' => 'required|in:ano,categoria,imovel',
            'status_servico' => 'required|in:pronto,pendente',
            'caminho_relativo' => 'required|string',
            'arquivo' => 'required|file',
        ]);

        try {
            DB::beginTransaction();

            $statusServico = $request->status_servico;
            $file = $request->file('arquivo');

            $partes = explode('/', str_replace('\\', '/', trim($request->caminho_relativo)));
            $nomeArquivo = array_pop($partes);
            $basename = strtolower($nomeArquivo);
            $ext = strtolower($file->getClientOriginalExtension());

            // Resolve Ano/Categoria/Imóvel conforme o nível enviado; $partes fica só com as subpastas.
            $niveis = $this->resolverNiveisUpload($request->tipo_upload, $partes, $request);
            if (isset($niveis['resposta'])) {
                DB::rollBack();

                return $niveis['resposta'];
            }
            ['ano' => $ano, 'categoria' => $categoria, 'imovel' => $imovel, 'partes' => $partes] = $niveis;

            if ($this->filtro->ehTemporario($nomeArquivo, $basename)) {
                DB::rollBack();

                return response()->json(['status' => 'ignored']);
            }

            $oculto = $this->filtro->extensaoIgnorada($ext, $basename);
            $ehPpp = false;
            foreach ($partes as $parte) {
                if ($this->filtro->pastaIgnorada($parte)) {
                    $oculto = true;
                }
                if ($this->filtro->ehPastaPpp($parte)) {
                    $ehPpp = true;
                }
            }

            if ($ehPpp && $ext === 'zip' && str_contains($basename, '@')) {
                $this->baseProcessor->processPppZip($file->getRealPath());
                $oculto = true;
            }

            // Cria a estrutura de pastas com lock para evitar concorrência entre uploads simultâneos.
            $pastaImovel = null;
            $pastaDestinoId = null;
            Cache::lock('cria_pastas_user_' . (auth()->id() ?: 'guest'), 15)->block(10, function () use (&$pastaImovel, &$pastaDestinoId, $ano, $categoria, $imovel, $statusServico, $partes, $oculto) {
                $pastaAno = Pasta::firstOrCreate(['nome' => $ano, 'parent_id' => null], ['tipo_servico' => 'pronto']);
                $pastaCategoria = Pasta::firstOrCreate(['nome' => $categoria, 'parent_id' => $pastaAno->id], ['tipo_servico' => 'pronto']);
                $pastaImovel = Pasta::firstOrCreate(['nome' => $imovel, 'parent_id' => $pastaCategoria->id], ['tipo_servico' => $statusServico]);

                $pastaDestinoId = $pastaImovel->id;
                foreach ($partes as $parte) {
                    if (trim($parte) === '') {
                        continue;
                    }
                    $sub = Pasta::firstOrCreate(
                        ['nome' => $parte, 'parent_id' => $pastaDestinoId],
                        ['tipo_servico' => 'pronto', 'oculto' => $oculto ? 1 : 0]
                    );
                    $pastaDestinoId = $sub->id;
                }
            });

            $this->importador->salvarArquivo(
                $pastaDestinoId,
                $file->getClientOriginalName(),
                file_get_contents($file->getRealPath()),
                round($file->getSize() / 1024 / 1024, 2),
                $ext,
                $oculto
            );

            $userId = auth()->id() ?: 1;
            $dados = $this->importador->extrairDados($file->getRealPath(), $ext, $imovel, $userId);

            if ($dados['cpf']) {
                $this->importador->associarClientePorCpf($pastaImovel, $dados['cpf']);
            }
            $this->marcos->salvarVerticesTexto($dados['vertices'], $imovel, $userId);

            DB::commit();

            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro no uploadWeb: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function salvarConfiguracaoSync(Request $request)
    {
        $request->validate([
            'sync_ativo' => 'required|in:0,1',
            'sync_root_dir' => 'nullable|string',
        ]);

        Configuracao::updateOrCreate(['chave' => 'sync_ativo'], ['valor' => $request->sync_ativo]);
        Configuracao::updateOrCreate(['chave' => 'sync_root_dir'], ['valor' => $request->sync_root_dir]);

        return response()->json(['status' => 'success', 'message' => 'Configurações salvas com sucesso!']);
    }

    public function syncAgora()
    {
        try {
            session_write_close();
            $this->sincronizador->solicitarGlobal();

            return response()->json(['status' => 'success', 'message' => 'Sincronização geral solicitada ao robô!']);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function syncPastaUnica($id)
    {
        try {
            session_write_close();
            $this->sincronizador->solicitarPasta($id);

            return response()->json(['status' => 'success', 'message' => 'Sincronização da pasta solicitada ao robô!']);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function toggleArquivoOculto($id)
    {
        try {
            $arquivo = Arquivo::findOrFail($id);
            $arquivo->oculto = ! $arquivo->oculto;
            $arquivo->save();

            return response()->json([
                'status' => 'success',
                'oculto' => $arquivo->oculto,
                'message' => $arquivo->oculto ? 'Arquivo ocultado do cliente.' : 'Arquivo visível para o cliente.',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function togglePastaOculto($id)
    {
        try {
            $pasta = Pasta::findOrFail($id);
            $pasta->oculto = ! $pasta->oculto;
            $pasta->save();

            return response()->json([
                'status' => 'success',
                'oculto' => $pasta->oculto,
                'message' => $pasta->oculto ? 'Pasta ocultada do cliente.' : 'Pasta visível para o cliente.',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getSyncProgress(Request $request)
    {
        $pastaId = $request->query('pasta_id');
        $cacheKey = $pastaId ? 'sync_progress_' . $pastaId : 'sync_progress_global';

        return response()->json(Cache::get($cacheKey, [
            'current' => 0,
            'total' => 0,
            'status' => 'idle',
            'percentage' => 0,
            'last_file' => '',
        ]));
    }

    /** Lista os imóveis (nível 3) de um caminho que aponta para um Ano. */
    private function imoveisDoAno(string $path, string $ano): array
    {
        $pastas = [];

        foreach (new \DirectoryIterator($path) as $categoria) {
            if ($categoria->isDot() || ! $categoria->isDir()) {
                continue;
            }

            foreach (new \DirectoryIterator($categoria->getPathname()) as $imovel) {
                if ($imovel->isDot() || ! $imovel->isDir()) {
                    continue;
                }

                $pastas[] = [
                    'ano' => $ano,
                    'categoria' => $categoria->getBasename(),
                    'imovel' => $imovel->getBasename(),
                    'caminho_completo' => $imovel->getPathname(),
                ];
            }
        }

        return $pastas;
    }

    /** Lista os imóveis (nível 3) de um caminho que aponta para uma Categoria. */
    private function imoveisDaCategoria(string $path, string $ano, string $categoria): array
    {
        $pastas = [];

        foreach (new \DirectoryIterator($path) as $imovel) {
            if ($imovel->isDot() || ! $imovel->isDir()) {
                continue;
            }

            $pastas[] = [
                'ano' => $ano,
                'categoria' => $categoria,
                'imovel' => $imovel->getBasename(),
                'caminho_completo' => $imovel->getPathname(),
            ];
        }

        return $pastas;
    }

    /**
     * Resolve Ano/Categoria/Imóvel a partir do tipo de upload, consumindo as primeiras partes
     * do caminho relativo. Retorna ['resposta' => JsonResponse] quando deve interromper o upload.
     *
     * @param  array<int, string> $partes
     * @return array<string, mixed>
     */
    private function resolverNiveisUpload(string $tipo, array $partes, Request $request): array
    {
        if ($tipo === 'ano') {
            if (count($partes) < 3) {
                return ['resposta' => response()->json(['status' => 'ignored'])];
            }

            return [
                'ano' => array_shift($partes),
                'categoria' => array_shift($partes),
                'imovel' => array_shift($partes),
                'partes' => $partes,
            ];
        }

        if ($tipo === 'categoria') {
            if (count($partes) < 2) {
                return ['resposta' => response()->json(['status' => 'ignored'])];
            }
            $ano = trim($request->ano);
            if (! $ano) {
                return ['resposta' => response()->json(['error' => 'Ano não informado'], 400)];
            }

            return [
                'ano' => $ano,
                'categoria' => array_shift($partes),
                'imovel' => array_shift($partes),
                'partes' => $partes,
            ];
        }

        // tipo === 'imovel'
        if (count($partes) < 1) {
            return ['resposta' => response()->json(['status' => 'ignored'])];
        }
        $ano = trim($request->ano);
        $categoria = trim($request->categoria);
        if (! $ano || ! $categoria) {
            return ['resposta' => response()->json(['error' => 'Ano ou Categoria não informados'], 400)];
        }

        return [
            'ano' => $ano,
            'categoria' => $categoria,
            'imovel' => array_shift($partes),
            'partes' => $partes,
        ];
    }
}
