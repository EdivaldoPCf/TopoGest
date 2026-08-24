<?php

namespace App\Console\Commands;

use App\Models\Arquivo;
use App\Models\Marco;
use App\Models\Pasta;
use App\Models\User;
use App\Services\SigefOdsParserService;
use FilesystemIterator;
use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Importa o fluxo completo (Ano > Categoria > Imóvel) de um diretório local,
 * inferindo o cliente pelo nome da pasta e/ou CPF dos arquivos, replicando a
 * estrutura e cadastrando os marcos encontrados.
 *
 * Antes vivia como uma closure de ~360 linhas em routes/console.php que declarava
 * funções globais via function_exists().
 */
class ImportarFluxoPastasCommand extends Command
{
    protected $signature = 'pastas:import {directory}';

    protected $description = 'Importa o fluxo de pastas, arquivos e marcos de um ano a partir de um diretório local.';

    /** Extensões de texto vasculhadas em busca de códigos de marcos. */
    private const EXTENSOES_TEXTO = ['txt', 'kml', 'pos', 'sum', 'inf', 'rw5', 'crd', 'csv'];

    /** Prefixos de 3 letras que parecem credencial mas não são. */
    private const FALSAS_CREDENCIAIS = ['XML', 'PDF', 'ZIP', 'PNG', 'JPG', 'CPY', 'TXT'];

    private int $pastasCriadas = 0;
    private int $arquivosImportados = 0;
    private int $marcosCriados = 0;

    public function handle()
    {
        $directory = trim($this->argument('directory'), '"\'');
        $realDirectory = realpath($directory);

        if (! $realDirectory || ! is_dir($realDirectory)) {
            $this->error("Diretório inválido: {$directory}");

            return 1;
        }

        $this->info("Iniciando importação do fluxo de pastas de: {$realDirectory}");

        $anoNome = basename($realDirectory);
        $anoFolder = Pasta::firstOrCreate(['nome' => $anoNome, 'parent_id' => null], ['tipo_servico' => 'pendente']);
        $this->info("Pasta de Ano criada/recuperada: {$anoNome} (ID: {$anoFolder->id})");

        foreach (array_filter(glob($realDirectory . '/*'), 'is_dir') as $catPath) {
            $catName = basename($catPath);
            $catFolder = Pasta::firstOrCreate(['nome' => $catName, 'parent_id' => $anoFolder->id], ['tipo_servico' => 'pendente']);
            $this->info("  Categoria criada/recuperada: {$catName} (ID: {$catFolder->id})");

            foreach (array_filter(glob($catPath . '/*'), 'is_dir') as $servicePath) {
                $this->importarImovel($servicePath, $catFolder, $catName);
            }
        }

        $this->info('Importação concluída com sucesso!');
        $this->info("Pastas criadas/verificadas: {$this->pastasCriadas}");
        $this->info("Arquivos importados: {$this->arquivosImportados}");
        $this->info("Marcos identificados e salvos: {$this->marcosCriados}");

        return 0;
    }

    /** Cria a pasta do imóvel (resolvendo o cliente) e importa seu conteúdo. */
    private function importarImovel(string $servicePath, Pasta $catFolder, string $catName): void
    {
        $serviceFolderName = basename($servicePath);
        $parsed = $this->parsearDono($serviceFolderName);

        $cliente = $this->resolverCliente($servicePath, $serviceFolderName, $catName, $parsed['cliente']);

        $serviceFolder = Pasta::firstOrCreate(
            ['nome' => $parsed['imovel'], 'parent_id' => $catFolder->id],
            [
                'tipo_servico' => 'pendente',
                'cliente_id' => $cliente['id'],
                'identificador_cliente' => $cliente['identificador'],
                'categoria_servico' => $catName,
            ]
        );
        $this->pastasCriadas++;

        // Para projetos sem cliente associado usa-se o usuário 1 (sistema) como dono dos marcos.
        $dono = $cliente['model'] ?? tap(new User(), fn ($u) => $u->id = 1);

        $this->importarSubpastasEArquivos($servicePath, $serviceFolder, $dono);
    }

