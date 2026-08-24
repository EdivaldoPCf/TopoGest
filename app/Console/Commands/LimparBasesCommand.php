<?php

namespace App\Console\Commands;

use App\Models\Base;
use Carbon\Carbon;
use Illuminate\Console\Command;

class LimparBasesCommand extends Command
{
    protected $signature = 'bases:clean {--older-than=} {--missing-files} {--dry-run} {--force}';

    protected $description = 'Limpa bases antigas ou sem arquivo associado do banco de dados.';

    public function handle()
    {
        $olderThan = $this->option('older-than');
        $missingFiles = $this->option('missing-files');

        if (! $olderThan && ! $missingFiles) {
            $this->error('Especifique pelo menos uma opção: --older-than=<dias> ou --missing-files');

            return 1;
        }

        $this->info('Procurando bases para limpeza...');

        $query = Base::query();
        if ($olderThan) {
            $query->where('created_at', '<', Carbon::now()->subDays((int) $olderThan));
        }

        $aExcluir = $query->get()->filter(function ($base) use ($missingFiles) {
            if (! $missingFiles) {
                return true;
            }

            return empty($base->arquivo_zip) || ! @file_exists(storage_path('app/public/' . $base->arquivo_zip));
        })->values();

        if ($aExcluir->isEmpty()) {
            $this->info('Nenhuma base encontrada para os critérios informados.');

            return 0;
        }

        $this->info('Bases encontradas: ' . $aExcluir->count());
        foreach ($aExcluir as $b) {
            $this->line(" - {$b->id} | {$b->nome} | norte={$b->norte} este={$b->este} arquivo={$b->arquivo_zip}");
        }

        if ($this->option('dry-run')) {
            $this->info('Dry-run ativado — nenhuma alteração será feita.');

            return 0;
        }

        if (! $this->option('force') && ! $this->confirm('Confirma exclusão das bases listadas?')) {
            $this->info('Operação cancelada.');

            return 0;
        }

        $removidas = 0;
        foreach ($aExcluir as $b) {
            if (! empty($b->arquivo_zip) && file_exists(storage_path('app/public/' . $b->arquivo_zip))) {
                @unlink(storage_path('app/public/' . $b->arquivo_zip));
            }

            $b->delete();
            $removidas++;
        }

        $this->info("Removidas: {$removidas} base(s)");

        return 0;
    }
}
