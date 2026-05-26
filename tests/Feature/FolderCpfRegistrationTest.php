<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Pasta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FolderCpfRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function generateValidCpf(): string
    {
        $n = [];
        for ($i = 0; $i < 9; $i++) {
            $n[] = rand(0, 9);
        }

        $d1 = 0;
        for ($i = 0; $i < 9; $i++) {
            $d1 += $n[$i] * (10 - $i);
        }
        $d1 = 11 - ($d1 % 11);
        if ($d1 >= 10) {
            $d1 = 0;
        }

        $d2 = 0;
        for ($i = 0; $i < 9; $i++) {
            $d2 += $n[$i] * (11 - $i);
        }
        $d2 += $d1 * 2;
        $d2 = 11 - ($d2 % 11);
        if ($d2 >= 10) {
            $d2 = 0;
        }

        return implode('', $n) . $d1 . $d2;
    }

    public function test_create_level_3_folder_with_unregistered_cpf_succeeds_and_saves_as_pending(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'approved' => true]);

        // Level 1 folder (Year)
        $level1 = Pasta::create(['nome' => '2026', 'tipo_servico' => 'pendente']);
        // Level 2 folder (Category)
        $level2 = Pasta::create(['nome' => 'Topografia', 'parent_id' => $level1->id, 'tipo_servico' => 'pendente']);

        $response = $this
            ->actingAs($admin)
            ->post('/pasta', [
                'nome' => 'Imovel Teste',
                'parent_id' => $level2->id,
                'identificador_cliente' => '123.456.789-01', // unregistered CPF
                'categoria_servico' => 'Georreferenciamento',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('pastas', [
            'nome' => 'Imovel Teste',
            'parent_id' => $level2->id,
            'cliente_id' => null,
            'identificador_cliente' => '12345678901',
            'categoria_servico' => 'Georreferenciamento',
        ]);
    }

    public function test_subfolder_inherits_unregistered_client_details(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'approved' => true]);

        // Level 1
        $level1 = Pasta::create(['nome' => '2026', 'tipo_servico' => 'pendente']);
        // Level 2
        $level2 = Pasta::create(['nome' => 'Topografia', 'parent_id' => $level1->id, 'tipo_servico' => 'pendente']);
        // Level 3
        $level3 = Pasta::create([
            'nome' => 'Imovel Teste',
            'parent_id' => $level2->id,
            'cliente_id' => null,
            'identificador_cliente' => '12345678901',
            'categoria_servico' => 'Georreferenciamento',
            'tipo_servico' => 'pendente'
        ]);

        $response = $this
            ->actingAs($admin)
            ->post('/pasta', [
                'nome' => 'Subpasta Documentos',
                'parent_id' => $level3->id,
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('pastas', [
            'nome' => 'Subpasta Documentos',
            'parent_id' => $level3->id,
            'cliente_id' => null,
            'identificador_cliente' => '12345678901',
            'categoria_servico' => 'Georreferenciamento',
        ]);
    }

    public function test_folders_automatically_linked_when_user_registers(): void
    {
        $cpf = $this->generateValidCpf();

        // Level 1
        $level1 = Pasta::create(['nome' => '2026', 'tipo_servico' => 'pendente']);
        // Level 2
        $level2 = Pasta::create(['nome' => 'Topografia', 'parent_id' => $level1->id, 'tipo_servico' => 'pendente']);
        // Level 3 (unregistered client)
        $level3 = Pasta::create([
            'nome' => 'Imovel A',
            'parent_id' => $level2->id,
            'cliente_id' => null,
            'identificador_cliente' => $cpf,
            'categoria_servico' => 'Georreferenciamento',
            'tipo_servico' => 'pendente'
        ]);
        // Level 4 subfolder
        $level4 = Pasta::create([
            'nome' => 'Documentos A',
            'parent_id' => $level3->id,
            'cliente_id' => null,
            'identificador_cliente' => $cpf,
            'categoria_servico' => 'Georreferenciamento',
            'tipo_servico' => 'pendente'
        ]);

        // Register client with matching CPF
        $client = User::create([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'cpf' => $cpf,
            'phone' => '11999999999',
            'password' => bcrypt('password'),
            'role' => 'cliente',
            'approved' => true
        ]);

        $this->assertDatabaseHas('pastas', [
            'id' => $level3->id,
            'cliente_id' => $client->id,
        ]);

        $this->assertDatabaseHas('pastas', [
            'id' => $level4->id,
            'cliente_id' => $client->id,
        ]);
    }

    public function test_associated_folders_cpf_migrates_on_user_cpf_update(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'approved' => true]);

        $oldCpf = $this->generateValidCpf();
        $newCpf = $this->generateValidCpf();

        // Create client
        $client = User::create([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'cpf' => $oldCpf,
            'phone' => '11999999999',
            'password' => bcrypt('password'),
            'role' => 'cliente',
            'approved' => true
        ]);

        // Level 1
        $level1 = Pasta::create(['nome' => '2026', 'tipo_servico' => 'pendente']);
        // Level 2
        $level2 = Pasta::create(['nome' => 'Topografia', 'parent_id' => $level1->id, 'tipo_servico' => 'pendente']);
        // Level 3
        $level3 = Pasta::create([
            'nome' => 'Imovel A',
            'parent_id' => $level2->id,
            'cliente_id' => $client->id,
            'identificador_cliente' => $oldCpf,
            'categoria_servico' => 'Georreferenciamento',
            'tipo_servico' => 'pendente'
        ]);

        // Admin updates client CPF
        $response = $this
            ->actingAs($admin)
            ->put("/admin/clientes/{$client->id}", [
                'name' => 'João Silva Alterado',
                'email' => 'joao@example.com',
                'cpf' => $newCpf,
                'phone' => '11999999999',
            ]);

        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', [
            'id' => $client->id,
            'cpf' => $newCpf,
        ]);

        // Folders should have migrated to new CPF while remaining linked to client
        $this->assertDatabaseHas('pastas', [
            'id' => $level3->id,
            'cliente_id' => $client->id,
            'identificador_cliente' => $newCpf,
        ]);
    }
}
