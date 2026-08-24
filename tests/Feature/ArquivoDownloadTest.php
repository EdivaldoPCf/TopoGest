<?php

namespace Tests\Feature;

use App\Models\Arquivo;
use App\Models\Pasta;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ArquivoDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_arquivo_download_returns_correct_filename_and_headers(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        // 1. Create a parent folder structure
        $pasta = Pasta::create([
            'nome' => 'Pasta Teste',
            'tipo_servico' => 'pendente',
            'cliente_id' => $user->id,
            'identificador_cliente' => $user->cpf,
        ]);

        // 2. Put a fake file on public disk
        $filePath = 'arquivos/testfilehash.pdf';
        Storage::disk('public')->put($filePath, 'fake content');

        // 3. Create database entry for this file dynamically based on schema columns
        $data = [
            'tipo' => 'PDF',
            'pasta_id' => $pasta->id,
        ];

        if (Schema::hasColumn('arquivos', 'nome')) {
            $data['nome'] = 'Relatorio De Medicao Final.pdf';
        }
        if (Schema::hasColumn('arquivos', 'nome_original')) {
            $data['nome_original'] = 'Relatorio De Medicao Final.pdf';
        }
        if (Schema::hasColumn('arquivos', 'path')) {
            $data['path'] = $filePath;
        }
        if (Schema::hasColumn('arquivos', 'caminho')) {
            $data['caminho'] = $filePath;
        }
        if (Schema::hasColumn('arquivos', 'tamanho')) {
            $data['tamanho'] = 0.05;
        }

        $arquivo = Arquivo::create($data);

        // 4. Download it as the user
        $response = $this
            ->actingAs($user)
            ->get(route('arquivo.download', $arquivo->id));

        // 5. Assertions
        $response->assertOk();
        $response->assertHeader('Content-Disposition', 'attachment; filename="Relatorio De Medicao Final.pdf"');
        
        // Assert Activity Log was created
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'acao' => 'download',
        ]);
        
        // Verify Activity Log details
        $log = ActivityLog::where('user_id', $user->id)->where('acao', 'download')->first();
        $this->assertNotNull($log);
        $this->assertEquals('Relatorio De Medicao Final.pdf', $log->detalhes['arquivo_nome']);
        $this->assertEquals($arquivo->id, $log->detalhes['arquivo_id']);
        $this->assertEquals('Pasta Teste', $log->detalhes['imovel_nome']);
    }
}
