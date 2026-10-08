<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProducaoSegurancaTest extends TestCase
{
    use RefreshDatabase;

    public function test_cadastro_publico_nao_esta_disponivel(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'Visitante',
            'email' => 'visitante@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_dados_de_demonstracao_sao_bloqueados_em_producao(): void
    {
        $this->app->instance('env', 'production');

        try {
            $this->app->make(DatabaseSeeder::class)->run();
            $this->fail('A carga de demonstração deveria ser bloqueada.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('não podem ser carregados em produção', $exception->getMessage());
        }

        $this->assertDatabaseCount('users', 0);
    }
}
