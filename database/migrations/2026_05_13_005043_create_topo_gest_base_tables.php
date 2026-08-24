<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pastas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            // Referência para a própria tabela para criar a hierarquia infinita
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('pastas')
                  ->onDelete('cascade'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // O down deve apenas remover o que o up criou
        Schema::dropIfExists('pastas');
    }
};