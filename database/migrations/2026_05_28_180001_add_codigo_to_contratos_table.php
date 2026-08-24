<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            // Nullable primeiro para não quebrar registros existentes
            $table->string('codigo_contrato', 20)->nullable()->unique()->after('id');
        });

        // Popula contratos já existentes com um código retroativo
        $contratos = DB::table('contratos')->whereNull('codigo_contrato')->orderBy('id')->get();
        foreach ($contratos as $c) {
            $ano = date('Y', strtotime($c->created_at));
            $seq = DB::table('contratos')
                ->whereYear('created_at', $ano)
                ->where('id', '<=', $c->id)
                ->count();
            DB::table('contratos')->where('id', $c->id)->update([
                'codigo_contrato' => 'GETEC/' . $ano . '/' . str_pad($seq, 5, '0', STR_PAD_LEFT),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->dropUnique(['codigo_contrato']);
            $table->dropColumn('codigo_contrato');
        });
    }
};
