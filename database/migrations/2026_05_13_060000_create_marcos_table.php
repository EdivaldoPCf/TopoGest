<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('marcos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('credencial'); // <--- ESSA COLUNA
        $table->string('tipo');       // <--- ESSA COLUNA
        $table->integer('numero');    
        $table->string('imovel');
        $table->timestamps();

        $table->unique(['credencial', 'tipo', 'numero']);
    });
}
    /**
     * Reverse the migrations.
     */
public function down(): void
    {
        Schema::dropIfExists('marcos');
    }
};
