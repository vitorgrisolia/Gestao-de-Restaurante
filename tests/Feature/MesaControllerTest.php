<?php

namespace Tests\Feature;

use App\Models\Mesa;
use App\Models\SetorSalao;
use App\Models\User;
use App\PapelUsuario;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MesaControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_gerente_cadastra_mesa_em_um_setor(): void
    {
        $gerente = User::factory()->create(['papel' => PapelUsuario::Gerente]);
        $setor = SetorSalao::factory()->create();

        $this->actingAs($gerente)
            ->from(route('salao.index'))
            ->post(route('mesas.store'), [
                'setor_salao_id' => $setor->id,
                'numero' => '10',
                'capacidade' => 6,
            ])
            ->assertRedirect(route('salao.index'))
            ->assertSessionHas('success', 'Mesa cadastrada com sucesso.');

        $this->assertDatabaseHas('mesas', [
            'setor_salao_id' => $setor->id,
            'numero' => '10',
            'capacidade' => 6,
            'estado' => 'livre',
        ]);
    }

    public function test_nao_permite_identificacao_repetida_no_mesmo_setor(): void
    {
        $proprietario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        $mesa = Mesa::factory()->create(['numero' => '10']);

        $this->actingAs($proprietario)
            ->from(route('salao.index'))
            ->post(route('mesas.store'), [
                'setor_salao_id' => $mesa->setor_salao_id,
                'numero' => '10',
                'capacidade' => 4,
            ])
            ->assertRedirect(route('salao.index'))
            ->assertSessionHasErrors([
                'numero' => 'Já existe uma mesa com esta identificação no setor.',
            ]);
    }
}
