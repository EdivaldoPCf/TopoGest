<?php

namespace App\Services\Importacao;

use App\Models\Arquivo;
use App\Models\Pasta;
use App\Models\User;
use App\Services\BaseProcessorService;
use FilesystemIterator;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Motor de importação de uma pasta de imóvel para o banco/Storage.
 *
 * Concentra a lógica que estava triplicada e quase idêntica em
 * AdminImportacaoController::processarPasta, AdminImportacaoController::uploadWeb
 * e SyncPendentesCommand::syncFolderContent: varredura do diretório, criação da
 * árvore de pastas, cópia dos arquivos e extração de CPF/vértices/marcos.
 */
class ImportadorPasta
{
    public function __construct(
        private FiltroImportacao $filtro,
        private ExtratorDadosDocumento $extrator,
        private ImportadorMarcos $marcos,
        private BaseProcessorService $baseProcessor,
    ) {
    }

    /**
     * Varre um diretório físico recursivamente, replicando sua estrutura em pastas/arquivos
     * e extraindo CPFs e vértices encontrados.
     *
     * @param  string        $statusPadrao         tipo_servico aplicado às pastas criadas ('pronto'|'pendente')
     * @param  ?int          $userIdMarcos         dono dos marcos criados (null => sistema/1)
     * @param  bool          $substituirExistentes remove o arquivo anterior antes de recriar (modo sync)
     * @param  ?callable     $onProgresso          callback(string $nomeArquivo) chamado por arquivo processado
     * @return array{novos: int, cpfs: array<int, string>, vertices: array<int, string>}
     */
    public function importarDiretorio(
        string $caminhoFisico,
        Pasta $pastaImovel,
        string $statusPadrao,
        ?int $userIdMarcos = null,
        bool $substituirExistentes = false,
        ?callable $onProgresso = null
    ): array {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($caminhoFisico, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $caminhoNormalizado = str_replace('\\', '/', $caminhoFisico);
        $mapaPastas = ['' => $pastaImovel->id];
        $cpfs = [];
        $vertices = [];
        $novos = 0;

        foreach ($iterator as $file) {
            $relativo = $this->caminhoRelativo($file->getPathname(), $caminhoNormalizado);

            [$pastaOculta, $ehPpp] = $this->analisarPartes($relativo);

            if ($file->isDir()) {
                $this->criarArvore($pastaImovel->id, $relativo, $statusPadrao, $pastaOculta, $mapaPastas);
                continue;
            }

            $ext = strtolower($file->getExtension());
            $basename = strtolower($file->getBasename());

            if ($this->filtro->ehTemporario($file->getFilename(), $basename)) {
                continue;
            }

            if ($onProgresso) {
                $onProgresso($file->getBasename());
            }

            $oculto = $pastaOculta || $this->filtro->extensaoIgnorada($ext, $basename);

            if ($ehPpp && $ext === 'zip' && str_contains($basename, '@')) {
                $this->baseProcessor->processPppZip($file->getPathname());
                $oculto = true;
            }

            $dados = $this->extrairDados($file->getPathname(), $ext, $pastaImovel->nome, $userIdMarcos);
            if ($dados['cpf']) {
                $cpfs[] = $dados['cpf'];
            }
            $vertices = array_merge($vertices, $dados['vertices']);

            $relativoPai = $this->caminhoRelativo($file->getPath(), $caminhoNormalizado);
            $pastaId = $this->criarArvore($pastaImovel->id, $relativoPai, $statusPadrao, $oculto, $mapaPastas);

            if ($substituirExistentes) {
                $this->removerArquivoExistente($file->getBasename(), $pastaId);
            }

            $this->salvarArquivo(
                $pastaId,
                $file->getBasename(),
                file_get_contents($file->getPathname()),
                round($file->getSize() / 1024 / 1024, 2),
                $ext,
                $oculto
            );
            $novos++;
        }

        return [
            'novos' => $novos,
            'cpfs' => array_values(array_unique($cpfs)),
            'vertices' => $vertices,
        ];
    }

    /**
     * Extrai CPF/vértices de um arquivo e, no caso de ODS, importa também os marcos.
     *
     * @return array{cpf: ?string, vertices: array<int, string>}
     */
    public function extrairDados(string $caminho, string $ext, string $imovel, ?int $userIdMarcos): array
    {
        if ($ext === 'ods') {
            $this->marcos->importarDeOds($caminho, $imovel, $userIdMarcos);
        }

        return $this->extrator->extrair($caminho, $ext);
    }

    /**
     * Garante que toda a árvore de pastas do caminho relativo exista, retornando o id da folha.
     *
     * @param  array<string, int> $mapaPastas cache 'caminho/relativo' => id (passado por referência)
     */
    public function criarArvore(int $raizId, string $relativo, string $statusPadrao, bool $oculto, array &$mapaPastas): int
    {
        $parentId = $raizId;

        if ($relativo === '') {
            return $parentId;
        }

        $caminhoAcumulado = '';
        foreach (explode('/', $relativo) as $parte) {
            if (trim($parte) === '') {
                continue;
            }

            $caminhoAcumulado .= ($caminhoAcumulado === '' ? '' : '/') . $parte;

            if (! isset($mapaPastas[$caminhoAcumulado])) {
                $pasta = Pasta::firstOrCreate(
                    ['nome' => $parte, 'parent_id' => $parentId],
                    ['tipo_servico' => $statusPadrao, 'oculto' => $oculto ? 1 : 0]
                );
                $mapaPastas[$caminhoAcumulado] = $pasta->id;
            }

            $parentId = $mapaPastas[$caminhoAcumulado];
        }

        return $parentId;
    }

    /** Copia o conteúdo para o disco público e cria o registro de Arquivo. */
    public function salvarArquivo(int $pastaId, string $nome, string $conteudo, float $tamanhoMb, string $ext, bool $oculto): Arquivo
    {
        $caminho = 'arquivos/' . $pastaId . '/' . uniqid() . '_' . $nome;
        Storage::disk('public')->put($caminho, $conteudo);

        return Arquivo::create([
            'pasta_id' => $pastaId,
            'nome' => $nome,
            'path' => $caminho,
            'tamanho' => $tamanhoMb,
            'tipo' => strtoupper($ext),
            'oculto' => $oculto ? 1 : 0,
        ]);
    }

    /**
     * Associa um cliente à pasta a partir de um CPF/CNPJ (define o titular ou adiciona
     * como cliente secundário). Usado na importação manual, que percorre todos os CPFs.
     */
    public function associarClientePorCpf(Pasta $pasta, string $cpf): void
    {
        $documento = preg_replace('/[^0-9]/', '', $cpf);
        $cliente = User::where('cpf', $documento)->orWhere('cnpj', $documento)->first();

        if ($cliente) {
            if (empty($pasta->cliente_id)) {
                $pasta->identificador_cliente = $documento;
                $pasta->cliente_id = $cliente->id;
                $pasta->save();
            } elseif ($pasta->cliente_id !== $cliente->id) {
                $pasta->clientesSecundarios()->syncWithoutDetaching([$cliente->id]);
            }

            return;
        }

        if (empty($pasta->identificador_cliente)) {
            $pasta->identificador_cliente = $documento;
            $pasta->save();
        }
    }

    /**
     * Define o cliente da pasta apenas se ela ainda não tiver titular (usado pelo sincronizador,
     * que considera somente o primeiro CPF encontrado).
     */
    public function definirClienteSeVazio(Pasta $pasta, ?string $cpf): void
    {
        if (! $cpf || $pasta->identificador_cliente) {
            return;
        }

        $documento = preg_replace('/[^0-9]/', '', $cpf);
        $pasta->identificador_cliente = $documento;

        $cliente = User::where('cpf', $documento)->orWhere('cnpj', $documento)->first();
        if ($cliente) {
            $pasta->cliente_id = $cliente->id;
        }

        $pasta->save();
    }

    /** Caminho relativo (com '/' como separador) de um item em relação à raiz informada. */
    private function caminhoRelativo(string $caminhoAbsoluto, string $raizNormalizada): string
    {
        return trim(str_replace($raizNormalizada, '', str_replace('\\', '/', $caminhoAbsoluto)), '/');
    }

    /**
     * Verifica nas partes do caminho relativo se há pasta ignorada e/ou pasta PPP.
     *
     * @return array{0: bool, 1: bool} [pastaOculta, ehPpp]
     */
    private function analisarPartes(string $relativo): array
    {
        $oculta = false;
        $ppp = false;

        if ($relativo !== '') {
            foreach (explode('/', $relativo) as $parte) {
                if ($this->filtro->pastaIgnorada($parte)) {
                    $oculta = true;
                }
                if ($this->filtro->ehPastaPpp($parte)) {
                    $ppp = true;
                }
            }
        }

        return [$oculta, $ppp];
    }

    /** Remove o arquivo (físico e registro) anterior de mesmo nome na pasta, se existir. */
    private function removerArquivoExistente(string $nome, int $pastaId): void
    {
        $existente = Arquivo::where('nome', $nome)->where('pasta_id', $pastaId)->first();

        if ($existente) {
            @unlink(storage_path('app/public/' . $existente->path));
            $existente->delete();
        }
    }
}
