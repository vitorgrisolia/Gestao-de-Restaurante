<?php

namespace Tests\Feature;

use App\Models\Ingrediente;
use App\Models\User;
use App\PapelUsuario;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class VisaoGeralTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_proprietario_recebe_resumos_de_todas_as_areas(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        Ingrediente::factory()->create(['ativo' => true, 'estoque_atual' => 1, 'estoque_minimo' => 2]);

        $this->actingAs($usuario)->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')->has('secoes', 8)
            ->where('secoes.3.id', 'estoque')
            ->where('secoes.3.indicadores.No mínimo ou abaixo', 1));
    }

    public function test_cozinha_nao_recebe_dados_financeiros_ou_administrativos(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Cozinha]);

        $this->actingAs($usuario)->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')->has('secoes', 1)->where('secoes.0.id', 'producao'));
    }
}
