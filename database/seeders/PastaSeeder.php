<?php

namespace Database\Seeders;

use App\Models\Pasta;
use Illuminate\Database\Seeder;

class PastaSeeder extends Seeder
{
    public function run(): void
{
    $anos = ['2026', '2025', '2024'];
    $tipos = ['LEI 3ª Edição', 'Particular', 'INCRA - SR 14'];

    foreach ($anos as $ano) {
        $pastaAno = \App\Models\Pasta::create(['nome' => $ano]);

        foreach ($tipos as $tipo) {
            \App\Models\Pasta::create([
                'nome' => $tipo,
                'parent_id' => $pastaAno->id // Vincula o tipo ao ano
            ]);
        }
    }
}
}