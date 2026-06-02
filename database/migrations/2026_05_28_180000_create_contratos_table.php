<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contratos', function (Blueprint $table) {
            $table->id();

            // Dados do Contratante (Proprietário / Cliente)
            $table->string('nome_proprietario');
            $table->string('cpf_proprietario', 14);
            $table->string('nome_imovel');
            $table->string('localizacao');
            $table->string('municipio');
            $table->string('codigo_incra')->nullable();
            $table->string('matricula')->nullable();

            // Dados do Serviço
            $table->string('tipo_servico');
            $table->decimal('valor_servico', 10, 2);

            // Dados do Contratado (Empresa)
            $table->string('nome_contratado');
            $table->string('cpf_cnpj_contratado');
            $table->string('endereco_contratado');

            // Vinculação com Pasta Nível 3
            $table->foreignId('pasta_id')->nullable()->constrained('pastas')->nullOnDelete();

            // Status: 'fila' = aguardando pasta, 'vinculado' = pasta encontrada
            $table->enum('status', ['fila', 'vinculado'])->default('fila');

            // PDF gerado
            $table->string('pdf_path')->nullable();

            // Assinatura digital simplificada
            $table->timestamp('assinado_em')->nullable();
            $table->string('assinatura_hash', 64)->nullable();
            $table->string('assinante_nome')->nullable();
            $table->string('assinante_ip', 45)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
