<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pastas', function (Blueprint $table) {
            // Adiciona parent_id caso não tenha colocado na migração anterior
            if (!Schema::hasColumn('pastas', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('nome');
                $table->foreign('parent_id')->references('id')->on('pastas')->onDelete('cascade');
            }
            
            // Adiciona a coluna que causou o erro
            if (!Schema::hasColumn('pastas', 'tipo_servico')) {
                $table->enum('tipo_servico', ['pendente', 'finalizado'])->default('pendente')->after('parent_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pastas', function (Blueprint $table) {
            $table->dropColumn(['parent_id', 'tipo_servico']);
        });
    }
};