<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\SetorProducao;
use App\Models\User;
use App\PapelUsuario;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PainelProducaoControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_cozinha_visualiza_itens_do_setor_e_atrasos(): void
    {
        $this->travelTo('2026-10-04 19:00:00');
        $usuario = User::factory()->create(['papel' => PapelUsuario::Cozinha]);
        $setor = SetorProducao::factory()->create(['nome' => 'Cozinha']);
        $pedido = Pedido::factory()->enviado()->create();
        PedidoItem::factory()->enviado()->for($pedido)->create([
            'setor_producao_id' => $setor->id,
            'nome_item' => 'Filé à parmegiana',
            'enviado_em' => now()->subMinutes(16),
        ]);

        $this->actingAs($usuario)
            ->get(route('producao.index', ['setor' => $setor->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('producao/painel')
                ->where('setorSelecionado', $setor->id)
                ->has('itens', 1)
                ->where('itens.0.nome_item', 'Filé à parmegiana')
                ->where('itens.0.atrasado', true));
    }

    public function test_painel_nao_mistura_itens_de_outro_setor(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Gerente]);
        $cozinha = SetorProducao::factory()->create();
        $bar = SetorProducao::factory()->create();
        PedidoItem::factory()->enviado()->create(['setor_producao_id' => $cozinha->id]);
        PedidoItem::factory()->enviado()->create(['setor_producao_id' => $bar->id]);

        $this->actingAs($usuario)
            ->get(route('producao.index', ['setor' => $cozinha->id]))
            ->assertInertia(fn (Assert $page): Assert => $page->has('itens', 1));
    }

    public function test_atendente_nao_acessa_painel_de_producao(): void
    {
        $atendente = User::factory()->create(['papel' => PapelUsuario::Atendente]);

        $this->actingAs($atendente)->get(route('producao.index'))->assertForbidden();
    }
}
