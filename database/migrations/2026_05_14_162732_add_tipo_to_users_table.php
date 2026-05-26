<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Verifica se a coluna já existe antes de tentar criar
        if (!Schema::hasColumn('users', 'tipo')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('tipo', 2)->default('PF')->after('cpf');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'tipo')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('tipo');
            });
        }
    }
};