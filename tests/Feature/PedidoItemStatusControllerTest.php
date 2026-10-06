<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\SetorProducao;
use App\Models\User;
use App\PapelUsuario;
use App\StatusItemPedido;
use App\StatusPedido;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PedidoItemStatusControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_cozinha_avanca_item_e_registra_responsavel_e_horario(): void
    {
        $this->travelTo('2026-10-04 18:30:00');
        $usuario = User::factory()->create(['papel' => PapelUsuario::Cozinha]);
        $pedido = Pedido::factory()->enviado()->create();
        $item = PedidoItem::factory()->enviado()->for($pedido)->create([
            'setor_producao_id' => SetorProducao::factory(),
        ]);

        $this->actingAs($usuario)
            ->patch(route('pedido-itens.status.update', $item), ['status' => StatusItemPedido::EmPreparo->value])
            ->assertRedirect()
            ->assertSessionHas('success', 'Status do item atualizado.');

        $this->assertDatabaseHas('pedido_itens', [
            'id' => $item->id,
            'status' => StatusItemPedido::EmPreparo->value,
            'iniciado_por_id' => $usuario->id,
            'iniciado_em' => '2026-10-04 18:30:00',
        ]);
        $this->assertDatabaseHas('pedidos', ['id' => $pedido->id, 'status' => StatusPedido::EmPreparo->value]);
    }

    public function test_item_percorre_fluxo_ate_entregue_e_sincroniza_pedido(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        $pedido = Pedido::factory()->enviado()->create();
        $item = PedidoItem::factory()->enviado()->for($pedido)->create([
            'setor_producao_id' => SetorProducao::factory(),
        ]);

        foreach ([StatusItemPedido::EmPreparo, StatusItemPedido::Pronto, StatusItemPedido::Entregue] as $status) {
            $this->actingAs($usuario)
                ->patch(route('pedido-itens.status.update', $item), ['status' => $status->value])
                ->assertRedirect();
            $item->refresh();
        }

        $this->assertSame(StatusItemPedido::Entregue, $item->status);
        $this->assertSame($usuario->id, $item->entregue_por_id);
        $this->assertSame(StatusPedido::Entregue, $pedido->refresh()->status);
        $this->assertNotNull($pedido->entregue_em);
    }

    public function test_nao_permite_pular_etapa_do_fluxo(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Cozinha]);
        $item = PedidoItem::factory()->enviado()->create(['setor_producao_id' => SetorProducao::factory()]);

        $this->actingAs($usuario)
            ->patch(route('pedido-itens.status.update', $item), ['status' => StatusItemPedido::Pronto->value])
            ->assertSessionHasErrors(['status' => 'O item não pode avançar para esse status.']);

        $this->assertSame(StatusItemPedido::Enviado, $item->refresh()->status);
        $this->assertNull($item->pronto_em);
    }

    public function test_atendente_nao_opera_producao(): void
    {
        $atendente = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $item = PedidoItem::factory()->enviado()->create(['setor_producao_id' => SetorProducao::factory()]);

        $this->actingAs($atendente)
            ->patch(route('pedido-itens.status.update', $item), ['status' => StatusItemPedido::EmPreparo->value])
            ->assertForbidden();

        $this->assertSame(StatusItemPedido::Enviado, $item->refresh()->status);
    }
}
