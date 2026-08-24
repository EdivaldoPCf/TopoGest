<?php

namespace App\Console\Commands;

use App\Events\SyncProgressUpdated;
use App\Models\Configuracao;
use App\Models\Pasta;
use App\Services\Importacao\ImportadorMarcos;
use App\Services\Importacao\ImportadorPasta;
use FilesystemIterator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class SyncPendentesCommand extends Command
{
    protected $signature = 'pastas:sync-pendentes {--pasta_id= : ID da pasta especifica para sincronizar}';

    protected $description = 'Sincroniza automaticamente as pastas pendentes buscando arquivos novos no computador';

    public function handle(ImportadorPasta $importador, ImportadorMarcos $marcos)
    {
        $pastaId = $this->option('pasta_id');
        $cacheKey = $pastaId ? 'sync_progress_' . $pastaId : 'sync_progress_global';

        try {
            $ativo = Configuracao::where('chave', 'sync_ativo')->first()?->valor;
            $rootDir = Configuracao::where('chave', 'sync_root_dir')->first()?->valor;

            if (! $pastaId && $ativo !== '1') {
                $this->info('Sincronização desativada nas configurações.');

                return;
            }

            if (empty($rootDir) || ! is_dir($rootDir)) {
                $mensagem = "Caminho raiz inválido ou não configurado: {$rootDir}";
                $this->error($mensagem);
                $this->publicarProgresso($cacheKey, 0, 0, 0, $mensagem, 'error');

                return;
            }

            $this->info('Iniciando sincronização...');

            $caminhos = $this->mapearCaminhos($this->buscarImoveis($pastaId), $rootDir);
            $totalArquivos = $this->contarArquivos($caminhos);

            $this->publicarProgresso($cacheKey, 0, 0, $totalArquivos, '', 'running');

            $progressoAtual = 0;
            $sincronizados = 0;

            foreach ($caminhos as $item) {
                $imovel = $item['imovel'];
                $this->info("Sincronizando: {$imovel->nome}");

                $resultado = $importador->importarDiretorio(
                    $item['caminho'],
                    $imovel,
                    'pendente',
                    1, // marcos pertencem ao sistema
                    true, // substitui arquivos já existentes
                    function (string $nomeArquivo) use (&$progressoAtual, $totalArquivos, $cacheKey) {
                        $progressoAtual++;
                        $percentual = $totalArquivos > 0 ? round(($progressoAtual / $totalArquivos) * 100) : 0;
                        $this->publicarProgresso($cacheKey, $percentual, $progressoAtual, $totalArquivos, $nomeArquivo, 'running');
                    }
                );

                $sincronizados += $resultado['novos'];
                $importador->definirClienteSeVazio($imovel, $resultado['cpfs'][0] ?? null);
                $marcos->salvarVerticesTexto($resultado['vertices'], $imovel->nome, 1);
            }

            $this->publicarProgresso($cacheKey, 100, $totalArquivos, $totalArquivos, 'Finalizado', 'done');
            $this->info("Sincronização concluída. Novos arquivos: {$sincronizados}");
        } catch (\Throwable $e) {
            Log::error('Erro na execução do comando pastas:sync-pendentes: ' . $e->getMessage());
            $this->publicarProgresso($cacheKey, 0, 0, 0, 'Erro: ' . $e->getMessage(), 'error');
        }
    }

    /** Pastas de imóvel (nível 3): específica quando informada, ou todas as pendentes. */
    private function buscarImoveis(?string $pastaId)
    {
        $query = Pasta::whereHas('parent', function ($q) {
            $q->whereHas('parent', function ($q2) {
                $q2->whereNull('parent_id');
            });
        });

        if ($pastaId) {
            $query->where('id', $pastaId);
        } else {
            $query->where('tipo_servico', 'pendente');
        }

        return $query->get();
    }

    /**
     * Resolve o caminho físico de cada imóvel e mantém apenas os que existem em disco.
     *
     * @return array<int, array{imovel: Pasta, caminho: string}>
     */
    private function mapearCaminhos($imoveis, string $rootDir): array
    {
        $caminhos = [];

        foreach ($imoveis as $imovel) {
            $categoria = $imovel->parent->nome;
            $ano = $imovel->parent->parent->nome;
            $caminho = $rootDir . DIRECTORY_SEPARATOR . $ano . DIRECTORY_SEPARATOR . $categoria . DIRECTORY_SEPARATOR . $imovel->nome;

            if (is_dir($caminho)) {
                $caminhos[] = ['imovel' => $imovel, 'caminho' => $caminho];
            }
        }

        return $caminhos;
    }

    /** Conta os arquivos (ignorando temporários) para alimentar a barra de progresso. */
    private function contarArquivos(array $caminhos): int
    {
        $total = 0;

        foreach ($caminhos as $item) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($item['caminho'], FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                if (! $file->isFile()) {
                    continue;
                }

                $basename = strtolower($file->getBasename());
                if (str_starts_with($file->getFilename(), '~$') || $basename === 'thumbs.db') {
                    continue;
                }

                $total++;
            }
        }

        return $total;
    }

    /** Atualiza o progresso no cache e dispara o evento de broadcast. */
    private function publicarProgresso(string $cacheKey, int $percentual, int $atual, int $total, string $ultimoArquivo, string $status): void
    {
        Cache::put($cacheKey, [
            'current' => $atual,
            'total' => $total,
            'status' => $status,
            'percentage' => $percentual,
            'last_file' => $ultimoArquivo,
        ], 300);

        event(new SyncProgressUpdated($percentual, $atual, $total, $ultimoArquivo, $status));
    }
}
