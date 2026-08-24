<?php

namespace App\Services\Importacao;

use App\Support\BinarioPhp;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Aciona o "robô" local de sincronização de pastas.
 *
 * Em Docker/Linux apenas escreve um arquivo de trigger lido pelo worker do host
 * Windows; rodando nativamente no Windows também dispara o artisan diretamente.
 * Antes essa lógica (e a detecção do binário PHP) estava repetida em syncAgora,
 * syncPastaUnica e runPowerShell.
 */
class SincronizadorRobo
{
    /** Solicita a sincronização de todas as pastas pendentes. */
    public function solicitarGlobal(): void
    {
        Cache::put('sync_progress_global', $this->estadoInicial(), 300);

        $trigger = storage_path('sync_request.txt');
        file_put_contents($trigger, '1');
        Log::info('Escreveu arquivo de trigger de sync global em: ' . $trigger);

        $this->dispararArtisan('pastas:sync-pendentes');
    }

    /** Solicita a sincronização de uma pasta específica. */
    public function solicitarPasta(int|string $pastaId): void
    {
        Cache::put('sync_progress_' . $pastaId, $this->estadoInicial(), 300);

        $trigger = storage_path('sync_request_pasta.txt');
        file_put_contents($trigger, $pastaId);
        Log::info("Escreveu arquivo de trigger de pasta única ({$pastaId}) em: " . $trigger);

        $this->dispararArtisan('pastas:sync-pendentes --pasta_id=' . escapeshellarg((string) $pastaId));
    }

    /** Abre uma janela visível do CMD para rodar a importação de pastas (somente Windows). */
    public function executarImportacaoEmJanela(string $caminho): void
    {
        $php = BinarioPhp::caminho();
        $artisan = base_path('artisan');

        $cmd = 'start "Importacao PowerShell" cmd /c """' . escapeshellarg($php) . '" "' . escapeshellarg($artisan)
            . '" pastas:import "' . escapeshellcmd($caminho)
            . '" && echo. && echo Importacao concluida. Pode fechar esta janela. && pause"';

        pclose(popen($cmd, 'r'));
    }

    public function ehWindows(): bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    /** Dispara o artisan em segundo plano no host Windows (no-op em outros sistemas). */
    private function dispararArtisan(string $comando): void
    {
        if (! $this->ehWindows()) {
            return;
        }

        $php = BinarioPhp::caminho();
        $artisan = base_path('artisan');

        $cmd = 'start /B "" ' . escapeshellarg($php) . ' ' . escapeshellarg($artisan) . ' ' . $comando . ' > NUL 2>&1';
        Log::info('Iniciando sync direto no Windows: ' . $cmd);
        pclose(popen($cmd, 'r'));
    }

    /** @return array<string, mixed> */
    private function estadoInicial(): array
    {
        return [
            'current' => 0,
            'total' => 0,
            'status' => 'starting',
            'percentage' => 0,
            'last_file' => 'Aguardando inicialização do robô local...',
        ];
    }
}
