<?php

namespace Tests\Feature;

use App\Models\Mesa;
use App\Models\SetorSalao;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MapaSalaoControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_convidado_e_redirecionado_para_o_login(): void
    {
        $this->get(route('salao.index'))->assertRedirect(route('login'));
    }

    public function test_usuario_autenticado_visualiza_setores_e_mesas_ativas(): void
    {
        $usuario = User::factory()->create();
        $setor = SetorSalao::factory()->create(['nome' => 'Salão principal']);
        Mesa::factory()->for($setor, 'setorSalao')->create(['numero' => '01']);
        Mesa::factory()->for($setor, 'setorSalao')->create(['numero' => '02', 'ativa' => false]);

        $this->actingAs($usuario)
            ->get(route('salao.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('salao/index')
                ->has('setores', 1)
                ->where('setores.0.nome', 'Salão principal')
                ->has('setores.0.mesas', 1)
                ->where('setores.0.mesas.0.numero', '01'));
    }
}
