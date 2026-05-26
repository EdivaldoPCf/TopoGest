<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'cpf')) {
            $table->string('cpf')->nullable()->unique()->after('email');
        }
        if (!Schema::hasColumn('users', 'cnpj')) {
            $table->string('cnpj')->nullable()->unique()->after('cpf');
        }
    });
}

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['cpf', 'cnpj']);
        });
    }
};