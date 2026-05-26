<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE pastas MODIFY COLUMN tipo_servico ENUM('pendente', 'emandamento', 'pronto', 'cancelado') DEFAULT 'pendente'");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE pastas MODIFY COLUMN tipo_servico ENUM('pendente', 'emandamento') DEFAULT 'pendente'");
        }
    }
};