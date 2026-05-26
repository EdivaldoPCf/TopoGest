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
    Schema::table('pastas', function (Blueprint $table) {
        $table->unsignedBigInteger('cliente_id')->nullable()->after('parent_id');
        $table->string('identificador_cliente')->nullable()->after('cliente_id'); // CPF, CNPJ ou Email
        $table->string('categoria_servico')->nullable()->after('identificador_cliente'); // Geo, Desmembramento...
        
        $table->foreign('cliente_id')->references('id')->on('users')->onDelete('set null');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pastas', function (Blueprint $table) {
            //
        });
    }
};