    /**
     * Determina cliente/identificador do imóvel a partir do CPF dos arquivos ou do nome da pasta.
     *
     * @return array{id: ?int, identificador: ?string, model: ?User}
     */
    private function resolverCliente(string $servicePath, string $serviceFolderName, string $catName, string $clienteNome): array
    {
        $vazio = ['id' => null, 'identificador' => null, 'model' => null];

        if (preg_match('/\b(INCRA|INTERACRE|FAPEC|TERRA LEGAL|GOVERNO|PREFEITURA)\b/i', $catName . ' ' . $serviceFolderName)) {
            return $vazio;
        }

        $adminCpfs = User::where('role', 'admin')->pluck('cpf')->toArray();
        $cpf = $this->extrairCpfDeArquivos($this->listarArquivos($servicePath), $adminCpfs);

        if ($cpf) {
            $cliente = User::where('cpf', $cpf)->first();

            return $cliente
                ? ['id' => $cliente->id, 'identificador' => $cliente->cpf, 'model' => $cliente]
                : ['id' => null, 'identificador' => $cpf, 'model' => null];
        }

        if ($clienteNome === 'Aguardando Cliente') {
            return $vazio;
        }

        $cliente = User::where('name', $clienteNome)->first();

        return $cliente
            ? ['id' => $cliente->id, 'identificador' => $cliente->cpf, 'model' => $cliente]
            : $vazio;
    }

