<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabela de Arquivos vinculada a uma Pasta
        Schema::create('arquivos', function (Blueprint $table) {
            $table->id();
            $table->string('nome_original');
            $table->string('caminho');
            $table->string('tipo'); // pdf, dwg, etc
            $table->foreignId('pasta_id')->constrained('pastas')->onDelete('cascade');
            $table->timestamps();
        });

        // Tabela de Pendências vinculada a uma Pasta
        Schema::create('pendencias', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->enum('status', ['pendente', 'resolvido'])->default('pendente');
            $table->foreignId('pasta_id')->constrained('pastas')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendencias');
        Schema::dropIfExists('arquivos');
    }
};