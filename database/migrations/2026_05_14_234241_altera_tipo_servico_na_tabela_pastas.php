<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE pastas MODIFY COLUMN tipo_servico ENUM('pendente', 'emandamento', 'pronto', 'cancelado') DEFAULT 'pendente'");

            return;
        }

        DB::statement('ALTER TABLE pastas DROP CONSTRAINT IF EXISTS pastas_tipo_servico_check');
        DB::statement("ALTER TABLE pastas ADD CONSTRAINT pastas_tipo_servico_check CHECK (tipo_servico IN ('pendente', 'emandamento', 'pronto', 'cancelado'))");
        DB::statement("ALTER TABLE pastas ALTER COLUMN tipo_servico SET DEFAULT 'pendente'");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE pastas MODIFY COLUMN tipo_servico ENUM('pendente', 'emandamento') DEFAULT 'pendente'");

            return;
        }

        DB::statement('ALTER TABLE pastas DROP CONSTRAINT IF EXISTS pastas_tipo_servico_check');
        DB::statement("ALTER TABLE pastas ADD CONSTRAINT pastas_tipo_servico_check CHECK (tipo_servico IN ('pendente', 'emandamento'))");
        DB::statement("ALTER TABLE pastas ALTER COLUMN tipo_servico SET DEFAULT 'pendente'");
    }
};