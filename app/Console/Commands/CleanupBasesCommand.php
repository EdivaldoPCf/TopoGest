<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Base;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CleanupBasesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bases:cleanup {--radius=50 : The radius in meters to consider as duplicate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up duplicate bases within a given radius, keeping only the most recently created one.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $radius = (float) $this->option('radius');
        $this->info("Iniciando pente-fino nas bases (Raio: {$radius} metros)...");

        $bases = Base::orderBy('created_at', 'desc')->get();
        $deletedCount = 0;
        $processedIds = [];

        foreach ($bases as $base) {
            // Se já foi excluída nesta rodada, pule
            if (in_array($base->id, $processedIds) || !$base->exists) {
                continue;
            }

            // Achar vizinhos num raio X
            // Usamos a mesma fórmula de distância euclidiana simples (já que UTM é em metros)
            $nearbyBases = Base::select('*')
                ->selectRaw("SQRT(POW(norte - ?, 2) + POW(este - ?, 2)) AS distancia", [$base->norte, $base->este])
                ->where('id', '!=', $base->id)
                ->having('distancia', '<', $radius)
                ->get();

            foreach ($nearbyBases as $duplicate) {
                // Como iteramos por `created_at desc`, a `$base` atual é a mais nova e será mantida
                // As `$duplicate` são bases mais antigas na mesma área e serão excluídas
                $this->info("Excluindo duplicata: {$duplicate->nome} (Distância: " . round($duplicate->distancia, 2) . "m) - Mantendo: {$base->nome}");
                
                if ($duplicate->arquivo_zip) {
                    Storage::disk('public')->delete($duplicate->arquivo_zip);
                }
                
                $duplicate->delete();
                $processedIds[] = $duplicate->id;
                $deletedCount++;
            }
        }

        $this->info("Pente-fino finalizado. {$deletedCount} bases duplicadas foram removidas.");
    }
}
