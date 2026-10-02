<?php

namespace Tests\Feature;

use App\EstadoMesa;
use App\Models\Comanda;
use App\Models\Mesa;
use App\Models\User;
use App\PapelUsuario;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ComandaControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_atendente_abre_uma_mesa_livre(): void
    {
        $atendente = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $mesa = Mesa::factory()->create();

        $this->actingAs($atendente)
            ->from(route('salao.index'))
            ->post(route('comandas.store'), [
                'mesa_id' => $mesa->id,
                'quantidade_pessoas' => 3,
            ])
            ->assertRedirect(route('salao.index'))
            ->assertSessionHas('success', 'Mesa aberta com sucesso.');

        $this->assertDatabaseHas('comandas', [
            'mesa_id' => $mesa->id,
            'aberta_por_id' => $atendente->id,
            'quantidade_pessoas' => 3,
            'status' => 'aberta',
            'ativa' => true,
        ]);
        $this->assertSame(EstadoMesa::Ocupada, $mesa->fresh()->estado);
    }

    public function test_cozinha_nao_pode_abrir_comanda(): void
    {
        $cozinha = User::factory()->create(['papel' => PapelUsuario::Cozinha]);
        $mesa = Mesa::factory()->create();

        $this->actingAs($cozinha)
            ->post(route('comandas.store'), [
                'mesa_id' => $mesa->id,
                'quantidade_pessoas' => 2,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('comandas', ['mesa_id' => $mesa->id]);
    }

    public function test_nao_abre_segunda_comanda_na_mesma_mesa(): void
    {
        $atendente = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $mesa = Mesa::factory()->create(['estado' => EstadoMesa::Ocupada]);
        Comanda::factory()->for($mesa)->for($atendente, 'abertaPor')->create();

        $this->actingAs($atendente)
            ->from(route('salao.index'))
            ->post(route('comandas.store'), [
                'mesa_id' => $mesa->id,
                'quantidade_pessoas' => 2,
            ])
            ->assertRedirect(route('salao.index'))
            ->assertSessionHasErrors([
                'mesa_id' => 'A mesa selecionada não está livre.',
            ]);

        $this->assertSame(1, Comanda::query()->whereBelongsTo($mesa)->count());
    }
}
