<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bases', function (Blueprint $table) {
            $table->string('nome')->nullable();
            $table->double('norte')->nullable();
            $table->double('este')->nullable();
            $table->string('norte_original')->nullable();
            $table->string('este_original')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('arquivo_zip')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bases', function (Blueprint $table) {
            $table->dropColumn(['nome', 'norte', 'este', 'norte_original', 'este_original', 'latitude', 'longitude', 'arquivo_zip']);
        });
    }
};