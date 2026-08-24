<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->timestamp('admin_assinado_em')->nullable();
            $table->string('admin_assinatura_hash', 64)->nullable();
            $table->string('admin_assinante_nome')->nullable();
            $table->string('admin_assinante_ip', 45)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->dropColumn([
                'admin_assinado_em',
                'admin_assinatura_hash',
                'admin_assinante_nome',
                'admin_assinante_ip'
            ]);
        });
    }
};