    /** @return array<int, string> */
    private function listarArquivos(string $servicePath): array
    {
        $arquivos = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($servicePath));
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $arquivos[] = $file->getRealPath();
            }
        }

        return $arquivos;
    }

    /**
     * Separa nome do imóvel e nome do cliente a partir do nome da pasta de serviço.
     *
     * @return array{imovel: string, cliente: string}
     */
    private function parsearDono(string $folderName): array
    {
        $imovelNome = $folderName;
        $clienteNome = null;
        $marcadores = '/(?:^|\s|_|-)(orçando|orcando|divisa|ok|pendente)(?:\s|_|-|$)/iu';

        if (preg_match('/^(.*?)\s*\((.*?)\)$/', $folderName, $matches)) {
            $imovelNome = trim($matches[1]);
            $ownerPart = trim($matches[2]);
            $clienteNome = trim(preg_replace('/\s+/', ' ', preg_replace('/\b(desmembramento|gleba|lote|proprietario|dono)\b/i', '', $ownerPart)));
        } elseif (preg_match('/^(.*?)\s*-\s*(.*?)$/', $folderName, $matches)) {
            $imovelNome = trim($matches[1]);
            $clienteNome = preg_match($marcadores, trim($matches[2])) ? 'Aguardando Cliente' : trim($matches[2]);
        }

        if (strpos($folderName, ' - ') !== false) {
            $parts = array_map('trim', explode(' - ', $folderName));
            $clienteNome = preg_match($marcadores, end($parts)) ? 'Aguardando Cliente' : end($parts);
        }

        if (empty($clienteNome) || $clienteNome === $folderName) {
            if (strpos($folderName, '_') !== false) {
                $parts = explode('_', $folderName);
                $clienteNome = trim($parts[0]);
                $imovelNome = str_replace('_', ' ', $folderName);
            } else {
                $clienteNome = preg_match($marcadores, $folderName) ? 'Aguardando Cliente' : $folderName;
            }
        }

        return [
            'imovel' => $imovelNome ?: $folderName,
            'cliente' => $clienteNome ?: 'Aguardando Cliente',
        ];
    }

    /**
     * Procura um CPF/CNPJ nos nomes dos arquivos e, em último caso, dentro dos ODS.
     *
     * @param  array<int, string> $files
     * @param  array<int, string> $adminCpfs
     */
    private function extrairCpfDeArquivos(array $files, array $adminCpfs = []): ?string
    {
        $odsFiles = [];

        foreach ($files as $file) {
            $filename = basename($file);
            if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'ods') {
                $odsFiles[] = $file;
            }

            $found = null;
            if (preg_match('/\b\d{11}\b/', $filename, $m)) {
                $found = $m[0];
            } elseif (preg_match('/\b\d{3}\.\d{3}\.\d{3}-\d{2}\b/', $filename, $m)) {
                $found = preg_replace('/[^0-9]/', '', $m[0]);
            } elseif (preg_match('/\b\d{14}\b/', $filename, $m)) {
                $found = $m[0];
            } elseif (preg_match('/\b\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}\b/', $filename, $m)) {
                $found = preg_replace('/[^0-9]/', '', $m[0]);
            }

            if ($found && ! in_array($found, $adminCpfs)) {
                return $found;
            }
        }

        if (! empty($odsFiles)) {
            $parser = app(SigefOdsParserService::class);
            foreach ($odsFiles as $odsFile) {
                try {
                    $dados = $parser->parseOdsFile($odsFile);
                    $cpfRaw = preg_replace('/[^0-9]/', '', $dados['identificacao']['cpf_cnpj'] ?? '');
                    if ($cpfRaw && ! in_array($cpfRaw, $adminCpfs)) {
                        return $cpfRaw;
                    }
                } catch (\Exception $e) {
                    // ignora ODS ilegível
                }
            }
        }

        return null;
    }

    /** Replica recursivamente subpastas e arquivos no banco/Storage e cadastra marcos. */
    private function importarSubpastasEArquivos(string $dirPath, Pasta $parentFolder, User $cliente): void
    {
        $storageRoot = storage_path('app/public/documentos');
        if (! is_dir($storageRoot)) {
            mkdir($storageRoot, 0755, true);
        }

        foreach (new FilesystemIterator($dirPath) as $item) {
            $name = $item->getFilename();

            if ($item->isDir()) {
                $subFolder = Pasta::firstOrCreate([
                    'nome' => $name,
                    'parent_id' => $parentFolder->id,
                    'tipo_servico' => $parentFolder->tipo_servico,
                    'cliente_id' => $parentFolder->cliente_id,
                    'identificador_cliente' => $parentFolder->identificador_cliente,
                    'categoria_servico' => $parentFolder->categoria_servico,
                ]);

                if (preg_match('/gnss|metrica|métrica/i', $name) || ($parentFolder->oculto ?? 0)) {
                    $subFolder->oculto = 1;
                    $subFolder->save();
                }

                $this->pastasCriadas++;
                $this->importarSubpastasEArquivos($item->getRealPath(), $subFolder, $cliente);

                continue;
            }

            $filePath = $item->getRealPath();
            if (! $filePath) {
                continue;
            }

            $this->copiarArquivo($item, $name, $parentFolder);
            $this->extrairMarcoDoNome($name, $cliente->id, $parentFolder->nome ?: 'Imóvel Geral');
            $this->extrairMarcosDoConteudo($item, $filePath, $cliente->id, $parentFolder->nome ?: 'Imóvel Geral');
        }
    }

    private function copiarArquivo(\SplFileInfo $item, string $name, Pasta $parentFolder): void
    {
        if (Arquivo::where('nome', $name)->where('pasta_id', $parentFolder->id)->exists()) {
            return;
        }

        $ext = strtolower($item->getExtension());
        $newFileName = md5(uniqid() . $name) . (empty($ext) ? '' : '.' . $ext);
        $targetPath = 'documentos/' . $newFileName;
        copy($item->getRealPath(), storage_path('app/public/' . $targetPath));

        $oculto = ($parentFolder->oculto ?? 0) || $ext === 'topo' || preg_match('/topos\.zip$/i', $name) ? 1 : 0;

        Arquivo::create([
            'nome' => $name,
            'path' => $targetPath,
            'tamanho' => round($item->getSize() / 1024 / 1024, 2),
            'tipo' => strtoupper($ext),
            'pasta_id' => $parentFolder->id,
            'oculto' => $oculto,
        ]);
        $this->arquivosImportados++;
    }

    private function extrairMarcoDoNome(string $name, int $userId, string $imovelNome): void
    {
        if (! preg_match('/\b([A-Za-z]{3})[-_]?([MVPmvp])[-_]?(\d+)\b/', $name, $matches)) {
            return;
        }

        $this->criarMarco(strtoupper($matches[1]), strtoupper($matches[2]), (int) $matches[3], $userId, $imovelNome);
    }

    private function extrairMarcosDoConteudo(\SplFileInfo $item, string $filePath, int $userId, string $imovelNome): void
    {
        $ext = strtolower($item->getExtension());
        if (! in_array($ext, self::EXTENSOES_TEXTO) || $item->getSize() >= 200000) {
            return;
        }

        $content = @file_get_contents($filePath);
        if ($content === false) {
            return;
        }

        preg_match_all('/\b([A-Za-z]{3})[-_]?([MVPmvp])[-_]?(\d{1,5})(?![0-9])/i', $content, $matches, PREG_SET_ORDER);
        foreach ($matches as $m) {
            $this->criarMarco(strtoupper($m[1]), strtoupper($m[2]), (int) $m[3], $userId, $imovelNome);
        }
    }

    /** Cria um marco se a credencial for válida e ele ainda não existir. */
    private function criarMarco(string $credencial, string $tipo, int $numero, int $userId, string $imovelNome): void
    {
        if (in_array($credencial, self::FALSAS_CREDENCIAIS) || $numero <= 0 || $numero > 99999) {
            return;
        }

        $existe = Marco::where('credencial', $credencial)
            ->where('tipo', $tipo)
            ->where('numero', $numero)
            ->exists();

        if ($existe) {
            return;
        }

        Marco::create([
            'user_id' => $userId,
            'credencial' => $credencial,
            'tipo' => $tipo,
            'numero' => $numero,
            'imovel' => $imovelNome,
        ]);
        $this->marcosCriados++;
    }
}
